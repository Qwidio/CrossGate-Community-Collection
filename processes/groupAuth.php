<?php
require_once 'database.php';
function generateApiKey($length) {
    return bin2hex(random_bytes($length / 2));
}
function getIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
if (isset($_POST['Register'])) {
    // check before check
    $errors = '';
    $root_route = "../";
    require_once '../secureSession.php';
    if (isset($_SESSION['profileTags'])) {
        $aidis = $_SESSION['profileTags'];
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    if (isset($_SESSION['GroupsToken'])) {
        header ('location: index.php');
        exit;
    }
    $GName = $_POST['GName'];
    $GName = htmlspecialchars($GName, ENT_QUOTES, 'UTF-8');
    $GDescs = $_POST['GDescs'];
    $GDescs = htmlspecialchars($GDescs, ENT_QUOTES, 'UTF-8');
    $passkeys = $_POST['passkeys'];
    if (empty($GName) || empty($passkeys) || empty($GDescs)) {
        $_SESSION['corsmsg'] = "missing credentials";
        header('location: ../Groups/index.php');
        exit;
    }
    $stmt_check_GName = $connects->prepare("SELECT identification FROM ogroup WHERE names = ?");
    $stmt_check_GName->bind_param("s", $GName);
    $stmt_check_GName->execute();
    $result_check_GName = $stmt_check_GName->get_result();
    if ($result_check_GName->num_rows == 0) {
        $randKey = generateApiKey(32);
        $new_gids = $GName . "_" . $randKey;
        $members = ["$aidis"];
        $sites[] = [
            "site"  => "", 
            "yt"    => ""
        ];
        $members = json_encode($members);
        $sites = json_encode($sites);
        $stmt_insert = $connects->prepare("INSERT INTO ogroup(identification, names, about, founder, founded, members, sites) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssssss", $new_gids, $GName, $GDescs, $aidis, date('d/m/Y'), $members, $sites);
        if ($stmt_insert->execute()) {
            $stmt_insert_access = $connects->prepare("INSERT INTO groupaccess (profileTags, passkeys, roles, og_identification, accountState) VALUES (?, MD5(?), 'founder', ?, 'approved')");
            $stmt_insert_access->bind_param("sss", $aidis, $passkeys, $new_gids);
            if ($stmt_insert_access->execute()) {
                $stmt_change_invite = $connects->prepare("UPDATE profiles SET allowInvite = 'inactive' WHERE profileTags = ?");
                $stmt_change_invite->bind_param("s", $aidis);
                if($stmt_change_invite->execute()){
                    $_SESSION['corsmsg'] = 'Registration successfully executed.';
                    header ('location: ../Groups/index.php');
                    $stmt_insert_access->close();
                    exit;
                }else{
                    $_SESSION['corsmsg'] = 'Failed to updated invite settings ' . $stmt_change_invite->error;
                    header ('location: ../Groups/index.php');
                    $stmt_change_invite->close();
                    exit;
                };
            } else {
                $_SESSION['corsmsg'] = 'Failed to add group access. ' . $stmt_insert_access->error;
                header ('location: ../Groups/index.php');
                $stmt_insert_access->close();
                exit;
            }
        } else {
            $_SESSION['corsmsg'] = 'Failed to register the new Groups. ' . $stmt_insert->error;
            header ('location: ../Groups/index.php');
            $insert_session->close();
            exit;
        }
    } else {
        $_SESSION['corsmsg'] = "Groups name taken. " . $stmt_check_GName->error;
        header('location: ../Groups/index.php');
        exit;
    }
    $stmt_check_username->close();
} else if (isset($_POST['Login'])) {
    // check before check
    $errors = '';
    $root_route = "../";
    require_once '../secureSession.php';
    if (isset($_SESSION['profileTags'])) {
        $aidis = $_SESSION['profileTags'];
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    if (isset($_SESSION['GroupsToken'])) {
        header ('location: index.php');
        exit;
    }
    $username = $_POST['username'];
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $passkeys = $_POST['passkeys'];
    if (empty($username) || empty($passkeys)) {
        $_SESSION['corsmsg'] = "missing credentials";
        header('location: ../Groups/index.php');
        exit;
    }
    $stmt_check_username = $connects->prepare("SELECT userState, profileTags FROM user WHERE username = ?");
    $stmt_check_username->bind_param("s", $username);
    $stmt_check_username->execute();
    $result_check_username = $stmt_check_username->get_result();
    if ($result_check_username->num_rows == 1) {
        $value = $result_check_username->fetch_assoc();
        $current_state = $value['userState'];
        $profileTags = $value['profileTags'];
        if ($aidis != $profileTags) {
            $_SESSION['corsmsg'] = "use the same user credentials you currently logged with";
            header('location: ../Groups/index.php');
            exit;
        }
        $state = "approved";
        if ($current_state != $state) {
            $errors = "your account currently still in review";
            $_SESSION['corsmsg'] = $errors;
            header('location: ../Groups/index.php');
            exit;
        }
        $stmt_check_passkeys = $connects->prepare("SELECT og_identification, roles FROM groupaccess WHERE profileTags = ? AND passkeys = MD5(?) AND accountState = ?");
        $stmt_check_passkeys->bind_param("sss", $aidis, $passkeys, $state);
        $stmt_check_passkeys->execute();
        $result_check_passkeys = $stmt_check_passkeys->get_result();
        if ($result_check_passkeys->num_rows == 1) {
            $tempCheckPassValue = $result_check_passkeys->fetch_assoc();
            $gids = $tempCheckPassValue['og_identification'];
            $roles = $tempCheckPassValue['roles'];
            $check_session = $connects->prepare("SELECT token FROM groupsession WHERE profileTags = ? AND og_identification = ?;");
            $check_session->bind_param("ss", $aidis, $gids);
            $check_session->execute();
            $result_check_session = $check_session->get_result();
            if ($result_check_session->num_rows > 0) {
                $tempCheckSessValue = $result_check_session->fetch_assoc();
                $OldToken = $tempCheckSessValue['token'];
                $stmt_delete_oldsess = $connects->prepare("DELETE FROM groupsession WHERE token = ? AND profileTags = ?");
                $stmt_delete_oldsess->bind_param("ss", $OldToken, $aidis);
                if($stmt_delete_oldsess->execute()){
                    $errors = 'old session token deleted';
                }else{
                    $errors = 'Failed to delete old sessions';
                };
                $stmt_delete_oldsess->close();
            };  
            $tokens = generateApiKey(64);
            $check_session = $connects->prepare("SELECT token FROM groupsession WHERE token = ?;");
            $check_session->bind_param("s", $tokens);
            $check_session->execute();
            $result_check_session = $check_session->get_result();
            if ($result_check_session->num_rows == 0) {
                $addrss = getIpAddr();
                $osids = $_POST['os'];
                $y = date("Y");
                $m = date("m");
                $d = date("d");
                $d = $d + 1;
                if ($d > 28) {
                    $m = $m + 1;
                    $d = 1;
                }
                $expdate = $m . "/" . $d . "/" . $y;
                $convertedexpdate = DateTime::createFromFormat('d/m/Y', $expdate);
                $unixexpdate = $convertedexpdate->getTimestamp();
                $insert_session = $connects->prepare("INSERT INTO groupsession(token, profileTags, og_identification, addrss, osids, expirationDate, lastlogs) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert_session->bind_param("sssssss", $tokens, $aidis, $gids, $addrss, $osids, $expdate, date('d/m/Y h:i'));
                if($insert_session->execute()){
                    $_SESSION['GroupsToken'] = $tokens;
                    $_SESSION['roles'] = $roles;
                    $_SESSION['corsmsg'] = 'Login Successful. ' . $errors;
                    header('location: ../Groups/manage.php');
                    $insert_session->close();
                    exit;
                }else{
                    $_SESSION['corsmsg'] = 'Failed to add new sessions. ' . $errors;
                    header ('location: ../Groups/index.php');
                    $insert_session->close();
                    exit;
                };
            }
            $check_session->close();
        } else {
            $_SESSION['corsmsg'] = "Invalid Passkeys, try again. " . $result_check_passkeys->error;
            header('location: ../Groups/index.php');
            exit;
        }
        $stmt_check_passkeys->close();
    } else {
        $_SESSION['corsmsg'] = "User data inaccessible. " . $result_check_username->error;
        header('location: ../Groups/index.php');
        exit;
    }
    $stmt_check_username->close();
}
?>

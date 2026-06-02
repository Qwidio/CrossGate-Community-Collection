<?php
require_once 'database.php';
$errors = '';
if (isset($_SESSION['prev_loc'])) {
    $prev_loc = $_SESSION['prev_loc'];
} else {
    $prev_loc = "index.php";
};
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

if (isset($_POST['Login'])) {
    $username = $_POST['username'];
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];
    if (empty($username) || empty($password)) {
        $_SESSION['corsmsg'] = "missing credentials";
        header('location: ../Groups/index.php');
        exit;
    }
    $stmt_check_username = $connects->prepare("SELECT userState FROM user WHERE username = ?");
    $stmt_check_username->bind_param("s", $username);
    $stmt_check_username->execute();
    $result_check_username = $stmt_check_username->get_result();
    if ($result_check_username->num_rows == 1) {
        $value = $result_check_username->fetch_assoc();
        $current_state = $value['userState'];
        $state = "approved";
        if ($current_state != $state) {
            $errors = "your account currently still in review";
            $_SESSION['corsmsg'] = $errors;
            header('location: ../connect_it/connect_it.php?state=login');
            exit;
        }
        $stmt_check_password = $connects->prepare("SELECT * FROM user WHERE username = ? AND password = MD5(?) AND userState = ?");
        $stmt_check_password->bind_param("sss", $username, $password, $state);
        $stmt_check_password->execute();
        $result_check_password = $stmt_check_password->get_result();
        if ($result_check_password->num_rows == 1) {
            $value = $result_check_password->fetch_assoc();
            $aidis = $value['profileTags'];
            if (isset($_POST['sessionless'])) {
                $check_session = $connects->prepare("SELECT sessiontokens FROM sessionlogs WHERE profileTags = ?;");
                $check_session->bind_param("s", $aidis);
                $check_session->execute();
                $result_check_session = $check_session->get_result();
                if ($result_check_session->num_rows > 2) {
                    $_SESSION['corsmsg'] = 'Your account exceeds the number of session allowed';
                    header ('location: ../connect_it/connect_it.php');
                    exit;
                };
                function generateApiKey($length) {
                    return bin2hex(random_bytes($length / 2));
                }
                $tokens = generateApiKey(64);
                $check_session = $connects->prepare("SELECT sessiontokens FROM sessionlogs WHERE sessiontokens = ?;");
                $check_session->bind_param("s", $tokens);
                $check_session->execute();
                $result_check_session = $check_session->get_result();
                if ($result_check_session->num_rows == 0) {
                    $addrss = getIpAddr();
                    $osids = $_POST['os'];
                    $y = date("Y");
                    $m = date("m");
                    $d = date("d");
                    $d = $d + 15;
                    if ($d > 27) {
                        $m = $m + 1;
                        $d = 15;
                    }
                    $expdate = $m . "/" . $d . "/" . $y;
                    $convertedexpdate = DateTime::createFromFormat('d/m/Y', $expdate);
                    $unixexpdate = $convertedexpdate->getTimestamp();
                    $insert_session = $connects->prepare("INSERT INTO sessionlogs(profileTags, sessiontokens, addrss, osids, expirationDate, lastlogs) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_session->bind_param("ssssss", $aidis, $tokens, $addrss, $osids, $expdate, date('d/m/Y h:i'));
                    if($insert_session->execute()){
                        unset($_COOKIE['sessionToken']);
                        setcookie("sessionToken", $tokens, $unixexpdate, "/");
                        $_SESSION['profileTags'] = $aidis;
                        $_SESSION['corsmsg'] = 'Login Successful';
                        header('location: ../' . $prev_loc);
                        exit;
                    }else{
                        $_SESSION['corsmsg'] = 'Failed to add new sessions';
                        header ('location: ../connect_it/connect_it.php');
                        exit;
                    };
                    $insert_session->close();
                }
                $check_session->close();
            } else {
                $_SESSION['profileTags'] = $aidis;
                $_SESSION['corsmsg'] = 'Login Successful';
                header('location: ../' . $prev_loc);
                exit;
            }
        } else {
            $errors = "Password Invalid, try again";
            $_SESSION['corsmsg'] = $errors;
            header('location: ../connect_it/connect_it.php?state=login');
            exit;
        }
        $stmt_check_password->close();
    } else {
        $errors = "User data inaccessible";
        $_SESSION['corsmsg'] = $errors;
        header('location: ../connect_it/connect_it.php?state=login');
        exit;
    }
    $stmt_check_username->close();
}
?>

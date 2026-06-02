<?php
require_once '../processes/database.php';
if (isset($_POST['submit'])) {
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
    // check before check
    $root_route = "../";
    require_once '../secureSession.php';
    if (isset($_SESSION['profileTags'])) {
        $aidis = $_SESSION['profileTags'];
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    if (!isset($_POST['inviteToken'])) {
        $_SESSION['corsmsg'] = "empty token";
        header ('location: ../profile.php?user=self');
        exit;
    }
    $inviteToken = $_POST['inviteToken'];
    // verif begins
    $stmt_check_invite = $connects->prepare("SELECT og_identification, custom_msg FROM groupinvite WHERE profileTags = ? AND inviteToken = ? ;");
    $stmt_check_invite->bind_param("ss", $aidis, $inviteToken);
    $stmt_check_invite->execute();
    $result_check_invite = $stmt_check_invite->get_result();
    if ($result_check_invite->num_rows > 0) {
        $tempInvtVAl = $result_check_invite->fetch_assoc();
        $gids = $tempInvtVAl['og_identification'];
    } else {
        $_SESSION['corsmsg'] = "Failed to find the invite. " . $stmt_check_invite->error;
        header ('location: ../profile.php?user=self');
        exit;
    }
    $check_orgs = $connects->prepare("SELECT names, members FROM ogroup WHERE identification = ? ;");
    $check_orgs->bind_param("s", $gids);
    $check_orgs->execute();
    $result_check_orgs = $check_orgs->get_result();
    if ($result_check_orgs->num_rows > 0) {
        $tempOgVAl = $result_check_orgs->fetch_assoc();
        $gName = $tempOgVAl['names'];
        $members = json_decode($tempOgVAl['members'], true);
    } else {
        $_SESSION['corsmsg'] = "Groups that invites does not exist. " . $check_orgs->error;
        header ('location: ../profile.php?user=self');
        exit;
    };

    $stmt_check_access = $connects->prepare("SELECT roles FROM groupaccess WHERE profileTags = ? AND og_identification = ? AND accountState = 'invited'");
    $stmt_check_access->bind_param("ss", $aidis, $gids);
    $stmt_check_access->execute();
    $result_check_access = $stmt_check_access->get_result();
    if ($result_check_access->num_rows > 0) {
        $tempCheckAcsValue = $result_check_access->fetch_assoc();
        $roles = $tempCheckAcsValue['roles'];
    } else {
        $_SESSION['corsmsg'] = "Failed to check access " . $stmt_check_access->error;
        header ('location: ../profile.php?user=self');
        exit;
    }

    $initReq = $_POST['submit'];
    // accepting invite and joins
    if ($initReq === "Join") {
        $newMembers = array();
        if (!in_array($aidis, $newMembers)) {
            $newMembers[$aidis];
        }
        foreach ($members as $member) {
            if ($member != $aidis) {
                $newMembers[$member];
            }
        }
        $newMembers = json_encode($newMembers, JSON_UNESCAPED_SLASHES);
        $stmt_update_access = $connects->prepare("UPDATE groupaccess SET accountState = 'approved' WHERE profileTags = ? and og_identification = ? ;");
        $stmt_update_access->bind_param("ss", $aidis, $gids);
        $stmt_update_access->execute();
        if ($stmt_update_access->affected_rows > 0) {
            $newMembers = json_encode($newMembers);
            $addMember = $connects->prepare("UPDATE ogroup SET members = ? WHERE identification = ? ;");
            $addMember->bind_param("ss", $newMembers, $gids);
            $addMember->execute();
            if ($addMember->affected_rows > 0) {
                $tokens = $aidis . bin2hex(random_bytes(32));
                $addrss = getIpAddr();
                $y = date("Y");
                $m = date("m");
                $d = date("d");
                $d = $d + 1;
                if ($d > 28) {
                    $m = $m + 1;
                    $d = 1;
                }
                $expdate = $d . "/" . $m . "/" . $y;
                $convertedexpdate = DateTime::createFromFormat('d/m/Y', $expdate);
                $unixexpdate = $convertedexpdate->getTimestamp();
                $insert_session = $connects->prepare("INSERT INTO groupsession(token, profileTags, og_identification, addrss, osids, expirationDate, lastlogs) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert_session->bind_param("sssssss", $tokens, $aidis, $gids, $addrss, $osids, $expdate, date('d/m/Y h:i'));
                if($insert_session->execute()){
                    $_SESSION['GroupsToken'] = $tokens;
                    $_SESSION['roles'] = $roles;
                    $_SESSION['resetPass'] = true;
                    $_SESSION['corsmsg'] = "Successfully joined " . $gName;
                    header('location: manage.php');
                    $insert_session->close();
                    exit;
                }else{
                    $_SESSION['corsmsg'] = 'Failed to add new sessions. ' . $insert_session->error;
                    header ('location: index.php');
                    $insert_session->close();
                    exit;
                };
            } else {
                $stmt_reupdate_access = $connects->prepare("UPDATE groupaccess SET profileTags = ?, accountState = 'disabled' WHERE profileTags = ? and og_identification = ? ;");
                $stmt_reupdate_access->bind_param("sss", $aidis.date('d/m/Y'), $aidis, $gids);
                $stmt_reupdate_access->execute();
                if ($result_update_access->affected_rows > 0) {
                    $_SESSION['corsmsg'] = "Failed to add you as new member. " . $addMember->error;
                    header ('location: ../profile.php?user=self');
                    exit;
                }
            }
        } else {
            $_SESSION['corsmsg'] = "Failed to update account access. " . $stmt_update_access->error;
            header ('location: ../profile.php?user=self');
            exit;
        }
    //  removing invites
    } else if ($initReq === "Dismiss") {
        $stmt_update_invite = $connects->prepare("UPDATE groupaccess SET accountState = 'disabled' WHERE profileTags = ? and og_identification = ? ;");
        $stmt_update_invite->bind_param("ss", $aidis, $gids);
        $stmt_update_invite->execute();
        if ($stmt_update_invite->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Dismissed " . $gName . " Invites.";
            header ('location: ../profile.php?user=self');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to check account access. " . $stmt_update_invite->error;
            header ('location: ../profile.php?user=self');
            exit;
        }
        $stmt_update_access = $connects->prepare("UPDATE groupaccess SET accountState = 'disabled' WHERE profileTags = ? and og_identification = ? ;");
        $stmt_update_access->bind_param("ss", $aidis, $gids);
        $stmt_update_access->execute();
        if ($stmt_update_access->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Dismissed " . $gName . " Invites.";
            header ('location: ../profile.php?user=self');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to check account access. " . $stmt_update_access->error;
            header ('location: ../profile.php?user=self');
            exit;
        }
    }
} else {
    header ('location: ../index.php');
    exit;
};
?>
<?php
require_once '../processes/database.php';
if (isset($_POST['submit'])) {
    if (!isset($_POST['profiletags'])) {
        $_SESSION['corsmsg'] = "Missing credentials";
        header('location: manage.php');
        exit;
    }
    // checks before check
    $root_route = "../";
    require_once '../secureSession.php';
    require_once 'ReAuth.php';
    if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
        $aidis = $_SESSION['profileTags'];
        $gToken = $_SESSION['GroupsToken'];
        $gids = $_SESSION['gids'];
        $ChangerRoles = $_SESSION['roles'];
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    $profileTags = $_POST['profiletags'];
    $newpasskeys = $_POST['newpasskeys'];
    $initReq = $_POST['submit'];
    $initReq = htmlspecialchars($initReq, ENT_QUOTES, 'UTF-8');
    $stmt_check_access = $connects->prepare("SELECT roles FROM groupaccess WHERE profileTags = ? AND og_identification = ? AND accountState = 'approved';");
    $stmt_check_access->bind_param("ss", $profileTags, $gids);
    $stmt_check_access->execute();
    $result_check_access = $stmt_check_access->get_result();
    if ($result_check_access->num_rows == 1) {
        $tempCheckAcsValue = $result_check_access->fetch_assoc();
        $roles = $tempCheckAcsValue['roles'];
    } else {
        $_SESSION['corsmsg'] = "Error: " . $result_check_access->error;
        header('location: manage.php');
        exit;
    }
    if ($aidis === $profileTags && isset($_SESSION['resetPass']) && $_SESSION['resetPass'] == true) {
        $initReq = "Reset";
    } else if ($initReq === "Change" && $ChangerRoles != "founder") {
        $_SESSION['corsmsg'] = "Unpermited access to change password";
        header ('location: manage.php');
        exit;
    }
    if ($initReq === "Reset") {
        $stmt_update_passkeys = $connects->prepare("UPDATE groupaccess SET passkeys = MD5(?) WHERE profileTags = ? and og_identification = ? AND accountState = 'approved';");
        $stmt_update_passkeys->bind_param("sss", $newpasskeys, $aidis, $gids);
        $stmt_update_passkeys->execute();
        if ($stmt_update_passkeys->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Passkeys changed";
            header('location: manage.php');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to update account passkeys " . $stmt_update_passkeys->error;
            header ('location: manage.php');
            exit;
        }
    } else if ($initReq === "Change") {
        $stmt_update_passkeys = $connects->prepare("UPDATE groupaccess SET passkeys = MD5(?) WHERE profileTags = ? and og_identification = ? AND accountState = 'approved';");
        $stmt_update_passkeys->bind_param("sss", $newpasskeys, $profileTags, $gids);
        $stmt_update_passkeys->execute();
        if ($stmt_update_passkeys->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Password successfully changed";
            header('location: manage.php');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to update account passkeys " . $stmt_update_passkeys->error;
            header ('location: ../index.php');
            exit;
        }
    }
}
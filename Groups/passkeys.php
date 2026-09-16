<?php
require_once '../processes/database.php';
if (isset($_POST['submit'])) {
    if (!isset($_POST['profiletags'])) {
        $_SESSION['corsmsg'] = "Missing credentials";
        header('location: manage.php');
        exit;
    }
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
    $profileTags = (string)($_POST['profileTags'] ?? '');
    $newpasskeys = (string)($_POST['newpasskeys'] ?? '');
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
    if ($newpasskeys === '') {
        $_SESSION['corsmsg'] = 'New passkey is required.';
        header('Location: manage.php');
        exit;
    }
    if (mb_strlen($newpasskeys) < 8) {
        $_SESSION['corsmsg'] = 'New passkey must be at least 8 characters.';
        header('Location: manage.php');
        exit;
    }
    $hashedPasskey = password_hash($newpasskeys, PASSWORD_DEFAULT);
    if ($hashedPasskey === false) {
        $_SESSION['corsmsg'] = 'Failed to secure the new passkey.';
        header('Location: manage.php');
        exit;
    }
    if ($initReq === "Reset") {
        $stmt_update_passkeys = $connects->prepare("UPDATE groupaccess SET passkeys = ? WHERE profileTags = ? and og_identification = ? AND accountState = 'approved';");
        $stmt_update_passkeys->bind_param("sss", $hashedPasskey, $aidis, $gids);
        if (!$stmt_update_passkeys->execute()) {
            $_SESSION['corsmsg'] = "Failed to update account passkeys " . $stmt_update_passkeys->error;
            header ('location: manage.php');
            exit;
        }
        $_SESSION['corsmsg'] = "Passkeys changed";
        header('location: manage.php');
        exit;
    } else if ($initReq === "Change") {
        $stmt_update_passkeys = $connects->prepare("UPDATE groupaccess SET passkeys = ? WHERE profileTags = ? and og_identification = ? AND accountState = 'approved';");
        $stmt_update_passkeys->bind_param("sss", $hashedPasskey, $profileTags, $gids);
        if (!$stmt_update_passkeys->execute()) {
            $_SESSION['corsmsg'] = "Failed to update account passkeys " . $stmt_update_passkeys->error;
            header ('location: ../index.php');
            exit;
        }
        $_SESSION['corsmsg'] = "Password successfully changed";
        header('location: manage.php');
        exit;
    }
}
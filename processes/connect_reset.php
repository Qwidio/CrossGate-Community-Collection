<?php
require_once 'database.php';
function redirectTo(string $location): never
{
    header('Location: ' . $location);
    exit;
}
if (!isset($_POST['ResetPass'])) {
    redirectTo('../connect_it/connect_reset.php?state=reset');
}
$root_route = "";
require_once '../secureSession.php';
if (!isset($_SESSION['profileTags'])) {
    redirectTo('../connect_it/connect_reset.php?state=reset');
}
$aidis = $_SESSION['profileTags'];
$currentPassword = (string)($_POST['currentpassword'] ?? '');
$newPassword = (string)($_POST['newpassword'] ?? '');
$confirmPassword = (string)($_POST['confirmpassword'] ?? '');
if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
    $_SESSION['corsmsg'] = 'All fields are required.';
    redirectTo('../connect_it/connect_reset.php?state=reset');
}
if ($newPassword !== $confirmPassword) {
    $_SESSION['corsmsg'] = 'New password and confirm password did not match.';
    redirectTo('../connect_it/connect_reset.php?state=reset');
}
if (mb_strlen($newPassword) < 8) {
    $_SESSION['corsmsg'] = 'New password Must be atleast 8 characters';
    redirectTo('../connect_it/connect_reset.php?state=reset');
}
if (mb_strlen($newPassword) > 255) {
    $_SESSION['corsmsg'] = 'New password is too long';
    redirectTo('../connect_it/connect_reset.php?state=reset');
}
try {
    $checkPass = $connects->prepare("SELECT password FROM user WHERE profileTags = ? ;");
    if (!$checkPass) {
        throw new RuntimeException($connects->error);
    }
    $checkPass->bind_param('s', $aidis);
    if (!$checkPass->execute()) {
        throw new RuntimeException($checkPass->error);
    }
    $checkPass->store_result();
    if ($checkPass->num_rows !== 1) {
        $checkPass->close();
        $_SESSION['corsmsg'] = 'Invalid access';
        redirectTo('../connect_it/connect_reset.php?state=reset');
    }
    $checkPass->bind_result($existingPasswordHash);
    $checkPass->fetch();
    $checkPass->close();
    if (!password_verify($currentPassword, $existingPasswordHash)) {
        $_SESSION['corsmsg'] = 'Invalid username or current password.';
        redirectTo('../connect_it/connect_reset.php?state=reset');
    }
    if (password_verify($newPassword, $existingPasswordHash)) {
        $_SESSION['corsmsg'] = 'The new password must be different.';
        redirectTo('../connect_it/connect_reset.php?state=reset');
    }
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($newPasswordHash === false) {
        throw new RuntimeException('Unable to hash the new password.');
    }
    $updatePass = $connects->prepare("UPDATE user SET password = ? WHERE profileTags = ? ;");
    if (!$updatePass) {
        throw new RuntimeException($connects->error);
    }
    $updatePass->bind_param('ss', $newPasswordHash, $aidis);
    if (!$updatePass->execute()) {
        throw new RuntimeException($updatePass->error);
    }
    $updatePass->close();
    $_SESSION['corsmsg'] = 'Account password has been changed successfully.';
    if (isset($_COOKIE['sessionToken'])) {
        $aidis = $_SESSION['profileTags'];
        $token = $_COOKIE['sessionToken'];
        $logout = $connects->prepare("DELETE FROM sessionlogs WHERE sessiontokens = ? AND profileTags = ?");
        $logout->bind_param("ss", $token, $aidis);
        if(!$logout->execute()){
            $_SESSION['corsmsg'] = 'Account password has been changed successfully, Failed to delete session token';
        };
        $logout->close();
        unset($_SESSION['profileTags']);
        unset($_SESSION['GroupsToken']);
        unset($_SESSION["gids"]);
        unset($_SESSION["roles"]);
        unset($_COOKIE['sessionToken']);
        setcookie('sessionToken', '', 1, "/",);
        redirectTo('../connect_it/connect_it.php?state=login');
    }else{
        unset($_SESSION['profileTags']);
        unset($_SESSION['GroupsToken']);
        unset($_SESSION["gids"]);
        unset($_SESSION["roles"]);
        redirectTo('../connect_it/connect_it.php?state=login');
    };
} catch (\Throwable $e) {
    error_log(
        'Encountered error when resetting: ' .
        $e->getMessage() .
        ' in ' .
        $e->getFile() .
        ':' .
        $e->getLine() .
        PHP_EOL .
        $e->getTraceAsString());
    $_SESSION['corsmsg'] = 'Failed to reset password.' . $e->getMessage();
    redirectTo('../connect_it/connect_reset.php?state=reset');
}


?>
<?php
require_once 'database.php';
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
    exit;
}else{
    unset($_SESSION['profileTags']);
    unset($_SESSION['GroupsToken']);
    unset($_SESSION["gids"]);
    unset($_SESSION["roles"]);
    header('Location: ../index.php');
    exit;
};
?>

<?php
require_once 'database.php';
if (isset($_COOKIE['sessionToken'])) {
    $aidis = $_SESSION['profileTags'];
    $token = $_COOKIE['sessionToken'];
    $logout = $connects->prepare("DELETE FROM sessionlogs WHERE sessiontokens = ? AND profileTags = ?");
    $logout->bind_param("ss", $token, $aidis);
    if($logout->execute()){
        unset($_SESSION['GroupsToken']);
        unset($_SESSION["gids"]);
        unset($_SESSION["roles"]);
        unset($_COOKIE['sessionToken']);
        setcookie('sessionToken', '', 1, "/",);
        unset($_SESSION['profileTags']);
        header('Location: ../index.php');
        exit;
    }else{
        $_SESSION['corsmsg'] = 'Failed to delete this tokens';
        header('Location: ../index.php');
        exit;
    };
    $logout->close();
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

<?php
require_once '../processes/database.php';
$errors = array();
$root_route = "../";
require_once '../secureSession.php';
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
    if (isset($_POST['token']) && isset($_POST['submit'])) {
        $token = $_POST['token'];
        if (isset($_COOKIE['sessionToken']) && $_POST['token'] === $_COOKIE['sessionToken']) {
            $_SESSION['corsmsg'] = 'Cannot delete currently used token';
            header ('location: ../session.php');
            exit;
        };
        $stmt_delsess = $connects->prepare("DELETE FROM sessionlogs WHERE sessiontokens = ? AND profileTags = ?");
        $stmt_delsess->bind_param("ss", $token, $aidis);
        if($stmt_delsess->execute()){
            $_SESSION['corsmsg'] = 'session token deleted';
            header ('location: ../session.php');
            exit;
        }else{
            $_SESSION['corsmsg'] = 'Failed to delete this tokens.';
            header ('location: ../session.php');
            exit;
        };
        $stmt_delsess->close();
    } else {
        $_SESSION['corsmsg'] = 'no token found';
        header ('location: ../session.php');
        exit;
    };
} else {
    header ('location: ../index.php');
    exit;
};
?>
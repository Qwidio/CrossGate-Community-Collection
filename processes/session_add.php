<?php
require_once '../processes/database.php';
$errors = array();
$root_route = "../";
require_once '../secureSession.php';
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
    $check_session = $connects->prepare("SELECT sessiontokens FROM sessionlogs WHERE profileTags = ?;");
    $check_session->bind_param("s", $aidis);
    $check_session->execute();
    $result_check_session = $check_session->get_result();
    if ($result_check_session->num_rows > 2) {
        $_SESSION['corsmsg'] = 'Your account exceeds the number of session allowed';
        header ('location: ../session.php');
        exit;
    };
    $tokens = bin2hex(random_bytes(64/2));
    $check_session = $connects->prepare("SELECT sessiontokens FROM sessionlogs WHERE sessiontokens = ?;");
    $check_session->bind_param("s", $tokens);
    $check_session->execute();
    $result_check_session = $check_session->get_result();
    if ($result_check_session->num_rows == 0) {
        $expdate = date('Y/m/d', strtotime('+15 days'));
        $insert_session = $connects->prepare("INSERT INTO sessionlogs(profileTags, sessiontokens, expirationDate) VALUES (?, ?, ?)");
        $insert_session->bind_param("sss", $aidis, $tokens, $expdate);
        if($insert_session->execute()){
            $_SESSION['corsmsg'] = 'new session added';
            header ('location: ../session.php');
            exit;
        }else{
            $_SESSION['corsmsg'] = 'Failed to add new sessions';
            header ('location: ../session.php');
            exit;
        };
        $insert_session->close();
    }
    $check_session->close();
} else {
    header ('location: ../index.php');
    exit;
};
?>
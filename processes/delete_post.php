<?php
require_once '../processes/database.php';
if (isset($_POST['submit'])) {
    $errors = array();
    $root_route = "../";
    require_once '../secureSession.php';
    if (isset($_SESSION['profileTags'])) {
        $aidis = $_SESSION['profileTags'];
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    $getUser = $connects->prepare("SELECT profileNames FROM profiles WHERE profileTags = ?");
    $getUser->bind_param("s", $aidis);
    $getUser->execute();
    $resultGetUser = $getUser->get_result();
    if ($resultGetUser->num_rows == 1) {
        $getname = $resultGetUser->fetch_assoc();
        $pfname = $getname['profileNames'];
    } else {
        $_SESSION['corsmsg'] = 'unidentified user';
        header ('location: ../index.php');
        exit;
    }
    $initReq = $_POST['submit'];
    $initReq = htmlspecialchars($initReq, ENT_QUOTES, 'UTF-8');
    if ($initReq === "REMOVE") {
        $FoIds = $_POST['foids'];
        $post_check = $connects->prepare("SELECT ForumTitles, ForumTopics FROM forums WHERE ForumIds = ? AND forumState = 'Publics';");
        $post_check->bind_param("s", $FoIds);
        $post_check->execute();
        $result_check_software = $post_check->get_result();
        if ($result_check_software->num_rows == 1) {
            $val = $result_check_software->fetch_assoc();
            $ForumTitles = $val['ForumTitles'];
            $ForumTopics = $val['ForumTopics'];
        } else {
            $_SESSION['corsmsg'] = "selected post does not exist";
            header('location: ../profile.php?user=self');
            exit;
        };
        $check_topic = $connects->prepare("SELECT * FROM topics WHERE topicIds = ? AND topicState = 'Publics' AND TopicType = 'all' ;");
        $check_topic->bind_param("s", $ForumTopics);
        $check_topic->execute();
        $result_check_topic = $check_topic->get_result();
        if ($result_check_topic->num_rows == 0) {
            $_SESSION['corsmsg'] = "Cannot remove this selected post.";
            header('location: ../profile.php?user=self');
            exit;
        };
        $New_FoIds = "deleted_" . $FoIds;
        $update_post = $connects->prepare("UPDATE forums SET ForumIds = ?, ForumState = 'Deleted' WHERE ForumIds = ? ;");
        $update_post->bind_param("ss", $New_FoIds, $FoIds);
        $update_post->execute();
        if ($update_post->affected_rows > 0) {
            $_SESSION['corsmsg'] = "removed " . $ForumTitles;
            header('location: ../profile.php?user=self');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to remove " . $ForumTitles . ". " . $update_post->error;
            header('location: ../profile.php?user=self');
            exit;
        }
    };
} else {
    header('location: ../profile.php?user=self');
    exit;
};
?>
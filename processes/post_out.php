<?php
require_once 'database.php';
$errors = array();
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    header ('location: ../index.php');
    exit;
}
if (isset($_POST['submit'])) {
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
    $dates = date('Y/m/d');
    if ($initReq === "comment") {
        $cmids = str_replace("/", "", $dates) . bin2hex(random_bytes(12));
        $fids = $_POST['fids'];
        $cmterTags = $_POST['usrIds'];
        $comment = $_POST['cmtContnt'];
        $stmt_cmtPost = $connects->prepare("INSERT INTO forumcomments (CommentIds, ForumIds, profileTags, profileNames, Comments, CommentDates, CmVs) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt_cmtPost->bind_param("ssssss", $cmids, $fids, $cmterTags, $pfname, $comment, $dates);
        if($stmt_cmtPost->execute()){
            $_SESSION['corsmsg'] = 'comment got posted';
            header ('location: ../TS/forum/forum.php?ids=' . $fids);
            exit;
        }else{
            $_SESSION['corsmsg'] = 'the comment failed to get posted';
            header ('location: ../TS/forum/dashboard.php');
            exit;
        };
        $stmt_cmtPost->close();
    } else if ($initReq === "Post") {
        $Fcreators = $aidis;
        $Ftitles = $_POST['ForumTitles'];
        $Ftopics = $_POST['ForumTopics'];
        $Fdescs = $_POST['ForumDescription'];
        $Fstate = 'Publics';
        $FHighlight = 'FALSE';
        $check_topic = $connects->prepare("SELECT * FROM topics WHERE topicIds = ? AND topicState = 'Publics' AND TopicType = 'All' ;");
        $check_topic->bind_param("s", $Ftopics);
        $check_topic->execute();
        $result_check_topic = $check_topic->get_result();
        if ($result_check_topic->num_rows == 0) {
            $_SESSION['corsmsg'] = "Selected topic does not exist";
            header ('location: ../TS/forum/dashboard.php');
            exit;
        };
        $FoIds = str_replace("/", "", $dates) . bin2hex(random_bytes(12));
        if (isset($_FILES["file"]["name"]) && $_FILES['file']['name'][0] != "") {
            $targetdir = "../TS/img/" . $FoIds . "/";
            if (!file_exists($targetdir)) {
                mkdir($targetdir, 0777, true);
            }
            $Fattach = basename($_FILES["file"]["name"]);
            $tarfilepath = $targetdir . strtolower($Fattach);
            $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
            $allowTypes = array('jpg', 'svg', 'png', 'jpeg', 'webp', 'gif');
            if($_FILES["file"]["size"] < 5242880) {
                if(in_array($fileType, $allowTypes)) {
                    $random = bin2hex(random_bytes(8));
                    $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $Fattach);
                    $createfromformat = DateTime::createFromFormat('Y/m/d', date('Y/m/d'));
                    $unixdate = $createfromformat->getTimestamp();
                    $Fattach = $FoIds . '_' . $unixdate . '_' . $random . '_' . $clean_name;
                    $tempPath = $_FILES["file"]["tmp_name"];
                    $targetPath = $targetdir . $Fattach;
                    if(move_uploaded_file($tempPath, $targetPath)) {
                        chmod($targetPath, 0644);
                        $stmt_frmPost = $connects->prepare("INSERT INTO forums (ForumIds, ForumTitles, ForumCreator, ForumTopics, ForumContents, ForumAttachment, ForumDates, ForumState, ForumHighlight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt_frmPost->bind_param("sssssssss", $FoIds, $Ftitles, $Fcreators, $Ftopics, $Fdescs, $Fattach, $dates, $Fstate, $FHighlight);
                        if($stmt_frmPost->execute()){
                            $_SESSION['corsmsg'] = 'Forum got posted';
                            $stmt_frmPost->close();
                            header ('location: ../TS/forum/forum.php?ids=' . $FoIds);
                            exit;
                        } else {
                            $_SESSION['corsmsg'] = 'The Forum failed to post';
                            $stmt_frmPost->close();
                            header ('location: ../TS/forum/dashboard.php');
                            exit;
                        };
                    } else {
                        $_SESSION['corsmsg'] = 'An error occured when uploading forum attachment';
                        header ('location: ../TS/forum/dashboard.php');
                        exit;
                    };
                } else {
                    $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif format allowed for the forum attachment';
                    header ('location: ../TS/forum/dashboard.php');
                    exit;
                };
            } else {
                $Fattach = "empty.png";
                $stmt_frmPost = $connects->prepare("INSERT INTO forums (ForumIds, ForumTitles, ForumCreator, ForumTopics, ForumContents, ForumAttachment, ForumDates, ForumState,ForumHighlight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_frmPost->bind_param("sssssssss", $FoIds, $Ftitles, $Fcreators, $Ftopics, $Fdescs, $Fattach, $dates, $Fstate, $FHighlight);
                if($stmt_frmPost->execute()){
                    $_SESSION['corsmsg'] = 'Forum got posted, uploaded file exceed 5MB';
                    $stmt_frmPost->close();
                    header ('location: ../TS/forum/forum.php?ids=' . $FoIds);
                    exit;
                } else {
                    $_SESSION['corsmsg'] = 'The Forum failed to post';
                    header ('location: ../TS/forum/dashboard.php');
                    $stmt_frmPost->close();
                    exit;
                };  
            };
        } else { 
            $Fattach = "empty.png";
            $stmt_frmPost = $connects->prepare("INSERT INTO forums (ForumIds, ForumTitles, ForumCreator, ForumTopics, ForumContents, ForumAttachment, ForumDates, ForumState,ForumHighlight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_frmPost->bind_param("sssssssss", $FoIds, $Ftitles, $Fcreators, $Ftopics, $Fdescs, $Fattach, $dates, $Fstate, $FHighlight);
            if($stmt_frmPost->execute()){
                $_SESSION['corsmsg'] = 'Forum got posted';
                $stmt_frmPost->close();
                header ('location: ../TS/forum/forum.php?ids=' . $FoIds);
                exit;
            } else {
                $_SESSION['corsmsg'] = 'The Forum failed to post';
                header ('location: ../TS/forum/dashboard.php');
                $stmt_frmPost->close();
                exit;
            };
        };
    $stmt_frmPost->close();
    };
};
?>
<?php
require_once '../../processes/database.php';
$errors = array();
session_start();
if (isset($_SESSION['profileTags'])) {
    function getRandomWord($len = 40) {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }
    $aidis = $_SESSION['profileTags'];
    if (isset($_POST['submit'])) {
        $initReq = $_POST['submit'];
        $initReq = htmlspecialchars($initReq, ENT_QUOTES, 'UTF-8');
        if ($initReq === "comment") {
            $rnum = random_int(10000000, 9897989798);
            $rword = getRandomWord();
            $cmids = $rnum . $rword;
            $fids = $_POST['fids'];
            $cmterTags = $_POST['usrIds'];
            $commenter = $_POST['cmtUser'];
            $comment = $_POST['cmtContnt'];
            $stmt_cmtPost = $connects->prepare("INSERT INTO forumcomments (CommentIds, ForumIds, profileTags, profileNames, Comments, CommentDates, CmVs) VALUES (?, ?, ?, ?, ?, NOW(), 0)");
            $stmt_cmtPost->bind_param("sssss", $cmids, $fids, $cmterTags, $commenter, $comment);
            if($stmt_cmtPost->execute()){
                $_SESSION['corsmsg'] = 'comment got posted';
                header ('location: ../forum/forum.php?ids=' . $fids);
                exit;
            }else{
                $_SESSION['corsmsg'] = 'the comment failed to get posted';
                header ('location: ../forum/forum.php?ids=' . $fids);
                exit;
            };
            $stmt_cmtPost->close();
        } else if ($initReq === "Post") {
            $rnum = random_int(100000, 989798);
            $rword = getRandomWord();
            $FoIds = $rnum . $rword;
            $Fcreators = $aidis;
            $Ftitles = $_POST['ForumTitles'];
            $Ftopics = $_POST['ForumTopics'];
            $Fdescs = $_POST['ForumDescription'];
            $Fstate = 'Publics';
            $FHighlight = 'NOs';
            if (isset($_FILES["file"]["name"])) {
                $targetdir = "../ArchFiles/";
                $filenames = basename($_FILES["file"]["name"]);
                $Fattach = basename($_FILES["file"]["name"]);
                $tarfilepath = $targetdir . strtolower($filenames);
                $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
                $allowTypes = array('jpg', 'svg', 'png', 'jpeg', 'webp', 'gif');
                if(!empty($_FILES["file"]["name"])) {
                    if(in_array($fileType, $allowTypes)) {
                        if(move_uploaded_file($_FILES["file"]["tmp_name"], $tarfilepath)) {
                            $stmt_frmPost = $connects->prepare("INSERT INTO forums (ForumIds, ForumTitles, ForumCreator, ForumTopics, ForumContents, ForumAttachment, ForumDates, ForumState,ForumHighlight) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
                            $stmt_frmPost->bind_param("ssssssss", $FoIds, $Ftitles, $Fcreators, $Ftopics, $Fdescs, $Fattach, $Fstate, $FHighlight);
                            if($stmt_frmPost->execute()){
                                $_SESSION['corsmsg'] = 'Forum got posted';
                                $stmt_frmPost->close();
                                header ('location: ../forum/forum.php?ids=' . $FoIds);
                                exit;
                            } else {
                                $_SESSION['corsmsg'] = 'The Forum failed to post';
                                $stmt_frmPost->close();
                                header ('location: ../forum/dashboard.php');
                                exit;
                            };
                        } else {
                            $_SESSION['corsmsg'] = 'An error occured when uploading forum attachment';
                            header ('location: ../forum/dashboard.php');
                            exit;
                        };
                    } else {
                        $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif format allowed for the forum attachment';
                        header ('location: ../forum/dashboard.php');
                        exit;
                    };
                } else {
                    $Fattach = "empty.png";
                    $stmt_frmPost = $connects->prepare("INSERT INTO forums (ForumIds, ForumTitles, ForumCreator, ForumTopics, ForumContents, ForumAttachment, ForumDates, ForumState,ForumHighlight) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
                    $stmt_frmPost->bind_param("ssssssss", $FoIds, $Ftitles, $Fcreators, $Ftopics, $Fdescs, $Fattach, $Fstate, $FHighlight);
                    if($stmt_frmPost->execute()){
                        $_SESSION['corsmsg'] = 'Forum got posted';
                        $stmt_frmPost->close();
                        header ('location: ../forum/forum.php?ids=' . $FoIds);
                        exit;
                    } else {
                        $_SESSION['corsmsg'] = 'The Forum failed to post';
                        header ('location: ../forum/dashboard.php');
                        $stmt_frmPost->close();
                        exit;
                    };
                };
            } else {
                $Fattach = "empty.png";
                $stmt_frmPost = $connects->prepare("INSERT INTO forums (ForumIds, ForumTitles, ForumCreator, ForumTopics, ForumContents, ForumAttachment, ForumDates, ForumState,ForumHighlight) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
                $stmt_frmPost->bind_param("ssssssss", $FoIds, $Ftitles, $Fcreators, $Ftopics, $Fdescs, $Fattach, $Fstate, $FHighlight);
                if($stmt_frmPost->execute()){
                    $_SESSION['corsmsg'] = 'Forum got posted';
                    $stmt_frmPost->close();
                    header ('location: ../forum/forum.php?ids=' . $FoIds);
                    exit;
                } else {
                    $_SESSION['corsmsg'] = 'The Forum failed to post';
                    header ('location: ../forum/dashboard.php');
                    $stmt_frmPost->close();
                    exit;
                };
            };
        $stmt_frmPost->close();
        };
    } else {
        header ('location: ../forum/dashboard.php');
        exit;
    };
} else {
    header ('location: ../../index.php');
    exit;
}
?>
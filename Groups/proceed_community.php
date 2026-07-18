<?php
require_once '../processes/database.php';
if (!isset($_POST['lIds']) || !isset($_POST['lc'])) {
    $_SESSION['corsmsg'] = "denied request";
    header ('location: ../index.php');
    exit;
}
if (isset($_POST['submit'])) {
    $allowChanges = false;
    $errors = array();
    $root_route = "../";
    require_once '../secureSession.php';
    require_once 'ReAuth.php';
    if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
        $aidis = $_SESSION['profileTags'];
        $gToken = $_SESSION['GroupsToken'];
        $gids = $_SESSION['gids'];
        $ChangerRoles = $_SESSION['roles'];
        if ($ChangerRoles === "founder" || $ChangerRoles === "administrator") {
            $allowChanges = true;
        }
        if ($allowChanges == false) {
            $_SESSION['corsmsg'] = "Unpermited access";
            header ('location: manage.php');
            exit;
        }
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    $libsIds = $_POST['lIds'];
    $topicIds = $_POST['lc'];
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
    $prebind = '"' . $aidis . '"';
    $check_orgs = $connects->prepare("SELECT names, about, founded, founder, admins, members, logo, banner, role_publish FROM ogroup WHERE identification = ? AND founder = ? OR JSON_CONTAINS(members, ?);");
    $check_orgs->bind_param("sss", $gids, $aidis, $prebind);
    $check_orgs->execute();
    $result_check_orgs = $check_orgs->get_result();
    if ($result_check_orgs->num_rows > 0) {
        while ($value = $result_check_orgs->fetch_assoc()) {
            $Ognames = $value['names'];
            $about = $value['about'];
            $founder = $value['founder'];
            $founded = $value['founded'];
            $admins = $value['admins'];
            $members = $value['members'];
            $logo = $value['logo'];
            $banner = $value['banner'];
            $stmt_check_software = $connects->prepare("SELECT libsIds FROM libslist WHERE libsPublisher = ? AND libsIds = ? AND libsForum = ? AND libsState = 'Publics' ;");
            $stmt_check_software->bind_param("sss", $gids, $libsIds, $topicIds);
            $stmt_check_software->execute();
            $result_check_software = $stmt_check_software->get_result();
            if ($result_check_software->num_rows == 0) {
                $_SESSION['corsmsg'] = "Required keys does not match. " . $result_check_software->error;
                header('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
                exit;
            }
        }
    } else {
        $_SESSION['corsmsg'] = "You are not allowed to access this page. " . $result_check_orgs->error;
        header('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
        exit;
    };
    $check_topic = $connects->prepare("SELECT * FROM topics WHERE topicIds = ? AND topicState = 'Publics' AND TopicType = 'publisherOnly' ;");
    $check_topic->bind_param("s", $topicIds);
    $check_topic->execute();
    $result_check_topic = $check_topic->get_result();
    if ($result_check_topic->num_rows == 0) {
        $_SESSION['corsmsg'] = "Selected topic does not exist. " . $result_check_topic->error;
        header('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
        exit;
    };
    function getRandomWord($len = 40) {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }
    $initReq = $_POST['submit'];
    $initReq = htmlspecialchars($initReq, ENT_QUOTES, 'UTF-8');
    if ($initReq === "Post") {
        $Fcreators = $aidis;
        $Ftitles = $_POST['ForumTitles'];
        $Ftopics = $_POST['ForumTopics'];
        $Fdescs = $_POST['ForumDescription'];
        $Fstate = 'Publics';
        $FHighlight = 'NOs';
        $dates = date('d/m/Y');
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
                    $Fattach = $unixdate . '_' . $random . '_' . $clean_name;
                    $tempPath = $_FILES["file"]["tmp_name"];
                    $targetPath = $targetdir . $Fattach;
                    if(move_uploaded_file($tempPath, $targetPath)) {
                        chmod($targetPath, 0644);
                        $stmt_frmPost = $connects->prepare("INSERT INTO forums (ForumIds, ForumTitles, ForumCreator, ForumTopics, ForumContents, ForumAttachment, ForumDates, ForumState,ForumHighlight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt_frmPost->bind_param("sssssssss", $FoIds, $Ftitles, $Fcreators, $Ftopics, $Fdescs, $Fattach, $dates, $Fstate, $FHighlight);
                        if($stmt_frmPost->execute()){
                            $_SESSION['corsmsg'] = 'Forum got posted';
                            $stmt_frmPost->close();
                            header ('location: ../TS/forum/forum.php?ids=' . $FoIds);
                            exit;
                        } else {
                            $_SESSION['corsmsg'] = 'The Forum failed to post. ' . $stmt_frmPost->error;
                            $stmt_frmPost->close();
                            header ('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
                            exit;
                        };
                    } else {
                        $_SESSION['corsmsg'] = 'An error occured when uploading forum attachment';
                        header ('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
                        exit;
                    };
                } else {
                    $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif format allowed for the forum attachment';
                    header ('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
                    exit;
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
                    $_SESSION['corsmsg'] = 'The Forum failed to post. ' . $stmt_frmPost->error;
                    header ('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
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
                header ('location: ../TS/forum/forum.php?ids=' . $FoIds);
                exit;
            } else {
                $_SESSION['corsmsg'] = 'The Forum failed to post. ' . $stmt_frmPost->error;
                header ('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
                $stmt_frmPost->close();
                exit;
            };
        };
    $stmt_frmPost->close();
    } else if ($initReq === "REMOVE") {
        $FoIds = $_POST['foids'];
        $post_check = $connects->prepare("SELECT ForumTitles FROM forums WHERE ForumIds = ? AND forumState = 'Publics';");
        $post_check->bind_param("s", $FoIds);
        $post_check->execute();
        $result_check_software = $post_check->get_result();
        if ($result_check_software->num_rows == 1) {
            $val = $result_check_software->fetch_assoc();
            $ForumTitles = $val['ForumTitles'];
        } else {
            $_SESSION['corsmsg'] = "selected post does not exist";
            header('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
            exit;
        };
        $New_FoIds = "deleted_" . $FoIds;
        $update_post = $connects->prepare("UPDATE forums SET ForumIds = ?, ForumState = 'Deleted' WHERE ForumIds = ? ;");
        $update_post->bind_param("ss", $New_FoIds, $FoIds);
        $update_post->execute();
        if ($update_post->affected_rows > 0) {
            $_SESSION['corsmsg'] = "removed " . $ForumTitles;
            header ('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to remove " . $ForumTitles . ". " . $update_post->error;
            header ('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
            exit;
        }
    };
} else {
    header ('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
    exit;
};
?>
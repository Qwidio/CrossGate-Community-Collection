<?php
require_once '../../processes/database.php';
$errors = array();
if (!isset($_GET['ids'])) {
    header ('location: dashboard.php');
    exit;
}
$fids = $_GET['ids'];
$fids = htmlspecialchars($fids, ENT_QUOTES, 'UTF-8');
$root_route = "../../";
$_SESSION['prev_loc'] = "TS/forum/forum.php?ids=" . $fids;
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    require_once '../../secureSession.php';
};
$noComment = false;
$commentArr = array();
$requestedItem = "empty";
if (isset($_GET['filter'])) {
    $FilterReq = $_GET['filter']; 
} else {
    $FilterReq = "none";
}
$stmt_check_forums = $connects->prepare("SELECT * FROM forums WHERE ForumState = 'Publics' AND ForumIds = ? ;");
$stmt_check_forums->bind_param("s", $fids);
$stmt_check_forums->execute();
$result_check_forums = $stmt_check_forums->get_result();
if ($result_check_forums->num_rows == 1) {
    $value = $result_check_forums->fetch_assoc();
    $creators = $value['ForumCreator'];
    $titles = $value['ForumTitles'];
    $relatedTopic = $value['ForumTopics'];
    $dates = $value['ForumDates'];
    $descs = $value['ForumContents'];
    $attachs = $value['ForumAttachment'];
    $ForumHighlight = $value['ForumHighlight'];
} else {
    $_SESSION['corsmsg'] = "The forum you try to open does not exist";
    header('location: dashboard.php');
    exit;
};
if (isset($_GET['cids'])) {
    $targetIds = $_GET['cids'];
    if($targetIds != "") {
        $targetIds = htmlspecialchars($targetIds, ENT_QUOTES, 'UTF-8');
        $_SESSION['prev_loc'] = "TS/forum/forum.php?filter=" . $FilterReq . "&ids=" . $fids . "&cids=" . $targetIds;
        $searchTarget = '%' . $targetIds . '%';
    } else {
        $FilterReq = "none";
        $targetIds = null;
    }
}
switch ($FilterReq) {
    case 'none':
        $stmt_check_comments = $connects->prepare("SELECT * FROM forumcomments WHERE ForumIds = ? ORDER BY CommentDates DESC;");
        $stmt_check_comments->bind_param("s", $fids);
        break;
    case 'oldtonew':
        $stmt_check_comments = $connects->prepare("SELECT * FROM forumcomments WHERE ForumIds = ? ORDER BY CommentDates ASC;");
        $stmt_check_comments->bind_param("s", $fids);
        break;
    case 'search':
        if($targetIds === "empty") {
        $stmt_check_comments = $connects->prepare("SELECT * FROM forumcomments WHERE ForumIds = ? ORDER BY CommentDates DESC;");
        $stmt_check_comments->bind_param("s", $fids);
        } else {
        $stmt_check_comments = $connects->prepare("SELECT * FROM forumcomments WHERE ForumIds = ? AND CommentIds = ? ORDER BY CommentDates DESC;");
        $stmt_check_comments->bind_param("ss", $fids, $searchTarget);
        }
        break;
    default:
        $_SESSION['corsmsg'] = "Unknown filter";
        header ('location: dashboard.php');
        exit;
        break;
}
$stmt_check_comments->execute();
$result_check_comments = $stmt_check_comments->get_result();
if ($result_check_comments->num_rows > 0) {
    while ($value = $result_check_comments->fetch_assoc()) {
        if (!in_array($value['CommentIds'], $commentArr)) {
            $commentArr[$value['CommentIds']] = [
                "CommentIds"    => $value['CommentIds'],
                "profileTags"   => $value['profileTags'],
                "profileNames"  => $value['profileNames'],
                "Comments"      => $value['Comments'],
                "CommentDates"  => $value['CommentDates']
            ];
        }
    }
} else {
    $noComment = true;
}

$noTopic = false;
$topicArr = array();
$stmt_check_topic = $connects->prepare("SELECT * FROM topics WHERE topicState = 'Publics';");
$stmt_check_topic->execute();
$result_check_topic = $stmt_check_topic->get_result();
if ($result_check_topic->num_rows > 0) {
    while ($value = $result_check_topic->fetch_assoc()) {
        if (!in_array($value['topicIds'], $topicArr)) {
            $topicArr[$value['topicIds']] = [
                "topicTitles" => $value['topicTitles'],
                "topicType"   => $value['topicType']
            ];
        }
    }
} else {
    $noTopic = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styling/pallate.css">
    <link rel="stylesheet" href="../../styling/Mindex.css">
    <link rel="stylesheet" href="../../styling/footer.css">
    <title><?php echo $titles;?></title>
</head>
<body>
<!-- the nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="../../img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="../../index.php" class="link-cover">.</a>
        </div>
        <div class="posr w60p flex gap-s">
            <?php
            if (isset($aidis)) {
                ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="../../Library/core/markout.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="../../profile.php?user=self" class="link-cover">.</a>
            </div>
            <?php
            }
            ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">TOPIC</h2>
                <a href="topic.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../../documentation/docs.php" class="link-cover">.</a>
            </div>
        </div>
        <?php
        if (!isset($aidis)) {
        ?>
        <div class="leftMg flex acjc gap10">
            <p class="posr pad-n-s pad-s-v txtc txt-n bg-1 border-1 bora-s border-hover-white">LOGIN
                <a href="../../connect_it/connect_it.php?state=login" class="link-cover">.</a>
            </p>
        </div>
        <?php
        };
        ?>
    </div>
<div class="posr bottomMg-s10 w100p maxw100 flex">
<!-- topic on right of the page -->
    <div class="posr pad-s w20p flex fld gap10 border-r z2">
        <div class="pad-n-s w100p flex border-b">
            <a href="dashboard.php" class="pad-s-v txt-n semibold txtnowrap c-orange hover-text-blue">< Forum /</a>
            <p class="pad-s-v pad-sl txt-n semibold txtnowrap ovh">Forum Post / <?php echo $titles;?></p>
        </div>
        <?php
        if ($noTopic == false) {
            foreach ($topicArr as $topicIndex => $value) {
                $ids = $topicIndex;
                $topicType = $value['topicType'];
                if ($topicType === "publisherOnly" && $ids === $relatedTopic) {
                    $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, libsTitles, libsDesc FROM libslist WHERE libsForum = ? AND libsState = 'publics';");
                    $check_software->bind_param("s", $relatedTopic);
                    $check_software->execute();
                    $result_check_software = $check_software->get_result();
                    if ($result_check_software->num_rows > 0) {
        ?>
        <div class="posr pad-n-s pad-st pad-nb w100p maxh30 flex border-b">
        <?php
                        $value = $result_check_software->fetch_assoc();
                        $libsIds = $value['libsIds'];
                        $libsPublisher = $value['libsPublisher'];
                        $libsAttachs = $value['libsAttachs'];
                        $libsTitles = $value['libsTitles'];
                        $libsDesc = $value['libsDesc'];
                        if (strlen($libsDesc) > 110) {
                            $libsDesc = substr_replace($libsDesc, '...', 100);
                        }
            ?>
            <img src="../../Library/libsImg/<?php echo $libsPublisher . "/" . $libsAttachs;?>" alt="" class="posr vertiMg r1-1  w20p flex acjc bg-half-white containfit bora-s z4">
            <div class="posr pad-sl w80p maxh10 flex fld">
                <h2 class="txt-n semibold"><?php echo $libsTitles;?></h2>
                <h2 class="txt-ms w100p ovh"><?php echo $libsDesc;?></h2>
            </div>
            <a href="../../Library/core/view.php?type=clts&ids=<?php echo $libsIds;?>" class="link-cover hover-white">.</a>
        </div>
        <?php
                    } else {
            ?>
            <p class="autoMg txtc">Failed to get Collection info</p>
        </div>
        <?php
                    };
                };
            };
        };
        ?>
        <div class="pad-n-s pad-m-v w100p maxh30 flex fld border-b ovh-s">
            <a href="topic.php" class="pad-sb w100p txt-n semibold points">Discussion Topic</a>
            <?php
            if ($noTopic == false) {
                foreach ($topicArr as $topicIndex => $value) {
                    $ids = $topicIndex;
                    $topicTitles = $value['topicTitles'];
                    $topicType = $value['topicType'];
                    if ($topicType === "all") {
            ?>
            <div class="posr pad-s-s pad-r pad-sb w100p flex fld">
                <h2 class="w100p txt-s ovh"><?php echo $topicTitles;?></h2>
                <a href="viewtopic.php?topicIds=<?php echo $ids;?>" class="link-cover">.</a>
            </div>
            <?php
                    };
                };
            } else {
            ?>
            <div class="posr pad-s-s pad-r pad-sb w100p flex fld">
                <h2 class="w100p txt-s">Error retrieving</h2>
                <a href="#" class="link-cover">.</a>
            </div>
            <?php
            };
            ?>
        </div>
        <div class="pad-n-s pad-m-v w100p maxh30 flex fld border-b ovh-s">
            <a href="topic.php" class="pad-sb w100p txt-n semibold points">Collection Topic</a>
            <?php
            if ($noTopic == false) {
                foreach ($topicArr as $topicIndex => $value) {
                    $ids = $topicIndex;
                    $topicTitles = $value['topicTitles'];
                    $topicType = $value['topicType'];
                    if ($topicType === "publisherOnly") {
            ?>
            <div class="posr pad-s-s pad-r pad-sb w100p flex fld">
                <h2 class="w100p txt-s ovh"><?php echo $topicTitles;?></h2>
                <a href="viewtopic.php?topicIds=<?php echo $ids;?>" class="link-cover">.</a>
            </div>
            <?php
                    };
                };
            };
            ?>
        </div>
        <div class="topMg minh10"></div>
    </div>
    <?php include_once '../../reportTab.php';
    if (isset($aidis) && $ForumHighlight === "FALSE") {
    ?>
    <div class="posf pad-n b0 r0 flex z999">
        <img src="../../img/warning.svg" alt="" class="posr icon-t containfit bg-half-white opacity3 hover-visible points" onclick="uniDisplaySwitch('reportDialog'); uniLoad(this, 'reportForm');" data-reportsource="forums" data-ids="<?php echo $fids;?>">
    </div>
    <?php
    }
    ?>
<!-- forum content -->
    <div class="pad-n w80p minh100 flex fld">
        <h1 class="sideMg bottomMg-s5 w95p txt-b"><?php echo $titles;?></h1>
        <?php
        $getUser = $connects->prepare("SELECT profileNames FROM profiles WHERE profileTags = ?");
        $getUser->bind_param("s", $creators);
        $getUser->execute();
        $resultGetUser = $getUser->get_result();
        if ($resultGetUser->num_rows == 1) {
            $getname = $resultGetUser->fetch_assoc();
        ?>
            <a href="../../profile.php?user=<?php echo $creators; ?>" class="sideMg w95p txt-s"><?php echo $getname['profileNames'];?> | <?php echo $dates; ?></a>
        <?php
        };
        ?>
        <?php
        if ($attachs != "empty.png" && isset($attachs)) {
        ?>
        <h2 class="sideMg topMg-s10 w95p"><?php echo $descs;?></h2>
        <img src="../ArchFiles/<?php echo $attachs;?>" alt="<?php echo $titles;?>" class="sideMg topMg-s10 w50p r16-9 containfit">
        <?php
        } else {
        ?>
        <p class="sideMg topMg-s10 w95p minh30"><?php echo $descs;?></p>
        <?php
        };
        if (isset($aidis)) {
        ?>
        <form action="../component/post_out.php" method="post" class="posr topMg-s10 pad-s-v w95p sideMg flex border-b border-t gap-s">
            <input type="text" name="fids" value="<?php echo $fids;?>" required hidden>
            <input type="text" name="usrIds" value="<?php echo $aidis;?>" required hidden>
            <input class="pad-n-s pad-m-v w88 bg-transparent c-white bora-s" type="text" name="cmtContnt" placeholder="Leave a reply..." auto-complete="off" maxlength="2000" required>
            <input class="w10p bg-half-gray c-white bora-s hover-white trs500ms " type="submit" name="submit" value="comment">
        </form>
        <?php
        } else {
        ?>
        <div class="posr topMg-s10 pad-s-v w95p sideMg flex border-b border-t gap-s">
            <h2 class="w100p txtc txt-n">Login to comment</h2>
        </div>
        <?php
        };
        ?>
        <div class="posr sideMg topMg-s10 w95p flex fld gap10">
        <?php
        if ($noComment == false) {
            foreach ($commentArr as $value) {
                $Cids = $value['CommentIds'];
                $Tags = $value['profileTags'];
                $Names = $value['profileNames'];
                $Comments = $value['Comments'];
                $Cdates = $value['CommentDates'];
        ?>
            <div class="posr pad-s-v pad-m-s flex fld bg-half-gray box-shad-black-1 border-purple bora-s gap5">
                <div class="posr flex gap5">
                    <a href="../../profile.php?user=<?php echo $Tags;?>"><?php echo $Names;?></a>
                    <span>|</span>
                    <p><?php echo $Cdates;?></p>
                </div>
                <p class="comment-content"><?php echo $Comments;?></p>
            </div>
        <?php
            };
        }else{
        ?>
                <h2 class="posr pad-s w100p h20 txtc z2">be the first one to replied</h2>
        <?php
        };
        ?>
        </div>
    </div>
</div>
<!-- messages alerter --> 
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <?php include_once '../../extra/footer.php';?>
    <script src="../../scriptstuff/script.js"></script>
    <script src="../../scriptstuff/alert.js"></script>
    <?php
    if (!empty($errors)) {
        echo "<script> ";
        echo "alerter('"; foreach ($errors as $error) {echo $error .";";} echo "')";
        echo "</script>";
    };
    if (!empty($_SESSION['corsmsg'])) {
        $corsmsg = $_SESSION['corsmsg'];
        echo "<script> ";
        echo "alerter('" . $corsmsg . "')";
        echo "</script>";
        $_SESSION['corsmsg'] = "";
    };
    ?>
</body>
</html>
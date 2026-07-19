<?php
require_once '../../processes/database.php';
$_SESSION['prev_loc'] = "TS/forum/dashboard.php";
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    $root_route = "../../";
    require_once '../../secureSession.php';
};

$noForum = false;
$noTopic = false;
$forumArr = array();
$HighlightforumArr = array();
$topicArr = array();
if (isset($_GET['filter'])) {
    $FilterReq = $_GET['filter']; 
} else {
    $FilterReq = "none";
}
if (isset($_GET['ids'])) {
    $targetIds = $_GET['ids'];
    if($targetIds != "") {
        $targetIds = htmlspecialchars($targetIds, ENT_QUOTES, 'UTF-8');
        $_SESSION['prev_loc'] = "TS/forum/dashboard.php?filter=" . $FilterReq . "&ids=" . $targetIds;
        $searchTarget = '%' . $targetIds . '%';
    } else {
        $FilterReq = "none";
        $targetIds = null;
    }
}
switch ($FilterReq) {
    case 'none':
    $check_Forum = $connects->prepare("SELECT * FROM forums WHERE ForumState = 'Publics' ORDER BY ForumDates DESC;");
        break;
    case 'oldtonew':
    $check_Forum = $connects->prepare("SELECT * FROM forums WHERE ForumState = 'Publics' ORDER BY ForumDates ASC;");
        break;
    case 'search':
        if($targetIds === "empty") {
    $check_Forum = $connects->prepare("SELECT * FROM forums WHERE ForumState = 'Publics' ORDER BY ForumDates DESC;");
        } else {
            $check_Forum = $connects->prepare("SELECT * FROM forums WHERE ForumState = 'Publics' AND ForumTitles LIKE ? ORDER BY ForumDates DESC;");
            $check_Forum->bind_param("s", $searchTarget);
        }
        break;
    default:
        $_SESSION['corsmsg'] = "Unknown filter";
        header ('location: dashboard.php');
        exit;
        break;
}
$check_Forum->execute();
$result_check_Forum = $check_Forum->get_result();
if ($result_check_Forum->num_rows > 0) {
    while ($value = $result_check_Forum->fetch_assoc()) {
        if ($value['ForumHighlight'] === "FALSE") {
            $forumArr[$value['ForumIds']] = [
                "ForumIds"      => $value['ForumIds'],
                "ForumCreator"  => $value['ForumCreator'],
                "ForumTitles"   => $value['ForumTitles'],
                "ForumTopics"   => $value['ForumTopics'],
                "ForumDates"    => $value['ForumDates'],
                "ForumContents" => $value['ForumContents']
            ];
        } else {
            $HighlightforumArr[$value['ForumIds']] = [
                "ForumIds"      => $value['ForumIds'],
                "ForumCreator"  => $value['ForumCreator'],
                "ForumTitles"   => $value['ForumTitles'],
                "ForumTopics"   => $value['ForumTopics'],
                "ForumDates"    => $value['ForumDates'],
                "ForumContents" => $value['ForumContents']
            ];
        }
    }
} else {
    $noForum = true;
}
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
    <link rel="shortcut icon" href="../../img/cgcclogotrsp.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styling/pallate.css">
    <link rel="stylesheet" href="../../styling/Mindex.css">
    <link rel="stylesheet" href="../../styling/footer.css">
    <title>Dashboard</title>
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
            <form id="SearchBar" class="posr vertiMg flex gap5 trs500ms bg-white border-1 bora-s" action="dashboard.php">
                <input type="text" name="ids" placeholder="search forum..." id="searchbox" class="pad-s-s bg-transparent c-black border-none" tabindex="1">
                <button type="submit" name="filter" value="search" class="posr vertiMg pad-s flex bg-transparent c-black border-none" tabindex="2"><img src="../../img/search.png" alt="" class="icon-rs h100p containfit points"></button>
            </form>
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
<div class="posr bottomMg-s10 w100p flex">
<!-- right part of the page -->
    <div class="posr pad-s w20p flex fld gap10 border-r z2">
        <?php
        if (isset($aidis)) {
        ?>
        <div class="posr pad-n-s pad-s-v w100p flex fld border-b">
            <button onclick="uniDisplaySwitch('postForumDialog');" class="posr pad-s w100p txtc txt-s bg-gold c-black border-1 border-hover-white hover-text-blue points">Post New Forum</button>
        </div>
        <?php
        };
        ?>
        <div class="posr pad-n-s pad-m-v w100p maxh40 flex fld border-b ovh-s">
            <a href="topic.php" class="pad-sb w100p txt-n semibold points">Discussion Topic</a>
            <?php
            if ($noTopic == false) {
                foreach ($topicArr as $topicIndex => $value) {
                    $ids = $topicIndex;
                    $titles = $value['topicTitles'];
                    $topicType = $value['topicType'];
                    if ($topicType === "all") {
            ?>
            <div class="posr pad-s-s pad-r pad-sb w100p flex fld">
                <h2 class="w100p txt-s ovh"><?php echo $titles;?></h2>
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
        <div class="pad-n-s pad-m-v w100p maxh40 flex fld border-b ovh-s">
            <a href="topic.php" class="pad-sb w100p txt-n semibold points">Collection Topic</a>
            <?php
            if ($noTopic == false) {
                foreach ($topicArr as $topicIndex => $value) {
                    $ids = $topicIndex;
                    $titles = $value['topicTitles'];
                    $topicType = $value['topicType'];
                    if ($topicType === "publisherOnly") {
            ?>
            <div class="posr pad-s-s pad-r pad-sb w100p flex fld">
                <h2 class="w100p txt-s ovh"><?php echo $titles;?></h2>
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
    </div>
<!-- Highlighted post -->
    <div class="posr pad-n-v w80p minh100 flex fld gap10 ovh-s">
    <?php
    if ($noForum == false) {
        foreach ($HighlightforumArr as $value) {
            $Hids = $value['ForumIds'];
            $Hcreators = $value['ForumCreator'];
            $Htitles = $value['ForumTitles'];
            $Htopics = $value['ForumTopics'];
            $Hdates = $value['ForumDates'];
            $Hcontents = $value['ForumContents'];
        ?>
        <div class="posr sideMg pad-s-v pad-n-s w95p flex fld bg-def-1 border-1 bora-s gap5 z2">
            <h2 class="posr sideMg w100p txt-b"><?php echo $Htitles;?></h2>
            <div class="posr w100p flex gap10">
            <?php
            $getUser = $connects->prepare("SELECT profileNames FROM profiles WHERE profileTags = ?");
            $getUser->bind_param("s", $Hcreators);
            $getUser->execute();
            $resultGetUser = $getUser->get_result();
            if ($resultGetUser->num_rows == 1) {
                $getname = $resultGetUser->fetch_assoc();
            ?>
                <p class="txt-s"><?php echo $getname['profileNames'];?> |</p>
            <?php
            };
            ?>
                <p class="txt-s"><?php echo $Hdates;?></p>
            </div>
            <p class="pad-m-v maxh20 txt-s ovh"><?php echo $Hcontents;?></p>
            <a href="forum.php?ids=<?php echo $Hids;?>" class="link-cover hover-white">.</a>
        </div>
    <?php
        };
    };
    ?>
    <!-- </div> -->
<!-- forum there -->
    <!-- <div class="leftMg-s10 rightMg-s10 w50p minh100 flex wrap gap10 z1"> -->
    <?php
    if ($noForum == false) {
        foreach ($forumArr as $value) {
            $ids = $value['ForumIds'];
            $creators = $value['ForumCreator'];
            $titles = $value['ForumTitles'];
            $topics = $value['ForumTopics'];
            $dates = $value['ForumDates'];
            $contents = $value['ForumContents'];
    ?>
        <div class="posr sideMg pad-s-v pad-n-s w95p flex fld border-1 bora-s gap5 z2">
        <!-- <div class="posr pad-s-v pad-n-s w100p h40 flex fld border-1 bora-s z2"> -->
            <h2 class="posr txt-b"><?php echo $titles;?></h2>
            <div class="posr w100p flex gap10">
            <?php
            $getUser = $connects->prepare("SELECT profileNames FROM profiles WHERE profileTags = ?");
            $getUser->bind_param("s", $creators);
            $getUser->execute();
            $resultGetUser = $getUser->get_result();
            if ($resultGetUser->num_rows == 1) {
                $getname = $resultGetUser->fetch_assoc();
            ?>
                <p class="txt-s"><?php echo $getname['profileNames'];?> |</p>
            <?php
            };
            ?>
                <p class="txt-s"><?php echo $dates;?></p>
            </div>
            <p class="posr pad-m-v maxh20 txt-s ovh"><?php echo $contents;?>
            </p>
            <a href="forum.php?ids=<?php echo $ids;?>" class="link-cover hover-white">.</a>
        </div>
    <?php
        };
    } else {
    ?>
        <p class="posr pad-s-v pad-n-s w100p h100p flex fld acjc z2">No forum found</p>
    <?php
    };
    ?>
    </div>
</div>
<!-- forum create dialog -->
    <dialog id="postForumDialog" class="posf c0 wh100p dp-none fld acjc bg-half-gray ovh-s z999">
        <div class="posr w100p flexblurbg flex"><h2 class="posr rightMg pad-s txt-b">Make New Forum</h2><p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('postForumDialog')">X</p></div>
        <form class="posr wh100p flexblurbg flex" action="../../processes/post_out.php" method="post" enctype="multipart/form-data">
            <div class="posr autoMg h50p r16-9 flex fld acjc gap5">
                <img id="prevs" class="posr sideMg wh100p containfit bg-half-white">
                <input class="posa c0 wh100p txtc c-black" type="file" name="file" accept="image/*" onchange="uniLoadFile(event, 'prevs')">
            </div>
            <div class="vertiMg w50p flex fld gap5">
                <div class="sideMg w88p flex fld">
                    <label for="ForumTitles">Forum Titles</label>
                    <input type="text" name="ForumTitles" class="inptxt" placeholder="Make title for the forum" auto-complete="off" maxlength="500" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="ForumDescription">Forum Description</label>
                <textarea class="inptxt h10 border-b ovh-s" type="text" id="ForumDescription" name="ForumDescription" placeholder="The description for the why or what start this forum " autocomplete="off" required></textarea>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="ForumTopics">Topic</label>
                    <select name="ForumTopics" class="inpselect" required>
                        <option value="" selected disabled>Select Topic</option>
                        <?php
                        if ($noTopic == false) {
                            foreach ($topicArr as $topicIndex => $value) {
                                $ids = $topicIndex;
                                $titles = $value['topicTitles'];
                                $topicType = $value['topicType'];
                                if ($topicType === "all") {
                                    echo "<option name='ForumTopics' value='$ids' required>$titles</option>";
                                };
                            };
                        };
                        ?>
                    </select>
                </div>
                <div class="sideMg w88p flex fld">
                    <input class="pad-s txtc txt-s bg-gold c-black border-1 border-hover-white hover-text-blue points" type="submit" name="submit" value="Post">
                </div>
            </div>
        </form>
    </dialog>
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
    }
    if (!empty($_SESSION['corsmsg'])) {
        $corsmsg = $_SESSION['corsmsg'];
        echo "<script> ";
        echo "alerter('" . $corsmsg . "')";
        echo "</script>";
        $_SESSION['corsmsg'] = "";
    }
    ?>
</body>
</html>
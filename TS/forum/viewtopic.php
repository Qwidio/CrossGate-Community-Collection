<?php
require_once '../../processes/database.php';
$errors = array();
if (!isset($_GET['topicIds'])) {
    header ('location: dashboard.php');
    exit;
}
$topicIds = $_GET['topicIds'];
$_SESSION['prev_loc'] = "TS/forum/viewtopic.php?topicIds=" . $topicIds;
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    $root_route = "../../";
    require_once '../../secureSession.php';
};
$topicIds = htmlspecialchars($topicIds, ENT_QUOTES, 'UTF-8');
$includeCollection = false;
if (isset($_GET['item']) && isset($_GET['onsearch'])) {
    $searchTrigger = $_GET['onsearch'];
    $requestedItem = $_GET['item'];
} else {
    $requestedItem = "empty";
};
$requestedItem = htmlspecialchars($requestedItem, ENT_QUOTES, 'UTF-8');
$stmt_check_Topic = $connects->prepare("SELECT * FROM topics WHERE TopicIds = ?;");
$stmt_check_Topic->bind_param("s", $topicIds);
$stmt_check_Topic->execute();
$result_check_Topic = $stmt_check_Topic->get_result();
if ($result_check_Topic->num_rows == 1) {
    $value = $result_check_Topic->fetch_assoc();
    $TopicIds = $value['topicIds'];
    $Ttitles = $value['topicTitles'];
    $dates = $value['topicDates'];
    $descs = $value['topicContents'];
    $attachs = $value['topicAttachs'];
    $topicType = $value['topicType'];
    if ($topicType === "publisher") {
        $includeCollection = true;
    }
} else {
    $_SESSION['corsmsg'] = "the topic you tried to open does not exists";
    header ('location: topic.php');
    exit;
};
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
    <title><?php echo $Ttitles;?></title>
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
                <a href="markout.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="../../profile.php?user=self" class="link-cover">.</a>
            </div>
            <?php
            }
            ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="../../TS/forum/dashboard.php" class="link-cover">.</a>
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
<!-- topic container -->
    <div class="w100p minh100 flex fld">
        <div class="posr w100p r4-1 flex fld acjc bg-3 border-1">
            <h2 class="w100p txtc txt-30 bold"><?php echo $Ttitles;?></h2>
            <p class="w100p txtc txt-s"><?php echo $descs;?></p>
        </div>
        <div class="sideMg bottomMg w95p minh50 flex wrap gap-s acjc z4">
        <?php
        if (isset($requestedItem) && isset($searchTrigger)) {
        $stmt_check_forumtopics = $connects->prepare("SELECT * FROM forums WHERE ForumTopics = ? AND ForumState = 'Publics' AND ForumTitles LIKE '%$requestedItem%' ORDER BY ForumDates DESC;");
        $stmt_check_forumtopics->bind_param("s", $TopicIds);
        } else {
        $stmt_check_forumtopics = $connects->prepare("SELECT * FROM forums WHERE ForumTopics = ? AND ForumState = 'Publics' ORDER BY ForumDates DESC;");
        $stmt_check_forumtopics->bind_param("s", $TopicIds);
        };
        $stmt_check_forumtopics->execute();
        $result_check_forumtopics = $stmt_check_forumtopics->get_result();
        if ($result_check_forumtopics->num_rows > 0) {
            $uniqueItem = [];
            while ($value = $result_check_forumtopics->fetch_assoc()) {
                $ids= $value['ForumIds'];
                $creators = $value['ForumCreator'];
                $titles = $value['ForumTitles'];
                $topics = $value['ForumTopics'];
                $dates = $value['ForumDates'];
                $contents = $value['ForumContents'];
                $attachs = $value['ForumAttachment'];
                if (!in_array($ids, $uniqueItem)) {
        ?>
        <div class="posr pad-s w20p r16-9 flex fld border-1 gap5">
        <?php
                    if ($attachs != "empty.png" && isset($attachs)) {
        ?>
            <img src="ArchFiles/<?php echo $attachs;?>" alt="" class="posa c0 coverfit">
        <?php
                    };
        ?>
            <h2 class="txt-n"><?php echo $titles;?></h2>
            <div class="bottomMg-s5 w100p flex space-between">
                <p class="txt-s"><?php echo $creators;?></p>
                <p class="txt-s"><?php echo $dates;?></p>
            </div>
            <p class="maxh10 txt-s ovh z3"><?php echo $contents;?></p>
            <a href="forum.php?ids=<?php echo $ids;?>" class="link-cover hover-white">.</a>
        </div>
        <?php
                };
            };
        } else {
        ?>
                <h2 class="posr pad-s w30p h40 txtc z2">no forum for this topic yet</h2>
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
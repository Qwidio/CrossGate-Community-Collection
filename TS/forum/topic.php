<?php
require_once '../../processes/database.php';
$errors = array();
$_SESSION['prev_loc'] = "TS/forum/topic.php";
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    $root_route = "../../";
    require_once '../../secureSession.php';
};
if (isset($_GET['item']) && isset($_GET['onsearch'])) {
    $searchTrigger = $_GET['onsearch'];
    $requestedItem = $_GET['item'];
} else {
    $requestedItem = "empty";
};
$requestedItem = htmlspecialchars($requestedItem, ENT_QUOTES, 'UTF-8');
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
    <title>Topics</title>
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
                <a href="../../Library/coremarkout.php" class="link-cover">.</a>
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
    <!-- the list goes on -->
    <div class="posr w100p r4-1 flex fld acjc bg-3 border-1">
        <h2 class="w100p txtc txt-30 bold">TOPIC</h2>
        <p class="w100p txtc txt-s">When there's nothing to talk about</p>
    </div>
    <section class="topMg-5 bottomMg-5 w100p flex wrap acjc gap">
        <?php
        if (isset($requestedItem) && isset($searchTrigger)) {
        $stmt_check_topic = $connects->prepare("SELECT topicIds, topicTitles, topicDates, topicContents, topicAttachs FROM topics WHERE topicState = 'Publics' AND topicTitles LIKE '%$requestedItem%' ORDER BY topicDates DESC;");
        } else {
        $stmt_check_topic = $connects->prepare("SELECT topicIds, topicTitles, topicDates, topicContents, topicAttachs FROM topics WHERE topicState = 'Publics' ;");
        };
        $stmt_check_topic->execute();
        $result_check_topic = $stmt_check_topic->get_result();
        if ($result_check_topic->num_rows > 0) {
            $uniqueItem = [];
            while ($value = $result_check_topic->fetch_assoc()) {
                $ids = $value['topicIds'];
                $titles = $value['topicTitles'];
                $dates = $value['topicDates'];
                $contents = $value['topicContents'];
                $attachs = $value['topicAttachs'];
                if (!in_array($ids, $uniqueItem)) {
        ?>
        <div class="posr pad-s w20p r16-9 flex fld border-1 gap5">
        <?php
                    if ($attachs != "empty.png" && isset($attachs)) {
        ?>
            <img src="../topicImg/<?php echo $attachs;?>" alt="" class="posa c0 coverfit">
        <?php
                    };
        ?>
            <h2 class="w100p txt-n"><?php echo $titles;?></h2>
            <p class="w100p txt-s"><?php echo $dates;?></p>
            <p class="w100p txt-s"><?php echo $contents;?></p>
            <a href="viewtopic.php?topicIds=<?php echo $ids;?>" class="link-cover hover-white">.</a>
        </div>
        <?php
                };
            };
        } else {
        ?>
            <h2 class="zthing">no topics found on the list</h2>
        <?php
        };
        ?>
    </section>
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
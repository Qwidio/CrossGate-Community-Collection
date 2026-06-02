<?php
require_once '../../processes/database.php';
$root_route = "../../";
require_once '../../secureSession.php';
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    header ('location: ../index.php');
    exit;
}

$noMkot = true;
$requestedItem = "empty";
if (isset($_GET['item'])) {
    $requestedItem = $_GET['item'];
} else {
    $requestedItem = "empty";
};
$check_profile = $connects->prepare("SELECT mkot FROM profiles WHERE profileTags = ? ;");
$check_profile->bind_param("s", $aidis);
$check_profile->execute();
$result_check_profile = $check_profile->get_result();
if ($result_check_profile->num_rows == 1) {
    $value = $result_check_profile->fetch_assoc();
    $mkot = $value['mkot'];
    $data = json_decode($mkot, true);
    $markedData = $data['marked'];
};
if (!empty($markedData) && $markedData != "empty") {
    $tempLibsArr = array();
    $marked = [];
    foreach ($markedData as $markedIndex => $info) {
        $marked[$markedIndex] = [
            "libsIds"  => $info['libsIds'],
            "Hours"    => (int)$info['Hours'],
            "lastLog"  => $info['lastLog']
        ];
        $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, libsForum FROM libslist WHERE libsIds = ? AND libsState = 'Publics' ;");
        $check_software->bind_param("s", $info['libsIds']);
        $check_software->execute();
        $result_check_software = $check_software->get_result();
        if ($result_check_software->num_rows > 0) {
            while ($value = $result_check_software->fetch_assoc()) {
                $ids = $value['libsIds'];
                $libsPublisher = $value['libsPublisher'];
                $attachs = $value['libsAttachs'];
                $libsBanners = $value['libsBanners'];
                $libsBanners = str_replace('"', "", $libsBanners);
                $titles = $value['libsTitles'];
                $Desc = $value['libsDesc'];
                $libsForum = $value['libsForum'];
                $check_forum = $connects->prepare("SELECT * FROM forums WHERE ForumState = 'Publics' AND ForumTopics = ? ORDER BY ForumDates DESC LIMIT 1;");
                $check_forum->bind_param("s", $libsForum);
                $check_forum->execute();
                $result_check_forum = $check_forum->get_result();
                if ($result_check_forum->num_rows == 1) {
                    $data = $result_check_forum->fetch_assoc();
                    $ForumIds = $data['ForumIds'];
                    $ForumCreator = $data['ForumCreator'];
                    $ForumTitles = $data['ForumTitles'];
                    $ForumTopics = $data['ForumTopics'];
                    $ForumDates = $data['ForumDates'];
                    $ForumContents = $data['ForumContents'];
                    $ForumAttachment = $data['ForumAttachment'];
                    $tempLibsArr[$ids] = [
                    "libsIds"        => "$ids",
                    "libsPublisher"  => "$libsPublisher",
                    "libsAttachs"    => "$attachs",
                    "libsBanners"    => "$libsBanners",
                    "libsTitles"     => "$titles",
                    "libsDesc"       => "$Desc",
                    "libsForum"      => "$libsForum",
                    "ForumIds"        => "$ForumIds",
                    "ForumCreator"    => "$ForumCreator",
                    "ForumTitles"     => "$ForumTitles",
                    "ForumTopics"     => "$ForumTopics",
                    "ForumDates"      => "$ForumDates",
                    "ForumContents"   => "$ForumContents",
                    "ForumAttachment" => "$ForumAttachment"
                    ];
                } else {
                    $tempLibsArr[$ids] = [
                    "libsIds"        => "$ids",
                    "libsPublisher"  => "$libsPublisher",
                    "libsAttachs"    => "$attachs",
                    "libsBanners"    => "$libsBanners",
                    "libsTitles"     => "$titles",
                    "libsDesc"       => "$Desc",
                    "libsForum"      => "$libsForum"
                    ];
                }
                $noMkot = false;
            };
        };
    }
    $usrDatTemp[] = [
        "marked"      => $marked
    ];
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
    <title>MarkOut Software</title>
</head>
<body class="wh100p">
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
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="../../profile.php?user=self" class="link-cover">.</a>
            </div>
            <?php
            }
            ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">CATEGORY</h2>
                <a href="category.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="../../TS/forum/dashboard.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../../documentation/docs.php" class="link-cover">.</a>
            </div>
            <!-- search bar -->
            <form id="SearchBar" class="posr vertiMg flex gap5 trs500ms bg-white border-1 bora-s" action="list.php">
                <input type="text" name="ids" placeholder="search software..." id="searchbox" class="pad-s-s bg-transparent c-black border-none" tabindex="1">
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
<?php
if ($noMkot == true) {
?>
    <section class="posr pad-s w100p h90 flex fld gap-s bg-thin-grad-white z2">
        <a href="list.php" class="autoMg w100p txtc txt-l hover-text-blue">MarkedOut collection will show up here</a>
    </section>
<?php
} else {
?>
    <section class="posa l0 t10 pad-s w20 minh100 flex fld gap-s bg-thin-grad-white z2">
        <div class="pad-n-s pad-st w100p flex fld gap5">
            <h2 class="pad-sb w100p txt-n semibold">MarkedOut</h2>
            <?php
            $tempCopy = $usrDatTemp[0]['marked'];
            uasort($tempCopy, function ($b, $a) {
                $timeA = strtotime($a['lastLog']);
                $timeB = strtotime($b['lastLog']);
                return $timeB <=> $timeA;
            });
            foreach ($tempCopy as $id => $value) {
                $LibIds = $value['libsIds'];
                $hour = $value['Hours'];
                $libsPublisher = $tempLibsArr[$LibIds]['libsPublisher'];
                $titles = $tempLibsArr[$LibIds]['libsTitles'];
                $attachs = $tempLibsArr[$LibIds]['libsAttachs'];
            ?>
            <div class="posr pad-m-v pad-s-s w100p flex gap5 bg-half-gray box-shad-black-1 border-purple hover-white">
                <img src="../libsImg/<?php echo $libsPublisher . "/" . $attachs;?>" alt="<?php echo $titles;?>" class="vertiMg rightMg-s10 icon-rs containfit bg-half-white">
                <h2 class="w100p txt-s"><?php echo $titles;?></h2>
                <a href="view.php?type=clts&ids=<?php echo $LibIds;?>" class="link-cover hover-white">.</a>
            </div>
            <?php
            };
            ?>
        </div>
    </section>
    <section class="posr leftMg pad-n w79 flex fld">
        <h2 class="posr leftMg w100p">Launched recently</h2>
        <div class="posr pad-s-v h100p flex gap10 ovh-v">
        <?php
        $tempCopy = $usrDatTemp[0]['marked'];
        uasort($tempCopy, function ($b, $a) {
            $timeA = strtotime($a['lastLog']);
            $timeB = strtotime($b['lastLog']);
            return $timeB <=> $timeA;
        });
        foreach ($tempCopy as $id => $value) {
            $LibIds = $value['libsIds'];
            $hour = $value['Hours'];
            $libsPublisher = $tempLibsArr[$LibIds]['libsPublisher'];
            $titles = $tempLibsArr[$LibIds]['libsTitles'];
            $attachs = $tempLibsArr[$LibIds]['libsAttachs'];
            $banners = $tempLibsArr[$LibIds]['libsBanners'];
        ?>
            <div class="posr h30 r16-9 bgc-purple flex fld border-1 z1">
                <img src="../libsImg/<?php echo $libsPublisher . "/" . $banners;?>" alt="<?php echo $banners;?>" class="posa ins0 wh100p bg-3 coverfit z2">
                <h2 class="topMg pad-s-s pad-m-v w100p txt-s bg-half-gray z3"><?php echo $titles;?></h2>
                <p class="pad-s-s pad-sb w100p txt-s bg-half-gray z3">Recorded: <?php echo $hour;?> hrs</p>
                <a href="view.php?type=clts&ids=<?php echo $LibIds;?>" class="link-cover hover-white">.</a>
            </div>
        <?php
        };
        ?>
    </section>
    <section class="leftMg pad-n w79 flex fld">
        <h2 class="leftMg bottomMg-s10 w100p">Publisher Announcement</h2>
        <div class="h100p flex gap10 ovh-v">
        <?php
        foreach ($tempCopy as $id => $value) {
            $cltsIds = $value['libsIds'];
            if (isset($tempLibsArr[$cltsIds]['ForumIds'])) {
                $ForumIds = $tempLibsArr[$cltsIds]['ForumIds'];
                $ForumAttachment = $tempLibsArr[$cltsIds]['ForumAttachment'];
                $ForumTopics = $tempLibsArr[$cltsIds]['ForumTopics'];
                $ForumTitles = $tempLibsArr[$cltsIds]['ForumTitles'];
                $ForumContents = $tempLibsArr[$cltsIds]['ForumContents'];
        ?>
            <div class="posr pad-s w30 r16-9 flex fld border-2 z1">
                <?php
                if ($ForumAttachment != "empty.png" && isset($ForumAttachment)) {
                ?>
                <img src="../../TS/ArchFiles/<?php echo $ForumAttachment;?>" alt="" class="posa ins0 r16-9 wh100p coverfit opacity5 z2">
                <?php
                } else {
                ?>
                <img src="#" alt="" class="posa ins0 r16-9 wh100p bg-1 z2">
                <?php
                };
                ?>
                <h2 class="bottomMg txt-n z3"><?php echo $ForumTitles;?></h2>
                <p class="txt-s z3"><?php echo $ForumContents;?></p>
                <a href="../../TS/forum/forum.php?ids=<?php echo $ForumIds;?>" class="link-cover hover-white">.</a>
            </div>
        <?php
            }
        };
        ?>
        </div>
    </section>
    <section class="leftMg pad-s w79 h20"></section>
<?php
};
?>
<!-- messages alerter --> 
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <?php include_once '../../extra/footer.php';?>
    <script src="../../scriptstuff/script.js"></script>
    <script src="../../scriptstuff/alert.js"></script>
    <?php
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
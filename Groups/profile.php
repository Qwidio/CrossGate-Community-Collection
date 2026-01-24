<?php
require_once '../processes/database.php';
$errors = array();
session_start();
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
}
if (!isset($_GET['gids'])) {
    $_SESSION['corsmsg'] = "denied request";
    header ('location: ../index.php');
    exit;
}
$gids = $_GET['gids'];
$publishing = false;
$State = "Publics";
$check_orgs = $connects->prepare("SELECT names, about, founded, founder, admins, members, logo, banner FROM ogroup WHERE identification = ?;");
$check_orgs->bind_param("s", $gids);
$check_orgs->execute();
$result_check_orgs = $check_orgs->get_result();
if ($result_check_orgs->num_rows > 0) {
    while ($value = $result_check_orgs->fetch_assoc()) {
        $names = $value['names'];
        $about = $value['about'];
        $founded = $value['founded'];
        $members = $value['members'];
        $logo = $value['logo'];
        $banner = $value['banner'];
    }
}
$tempLibsArr = array();
$tempForumArr = array();
$check_software = $connects->prepare("SELECT libsIds, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, libsForum FROM libslist WHERE libsPublisher = ? AND libsState = ? ;");
$check_software->bind_param("ss", $gids, $State);
$check_software->execute();
$result_check_software = $check_software->get_result();
if ($result_check_software->num_rows > 0) {
    while ($value = $result_check_software->fetch_assoc()) {
        $ids = $value['libsIds'];
        $attachs = $value['libsAttachs'];
        $libsBanners = $value['libsBanners'];
        $libsBanners = str_replace('"', "", $libsBanners);
        $titles = $value['libsTitles'];
        $Desc = $value['libsDesc'];
        $libsForum = $value['libsForum'];
        $check_forum = $connects->prepare("SELECT * FROM forums WHERE ForumState = ? AND ForumTopics = ? ORDER BY ForumDates DESC LIMIT 5;");
        $check_forum->bind_param("ss", $State, $libsForum);
        $check_forum->execute();
        $result_check_forum = $check_forum->get_result();
        if ($result_check_forum->num_rows > 0) {
            while ($data = $result_check_forum->fetch_assoc()) {
                $ForumIds = $data['ForumIds'];
                $ForumCreator = $data['ForumCreator'];
                $ForumTitles = $data['ForumTitles'];
                $ForumTopics = $data['ForumTopics'];
                $ForumDates = $data['ForumDates'];
                $ForumContents = $data['ForumContents'];
                $ForumAttachment = $data['ForumAttachment'];
                if (!in_array($ids, $tempLibsArr)) {
                    $tempForumArr[$ForumIds] = [
                    "ForumIds"        => "$ForumIds",
                    "ForumCreator"    => "$ForumCreator",
                    "ForumTitles"     => "$ForumTitles",
                    "ForumDates"      => "$ForumDates",
                    "ForumContents"   => "$ForumContents",
                    ];
                };
            };
        }
        if (!in_array($ids, $tempLibsArr)) {
            $tempLibsArr[$ids] = [
            "libsIds"        => "$ids",
            "libsAttachs"    => "$attachs",
            "libsBanners"    => "$libsBanners",
            "libsTitles"     => "$titles",
            "libsDesc"       => "$Desc",
            "libsForum"      => "$libsForum"
            ];
        };
    };
    $publishing = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styling/pallate.css">
    <link rel="stylesheet" href="../styling/Mindex.css">
    <link rel="stylesheet" href="../styling/footer.css">
    <title><?php echo $names;?> || CrossGate Profile</title>
</head>
<body class="w100p minh100 bg-2">
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg border-b z4">
        <a href="../index.php" class="vertiMg pad-s txt-l semibold">CROSSGATE</a>
        <div class="posr w60p flex gap-s">
            <?php
            if (isset($aidis)) {
                ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="../Library/core/markout.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="../profile.php?user=self" class="link-cover">.</a>
            </div>
            <?php
            }
            ?>
            <div class="posr pad-s flex fld acjc">   
                <h2 class="txt-n txtc semibold">CATEGORY</h2>
                <a href="../Library/core/category.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="../TS/forum/dashboard.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../documentation/docs.php" class="link-cover">.</a>
            </div>
        </div>
        <?php
        if (!isset($aidis)) {
        ?>
        <div class="leftMg flex acjc gap10">
            <p class="posr pad-n-s pad-s-v txtc txt-n bg-1 border-1 bora-s border-hover-white">LOGIN
                <a href="../forum-connect/connect_it.php?state=login" class="link-cover">.</a>
            </p>
        </div>
        <?php
        };
        ?>
    </div>
    <div class="posr w75p h30 flex z2">
        <div class="posr vertiMg r1-1 w20p flex z3">
            <?php
            if (empty($logo) || $logo === "empty") {
            ?>
            <img src="../img/business-outline.svg" class="autoMg r1-1 h80p flex acjc bg-blur objfit border-1 bora-s z4">
            <?php
            } else {
            ?>
            <img src="img/<?php echo $logo;?>" alt="<?php echo $names;?>" class="autoMg r1-1 h80p flex acjc bg-blur objfit border-1 bora-s z4">
            <?php
            };
            ?>
        </div>
        <div class="posr pad-n-v pad-sr w60p h100p flex fld z4">
            <h2 class="topMg w100p txt-l"><?php echo $names;?></h2>
            <div class="rightMg txt-s ovh-s"><?php echo $founded;?></div>
            <div class="topMg-s5 w100p minh20 maxh20 txt-s ovh"><?php echo $about;?></div>
        </div>
    </div>
    <div class="pad-b-s pad-s-v w75p flex gap10">
        <div class="pad-m flex gap5 border-b bora-s hover-white">
            <img src="../img/warning.svg" class="icon-m">
            <h2 class="txt-n">Overview</h2>
        </div>
        <div class="pad-m flex gap5 bora-s hover-white">
            <img src="../img/warning.svg" class="icon-m">
            <h2 class="txt-n">Annoucement</h2>
        </div>
        <div class="pad-m flex gap5 bora-s hover-white">
            <img src="../img/warning.svg" class="icon-m">
            <h2 class="txt-n">Software</h2>
        </div>
        <div class="pad-m flex gap5 bora-s hover-white">
            <img src="../img/warning.svg" class="icon-m">
            <h2 class="txt-n">Members</h2>
        </div>
    </div>
    <section class="sideMg pad-n w75p flex">
        <?php
        if ($publishing == true) {
        ?>
        <div class="w75p flex gap5 ovs-s ovh-v">
            <?php
            foreach ($tempLibsArr as $id => $value) {
                $LibIds = $value['libsIds'];
                $titles = $value['libsTitles'];
                $attachs = $value['libsAttachs'];
                $banners = $value['libsBanners'];
        ?>
            <div class="posr h30 r16-9 bg-1 flex fld border-1 z1">
                <img src="../Library/libsimg/<?php echo $banners;?>" alt="<?php echo $banners;?>" class="posa ins0 wh100p bg-3 z2">
                <h2 class="topMg pad-s-s pad-m-v w100p txt-s bg-half-gray z3"><?php echo $titles;?></h2>
                <a href="../Library/core/view.php?type=clts&ids=<?php echo $LibIds;?>" class="link-cover hover-white">.</a>
            </div>
        <?php
            };
        ?>
        </div>
        <?php
        } else {
        ?>
        <div class="pad-n-v w75p flex">
            <h2 class="sideMg txt-s z3">No Publishes Found</h2>
        </div>
        <?php
        };
        ?>
        <div class="pad-s-s w20p flex fld border-l gap5">
            <h2 class="w100p txt-b">Members</h2>
        <?php
        $memberslist = json_decode($members);
        foreach ($memberslist as $Members => $value) {
            $uDs = $value;
            $check_profile = $connects->prepare("SELECT profileTags, profileAttachs, profileNames FROM profiles WHERE profileTags = ? ;");
            $check_profile->bind_param("s", $uDs);
            $check_profile->execute();
            $result_check_profile = $check_profile->get_result();
            if ($result_check_profile->num_rows > 0) {
                $value = $result_check_profile->fetch_assoc();
                $Tags = $value['profileTags'];
                $pfAttachs = $value['profileAttachs'];
                $Names = $value['profileNames'];
                $iconAlt = ucfirst(substr($Names, 0, 1));
        ?>
            <div class="posr pad-m-v pad-s-s w100p flex border-1 z4">
            <?php
                if (empty($pfAttachs) || $pfAttachs === "empty") {
            ?>
                <img src="../img/person.svg" class="r1-1 w20p flex acjc border-1 objfit z4">
            <?php
                } else {
            ?>
                <img src="../zprpic/<?php echo $pfAttachs;?>" alt="<?php echo $Names;?>" class="r1-1 w20p flex acjc border-1 objfit z4">
            <?php
                };
            ?>
                <div class="posr w80p flex fld">
                    <h2 class="vertiMg rightMg pad-s-s txt-b"><?php echo $Names;?></h2>
                </div>
                <a href="../profile.php?user=<?php echo $Tags;?>" class="link-cover hover-white">.</a>
            </div>
        <?php
            };
        };
        ?>
        </div>
    </section>
    <section class="posr sideMg pad-n w75p flex">
        <div class="w75p flex wrap">
        <?php
        if ($publishing == true) {
            foreach ($tempForumArr as $id => $value) {
                $ids = $value['ForumIds'];
                $creators = $value['ForumCreator'];
                $titles = $value['ForumTitles'];
                $dates = $value['ForumDates'];
                $contents = $value['ForumContents'];
        ?>
                <div class="posr pad-s-s pad-m-v w50p r16-9 flex fld border-1 hover-white z2">
                    <h2 class="pad-m-v w100p txt-n border-b wrap"><?php echo $titles;?></h2>
                    <div class="bottomMg-s5 w100p flex space-between border-b">
                        <p class="txt-s"><?php echo $creators;?></p>
                        <p class="txt-s"><?php echo $dates;?></p>
                    </div>
                    <p class="forum-content"><?php echo $contents;?>
                    </p>
                    <a href="../TS/forum/forum.php?ids=<?php echo $ids;?>" class="link-cover">.</a>
                </div>
        <?php
            };
        } else {
        ?>
            <h2 class="sideMg txt-s z3">No Forum Post Found</h2>
        <?php
        };
        ?>
        </div>
        <div class="pad-s-s w20p minh10 flex fld gap5">
        </div>
    </section>
    <?php include_once '../footer.php';?>
<!-- another messages passer -->
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script src="../scriptstuff/script.js"></script>
    <script src="../scriptstuff/alert.js"></script>
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
<?php
require_once '../processes/database.php';
$errors = array();
$root_route = "../";
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    require_once 'secureSession.php';
};
if (!isset($_GET['gids'])) {
    $_SESSION['corsmsg'] = "denied request";
    header ('location: ../index.php');
    exit;
}
$gids = $_GET['gids'];
$_SESSION['prev_loc'] = "Groups/profile.php?gids=" . $gids;
$publishing = false;
$State = "Publics";
$check_orgs = $connects->prepare("SELECT names, about, founded, founder, admins, members, logo, banner, sites FROM ogroup WHERE identification = ?;");
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
        $sites = json_decode($value['sites'], true);
        foreach ($sites as $siteIndex => $siteData) {
            $site = $siteData["site"];
            $yt = $siteData["yt"];
        }
    }
} else {
    $_SESSION['corsmsg'] = "cannot find the group your searched";
    header ('location: ../index.php');
    exit;
}
$tempLibsArr = array();
$tempForumArr = array();
$check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, libsForum FROM libslist WHERE libsPublisher = ? AND libsState = 'publics';");
$check_software->bind_param("s", $gids);
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
        if (!in_array($ids, $tempLibsArr)) {
            $tempLibsArr[$ids] = [
            "libsIds"        => "$ids",
            "libsPublisher"    => "$libsPublisher",
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
$memberslist = json_decode($members);
foreach ($memberslist as $Members => $datas) {
    $creatorTarg = $datas;
    $check_forum = $connects->prepare("SELECT * FROM forums WHERE ForumState = ? AND ForumCreator = ? ORDER BY ForumDates DESC;");
    $check_forum->bind_param("ss", $State, $creatorTarg);
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
            if (!in_array($ForumIds, $tempForumArr)) {
                $tempForumArr[$ForumIds] = [
                "ForumIds"        => "$ForumIds",
                "ForumCreator"    => "$ForumCreator",
                "ForumTitles"     => "$ForumTitles",
                "ForumDates"      => "$ForumDates",
                "ForumContents"   => "$ForumContents",
                "ForumAttachment"   => "$ForumAttachment"
                ];
            };
        };
        $publishing = true;
    };
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
<body class="minh100">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity05 z-1">
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="../img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="../index.php" class="link-cover">.</a>
        </div>
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
                <a href="../connect_it/connect_it.php?state=login" class="link-cover">.</a>
            </p>
        </div>
<?php
} else if (isset($_SESSION['GroupsToken']) && $_SESSION['gids']) {
    $check_orgs = $connects->prepare("SELECT og_identification FROM groupaccess WHERE profileTags = ? AND og_identification = ?;");
    $check_orgs->bind_param("ss", $aidis, $_SESSION['gids']);
    $check_orgs->execute();
    $result_check_orgs = $check_orgs->get_result();
    if ($result_check_orgs->num_rows > 0) {
?>
            <div class="leftMg flex acjc gap10">
                <p class="posr pad-n-s pad-s-v txtc txt-n bg-3 border-1 bora-s">Dashboard
                    <a href="manage.php" class="link-cover">.</a>
                </p>
            </div>
<?php
    }
}
?>
    </div>
    
<?php include_once '../reportTab.php';
if (isset($aidis)) {
?>
    <div class="posf pad-n b0 r0 flex z999">
        <img src="../img/warning.svg" alt="" class="posr icon-t containfit bg-half-white opacity3 hover-visible points" onclick="uniDisplaySwitch('reportDialog'); uniLoad(this, 'reportForm');" data-reportsource="groups" data-ids="<?php echo $gids;?>">
    </div>
<?php
}
?>

    <!-- profile -->
    <div class="posr w70p h30 flex blurbg z2">
        <div class="posr vertiMg r1-1 w20p flex z3">
            <?php
            if (empty($logo) || $logo === "empty") {
                ?>
            <img src="../img/business-outline.svg" class="autoMg r1-1 h80p flex acjc blurbg containfit bg-half-white border-1 bora-s z4">
            <?php
            } else {
                ?>
            <img src="img/<?php echo $gids . "/" . $logo;?>" alt="<?php echo $names;?>" class="autoMg r1-1 h80p flex acjc blurbg containfit border-1 bora-s z4">
            <?php
            };
            ?>
        </div>
        <div class="posr pad-n-v pad-sr w50p max50p h100p flex fld z4">
            <h2 class="topMg w100p txt-l"><?php echo $names;?></h2>
            <div class="rightMg txt-s ovh-s"><?php echo $founded;?></div>
            <div class="topMg-s5 w100p minh10 txt-s wrap ovh"><?php echo $about;?></div>
            <div class="posr vertiMg pad-s-v flex gap5">
            <?php
                if ($site != "") {
                ?>
                <a href="https://<?php echo $site;?>" class="pad-m-v pad-n-s txt-s txtc c-white bgc-orange box-shad-black-1 border-purple bora-s hover-text-black">Websites</a>
            <?php
                }
                if ($yt != "") {
                ?>
                <a href="https://<?php echo $yt;?>" class="pad-m-v pad-n-s txt-s txtc c-white bgc-red box-shad-black-1 border-purple bora-s hover-text-black">Youtube</a>
            <?php
                }
                ?>
            </div>
        </div>
    </div>
    <!-- pubslishes & post -->
    <section class="posr sideMg bottomMg-10 pad-n-v w70p flex blurbg z2">
        <div class="posr rightMg pad-n-v pad-s-s w75p flex fld z3">
            <div class="bgc-orange w100p flex"><h2 class="rightMg pad-s txt-b">Published Collection</h2></div>
            <?php
            if ($publishing == true) {
            ?>
            <div class="pad-s w100p flex gap5 ovh-v">
                <?php
                foreach ($tempLibsArr as $id => $value) {
                    $LibIds = $value['libsIds'];
                    $titles = $value['libsTitles'];
                    $libsPublisher = $value['libsPublisher'];
                    $attachs = $value['libsAttachs'];
                    $banners = $value['libsBanners'];
            ?>
                <div class="posr h30 r16-9 bg-1 flex fld border-1 z1">
                    <img src="../Library/libsImg/<?php echo $libsPublisher . "/" . $banners;?>" alt="<?php echo $banners;?>" class="posa ins0 wh100p coverfit bg-3 z3">
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
            <div class="pad-n-v w100p flex">
                <h2 class="sideMg minh20 txt-s z3">No Publishes Found</h2>
            </div>
            <?php
            };
            ?>
            <div class="bgc-orange w100p flex"><h2 class="rightMg pad-s txt-b">Posted Announcement</h2></div>
            <div class="bottomMg pad-n-v pad-s-s w100p minh20 flex wrap ovh-s z1">
            <?php
            if ($publishing == true) {
                foreach ($tempForumArr as $id => $value) {
                    $ids = $value['ForumIds'];
                    $creators = $value['ForumCreator'];
                    $titles = $value['ForumTitles'];
                    $dates = $value['ForumDates'];
                    $contents = $value['ForumContents'];
                    $attachs = $value['ForumAttachment'];
            ?>
                <div class="posr pad-s minw15 w50p r16-9 flex fld bg-half-gray border-1 gap5">
            <?php
                            if ($attachs != "empty.png" && isset($attachs)) {
            ?>
                    <img src="../TS/ArchFiles/<?php echo $attachs;?>" alt="" class="posa c0 w100p r16-9 coverfit opacity3 z2">
            <?php
                            };
            ?>
                    <h2 class="txt-n z3"><?php echo $titles;?></h2>
                    <div class="bottomMg-s5 w100p flex space-between z3">
                        <p class="txt-s z3"><?php echo $creators;?></p>
                        <p class="txt-s z3"><?php echo $dates;?></p>
                    </div>
                    <p class="maxh10 txt-s ovh z3"><?php echo $contents;?></p>
                    <a href="../TS/forum/forum.php?ids=<?php echo $ids;?>" class="posr link-cover hover-white z4">.</a>
                </div>
            <?php
                };
            } else {
            ?>
                <h2 class="sideMg minh20 txt-s z3">No Forum Post Found</h2>
            <?php
            };
            ?>
            </div>
            <div class="posr pad-s-s w20p minh10">
            </div>
        </div>
        <!-- members -->
        <div class="pad-n-s w30p flex fld border-l gap5">
            <h2 class="bottomMg-s5 w100p txt-b">Members</h2>
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
                <img src="../img/person.svg" class="r1-1 w20p flex acjc bg-half-white border-1 bora-s containfit z4">
            <?php
                } else {
            ?>
                <img src="../zprpic/<?php echo $Tags . "/" . $pfAttachs;?>" alt="<?php echo $Names;?>" class="r1-1 w20p flex acjc border-1 containfit z4">
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
    <?php include_once '../extra/footers.php';?>
<!-- messages alerter -->
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
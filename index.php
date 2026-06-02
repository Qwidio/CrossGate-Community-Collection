<?php
require_once 'processes/database.php';
$errors = array();
$_SESSION['prev_loc'] = "index.php";
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    $root_route = "";
    require_once 'secureSession.php';
};

$State = "publics";
$tempLibsArr = array();
$stmt_check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs, libsCategorys FROM libslist WHERE libsState = ? ORDER BY addedDates DESC LIMIT 10;");
$stmt_check_software->bind_param("s", $State);
$stmt_check_software->execute();
$result_check_software = $stmt_check_software->get_result();
if ($result_check_software->num_rows > 0) {
    $uniqueItem = [];
    while ($value = $result_check_software->fetch_assoc()) {
        $ids = $value['libsIds'];
        $attachs = $value['libsAttachs'];
        $libsPublisher = $value['libsPublisher'];
        $libsBanners = $value['libsBanners'];
        $libsBanners = str_replace('"', "", $libsBanners);
        $titles = $value['libsTitles'];
        $Desc = $value['libsDesc'];
        $addedDates = $value['addedDates'];
        $cltNumbs = $value['cltNumbs'];
        $category = $value['libsCategorys'];
        if (!in_array($ids, $uniqueItem)) {
            $tempLibsArr[$ids] = [
            "libsIds"        => "$ids",
            "libsPublisher"  => "$libsPublisher",
            "libsAttachs"    => "$attachs",
            "libsBanners"    => "$libsBanners",
            "libsTitles"     => "$titles",
            "libsDesc"       => "$Desc",
            "libsCategorys"  => "$category",
            "addedDates"     => "$addedDates",
            "cltNumbs"       =>  $cltNumbs,
            ];
        };
    };
};

$tempCatgArray = [];
$stmt_check_category = $connects->prepare("SELECT * FROM categorys WHERE categoryState = ?;");
$stmt_check_category->bind_param("s", $State);
$stmt_check_category->execute();
$result_check_category = $stmt_check_category->get_result();
if ($result_check_category->num_rows > 0) {
    $uniqueItem = [];
    while ($value = $result_check_category->fetch_assoc()) {
        $ids = $value['categoryIds'];
        $titles = $value['categoryTitles'];
        if (!in_array($ids, $uniqueItem)) {
            $tempCatgArray[$ids] = $titles;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="styling/pallate.css">
    <link rel="stylesheet" href="styling/footer.css">
    <link rel="stylesheet" href="styling/Mindex.css">
    <link rel="stylesheet" href="styling/slides.css">
    <title>CGCC</title>
</head>
<?php
if (!isset($_SESSION['profileTags'])) {
?>
<body class="wh100p bg-5 ovh">
    <img src="img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity3 z1">
    <!-- unlogged welcome -->
    <div class="posr w100p h100 flex blurbg z2">
        <div class="posr pad-n w40p h100p flex fld blurbg bg-prf-default border-custom-r z3">
            <h2 class="pad-nt trlt-b-5 pad-b-s txt-maintext bold text-stroke">CROSSGATES</h2>
            <h2 class="bottomMg-s10 pad-nb pad-b-s txt-l c-orange text-stroke">Community Collection</h2>
            <div class="posr pad-s-v pad-b-s flex fld">
                <h2 class="txt-b semibold">BROWSE</h2>
                <a href="Library/core/list.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-s-v pad-b-s flex fld">   
                <h2 class="txt-b semibold">CATEGORY</h2>
                <a href="Library/core/category.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-s-v pad-b-s flex fld">
                <h2 class="txt-b semibold">FORUM</h2>
                <a href="TS/forum/dashboard.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-s-v pad-b-s flex fld">
                <h2 class="txt-b semibold">DOCS</h2>
                <a href="documentation/docs.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr topMg pad-n-v pad-b-s flex fld">
                <p class="posr pad-n-s pad-s-v txtc txt-n bg-prf-default border-3 hover-special-toright hover-enlarge ovh">LOGIN
                    <a href="connect_it/connect_it.php?state=login" class="link-cover">.</a>
                </p>
            </div>
        </div>
        <section class="posr w70p h100p flex fld acjc z3">
            <h2 class="sideMg pad-sb txt-b semibold">Featured Releases</h2>
            <div class="posr sideMg w50p flex fld gap-s">
            <?php
            $tempLibsArrCopy = $tempLibsArr;
            uasort($tempLibsArrCopy, function ($a, $b) {
                return $b['cltNumbs'] <=> $a['cltNumbs'];
            });
            $count = 0;
            foreach ($tempLibsArrCopy as $id => $value) {
                $ids = $value['libsIds'];
                $attachs = $value['libsAttachs'];
                $libsPublisher = $value['libsPublisher'];
                $banners = $value['libsBanners'];
                $titles = $value['libsTitles'];
                $Desc = $value['libsDesc'];
                $addedDates = $value['addedDates'];
                $cltNumbs = $value['cltNumbs'];
                $category = $value['libsCategorys'];
                $catgList = $tempCatgArray[$category] ?? null;
                if ($count < 2) {
                    $count = $count + 1;
            ?>
                <div class="posr vertiMg minw100px w90p r16-9 flex fld border-1 bora-s box-shad-white-1 gap10 z4">
                    <img src="Library/libsImg/<?php echo $libsPublisher . "/" . $banners;?>" class="posa ins0 wh100p bgc-purple bora-s z2">
                    <h2 class="topMg pad-s w100p txt-s bg-half-gray z3"><?php echo $titles;?></h2>
                    <a href="Library/core/view.php?type=clts&ids=<?php echo $ids;?>" class="link-cover hover-white">.</a>
                </div>
            <?php
                }
            };
            ?>
            </div>
        </section>
    </div>
<?php
} else {
?>
<body class="wh100p ovh-s ovs-v">
    <img src="img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity05 z1">
    <!-- nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
        </div>
        <div class="posr w60p flex gap-s">
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="Library/core/markout.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="profile.php?user=self" class="link-cover">.</a>
            </div>
<?php
$prebind = '"' . $aidis . '"';
$check_orgs = $connects->prepare("SELECT identification FROM ogroup WHERE founder = ? OR JSON_CONTAINS(members, ?);");
$check_orgs->bind_param("ss", $aidis, $prebind);
$check_orgs->execute();
$result_check_orgs = $check_orgs->get_result();
if ($result_check_orgs->num_rows > 0) {
    $value = $result_check_orgs->fetch_assoc();
    $identification = $value['identification'];
?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">GROUPS</h2>
                <a href="Groups/index.php" class="link-cover">.</a>
            </div>
<?php
}
?>
            <div class="posr pad-s flex fld acjc">   
                <h2 class="txt-n txtc semibold">CATEGORY</h2>
                <a href="Library/core/category.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="TS/forum/dashboard.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="documentation/docs.php" class="link-cover">.</a>
            </div>
            <!-- search bar -->
            <form id="SearchBar" class="posr vertiMg flex gap5 trs500ms bg-white border-1 bora-s" action="Library/core/list.php">
                <input type="text" name="ids" placeholder="search software..." id="searchbox" class="pad-s-s bg-transparent c-black border-none" tabindex="1">
                <button type="submit" name="filter" value="search" class="posr vertiMg pad-s flex bg-transparent c-black border-none" tabindex="2"><img src="img/search.png" alt="" class="icon-rs h100p containfit points"></button>
            </form>
        </div>
    </div>
<!-- banner stuff -->
    <section class="posr pad-sl w100 r4-1 flex z4">
        <div class="posa t0 r0 wh100p flex" id="slides">
        </div>
        <button class="prev">&#10094;</button>
        <button class="next">&#10095;</button>
    </section>
<!-- featured software -->
    <section class="posr topMg-5 sideMg w65 flex fld z4">
        <h2 class="sideMg pad-sb w100p">Featured Releases</h2>
        <div class="h100p flex gap5 ovh">
        <?php
        $tempLibsArrCopy = $tempLibsArr;
        uasort($tempLibsArrCopy, function ($a, $b) {
            return $b['cltNumbs'] <=> $a['cltNumbs'];
        });
        $count = 0;
        foreach ($tempLibsArrCopy as $id => $value) {
            $ids = $value['libsIds'];
            $libsPublisher = $value['libsPublisher'];
            $attachs = $value['libsAttachs'];
            $banners = $value['libsBanners'];
            $titles = $value['libsTitles'];
            $Desc = $value['libsDesc'];
            $addedDates = $value['addedDates'];
            $cltNumbs = $value['cltNumbs'];
            $category = $value['libsCategorys'];
            $catgList = $tempCatgArray[$category] ?? null;
            if ($count < 2) {
                $count = $count + 1;
        ?>
            <div class="posr vertiMg w50p r16-9 flex fld border-1 gap10 z3">
                <img src="Library/libsImg/<?php echo $libsPublisher . "/" . $banners;?>" class="posa ins0 wh100p bgc-purple z3">
                <h2 class="topMg pad-s w100p txt-s bg-half-gray z4"><?php echo $titles;?></h2>
                <a href="Library/core/view.php?type=clts&ids=<?php echo $ids;?>" class="link-cover hover-white">.</a>
            </div>
        <?php
            }
        };
        ?>
        </div>
    </section>
<!-- software list -->
    <section class="posr topMg-5 sideMg w65 minh20 flex fld" id="softwarelist">
        <h2 class="posr pad-n-v w100p">New Releases</h2>
        <?php
        foreach ($tempLibsArr as $id => $value) {
            $ids = $value['libsIds'];
            $libsPublisher = $value['libsPublisher'];
            $banners = $value['libsBanners'];
            $titles = $value['libsTitles'];
            $Desc = $value['libsDesc'];
            $addedDates = $value['addedDates'];
            $cltNumbs = $value['cltNumbs'];
            $category = $value['libsCategorys'];
            $catgList = $tempCatgArray[$category] ?? null;
            ?>
        <div class="posr pad-s w100p flex gap5 blurbg border-purple hover-white z3">
            <img src="Library/libsImg/<?php echo $libsPublisher . "/" . $banners;?>" class="posr topMg-s10 bottomMg-s10 leftMg-s10 h10 r16-9 coverfit">
            <div class="posr topMg-s10 leftMg-s10 h100p flex fld">
                <h2 class="rightMg txt-n"><?php echo $titles;?></h2>
                <h2 class="rightMg txt-s c-lightgray"><?php echo $Desc;?></h2>
                <p class="topMg rightMg txt-s c-semiwhite"><?php
                    if (isset($catgList)) {
                        echo $catgList;
                    } else {
                        echo "Undefined";
                    };
                    ?></p>
            </div>
            <div class="posr topMg-s10 leftMg rightMg-s10 h100p flex fld">
                <p class="posr txt-s c-semiwhite"><?php echo $addedDates;?></p>
            </div>
            <a href="Library/core/view.php?type=clts&ids=<?php echo $ids;?>" class="link-cover hover-white">.</a>
        </div>
        <?php
        };
        ?>
    </section>
<!-- recommendation -->
    <div class="posr topMg-5 bottomMg-5 pad-b-s pad-n-v w65 blurbg flex gap5 bg-4 border-1 z2">
        <h2 class="posr vertiMg txt-b semibold">Wanna see all listed software? browse on Library list</h2>
        <div class="posr leftMg flex acjc border-1 bgc-purple">
            <a href="Library/core/list.php" class="posr pad-s-v pad-n-s  wh100p txtc txt-b semibold hover-white hover-text-orange">Browse</a>
        </div>
    </div>
    <?php include_once 'footer.php';?>
    <script src="scriptstuff/slide.js"></script>
<?php
}
?>
<!-- messages alerter -->
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script src="scriptstuff/script.js"></script>
    <script src="scriptstuff/alert.js"></script>
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
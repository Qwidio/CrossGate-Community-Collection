<?php
require_once 'processes/database.php';
$errors = array();
session_start();
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
};
$page = "index";
$_SESSION['prev_loc'] = "index.php";

$State = "publics";
$tempLibsArr = array();
$stmt_check_software = $connects->prepare("SELECT libsIds, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs, libsCategorys FROM libslist WHERE libsState = ? ORDER BY addedDates DESC LIMIT 10;");
$stmt_check_software->bind_param("s", $State);
$stmt_check_software->execute();
$result_check_software = $stmt_check_software->get_result();
if ($result_check_software->num_rows > 0) {
    $uniqueItem = [];
    while ($value = $result_check_software->fetch_assoc()) {
        $ids = $value['libsIds'];
        $attachs = $value['libsAttachs'];
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
    <title>Welcome</title>
</head>
<body class="wh100p bg-2 flex fld ovh-s ovs-v">
    <!-- nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <a href="index.php" class="vertiMg pad-s txt-l semibold">CROSSGATE</a>
        <div class="posr w60p flex gap-s">
            <?php
            if (isset($aidis)) {
                ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="Library/core/markout.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="profile.php?user=self" class="link-cover">.</a>
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
        </div>
        <?php
        if (!isset($aidis)) {
        ?>
        <div class="leftMg flex acjc gap10">
            <p class="posr pad-n-s pad-s-v txtc txt-n bg-1 border-1 bora-s border-hover-white">LOGIN
                <a href="forum-connect/connect_it.php?state=login" class="link-cover">.</a>
            </p>
        </div>
        <?php
        }
        ?>
    </div>
<!-- banner stuff -->
    <section class="posr pad-sl w100 r4-1 flex">
        <div class="posa t0 r0 wh100p flex" id="slides">
        </div>
        <button class="prev">&#10094;</button>
        <button class="next">&#10095;</button>
    </section>
<!-- featured software -->
    <section class="topMg-5
     sideMg w65 flex fld">
        <h2 class="sideMg pad-sb w100p">Featured Releases</h2>
        <div class="h100p flex gap5 ovh">
        <?php
        $tempLibsArrCopy = $tempLibsArr;
        uasort($tempLibsArrCopy, function ($a, $b) {
            return $b['cltNumbs'] <=> $a['cltNumbs'];
        });
        foreach ($tempLibsArrCopy as $id => $value) {
            $ids = $value['libsIds'];
            $attachs = $value['libsAttachs'];
            $banners = $value['libsBanners'];
            $titles = $value['libsTitles'];
            $Desc = $value['libsDesc'];
            $addedDates = $value['addedDates'];
            $cltNumbs = $value['cltNumbs'];
            $category = $value['libsCategorys'];
            $catgList = $tempCatgArray[$category] ?? null;
        ?>
            <div class="posr vertiMg w50p r16-9 bg-1 flex fld border-1 gap10 z1">
                <img src="Library/libsImg/<?php echo $banners;?>" alt="<?php echo $banners;?>" class="posa ins0 wh100p bg-3 z2">
                <h2 class="topMg pad-s w100p txt-s bg-half-gray z3"><?php echo $titles;?></h2>
                <a href="Library/core/view.php?type=clts&ids=<?php echo $ids;?>" class="link-cover hover-white">.</a>
            </div>
        <?php
        };
        ?>
        </div>
    </section>
<!-- software list -->
    <section class="topMg-5 sideMg pad-s-v w65 h100 flex fld" id="softwarelist">
        <h2 class="pad-sb w100p">New Releases</h2>
        <?php
        foreach ($tempLibsArr as $id => $value) {
            $ids = $value['libsIds'];
            $banners = $value['libsBanners'];
            $titles = $value['libsTitles'];
            $Desc = $value['libsDesc'];
            $addedDates = $value['addedDates'];
            $cltNumbs = $value['cltNumbs'];
            $category = $value['libsCategorys'];
            $catgList = $tempCatgArray[$category] ?? null;
            ?>
        <div class="posr pad-s w100p flex gap5 border-1">
            <img src="Library/libsImg/<?php echo $banners;?>" alt="<?php echo $banners;?>" class="h10 r16-9 objfit">
            <div class="h100p flex fld">
                <h2 class="rightMg txt-n"><?php echo $titles;?></h2>
                <h2 class="rightMg txt-s c-lightgray"><?php echo $Desc;?></h2>
                <p class="topMg rightMg txt-s c-semiwhite"><?php
                    if (isset($catgList)) {
                        echo $catgList;
                    } else {
                        echo "Undefined";
                    };
                    ?></p>
                <a href="Library/core/view.php?type=clts&ids=<?php echo $ids;?>" class="link-cover hover-white">.</a>
            </div>
            <div class="leftMg h100p flex fld">
                <p class="topMg leftMg txt-s c-semiwhite"><?php echo $addedDates;?></p>
            </div>
        </div>
        <?php
        };
        ?>
    </section>
    <?php include_once 'footer.php';?>
<!-- another messages passer -->
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script src="scriptstuff/script.js"></script>
    <script src="scriptstuff/slide.js"></script>
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
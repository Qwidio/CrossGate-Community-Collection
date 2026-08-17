<?php
require_once '../../processes/database.php';
$errors = array();
$signed = false;
$nolibs = false;
$tempLibsArr = array();
$usedCatg = [];
if (isset($_GET['filter'])) {
    $FilterReq = $_GET['filter']; 
} else {
    $FilterReq = "none";
}
if (isset($_GET['ids'])) {
    $targetIds = $_GET['ids'];
    if($targetIds != "") {
        $targetIds = htmlspecialchars($targetIds, ENT_QUOTES, 'UTF-8');
        $searchTarget = "%".$targetIds."%";
    } else {
        $FilterReq = "none";
        $targetIds = null;
    }
}
if (isset($_SESSION['profileTags'])) {
    $signed = true;
    $aidis = $_SESSION['profileTags'];
};
$pages = 1;
if (isset($_GET['page']) && $_GET['page'] > 0) {
    $pages = $_GET['page'];
}
$totalDisplayed = 0;
$currentOffset = 0;
$currentIndex = 1;
$displayLimit = 50;
if ($pages > 1 && $currentIndex == $displayLimit) {
    $currentOffset = $pages - 1 * $displayLimit;
}

$check_count = $connects->prepare("SELECT COUNT(libslist.libsIds) AS totalCount FROM libslist WHERE libsState = 'publics';");
$check_count->execute();
$check_count = $check_count->get_result();
if ($check_count->num_rows > 0) {
    while ($value = $check_count->fetch_assoc()) {
        $totalCount = $value['totalCount'];
    };
} else {
    $nolibs = true;
    $FilterReq = "nodata";
};
switch ($FilterReq) {
    case 'none':
        $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs, libsCategorys FROM libslist WHERE libsState = 'publics' ORDER BY addedDates DESC LIMIT ? OFFSET ? ;");
        $check_software->bind_param("ii", $displayLimit, $currentOffset);
        break;
    case 'oldtonew':
        $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs, libsCategorys FROM libslist WHERE libsState = 'publics' ORDER BY addedDates ASC LIMIT ? OFFSET ? ;");
        $check_software->bind_param("ii", $displayLimit, $currentOffset);
        break;
    case 'az':
        $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs, libsCategorys FROM libslist WHERE libsState = 'publics' ORDER BY libsTitles ASC LIMIT ? OFFSET ? ;");
        $check_software->bind_param("ii", $displayLimit, $currentOffset);
        break;
    case 'za':
        $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs, libsCategorys FROM libslist WHERE libsState = 'publics' ORDER BY libsTitles DESC LIMIT ? OFFSET ? ;");
        $check_software->bind_param("ii", $displayLimit, $currentOffset);
        break;
    case 'search':
        if(!isset($searchTarget)) {
            $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs, libsCategorys FROM libslist WHERE libsState = 'publics' ORDER BY addedDates DESC LIMIT ? OFFSET ? ;");
            $check_software->bind_param("ii", $displayLimit, $currentOffset);
        } else {
            $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs, libsCategorys FROM libslist WHERE libsTitles LIKE ? AND libsState = 'publics' ORDER BY addedDates DESC LIMIT ? OFFSET ? ;");
            $check_software->bind_param("sii",  $searchTarget, $displayLimit, $currentOffset);
        }
        break;
    case 'nodata':
        $nolibs = true;
        break;
    default:
        $_SESSION['corsmsg'] = "Unknown filter";
        header ('location: list.php?filter=none');
        exit;
        break;
}
if ($FilterReq != "nodata") {
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
            $addedDates = $value['addedDates'];
            $cltNumbs = $value['cltNumbs'];
            $libsCategorys = $value['libsCategorys'];
            if (!in_array($ids, $tempLibsArr)) {
                $tempLibsArr[$ids] = [
                "libsIds"        => "$ids",
                "libsPublisher"  => "$libsPublisher",
                "libsAttachs"    => "$attachs",
                "libsBanners"    => "$libsBanners",
                "libsTitles"     => "$titles",
                "libsDesc"       => "$Desc",
                "libsCategorys"  => "$libsCategorys",
                "addedDates"     => "$addedDates",
                "cltNumbs"       =>  $cltNumbs
                ];
                $currentIndex++;
            };
            if (!in_array($libsCategorys, $usedCatg)) {
                $usedCatg[$libsCategorys] = [$libsCategorys];
            };
        };
    } else {
        $nolibs = true;
    };
}

$tempCatgArray = [];
foreach ($usedCatg as $catgIds => $ctgIds) {
    $stmt_check_category = $connects->prepare("SELECT * FROM categorys WHERE categoryIds = ? AND categoryState = 'publics';");
    $stmt_check_category->bind_param("s", $catgIds);
    $stmt_check_category->execute();
    $result_check_category = $stmt_check_category->get_result();
    if ($result_check_category->num_rows > 0) {
        while($value = $result_check_category->fetch_assoc()) {
            $ids = $value['categoryIds'];
            $titles = $value['categoryTitles'];
            if (!in_array($ids, $tempCatgArray)) {
                $tempCatgArray[$ids] = $titles;
            }
        }
    }
}
if (isset($tempLibsArr)) {
    $totalDisplayed = count($tempLibsArr);
}
$_SESSION['prev_loc'] = "Library/core/list.php?filter=" . $FilterReq;
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
    <title>Library List</title>
</head>
<body class="wh100p">
    <img src="../../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity05 z1">
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
                <input type="text" name="ids" placeholder="<?php if(empty($targetIds)) {?>search software...<?php } else {echo $targetIds;};?>" id="searchbox" class="pad-s-s bg-transparent c-black border-none" tabindex="1">
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
    <div class="posr sideMg w70 flex fld gap10 z15">
        <div class="posr pad-s-v pad-n-s w100p flex bg-def-1 box-shad-black-1 border-purple">
            <p class="posr txt-n c-lightgray gap5">Sort by 
                <a class="c-lightpurple hover-text-white" href="list.php?filter=none">newest</a>
                <a class="c-lightpurple hover-text-white" href="list.php?filter=oldtonew">oldest</a>
                <a class="c-lightpurple hover-text-white" href="list.php?filter=az">A-Z</a>
                <a class="c-lightpurple hover-text-white" href="list.php?filter=za">Z-A</a>
            </p>
            <div class="posr leftMg flex txt-n c-white gap5">
                <?php
                if ($pages > 1) {
                ?>
                <a class="posr pad-m-s txt-n c-white hover-text-blue" href="list.php?filter=<?php echo $FilterReq;?>&page=<?php echo $pages - 1;?>"><<</a>
                <?php
                } else {
                ?>
                <p class="posr pad-m-s txt-n c-gray"><<</p>
                <?php
                }
                ?>
                <p class="posr pad-m-s txt-n c-white bg-half-gray"><?php echo $pages;?></p>
                <?php
                if ($totalDisplayed == $displayLimit && $pages - 1 * $displayLimit < $totalCount - 1) {
                ?>
                <a class="posr pad-m-s txt-n c-white hover-text-blue" href="list.php?filter=<?php echo $FilterReq;?>&page=<?php echo $pages + 1;?>">>></a>
                <?php
                } else {
                ?>
                <p class="posr pad-m-s txt-n c-gray">>></p>
                <?php
                }
                ?>
                <p class="posr pad-sl txt-n c-lightgray border-l">showing out <?php echo $totalDisplayed;?> of <?php echo $totalCount;?></p>
            </div>
        </div>
    </div>
    <section class="bottomMg-s10 sideMg pad-s w70 minh70 flex fld border-custom-l-s border-custom-r-s gap5 z2" id="softwarelist">
<?php
switch ($FilterReq) {
    case 'none':
?>
        <h2 class="pad-sb w100p">New Released Software</h2>
<?php
        break;
    case 'oldtonew':
?>
        <h2 class="pad-sb w100p">Listed Software Sorted from 'Oldest'</h2>
<?php
        break;
    case 'az':
?>
        <h2 class="pad-sb w100p">Listed Software Sorted from 'A' to 'Z'</h2>
<?php
        break;
    case 'za':
?>
        <h2 class="pad-sb w100p">Listed Software Sorted from 'Z' to 'A'</h2>
<?php
        break;
    case 'search':
        if ($nolibs == false) {
?>
        <h2 class="pad-sb w100p">Listed Software Named '<?php echo $targetIds;?>'</h2>
<?php
        }
        break;
    default;
        break;
}
if ($nolibs == true) {
    if ($FilterReq === "search") {
?>
    <h2 class="pad-n w100p txtc txt-n">can't find '<?php echo $targetIds;?>' published on the list</h2>
<?php
    } else {
?>
    <h2 class="pad-n w100p txtc txt-n">no collection found</h2>
<?php
    }
} else {
    foreach ($tempLibsArr as $id => $value) {
        $ids = $value['libsIds'];
        $libsPublisher = $value['libsPublisher'];
        $banners = $value['libsBanners'];
        $titles = $value['libsTitles'];
        $Desc = $value['libsDesc'];
        $addedDates = $value['addedDates'];
        $cltNumbs = $value['cltNumbs'];
        $libscatg = $value['libsCategorys'];
        $catgList = $tempCatgArray[$libscatg] ?? null;
?>
        <div class="posr pad-s w100p flex gap5 blurbg box-shad-black-1 border-purple hover-white z3">
            <img src="../libsImg/<?php echo $libsPublisher . "/" . $banners;?>" alt="<?php echo $banners;?>" class="h10 r16-9 coverfit">
            <div class="leftMg-s10 h100p flex fld">
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
            <div class="leftMg h100p flex fld">
                <p class="topMg leftMg txt-s c-semiwhite"><?php echo $addedDates;?></p>
            </div>
            <a href="view.php?type=clts&ids=<?php echo $ids;?>" class="link-cover hover-white">.</a>
        </div>
<?php
    };
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
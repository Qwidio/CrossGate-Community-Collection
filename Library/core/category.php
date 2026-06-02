<?php
require_once '../../processes/database.php';
$errors = array();
$signed = false;
$noCtg = false;
if (isset($_GET['filter'])) {
    $FilterReq = $_GET['filter']; 
} else {
    $FilterReq = "none";
}
$_SESSION['prev_loc'] = "Library/core/category.php";
if (isset($_SESSION['profileTags'])) {
    $signed = true;
    $aidis = $_SESSION['profileTags'];
}
if (isset($_GET['filter'])) {
    $FilterReq = $_GET['filter']; 
} else {
    $FilterReq = "none";
}
if (isset($_GET['ids'])) {
    $targetIds = $_GET['ids'];
    if($targetIds != "") {
        $targetIds = htmlspecialchars($targetIds, ENT_QUOTES, 'UTF-8');
        $_SESSION['prev_loc'] = "Library/core/category.php?filter=" . $FilterReq . "&ids=" . $targetIds;
    } else {
        $FilterReq = "none";
        $targetIds = null;
    }
}
switch ($FilterReq) {
    case 'none':
            $check_category = $connects->prepare("SELECT categoryIds, categoryTitles FROM categorys WHERE categoryState = 'publics' ORDER BY categoryTitles ASC;");
        break;
    case 'search':
        $check_category = $connects->prepare("SELECT categoryIds, categoryTitles FROM categorys WHERE categoryState = 'publics' AND categoryTitles LIKE '%$targetIds%' ORDER BY categoryTitles ASC;");
        break;
    default:
        $_SESSION['corsmsg'] = "Unknown filter";
        header ('location: ../../');
        exit;
        break;
}
$tempCtgArr = array();
$check_category->execute();
$result_check_category = $check_category->get_result();
if ($result_check_category->num_rows > 0) {
    while ($value = $result_check_category->fetch_assoc()) {
        $ctgids = $value['categoryIds'];
        $titles = $value['categoryTitles'];
        if (!in_array($ctgids, $tempCtgArr)) {
            $tempCtgArr[$ctgids] = [
            "categoryIds"        => "$ctgids",
            "categoryTitles"    => "$titles"
            ];
        }
    }
} else {
    $noCtg = true;
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
    <title>Category list</title>
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
            <!-- search bar -->
            <form id="SearchBar" class="posr vertiMg flex gap5 trs500ms bg-white border-1 bora-s" action="category.php">
                <input type="text" name="ids" placeholder="<?php if(empty($targetIds)) {?>search category...<?php } else {echo $targetIds;};?>" id="searchbox" class="pad-s-s bg-transparent c-black border-none" tabindex="1">
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
    <div class="posr w100p r4-1 flex fld acjc bg-3 border-1">
        <h2 class="w100p txtc txt-30 bold">CATEGORY</h2>
        <p class="w100p txtc txt-s">Where everything must be categorized</p>
    </div>
<?php
if ($noCtg == true) {
?>
    <section class="topMg-5 bottomMg-10 w100p flex">
    <h2 class="sideMg pad-sb w100p txtc">No Category listed with name '<?php echo $targetIds;?>'</h2>
<?php
} else {
?>
    <section class="topMg-5 bottomMg-10 w100p flex wrap acjc gap">
<?php
}
foreach ($tempCtgArr as $id => $data) {
    $ctgid = $data['categoryIds'];
    $ctgtitles = $data['categoryTitles'];
    ?>
    <div class="posr w20p r16-9 flex acjc bg-1 border-1 bora-s">
        <p class="txtc txt-n semibold"><?php echo $ctgtitles;?></p>
        <a href="view.php?type=category&ids=<?php echo $ctgid;?>" class="link-cover hover-white">.</a>
    </div>
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
<script src="../../scriptstuff/script.js"></script>
<script src="../../scriptstuff/alert.js"></script>
</body>
</html>
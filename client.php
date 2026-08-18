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

$totalonline = 0;
$totalplaying = 0;
$curdt = date('Y/m/d');
$onlineDefine = date('d/m/Y H:i:s', strtotime('-120 seconds'));
$getStats = $connects->prepare("SELECT isRunningClts FROM sessionlogs WHERE lastlogs > ? AND expirationDate > ? ;");
$getStats->bind_param("ss", $onlineDefine, $curdt);
$getStats->execute();
$result_getStats = $getStats->get_result();
if ($result_getStats->num_rows > 0) {
    while($data = $result_getStats->fetch_assoc()) {
        $totalonline++;
        if ($data["isRunningClts"] == 1) {
            $totalplaying++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="img/cgcclogotrsp.ico" type="image/x-icon">
    <link rel="stylesheet" href="styling/pallate.css">
    <link rel="stylesheet" href="styling/footer.css">
    <link rel="stylesheet" href="styling/Mindex.css">
    <link rel="stylesheet" href="styling/slides.css">
    <title>Download Launcher / CGCC</title>
</head>
<body class="bg-prf-1">
    <!-- nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="index.php" class="link-cover">.</a>
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
    </div>
    <div class="posr sideMg bottomMg-10 w75p maxw100 flex fld bg-transparent z4">
        <div class="posr topMg-10 w100p h70 flex flex-r">
            <img src="img/ilstcgcc1.png" class="posr h50 r16-9 containfit bg-transparent" alt="">
            <div class="posr w50p h60 flex fld gap5">
                <img src="img/cgcc_logos_widetmp.png" class="posr topMg rightMg h10 r16-9 containfit" alt="">
                <h2 class="posr topMg-s5 w100p txt-l">Launch and Manage from one place.</h2>
                <div class="posr topMg-s5 w100p flex ovh">
                    <div class="posr w50p flex gap5">
                        <span style="width:10px; height:10px; border-radius:50%;" class="posr vertiMg bgc-blue c-trsp">.</span>
                        <p class="posr txt-n c-gray">Online</p>
                    </div>
                    <div class="posr w50p flex gap5">
                        <span style="width:10px; height:10px; border-radius:50%;" class="posr vertiMg bgc-green c-trsp">.</span>
                        <p class="posr txt-n c-gray">Playing & Vibing</p>
                    </div>
                </div>
                <div class="posr w100p flex">
                    <p class="posr w50p txt-b txtnowrap"><?php echo $totalonline;?></p>
                    <p class="posr w50p txt-b txtnowrap"><?php echo $totalplaying;?></p>
                </div>
                <div class="posr topMg-s5 bottomMg w100p flex fld gap5">
                    <a href="https://github.com/Qwidio/CrossGate-Community-Collection/releases/download/v1.0.0/cgcc_v1.0.0.zip" class="posr rightMg pad-b-s pad-s-v flex acjc gap10 bgc-blue txt-b txtc c-white box-shad-black-1 border-purple bora-s hover-ltr-blue ovh z4">
                        Download Launcher
                        <img src="img/logo-windows.svg" class="posr leftMg-s10x icon-t containfit z4">
                    </a>
                    <p class="posr w75p txt-s c-gray">
                        current version only works on windows, you can build the launcher for specific OS from 
                        <a href="https://github.com/Qwidio/CrossGate-Community-Collection" class="posr txt-s c-orange hover-text-blue">the source</a>
                    </p>
                </div>
            </div>
        </div>
        <!-- forum -->
        <div class="posr topMg-10 w100p flex acjc">
            <div class="posr pad-s-s w40p h40 flex fld gap10">
                <h2 class="posr topMg txt-l">Manage & Earn Badges</h2>
                <div class="posr bottomMg flex fld gap5">
                    <p class="posr w75p txt-n">
                        Download, Update, Launch and Earn collection badges easily on one place
                    </p>
                    <a href="TS/forum/dashboard.php" class="posr pad-s-v w50p txt-b bold c-blue hover-text-white ovh z4">
                        Open Forum ->
                    </a>
                </div>
            </div>
            <img src="img/ilstcgcc2.png" class="posr h40 r16-9 containfit bg-transparent" alt="">
        </div>
        <!-- create -->
        <div class="posr topMg-10 w100p flex acjc">
            <img src="img/ilstcgcc3.png" class="posr h40 r16-9 containfit bg-transparent" alt="">
            <div class="posr pad-s-s w40p h40 flex fld gap10">
                <h2 class="posr topMg txt-l">Publish your Software/Game</h2>
                <div class="posr bottomMg flex fld gap5">
                    <p class="posr w75p txt-n">
                        CGCC provide tools that help developers intergrate their sofware/game and Groups-Flow Management tools for the publishers to get the most out of distributing on our platform with no fees! 
                    </p>
                    <a href="documentation/groupspublishing.php" class="posr pad-s-v w50p txt-b bold c-blue hover-text-white ovh z4">
                        learn More ->
                    </a>
                </div>
            </div>
        </div>
        <div class="posr topMg-10 bottomMg-10 h80 flex acjc" id="demo">
            <iframe class="posr h100p r16-9" src="https://www.youtube.com/embed/4p1R0SJKGnc?si=Sq3OJhWMerdutix8" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
    </div>


    <?php include_once 'footer.php';?>
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
<?php
require_once '../processes/database.php';
$errors = array();
$root_route = "../";
if (isset($_SESSION['profileTags'])) {
    require_once '../secureSession.php';
    require_once '../Groups/ReAuth.php';
    $aidis = $_SESSION['profileTags'];
}
if (isset($_SESSION['GroupsToken'])) {
    $gToken = $_SESSION['GroupsToken'];
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
    <title>Cookie</title>
</head>
<body class="minh100 ovh-s z1" id="intro">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity05 z1">
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="../img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="../index.php" class="link-cover">.</a>
        </div>
        <div class="posr w60p flex gap-s">
            <div class="posr pad-s flex fld acjc">   
                <h2 class="txt-n txtc semibold">BROWSE</h2>
                <a href="../Library/core/list.php" class="link-cover">.</a>
            </div>
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
                <a href="../Groups/index.php" class="link-cover">.</a>
            </div>
<?php
    }
}
?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="../TS/forum/dashboard.php" class="link-cover">.</a>
            </div>
        </div>
<?php
if (isset($aidis)) {
    $inGroups = false;
    if (isset($_SESSION['GroupsToken']) && $_SESSION['gids']) {
        $check_orgs = $connects->prepare("SELECT og_identification FROM groupaccess WHERE profileTags = ? AND og_identification = ?;");
        $check_orgs->bind_param("ss", $aidis, $_SESSION['gids']);
        $check_orgs->execute();
        $result_check_orgs = $check_orgs->get_result();
        if ($result_check_orgs->num_rows > 0) {
?>
            <div class="leftMg flex acjc gap10">
                <p class="posr pad-n-s pad-s-v txtc txt-n bg-3 border-1 bora-s">Open Dashboard
                    <a href="../Groups/manage.php" class="link-cover">.</a>
                </p>
            </div>
<?php
        }
        $inGroups = true; 
    }
}
?>
    </div>
    <div class="posr topMg pad-n-v w100vh minw50 maxw100 flex spacebetween z4">
        <a href="privacypolicy.php" class="rightMg pad-s-v pad-n-s txt-n bg-half-gray c-white border-1 hover-white">Privacy Policy</a>
        <a href="copyright.php" class="leftg pad-s-v pad-n-s txt-n bg-half-gray c-white border-1 hover-white">Copyright</a>
    </div>
    <div class="posr pad-n w100vh minw50 maxw100 bg-half-gray blurbg flex fld ovh-s gap10 z4">
        <h2 class='posr rightMg txt-n txtnowrap ovh'>Third Party Cookies</h2>
        <h3 class='posr rightMg txt-s txtc txtnowrap'>Cookie used by third party website</h3>
        <div class="posr topMg-s10 pad-s w100p flex fld bg-half-gray box-shad-black-1 border-purple bora-s gap5">
            <h2 class='posr rightMg txt-n txtnowrap ovh'>Youtube</h2>
            <h3 class='posr rightMg txt-s txtc txtnowrap'>Cookies placed by embedded YouTube players within CGCC. Subject to <a href="https://policies.google.com/privacy" class="posr txt-s c-lightpurple hover-text-white">Google's Privacy Policy</a></h3>
        </div>
    </div>
    <div class="posr bottomMg pad-n w100vh minw50 maxw100 bg-half-gray blurbg flex fld ovh-s gap10 z4">
        <h2 class='posr rightMg txt-n txtnowrap ovh'>Necessary Cookies</h2>
        <h3 class='posr rightMg txt-s txtc txtnowrap'>Cookie used in order to make the site work, so you know why the site broken when you blocked them</h3>
        <div class="posr topMg-s10 pad-s w100p flex fld bg-half-gray box-shad-black-1 border-purple bora-s gap5">
            <h2 class='posr rightMg txt-n txtnowrap ovh'>sessionToken</h2>
            <h3 class='posr rightMg txt-s txtc txtnowrap'>These are used to enable persistent login</h3>
        </div>
        <div class="posr pad-s w100p flex fld bg-half-gray box-shad-black-1 border-purple bora-s gap5">
            <h2 class='posr rightMg txt-n txtnowrap ovh'>corsmsg</h2>
            <h3 class='posr rightMg txt-s txtc txtnowrap'>For sending output message/error between page and component</h3>
        </div>
        <div class="posr pad-s w100p flex fld bg-half-gray box-shad-black-1 border-purple bora-s gap5">
            <h2 class='posr rightMg txt-n txtnowrap ovh'>GroupsToken</h2>
            <h3 class='posr rightMg txt-s txtc txtnowrap'>Intended for groups auth security but unused in the time of writing</h3>
        </div>
    </div>
    <?php include_once '../extra/footers.php';?>
</body>
</html>
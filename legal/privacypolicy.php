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
                    <a href="../Groups/manage.php" class="link-cover hover-white">.</a>
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
        <a href="cookies.php" class="rightMg pad-s-v pad-n-s txt-n bg-half-gray c-white border-1 hover-white">Cookies</a>
        <a href="copyright.php" class="leftg pad-s-v pad-n-s txt-n bg-half-gray c-white border-1 hover-white">Copyright</a>
    </div>
    <div class="posr bottomMg-s10 pad-n w100vh minw50 maxw100 bg-half-gray blurbg flex fld ovh-s gap10 z4">
        <h2 class='posr sideMg txt-n txtc txtnowrap ovh'>Privacy Policy</h2>
        <h3 class='posr w100p txt-n txtjustify'>
        Your privacy is important to us. It is in POROSIVE's interest to respect your privacy regarding any information we may collect from you across our website, plugin, and other services we operate.<br><br>
        We only ask for personal information when we truly need it to provide a service to you. We collect it by fair and lawful means, with your knowledge and consent. We also let you know why we're collecting it and how it will be used.<br><br>
        We only retain collected information for as long as necessary to provide you with your requested service. What data we store, we'll protect within commercially acceptable means to prevent loss and theft, as well as unauthorized access, disclosure, copying, use or modification.<br><br>
        We don't share any personally identifying information publicly or with third-parties, except when required to by law.<br><br>
        Our website may link to external sites that are not operated by us. Please be aware that we have no control over the content and practices of these sites, and cannot accept responsibility or liability for their respective privacy policies.<br><br>
        You are free to refuse our request for your personal information, with the understanding that we may be unable to provide you with some of your desired services.<br><br>
        Your continued use of our website will be regarded as acceptance of our practices around privacy and personal information. If you have any questions about how we handle user data and personal information, feel free to contact us.<br><br>
        This policy is effective as of June 1st 2026.<br>
        </h3>
    </div>
    <?php include_once '../extra/footers.php';?>
</body>
</html>
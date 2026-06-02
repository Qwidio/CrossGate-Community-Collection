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
        <a href="cookies.php" class="leftMg pad-s-v pad-n-s txt-n bg-half-gray c-white border-1 hover-white">Cookies</a>
    </div>
    <div class="posr bottomMg pad-n w100vh minw50 maxw100 bg-half-gray blurbg flex fld ovh-s gap10 z4">
        <h2 class='posr rightMg txt-b bold txtnowrap ovh'>Copyright</h2>
        <h3 class='posr w100p txt-n txtjustify'>
            © 2026 POROSIVE Company. All rights reserved. POROSIVE, POROSIVE logo, CGCC logo are trademarks and/or registered trademarks of POROSIVE Company. All other trademarks are property of their respective owners.
        </h3>
        <h2 class='posr rightMg txt-b bold txtnowrap ovh'>Claims of Copyright Infringement</h2>
        <h3 class='posr w100p txt-n txtjustify'>
            POROSIVE respects the intellectual property rights of others, and we ask that everyone using our internet sites and services do the same.
            If you are a copyright owner, or are authorized to act on behalf of one, or authorized to act under any exclusive right under copyright, report alleged copyright infringements taking place on or through our website by completing the following DMCA Notice and delivering it to us. Identify the copyrighted work that you claim has been infringed, or - if multiple copyrighted works are covered by this Notice - provide a representative list of the copyrighted works that you claim have been infringed.
            <br>
            <br>
            Submitting a claim of copyright infringement is a serious legal matter. Before you proceed, you might consider contacting the individual directly to address the complaint. It might be a simple misunderstanding and possible to address without involving proper legal process.
            <br>
            Be aware that under Section 512(f) of the Digital Millennium Copyright Act, any person who knowingly materially misrepresents that material or activity is infringing may be liable for damages.
        </h3>
        <h3 class='posr w100p txt-n txtjustify'>
            Please follow these steps to file a notice:
            <ol class='posr sideMg w95p pad-n flex fld gap5' type="1">
                <li class='posr txt-n'>Identify the copyrighted work that you claim has been infringed, or - if multiple copyrighted works are covered by this Notice - you may provide a representative list of the copyrighted works that you claim have been infringed.</li>
                <li class='posr txt-n'>Identify the material or link you claim is infringing (or the subject of infringing activity) and to which access is to be disabled, including at a minimum, if applicable, the URL of the link shown on the website or the exact location where such material may be found.</li>
                <li class='posr txt-n'>Provide your full contact details so that we can contact you. These details should include: full name, mailing address, telephone number, and email address.</li>
                <li class='posr txt-n'>Include both of the following statements in the body of the Notice:</li>
                <p class='posr txt-n'>"I hereby state that I have a good faith belief that the disputed use of the copyrighted material is not authorized by the copyright owner, its agent, or the law."</p>
                <p class='posr txt-n'>"I hereby state that the information in this Notice is accurate and, under penalty of perjury, that I am the owner, or authorized to act on behalf of the owner, of the copyright or of an exclusive right under the copyright that is allegedly infringed."</p>
                <li class='posr txt-n'>Provide a statement that the complaining party has a good faith belief the use is not authorized by the copyright owner, or the law.</li>
                <li class='posr txt-n'>Deliver this Notice, with all items completed, to our contact dmca's email</li>
            </ol> 
        </h3>
        <h2 class='posr rightMg txt-b bold txtnowrap ovh'>Counter-Notification Procedures</h2>
        <h3 class='posr w100p txt-n txtjustify'>
            If material from your website was removed or access to it was disabled due to a DMCA complaint, you may submit a counter-notification in which you must include the following:
                <ol class='posr sideMg w95p pad-n flex fld gap5' type="1">
                    <li class='posr txt-n'>Information - This should be enough for us to identify the material that has been removed or to which access has been disabled and the location at which the material appeared before it was removed or access disabled.</li>
                    <li class='posr txt-n'>Contact Details - Provide your name, full mailing address, telephone number, and email address.</li>
                    <li class='posr txt-n'>A statement under penalty of perjury that you have a good faith belief that the material was removed or disabled as a result of mistake or misidentification of the material to be removed or disabled.</li>
                    <li class='posr txt-n'>A statement that you consent to the jurisdiction of Federal District Court for the judicial district in which your address is located (or if your address is outside of the United States, for any judicial district in which you may be found) and that you will accept service of process from the person who provided us with the complaint at issue.</li>
                </ol> 
        </h3>
        <h3 class='posr w100p txt-n txtjustify'>Deliver this Notice, with all items completed, to our dmca's email</h3>
        <h3 class='posr w100p txt-n txtjustify'>Please allow 1-4 business days for an email response. Note that emailing your complaint to other parties such as our Internet Service Provider will not expedite your request and may result in a delayed response due to the complaint not being filed properly.</h3>
        <h2 class='posr rightMg txt-b bold txtnowrap ovh'>Consequences of Infringement</h2>
        <h3 class='posr w100p txt-n txtjustify'>If it is determined that you are a repeat offender, your access to our website may be terminated, with or without notice, and all infringing material removed or access disabled.</h3>
        <h2 class='posr rightMg txt-b bold txtnowrap ovh'>Changes to this DMCA Policy</h2>
        <h3 class='posr w100p txt-n txtjustify'>Please note that this DMCA Policy may be amended from time to time. Any changes will be posted on this page. It is your responsibility to review this DMCA Policy periodically for updates.</h3>
        <h3 class='posr w100p txt-n txtjustify'>This DMCA Policy was last updated on June 1st 2026</h3>
    </div>
    <?php include_once '../extra/footers.php';?>
</body>
</html>
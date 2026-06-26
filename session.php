<?php
require_once 'processes/database.php';
$errors = array();
require_once 'secureSession.php';
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    header ('location: index.php');
    exit;
};
$sessionExist = false;
$allowNewSession = true;
$tempSessions = array();
$check_session = $connects->prepare("SELECT sessiontokens, addrss, osids, expirationDate, lastlogs FROM sessionlogs WHERE profileTags = ?;");
$check_session->bind_param("s", $aidis);
$check_session->execute();
$result_check_session = $check_session->get_result();
if ($result_check_session->num_rows >= 3) {
    $allowNewSession = false;
}
if ($result_check_session->num_rows > 0) {
    while ($value = $result_check_session->fetch_assoc()) {
        $sessToken = $value['sessiontokens'];
        $addrss = $value['addrss'];
        $osids = $value['osids'];
        $expirationDate = $value['expirationDate'];
        $lastlogs = $value['lastlogs'];
        if (!in_array($sessToken, $tempSessions)) {
            $tempSessions[$sessToken] = [
            "sessiontokens" => "$sessToken",
            "addrss" => "$addrss",
            "osids" => "$osids",
            "expirationDate" => "$expirationDate",
            "lastlogs" => "$lastlogs"
            ];
        }
    }
    $sessionExist = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="styling/pallate.css">
    <link rel="stylesheet" href="styling/Mindex.css">
    <link rel="stylesheet" href="styling/footer.css">
    <script>
    function loadSession(ReqstData) {
        const form = document.forms.EDITSESSION;
        const values = ReqstData.dataset;
        Object.keys(values).forEach((key) => {
            if (form[key]) 
                form[key].value = values[key];
        });
    };
    </script>
    <title>Session Manager</title>
</head>
<body>
    <!-- nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="index.php" class="link-cover">.</a>
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
    </div>
    <div class="topMg-5 bottomMg-s10 w88 flex">
        <a href="profile.php?user=self" class="rightMg pad-s-v pad-n-s txt-n bg-half-gray c-white border-1 hover-white">< Back</a>
        <a href="processes/session_add.php" class="leftMg pad-s txt-n bg-half-gray c-white border-1 hover-white"><?php if ($allowNewSession == true) {echo "Add new session";} else { echo "Maximum session allowed";};?></a>
    </div>
<?php
if (isset($_COOKIE['sessionToken'])) {
    $deviceToken = $_COOKIE['sessionToken'];
} else {
    $deviceToken = "none";
}
foreach ($tempSessions as $id => $value) {
    $sessToken = $value['sessiontokens'];
    $addrss = $value['addrss'];
    $osids = $value['osids'];
    $expirationDate = $value['expirationDate'];
    $lastlogs = $value['lastlogs'];
?>
    <div class="posr bottomMg-s10 pad-s-v pad-n-s w88 flex fld bg-half-purple box-shad-white-1 border-purple bora-s gap5 z4">
        <input type="text" class="pad-s-v w100p txt-n txtnowrap bg-transparent border-none ovh" id="<?php echo $sessToken;?>" value="ID: <?php echo $sessToken;?>" disabled>
        <div class="posr w100p flex gap5">
            <p class="posr pad-s-v w30p txt-s txtnowrap ovh">Address: <?php echo $addrss;?><span class="blur-censor">.</span></p>
            <p class="pad-s-v w30p txt-s txtnowrap ovh">Device: <?php echo $osids;?></p>
            <p class="pad-s-v w20p txt-s txtnowrap ovh">Expiry: <?php echo $expirationDate;?></p>
            <p class="pad-s-v w20p txt-s txtnowrap ovh">Last login: <?php echo $lastlogs;?></p>
<?php
    if ($sessToken != $deviceToken) {
?>
            <div class="posr pad-m-v pad-ml icon-ts flex">
                <img src="img/copy.svg" alt="" class="posr wh100p containfit points" onclick="copy('<?php echo $sessToken;?>');">
            </div>
            <div class="posr pad-m icon-ts flex">
                <img src="img/trash-outline.svg" alt="" class="posr wh100p containfit points" onclick="uniDisplaySwitch('deleteDialog'); loadSession(this);" data-token="<?php echo $sessToken;?>">
            </div>
<?php
    } else {
?>
            <p class="pad-s-v w20p txtr txt-s ovh">Currently Used</p>
<?php
    }
?>
        </div>
    </div>
<?php
};
if ($sessionExist == true) {
?>
    <div class="posr w100p minh50 flex acjc">
        <p class="link-cover">.</p>
    </div>
<?php
} else {
?>
    <div class="posr w100p minh60 flex">
        <p class="posr pad-n w100p txtc txt-n">No Session found</p>
    </div>
<?php
}
?>
    <dialog id="deleteDialog" class="posf pad-n c0 pad-b-v minw20 maxh50 dp-none fld bg-1 border-1 bora-s z999">
        <form class="wh100p flex fld" name="EDITSESSION" action="processes/session_out.php" method="post">
            <h2 class="w100p txt-b txtc">Confirm Delete This Session?</h2>
            <input class="hiddeninp" type="text" name="token" hidden>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-red border-1 border-hover-white" type="submit" name="submit" value="YES">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 border-hover-white" onclick="uniDisplaySwitch('deleteDialog')">NO</button>
    </dialog>
    <!-- messages alerter -->
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <?php include_once 'footer.php';?>
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
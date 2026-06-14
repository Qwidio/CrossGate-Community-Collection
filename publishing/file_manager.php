<?php
require_once "../processes/database.php";
if (!isset($_GET['libsids'])) {
    if (!isset($_SESSION['libsids'])) {
        $_SESSION['corsmsg'] = 'Missing the required input';
        header ('location: manage.php');
        exit;
    } else {
        $libsIds = $_SESSION['libsids'];
    };
} else {
    $libsIds = $_GET['libsids'];
}
$allowChanges = false;
$root_route = "../";
require_once "../secureSession.php";
require_once "../Groups/ReAuth.php";
if (isset($_SESSION['resetPass']) && $_SESSION['resetPass'] == true) {
    header ('location: ../Groups/manage.php');
    exit;
}
if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
    $aidis = $_SESSION['profileTags'];
    $gToken = $_SESSION['GroupsToken'];
    $gids = $_SESSION['gids'];
    $ChangerRoles = $_SESSION['roles'];
    if ($ChangerRoles === "founder" || $ChangerRoles === "developer") {
        $allowChanges = true;
    }
    if ($allowChanges == false) {
        $_SESSION['corsmsg'] = "Unpermited access";
        header ('location: manage.php');
        exit;
    }
} else {
    $_SESSION['corsmsg'] = "sign in to access";
    header ('location: ../index.php');
    exit;
}
$check_software = $connects->prepare("SELECT libsPublisher, libsTitles, recspecs, fdrLibs, libsState FROM libslist WHERE libsIds = ? AND libsPublisher = ? ;");
$check_software->bind_param("ss", $libsIds, $gids);
$check_software->execute();
$result_check_software = $check_software->get_result();
if ($result_check_software->num_rows > 0) {
    $publishing = true;
    while ($value = $result_check_software->fetch_assoc()) {
        $libsPublisher = $value['libsPublisher'];
        $libsTitles = $value['libsTitles'];
        if ($gids != $libsPublisher) {
            $_SESSION['corsmsg'] = "Unpermited access";
            header ('location: manage.php');
            exit;
        }
        $fdrLibs = $value['fdrLibs'];
        $targetdir = "../vaults/" . $gids . "/" . $fdrLibs;
        if (!file_exists($targetdir)) {
            $fdrLibs = "";
        }
    }
} else {
    $_SESSION['corsmsg'] = "Inexistent collection";
    header ('location: manage.php');
    exit;
}
$targetdir = "../vaults/" . $gids . "/";
if (!file_exists($targetdir)) {
    mkdir($targetdir, 0777, true);
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
    <script>
        var Selecting = false;
    </script>
    <title>File Manager</title>
</head>
<body class="minh100">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity1 z1">
    <div class="posr w100p flex blurbg border-purple-b z4">
        <div class="posr rightMg w60p flex border-purple-b">
            <div class="posr pad-n flex fld acjc bgc-purple">
                <h2 class="txt-n txtc semibold">DASHBOARD</h2>
                <a href="../Groups/manage.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">PUBLISHES</h2>
                <a href="../publishing/manage.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-white">
                <h2 class="txt-n txtc semibold">FILE MANAGER</h2>
                <a href="#" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="../Groups/profile.php?gids=<?php echo $gids;?>" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../documentation/docs.php" class="link-cover hover-white">.</a>
            </div>
        </div>
        <p class="posr pad-n txt-b hover-red" onclick="alerter('Close.This')">X</p>
    </div>
    <div class="posr pad-m-v pad-s-s w100p flex gap10 blurbg bg-half-gray border-purple-b z4">
        <h3 class="posr pad-m-v pad-s txt-n txtc semibold"><?php echo $libsTitles;?></h3>
        <button onclick="uniDisplaySwitch('uploadFile')" class="posr leftMg pad-m-v pad-s-s txtc bg-blue border-orange points hover-text-black">Upload</button>
        <button onclick="if (Selecting == true){uniDisplaySwitch('confirmActive');}else{alerter('no file selected')};" class="posr pad-m-v pad-s-s txtc bg-def-1 border-orange points hover-text-blue">Set Active</button>
        <button onclick="if (Selecting == true){uniDisplaySwitch('confirmRemove');}else{alerter('no file selected')};" class="posr pad-m-v pad-s-s txtc bgc-red border-orange points hover-text-black">Remove</button>
    </div>
    <div class="posr w100p h90 flex ovh-s z4">
        <div class="posr pad-s blurbg rightMg w75p flex fld gap10 ovh-s">
    <?php
    $fileDir = '../vaults/' . $gids . '/';
    $counts = 1;
    if ($dh = opendir($fileDir)){
        while (($listedfile = readdir($dh)) !== false){
            $tmpFile = basename($listedfile); 
            $tmpPath = $fileDir . strtolower($tmpFile);
            $fileType = pathinfo($tmpPath, PATHINFO_EXTENSION);
            $typeAllow = array('zip', 'php');
            if(in_array($fileType, $typeAllow)) {
                $finalName = $tmpFile;
                if (strlen($tmpFile) > 32) {
                    $finalName = substr_replace($finalName, '...', 35);
                }
    ?>
            <div class="posr pad-s w100p flex bg-half-gray <?php if ($fdrLibs != "" && $tmpFile === $fdrLibs){ ?>box-shad-white-1 border-green<?php }else{ ?>box-shad-black-1 border-purple<?php }; ?> bora-s">
                <input type="checkbox" name="files" id="chk<?php echo $counts;?>" value="<?php echo $tmpFile;?>">
                <?php echo "<span class='posr leftMg-s5 w50p txtnowrap ovh'>" . $finalName . "</span>|<h3 class='posr leftMg-s5 w30p txtc txtnowrap'>" . date("F/d/Y H:i:s", filemtime($tmpPath)) . "</h3>|<h3 class='posr sideMg txtc txtnowrap'>" . $fileType . "</h3>";?>
            </div>
    <?php
                $counts++;
            }
        }
        closedir($dh);
    } 
    if ($counts == 1) {
    ?>
            <div class="posr pad-s w100p flex bora-s"><h3 class='posr w100p txtc txtnowrap'>no file</h3></div>
    <?php
    }
    ?>
        </div>
        <div class="posr w30p minh80 h100p blurbg border-purple-l flex fld gap10 ovh-s">
            <p class="posr topMg-s5 pad-n w100p flex wrap txt-b gap5" >selected<span id="selectedFile">none</span></p>

        </div>
    </div>
    <!-- yes upload dialog -->
    <dialog id="uploadFile" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-orange blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" id="formPost" action="file_proceed.php" method="post" enctype="multipart/form-data">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Post New File</h2>
            <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
            <input class="topMg-s10 inptxt bg-semiwhite bora-s" type="file" accept=".zip" name="zipfile" auto-complete="off" required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Upload">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('uploadFile')">Cancel</button>
    </dialog>
    <!--usually for roll back -->
    <dialog id="confirmActive" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-orange blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" id="formActivate" action="file_proceed.php" method="post" enctype="multipart/form-data">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Confirm to Activate?</h2>
            <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
            <input class="hiddeninp" type="text" name="filenamedata" id="filenamedata" hidden required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Activate">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('confirmActive')">Cancel</button>
    </dialog>
    <!-- should this be allowed? -->
    <dialog id="confirmRemove" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-orange blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" id="formRemove" action="file_proceed.php" method="post" enctype="multipart/form-data">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Confirm to Remove?</h2>
            <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
            <input class="hiddeninp" type="text" name="deletefilenamedata" id="deletefilenamedata" hidden required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Remove">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('confirmRemove')">Cancel</button>
    </dialog>
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script src="../scriptstuff/script.js"></script>
    <script src="../scriptstuff/alert.js"></script>
    <script type="text/javascript">
        function uncheckAllCheckboxes() {
            var checkboxes = document.querySelectorAll('input[type=checkbox]');
            for (var i = 0, length = checkboxes.length; i < length; i++) {
                checkboxes[i].checked = false;
            }
        }
        function manageClick() {
            uncheckAllCheckboxes();
            this.checked = true;
            var getfilename = this.value;
            document.getElementById('selectedFile').innerHTML = getfilename;
            document.getElementById('filenamedata').value = getfilename;
            document.getElementById('deletefilenamedata').value = getfilename;
            Selecting = true;
        }
        function init() {
            var checkboxes = document.querySelectorAll('input[type=checkbox]');
            for (var i = 0, length = checkboxes.length; i < length; i++) {
                checkboxes[i].addEventListener('click', manageClick);
            }
        }
        init();
    </script>
    <?php
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
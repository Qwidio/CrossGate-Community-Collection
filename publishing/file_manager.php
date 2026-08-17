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
$check_software = $connects->prepare("SELECT libsPublisher, libsTitles, recspecs, fdrLibs, rollbacks, detailData, libsState FROM libslist WHERE libsIds = ? AND libsPublisher = ? ;");
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
        $rollbacks = $value['rollbacks'];
        $targetdir = "../vaults/" . $gids . '/' . $libsIds . "/" . $fdrLibs;
        if (!file_exists($targetdir)) {
            $fdrLibs = "";
        }
        $detailData = array();
        $detailData = json_decode($value['detailData'], true);
        $releaseDetail = $detailData["fdrLibs"];
        $rollbackDetail = $detailData["rollbacks"];
        $theme = $detailData["theme"];
    }
} else {
    $_SESSION['corsmsg'] = "Inexistent collection";
    header ('location: manage.php');
    exit;
}
$targetdir = "../vaults/" . $gids . '/' . $libsIds . "/";
if (!file_exists($targetdir)) {
    mkdir($targetdir, 0777, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/cgcclogotrsp.ico" type="image/x-icon">
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
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../documentation/groupspublishing.php" class="link-cover hover-white">.</a>
            </div>
        </div>
        <p class="posr pad-n txt-b hover-red" onclick="alerter('Close.This')">X</p>
    </div>
    <div class="posr pad-m-v pad-s-s w100p flex gap10 blurbg bg-half-gray border-purple-b z4">
        <button onclick="if (Selecting == true){uniDisplaySwitch('confirmActive');}else{alerter('no file selected')};" class="posr pad-m-v pad-s-s txtc bg-def-1 border-orange points hover-text-blue">Set Active</button>
        <button onclick="if (Selecting == true){uniDisplaySwitch('rollbackDialog');}else{alerter('no file selected')};" class="posr pad-m-v pad-s-s txtc bg-def-1 border-orange points hover-text-orange">Set Rollback</button>
        <button onclick="if (Selecting == true){uniDisplaySwitch('confirmRemove');}else{alerter('no file selected')};" class="posr pad-m-v pad-s-s txtc bgc-red border-orange points hover-text-black">Remove</button>
        <button onclick="uniDisplaySwitch('uploadFile')" class="posr leftMg pad-m-v pad-s-s txtc bg-blue border-orange points hover-text-black">Upload</button>
        <button onclick="uniDisplaySwitch('detailDataPanel')" class="posr pad-m-v pad-s-s txtc bgc-orange border-orange points hover-text-black">Release Detail</button>
    </div>
    <div class="posr w100p h90 flex ovh-s z4">
        <div class="posr pad-s blurbg rightMg w75p flex fld gap10 ovh-s">
    <?php
    $fileDir = '../vaults/' . $gids . '/' . $libsIds . '/';
    $counts = 1;
    if ($dh = opendir($fileDir)){
        while (($listedfile = readdir($dh)) !== false){
            $tmpFile = basename($listedfile); 
            $tmpPath = $fileDir . strtolower($tmpFile);
            $fileType = pathinfo($tmpPath, PATHINFO_EXTENSION);
            $typeAllow = array('zip');
            if(in_array($fileType, $typeAllow)) {
                $finalName = $tmpFile;
                if (strlen($tmpFile) > 32) {
                    $finalName = substr_replace($finalName, '...', 35);
                }
    ?>
            <div class="posr pad-s w100p flex <?php if ($rollbacks != "" && $tmpFile === $rollbacks){ ?> bg-half-orange <?php }else{ ?> bg-half-gray <?php }; if ($fdrLibs != "" && $tmpFile === $fdrLibs){ ?>box-shad-white-1 border-green<?php }else{ ?>box-shad-black-1 border-purple<?php }; ?> bora-s">
                <input type="checkbox" name="files" id="chk<?php echo $counts;?>" value="<?php echo $tmpFile;?>">
                <?php echo "<span class='posr leftMg-s5 w50p txtnowrap c-white ovh'>" . $finalName . "</span>|<h3 class='posr leftMg-s5 w30p txtc txtnowrap'>" . date("F/d/Y H:i:s", filemtime($tmpPath)) . "</h3>|<h3 class='posr sideMg txtc txtnowrap'>" . $fileType . "</h3>";?>
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
            <h3 class="posr topMg-s5 pad-s-v pad-n-s txt-b semibold"><?php echo $libsTitles;?></h3>
            <p class="posr pad-n-s w100p flex wrap txt-b gap5">selected<span id="selectedFile">none</span></p>
    <?php
    if ($counts != 1) {
    ?>
            <p class="posr topMg-s5 pad-n-s w100p flex wrap txt-n gap5">active release: <br>
            <span id="selectedFile"><?php echo $fdrLibs;?></span></p>
            <p class="posr pad-n-s w100p flex wrap txt-n gap5">active rollback: <br>
            <span id="selectedFile"><?php echo $rollbacks;?></span></p>
    <?php
    }
    ?>
        </div>
    </div>
    <!-- called panel even though it's a dialog -->
    <dialog id="detailDataPanel" class="posf c0 w100vh minw40 maxw100 maxh100 dp-none fld bg-half-gray blurbg border-1 bora-s ovh-s z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">Detail for Client</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('detailDataPanel');">X</p></div>
        <form class="posr pad-n-v w100p flex fld acjc gap10" id="detailDataForm" action="edit.php" method="post">
            <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
            <div class="w95p flex space-between">
                <div id="releaseDetail" class="pad-s-v w49p flex fld gap10 bg-half-orange box-shad-black-1 bora-s">
                    <h2 class="pad-s-s w100p txt-b">Release</h2>
                    <div class="pad-s-s w100p flex fld">
                        <label for="releaseexecutables">Executable file</label>
                        <input type="text" name="releaseexecutables" class="inptxt" placeholder="name of the executables(include the file extension)" value="<?php echo $releaseDetail['executables'];?>" auto-complete="off" required>
                    </div>
                    <div class="pad-s-s w100p flex fld">
                        <label for="releaseuninst">Uninstaller</label>
                        <input type="text" name="releaseuninst" class="inptxt" placeholder="leave 'none' if the software doesn't use Uninstaller" value="<?php echo $releaseDetail['uninst'];?>" auto-complete="off" required>
                    </div>
                    <div class="pad-s-s w100p flex fld">
                        <label for="releasever">version</label>
                        <input type="text" name="releasever" class="inptxt" placeholder="example: '1.0.0' for version checking and client information display" value="<?php echo $releaseDetail['ver'];?>" auto-complete="off" required>
                    </div>
                </div>
                <div id="rollbackDetail" class="pad-s-v w49p flex fld gap10 bg-half-orange box-shad-black-1 bora-s">
                    <h2 class="pad-s-s w100p txt-b">Rollback</h2>
                    <div class="pad-s-s w100p flex fld">
                        <label for="rollbackexecutables">Executable file</label>
                        <input type="text" name="rollbackexecutables" class="inptxt" placeholder="Include filename with the file extension" value="<?php echo $rollbackDetail['executables'];?>" auto-complete="off" required>
                    </div>
                    <div class="pad-s-s w100p flex fld">
                        <label for="rollbackuninst">Uninstaller</label>
                        <input type="text" name="rollbackuninst" class="inptxt" placeholder="leave 'none' if not using Uninstaller" value="<?php echo $rollbackDetail['uninst'];?>" auto-complete="off" required>
                    </div>
                    <div class="pad-s-s w100p flex fld">
                        <label for="rollbackver">version</label>
                        <input type="text" name="rollbackver" class="inptxt" placeholder="example: '1.0.0'" value="<?php echo $rollbackDetail['ver'];?>" auto-complete="off" required>
                    </div>
                </div>
            </div>
            <div class="pad-s w95p flex fld bg-half-orange box-shad-black-1 bora-s">
                <label for="theme">Theme</label>
                <select name="theme" class="inpselect" required>
                    <option name="theme" value="dark" required <?php if ($theme === "dark") { ?>selected<?php };?>>dark</option>
                    <option name="theme" value="light" required <?php if ($theme === "light") { ?>selected<?php };?>>light</option>
                </select>
            </div>
            <button class="pad-s-v w95p txt-n txtc bg-green box-shad-black-1 border-1 bora-s border-hover-white" type="submit" name="submit" value="ChangeDetail">Save Changes</button>
        </form>
    </dialog>
    <!-- upload dialog -->
    <dialog id="uploadFile" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-orange blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" id="formPost" action="file_proceed.php" method="post" enctype="multipart/form-data">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Post New File</h2>
            <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
            <input class="hiddeninp" type="text" name="request" value="Upload" hidden required>
            <input class="topMg-s10 inptxt bg-semiwhite bora-s" type="file" accept=".zip" name="zipfile" required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Upload">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('uploadFile')">Cancel</button>
    </dialog>
    <!-- switch active release-->
    <dialog id="confirmActive" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-orange blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" id="formActivate" action="file_proceed.php" method="post" enctype="multipart/form-data">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Confirm to Activate?</h2>
            <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
            <input class="hiddeninp" type="text" name="request" value="Activate" hidden required>
            <input class="hiddeninp" type="text" name="filenamedata" id="filenamedata" hidden required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Activate">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('confirmActive')">Cancel</button>
    </dialog>
    <!-- roll backs -->
    <dialog id="rollbackDialog" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-orange blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" id="confirmRollback" action="file_proceed.php" method="post" enctype="multipart/form-data">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Confirm to set as rollback?</h2>
            <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
            <input class="hiddeninp" type="text" name="request" value="Rollback" hidden required>
            <input class="hiddeninp" type="text" name="rollbackfilename" id="rollbackfilename" hidden required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Switch">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('confirmActive')">Cancel</button>
    </dialog>
    <!-- should this be allowed? -->
    <dialog id="confirmRemove" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-orange blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" id="formRemove" action="file_proceed.php" method="post" enctype="multipart/form-data">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Confirm to Remove?</h2>
            <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
            <input class="hiddeninp" type="text" name="request" value="Remove" hidden required>
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
            document.getElementById('rollbackfilename').value = getfilename;
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
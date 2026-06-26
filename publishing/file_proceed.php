<?php
require_once '../processes/database.php';
if (!isset($_POST['submit']) || !isset($_POST['libsids'])) {
    $_SESSION['corsmsg'] = 'Missing required input';
    header ('location: manage.php');
    exit;   
}
$errors = '';
$allowChanges = false;
$root_route = "../";
require_once '../secureSession.php';
require_once '../Groups/ReAuth.php';
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
    $_SESSION['corsmsg'] = "denied access";
    header ('location: ../index.php');
    exit;
}
$libsIds = $_POST['libsids'];
$initReq = $_POST['submit'];
if ($initReq === "Upload" && isset($_FILES["zipfile"]["name"])) {
    $targetdir = "../vaults/" . $gids . "/";
    if (!file_exists($targetdir)) {
        mkdir($targetdir, 0777, true);
    }
    $tempZip = basename($_FILES["zipfile"]["name"]);
    $tarfilepath = $targetdir . strtolower($tempZip);
    $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
    $allowTypes = array("zip");
    if(!in_array($fileType, $allowTypes)) {
        $_SESSION['corsmsg'] = 'please upload your file in .zip format';
        header ('location: manage.php');
        exit;
    }
    // if($_FILES["zipfile"]["size"] === UPLOAD_ERR_INI_SIZE) {
    if($_FILES["zipfile"]["size"] > 524288000) { // ~500MB
        $_SESSION['corsmsg'] = "exceeding file size limit ";
        header ('location: manage.php');
        exit;
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
        <title>proceeding data</title>
    </head>
    <body class="wh100p minh100 ovh">
        <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity1 z1">
        <h2 class="posr autoMg blurbg txtc txt-b z4">Uploading file, please wait...</h2>
    </body>
    </html>
    <?php
    if(in_array($fileType, $allowTypes)) {
        $randKey = bin2hex(random_bytes(4));
        $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempZip);
        $createfromformat = DateTime::createFromFormat('Y/m/d', date('Y/m/d'));
        $unixdate = $createfromformat->getTimestamp();
        $tempZip = $unixdate . '_' . $libsIds . '_' . $randKey . '_' . $clean_name;
        $tempPath = $_FILES["zipfile"]["tmp_name"];
        $targetPath = $targetdir . $tempZip;
        if(move_uploaded_file($tempPath, $targetPath)) {
            chmod($targetPath, 0644);
            $final_file = $tempZip;
            $stmt_Post = $connects->prepare("UPDATE libslist SET fdrLibs = ? WHERE libsIds = ?");
            $stmt_Post->bind_param("ss", $final_file, $libsIds);
            if($stmt_Post->execute()){
                $_SESSION['corsmsg'] = 'file uploaded';
                $_SESSION['libsids'] = $libsIds;
                $stmt_Post->close();
                header ('location: file_manager.php');
                exit;
            } else {
                $_SESSION['corsmsg'] = "failed to upload " . $tempZip . ". " . $stmt_Post->error;
                $_SESSION['libsids'] = $libsIds;
                $stmt_Post->close();
                header ('location: manage.php');
                exit;
            };
        } else {
            $_SESSION['corsmsg'] = "An error occured when uploading $tempZip";
            $_SESSION['libsids'] = $libsIds;
            header ('location: manage.php');
            exit;
        };
    } else {
        $_SESSION['corsmsg'] = "please upload your file in .zip format";
        $_SESSION['libsids'] = $libsIds;
        header ('location: manage.php');
        exit;
    };
} else if ($initReq === "Activate" && isset($_POST["filenamedata"])) {
    $newFile = $_POST["filenamedata"];
    $check_existing_collection = $connects->prepare("SELECT libsIds, libsPublisher, libsTitles, libsState FROM libslist WHERE libsIds = ? AND fdrLibs = ? ;");
    $check_existing_collection->bind_param("ss", $gids, $newFile);
    $check_existing_collection->execute();
    $result_check_existing_collection = $check_existing_collection->get_result();
    if ($result_check_existing_collection->num_rows > 0) {
        $otherLibsIds = $value['libsIds'];
        $otherlibsTitles = $value['libsTitles'];
        if ($libsIds === $otherLibsIds) {
            $_SESSION['corsmsg'] = "file used in other collection: " . $otherlibsTitles;
            header ('location: manage.php');
            exit;
        }
    }
    $check_existing_collection->close();
    $check_collection = $connects->prepare("SELECT libsPublisher, libsTitles, fdrLibs, libsState FROM libslist WHERE libsIds = ? AND libsPublisher = ? ;");
    $check_collection->bind_param("ss", $libsIds, $gids);
    $check_collection->execute();
    $result_check_collection = $check_collection->get_result();
    if ($result_check_collection->num_rows > 0) {
        $publishing = true;
        while ($value = $result_check_collection->fetch_assoc()) {
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
    if ($newFile === $fdrLibs) {
        $_SESSION['corsmsg'] = "File's already activated";
        header ('location: manage.php');
        exit;
    }
    $targetdir = "../vaults/" . $gids . "/";
    $tarfilepath = $targetdir . $newFile;
    if (!file_exists($tarfilepath)) {
        $_SESSION['corsmsg'] = "Cannot find the selected file";
        header ('location: manage.php');
        exit;
    }
    $stmt_activate = $connects->prepare("UPDATE libslist SET fdrLibs = ? WHERE libsIds = ?");
    $stmt_activate->bind_param("ss", $newFile, $libsIds);
    if($stmt_activate->execute()){
        $_SESSION['corsmsg'] = 'selected file active';
        $_SESSION['libsids'] = $libsIds;
        $stmt_activate->close();
        header ('location: file_manager.php');
        exit;
    } else {
        $_SESSION['corsmsg'] = "failed to activate " . $newFile . ". " . $stmt_activate->error;
        $_SESSION['libsids'] = $libsIds;
        $stmt_activate->close();
        header ('location: file_manager.php');
        exit;
    };
} else if ($initReq === "Remove" && isset($_POST["deletefilenamedata"])) {
    $requestedFile = $_POST["deletefilenamedata"];
    $check_software = $connects->prepare("SELECT libsPublisher, libsTitles, fdrLibs, libsState FROM libslist WHERE libsIds = ? AND libsPublisher = ? ;");
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
    if ($requestedFile === $fdrLibs) {
        $_SESSION['corsmsg'] = "File's currently active, activate another before removing";
        header ('location: manage.php');
        exit;
    }
    $targetdir = "../vaults/" . $gids . "/";
    $tarfilepath = $targetdir . $requestedFile;
    if (!file_exists($tarfilepath)) {
        $_SESSION['corsmsg'] = "Cannot find the selected file";
        header ('location: manage.php');
        exit;
    }
    $old = getcwd(); // Save the current directory
    chdir($targetdir);
    if(unlink($requestedFile)){
        chdir($old); // Restore the old working directory
        $_SESSION['corsmsg'] = 'selected file removed';
        $_SESSION['libsids'] = $libsIds;
        header ('location: file_manager.php');
        exit;
    } else {
        chdir($old);
        $_SESSION['corsmsg'] = "failed to remove " . $requestedFile . ". ";
        $_SESSION['libsids'] = $libsIds;
        header ('location: file_manager.php');
        exit;
    };
} else {
    $_SESSION['libsids'] = $libsIds;
    header ('location: file_manager.php');
    exit;
};

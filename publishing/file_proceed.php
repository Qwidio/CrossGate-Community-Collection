<?php
require_once '../processes/database.php';
if (!isset($_POST['request']) || !isset($_POST['libsids'])) {
    $_SESSION['corsmsg'] = "Missing required input " . $_POST['libsids'] . $_POST['request'] . " .";
    header ('location: manage.php');
    exit;   
}if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $_SESSION['corsmsg'] = "The uploaded file exceeds the server's upload limit.";
    header("Location: manage.php");
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
$initReq = $_POST['request'];
if ($initReq === "Upload" && isset($_FILES["zipfile"]["name"])) {
    $targetdir = "../vaults/" . $gids . '/' . $libsIds . "/";
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
        <link rel="shortcut icon" href="../img/cgcclogotrsp.ico" type="image/x-icon">
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
    $check_existing_collection = $connects->prepare("SELECT libsIds, libsTitles FROM libslist WHERE libsPublisher = ? AND fdrLibs = ? ;");
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
            $targetdir = "../vaults/" . $gids . '/' . $libsIds . "/" . $fdrLibs;
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
    $targetdir = "../vaults/" . $gids . '/' . $libsIds . "/";
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
}  else if ($initReq === "Rollback" && isset($_POST["rollbackfilename"])) {
    $newFile = $_POST["rollbackfilename"];
    $check_existing_collection = $connects->prepare("SELECT libsIds, libsTitles FROM libslist WHERE libsPublisher = ? AND rollbacks = ? ;");
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
    $check_collection = $connects->prepare("SELECT libsPublisher, libsTitles, rollbacks, libsState FROM libslist WHERE libsIds = ? AND libsPublisher = ? ;");
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
            $rollbacks = $value['rollbacks'];
            $targetdir = "../vaults/" . $gids . '/' . $libsIds . "/" . $rollbacks;
            if (!file_exists($targetdir)) {
                $rollbacks = "";
            }
        }
    } else {
        $_SESSION['corsmsg'] = "Inexistent collection";
        header ('location: manage.php');
        exit;
    }
    if ($newFile === $rollbacks) {
        $_SESSION['corsmsg'] = "File's already activated";
        header ('location: manage.php');
        exit;
    }
    $targetdir = "../vaults/" . $gids . '/' . $libsIds . "/";
    $tarfilepath = $targetdir . $newFile;
    if (!file_exists($tarfilepath)) {
        $_SESSION['corsmsg'] = "Cannot find the selected file";
        header ('location: manage.php');
        exit;
    }
    $stmt_activate = $connects->prepare("UPDATE libslist SET rollbacks = ? WHERE libsIds = ?");
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
    $targetDir = "../vaults/" . $gids . '/' . $libsIds . "/";
    $fileDir = "../vaults/" . $gids . '/' . $libsIds . "/" . $requestedFile;
    if ($dh = opendir($fileDir)){
        if (readdir($dh) !== false){
            $tmpFile = basename($requestedFile); 
            $tmpPath = $fileDir . strtolower($tmpFile);
            $fileType = pathinfo($tmpPath, PATHINFO_EXTENSION);
            $typeAllow = array('zip');
            if(!in_array($fileType, $typeAllow)) {
                $_SESSION['corsmsg'] = 'selected file is not in .zip format';
                header ('location: manage.php');
                exit;
            }
        } else {
            $_SESSION['corsmsg'] = "Cannot find the selected file";
            header ('location: manage.php');
            exit;
        }
        closedir($dh);
    }
    
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
            $targetdir = "../vaults/" . $gids . '/' . $libsIds . "/" . $fdrLibs;
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
        $_SESSION['libsids'] = $libsIds;
        header ('location: file_manager.php');
        exit;
    }

    $old = getcwd(); // Save the current directory
    chdir($targetDir);
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
    $_SESSION['corsmsg'] = "Invalid request " . $_POST['libsids'] . " / " . $_FILES["zipfile"]["name"] . " / " .  $_POST['request'] . " .";
    $_SESSION['libsids'] = $libsIds;
    header ('location: file_manager.php');
    exit;
};

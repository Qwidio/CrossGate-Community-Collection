<?php
require_once '../processes/database.php';
if (!isset($_POST['submit']) || !isset($_POST['libsids'])) {
    $_SESSION['corsmsg'] = 'Missing the required input';
    header ('location: manage.php');
    exit;   
};
$initReq = $_POST['submit'];
$libsIds = $_POST['libsids'];
$libsIds = htmlspecialchars($libsIds, ENT_QUOTES, 'UTF-8');
// checks before check
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
        header ('location: ../Groups/manage.php');
        exit;
    }
} else {
    $_SESSION['corsmsg'] = "denied access";
    header ('location: ../index.php');
    exit;
}
$check_software = $connects->prepare("SELECT libsPublisher, libsVT, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBannersFirst, libsBanners, libsTitles, libsDesc, libsMD, extlink, addedDates, cltNumbs, libsCategorys, libsType, fdrLibs, recspecs, devstats, devstatdesc, libsState FROM libslist WHERE libsIds = ? AND libsPublisher = ? ;");
$check_software->bind_param("ss", $libsIds, $gids);
$check_software->execute();
$result_check_software = $check_software->get_result();
if ($result_check_software->num_rows > 0) {
    $publishing = true;
    while ($value = $result_check_software->fetch_assoc()) {
        $libsPublisher = $value['libsPublisher'];
        $libsVT = $value['libsVT'];
        $libsAttachs = $value['libsAttachs'];
        $libsBanners = $value['libsBanners'];
        $BannersFirst = $value['libsBannersFirst'];
        $libsTitles = $value['libsTitles'];
        $libsDesc = $value['libsDesc'];
        $repolink = $value['repolink'];
        $libsMD = $value['libsMD'];
        $extlink = $value['extlink'];
        $addedDates = $value['addedDates'];
        $cltNumbs = $value['cltNumbs'];
        $libsType = $value['libsType'];
        $libsCatg = $value['libsCategorys'];
        $fdrLibs = $value['fdrLibs'];
        $recspecs = $value['recspecs'];
        $devstats = $value['devstats'];
        $devstatdesc = $value['devstatdesc'];
        $libsState = $value['libsState'];
        $extlink = json_decode($extlink, true);
        $libsBanners = json_decode($libsBanners, true);
        $BannersFirst = json_decode($BannersFirst, true);
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
if ($initReq === "Update") {
    $errors = [];
    if (isset($_POST['libsvt']) && $_POST['libsvt'] != $libsVT) {
        $libsVT = $_POST['libsVT'];
        $libsVT = str_replace('https://', '', $libsVT);
    }
    if (isset($_POST['newtitle']) && $_POST['newtitle'] != $libsTitles) {
        $libsTitles = $_POST['newtitle'];
    }
    if (isset($_POST['desc']) && $_POST['desc'] != $libsDesc) {
        $libsDesc = $_POST['desc'];
    }
    if (isset($_POST['repolink']) && $_POST['desc'] != $repolink) {
        $repolink = $_POST['repolink'];
    }
    if (isset($_POST['md']) && $_POST['md'] != $libsMD) {
        $libsMD = $_POST['md'];
    }
    if (isset($_POST['categoryids']) && $_POST['categoryids'] != $libsCatg) {
        $libsCatg = $_POST['categoryids'];
    }
    if (isset($_POST['cType']) && $_POST['cType'] != $libsType) {
        $libsType = $_POST['cType'];
    }
    if (isset($_POST['devstats']) && $_POST['devstats'] != $devstats) {
        $devstats = $_POST['devstats'];
    }
    if (isset($_POST['devstatdesc']) && $_POST['devstatdesc'] != $devstatdesc) {
        $devstatdesc = $_POST['devstatdesc'];
    }
    $libsVT = htmlspecialchars($libsVT, ENT_QUOTES, 'UTF-8');
    $libsTitles = htmlspecialchars($libsTitles, ENT_QUOTES, 'UTF-8');
    $libsDesc = htmlspecialchars($libsDesc, ENT_QUOTES, 'UTF-8');
    $repolink = htmlspecialchars($repolink, ENT_QUOTES, 'UTF-8');
    $libsMD = htmlspecialchars($libsMD, ENT_QUOTES, 'UTF-8');
    $libsCatg = htmlspecialchars($libsCatg, ENT_QUOTES, 'UTF-8');
    $devstats = htmlspecialchars($devstats, ENT_QUOTES, 'UTF-8');
    $devstatdesc = htmlspecialchars($devstatdesc, ENT_QUOTES, 'UTF-8');
    if (isset($_FILES["newattach"]["name"]) && $_FILES["newattach"]["name"][0] != "") {
        $targetdir = "../Library/libsImg/" . $gids . "/";
        if (!file_exists($targetdir)) {
            mkdir($targetdir, 0777, true);
        }
        $tempAttach = basename($_FILES["newattach"]["name"]);
        $tarfilepath = $targetdir . strtolower($tempAttach);
        $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
        $allowTypes = array("jpg", "svg", "png", "jpeg", "webp");
        if($_FILES["newattach"]["size"] < 5242880) {
            if(in_array($fileType, $allowTypes)) {
                $randKey = bin2hex(random_bytes(8));
                $attach_clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempAttach);
                $tempAttach = $libsIds . '_' . time() . '_' . $randKey . '_' . $attach_clean_name;
                $tempAttPath = $_FILES["newattach"]["tmp_name"];
                $targetAttPath = $targetdir . $tempAttach;
                if(move_uploaded_file($tempAttPath, $targetAttPath)) {
                    chmod($targetAttPath, 0644);
                    $libsAttachs = $tempAttach;
                } else {
                    array_push($errors,"An error occured when uploading $tempAttach ");
                };
            } else {
                array_push($errors,"only jpg, jpeg, png, & webp format are allowed ");
            };
        } else {
            array_push($errors,"exceeding 5MB size limit ");
        }
    }
    $countExtlimit = 1;
    $stopExtCount = false;
    while ($countExtlimit < 10 && $stopExtCount == false) {
        $linkName = "linkname" . $countExtlimit;
        $CurrentExtLink = "extlink" . $countExtlimit;
        $linkName = htmlspecialchars($linkName, ENT_QUOTES, 'UTF-8');
        $CurrentExtLink = htmlspecialchars($CurrentExtLink, ENT_QUOTES, 'UTF-8');
        if (isset($_POST[$CurrentExtLink]) && $_POST[$CurrentExtLink] != "" && isset($_POST[$linkName]) && $_POST[$linkName] != "")  {
            $extLink = [
                $_POST[$linkName] => [$_POST[$CurrentExtLink]],
            ];
            $countExtlimit++;
        } else {
            $stopExtCount = true;
            $countExtlimit = 11;
        }
    }
    $extLink = json_encode($extLink, JSON_UNESCAPED_SLASHES);
    $countlimit = 1;
    $stopCount = false;
    $finalLibsBanners = [];
    foreach ($libsBanners as $bannerValue) {
        $countlimit++;
    }
    while ($countlimit < 10 && $stopCount == false) {
        $BannerIteration = "banners" . $countlimit;
        if (isset($_FILES[$BannerIteration]["name"]) && $_FILES[$BannerIteration]["name"][0] != "") {
            $targetdir = "../Library/libsImg/" . $gids . "/";
            if (!file_exists($targetdir)) {
                mkdir($targetdir, 0777, true);
            }
            $tempBanners = basename($_FILES[$BannerIteration]["name"]);
            $tarfilepath = $targetdir . strtolower($tempBanners);
            $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
            $allowTypes = array("jpg", "svg", "png", "jpeg", "webp");
            if($_FILES[$BannerIteration]["size"] < 6291456) {
                if(in_array($fileType, $allowTypes)) {
                    $randKey = bin2hex(random_bytes(8));
                    $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempBanners);
                    $tempBanners = $countlimit . "_" . time() . '_' . $randKey . '_' . $clean_name;
                    $tempPath = $_FILES[$BannerIteration]["tmp_name"];
                    $targetPath = $targetdir . $tempBanners;
                    if(move_uploaded_file($tempPath, $targetPath)) {
                        chmod($targetPath, 0644);
                        array_push($libsBanners, $tempBanners);
                        $finalLibsBanners = $libsBanners;
                    } else {
                        array_push($errors, "Banner $countlimit: An error occured when uploading banner ");
                    }
                } else {
                    array_push($errors, "Banner $countlimit: only jpg, jpeg, png, & webp format are allowed ");
                }
            } else {
                array_push($errors, "Banner $countlimit:exceeding 6MB size limit ");
            }
            $countlimit++;
        } else {
            $stopCount = true;
            $countlimit = 11;
        }
    }
    if (empty($finalLibsBanners) || $countlimit == 1) {
        $finalLibsBanners = $libsBanners;
    }
    $finalLibsBanners = json_encode($finalLibsBanners, JSON_UNESCAPED_SLASHES);
    $stmt_update_clts = $connects->prepare("UPDATE libslist SET libsVT = ?, libsAttachs = ?, libsBanners = ?, libsTitles = ?, libsDesc = ?, repolink = ?, libsMD = ?, extlink = ?, libsType = ?, libsCategorys = ?, devstats = ?, devstatdesc = ? WHERE libsIds = ? AND libsPublisher = ? ;");
    $stmt_update_clts->bind_param("ssssssssssssss", $libsVT, $libsAttachs, $finalLibsBanners, $libsTitles, $libsDesc, $repolink, $libsMD, $extLink, $libsType, $libsCatg, $devstats, $devstatdesc ,$libsIds ,$gids);
    if($stmt_update_clts->execute()){
        if (!empty($errors)){
            $errors = json_decode($errors,true);
            $err;
            foreach ($errors as $error) {
                $err = $err . $error;
            }
            $_SESSION['corsmsg'] = $libsTitles . ' updated, ' . $err;
        } else {
            $_SESSION['corsmsg'] = 'updated ' . $libsTitles;
        }
        $stmt_update_clts->close();
        header ('location: manage.php?view='.$libsState);
        exit;
    } else {
        $_SESSION['corsmsg'] = 'failed to update ' . $libsTitles . '. ' . $stmt_update_clts->error;
        $stmt_update_clts->close();
        header ('location: manage.php?view='.$libsState);
        exit;
    };
} else if ($initReq === "ARCHIVE" && $libsState != "archived") {
    $stmt_archive = $connects->prepare("UPDATE libslist SET libsState = 'archived' WHERE libsIds = ? AND libsPublisher = ? ;");
    $stmt_archive->bind_param("ss", $libsIds, $gids);
    $stmt_archive->execute();
    if ($stmt_archive->affected_rows > 0) {
        $_SESSION['corsmsg'] = "Successfully archived the collection";
        header('location: manage.php?filter=none&view=archived');
        exit;
    } else {
        $_SESSION['corsmsg'] = "Failed to archive " . $stmt_archive->error;
        header ('location: manage.php');
        exit;
    }
} else if ($initReq === "PUBLISH" && $libsState != "publics") {
    if ($fdrLibs === "") {
        $_SESSION['corsmsg'] = "Upload collection files before publishing";
        header ('location: manage.php');
        exit;
    }
    $stmt_publish = $connects->prepare("UPDATE libslist SET libsState = 'publics' WHERE libsIds = ? AND libsPublisher = ? ;");
    $stmt_publish->bind_param("ss", $libsIds, $gids);
    $stmt_publish->execute();
    if ($stmt_publish->affected_rows > 0) {
        $_SESSION['corsmsg'] = "Successfully Published the collection";
        header('location: manage.php');
        exit;
    } else {
        $_SESSION['corsmsg'] = "Failed to archive " . $stmt_publish->error;
        header ('location: manage.php');
        exit;
    }
} else if ($initReq === "DRAFT" && $libsState != "draft") {
    $stmt_publish = $connects->prepare("UPDATE libslist SET libsState = 'draft' WHERE libsIds = ? AND libsPublisher = ? ;");
    $stmt_publish->bind_param("ss", $libsIds, $gids);
    $stmt_publish->execute();
    if ($stmt_publish->affected_rows > 0) {
        $_SESSION['corsmsg'] = "Successfully moved the collection";
        header('location: manage.php?filter=none&view=draft');
        exit;
    } else {
        $_SESSION['corsmsg'] = "Failed to archive " . $stmt_publish->error;
        header ('location: manage.php');
        exit;
    }
} else {
    header ('location: manage.php');
    exit;
}
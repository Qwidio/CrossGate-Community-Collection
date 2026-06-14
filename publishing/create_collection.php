<?php
require_once '../processes/database.php';
if (!isset($_POST['submit']) || !isset($_POST['title']) || !isset($_POST['desc']) || !isset($_POST['repolink']) || !isset($_POST['categoryids']) || !isset($_POST['cType']) || !isset($_POST['devstats']) || $_FILES['attach']['name'][0] === "") {
    $_SESSION['corsmsg'] = 'Missing the required input';
    header ('location: manage.php');
    exit;   
}
$errors = array();
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
        header ('location: ../Groups/manage.php');
        exit;
    }
} else {
    $_SESSION['corsmsg'] = "denied access";
    header ('location: ../index.php');
    exit;
}

$libsTitles = $_POST['title'];
$libsTitles = htmlspecialchars($libsTitles, ENT_QUOTES, 'UTF-8');
$sanitized = str_replace('%', 'prcn', $libsTitles);
$sanitized = str_replace(' ', '_', $sanitized);
$sanitized = str_replace('/', 'I', $sanitized);
$libsTopics = $sanitized . '_topic_' . bin2hex(random_bytes(8 / 2));
if (isset($_POST['custIds'])) {
    $libsIds = $_POST['custIds'];
} else {
    $libsIds = $sanitized  . bin2hex(random_bytes(8 / 2)) . date('Y/m/d');
}
if (isset($_POST['libsVT'])) {
    $libsVT = $_POST['libsVT'];
    $libsVT = str_replace('https://', '', $libsVT);
    $libsVT = htmlspecialchars($libsVT, ENT_QUOTES, 'UTF-8');
} else {
    $libsVT = "";
}
$libsAttachs = "";
if (isset($_FILES['attach']["name"]) && $_FILES['attach']["name"][0] != "") {
    $targetdir = "../Library/libsImg/" . $gids . "/";
    if (!file_exists($targetdir)) {
        mkdir($targetdir, 0777, true);
    }
    $tempAttach = basename($_FILES['attach']["name"]);
    $tarfilepath = $targetdir . strtolower($tempAttach);
    $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
    $allowTypes = array("jpg", "svg", "png", "jpeg", "webp");
    if($_FILES['attach']["size"] < 5242880) {
        if(in_array($fileType, $allowTypes)) {
            $randKey = bin2hex(random_bytes(8));
            $attach_clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempAttach);
            $tempAttach = $sanitized . '_' . time() . '_' . $randKey . '_' . $attach_clean_name;
            $tempAttPath = $_FILES['attach']["tmp_name"];
            $targetAttPath = $targetdir . $tempAttach;
            if(move_uploaded_file($tempAttPath, $targetAttPath)) {
                chmod($targetAttPath, 0644);
                $libsAttachs = $tempAttach;
            } else {
                array_push($errors,"$tempAttach: An error occured when uploading $tempAttach ");
            };
        } else {
            array_push($errors,"$tempAttach: only jpg, jpeg, png, & webp format are allowed ");
        };
    } else {
        array_push($errors,"$tempAttach: exceeding 5MB size limit ");
    }
}
$libsDesc = $_POST['desc'];
$repolink = $_POST['repolink'];
if (isset($_POST['md'])) {
    $libsMD = $_POST['md'];
} else {
    $libsMD = "";
}
if (isset($_POST['cType'])) {
    $libsType = $_POST['cType'];
} else {
    $libsType = "";
}
$libsCatg = $_POST['categoryids'];
$devstats = $_POST['devstats'];
$devstatdesc = "";
if (isset($_POST['devstatdesc'])) {
    $devstatdesc = $_POST['devstatdesc'];
}
$libsDesc = htmlspecialchars($libsDesc, ENT_QUOTES, 'UTF-8');
$repolink = htmlspecialchars($repolink, ENT_QUOTES, 'UTF-8');
$libsMD = htmlspecialchars($libsMD, ENT_QUOTES, 'UTF-8');
$libsCatg = htmlspecialchars($libsCatg, ENT_QUOTES, 'UTF-8');
$devstats = htmlspecialchars($devstats, ENT_QUOTES, 'UTF-8');
$devstatdesc = htmlspecialchars($devstatdesc, ENT_QUOTES, 'UTF-8');
$countExtlimit = 1;
$extLink = [];
$stopExtCount = false;
while ($countExtlimit < 10 && $stopExtCount == false) {
    $linkName = "linkname" . $countExtlimit;
    $CurrentExtLink = "extlink" . $countExtlimit;
    $linkName = htmlspecialchars($linkName, ENT_QUOTES, 'UTF-8');
    $CurrentExtLink = htmlspecialchars($CurrentExtLink, ENT_QUOTES, 'UTF-8');
    if (isset($_POST[$CurrentExtLink]) && $_POST[$CurrentExtLink] != "" && isset($_POST[$linkName]) && $_POST[$linkName] != "") {
        $extLink = [
            $linkName => [$_POST[$CurrentExtLink]],
        ];
        $countExtlimit++;
    } else {
        $stopExtCount = true;
        $countExtlimit = 11;
    }
}
$extLink = json_encode($extLink, JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styling/pallate.css">
    <link rel="stylesheet" href="../styling/Mindex.css">
    <title>creating collection</title>
</head>
<body class="wh100p minh100 ovh">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity1 z1">
    <h2 class="posr autoMg blurbg txtc txt-b z4">Creating collection, please wait...</h2>
</body>
</html>
<?php
$countlimit = 1;
$libsBanners = [];
$stopCount = false;
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
                $tempBanners = $sanitized . '_' . $countlimit . "_" . time() . '_' . $randKey . '_' . $clean_name;
                $tempPath = $_FILES[$BannerIteration]["tmp_name"];
                $targetPath = $targetdir . $tempBanners;
                if(move_uploaded_file($tempPath, $targetPath)) {
                    chmod($targetPath, 0644);
                    $libsBanners[] = $tempBanners;
                } else {
                    array_push($errors, "Banner $countlimit: An error occured when uploading banner ");
                };
            } else {
                array_push($errors, "Banner $countlimit: only jpg, jpeg, png, & webp format are allowed ");
            };
        } else {
            array_push($errors, "Banner $countlimit:exceeding 6MB size limit ");
        }
        $countlimit++;
    } else {
        $stopCount = true;
        $countlimit = 11;
    }
}
$libsBanners = json_encode($libsBanners);
$stmt_insert_newClts = $connects->prepare("INSERT INTO libslist (libsIds, libsPublisher, libsVT, libsAttachs, libsBanners, libsTitles, libsDesc, repolink, libsMD, extlink, addedDates, cltNumbs, libsType, libsCategorys, libsForum, libsState, devstats, devstatdesc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, 'draft', ?, ?)");
$stmt_insert_newClts->bind_param("ssssssssssssssss", $libsIds, $gids, $libsVT, $libsAttachs, $libsBanners, $libsTitles, $libsDesc, $repolink, $libsMD, $extLink, date('d/m/Y'), $libsType, $libsCatg, $libsTopics, $devstats, $devstatdesc);
if($stmt_insert_newClts->execute()){
    $topicsTitles = $libsTitles . " announcement";
    $topicsDesc = $libsTitles . "announcement topics";
    $stmt_insert_newTopics = $connects->prepare("INSERT INTO topics (topicIds, topicTitles, topicDates, topicContents, topicState, topicAttachs, topicType) VALUES (?, ?, ?, ?, 'Publics', 'empty.png', 'publisherOnly')");
    $stmt_insert_newTopics->bind_param("ssss", $libsTopics, $topicsTitles, date('d/m/Y'), $topicsDesc);
    if($stmt_insert_newTopics->execute()){
        if ($errors != ""){
            $_SESSION['corsmsg'] = $libsTitles . ' created, ' . foreach ($errors as $error) { echo $errors; };
        } else {
            $_SESSION['corsmsg'] = 'created ' . $libsTitles;
        }
        $stmt_insert_newTopics->close();
        header ('location: manage.php?view=draft');
        exit;
    } else {
        $_SESSION['corsmsg'] = 'failed to add topics. ' . $errors . '. ' . $stmt_insert_newTopics->error;
        $stmt_insert_newTopics->close();
        header ('location: manage.php');
        exit;
    };
    $stmt_insert_newClts->close();
} else {
    $_SESSION['corsmsg'] = 'failed to add ' . $libsTitles . '. ' . $stmt_insert_newClts->error;
    $stmt_insert_newClts->close();
    header ('location: manage.php');
    exit;
};
?>

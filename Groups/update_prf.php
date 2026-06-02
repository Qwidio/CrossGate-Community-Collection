<?php
require_once '../processes/database.php';
if (isset($_POST['submit'])) {
    // checks before check
    $root_route = "../";
    require_once '../secureSession.php';
    require_once 'ReAuth.php';
    if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
        $aidis = $_SESSION['profileTags'];
        $gToken = $_SESSION['GroupsToken'];
        $gids = $_SESSION['gids'];
        $ChangerRoles = $_SESSION['roles'];
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    $initReq = $_POST['submit'];
    // updating profile
    if ($initReq === "Update") {
        if ($ChangerRoles === "founder") {
            $allowChanges = true;
        }
        if ($allowChanges == false) {
            $_SESSION['corsmsg'] = "Unpermited access";
            header ('location: manage.php');
            exit;
        }
        $check_groups = $connects->prepare("SELECT logo, names, about, sites FROM ogroup WHERE identification = ? ;");
        $check_groups->bind_param("s", $gids);
        $check_groups->execute();
        $result_check_groups = $check_groups->get_result();
        $result_groups = $result_check_groups->fetch_assoc();
        $final_logo = $result_groups['logo'];
        $final_name = $result_groups['names'];
        $final_about = $result_groups['about'];
        $sites = json_decode($result_groups['sites'], true);
        foreach ($sites as $siteIndex => $siteData) {
            $sitesArr[0] = [
                "site"  => $siteData["site"], 
                "yt"    => $siteData["yt"]
            ];
        }
        if (isset($_FILES["logo"]["name"]) && $_FILES["logo"]["name"][0] != "" && $_FILES['logo'] != $final_logo) {
            $targetdir = "img/" . $gids . "/";
            if (!file_exists($targetdir)) {
                mkdir($targetdir, 0777, true);
            }
            $tempLogo = basename($_FILES["logo"]["name"]);
            $tarfilepath = $targetdir . strtolower($tempLogo);
            $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
            $allowTypes = array("jpg", "svg", "png", "jpeg", "webp", "gif");
            if($_FILES["logo"]["size"] < 5242880) {
                if(in_array($fileType, $allowTypes)) {
                    $randKey = bin2hex(random_bytes(8));
                    $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempLogo);
                    $tempLogo =  time() . '_' . $randKey . '_' . $clean_name;
                    $tempPath = $_FILES["logo"]["tmp_name"];
                    $targetPath = $targetdir . $tempLogo;
                    if(move_uploaded_file($tempPath, $targetPath)) {
                        chmod($targetPath, 0644);
                        $final_logo = $tempLogo;
                    } else {
                        $_SESSION['corsmsg'] = 'An error occured when uploading logo';
                        header('location: manage.php');
                        exit;
                    };
                } else {
                    $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif format are allowed';
                    header('location: manage.php');
                    exit;
                };
            } else {
                $_SESSION['corsmsg'] = 'exceeding 5MB filesize limit';
                header('location: manage.php');
                exit;
            }
        }
        if (isset($_POST['names']) && $_POST['names'] != $final_names) {
            $final_names = $_POST['names'];
        }
        if (isset($_POST['about']) && $_POST['about'] != $final_about) {
            $final_about = $_POST['about'];
        }
        $site = "";
        $yt = "";
        foreach ($sitesArr as $sIndex => $value) {
            $site = $value["site"];
            $yt = $value["yt"];
            if (isset($_POST['site']) && $_POST['site'] != $site) {
                $site = $_POST['site'];
                $site = str_replace('https://', '', $site);
                $site = htmlspecialchars($site, ENT_QUOTES, 'UTF-8');
            }
            if (isset($_POST['yt']) && $_POST['yt'] != $yt) {
                $yt = $_POST['yt'];
                $yt = str_replace('https://', '', $yt);
                $yt = htmlspecialchars($yt, ENT_QUOTES, 'UTF-8');
            }
            $final_sites[0] = [
                "site"    => $site,
                "yt"    => $yt
            ];
        }
        $final_sites = json_encode($final_sites, JSON_UNESCAPED_SLASHES);
        $stmt_update_orgs = $connects->prepare("UPDATE ogroup SET logo = ? , names = ? , about = ? , sites = ? WHERE identification = ? ;");
        $stmt_update_orgs->bind_param("sssss", $final_logo, $final_name, $final_about, $final_sites, $gids);
        $stmt_update_orgs->execute();
        if ($stmt_update_orgs->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Profile data updated";
            header('location: manage.php');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to update " . $stmt_update_orgs->error;
            header ('location: manage.php');
            exit;
        }
    } else if ($initReq === "Change") {
        if ($ChangerRoles === "founder" || $ChangerRoles === "administrator") {
            $allowChanges = true;
        }
        if ($allowChanges == false) {
            $_SESSION['corsmsg'] = "Unpermited access";
            header ('location: manage.php');
            exit;
        }
        if (!isset($_POST['libsids']) || !isset($_POST['ForumTopics'])) {
            $_SESSION['corsmsg'] = "denied request";
            header ('location: manage.php');
            exit;
        }
        $libsIds = $_POST['libsids'];
        $topicIds = $_POST['ForumTopics'];
        $check_topic = $connects->prepare("SELECT topicContents FROM topics WHERE topicIds = ? AND topicState = 'Publics' AND TopicType = 'publisherOnly' ;");
        $check_topic->bind_param("s", $topicIds);
        $check_topic->execute();
        $result_check_topic = $check_topic->get_result();
        if ($result_check_topic->num_rows == 0) {
            $_SESSION['corsmsg'] = "Selected topic does not exist. " . $result_check_topic->error;
            header('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
            exit;
        };
        $vals = $result_check_topic->fetch_assoc();
        $topicdesc = $vals['topicContents'];
        if (isset($_POST['topicdesc']) && $_POST['topicdesc'] != $topicdesc) {
            $topicdesc = $_POST['topicdesc'];
        }
        $stmt_update_topic = $connects->prepare("UPDATE topics SET topicContents = ? WHERE topicIds = ? ;");
        $stmt_update_topic->bind_param("ss", $topicdesc, $topicIds);
        $stmt_update_topic->execute();
        if ($stmt_update_topic->affected_rows > 0) {
            $_SESSION['corsmsg'] = "topic updated";
            header('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to update " . $stmt_update_topic->error;
            header('location: community.php?lIds='.$libsIds.'&lc='.$topicIds);
            exit;
        }

    } else {
        header ('location: manage.php');
        exit;
    };
} else {
    header ('location: ../index.php');
    exit;
};
?>
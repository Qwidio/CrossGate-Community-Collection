<?php
require_once '../processes/database.php';
$root_route = "../";
require_once '../secureSession.php';
if (isset($_POST['submit']) && isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
    $initReq = $_POST['submit'];
    $initReq = htmlspecialchars($initReq, ENT_QUOTES, 'UTF-8');
    if ($initReq === "Update Bio") {
        $check_profile_data = $connects->prepare("SELECT profileBios FROM profiles WHERE profileTags = ? ;");
        $check_profile_data->bind_param("s", $aidis);
        $check_profile_data->execute();
        $result_check_profile_data = $check_profile_data->get_result();
        if ($result_check_profile_data->num_rows == 1) {
            $value = $result_check_profile_data->fetch_assoc();
            $Final_Bios = $value['profileBios'];
        } else {
            $_SESSION['corsmsg'] = "user account does not exists or on a temporary bans";
            header ('location: index.php');
            exit;
        };
        if (isset($_POST['bioedits']) && $_POST['bioedits'] != $Final_Bios) {
            $Final_Bios = $_POST['bioedits'];
        } else if (isset($_POST['bioedits']) && $_POST['bioedits'] === "" && $Final_Bios === "") {
            $_SESSION['corsmsg'] = "no changes detected";
            header ('location: ../settings.php');
            exit;
        } else if (isset($_POST['bioedits']) && $_POST['bioedits'] === $Final_Bios) {
            $_SESSION['corsmsg'] = "no changes detected";
            header ('location: ../settings.php');
            exit;
        } else {
            $Final_Bios = "";
        }
        $Bios = htmlspecialchars($Bios, ENT_QUOTES, 'UTF-8');
        $stmt_bios = $connects->prepare("UPDATE profiles SET profileBios = ? WHERE profileTags = ?");
        $stmt_bios->bind_param("ss", $Final_Bios, $aidis);
        if($stmt_bios->execute()){
            $_SESSION['corsmsg'] = 'Bio successfully updated';
            header ('location: ../settings.php');
            $stmt_bios->close();
            exit;
        }else{
            $_SESSION['corsmsg'] = 'Failed to update bio. ' . $stmt_bios->error;
            header ('location: ../settings.php');
            $stmt_bios->close();
            exit;
        };
    } else if ($initReq === "Change Profile") {
        $check_profile_data = $connects->prepare("SELECT profileAttachs FROM profiles WHERE profileTags = ? ;");
        $check_profile_data->bind_param("s", $aidis);
        $check_profile_data->execute();
        $result_check_profile_data = $check_profile_data->get_result();
        if ($result_check_profile_data->num_rows == 1) {
            $value = $result_check_profile_data->fetch_assoc();
            $final_propic = $value['profileAttachs'];
        } else {
            $_SESSION['corsmsg'] = "user account does not exists or on a temporary bans";
            header ('location: index.php');
            exit;
        };
        if (isset($_FILES["profilepic"]["name"]) && $_FILES["profilepic"]["name"][0] != "" && $_FILES["profilepic"]["size"] > 100) {
            $targetdir = "../zprpic/" . $aidis . "/";
            if (!file_exists($targetdir)) {
                mkdir($targetdir, 0777, true);
            }
            $tempProfilePic = basename($_FILES["profilepic"]["name"]);
            $tarfilepath = $targetdir . strtolower($tempProfilePic);
            $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
            $allowTypes = array("jpg", "svg", "png", "jpeg", "webp", "gif");
            if($_FILES["profilepic"]["size"] < 5242880) {
                if(in_array($fileType, $allowTypes)) {
                    $randKey = bin2hex(random_bytes(8));
                    $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempProfilePic);
                    $createfromformat = DateTime::createFromFormat('Y/m/d', date('Y/m/d'));
                    $unixdate = $createfromformat->getTimestamp();
                    $tempProfilePic =  $unixdate . '_' . $randKey . '_' . $clean_name;
                    $tempPath = $_FILES["profilepic"]["tmp_name"];
                    $targetPath = $targetdir . $tempProfilePic;
                    if(move_uploaded_file($tempPath, $targetPath)) {
                        chmod($targetPath, 0644);
                        $final_profilepic = $tempProfilePic;
                    } else {
                        $_SESSION['corsmsg'] = 'An error occured when uploading image' . $targetdir;
                        header ('location: ../settings.php');
                        exit;
                    };
                } else {
                    $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif format allowed';
                    header ('location: ../settings.php');
                    exit;
                };
            } else {
                $_SESSION['corsmsg'] = 'exceeding 5MB filesize limit';
                header ('location: ../settings.php');
                exit;
            }
        } else {
            $_SESSION['corsmsg'] = 'Invalid profile picture';
            header ('location: ../settings.php');
            exit;
        }
        $stmt_update_profiles = $connects->prepare("UPDATE profiles SET profileAttachs = ? WHERE profileTags = ? ;");
        $stmt_update_profiles->bind_param("ss", $final_profilepic, $aidis);
        $stmt_update_profiles->execute();
        if ($stmt_update_profiles->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Profile data updated";
            header ('location: ../settings.php');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to update " . $stmt_update_profiles->error;
            header ('location: ../settings.php');
            exit;
        }
    } else if ($initReq === "Update Settings" || $initReq === "Save Changes") {
        $totalChanges = 0;
        $changedval = "";
        $check_profile = $connects->prepare("SELECT Badge, mkot, allowInvite FROM profiles WHERE profileTags = ? ;");
        $check_profile->bind_param("s", $aidis);
        $check_profile->execute();
        $result_check_profile = $check_profile->get_result();
        if ($result_check_profile->num_rows == 1) {
            $badges = array();
            $value = $result_check_profile->fetch_assoc();
            $Badge = $value['Badge'];
            $mkot = $value['mkot'];
            $badgeData = json_decode($Badge, true);
            $data = json_decode($mkot, true);
            $markedData = $data['marked'];
            $private = $data['private'];
            $favbadge = $data['favbadge'];
            $themes = $data['themes'];
            $allowInvite = $value['allowInvite'];
        };
        if (isset($_POST["request"]) && $_POST["request"] === "selectBadges") {
            if (isset($_POST["selectedBadges"])) {
                $newfavbadge = $_POST["selectedBadges"];
            }
            foreach ($badgeData as $badgeIndex => $badgeval) {
                $badges[$badgeIndex] = $badgeIndex;
            }
            if ($newfavbadge != $favbadge && in_array($newfavbadge, $badges)) {
                $favbadge = $newfavbadge;
                $totalChanges++;
            }
        }
        if (isset($_POST["request"]) && $_POST["request"] === "selectThemes") {
            if (isset($_POST["selectedThemes"])) {
                $newThemes = $_POST["selectedThemes"];
            }
            if ($newThemes != $themes && $newThemes != "") {
                $themes = $newThemes;
                $totalChanges++;
            }
        }
        if (isset($_POST["request"]) && $_POST["request"] === "settings") {
            if (isset($_POST["privated"])) {
                $privated = true;
            } else {
                $privated = false;
            }
            if (isset($_POST["allowinvite"])) {
                $newallowInvite = 'active';
            } else {
                $newallowInvite = 'inactive';
            }
            if ($newallowInvite != $allowInvite) {
                $allowInvite = $newallowInvite;
                $totalChanges++;
            }
            if ($privated != $private) {
                $private = $privated;
                $totalChanges++;
            }
        }
        if ($totalChanges == 0) {
            $_SESSION['corsmsg'] = "no changes detected";
            header ('location: ../settings.php');
            exit;
        }
        $usrDatTemp = [
            "marked"    => $markedData,
            "private"   => $private,
            "favbadge"  => $favbadge,
            "themes"    => $themes
        ];
        $usrDatTemp = json_encode($usrDatTemp, JSON_UNESCAPED_SLASHES);

        $update_settings = $connects->prepare("UPDATE profiles SET mkot = ?, allowInvite = ? WHERE profileTags = ? ;");
        $update_settings->bind_param("sss", $usrDatTemp, $allowInvite, $aidis);
        $update_settings->execute();
        if ($update_settings->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Profile data updated";
            header ('location: ../settings.php');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to update settings. $changedval" . $update_settings->error;
            header ('location: ../settings.php');
            exit;
        }
    } else {
        $_SESSION['corsmsg'] = "denied request";
        header ('location: ../settings.php');
        exit;
    };
} else {
    $_SESSION['corsmsg'] = "denied access";
    header ('location: ../index.php');
    exit;
};
?>
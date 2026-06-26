<?php
require_once '../processes/database.php';
$root_route = "../";
require_once '../secureSession.php';
if (isset($_POST['submit']) && isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
    $check_profile_data = $connects->prepare("SELECT profileAttachs, profileBios FROM profiles WHERE profileTags = ? ;");
    $check_profile_data->bind_param("s", $aidis);
    $check_profile_data->execute();
    $result_check_profile_data = $check_profile_data->get_result();
    if ($result_check_profile_data->num_rows == 1) {
        $value = $result_check_profile_data->fetch_assoc();
        $final_propic = $value['profileAttachs'];
        $Final_Bios = $value['profileBios'];
    } else {
        $_SESSION['corsmsg'] = "user account does not exists or on a temporary bans";
        header ('location: index.php');
        exit;
    };
    $initReq = $_POST['submit'];
    $initReq = htmlspecialchars($initReq, ENT_QUOTES, 'UTF-8');
    if ($initReq === "Update Bio") {
        if (isset($_POST['bioedits']) && $_POST['bioedits'] != $Final_Bios) {
            $Final_Bios = $_POST['bioedits'];
        }
        $Bios = htmlspecialchars($Bios, ENT_QUOTES, 'UTF-8');
        $stmt_bios = $connects->prepare("UPDATE profiles SET profileBios = ? WHERE profileTags = ?");
        $stmt_bios->bind_param("ss", $Final_Bios, $aidis);
        if($stmt_bios->execute()){
            $_SESSION['corsmsg'] = 'Bio successfully updated';
            header ('location: ../profile.php?user=self');
            $stmt_bios->close();
            exit;
        }else{
            $_SESSION['corsmsg'] = 'Failed to update bio. ' . $stmt_bios->error;
            header ('location: ../profile.php?user=self');
            $stmt_bios->close();
            exit;
        };
    } else if ($initReq === "Change Profile") {
        if (isset($_FILES["profilepic"]["name"]) && $_FILES["profilepic"]["name"][0] != "" && $_FILES['profilepic'] != $final_profilepic) {
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
                        header ('location: ../profile.php?user=self');
                        exit;
                    };
                } else {
                    $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif format allowed';
                    header ('location: ../profile.php?user=self');
                    exit;
                };
            } else {
                $_SESSION['corsmsg'] = 'exceeding 5MB filesize limit';
                header ('location: ../profile.php?user=self');
                exit;
            }
        } else {
            $_SESSION['corsmsg'] = 'Invalid profile picture';
            header ('location: ../profile.php?user=self');
            exit;
        }
        $stmt_update_profiles = $connects->prepare("UPDATE profiles SET profileAttachs = ? WHERE profileTags = ? ;");
        $stmt_update_profiles->bind_param("ss", $final_profilepic, $aidis);
        $stmt_update_profiles->execute();
        if ($stmt_update_profiles->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Profile data updated";
            header ('location: ../profile.php?user=self');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to update " . $stmt_update_profiles->error;
            header ('location: ../profile.php?user=self');
            exit;
        }
    } else if ($initReq === "Update Settings") {
        if (isset($_POST["privated"])) {
            $privated = true;
        }
        if (isset($_POST["allowinvite"])) {
            $allowInvite = 'active';
        } else {
            $allowInvite = 'inactive';
        }
        $check_profile = $connects->prepare("SELECT mkot, allowInvite FROM profiles WHERE profileTags = ? ;");
        $check_profile->bind_param("s", $aidis);
        $check_profile->execute();
        $result_check_profile = $check_profile->get_result();
        if ($result_check_profile->num_rows == 1) {
            $value = $result_check_profile->fetch_assoc();
            $mkot = $value['mkot'];
            $data = json_decode($mkot, true);
            $ltlnData = $data['lastLogin'];
            $markedData = $data['marked'];
            $private = $data['private'];
        };
        if ($privated != $private) {
            $private = $privated;
        }
        $usrDatTemp = [
            "lastLogin" => $ltlnData,
            "marked"    => $markedData,
            "private"   => $private
        ];
        $usrDatTemp = json_encode($usrDatTemp, JSON_UNESCAPED_SLASHES);

        $update_settings = $connects->prepare("UPDATE profiles SET mkot = ?, allowInvite = ? WHERE profileTags = ? ;");
        $update_settings->bind_param("sss", $usrDatTemp, $allowInvite, $aidis);
        $update_settings->execute();
        if ($update_settings->affected_rows > 0) {
            $_SESSION['corsmsg'] = "Profile data updated";
            header ('location: ../profile.php?user=self');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to update settings. " . $update_settings->error;
            header ('location: ../profile.php?user=self');
            exit;
        }
    } else {
        $_SESSION['corsmsg'] = "denied request";
        header ('location: ../profile.php?user=self');
        exit;
    };
} else {
    $_SESSION['corsmsg'] = "denied access";
    header ('location: ../index.php');
    exit;
};
?>
<?php
require_once '../processes/database.php';
if (!isset($_POST['request']) || !isset($_POST['libsids'])) {
    $_SESSION['corsmsg'] = "Missing required input " . $_POST['libsids'] . $_POST['request'] . " .";
    header ('location: badges.php?libsids='.$libsIds);
    exit;   
}if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $_SESSION['corsmsg'] = "The uploaded file exceeds the server's upload limit.";
    header("Location: badges.php");
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
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
} else {
    $_SESSION['corsmsg'] = "denied access";
    header ('location: ../index.php');
    exit;
}

$libsIds = $_POST['libsids'];
$initReq = $_POST['request'];
$totalChanges = 0;
if ($initReq === "NewBadgeGroups" || $initReq === "EditBadgeGroups" || $initReq === "NewBadges" || $initReq === "EditBadges") {
    $check_software = $connects->prepare("SELECT libsPublisher FROM libslist WHERE libsIds = ? AND libsPublisher = ? ;");
    $check_software->bind_param("ss", $libsIds, $gids);
    $check_software->execute();
    $result_check_software = $check_software->get_result();
    if ($result_check_software->num_rows == 0) {
        $_SESSION['corsmsg'] = "Unpermited access";
        header ('location: manage.php');
        exit;
    }
}
if ($initReq === "NewBadgeGroups" && isset($_POST['badgegrouptitle'])) {
    $createfromformat = DateTime::createFromFormat('Y/m/d', date('Y/m/d'));
    $unixdate = $createfromformat->getTimestamp();
    $badgegrouptitle = $_POST['badgegrouptitle'];
    $badgegrouptitle = htmlspecialchars($badgegrouptitle, ENT_QUOTES, 'UTF-8');
    $sanitized = str_replace('%', 'prcn', $badgegrouptitle);
    $sanitized = str_replace(' ', '_', $sanitized);
    $sanitized = str_replace('/', 'I', $sanitized);
    $badgegroupids = $sanitized . bin2hex(random_bytes(8 / 2)) . $unixdate;
    if (isset($_POST['badgegroupdesc']) && $_POST['badgegroupdesc'] != "") {
        $badgegroupdesc = $_POST['badgegroupdesc'];
        $badgegroupdesc = htmlspecialchars($badgegroupdesc, ENT_QUOTES, 'UTF-8');
    } else {
        $badgegroupdesc = "";
    }

    if (isset($_FILES["badgesgroupimg"]["name"]) && $_FILES["badgesgroupimg"]["size"] > 100) {
        $targetdir = "../ab/" . $badgegroupids . "/";
        if (!file_exists($targetdir)) {
            mkdir($targetdir, 0777, true);
        }
        $tempFile = basename($_FILES["badgesgroupimg"]["name"]);
        $tarfilepath = $targetdir . strtolower($tempFile);
        $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'svg', 'png', 'jpeg', 'webp', 'gif');
        if(!in_array($fileType, $allowTypes)) {
            $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif';
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        }
        if($_FILES["badgesgroupimg"]["size"] > 2097152) { // ~2MB
            $_SESSION['corsmsg'] = "exceeding file size limit of 2MB";
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        }
        $randKey = bin2hex(random_bytes(4));
        $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempFile);
        $tempFile = $unixdate . '_' . $libsIds . '_' . $randKey . '_' . $clean_name;
        $tempPath = $_FILES["badgesgroupimg"]["tmp_name"];
        $targetPath = $targetdir . $tempFile;
        if(move_uploaded_file($tempPath, $targetPath)) {
            chmod($targetPath, 0644);
            $create_badgeGroup = $connects->prepare("INSERT INTO badgegroup(groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, icons, state) VALUES (?, ?, ?, ?, ?, 'publics')");
            $create_badgeGroup->bind_param("sssss", $badgegroupids, $libsIds, $badgegrouptitle, $badgegroupdesc, $tempFile);
        } else {
            $create_badgeGroup = $connects->prepare("INSERT INTO badgegroup(groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, icons, state) VALUES (?, ?, ?, ?, ?, 'publics')");
            $create_badgeGroup->bind_param("sssss", $badgegroupids, $libsIds, $badgegrouptitle, $badgegroupdesc, "empty");

            $errors = "An error occured when uploading $tempFile";
        };
    } else {
        $create_badgeGroup = $connects->prepare("INSERT INTO badgegroup(groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, icons, state) VALUES (?, ?, ?, ?, ?, 'publics')");
        $create_badgeGroup->bind_param("sssss", $badgegroupids, $libsIds, $badgegrouptitle, $badgegroupdesc, "empty");

        $errors = "Cannot load the uploaded icons";
    };
    if($create_badgeGroup->execute()){
        $_SESSION['corsmsg'] = 'badges group created. ' . $errors;
        $create_badgeGroup->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    } else {
        $_SESSION['corsmsg'] = "Failed inserting badges group data to the database. " . $create_badgeGroup->error;
        $create_badgeGroup->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    };
} else if ($initReq === "EditBadgeGroups" && isset($_POST["badgegroupids"])) {
    if (!isset($_POST['editbadgegrouptitle'])) {
        $_SESSION['corsmsg'] = "Title cannot be empty";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $badgegroupids = $_POST['badgegroupids'];
    if (isset($_POST['editbadgegrouptitle'])) {
        $newbadgegrouptitle = $_POST['editbadgegrouptitle'];
    }
    if (isset($_POST['editbadgegroupdesc'])) {
        $newbadgegroupdesc = $_POST['editbadgegroupdesc'];
    } else {
        $newbadgegroupdesc = "";
    }
    $newbadgegrouptitle = htmlspecialchars($newbadgegrouptitle, ENT_QUOTES, 'UTF-8');
    $newbadgegroupdesc = htmlspecialchars($newbadgegroupdesc, ENT_QUOTES, 'UTF-8');
    $check_badgeGroup = $connects->prepare("SELECT badgeGroupTitle, badgeGroupDesc, icons FROM badgegroup WHERE libsIds = ? AND groupRefs = ? ;");
    $check_badgeGroup->bind_param("ss", $libsIds, $badgegroupids);
    $check_badgeGroup->execute();
    $result_check_badgeGroup = $check_badgeGroup->get_result();
    if ($result_check_badgeGroup->num_rows > 0) {
        $value = $result_check_badgeGroup->fetch_assoc();
        $badgeGroupTitle = $value['badgeGroupTitle'];
        $badgeGroupDesc = $value['badgeGroupDesc'];
        $final_file = $value['icons'];
        $targetdir = "../ab/" . $badgegroupids . "/" . $final_file;
        if (!file_exists($targetdir)) {
            $final_file = "";
        }
    } else {
        $_SESSION['corsmsg'] = "Cannot find badges group";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $totalChanges = 0;
    if ($newbadgegrouptitle != $badgeGroupTitle) {
        $badgeGroupTitle = $newbadgegrouptitle;
        $totalChanges++;
    }
    if ($newbadgegroupdesc != $badgeGroupDesc) {
        $badgeGroupDesc = $newbadgegroupdesc;
        $totalChanges++;
    }

    if (isset($_FILES["editBadgeGroupImg"]["name"]) && $_FILES["editBadgeGroupImg"]["name"][0] != "" && $_FILES["editBadgeGroupImg"]["size"] > 100) {
        $targetdir = "../ab/" . $badgegroupids . "/";
        if (!file_exists($targetdir)) {
            mkdir($targetdir, 0777, true);
        }
        $tempFile = basename($_FILES["editBadgeGroupImg"]["name"]);
        $tarfilepath = $targetdir . strtolower($tempFile);
        $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'svg', 'png', 'jpeg', 'webp', 'gif');
        if(!in_array($fileType, $allowTypes)) {
            $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif accepted';
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        }
        if($_FILES["editBadgeGroupImg"]["size"] > 2097152) { // ~2MB
            $_SESSION['corsmsg'] = "exceeding file size limit of 2MB";
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        }
        $randKey = bin2hex(random_bytes(4));
        $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempFile);
        $createfromformat = DateTime::createFromFormat('Y/m/d', date('Y/m/d'));
        $unixdate = $createfromformat->getTimestamp();
        $tempFile = $unixdate . '_' . $libsIds . '_' . $randKey . '_' . $clean_name;
        $tempPath = $_FILES["editBadgeGroupImg"]["tmp_name"];
        $targetPath = $targetdir . $tempFile;
        if(move_uploaded_file($tempPath, $targetPath)) {
            chmod($targetPath, 0644);
            $final_file = $tempFile;
            $totalChanges++;
        }
    };
    if ($totalChanges == 0) {
        $_SESSION['corsmsg'] = "no changes detected";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $update_badgeGroup = $connects->prepare("UPDATE badgegroup SET badgeGroupTitle = ? , badgeGroupDesc = ? , icons = ? WHERE libsIds = ? AND groupRefs = ? AND state = 'publics';");
    $update_badgeGroup->bind_param("sssss", $badgeGroupTitle ,$badgeGroupDesc, $final_file, $libsIds, $badgegroupids);
    $update_badgeGroup->execute();
    if ($update_badgeGroup->affected_rows > 0) {
        $_SESSION['corsmsg'] = 'badges group updated successfully. ' . $errors;
        $update_badgeGroup->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    } else {
        $_SESSION['corsmsg'] = "Failed to save badges group changes. $totalChanges / $badgeGroupTitle / $badgeGroupDesc  /  $final_file  /  $libsIds  /  $badgegroupids";
        $update_badgeGroup->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    };
} else if ($initReq === "NewBadges" && isset($_POST["badgetitle"])) {
    if (!isset($_POST['badgeGroup'])) {
        $_SESSION['corsmsg'] = "Please choose a badge groups";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $createfromformat = DateTime::createFromFormat('Y/m/d', date('Y/m/d'));
    $unixdate = $createfromformat->getTimestamp();
    $badgegroupids = $_POST['badgeGroup'];
    $badgetitle = $_POST['badgetitle'];
    $sanitized = str_replace('%', 'prcn', $badgetitle);
    $sanitized = str_replace(' ', '_', $sanitized);
    $sanitized = str_replace('/', 'I', $sanitized);
    $badgesids = $sanitized . bin2hex(random_bytes(8 / 2)) . $unixdate;
    $badgetitle = htmlspecialchars($badgetitle, ENT_QUOTES, 'UTF-8');
    if (isset($_POST['badgedesc']) && $_POST['badgedesc'] != "") {
        $badgedesc = $_POST['badgedesc'];
        $badgedesc = htmlspecialchars($badgedesc, ENT_QUOTES, 'UTF-8');
    } else {
        $badgedesc = "";
    }
    
    $check_badgeGroup = $connects->prepare("SELECT badgeList, icons FROM badgegroup WHERE libsIds = ? AND groupRefs = ? AND state = 'publics'");
    $check_badgeGroup->bind_param("ss", $libsIds, $badgegroupids);
    $check_badgeGroup->execute();
    $result_check_badgeGroup = $check_badgeGroup->get_result();
    if ($result_check_badgeGroup->num_rows > 0) {
        $value = $result_check_badgeGroup->fetch_assoc();
        $final_icon = $value['icons'];
        $badgeList = $value['badgeList'];
        $badgeList = json_decode($badgeList, true);
    } else {
        $_SESSION['corsmsg'] = "Cannot find badges group";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }

    if (isset($_FILES["badgesImage"]["name"]) && $_FILES["badgesImage"]["size"] > 100) {
        $targetdir = "../ab/" . $badgegroupids . "/";
        if (!file_exists($targetdir)) {
            mkdir($targetdir, 0777, true);
        }
        $tempFile = basename($_FILES["badgesImage"]["name"]);
        $tarfilepath = $targetdir . strtolower($tempFile);
        $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'svg', 'png', 'jpeg', 'webp', 'gif');
        if(!in_array($fileType, $allowTypes)) {
            $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif';
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        }
        if($_FILES["badgesImage"]["size"] > 2097152) { // ~2MB
            $_SESSION['corsmsg'] = "exceeding file size limit of 2MB";
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        }
        $randKey = bin2hex(random_bytes(4));
        $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempFile);
        $tempFile = $unixdate . '_' . $libsIds . '_' . $randKey . '_' . $clean_name;
        $tempPath = $_FILES["badgesImage"]["tmp_name"];
        $targetPath = $targetdir . $tempFile;
        if(move_uploaded_file($tempPath, $targetPath)) {
            chmod($targetPath, 0644);
            $final_icon = $tempFile;
        } else {
            $errors = "An error occured when uploading $tempFile";
        };
    }
    $create_badges = $connects->prepare("INSERT INTO badges(badgeIds, badgeName, badgeDesc, badgeType, badgeRefs, icon) VALUES (?, ?, ?, 'achievement', ?, ?)");
    $create_badges->bind_param("sssss", $badgesids, $badgetitle, $badgedesc, $badgegroupids, $final_icon);
    if ($create_badges->execute()) {
        $newBadgeList = array();
        if (!empty($badgeList)) {
            foreach ($badgeList as $badgeListIndex) {
                $newBadgeList[] = $badgeListIndex;
            }
        }
        $newBadgeList[] = $badgesids;
        $newBadgeList = json_encode($newBadgeList, JSON_UNESCAPED_SLASHES);
        $update_badgelist = $connects->prepare("UPDATE badgegroup SET badgeList = ? WHERE groupRefs = ? ;");
        $update_badgelist->bind_param("ss", $newBadgeList, $badgegroupids);
        $update_badgelist->execute();
        if ($update_badgelist->affected_rows > 0) {
            $_SESSION['corsmsg'] = 'badges created. ' . $errors;
            header ('location: badges.php?libsids='.$libsIds);
            $update_badgelist->close();
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to add badge to badges group. " . $newBadgeList . $update_badgelist->error;
            $update_badgelist->close();
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        };
        $create_badges->close();
    } else {
        $_SESSION['corsmsg'] = "Failed to create badges. " . $create_badges->error;
        $create_badges->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    };
    $create_badges->close();
} else if ($initReq === "EditBadges" && isset($_POST["badgesids"])) {
    if (!isset($_POST['editbadgegroup'])) {
        $_SESSION['corsmsg'] = "Missing input, badge groups.";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $badgesids = $_POST['badgesids'];
    $badgegroupids = $_POST['editbadgegroup'];
    
    $check_badges = $connects->prepare("SELECT badgeName, badgeDesc, icon FROM badges WHERE badgeIds = ?");
    $check_badges->bind_param("s", $badgesids);
    $check_badges->execute();
    $result_check_badges = $check_badges->get_result();
    if ($result_check_badges->num_rows > 0) {
        $value = $result_check_badges->fetch_assoc();
        $badgetitle = $value['badgeName'];
        $badgedesc = $value['badgeDesc'];
        $final_file = $value['icon'];
    } else {
        $_SESSION['corsmsg'] = "Cannot find badges group";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $totalChanges = 0;
    if (isset($_POST["editbadgetitle"]) && $_POST["editbadgetitle"] != $badgetitle) {
        $badgetitle = $_POST['editbadgetitle'];
        $totalChanges++;
    }
    if (isset($_POST["editbadgedesc"])) {
        $newbadgedesc = $_POST['editbadgedesc'];
    } else {
        $newbadgedesc = "";
    }
    $badgetitle = htmlspecialchars($badgetitle, ENT_QUOTES, 'UTF-8');
    $newbadgedesc = htmlspecialchars($newbadgedesc, ENT_QUOTES, 'UTF-8');
    if ($newbadgedesc != $badgedesc) {
        $badgedesc = $newbadgedesc;
        $totalChanges++;
    }

    $check_badgeGroup = $connects->prepare("SELECT badgeList FROM badgegroup WHERE libsIds = ? AND groupRefs = ? AND state = 'publics'");
    $check_badgeGroup->bind_param("ss", $libsIds, $badgegroupids);
    $check_badgeGroup->execute();
    $result_check_badgeGroup = $check_badgeGroup->get_result();
    if ($result_check_badgeGroup->num_rows > 0) {
        $value = $result_check_badgeGroup->fetch_assoc();
        $badgeList = $value['badgeList'];
        $badgeList = json_decode($badgeList, true);
    } else {
        $_SESSION['corsmsg'] = "Cannot find badges group";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }

    if (isset($_FILES["badgesImage"]["name"]) && $_FILES["badgesImage"]["name"][0] != "" && $_FILES["badgesImage"]["size"] > 100) {
        $targetdir = "../ab/" . $badgegroupids . "/";
        if (!file_exists($targetdir)) {
            mkdir($targetdir, 0777, true);
        }
        $tempFile = basename($_FILES["badgesImage"]["name"]);
        $tarfilepath = $targetdir . strtolower($tempFile);
        $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'svg', 'png', 'jpeg', 'webp', 'gif');
        if(!in_array($fileType, $allowTypes)) {
            $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif';
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        }
        if($_FILES["badgesImage"]["size"] > 2097152) { // ~2MB
            $_SESSION['corsmsg'] = "exceeding file size limit of 2MB";
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        }
        $randKey = bin2hex(random_bytes(4));
        $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $tempFile);
        $createfromformat = DateTime::createFromFormat('Y/m/d', date('Y/m/d'));
        $unixdate = $createfromformat->getTimestamp();
        $tempFile = $unixdate . '_' . $libsIds . '_' . $randKey . '_' . $clean_name;
        $tempPath = $_FILES["badgesImage"]["tmp_name"];
        $targetPath = $targetdir . $tempFile;
        if(move_uploaded_file($tempPath, $targetPath)) {
            chmod($targetPath, 0644);
            $final_file = $tempFile;
            $totalChanges++;
        };
    };
    if ($totalChanges == 0) {
        $_SESSION['corsmsg'] = "no changes detected";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }

    $editBadges = $connects->prepare("UPDATE badges SET badgeName = ?, badgeDesc = ?, icon = ? WHERE badgeIds = ? ;");
    $editBadges->bind_param("ssss", $badgetitle, $badgedesc, $final_file, $badgesids);
    $editBadges->execute();
    if ($editBadges->affected_rows > 0) {
        $_SESSION['corsmsg'] = 'Badges updated. ' . $errors;
        $editBadges->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    } else {
        $_SESSION['corsmsg'] = "Failed to saved changes. " . $editBadges->error;
        $editBadges->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    };
} else if ($initReq === "DeleteBadgeGroups" && isset($_POST["badgegroupids"])) {
    if (!isset($_POST['badgegroupids'])) {
        $_SESSION['corsmsg'] = "Badge group data missing";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $badgegroupids = $_POST['badgegroupids'];
    $check_badgeGroup = $connects->prepare("SELECT badgeGroupTitle FROM badgegroup WHERE libsIds = ? AND groupRefs = ? AND state = 'publics'");
    $check_badgeGroup->bind_param("ss", $libsIds, $badgegroupids);
    $check_badgeGroup->execute();
    $result_check_badgeGroup = $check_badgeGroup->get_result();
    if ($result_check_badgeGroup->num_rows == 0) {
        $_SESSION['corsmsg'] = "Cannot find badges group to remove";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $delete_badgeGroup = $connects->prepare("UPDATE badgegroup SET state = 'deleted' WHERE libsIds = ? AND groupRefs = ? ;");
    $delete_badgeGroup->bind_param("ss", $libsIds, $badgegroupids);
    $delete_badgeGroup->execute();
    if ($delete_badgeGroup->affected_rows > 0) {
        $_SESSION['corsmsg'] = 'badges group deleted successfully. ' . $errors;
        $delete_badgeGroup->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    } else {
        $_SESSION['corsmsg'] = "Failed to remove badges group. " . $delete_badgeGroup->error;
        $delete_badgeGroup->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    };
} else if ($initReq === "DeleteBadges" && isset($_POST["badgesids"])) {
    if (!isset($_POST['deletebadgegroup'])) {
        $_SESSION['corsmsg'] = "Missing input, badge groups.";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $badgesids = $_POST['badgesids'];
    $badgegroupids = $_POST['deletebadgegroup'];
    
    $check_badgeGroup = $connects->prepare("SELECT badgeList FROM badgegroup WHERE libsIds = ? AND groupRefs = ? AND state = 'publics'");
    $check_badgeGroup->bind_param("ss", $libsIds, $badgegroupids);
    $check_badgeGroup->execute();
    $result_check_badgeGroup = $check_badgeGroup->get_result();
    if ($result_check_badgeGroup->num_rows > 0) {
        $value = $result_check_badgeGroup->fetch_assoc();
        $badgeList = $value['badgeList'];
        $badgeList = json_decode($badgeList, true);
    } else {
        $_SESSION['corsmsg'] = "Cannot find badges group";
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    }
    $delete_badges = $connects->prepare("DELETE FROM badges WHERE badgeIds = ? ;");
    $delete_badges->bind_param("s", $badgesids);
    if ($delete_badges->execute()) {
        $newBadgeList = array();
        if (!empty($badgeList)) {
            foreach ($badgeList as $badgeListIndex) {
                if ($badgeListIndex != $badgesids) {
                    $newBadgeList[] = $badgeListIndex;
                }
            }
        }
        $newBadgeList = json_encode($newBadgeList, JSON_UNESCAPED_SLASHES);
        $update_badgelist = $connects->prepare("UPDATE badgegroup SET badgeList = ? WHERE libsIds = ? AND groupRefs = ? ;");
        $update_badgelist->bind_param("sss", $newBadgeList , $libsIds, $badgegroupids);
        $update_badgelist->execute();
        if ($update_badgelist->affected_rows > 0) {
            $_SESSION['corsmsg'] = 'badges deleted. ' . $errors;
            header ('location: badges.php?libsids='.$libsIds);
            $update_badgelist->close();
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to delete badge from badges group. " . $badgesids;
            $update_badgelist->close();
            header ('location: badges.php?libsids='.$libsIds);
            exit;
        };
    } else {
        $_SESSION['corsmsg'] = "Failed to delete badges. " . $badgesids;
        $delete_badges->close();
        header ('location: badges.php?libsids='.$libsIds);
        exit;
    };
} else {
    $_SESSION['corsmsg'] = "Invalid request / " . $_POST['libsids'] . " / " .  $_POST['request'] . " .";
    header ('location: badges.php?libsids='.$libsIds);
    exit;
};

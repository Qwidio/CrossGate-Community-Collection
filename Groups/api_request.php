<?php
if (isset($_POST['request'])) {
    function generateApiKey($length) {
        return bin2hex(random_bytes($length / 2));
    }
    require_once '../processes/database.php';
    $errors = array();
    $root_route = "../";
    require_once '../secureSession.php';
    require_once 'ReAuth.php';
    if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
        $aidis = $_SESSION['profileTags'];
        $gToken = $_SESSION['GroupsToken'];
        $ChangerRoles = $_SESSION['roles'];
    $gids = $_SESSION['gids'];
        if ($ChangerRoles === "founder" || $ChangerRoles === "developer") {
            $allowChanges = true;
        }
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    
    $apiId = generateApiKey(32);
    $hashedkeys = generateApiKey(64);
    $initReq = $_POST['request'];
    if ($initReq === "NDT") {
        $check_api = $connects->prepare("SELECT apiId FROM api_keys WHERE og_identification = ? AND useScope = 'Development';");
        $check_api->bind_param("s", $gids);
        $check_api->execute();
        $result_check_api = $check_api->get_result();
        if ($result_check_api->num_rows > 0) {
            $rca_val = $result_check_api->fetch_assoc();
            $oldApiId = $rca_val['apiId'];
            $delete_keys = $connects->prepare("DELETE FROM api_keys WHERE apiId = ? AND og_identification = ? AND useScope = 'Development';");
            $delete_keys->bind_param("ss", $oldApiId, $gids);
            if($delete_keys->execute()){
                $tempMsg = 'Existing api removed';
            }else{
                $_SESSION['corsmsg'] = 'Failed to delete existing keys.';
                header ('location: manage.php');
                exit;
            };
        }
        $useScope = "Development";
        $insert_api = $connects->prepare("INSERT INTO api_keys (apiId, og_identification, hashedKeys, useScope, addedDate) VALUES (?, ?, ?, ?, ?)");
        $insert_api->bind_param("sssss", $apiId, $gids, $hashedkeys, $useScope, date("Y/m/d H:i"));
        if ($insert_api->execute()) {
            $_SESSION['corsmsg'] = "Developement API keys refreshed, " . $tempMsg;
            header('location: manage.php');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to reset keys";
            header('location: manage.php');
            exit;
        }
    } else if ($initReq === "NPT") {
        $check_api = $connects->prepare("SELECT apiId FROM api_keys WHERE og_identification = ? AND useScope = 'Production';");
        $check_api->bind_param("s", $gids);
        $check_api->execute();
        $result_check_api = $check_api->get_result();
        if ($result_check_api->num_rows > 0) {
            $rca_val = $result_check_api->fetch_assoc();
            $oldApiId = $rca_val['apiId'];
            $delete_keys = $connects->prepare("DELETE FROM api_keys WHERE apiId = ? AND og_identification = ? AND useScope = 'Production';");
            $delete_keys->bind_param("ss", $oldApiId, $gids);
            if($delete_keys->execute()){
                $tempMsg = 'Existing api removed';
            }else{
                $_SESSION['corsmsg'] = 'Failed to delete existing keys.';
                header ('location: manage.php');
                exit;
            };
        }
        $useScope = "Production";
        $insert_api = $connects->prepare("INSERT INTO api_keys (apiId, og_identification, hashedKeys, useScope, addedDate) VALUES (?, ?, ?, ?, ?)");
        $insert_api->bind_param("sssss", $apiId, $gids, $hashedkeys, $useScope, date("Y/m/d H:i"));
        if ($insert_api->execute()) {
            $_SESSION['corsmsg'] = "Developement API keys refreshed, " . $tempMsg;
            header('location: manage.php');
            exit;
        } else {
            $_SESSION['corsmsg'] = "Failed to reset keys";
            header('location: manage.php');
            exit;
        }
    }
} else {
    header ('location: manage.php');
    exit;
};
?>
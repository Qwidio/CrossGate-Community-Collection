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
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    $gids = $_SESSION['gids'];
    $prebind = '"' . $aidis . '"';
    $check_orgs = $connects->prepare("SELECT members FROM ogroup WHERE identification = ? AND founder = ? OR JSON_CONTAINS(members, ?);");
    $check_orgs->bind_param("sss", $gids, $aidis, $prebind);
    $check_orgs->execute();
    $result_check_orgs = $check_orgs->get_result();
    if ($result_check_orgs->num_rows > 0) {
        while ($value = $result_check_orgs->fetch_assoc()) {
            $members = json_decode($value['members'], true);
        }
    } else {
        $_SESSION['corsmsg'] = "You are not allowed to make group changes";
        header('location: ../index.php');
        exit;
    }
    $apiId = generateApiKey(32);
    $hashedkeys = generateApiKey(64);
    // $hashedkeys = password_hash($hashedkeys, PASSWORD_DEFAULT);
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
        $rca_val = $result_check_api->fetch_assoc();
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
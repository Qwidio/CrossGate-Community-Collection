<?php
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!str_contains($providedKey, '.')) {
    http_response_code(401);
    die(json_encode([
        'message' => 'Invalid API key format'
    ]));
}
function getIpAddr(): string {
    if (
        isset($_SERVER['HTTP_X_FORWARDED_FOR']) &&
        filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)
    ) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
require_once "../processes/database.php";
[$keyId, $secret] = explode('.', $providedKey, 2);
$stmt_check_apis = $connects->prepare("SELECT useScope, hashedKeys, addedDate, active FROM api_keys WHERE apiId = ?");
$stmt_check_apis->bind_param("s", $keyId);
$stmt_check_apis->execute();
$result_check_apis = $stmt_check_apis->get_result();
$rca_val = $result_check_apis->fetch_assoc();
if (!$rca_val) {
    http_response_code(401);
    die(json_encode([
        'message' => 'Invalid API key'
    ]));
}
$apiState = $rca_val['active'];
$scope = $rca_val['useScope'];
$hashedKeys = $rca_val['hashedKeys'];
$addedDate = $rca_val['addedDate'];
if ($scope === "Development") {
    http_response_code(403);
    die(json_encode([
        'message' => 'Incorrect API key used'
    ]));
}
if ($apiState == 0) {
    http_response_code(403);
    die(json_encode([
        'message' => 'API key is inactive'
    ]));
}
if (!hash_equals($hashedKeys, $secret)) {
    http_response_code(401);
    die(json_encode([
        'message' => 'Invalid API key'
    ]));
}
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
if ($method === "POST") {
    $badgegroups = $input['groupref'] ?? null;
    $target = $input['target'] ?? null;
    $sessiontokens = $input['tokens'] ?? null;
    $addrss = $input['address'] ?? 'Unknown';
    $osids = $input['os'] ?? 'Unknown';
    if (!isset($sessiontokens) || !isset($badgegroups) || !isset($target)) {
        die(json_encode(["message" => "Missing Required session token"]));
    }
    $session_check = $connects->prepare("SELECT profileTags, osids, addrss, expirationDate FROM sessionlogs WHERE sessiontokens = ?");
    $session_check->bind_param("s", $sessiontokens);
    $session_check->execute();
    $result_session_check = $session_check->get_result();
    if ($result_session_check->num_rows > 0) {
        $data = $result_session_check->fetch_assoc();
        $profileTags = $data['profileTags'];
        $savedOS = $data['osids'];
        $oldaddrss = $data['addrss'];
        $exps = $data['expirationDate'];
        $curdt = date('Y/m/d');
        if ($exps < $curdt) {
            http_response_code(401);
            die(json_encode(["message" => "Session has expired"]));
        }
        if ($osids != $savedOS && $savedOS != "unset") {
            http_response_code(401);
            die(json_encode(["message" => "Session already used on another device"]));
        }
        if ($oldaddrss !== $addrss) {
            http_response_code(401);
            die(json_encode(['message' => 'IP mismatch']));
        }
    } else {
        http_response_code(401);
        die(json_encode(["message" => "Failed to find session"]));
    }

    $errors = array();
    $badgeGroups= array();

    $check_profile = $connects->prepare("SELECT Badge FROM profiles WHERE profileTags = ? ;");
    $check_profile->bind_param("s", $profileTags);
    $check_profile->execute();
    $result_check_profile = $check_profile->get_result();
    if ($result_check_profile->num_rows == 1) {
        $value = $result_check_profile->fetch_assoc();
        $ownedBadges = $value['Badge'];
        $ownedBadges = json_decode($ownedBadges, true);
        if (in_array($target, $ownedBadges)) {
            die(json_encode(["message" => "cannot add badges"]));
        }
        $check_badges = $connects->prepare("SELECT 
        badges.badgeName, badges.badgeDesc, badges.badgeType, badges.badgeRefs, badges.icon, badgegroup.state, badgegroup.badgeList
        FROM badges INNER JOIN badgegroup ON badges.badgeRefs = badgegroup.groupRefs
        WHERE badges.badgeIds = ? ;");
        $check_badges->bind_param("s", $target);
        $check_badges->execute();
        $result_check_badges = $check_badges->get_result();
        if ($result_check_badges->num_rows > 0) {
            $value = $result_check_badges->fetch_assoc();
            $tempBadgeList = json_decode($value['badgeList'], true);
            if (!in_array($target, $tempBadgeList)) {
                http_response_code(403);
                die(json_encode(["message" => "Target does not exist in the Referenced badges groups"]));
            }
            if ($value['state'] != "publics") {
                http_response_code(403);
                die(json_encode(["message" => "The specified badges unavailable"]));
            }
        }
        $ownedBadges["$target"] = date("d/m/Y H:i");
        $ownedBadges = json_encode($ownedBadges, JSON_UNESCAPED_SLASHES);
        $update_badge = $connects->prepare("UPDATE profiles SET Badge = ? WHERE profileTags = ? ;");
        $update_badge->bind_param("ss", $ownedBadges, $profileTags);
        $update_badge->execute();
        if ($update_badge->affected_rows > 0) {
            die(json_encode(["message" => "Successfully added the Badge"]));
        } else {
            http_response_code(404);
            die(json_encode(["message" => "Failed to add the Badges",]));
        }
    } else {
        http_response_code(404);
        die(json_encode(["message" => "user account does not exists or on a temporary bans"]));
    }
} else {
    http_response_code(403);
    die(json_encode(["message" => "Invalid request method"]));
}
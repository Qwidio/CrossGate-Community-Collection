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
$requestAddress = getIpAddr();
$debugMode = false;
require_once "../processes/database.php";
[$keyId, $secret] = explode('.', $providedKey, 2);
$stmt_check_apis = $connects->prepare("SELECT useScope, og_identification, hashedKeys, addedDate, active FROM api_keys WHERE apiId = ?");
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
$og_identification = $rca_val['og_identification'];
$scope = $rca_val['useScope'];
if ($scope === "Development") {
    $debugMode = true;
}
$hashedKeys = $rca_val['hashedKeys'];
$apiState = $rca_val['active'];
if ($apiState == 0) {
    http_response_code(403);
    die(json_encode([
        'message' => 'API key is inactive'
    ]));
}
$addedDate = $rca_val['addedDate'];
if (!hash_equals($hashedKeys, $secret)) {
    http_response_code(401);
    die(json_encode([
        'message' => 'Invalid API key'
    ]));
} else {
    header("Content-Type:application/json");
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    if ($method != "PUT") {
        die(json_encode(["message" => "Invalid request"]));
    }
    $sessiontokens = $input['tokens'];
    $addrss = $input['address'] ?? 'Unknown';
    $osids = $input['os'] ?? 'Unknown';
    if (!isset($sessiontokens) || !isset($addrss) || !isset($osids)) {
        die(json_encode(["message" => "Missing Required data"]));
    }
    $session_check = $connects->prepare("SELECT profileTags, osids, addrss, expirationDate FROM sessionlogs WHERE sessiontokens = ?;");
    $session_check->bind_param("s", $sessiontokens);
    $session_check->execute();
    $result_session_check = $session_check->get_result();
    if ($result_session_check->num_rows > 0) {
        $data = $result_session_check->fetch_assoc();
        $aidis = $data['profileTags'];
        $savedOS = $data['osids'];
        $oldaddrss = $data['addrss'];
        $exps = $data['expirationDate'];
        $curdt = date('Y/m/d');
        if ($exps < $curdt) {
            http_response_code(401);
            die(json_encode(["message" => "Session Have been expired"]));
        }
        if ($osids != $savedOS && $savedOS != "unset") {
            http_response_code(401);
            die(json_encode(["message" => "Sessions already used on another device"]));
        }
        if ($oldaddrss !== $addrss) {
            http_response_code(401);
            die(json_encode([
                'message' => 'IP mismatch'
            ]));
        }
    } else {
        http_response_code(401);
        die(json_encode(["message" => "Failed to find sessions"]));
    }
    $lastLog = date("d/m/Y H:i:s");
    $update_auth = $connects->prepare("UPDATE sessionlogs SET addrss = ?, osids = ?, lastlogs = ? WHERE sessiontokens = ? ;");
    $update_auth->bind_param("ssss", $addrss, $osids, $lastLog, $sessiontokens);
    $update_auth->execute();
    if ($update_auth) {
        $returnData = array();
        $check_profile = $connects->prepare("SELECT * FROM profiles WHERE profileTags = ? ;");
        $check_profile->bind_param("s", $aidis);
        $check_profile->execute();
        $result_check_profile = $check_profile->get_result();
        $value = $result_check_profile->fetch_assoc();
        if ($value) {
            $returnData = [
                    "message" => "Session Valid",
                    "profileTags"   => $aidis,
                    "profileAttachs"=> $value['profileAttachs'],
                    "profileNames"  => $value['profileNames'],
                    "profileBios"   => $value['profileBios'],
                    "profileJDates" => $value['profileJDates'],
                    "profileBadge"  => $value['Badge'],
                    "profileMarkOut"=> $value['mkot'],
                    "activityState" => $value['oState']
            ];
            if ($debugMode == true) {
                $returnData = array_merge($returnData, [
                    "clientDev"     => $og_identification,
                    "useScope"      => $scope,
                ]);
            }
            die(json_encode($returnData, JSON_UNESCAPED_SLASHES));
        }
        exit;
    }
    $connects->close();
}
?>
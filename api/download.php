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
if ($method != "POST") {
    http_response_code(403);
    die(json_encode(["message" => "Invalid request method"]));
}
$sessiontokens = $input['tokens'] ?? null;
$addrss = $input['address'] ?? 'Unknown';
$osids = $input['os'] ?? 'Unknown';
if (!isset($sessiontokens)) {
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
$libsIds = $input['libsIds'];
$isRollback = $input['isRollback'];
$ver = $input['ver'];
if (!isset($libsIds) || !isset($isRollback) || !isset($ver)) {
    http_response_code(403);
    die(json_encode([
        'message' => 'Missing Input'
    ]));
}
$check_software = $connects->prepare("SELECT libsPublisher, libsTitles, fdrLibs, rollbacks, detailData FROM libslist WHERE libsIds = ? AND downloadDisabled = 0 AND libsState = 'Publics';");
$check_software->bind_param("s", $libsIds);
$check_software->execute();
$result_check_software = $check_software->get_result();
if ($result_check_software->num_rows > 0) {
    $publishing = true;
    while ($value = $result_check_software->fetch_assoc()) {
        $libsTitles = $value['libsTitles'];
        $libsPublisher = $value['libsPublisher'];
        $detailData = json_decode($value['detailData'], true);
        if ($isRollback == true) {
            $targetFile = $value['rollbacks'];
            $verCheck = $detailData["rollbacks"]["ver"] ?? '';
        } else {
            $targetFile = $value['fdrLibs'];
            $verCheck = $detailData["fdrLibs"]["ver"] ?? '';
        }
        $file_path = '../vaults/' . $libsPublisher . '/' . $libsIds . "/" . $targetFile;
        if (!file_exists($file_path) || !is_readable($file_path)) {
            http_response_code(404);
            echo json_encode(["message" => "Error: File not found"]);
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }

        $file_name = basename($file_path);
        $file_size = filesize($file_path);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode($file_name));
        header('Content-Length: ' . $file_size);
        header('Cache-Control: no-cache');
        header('Pragma: public');

        readfile($file_path);
        $connects->close();
        exit;
    }
} else {
    http_response_code(403);
    die(json_encode(["message" => "No Downlaodable Collection found"]));
}
?>
<?php
require_once "../processes/database.php";
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!str_contains($providedKey, '.')) {
    http_response_code(401);
    die(json_encode(['message' => 'Invalid API key format']));
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
[$keyId, $secret] = explode('.', $providedKey, 2);
$stmt_check_apis = $connects->prepare("SELECT useScope, og_identification, hashedKeys, addedDate, active FROM api_keys WHERE apiId = ?");
$stmt_check_apis->bind_param("s", $keyId);
$stmt_check_apis->execute();
$result_check_apis = $stmt_check_apis->get_result();
$rca_val = $result_check_apis->fetch_assoc();
if (!$rca_val) {
    http_response_code(401);
    die(json_encode(['message' => 'Invalid API key']));
}
$og_identification = $rca_val['og_identification'];
$scope = $rca_val['useScope'];
if ($scope === "Development") {
    $debugMode = true;
}
$hashedKeys = $rca_val['hashedKeys'];
if ($rca_val['active'] == 0) {
    http_response_code(403);
    die(json_encode(['message' => 'API key is inactive']));
}
if (!hash_equals($hashedKeys, $secret)) {
    http_response_code(401);
    die(json_encode(['message' => 'Invalid API key']));
}
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
if ($method === "POST") {
    $sessiontokens = $input['tokens'] ?? null;
    $targetlibs = $input['targetlibs'] ?? [];
    $addrss = $input['address'] ?? 'Unknown';
    $osids = $input['os'] ?? 'Unknown';
    $fetchImages = $input['fetch_images'] ?? false; 
    if (!$sessiontokens || empty($targetlibs)) {
        die(json_encode(["message" => "Missing Required data or empty collection request"]));
    }
    $session_check = $connects->prepare("SELECT osids, addrss, expirationDate FROM sessionlogs WHERE sessiontokens = ?");
    $session_check->bind_param("s", $sessiontokens);
    $session_check->execute();
    $result_session_check = $session_check->get_result();
    if ($result_session_check->num_rows > 0) {
        $data = $result_session_check->fetch_assoc();
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

    $returnData = array();
    $State = "Publics";
    $placeholders = implode(',', array_fill(0, count($targetlibs), '?'));
    $types = str_repeat('s', count($targetlibs)) . 's';
    $params = $targetlibs;
    $params[] = $State;
    $query = "SELECT libsIds, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsAttachs, libsPublisher, libsTitles, libsDesc, addedDates, fdrLibs, rollbacks, detailData FROM libslist WHERE libsIds IN ($placeholders) AND libsState = ?";
    $check_software = $connects->prepare($query);
    $check_software->bind_param($types, ...$params);
    $check_software->execute();
    $result_check_software = $check_software->get_result();
    if ($result_check_software->num_rows > 0) {
        while ($value = $result_check_software->fetch_assoc()) {
            $libsIds = $value['libsIds'];
            $publisher = str_replace('"', "", $value['libsPublisher']);
            $iconFile = str_replace('"', "", $value['libsAttachs'] ?? '');
            $bannerFile = str_replace('"', "", $value['libsBanners'] ?? '');
            $detailData = json_decode($value['detailData'], true);
            $appData = [
                "libsIds"       => $libsIds,
                "libsPublisher" => $publisher,
                "libsIcon"      => $iconFile,
                "libsBanners"   => $bannerFile,
                "libsTitles"    => $value['libsTitles'],
                "libsDesc"      => $value['libsDesc'],
                "addedDates"    => $value['addedDates'],
                "fdrLibs"       => $value['fdrLibs'],
                "rollbacks"     => $value['rollbacks'],
                "detailData"    => $value['detailData'],
                "theme"         => $detailData["theme"]
            ];
            if ($fetchImages) {
                $baseImgDir = "../Library/libsImg/" . $publisher . "/";
                if (!empty($iconFile) && file_exists($baseImgDir . $iconFile)) {
                    $appData['icon_base64'] = base64_encode(file_get_contents($baseImgDir . $iconFile));
                }
                
                if (!empty($bannerFile) && file_exists($baseImgDir . $bannerFile)) {
                    $appData['banner_base64'] = base64_encode(file_get_contents($baseImgDir . $bannerFile));
                }
            }
            $returnData[$libsIds] = $appData;
        }
        http_response_code(200);
        die(json_encode($returnData, JSON_UNESCAPED_SLASHES));
    } else {
        die(json_encode(["message" => "No Collection found"]));
    }
} else {
    die(json_encode(["message" => "Invalid request method"]));
}
$connects->close();
?>
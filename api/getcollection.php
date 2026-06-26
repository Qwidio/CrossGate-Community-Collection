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
function generateApiKey($length) {
    return bin2hex(random_bytes($length / 2));
}
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
    if ($method === "GET") {
        $sessiontokens = $input['tokens'];
        $targetlibs = $input['targetlibs'];
        $addrss = $input['address'] ?? 'Unknown';
        $osids = $input['os'] ?? 'Unknown';
        if (!isset($sessiontokens) || !isset($addrss) || !isset($osids)) {
            die(json_encode(["message" => "Missing Required data"]));
        }
        $session_check = $connects->prepare("SELECT profileTags, osids, addrss, expirationDate FROM sessionlogs WHERE sessiontokens = ?;");
        $session_check->bind_param("s", $sessiontokens);
        $session_check->execute();
        $result_session_check = $session_check->get_result();
        $data = $result_session_check->fetch_assoc();
        if (isset($data)) {
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
        $update_auth = $connects->prepare("UPDATE sessionlogs SET addrss = ?, osids = ?, lastlogs = NOW() WHERE sessiontokens = ? ;");
        $update_auth->bind_param("sss", $addrss, $osids, $sessiontokens);
        $update_auth->execute();
        if ($update_auth) {
            $returnData = array();
            $check_software = $connects->prepare("SELECT libsIds, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsPublisher, libsTitles, libsDesc, addedDates, cltNumbs, fdrLibs FROM libslist WHERE libsIds = ? AND libsState = ? ;");
            $check_software->bind_param("ss", $targetlibs, $State);
            $check_software->execute();
            $result_check_software = $check_software->get_result();
            if ($result_check_software->num_rows > 0) {
                while ($value = $result_check_software->fetch_assoc()) {
                    $libsIds = $value['libsIds'];
                    $libsBanners = $value['libsBanners'];
                    $libsPublisher = $value['libsPublisher'];
                    $libsBanners = str_replace('"', "", $libsBanners);
                    $libsTitles = $value['libsTitles'];
                    $libsDesc = $value['libsDesc'];
                    $addedDates = $value['addedDates'];
                    $cltNumbs = $value['cltNumbs'];
                    $fdrLibs = $value['fdrLibs'];
                    if (!in_array($libsIds, $returnData)) {
                        $returnData[$libsIds] = [
                        "libsIds"        => "$libsIds",
                        "libsPublisher"  => "$libsPublisher",
                        "libsBanners"    => "$libsBanners",
                        "libsTitles"     => "$libsTitles",
                        "libsDesc"       => "$libsDesc",
                        "addedDates"     => "$addedDates",
                        "cltNumbs"       =>  $cltNumbs,
                        "fdrLibs"        => "$fdrLibs"
                        ];
                    };
                };
                http_response_code(200);
                // if ($debugMode == true) {
                //     $returnData = array_merge($returnData, [
                //         "clientDev"     => $og_identification,
                //         "useScope"      => $scope,
                //     ]);
                // }
                die(json_encode($returnData, JSON_UNESCAPED_SLASHES));
            } else {
                die(json_encode(["message" => "No Collection found"]));
            };
        }
    } else {
        die(json_encode(["message" => "Invalid request"]));
    }
    $connects->close();
}
?>
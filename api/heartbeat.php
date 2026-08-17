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
    if ($method != "POST") {
        die(json_encode(["message" => "Invalid request"]));
    }
    $isRunningClts = $input['isRun'] ? 1 : 0;
    $runningSoftware = $input['target'] ?? 'None';
    $sessiontokens = $input['tokens'];
    $addrss = $input['address'] ?? 'Unknown';
    $osids = $input['os'] ?? 'Unknown';
    $curdt = date('Y/m/d');
    if (!isset($sessiontokens)) {
        http_response_code(401);
        die(json_encode(["message" => "Missing session token"]));
    }
    $newlogtime = date('d/m/Y H:i:s');
    $onlineDefine = date('d/m/Y H:i:s', strtotime('-59 seconds'));

    // le new statUpdate 
    $session_check = $connects->prepare("SELECT profileTags, lastlogs FROM sessionlogs WHERE sessiontokens = ? AND osids = ? AND addrss = ? AND expirationDate > ?;");
    $session_check->bind_param("ssss", $sessiontokens, $osids, $addrss, $curdt);
    $session_check->execute();
    $result_session_check = $session_check->get_result();
    $data = $result_session_check->fetch_assoc();
    if (isset($data)) {
        $aidis = $data['profileTags'];
        $lastlogs = $data['lastlogs'];
    } else {
        http_response_code(401);
        die(json_encode(["message" => "Failed to find sessions"]));
    }
    if ($lastlogs < $onlineDefine) {
        $heartbeat = $connects->prepare("UPDATE sessionlogs set lastlogs = ?, isRunningClts = ?, lastCltsRun = ? WHERE sessiontokens = ? AND osids = ? AND addrss = ? AND expirationDate > ? ;");
        $heartbeat->bind_param("sssssss", $newlogtime, $isRunningClts, $runningSoftware, $sessiontokens, $osids, $addrss, $curdt);
        $heartbeat->execute();
        if ($heartbeat->affected_rows > 0) {
            $message = "Heartbeat updated";
        }
    } else {
        $message = "Heartbeat updated less than a minute ago";
    }

    if ($isRunningClts == 1 && $runningSoftware != "None") {
        $check_Libs = $connects->prepare("SELECT libsIds FROM libslist WHERE libsIds = ? AND libsState = 'publics';");
        $check_Libs->bind_param("s", $runningSoftware);
        $check_Libs->execute();
        $result_check_Libs = $check_Libs->get_result();
        if ($result_check_Libs->num_rows == 1) {
            $value = $result_check_Libs->fetch_assoc();
            $new_marked[$value['libsIds']] = [
                "libsIds"  => $value['libsIds'],
                "Hours"    => 0,
                "lastLog"  => "notset"
            ];

            $check_profile = $connects->prepare("SELECT mkot FROM profiles WHERE profileTags = ? ;");
            $check_profile->bind_param("s", $aidis);
            $check_profile->execute();
            $result_check_profile = $check_profile->get_result();
            if ($result_check_profile->num_rows == 1) {
                $value = $result_check_profile->fetch_assoc();
                $mkot = $value['mkot'];
                $data = json_decode($mkot, true);
                $markedData = $data['marked'];
                $private = $data['private'];
                $favbadge = $data['favbadge'];
                $themes = $data['themes'];
                $lastLogged = $markedData[$runningSoftware]['lastLog'];
                if ($lastLogged < $onlineDefine || $lastLogged === "unset") {
                    $marked = [];
                    if (!empty($markedData) && $markedData != "empty") {
                        foreach ($markedData as $markedIndex => $info) {
                            if ($markedIndex != $runningSoftware) {
                                $marked[$markedIndex] = [
                                    "libsIds"  => $info['libsIds'],
                                    "Hours"    => (int)$info['Hours'],
                                    "lastLog"  => $info['lastLog'],
                                ];
                            }
                        }
                    } else {
                        http_response_code(403);
                        die(json_encode(["message" => "Empty MarkOut"]));
                    }
                    $totalHours = 1 / 60;
                    $totalHours = round($totalHours, 2);
                    $totalHours = number_format($totalHours, 2, '.', '');
                    if ($totalHours > 2) {
                        http_response_code(403);
                        die(json_encode(["message" => "total added minute cannot exceed two hour"]));
                    }
                    if (!in_array($runningSoftware, $marked)) {
                        $newHours = $markedData[$runningSoftware]['Hours'] + $totalHours;
                        $newHours = round($newHours, 2);
                        $marked[$runningSoftware] = [
                            "libsIds"  => $runningSoftware,
                            "Hours"    => $newHours,
                            "lastLog"  => date("d/m/Y H:i:s"),
                        ];
                    }
                
                    $usrDatTemp = [
                        "marked"    => $marked,
                        "private"   => $private,
                        "favbadge"  => $favbadge,
                        "themes"    => $themes
                    ];
                    $encodedUsrDatTemp = json_encode($usrDatTemp, JSON_UNESCAPED_SLASHES);
                    $update_mkot = $connects->prepare("UPDATE profiles SET mkot = ? WHERE profileTags = ? ;");
                    $update_mkot->bind_param("ss", $encodedUsrDatTemp, $aidis);
                    $update_mkot->execute();
                    if ($update_mkot->affected_rows > 0) {
                        http_response_code(200);
                        die(json_encode([
                            "message" => $message,
                            "statMsg" => "Stats updated",
                            "newData" => $usrDatTemp
                            ]));
                    } else {
                        http_response_code(200);
                        die(json_encode([
                            "message" => $message,
                            "statMsg" => "Failed to update status",
                            "newData" => $usrDatTemp
                            ]));
                    }
                } else {
                    http_response_code(200);
                    die(json_encode([
                        "message" => $message,
                        "statMsg" => "Stats updated less than a minute ago"
                        ]));
                }
            } else {
                http_response_code(200);
                die(json_encode([
                    "message" => $message,
                    "statMsg" => "Error user missing default credentials"
                    ]));
            };
        } else {
            http_response_code(200);
            die(json_encode([
                "message" => $message,
                "statMsg" => "Current running software is not listed in collection library"
                ]));
        };
    }
}
?>
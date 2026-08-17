<?php
// when working on heart beat, I realized that the stats can be updated more properly 
// so this will and should not be deployed on prod but it is available on the repo in case your want to experiment with the stats
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!str_contains($providedKey, '.')) {
    http_response_code(401);
    die(json_encode([
        'message' => 'Invalid API key format'
    ]));
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
    if ($method != "POST") {
        die(json_encode(["message" => "Invalid request"]));
    }
    $targetLibsIds = $input['libsids'];
    $request = $input['request'];
    $sessiontokens = $input['tokens'];
    $addrss = $input['address'] ?? 'Unknown';
    $osids = $input['os'] ?? 'Unknown';
    if (!isset($sessiontokens) || !isset($addrss) || !isset($osids) || !isset($request) || !isset($targetLibsIds)) {
        http_response_code(401);
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

    $check_Libs = $connects->prepare("SELECT libsIds FROM libslist WHERE libsIds = ? AND libsState = 'publics';");
    $check_Libs->bind_param("s", $targetLibsIds);
    $check_Libs->execute();
    $result_check_Libs = $check_Libs->get_result();
    if ($result_check_Libs->num_rows == 1) {
        $value = $result_check_Libs->fetch_assoc();
        $new_marked[$value['libsIds']] = [
            "libsIds"  => $value['libsIds'],
            "Hours"    => 0,
            "lastLog"  => "notset"
        ];
    } else {
        http_response_code(404);
        die(json_encode(["message" => "The Collection does not exists"]));
    };
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
    } else {
        http_response_code(403);
        die(json_encode(["message" => "Error user missing default credentials"]));
    };
    
    if ($request === "updateHours") {
        if (empty($input['addedMinute']) || $input['addedMinute'] < 0) {
            http_response_code(403);
            die(json_encode(["message" => "total added minute cannot be 0"]));
        }
        $totalHours = (int)$input['addedMinute'] / 60;
        $totalHours = round($totalHours, 2);
        $totalHours = number_format($totalHours, 2, '.', '');
        if ($totalHours > 2) {
            http_response_code(403);
            die(json_encode(["message" => "total added minute cannot exceed two hour"]));
        }
        $marked = [];
        if (!empty($markedData) && $markedData != "empty") {
            foreach ($markedData as $markedIndex => $info) {
                if ($markedIndex != $targetLibsIds) {
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
        foreach ($new_marked as $new_mark) {
            if (!in_array($targetLibsIds, $marked)) {
                $newHours = $markedData[$targetLibsIds]['Hours'] + $totalHours;
                $newHours = round($newHours, 2);
                $marked[$targetLibsIds] = [
                    "libsIds"  => $targetLibsIds,
                    "Hours"    => $newHours,
                    "lastLog"  => date("d/m/Y H:i"),
                ];
            }
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
            die(json_encode([
                "message" => "Stats Updated",
                "newdata" => $usrDatTemp
                ]));
        } else {
            http_response_code(405);
            die(json_encode(["message" => "Failed to add to MarkOut"]));
        }
    } else {
        http_response_code(403);
        die(json_encode(["message" => "Request denied"]));
    }
}

?>
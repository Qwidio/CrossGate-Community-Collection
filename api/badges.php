<?php
require_once "../processes/database.php";
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
    if (!isset($input['type']) && !isset($input['target'])) {
        http_response_code(401);
        die(json_encode(["message" => "Invalid request, missing required input"]));
    }

    $type = $input['type']; // user, clts
    $target = $input['target']; // libsids, tokens
    $reservedArray = array();
    $tempGroupRefs= array();
    $currentOffset = 0;
    $currentIndex = 1;
    $totalCount = 0;
    $fetchImages = $input['fetch_images'] ?? false; 
    if ($type === "user") {
        $sessiontokens = $input['target'] ?? null;
        $addrss = $input['address'] ?? 'Unknown';
        $osids = $input['os'] ?? 'Unknown';
        if (!$sessiontokens) {
            die(json_encode(["message" => "Missing the required session token"]));
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
        
        $check_profile = $connects->prepare("SELECT profiles.Badge, profiles.mkot, user.userState 
        FROM profiles INNER JOIN user on profiles.profileTags = user.profileTags
        WHERE user.userState = 'approved' AND profiles.profileTags = ? ;");
        $check_profile->bind_param("s", $profileTags);
        $check_profile->execute();
        $result_check_profile = $check_profile->get_result();
        if ($result_check_profile->num_rows == 1) {
            $value = $result_check_profile->fetch_assoc();
            $mkot = $value['mkot'];
            $badgeArr = $value['Badge'];
            $badgeArr = json_decode($badgeArr, true);
            $data = json_decode($mkot, true);
            $markedData = $data['marked'];
            $privated = $data['private'];
            if ($privated == true) {
                $_SESSION['corsmsg'] = "user profile are privated";
                header ('location: ' . $prev_loc);
                exit;
            }
        } else {
            http_response_code(403);
            die(json_encode(["message" => "user account does not exists or on a temporary bans"]));
        }
        foreach ($badgeArr as $badgeIndex => $badgeValue) {
            $check_badges = $connects->prepare("SELECT badges.badgeName, badges.badgeDesc, badges.badgeType, badges.badgeRefs, badges.icon, badgegroup.state FROM badges
            INNER JOIN badgegroup ON badges.badgeRefs = badgegroup.groupRefs WHERE badgeIds = ? ;");
            $check_badges->bind_param("s", $badgeIndex);
            $check_badges->execute();
            $result_check_badges = $check_badges->get_result();
            if ($result_check_badges->num_rows > 0) {
                $value = $result_check_badges->fetch_assoc();
                if ($value['state'] === "publics") {
                    $badges["$badgeIndex"] = [
                        "badgesIds" => "$badgeIndex",
                        "badgeName" => $value['badgeName'],
                        "badgeDesc" => $value['badgeDesc'],
                        "badgeType" => $value['badgeType'],
                        "badgeRefs" => $value['badgeRefs'],
                        "badgeIcon" => $value['icon'],
                        "badgeDate" => $badgeValue
                    ];
                    if (!in_array($value['badgeRefs'], $tempGroupRefs)) {
                        $tempGroupRefs[] = $value['badgeRefs'];
                    }
                    $currentIndex++;
                    $totalCount++;
                }
            }
        }
        foreach ($tempGroupRefs as $tgrIndex) {
            $badgeList = array();
            $check_groupRefs = $connects->prepare("SELECT 
            groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, badgeList, icons
            FROM badgegroup WHERE groupRefs = ? ");
            $check_groupRefs->bind_param("s", $tgrIndex);
            $check_groupRefs->execute();
            $result_check_groupRefs = $check_groupRefs->get_result();
            if ($result_check_groupRefs->num_rows > 0) {
                while ($value = $result_check_groupRefs->fetch_assoc()) {
                    $tempBadgeList = json_decode($value['badgeList'], true);
                    foreach ($badges as $tgrbadgeListIndex => $tgrbadgeListVal) {
                        if (in_array($tgrbadgeListVal['badgesIds'], $tempBadgeList)) {
                            $badgeList[$tgrbadgeListVal['badgesIds']] = [
                                "badgesIds" => $badges[$tgrbadgeListVal['badgesIds']]['badgesIds'],
                                "badgeName" => $badges[$tgrbadgeListVal['badgesIds']]['badgeName'],
                                "badgeDesc" => $badges[$tgrbadgeListVal['badgesIds']]['badgeDesc'],
                                "badgeType" => $badges[$tgrbadgeListVal['badgesIds']]['badgeType'],
                                "badgeRefs" => $badges[$tgrbadgeListVal['badgesIds']]['badgeRefs'],
                                "badgeIcon" => $badges[$tgrbadgeListVal['badgesIds']]['badgeIcon'],
                                "badgeDate" => $badges[$tgrbadgeListVal['badgesIds']]['badgeDate']
                            ];
                        }
                    }
                    $reservedArray[$value['groupRefs']] = [
                        "libsIds"           => $value['libsIds'],
                        "badgeGroupTitle"   => $value['badgeGroupTitle'],
                        "badgeGroupDesc"    => $value['badgeGroupDesc'],
                        "badgeList"         => $badgeList,
                        "icons"             => $value['icons']
                    ];
                }
            }
        }
    } else if ($type === "clts") {
        $badgeGroups = array();
        $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs FROM libslist WHERE libsState = 'publics' AND libsIds = ? ;");
        $check_software->bind_param("s", $target);
        $check_software->execute();
        $result_check_software = $check_software->get_result();
        if ($result_check_software->num_rows > 0) {
            while ($value = $result_check_software->fetch_assoc()) {
                $libsIds = $value['libsIds'];
                $libsPublisher = $value['libsPublisher'];
                $libsAttachs = $value['libsAttachs'];
            };
            $check_groupRefs = $connects->prepare("SELECT groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, badgeList, icons FROM badgegroup WHERE libsIds = ? AND state = 'publics'");
            $check_groupRefs->bind_param("s", $libsIds);
            $check_groupRefs->execute();
            $result_check_groupRefs = $check_groupRefs->get_result();
            if ($result_check_groupRefs->num_rows > 0) {
                while ($value = $result_check_groupRefs->fetch_assoc()) {
                    if (!in_array($value['groupRefs'], $badgeGroups)) {
                        $badgeGroups[$value['groupRefs']] = [
                            "libsIds"           => $value['libsIds'],
                            "badgeGroupTitle"   => $value['badgeGroupTitle'],
                            "badgeGroupDesc"    => $value['badgeGroupDesc'],
                            "badgeList"         => json_decode($value['badgeList'], true),
                            "icons"             => $value['icons']
                        ];
                    }
                };
            }
            foreach ($badgeGroups as $bgIndex => $bgValue) {
                $check_badges = $connects->prepare("SELECT badges.badgeIds, badges.badgeName, badges.badgeDesc, badges.badgeType, badges.badgeRefs, badges.icon
                    FROM badges WHERE badgeType = 'achievement' AND badgeRefs = ? LIMIT 50;");
                $check_badges->bind_param("s", $bgIndex);
                $check_badges->execute();
                $result_check_badges = $check_badges->get_result();
                if ($result_check_badges->num_rows > 0) {
                    while($value = $result_check_badges->fetch_assoc()){
                        $badgeList[$value['badgeIds']] = [
                            "badgesIds" => $value['badgeIds'],
                            "badgeName" => $value['badgeName'],
                            "badgeDesc" => $value['badgeDesc'],
                            "badgeType" => $value['badgeType'],
                            "badgeRefs" => $value['badgeRefs'],
                            "badgeIcon" => $value['icon']
                        ];
                    }
                }
                $reservedArray["$bgIndex"] = [
                    "icons"             => $bgValue['icons'],
                    "libsIds"           => $bgValue['libsIds'],
                    "badgeGroupTitle"   => $bgValue['badgeGroupTitle'],
                    "badgeGroupDesc"    => $bgValue['badgeGroupDesc'],
                    "badgeList"         => $badgeList
                ];
            }
        } else {
            http_response_code(403);
            die(json_encode(["message" => "Collection data cannot be found"]));
        };
    }

    if (!empty($reservedArray)) {
        if ($fetchImages) {
            foreach ($reservedArray as $bgIndex => $bgValue) {
                $badgeList = $bgValue['badgeList'];
                $iconFile = $bgValue['icons'];
                $baseImgDir = "../ab/" . $bgIndex . "/";
                if (!empty($iconFile) && file_exists($baseImgDir . $iconFile)) {
                    $imgArray[$iconFile] = base64_encode(file_get_contents($baseImgDir . $iconFile));
                }
                foreach ($badgeList as $listIndex => $listValue) {
                    $badgeIcon = $listValue['badgeIcon'];
                    $badgeBaseImgDir = "../ab/" . $bgIndex . "/";
                    if (!empty($badgeIcon) && file_exists($badgeBaseImgDir . $badgeIcon)) {
                        $imgArray[$badgeIcon] = base64_encode(file_get_contents($badgeBaseImgDir . $badgeIcon));
                    }
                }
            }
        }
        if (!empty($imgArray)) {
            die(json_encode([
                "data" => $reservedArray,
                "img" => $imgArray
                ]
                ,JSON_UNESCAPED_SLASHES));
        } else {
            die(json_encode([
                "data" => $reservedArray
                ]
                ,JSON_UNESCAPED_SLASHES));
        }
    }
} else {
    http_response_code(403);
    die(json_encode(["message" => "Invalid request method"]));
}
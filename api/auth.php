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
function generateApiKey($length) {
    return bin2hex(random_bytes($length / 2));
}
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
$hashedKeys = $rca_val['hashedKeys'];
$apiState = $rca_val['active'];
$addedDate = $rca_val['addedDate'];
if (!hash_equals($hashedKeys, $secret)) {
    http_response_code(403);
    die(json_encode([
        'message' => 'Invalid API key'
    ]));
} else {
    header("Content-Type:application/json");
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    switch ($method) {
        case 'POST':
            $username = $input['username'];
            $password = $input['password'];
            if (empty($username) || empty($password)) {
                http_response_code(403);
                die(json_encode([
                    'message' => 'Missing Required data'
                ]));
            }
            $tokens = generateApiKey(64);
            $stmt_check_username = $connects->prepare("SELECT userState FROM user WHERE username = ?");
            $stmt_check_username->bind_param("s", $username);
            $stmt_check_username->execute();
            $result_check_username = $stmt_check_username->get_result();
            if ($result_check_username->num_rows == 1) {
                $value = $result_check_username->fetch_assoc();
                $current_state = $value['userState'];
                $state = "approved";
                if ($current_state != $state) {
                    http_response_code(403);
                    die(json_encode(["message" => "your account currently still in review"]));
                }
                $stmt_check_password = $connects->prepare("SELECT * FROM user WHERE username = ? AND password = MD5(?) AND userState = ?");
                $stmt_check_password->bind_param("sss", $username, $password, $state);
                $stmt_check_password->execute();
                $result_check_password = $stmt_check_password->get_result();
                if ($result_check_password->num_rows == 1) {
                    $value = $result_check_password->fetch_assoc();
                    $aidis = $value['profileTags'];
                    if (isset($input['sessionless'])) {
                        $check_session = $connects->prepare("SELECT sessiontokens FROM sessionlogs WHERE profileTags = ?;");
                        $check_session->bind_param("s", $aidis);
                        $check_session->execute();
                        $result_check_session = $check_session->get_result();
                        if ($result_check_session->num_rows > 2) {
                            http_response_code(403);
                            die(json_encode(["message" => "Your account exceeds the number of session allowed"]));
                        };
                        $tokens = generateApiKey(64);
                        $check_session = $connects->prepare("SELECT sessiontokens FROM sessionlogs WHERE sessiontokens = ?;");
                        $check_session->bind_param("s", $tokens);
                        $check_session->execute();
                        $result_check_session = $check_session->get_result();
                        if ($result_check_session->num_rows == 0) {
                            $addrss = getIpAddr();
                            $osids = $input['os'] ?? 'Unknown';
                            $expdate = date('Y/m/d', strtotime('+15 days'));
                            $convertedexpdate = DateTime::createFromFormat('Y/m/d', $expdate);
                            $unixexpdate = $convertedexpdate->getTimestamp();
                            $lastlogs = date('d/m/Y h:i');
                            $insert_session = $connects->prepare("INSERT INTO sessionlogs(profileTags, sessiontokens, addrss, osids, expirationDate, lastlogs) VALUES (?, ?, ?, ?, ?, ?)");
                            $insert_session->bind_param("ssssss", $aidis, $tokens, $addrss, $osids, $expdate, $lastlogs);
                            if($insert_session->execute()){
                                http_response_code(200);
                                $check_profile = $connects->prepare("SELECT * FROM profiles WHERE profileTags = ? ;");
                                $check_profile->bind_param("s", $aidis);
                                $check_profile->execute();
                                $result_check_profile = $check_profile->get_result();
                                $value = $result_check_profile->fetch_assoc();
                                if ($value) {
                                    die(json_encode([
                                        "message" => "Login Successful",
                                        "clientDev" => $og_identification,
                                        "sessionToken" => $tokens,
                                        "unixexpdate" => $unixexpdate,
                                        "profileTags" => $aidis,
                                        "profileAttachs"=> $value['profileAttachs'],
                                        "profileNames"  => $value['profileNames'],
                                        "profileBios"   => $value['profileBios'],
                                        "profileJDates" => $value['profileJDates'],
                                        "Badge"         => $value['Badge'],
                                        "mkot"          => $value['mkot'],
                                        "oState"        => $value['oState']
                                    ], JSON_UNESCAPED_SLASHES));
                                }
                            }else{
                                http_response_code(401);
                                die(json_encode([
                                    "message" => "Failed to add new sessions"
                                ]));
                            };
                            $insert_session->close();
                        }
                        $check_session->close();
                    } else {
                        http_response_code(200);
                        $check_profile = $connects->prepare("SELECT * FROM profiles WHERE profileTags = ? ;");
                        $check_profile->bind_param("s", $aidis);
                        $check_profile->execute();
                        $result_check_profile = $check_profile->get_result();
                        $value = $result_check_profile->fetch_assoc();
                        if ($value) {
                            die(json_encode([
                                "message" => "Login Successful",
                                "profileTags" => $aidis,
                                "profileAttachs"=> $value['profileAttachs'],
                                "profileNames"  => $value['profileNames'],
                                "profileBios"   => $value['profileBios'],
                                "profileJDates" => $value['profileJDates'],
                                "Badge"         => $value['Badge'],
                                "mkot"          => $value['mkot'],
                                "oState"        => $value['oState']
                            ], JSON_UNESCAPED_SLASHES));
                        }
                    }
                } else {
                    http_response_code(403);
                    die(json_encode([
                        'message' => 'Password Invalid, try again'
                    ]));
                }
                $stmt_check_password->close();
            } else {
                http_response_code(404);
                die(json_encode([
                    'message' => 'User data cannot be found'
                ]));
            }
            $stmt_check_username->close();
            break;
        case 'PUT':
            $sessiontokens = $input['tokens'];
            $addrss = getIpAddr();
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
                $Tags = $data['profileTags'];
                $savedOS = $data['osids'];
                $oldaddrss = $data['addrss'];
                $exps = $data['expirationDate'];
                $curdt = date('Y/m/d');
                if ($exps < $curdt) {
                    http_response_code(403);
                    die(json_encode(["message" => "Session Have been expired"]));
                }
                if ($osids != $savedOS && $savedOS != "unset") {
                    http_response_code(403);
                    die(json_encode(["message" => "Sessions already used on another device"]));
                }
                if ($oldaddrss !== $addrss) {
                    http_response_code(403);
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
                $check_profile = $connects->prepare("SELECT * FROM profiles WHERE profileTags = ? ;");
                $check_profile->bind_param("s", $Tags);
                $check_profile->execute();
                $result_check_profile = $check_profile->get_result();
                $value = $result_check_profile->fetch_assoc();
                if ($value) {
                    http_response_code(200);
                    echo json_encode([
                        "message"       => "Logged in successfully",
                        "profileTags"   => $Tags,
                        "profileAttachs"=> $value['profileAttachs'],
                        "profileNames"  => $value['profileNames'],
                        "profileBios"   => $value['profileBios'],
                        "profileJDates" => $value['profileJDates'],
                        "Badge"         => $value['Badge'],
                        "mkot"          => $value['mkot'],
                        "oState"        => $value['oState']
                    ], JSON_UNESCAPED_SLASHES);
                }
                exit;
            }
            break;
        default:
            echo json_encode(["message" => "Invalid request"]);
            break;
    }
    $connects->close();
}
?>
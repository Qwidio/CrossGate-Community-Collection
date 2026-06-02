<?php
// require_once "../processes/database.php";

// $vercode = $_GET['vercode'];
// if (isset($vercode)) {
//     header("Content-Type:application/json");
//     $method = $_SERVER['REQUEST_METHOD'];
//     $input = json_decode(file_get_contents('php://input'), true);
//     switch ($method) {
//         case 'POST':
//             $username = $input['username'];
//             $password = $input['password'];
//             if (!isset($username) || !isset($password) || !isset($addrss) || !isset($osids)) {
//                 echo json_encode(["message" => "Missing Required data"]);
//                 exit;
//             }
//             function generateApiKey($length) {
//                 return bin2hex(random_bytes($length / 2));
//             }
//             $tokens = generateApiKey(64);

//             break;
//         case 'PUT':
//             $sessiontokens = $input['tokens'];
//             $addrss = $input['addrss'];
//             $osids = $input['osids'];
//             if (!isset($sessiontokens) || !isset($addrss) || !isset($osids)) {
//                 echo json_encode(["message" => "Missing Required data"]);
//                 exit;
//             }
//             $session_check = $connects->prepare("SELECT profileTags, osids, expirationDate FROM sessionlogs WHERE sessiontokens = ?;");
//             $session_check->bind_param("s", $sessiontokens);
//             $session_check->execute();
//             $result_session_check = $session_check->get_result();
//             $data = $result_session_check->fetch_assoc();
//             if (isset($data)) {
//                 $Tags = $data['profileTags'];
//                 $saveddevc = $data['osids'];
//                 $exps = $data['expirationDate'];
//                 $curdt = date('m/d/Y');
//                 if ($exps < $curdt) {
//                     echo json_encode(["message" => "Session Have been expired"]);
//                     exit;
//                 }
//                 if ($osids != $saveddevc && $saveddevc != "unset") {
//                     echo json_encode(["message" => "Sessions already used on another device"]);
//                     exit;
//                 }
//             } else {
//                 echo json_encode(["message" => "Failed to find sessions"]);
//                 exit;
//             }
//             $update_auth = $connects->prepare("UPDATE sessionlogs SET addrss = ?, osids = ?, lastlogs = NOW() WHERE sessiontokens = ? ;");
//             $update_auth->bind_param("sss", $addrss, $osids, $sessiontokens);
//             $update_auth->execute();
//             if ($update_auth) {
//                 $check_profile = $connects->prepare("SELECT * FROM profiles WHERE profileTags = ? ;");
//                 $check_profile->bind_param("s", $Tags);
//                 $check_profile->execute();
//                 $result_check_profile = $check_profile->get_result();
//                 $value = $result_check_profile->fetch_assoc();
//                 if ($value) {
//                     echo json_encode([
//                         "message"       => "Logged in successfully",
//                         "profileTags"   => $Tags,
//                         "profileAttachs"=> $value['profileAttachs'],
//                         "profileNames"  => $value['profileNames'],
//                         "profileBios"   => $value['profileBios'],
//                         "profileJDates" => $value['profileJDates'],
//                         "Badge"         => $value['Badge'],
//                         "mkot"          => $value['mkot'],
//                         "oState"        => $value['oState']
//                     ], JSON_UNESCAPED_SLASHES);
//                 }
//                 exit;
//             }
//             break;
//         default:
//             echo json_encode(["message" => "Invalid request"]);
//             break;
//     }
//     $connects->close();
// } else {
//     echo json_encode(["message" => "Invalid Code"]);
// }
?>
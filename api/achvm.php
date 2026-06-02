<?php
// require_once "../processes/database.php";

// $connect_token = $_GET['tokens'];
// if (isset($connect_token)) {
//     header("Content-Type:application/json");
//     $method = $_SERVER['REQUEST_METHOD'];
//     $input = json_decode(file_get_contents('php://input'), true);
//     switch ($method) {
//         case 'GET':
//             if (isset($_GET['profileTags'])) {
//                 $profileTags = $_GET['profileTags'];
//                 $stmt_check = $connects->prepare("SELECT * FROM achieverlist WHERE profileTags = ?;");
//                 $stmt_check->bind_param("s", $profileTags);
//                 $stmt_check->execute();
//                 $result_check = $stmt_check->get_result();
//                 if ($result_check->num_rows > 0) {
//                     $uniques = [];
//                     while ($value = $result_check->fetch_assoc()) {
//                         $achieverIds = $value['achieverIds'];
//                         if (!in_array($achieverIds, $uniques)) {
//                             echo json_encode($value);
//                         }
//                     }
//                 }
//             } else {
//                 echo json_encode(["message" => "Error: Missing prequisite"]);
//             }
//             break;
//         case 'POST':
//             $achieverIds = random_bytes(100);
//             $libsIds = $input['libsIds'];
//             $achievementIds = $input['achievementIds'];
//             $profileTags = $input['profileTags'];
//             if (isset($libsIds)) {
//                 $result = $connects->query("SELECT * FROM achievement WHERE libsIds = '$libsIds';");
//                 $data = $result->fetch_assoc();
//                 if (isset($data)) {
//                     $achievementName = $data['achievementName'];
//                     $stmt_insert = $connects->prepare("INSERT INTO achieverlist (achieverIds, libsIds, profileTags, achievementIds, achievementName, dates) VALUES (md5(?), ?, ?, ?, ?, NOW())");
//                     $stmt_insert->bind_param("sssss", $achieverIds, $libsIds, $profileTags, $achievementIds, $achievementName);
//                     if ($stmt_insert->execute()) {
//                         echo json_encode(["message" => "achievement added successfully"]);
//                     }
//                 } else {
//                 echo json_encode(["message" => "Error: Failed to find achievement"]);
//                 }
//             } else {
//                 echo json_encode(["message" => "Error: Missing data value"]);
//             }
//             break;
//         default:
//             echo json_encode(["message" => "Error: Invalid request"]);
//             break;
//     }
//     $connects->close();
// } else {
//     echo json_encode(["message" => "ERROR"]);
// }
?>
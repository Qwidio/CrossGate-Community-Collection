<?php
// $gids = $_GET['gid'];
// $filename = $_GET['pid'];
// $vercode = $_GET['vercode'];
// if (isset($vercode) || isset($gids) || isset($filename)) {
//     $file_path = '../vaults/' . $gids . '/' . $filename;
//     if (!file_exists($file_path) || !is_readable($file_path)) {
//         http_response_code(404);
//         echo json_encode(["message" => "Error: File not found"]);
//         exit;
//     }
//     while (ob_get_level()) {
//         ob_end_clean();
//     }

//     $file_name = basename($file_path);
//     $file_size = filesize($file_path);
//     header('Content-Type: application/octet-stream');
//     header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode($file_name));
//     header('Content-Length: ' . $file_size);
//     header('Cache-Control: no-cache');
//     header('Pragma: public');

//     readfile($file_path);
//     $connects->close();
//     exit;
// } else {
//     echo json_encode(["message" => "Invalid Code"]);
//     exit;
// }
?>
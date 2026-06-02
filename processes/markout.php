<?php
require_once 'database.php';
$root_route = "../";
require_once '../secureSession.php';
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    header ('location: ../Library/core/markout.php');
    exit;
};
if (isset($_POST['libsIds']) && isset($_POST['MarkOut'])) {
    $check_Libs = $connects->prepare("SELECT libsIds FROM libslist WHERE libsIds = ? AND libsState = 'publics';");
    $check_Libs->bind_param("s", $_POST['libsIds']);
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
        $_SESSION['corsmsg'] = "The Collection does not exists";
        header ('location: ../Library/core/markout.php');
        exit;
    };
} else {
    $_SESSION['corsmsg'] = "Request denied";
    header ('location: ../Library/core/markout.php');
    exit;
}
$check_profile = $connects->prepare("SELECT mkot FROM profiles WHERE profileTags = ? ;");
$check_profile->bind_param("s", $aidis);
$check_profile->execute();
$result_check_profile = $check_profile->get_result();
if ($result_check_profile->num_rows == 1) {
    $value = $result_check_profile->fetch_assoc();
    $mkot = $value['mkot'];
    $data = json_decode($mkot, true);
    $ltlnData = $data['lastLogin'];
    $markedData = $data['marked'];
    $private = $data['private'];
};
$marked = [];
if (!empty($markedData) && $markedData != "empty") {
    foreach ($markedData as $markedIndex => $info) {
        $marked[$markedIndex] = [
            "libsIds"  => $info['libsIds'],
            "Hours"    => (int)$info['Hours'],
            "lastLog"  => $info['lastLog'],
        ];
    }
}
foreach ($new_marked as $new_mark) {
    if (!in_array($new_mark['libsIds'], $marked)) {
        $marked[$new_mark['libsIds']] = [
            "libsIds"  => $new_mark['libsIds'],
            "Hours"    => (int)$new_mark['Hours'],
            "lastLog"  => $new_mark['lastLog'],
        ];
    }
}
$usrDatTemp = [
    "lastLogin" => $ltlnData,
    "marked"    => $marked,
    "private"   => $private
];
$usrDatTemp = json_encode($usrDatTemp, JSON_UNESCAPED_SLASHES);
$update_mkot = $connects->prepare("UPDATE profiles SET mkot = ? WHERE profileTags = ? ;");
$update_mkot->bind_param("ss", $usrDatTemp, $aidis);
$update_mkot->execute();
if ($update_mkot->affected_rows > 0) {
    $_SESSION['corsmsg'] = "MarkedOut!";
    header ('location: ../Library/core/markout.php');
    exit;
} else {
    $_SESSION['corsmsg'] = "Failed to add to MarkOut";
    header ('location: ../Library/core/markout.php');
    exit;
}

?>
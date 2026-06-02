<?php
if (!isset($root_route) || empty($root_route) || $root_route === "") {
    $root_route = "";
}
require_once $root_route . 'processes/database.php';
if (isset($_COOKIE['sessionToken'])) {
    $session_check = $connects->prepare("SELECT profileTags, osids, expirationDate FROM sessionlogs WHERE sessiontokens = ?;");
    $session_check->bind_param("s", $_COOKIE['sessionToken']);
    $session_check->execute();
    $result_session_check = $session_check->get_result();
    $data = $result_session_check->fetch_assoc();
    if (isset($data)) {
        $Tags = $data['profileTags'];
        $saveddevc = $data['osids'];
        $exps = $data['expirationDate'];
        $curdt = date('m/d/Y');
        if ($exps < $curdt) {
            unset($_COOKIE['sessionToken']);
            setcookie('sessionToken', '', 1, "/",);
            $_SESSION['corsmsg'] = "Session Have been expired";
            header ("location:" . $root_route . "connect_it/connect_it.php");
            exit;
        }
    } else {
        unset($_COOKIE['sessionToken']);
        setcookie('sessionToken', '', 1, "/",);
        $_SESSION['corsmsg'] = "failed to find sessions";
        header ("location:" . $root_route . "connect_it/connect_it.php");
        exit;
    }
    $newlastlog = date('d/m/Y h:i');
    $update_auth = $connects->prepare("UPDATE sessionlogs SET lastlogs = ? WHERE sessiontokens = ? ;");
    $update_auth->bind_param("ss", $newlastlog, $_COOKIE['sessionToken']);
    $update_auth->execute();
    if ($update_auth) {
        unset($_SESSION["profileTags"]);
        $check_profile = $connects->prepare("SELECT profileTags FROM user WHERE profileTags = ? AND userState = 'approved';");
        $check_profile->bind_param("s", $Tags);
        $check_profile->execute();
        $result_check_profile = $check_profile->get_result();
        $profile_data = $result_check_profile->fetch_assoc();
        if ($profile_data) {
            $_SESSION['profileTags'] = $profile_data['profileTags'];
            $aidis = $_SESSION['profileTags'];
        } else {
            unset($_COOKIE['sessionToken']);
            setcookie('sessionToken', '', 1, "/",);
            $_SESSION['corsmsg'] = "unknown user tried to get logged in";
            header ("location:" . $root_route . "connect_it/connect_it.php");
            exit;
        }
    }
}
?>
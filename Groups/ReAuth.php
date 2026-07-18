<?php
require_once '../processes/database.php';
if (!isset($root_route) || empty($root_route) || $root_route === "") {
    $root_route = "";
}
if (isset($_SESSION['GroupsToken'])) {
    $session_check = $connects->prepare("SELECT profileTags, og_identification, osids, expirationDate FROM groupsession WHERE token = ?;");
    $session_check->bind_param("s", $_SESSION['GroupsToken']);
    $session_check->execute();
    $result_session_check = $session_check->get_result();
    $data = $result_session_check->fetch_assoc();
    if (isset($data)) {
        $Tags = $data['profileTags'];
        $gids = $data['og_identification'];
        $saveddevc = $data['osids'];
        $exps = $data['expirationDate'];
        $curdt = date('Y/m/d');
        if ($exps < $curdt) {
            unset($_SESSION['GroupsToken']);
            unset($_SESSION["gids"]);
            unset($_SESSION["roles"]);
            $_SESSION['corsmsg'] = "Session Have been expired";
            header ("location:" . $root_route . "Groups/index.php");
            exit;
        }
    } else {
        unset($_SESSION['GroupsToken']);
        unset($_SESSION["gids"]);
        unset($_SESSION["roles"]);
        $_SESSION['corsmsg'] = "failed to find sessions";
        header ("location:" . $root_route . "Groups/index.php");
        exit;
    }
    $newlastlog = date('d/m/Y h:i');
    $update_auth = $connects->prepare("UPDATE groupsession SET lastlogs = ? WHERE token = ? ;");
    $update_auth->bind_param("ss", $newlastlog, $_SESSION['GroupsToken']);
    $update_auth->execute();
    if ($update_auth) {
        unset($_SESSION["gids"]);
        $check_groups = $connects->prepare("SELECT passkeys, og_identification, roles FROM groupaccess WHERE og_identification = ? AND profileTags = ? ");
        $check_groups->bind_param("ss", $gids, $Tags);
        $check_groups->execute();
        $result_check_groups = $check_groups->get_result();
        $profile_data = $result_check_groups->fetch_assoc();
        if ($profile_data) {
            if ($profile_data['passkeys'] != "unset") {
                $_SESSION['resetPass'] = false;
            }
            $_SESSION['gids'] = $profile_data['og_identification'];
            $_SESSION['roles'] = $profile_data['roles'];
        } else {
            unset($_SESSION['GroupsToken']);
            $_SESSION['corsmsg'] = "unknown user using active token";
            header ("location:" . $root_route . "Groups/index.php");
            exit;
        }
    }
}
?>
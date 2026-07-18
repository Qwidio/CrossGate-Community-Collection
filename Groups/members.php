<?php
require_once '../processes/database.php';
if (isset($_POST['submit'])) {
    $errors = array();
    // default security stuff
    $root_route = "../";
    require_once '../secureSession.php';
    require_once 'ReAuth.php';
    if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
        $aidis = $_SESSION['profileTags'];
        $gToken = $_SESSION['GroupsToken'];
    } else {
        $_SESSION['corsmsg'] = "denied access";
        header ('location: ../index.php');
        exit;
    }
    $gids = $_SESSION['gids'];
    // verifying the input
    $profileTags = $_POST['profiletags'];
    $getUser = $connects->prepare("SELECT profileNames FROM profiles WHERE profileTags = ? ;");
    $getUser->bind_param("s", $profileTags);
    $getUser->execute();
    $resultGetUser = $getUser->get_result();
    if ($resultGetUser->num_rows == 0) { 
        $_SESSION['corsmsg'] = 'unidentified user tried to be added';
        header ('location: ../index.php');
        exit;
    }
    $prebind = '"' . $aidis . '"';
    $check_orgs = $connects->prepare("SELECT members FROM ogroup WHERE identification = ? AND founder = ? OR JSON_CONTAINS(members, ?);");
    $check_orgs->bind_param("sss", $gids, $aidis, $prebind);
    $check_orgs->execute();
    $result_check_orgs = $check_orgs->get_result();
    if ($result_check_orgs->num_rows > 0) {
        while ($value = $result_check_orgs->fetch_assoc()) {
            $members = json_decode($value['members'], true);
        }
    } else {
        $_SESSION['corsmsg'] = "You are not allowed to make group changes";
        header('location: ../index.php');
        exit;
    }
    $initReq = $_POST['submit'];
    // adding/invite new member
    if ($initReq === "Add") {
        $roles = $_POST['roles']; 
        $stmt_insert_access = $connects->prepare("INSERT INTO groupaccess (profileTags, passkeys, roles, og_identification, accountState) VALUES (?, 'unset', ?, ?, 'invited');");
        $stmt_insert_access->bind_param("sss", $profileTags, $roles, $gids);
        if ($stmt_insert_access->execute()) {
            $randKey = bin2hex(random_bytes(64));
            if (isset($_POST['custom_msg']) || $_POST['custom_msg'] != "") {
                $custom_msg = $_POST['custom_msg'];
            } else {
                $custom_msg = "0";
            }
            $stmt_insert_invite = $connects->prepare("INSERT INTO groupinvite (inviteToken, profileTags, og_identification, custom_msg) VALUES (?, ?, ?, ?);");
            $stmt_insert_invite->bind_param("ssss", $randKey, $profileTags, $gids, $custom_msg);
            if ($stmt_insert_invite->execute()) {
                $_SESSION['corsmsg'] = "successfully invite new member";
                header ('location: manage.php');
                exit;
            } else {
                $_SESSION['corsmsg'] = 'Failed to send group invite. ' . $stmt_insert_invite->error;
                header ('location: manage.php');
                $stmt_insert_invite->close();
                exit;
            }
        } else {
            $_SESSION['corsmsg'] = 'Failed to add group access. ' . $stmt_insert_access->error;
            header ('location: manage.php');
            $stmt_insert_access->close();
            exit;
        }
    //  revoke access
    } else if ($initReq === "Revoke") {
        $check_access = $connects->prepare("SELECT roles FROM groupaccess WHERE profileTags = ? AND og_identification = ?;");
        $check_access->bind_param("ss", $profileTags, $gids);
        $check_access->execute();
        $result_check_access = $check_access->get_result();
        if ($result_check_access->num_rows > 0) {
            $temp_check_access_val = $result_check_access->fetch_assoc();
            $final_roles = $temp_check_access_val['roles'];
            if (isset($_POST['roles']) && $_POST['roles'] != $final_roles) {
                $final_roles = $_POST['roles'];
            }
            $stmt_revoke_access = $connects->prepare("UPDATE groupaccess set roles = ? WHERE profileTags = ? AND og_identification = ?;");
            $stmt_revoke_access->bind_param("sss", $final_roles, $profileTags, $gids);
            $stmt_revoke_access->execute();
            if ($stmt_revoke_access->affected_rows > 0) {
                $_SESSION['corsmsg'] = "successfully changed members access";
                header ('location: manage.php');
                exit;
            } else {
                $_SESSION['corsmsg'] = 'Failed to revoke access. ' . $stmt_revoke_access->error;
                header ('location: manage.php');
                $stmt_revoke_access->close();
                exit;
            }
        } else {
            $_SESSION['corsmsg'] = "Failed check member access. " . $result_check_access->error;
            header ('location: manage.php');
            exit;
         }
    //  removing member
    } else if ($initReq === "REMOVE") {
        $profilename = $_POST['pname']; 
        $newMembers = array();
        foreach ($members as $member) {
            if ($member != $profileTags) {
                $newMembers[] = $member;
            }
        }
        $check_access = $connects->prepare("SELECT roles FROM groupaccess WHERE profileTags = ? AND og_identification = ?;");
        $check_access->bind_param("ss", $profileTags, $gids);
        $check_access->execute();
        $result_check_access = $check_access->get_result();
        if ($result_check_access->num_rows > 0) {
            $temp_check_access_val = $result_check_access->fetch_assoc();
            $final_roles = $temp_check_access_val['roles'];
            if (isset($_POST['roles']) && $_POST['roles'] != $final_roles) {
                $Roles = $_POST['roles'];
            }
            if ($Roles === "founder") {
                $_SESSION['corsmsg'] = "Cannot delete founder account";
                header ('location: manage.php');
                exit;
            }
            $stmt_revoke_access = $connects->prepare("UPDATE groupaccess set accountState = 'deleted' WHERE profileTags = ? AND og_identification = ?;");
            $stmt_revoke_access->bind_param("ss", $profileTags, $gids);
            $stmt_revoke_access->execute();
            if ($stmt_revoke_access->affected_rows > 0) {
                $newMembers = json_encode($newMembers, JSON_UNESCAPED_SLASHES);
                $updateMember = $connects->prepare("UPDATE ogroup SET members = ? WHERE identification = ? ;");
                $updateMember->bind_param("ss", $newMembers, $gids);
                $updateMember->execute();
                if ($updateMember->affected_rows > 0) {
                    $_SESSION['corsmsg'] = "removed " . $profilename;
                    header ('location: manage.php');
                    exit;
                } else {
                    $_SESSION['corsmsg'] = "Failed to remove " . $profilename . ". " . $addMember->error;
                    header ('location: manage.php');
                    exit;
                }
            } else {
                $_SESSION['corsmsg'] = 'Failed to revoke access. ' . $stmt_revoke_access->error;
                header ('location: manage.php');
                $stmt_revoke_access->close();
                exit;
            }
        } else {
            $_SESSION['corsmsg'] = "Failed check member access. " . $result_check_access->error;
            header ('location: manage.php');
            exit;
        }
    }
} else {
    header ('location: manage.php');
    exit;
};
?>
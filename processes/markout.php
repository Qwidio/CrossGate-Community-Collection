<?php
require_once 'database.php';
$root_route = "../";
require_once '../secureSession.php';
if (!isset($_SESSION['profileTags'])) {
    header('Location: ../index.php');
    exit;
}
$aidis  = $_SESSION['profileTags'];
$libsIds = $_POST['libsids'] ?? '';
$action = $_POST['MarkOut'] ?? '';
if ($libsIds === '' || !in_array($action, ['MarkOut', 'Remove'], true)) {
    $_SESSION['corsmsg'] = 'Request denied';
    header('Location: ../Library/core/markout.php');
    exit;
}
try {
    // This prevents two parallel requests from modifying at the same time, I'm sure it will not stop the pen test from breaking it
    $connects->begin_transaction();
    $stmt = $connects->prepare("SELECT libsIds FROM libslist WHERE libsIds = ? AND libsState = 'publics' FOR UPDATE");
    $stmt->bind_param("s", $libsIds);
    $stmt->execute();
    $libraryResult = $stmt->get_result();
    if ($libraryResult->num_rows !== 1) {
        throw new Exception('Cannot find collection tried be added.');
    }

    $check_profile = $connects->prepare("SELECT mkot FROM profiles WHERE profileTags = ? FOR UPDATE;");
    $check_profile->bind_param("s", $aidis);
    $check_profile->execute();
    $profileResult = $check_profile->get_result();
    if ($profileResult->num_rows !== 1) {
        throw new Exception('User profile inexistent.');
    }
    $profile = $profileResult->fetch_assoc();
    $data = json_decode($profile['mkot'], true);
    if (!is_array($data)) {
        throw new Exception('Invalid profile data.');
    }
    $marked = [];
    if (isset($data['marked']) && is_array($data['marked'])) {
        foreach ($data['marked'] as $info) {
            if (!is_array($info) || !isset($info['libsIds'])) {
                continue;
            }
            $id = (string) $info['libsIds'];
            $marked[$id] = [
                'libsIds' => $id,
                'Hours'   => (int) ($info['Hours'] ?? 0),
                'lastLog' => $info['lastLog'] ?? 'notset'
            ];
        }
    }

    $alreadyMarked = isset($marked[$libsIds]);
    $counterChanged = false;
    if ($action === 'MarkOut') {
        if (!$alreadyMarked) {
            $marked[$libsIds] = [
                'libsIds' => $libsIds,
                'Hours'   => 0,
                'lastLog' => 'notset'
            ];

            $stmt = $connects->prepare(
                "UPDATE libslist
                 SET cltNumbs = COALESCE(cltNumbs, 0) + 1
                 WHERE libsIds = ?
                   AND libsState = 'publics'"
            );

            $stmt->bind_param("s", $libsIds);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                throw new Exception('Collection count cloud not get updated.');
            }

            $counterChanged = true;
        }
        $Messages = 'MarkedOut!';

    } elseif ($action === 'Remove') {
        if ($alreadyMarked) {
            unset($marked[$libsIds]);
            $stmt = $connects->prepare(
                "UPDATE libslist
                 SET cltNumbs = GREATEST(COALESCE(cltNumbs, 0) - 1, 0)
                 WHERE libsIds = ?
                   AND libsState = 'publics'"
            );
            $stmt->bind_param("s", $libsIds);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                throw new Exception('Collection count cloud not get updated.');
            }
            $counterChanged = true;
        }
        $Messages = 'Removed From MarkOut.';
    }
    // $data['marked'] = $marked;
    // if (!array_key_exists('private', $data)) {
    //     $data['private'] = [];
    // }
    // if (!array_key_exists('favbadge', $data)) {
    //     $data['favbadge'] = [];
    // }
    // if (!array_key_exists('themes', $data)) {
    //     $data['themes'] = [];
    // }
    // if (!array_key_exists('borders', $data)) {
    //     $data['borders'] = [];
    // }
    $data = [
        "marked"    => $marked,
        "private"   => $private,
        "favbadge"  => $favbadge,
        "themes"    => $themes,
        "borders"    => $borders
    ];
    $newMkot = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $stmt = $connects->prepare("UPDATE profiles SET mkot = ? WHERE profileTags = ?");
    $stmt->bind_param("ss", $newMkot, $aidis);
    $stmt->execute();
    $connects->commit();
    $_SESSION['corsmsg'] = $Messages;
} catch (Throwable $e) {
    $connects->rollback();
    error_log($e->getMessage());
    $_SESSION['corsmsg'] = 'could not be complete MarkingOut.';
}

header('Location: ../Library/core/markout.php');
exit;

?>

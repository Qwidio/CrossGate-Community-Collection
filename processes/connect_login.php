<?php
require_once 'database.php';
function redirectTo(string $location): never
{
    header('Location: ' . $location);
    exit;
}
function getSafePreviousLocation(): string
{
    $location = (string)($_SESSION['prev_loc'] ?? 'index.php');
    if (
        $location === '' ||
        str_contains($location, "\r") ||
        str_contains($location, "\n") ||
        str_starts_with($location, '/') ||
        str_contains($location, '://') ||
        str_contains($location, '..')
    ) {
        return 'index.php';
    }
    return ltrim($location, '/');
}
function getIpAddr(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    return filter_var($ip, FILTER_VALIDATE_IP)
        ? $ip
        : '0.0.0.0';
}

if (!isset($_POST['Login'])) {
    exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$plainPassword = (string)($_POST['password'] ?? '');
if ($username === '' || $plainPassword === '') {
    $_SESSION['corsmsg'] = 'Missing credentials';
    redirectTo('../connect_it/connect_it.php?state=login');
}
$stmt = $connects->prepare(
    'SELECT UserID, profileTags, username, password, userState
     FROM user
     WHERE username = ?
     LIMIT 1'
);
if (!$stmt) {
    error_log('Login prepare error: ' . $connects->error);
    $_SESSION['corsmsg'] = 'Login failed';
    redirectTo('../connect_it/connect_it.php?state=login');
}
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
if (!$user) {
    $_SESSION['corsmsg'] = 'Invalid username or password';
    redirectTo('../connect_it/connect_it.php?state=login');
}
if ($user['userState'] !== 'approved') {
    $_SESSION['corsmsg'] = 'Your account is currently still in review';
    redirectTo('../connect_it/connect_it.php?state=login');
}

$storedPassword = (string)$user['password'];
$passwordIsValid = false;
$needsUpgrade = false;
if (
    str_starts_with($storedPassword, '$2y$') ||
    str_starts_with($storedPassword, '$2b$') ||
    str_starts_with($storedPassword, '$argon2')
) {
    $passwordIsValid = password_verify($plainPassword, $storedPassword);
    if (
        $passwordIsValid &&
        password_needs_rehash($storedPassword, PASSWORD_DEFAULT)
    ) {
        $needsUpgrade = true;
    }
} else {
    $legacyMd5 = md5($plainPassword);
    if (
        strlen($storedPassword) === 32 &&
        hash_equals(strtolower($storedPassword), $legacyMd5)
    ) {
        $passwordIsValid = true;
        $needsUpgrade = true;
    }
}

if (!$passwordIsValid) {
    $_SESSION['corsmsg'] = 'Invalid username or password';
    redirectTo('../connect_it/connect_it.php?state=login');
}

if ($needsUpgrade) {
    $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    if ($newHash === false) {
        error_log('Password rehash failed for user ID ' . $user['UserID']);
        $_SESSION['corsmsg'] = 'Login failed';
        redirectTo('../connect_it/connect_it.php?state=login');
    }

    $update = $connects->prepare(
        'UPDATE user SET password = ? WHERE UserID = ?'
    );
    if (!$update) {
        error_log('Password update prepare error: ' . $connects->error);
        $_SESSION['corsmsg'] = 'Login failed';
        redirectTo('../connect_it/connect_it.php?state=login');
    }

    $userId = (int)$user['UserID'];
    $update->bind_param('si', $newHash, $userId);
    $update->execute();
    $update->close();
}
session_regenerate_id(true);

$profileTags = $user['profileTags'];
$previousLocation = getSafePreviousLocation();
if (!isset($_POST['sessionless'])) {
    $_SESSION['profileTags'] = $profileTags;
    $_SESSION['corsmsg'] = 'Login successful';
    redirectTo('../' . $previousLocation);
}

$checkSession = $connects->prepare(
    'SELECT COUNT(*) AS total
     FROM sessionlogs
     WHERE profileTags = ?
       AND expirationDate >= ?'
);
if (!$checkSession) {
    error_log('Session check prepare error: ' . $connects->error);
    $_SESSION['corsmsg'] = 'Unable to create session';
    redirectTo('../connect_it/connect_it.php?state=login');
}

$today = date('Y/m/d');
$checkSession->bind_param('ss', $profileTags, $today);
$checkSession->execute();
$sessionResult = $checkSession->get_result();
$sessionData = $sessionResult->fetch_assoc();
$checkSession->close();
if ((int)($sessionData['total'] ?? 0) >= 3) {
    $_SESSION['corsmsg'] = 'Your account exceeds the allowed number of sessions';
    redirectTo('../connect_it/connect_it.php?state=login');
}

$token = bin2hex(random_bytes(32));
$ipAddress = getIpAddr();
$clientOs = trim((string)($_POST['os'] ?? 'Unknown'));
$clientOs = mb_substr($clientOs, 0, 100);
$expirationDate = date('Y/m/d', strtotime('+15 days'));
$lastLog = date('d/m/Y H:i');
$insertSession = $connects->prepare(
    'INSERT INTO sessionlogs
        (profileTags, sessiontokens, addrss, osids, expirationDate, lastlogs)
     VALUES (?, ?, ?, ?, ?, ?)'
);

if (!$insertSession) {
    error_log('Session insert prepare error: ' . $connects->error);
    $_SESSION['corsmsg'] = 'Unable to create session';
    redirectTo('../connect_it/connect_it.php?state=login');
}

$insertSession->bind_param(
    'ssssss',
    $profileTags,
    $token,
    $ipAddress,
    $clientOs,
    $expirationDate,
    $lastLog
);
if (!$insertSession->execute()) {
    error_log('Session insert error: ' . $insertSession->error);
    $insertSession->close();

    $_SESSION['corsmsg'] = 'Unable to create session';
    redirectTo('../connect_it/connect_it.php?state=login');
}
$insertSession->close();

$cookieOptions = [
    'expires'  => time() + (15 * 24 * 60 * 60),
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
];
setcookie('sessionToken', $token, $cookieOptions);
$_SESSION['profileTags'] = $profileTags;
$_SESSION['corsmsg'] = 'Login successful';
redirectTo('../' . $previousLocation);

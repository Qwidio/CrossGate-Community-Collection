<?php
require_once 'database.php';
function redirectTo(string $location): never
{
    header('Location: ' . $location);
    exit;
}
if (!isset($_POST['Register'])) {
    exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$email = trim((string)($_POST['email'] ?? ''));
if ($username === '' || $password === '' || $email === '') {
    $_SESSION['corsmsg'] = 'Missing credentials';
    redirectTo('../connect_it/connect_it.php?state=register');
}
if (mb_strlen($username) > 255) {
    $_SESSION['corsmsg'] = 'Username is too long';
    redirectTo('../connect_it/connect_it.php?state=register');
}
if (!preg_match('/^[A-Za-z0-9_.-]+$/', $username)) {
    $_SESSION['corsmsg'] = 'Username contains invalid characters';
    redirectTo('../connect_it/connect_it.php?state=register');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['corsmsg'] = 'Invalid email address';
    redirectTo('../connect_it/connect_it.php?state=register');
}
if (mb_strlen($email) > 255) {
    $_SESSION['corsmsg'] = 'Email address is too long';
    redirectTo('../connect_it/connect_it.php?state=register');
}
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if ($passwordHash === false) {
    $_SESSION['corsmsg'] = 'Unable to create account';
    redirectTo('../connect_it/connect_it.php?state=register');
}

$profileTags = $username . bin2hex(random_bytes(16));
$profileName = $username;
$joinDate = date('d/m/Y');
$profileData = [
    'marked'  => 'empty',
    'private' => false,
    'favbadge'=> 'none',
    'themes'  => '0',
    "borders" => '0'
];
$encodedMkot = json_encode(
    $profileData,
    JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
);
$activationToken = bin2hex(random_bytes(32));
$activationTokenHash = hash('sha256', $activationToken);
$activationExpires = date('Y-m-d H:i:s', time() + 3,600); // 1 hours

try {
    $connects->begin_transaction();
    $stmt = $connects->prepare(
        'INSERT INTO user
            (profileTags, username, password, Email, userState, activation_token_hash, activation_expires)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        throw new RuntimeException($connects->error);
    }

    $userState = 'pending';
    $stmt->bind_param(
        'sssssss',
        $profileTags,
        $username,
        $passwordHash,
        $email,
        $userState,
        $activationTokenHash,
        $activationExpires
    );
    if (!$stmt->execute()) {
        // Duplicate username/email errors are handled without exposing SQL details
        if ($connects->errno === 1062) {
            throw new InvalidArgumentException(
                'Username or email address is already registered'
            );
        }
        throw new RuntimeException($stmt->error);
    }

    $stmt->close();
    $profileBio = '';
    $profileStmt = $connects->prepare(
        'INSERT INTO profiles
            (profileTags, profileNames, profileBios, profileJDates, mkot)
         VALUES (?, ?, ?, ?, ?)'
    );
    if (!$profileStmt) {
        throw new RuntimeException($connects->error);
    }
    $profileStmt->bind_param(
        'sssss',
        $profileTags,
        $profileName,
        $profileBio,
        $joinDate,
        $encodedMkot
    );
    if (!$profileStmt->execute()) {
        throw new RuntimeException($profileStmt->error);
    }
    $profileStmt->close();
    $connects->commit();
    
    $safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $safeActivationUrl = htmlspecialchars($activationUrl, ENT_QUOTES, 'UTF-8');
    $activationUrl =
        'https://cgcc.porosive.com/activation.php?id=' .
        urlencode((string)$userId) .
        '&token=' .
        urlencode($activationToken);

    $subject = 'Confirm your account';
    $htmlMessage = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Confirm your account</title>
    </head>
    <body style="margin:0; padding:0; background:#f2f2f2; font-family:Arial,sans-serif;">
        <div style="max-width:600px; margin:40px auto; background:#ffffff; padding:30px; border-radius:8px; color:#333;">
            <h2 style="color:#222;">Welcome, {$safeUsername}!</h2>
            <p>Thank you for registering with CGCC. Please click the button below to activate your account.</p>
            <p style="text-align:center; margin:30px 0;">
                <a href="{$safeActivationUrl}" style="background:#2563eb; color:#ffffff; padding:14px 24px; text-decoration:none; border-radius:5px; display:inline-block;">
                    Activate account
                </a>
            </p>
            <p>This link will expire within 1 hours.</p>
            <p>If you did not create this account, you can ignore this email.</p>
            <p style="font-size:13px; color:#777;">
                If the button does not work, copy and paste this link into your browser:
            </p>

            <p style="font-size:13px; word-break:break-all;">
                {$safeActivationUrl}
            </p>
        </div>
    </body>
    </html>
    HTML;
    $plainMessage =
        "Hi {$username},\n\n" .
        "hank you for registering with CGCC. Please confirm your account creation by clicking this link:\n\n" .
        $activationUrl . "\n\n" .
        "This link will expire within 1 hours.\n\n" .
        "If you did not create this account, you can ignore this email.";
    $headers = [
        'From: no-reply@cgcc.porosive.com',
        'Reply-To: no-reply@cgcc.porosive.com.com',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8'
    ];

    mail(
        $email,
        $subject,
        $htmlMessage,
        implode("\r\n", $headers)
    );

    $_SESSION['corsmsg'] = 'Your account was created. Please check your email to activate it.';
    redirectTo('../connect_it/connect_it.php?state=login');
} catch (InvalidArgumentException $e) {
    $connects->rollback();
    $_SESSION['corsmsg'] = $e->getMessage();
    redirectTo('../connect_it/connect_it.php?state=register');
} catch (Throwable $e) {
    $connects->rollback();
    error_log('Registration error: ' . $e->getMessage());
    $_SESSION['corsmsg'] = 'Registration failed';
    redirectTo('../connect_it/connect_it.php?state=register');
}

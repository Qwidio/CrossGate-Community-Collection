<?php
require_once 'database.php';
$ids = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
$token = (string)($_GET['token'] ?? '')
if (!$ids || $token === '') {
  $_SESSION['corsmsg'] = 'Invalid activation link';
  header ('location: index.php');
  exit;
}
$tokenHash = hash('sha256', $token);
$stmt = $connects->prepare('SELECT UserID, userState, activation_expires FROM `user` WHERE UserID = ? AND activation_token_hash = ? LIMIT 1');
$stmt->bind_param('is', $id, $tokenHash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
if (!$user) {
  $_SESSION['corsmsg'] = 'account is already activated';
  header ('location: index.php');
  exit;
}
if (empty($user['activation_expires']) || strtotime($user['activation_expires']) < time()) {
  $_SESSION['corsmsg'] = 'Activation link expired';
  header ('location: index.php');
  exit;
}
$update = $connects->prepare('SELECT UserID, userState, activation_expires FROM `user` WHERE UserID = ? AND activation_token_hash = ? LIMIT 1');
$update->bind_param('is', $id, $tokenHash);
$update->execute();
$update->close();
$_SESSION['corsmsg'] = 'Activation succeeded';
redirectTo('../connect_it/connect_it.php?state=login');
?>
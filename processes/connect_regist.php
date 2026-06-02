<?php
require_once 'database.php';
$errors = '';
if (empty($_SESSION['prev_loc'])) {
    $prev_loc = $_SESSION['prev_loc'];
} else {
    $prev_loc = "index.php";
};

if (isset($_POST['Register'])) {
    function getRandomWord($len = 10) {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }
    $username = $_POST['username'];
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];
    $Email = $_POST['email'];
    if (empty($username) || empty($password) || empty($Email)) {
        $_SESSION['corsmsg'] = "missing credentials";
        header('location: ../Groups/index.php');
        exit;
    }
    $stmt_check = $connects->prepare("SELECT username FROM user WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        $rnum = random_int(1000, 9897);
        $rword = getRandomWord();
        $profileTags = $username . "_" . $rword . "_" . $rnum;
        $stmt_insert = $connects->prepare("INSERT INTO user (profileTags, username, password, Email) VALUES (?, ?, MD5(?), ?)");
        $stmt_insert->bind_param("ssss", $profileTags, $username, $password, $Email);
        if ($stmt_insert->execute()) {
            $date = date('d/m/Y h:i');
            $usrDatTemp = [
                "lastLogin"   => "$date",
                "marked"      => "empty",
                "private"     => false
            ];
            $encodedMkot = json_encode($usrDatTemp, JSON_UNESCAPED_SLASHES);
            $stmt_profile_insert = $connects->prepare("INSERT INTO profiles (profileTags, profileNames, profileJDates, mkot) VALUES (?, ?, ?, ?)");
            $stmt_profile_insert->bind_param("ssss", $profileTags, $username, date('d/m/Y'), $encodedMkot);
            if ($stmt_profile_insert->execute()) {
                $_SESSION['corsmsg'] = 'Your account have been Registered';
                header('location: ../connect_it/connect_it.php?state=login');
                exit;
            } else {
                $errors = "Registration failed: " . $stmt_profile_insert->error;
                $_SESSION['corsmsg'] = $errors;
                header('location: ../connect_it/connect_it.php?state=register');
                exit;
            }
            $stmt_profile_insert->close();
        } else {
            $errors = "Registration failed: " . $stmt_insert->error;
            $_SESSION['corsmsg'] = $errors;
            header('location: ../connect_it/connect_it.php?state=register');
            exit;
        }
        $stmt_insert->close();
    } else {
        $errors = "Username taken, choose another" . $stmt_insert->error;
        $_SESSION['corsmsg'] = $errors;
        header('location: ../connect_it/connect_it.php?state=register');
        exit;
    }
    $stmt_check->close();
}

?>
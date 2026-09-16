<?php
require_once "../processes/database.php";
if (!isset($_GET['state'])) {
    header ('location: ../index.php');
    exit;
}
if (!isset($_SESSION['profileTags'])) {
    header ('location: ../index.php');
    exit;
};

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
$previousLocation = getSafePreviousLocation();
$state = $_GET['state'];
$errors = array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/cgcclogotrsp.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styling/pallate.css">
    <link rel="stylesheet" href="../styling/Mindex.css">
<?php
if ($state === 'reset') {
?>
  <title>Reset password / CGCC</title>
</head>
<body class="h100 row bg-def-1">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity1 z1">
    <form class="posr rightMg pad-b-s pad-n-v minw200 w40 h100p flex fld acjc gap10 bgc-gray border-custom-l border-custom-r z5" action="../processes/connect_reset.php" method="post">
        <h1 class="sideMg txtc txt-l bold">Reset Account Password</h1>
        <div class="form-input-row sideMg w88p flex fld">
          <label for="currentpassword">Current Password</label>
          <input class="inptxt" type="currentpassword" id="currentpassword" name="currentpassword" minlength="8" placeholder="The current password" autocomplete="off" tabindex="1" required>
        </div>
        <div class="form-input-row sideMg w88p flex fld">
          <label for="newpassword">New Password</label>
          <input class="inptxt" type="newpassword" id="newpassword" name="newpassword" minlength="8" placeholder="Input the new password" autocomplete="off" tabindex="2" required>
          <input class="inptxt" type="confirmpassword" id="confirmpassword" name="confirmpassword" minlength="8" placeholder="Confirm the new password" autocomplete="off" tabindex="2" required>
        </div>
          <div class="form-input-row sideMg w88p flex fld">
          <button type="submit" class="pad-s bgc-gold txt-n c-black" name="ResetPass" tabindex="3">Reset</button>
        </div>
        <div class="topMg-s5 sideMg w88p flex fld">
          <p class="txtc">Misclicked? <a href="../<?php echo $previousLocation;?>" class="c-orange hover-text-white" tabindex="7">back to settings</a></p>
          <p class="pad-m txtc">Having problem with your account?</br><a href="../documentation/docs.php#account" class="c-orange hover-text-white" tabindex="7">check docs</a> or <a href="#" class="c-orange hover-text-white" tabindex="7">dm me</a></p>
        </div>
    </form>
    <p class="posr w60 h100p blurbg z5"></p>
<?php
}
?>
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script src="../scriptstuff/alert.js"></script>
    <?php
    if (!empty($_SESSION['corsmsg'])) {
        $corsmsg = $_SESSION['corsmsg'];
        echo "<script> ";
        echo "alerter('" . $corsmsg . "')";
        echo "</script>";
        $_SESSION['corsmsg'] = "";
    }
    ?>
</body>
</html>
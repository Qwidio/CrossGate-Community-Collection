<?php
require_once 'processes/database.php';
$errors = array();
session_start();
$setBios = false;
if (!isset($_GET['user'])) {
    $_SESSION['corsmsg'] = "no user tags found";
    header ('location: index.php');
    exit;
}
$uDs = $_GET['user'];
$_SESSION['prev_loc'] = "profile.php?user=" . $uDs;
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
}
if (isset($_SESSION['profileTags']) && $uDs === "self") {
    $setBios = true;
    $uDs = $_SESSION['profileTags'];
    $uDs = htmlspecialchars($uDs, ENT_QUOTES, 'UTF-8');
    $sources = "drx/$aidis.json";
    $jsonData = file_get_contents($sources);
    $data = json_decode($jsonData, true);
    $profileTag = $data['profileTags'];
    $markedData = $data['marked'];
    $marked = [];
    foreach ($markedData as $markedIndex => $info) {
        $marked[$markedIndex] = [
            "libsIds"  => $info['libsIds'],
            "Hours"    => (int)$info['Hours'],
            "lastLog"  => $info['lastLog'],
        ];
    }
    $usrDatTemp[] = [
        "profileTags" => $profileTag,
        "marked"      => $marked
    ];
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="styling/pallate.css">
    <link rel="stylesheet" href="styling/Mindex.css">
    <link rel="stylesheet" href="styling/footer.css">
    <title>Profiles</title>
</head>
<body>
    <!-- nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <a href="index.php" class="vertiMg pad-s txt-l semibold">CROSSGATE</a>
        <div class="posr w60p flex gap-s">
            <?php
            if (isset($aidis)) {
                ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="Library/core/markout.php" class="link-cover">.</a>
            </div>
            <?php
            }
            ?>
            <div class="posr pad-s flex fld acjc">   
                <h2 class="txt-n txtc semibold">CATEGORY</h2>
                <a href="Library/core/category.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="TS/forum/dashboard.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="documentation/docs.php" class="link-cover">.</a>
            </div>
        </div>
        <?php
        if (!isset($aidis)) {
        ?>
        <div class="leftMg flex acjc gap10">
            <p class="posr pad-n-s pad-s-v txtc txt-n bg-1 border-1 bora-s border-hover-white">LOGIN
                <a href="forum-connect/connect_it.php?state=login" class="link-cover">.</a>
            </p>
        </div>
        <?php
        };
        ?>
    </div>
<!-- log-out confirm -->
    <div id="confirmElems" class="posf pad-n c0 pad-b-v minw20 maxh50 dp-none fld bg-1 border1 bora-s z999">
        <h2 class="w100p txt-b txtc">Want to Log-Out?</h2>
        <div class="topMg-s10 sideMg flex acjc gap-s">
            <button class="pad-n-s pad-s-v txt-n txtc bg-red border-1 border-hover-white" onclick="linker('logout')">YES</button>
            <button class="pad-n-s pad-s-v txt-n txtc c-black border-1 border-hover-white" onclick="confirmElement(false)">NO</button>
        </div>
    </div>
<!-- edit bio form -->
    <dialog id="edit-dialog" class="posf c0 w50 minh70 maxh70 flex fld acjc bg-white border-1 bora-s ovh z999">
        <div class="posa lt0 bg-blue w100p flex"><h2 class="rightMg pad-s txt-b">Edit Bio</h2><p class="pad-s-v pad-n-s txt-b red-hover" onclick="SetDialog('edit')">X</p></div>
        <form class="w100p h70 flex fld gap5" name="BIOS" action="TS/component/bionic.php" method="post">
            <div class="topMg-10 sideMg w95p flex fld">
                <textarea type="text" name="bioedits" class="pad-m w100p h50 c-black bora-s ovh" placeholder="" auto-complete="off" maxlength="2500" required></textarea>
            </div>
            <div class="vertiMg w100p flex fld">
                <input class="sideMg pad-m-v pad-n-s txt-n c-white bg-blue bora-n" type="submit" name="submit" value="change bio">
            </div>
        </form>
    </dialog>
<!-- the profile content and other stuff -->
    <div class="posr vertiMg w88p h40 flex bg-prf-default z2">
        <?php
        $check_profile = $connects->prepare("SELECT * FROM profiles WHERE profileTags = ? ;");
        $check_profile->bind_param("s", $uDs);
        $check_profile->execute();
        $result_check_profile = $check_profile->get_result();
        if ($result_check_profile->num_rows == 1) {
            $value = $result_check_profile->fetch_assoc();
            $Tags = $value['profileTags'];
            $pfAttachs = $value['profileAttachs'];
            $Names = $value['profileNames'];
            $Bios = $value['profileBios'];
            $JDates = $value['profileJDates'];
            $specialBadge = $value['specialBadge'];
            $oState = $value['oState'];
            $iconAlt = ucfirst(substr($Names, 0, 1));
        ?>
        <div class="posr vertiMg r1-1 w20p flex z3">
            <?php
            if (empty($pfAttachs) || $pfAttachs === "empty") {
            ?>
            <img src="img/person.svg" class="autoMg r1-1 h80p flex acjc bgc-blue objfit bora-s z4">
            <?php
            } else {
            ?>
            <img src="zprpic/<?php echo $pfAttachs;?>" alt="<?php echo $Names;?>" class="autoMg r1-1 h80p flex acjc bgc-blue objfit bora-s z4">
            <?php
            };
            ?>
        </div>
        <div class="posr pad-n-v pad-sr w50p h100p flex fld gap5 z4">
            <h2 class="topMg w100p txt-l"><?php echo $Names;?></h2>
            <div class="w100p flex">
                <p class="rightMg txt-s">Joined since <?php echo $JDates;?></p>
            </div>
            <?php
            if ($setBios == true) {
            ?>
            <div class="pad-s-v w100p minh20 maxh20 txt-s border-t border-b ovh-s"><?php echo $Bios;?></div>
            <button class="bottomMg w20p pad-m-v pad-n-s txt-s c-white bg-blue bora-s" onclick="SetDialog('edit'); LoadBios(this);" data-bioedits="<?php echo $Bios;?>">Edit Bio</button>
            <?php
            } else {
            ?>
            <div class="bottomMg pad-s-v w100p minh20 maxh20 txt-s border-t border-b ovh-s"><?php echo $Bios;?></div>
            <?php    
            }
            ?>
        </div>
        <?php
        ?>
        <div class="posr pad-n-v w30p h100p flex fld acjc gap5 z4">
            <div class="posr sideMg w95p r4-1 flex acjc z4">
                <?php
                $check_badges = $connects->prepare("SELECT badgeIds, badgeName, badgeDesc, icon FROM specialbadge WHERE badgeIds = ? ;");
                $check_badges->bind_param("s", $specialBadge);
                $check_badges->execute();
                $result_check_badges = $check_badges->get_result();
                if ($result_check_badges->num_rows > 0) {
                    while ($value = $result_check_badges->fetch_assoc()) {
                        $badgesIds = $value['badgeIds'];
                        $badgeName = $value['badgeName'];
                        $badgeIcon = $value['icon'];
                ?>
                <div class="posr pad-m icon-s border-1 bora-s">
                    <img src="img/<?php echo $badgeIcon;?>" alt="<?php echo $badgeIcon;?>" class="wh100p objfit">
                    <a href="#" class="link-cover">.</a>
                </div>
            </div>
                <?php
                    };
                };
                if ($setBios == true) {
                ?>
            <div class="posr sideMg w95p flex acjc bg-green">
                <p class="w100p pad-n-s pad-s-v border-1">Session Manager</p>
                <a href="session.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr sideMg w95p flex acjc bg-red">
                <p class="w100p pad-n-s pad-s-v border-1">LOG-OUT</p>
                <p onclick="confirmElement(true)" class="link-cover hover-white">.</p>
            </div>
                <?php
                    };
                ?>
        </div>
    </div>
    <div class="posr bottomMg-10 pad-s w88p flex gap-s bg-prf-default z2">
        <div class="posr sideMg pad-n-v pad-s-s w70p flex fld z3">
            <div class="bg-blue w100p flex"><h2 class="rightMg pad-s txt-b">Achievement Showcase</h2></div>
        </div>
        <div class="posr sideMg pad-n-v pad-s-s w30p flex fld blurbg z3">
            <div class="bottomMg-s10 w100p flex">
                <h2 class="rightMg pad-s txt-b">Currently <?php echo $oState;?></h2>
            </div>
            <?php
            if (isset($aidis)) {
                $prebind = '"' . $aidis . '"';
            } else {
                $prebind = '"' . $uDs . '"';
            }
            $check_orgs = $connects->prepare("SELECT identification, names, JSON_LENGTH(members) AS member_count, logo FROM ogroup WHERE JSON_CONTAINS(members, ?);");
            $check_orgs->bind_param("s", $prebind);
            $check_orgs->execute();
            $result_check_orgs = $check_orgs->get_result();
            if ($result_check_orgs->num_rows > 0) {
            ?>
            <div class="w100p flex z4">
                <h2 class="rightMg pad-s-s pad-m-v txt-n z5">Part of</h2>
            </div>
            <?php
                $uniqueItem = [];
                while ($value = $result_check_orgs->fetch_assoc()) {
                    $OgIdentific = $value['identification'];
                    $OgName = $value['names'];
                    $member_count = $value['member_count'];
                    $logo = $value['logo'];
                    if (!in_array($OgIdentific, $uniqueItem)) {
            ?>
            <div class="posr bottomMg-s5 pad-m-v pad-s-s w100p flex z4">
                <?php
                if (empty($logo) || $logo === "empty") {
                ?>
                <img src="img/business-outline.svg" class="r1-1 w20p flex acjc border-1 objfit z4">
                <?php
                } else {
                ?>
                <img src="Groups/img/<?php echo $logo;?>" alt="<?php echo $OgName;?>" class="autoMg r1-1 h80p flex acjc bg-blur objfit border-1 bora-s z4">
                <?php
                };
                ?>
                <div class="posr w80p flex fld">
                    <h2 class="topMg rightMg pad-s-s txt-s"><?php echo $OgName;?></h2>
                    <h2 class="bottomMg rightMg pad-s-s txt-s c-gray"><?php echo $member_count;?> Members</h2>
                </div>
                <a href="Groups/profile.php?gids=<?php echo $OgIdentific;?>" class="link-cover hover-white">.</a>
            </div>
            <?php
                    };
                };
            };
            ?>
        </div>
    </div>
        <?php
        } else {
            $_SESSION['corsmsg'] = "user account does not exists or on a temporary bans";
            header ('location: dashboard.php');
            exit;
        };
        ?>
    <!-- lil bit of messages passer -->
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <?php include_once 'footer.php';?>
    <script src="scriptstuff/script.js"></script>
    <script src="scriptstuff/alert.js"></script>
    <?php
    if (!empty($errors)) {
        echo "<script> ";
        echo "alerter('"; foreach ($errors as $error) {echo $error .";";} echo "')";
        echo "</script>";
    }
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
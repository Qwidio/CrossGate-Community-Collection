<?php
require_once 'processes/database.php';
$allowEdits = false;
$ownedBadges = false;
if (!isset($_GET['user'])) {
    $_SESSION['corsmsg'] = "no user tags found";
    header ('location: index.php');
    exit;
}
$uDs = $_GET['user'];
$_SESSION['prev_loc'] = "profile.php?user=" . $uDs;
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    $root_route = "";
    require_once 'secureSession.php';
};
if (isset($aidis) && $uDs === "self") {
    $allowEdits = true;
    $uDs = $_SESSION['profileTags'];
};
$check_user = $connects->prepare("SELECT userState FROM user WHERE profileTags = ? ;");
$check_user->bind_param("s", $uDs);
$check_user->execute();
$result_check_user = $check_user->get_result();
if ($result_check_user->num_rows == 1) {
    $tempValue = $result_check_user->fetch_assoc();
    $userState = $tempValue["userState"];
    if ($userState != "approved") {
        $_SESSION['corsmsg'] = "user account currently banned";
        header ('location: index.php');
        exit;
    }
} else {
    $_SESSION['corsmsg'] = "user account does not exist";
    header ('location: index.php');
    exit;
};
$favbadge = "none";
$check_profile = $connects->prepare("SELECT profiles.*, user.userState 
FROM profiles INNER JOIN user on profiles.profileTags = user.profileTags
WHERE user.userState = 'approved' AND profiles.profileTags = ? ;");
$check_profile->bind_param("s", $uDs);
$check_profile->execute();
$result_check_profile = $check_profile->get_result();
if ($result_check_profile->num_rows == 1) {
    $badges = array();
    $tempGroupRefs = array();
    $badgeGroups = array();
    $themes = array();

    $value = $result_check_profile->fetch_assoc();
    $Tags = $value['profileTags'];
    $pfAttachs = $value['profileAttachs'];
    $Names = $value['profileNames'];
    $Bios = $value['profileBios'];
    $JDates = $value['profileJDates'];
    $allowInvite = $value['allowInvite'];
    $oState = $value['oState'];

    $mkot = $value['mkot'];
    $badgeArr = $value['Badge'];
    $badgeArr = json_decode($badgeArr, true);
    $data = json_decode($mkot, true);
    $markedData = $data['marked'];
    $privated = $data['private'];
    $savedfavbadge = $data['favbadge'];
    $themes = $data['themes'];
    if ($themes == 2) {
        $themes = [
            "bg" => "bg-prf-2",
            "accent" => "bgc-blue",
            "color" => "c-white"
        ];
    } else if ($themes == 3) {
        $themes = [
            "bg" => "bg-prf-3",
            "accent" => "bgc-blue",
            "color" => "c-white"
        ];
    } else if ($themes == 4) {
        $themes = [
            "bg" => "bg-prf-4",
            "accent" => "bgc-gold",
            "color" => "c-black"
        ];
    } else {
        $themes = [
            "bg" => "bg-prf-1",
            "accent" => "bgc-purple",
            "color" => "c-white"
        ];
    }
    if (!empty($markedData) && $markedData != "empty") {
        $marked = [];
        foreach ($markedData as $markedIndex => $info) {
            $marked[$markedIndex] = [
                "libsIds"  => $info['libsIds'],
                "Hours"    => (int)$info['Hours'],
                "lastLog"  => $info['lastLog']
            ];
        }
    } else {
        $no_mkot = true;
    }
    $usrDatTemp[] = [
        "private"   => $privated
    ];
    foreach ($badgeArr as $badgeIndex => $badgeValue) {
        $check_badges = $connects->prepare("SELECT badges.badgeName, badges.badgeDesc, badges.badgeType, badges.badgeRefs, badges.icon, badgegroup.state FROM badges
        INNER JOIN badgegroup ON badges.badgeRefs = badgegroup.groupRefs WHERE badgeIds = ? ;");
        $check_badges->bind_param("s", $badgeIndex);
        $check_badges->execute();
        $result_check_badges = $check_badges->get_result();
        if ($result_check_badges->num_rows > 0) {
            $value = $result_check_badges->fetch_assoc();
            if ($value['state'] === "publics") {
                if ($badgeIndex === $savedfavbadge || $badgeIndex == $savedfavbadge ) {
                    $favbadge = $savedfavbadge;
                }
                $badges[$badgeIndex] = [
                    "badgesIds" => $badgeIndex,
                    "badgeName" => $value['badgeName'],
                    "badgeDesc" => $value['badgeDesc'],
                    "badgeType" => $value['badgeType'],
                    "badgeRefs" => $value['badgeRefs'],
                    "badgeIcon" => $value['icon'],
                    "badgeDate" => $badgeValue
                ];
                $ownedBadges = true;
            }
        }
    }
} else {
    $_SESSION['corsmsg'] = "user profile cannot be accessed";
    header ('location: index.php');
    exit;
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="img/cgcclogotrsp.ico" type="image/x-icon">
    <link rel="stylesheet" href="styling/pallate.css">
    <link rel="stylesheet" href="styling/Mindex.css">
    <link rel="stylesheet" href="styling/footer.css">
    <title>Profiles</title>
</head>
<body>
    <img src="img/contour3bw.png" alt="" class="posa lt0 w100p h100 coverfit filInvert opacity05 z-1">
    <div class="posr pad-n-s w100p maxw100 minh10 flex gap-s bg-4 blurbg z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="index.php" class="link-cover">.</a>
        </div>
        <div class="posr w60p flex gap-s">
            <div class="posr pad-s flex fld acjc">   
                <h2 class="txt-n txtc semibold">BROWSE</h2>
                <a href="Library/core/list.php" class="link-cover">.</a>
            </div>
            <?php
            if (isset($aidis)) {
                ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="Library/core/markout.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="profile.php?user=self" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">GROUPS</h2>
                <a href="Groups/index.php" class="link-cover">.</a>
            </div>
            <?php
            }
            ?>
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
        <p class="posr leftMg vertiMg pad-n-s pad-s-v txtc txt-n bg-half-purple blurbg border-1 bora-s border-hover-white">LOGIN
            <a href="connect_it/connect_it.php?state=login" class="link-cover">.</a>
        </p>
        <?php
        } else if (isset($aidis) && $_GET['user'] === "self") {;
        ?>
            <a class="posr leftMg vertiMg pad-n-s pad-s-v txtc txt-n c-white bg-1 blurbg border-1 bora-s border-hover-orange hover-text-orange points" href="settings.php">Settings</a>
        <?php
        }
        ?>
    </div>
<!-- log-out confirm -->
    <div id="confirmElems" class="posf pad-n c0 pad-b-v minw20 maxh50 dp-none fld bg-1 border1 bora-s z999">
        <h2 class="w100p txt-b txtc">Want to Log-Out?</h2>
        <div class="topMg-s10 sideMg flex acjc gap-s">
            <button class="pad-n-s pad-s-v txt-n txtc bg-red border-1 border-hover-white" onclick="linker('processes/logout.php')">YES</button>
            <button class="pad-n-s pad-s-v txt-n txtc c-black border-1 border-hover-white" onclick="uniDisplaySwitch('confirmElems')">NO</button>
        </div>
    </div>
<!-- Remove Post -->
    <dialog id="postRemoveDiag" class="posf pad-n c0 pad-b-v minw20 maxh50 dp-none fld bg-2 border-1 bora-s z999">
        <form id="removepost" class="wh100p flex fld" name="REMOVE" action="processes/delete_post.php" method="post">
            <h2 class="w100p txt-n txtc">Confirm to Remove this Post?</h2>
            <input class="pad-n-v bg-transparent maxh10 txtc txt-n c-white border-none ovh" type="text" name="postname" readonly>
            <input class="hiddeninp" type="text" name="foids" hidden>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-red border-1 border-hover-white" type="submit" name="submit" value="REMOVE">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-green" onclick="uniDisplaySwitch('postRemoveDiag')">Cancel</button>
    </dialog>
    <?php include_once 'reportTab.php';
    if ($allowEdits == false) {
    ?>
    <div class="posf pad-n r1-1 b0 r0 flex z999">
        <img src="img/warning.svg" alt="" class="posr icon-t containfit bg-half-white opacity3 hover-visible points" onclick="uniDisplaySwitch('reportDialog'); uniLoad(this, 'reportForm');" data-reportsource="user" data-ids="<?php echo $uDs;?>">
    </div>
    <?php
    }
    ?>
<!-- the profile content and other stuff -->
<div class="posr topMg-s10 bottomMg-s10 w70p minh100 flex fld <?php echo $themes["bg"];?> blurbg bora-s">
    <div class="posr w100p h40 flex z2">
        <div class="posr leftMg vertiMg r1-1 w20p flex z3">
        <?php
        if (empty($pfAttachs) || $pfAttachs === "empty") {
        ?>
            <img src="img/person.svg" class="autoMg r1-1 minh10 h80p flex acjc bg-half-white coverfit bora-s z4">
        <?php
        } else {
        ?>
            <img src="zprpic/<?php echo $Tags . "/" . $pfAttachs;?>" alt="<?php echo $Names;?>" class="autoMg r1-1 minh10 h80p flex acjc bgc-purple coverfit z4">
        <?php
        };
        ?>
        </div>
        <div class="posr vertiMg pad-n-v pad-sr w50p h80p flex fld z4">
            <h2 class="posr topMg w100p txt-l"><?php echo $Names;?></h2>
            <div class="posr w100p flex">
                <p class="posr rightMg txt-s">Joined since <?php echo $JDates;?></p>
            </div>
            <div class="posr topMg-s5 pad-s-v wh100p minh20 maxh20 txt-s ovh-s"><?php echo $Bios;?></div>
        </div>
        <div class="posr leftMg pad-n-v w30p h100p flex fld acjc gap5 z4">
<?php
if ($ownedBadges == true && $privated == false && $favbadge != "none" || $allowEdits == true && $ownedBadges == true && $favbadge != "none" ) {
        $badgeVals = $badges[$favbadge];
        $badgeName = $badgeVals['badgeName'];
        $badgeDesc = $badgeVals['badgeDesc'];
        $badgeType = $badgeVals['badgeType'];
        $badgeIcon = $badgeVals['badgeIcon'];
        $badgeRefs = $badgeVals['badgeRefs'];
        $badgeDate = $badgeVals['badgeDate'];
?>
        <div class="posr sideMg w95p pad-s-v pad-n-s flex gap10 bg-def-2 box-shad-black-1 border-purple bora-s hover-border-white ovh-s z4">
            <div class="posr vertiMg h10 r1-1 flex">
                <img src="ab/<?php echo $badgeRefs . "/" . $badgeIcon;?>" alt="<?php echo $badgeIcon;?>" class="posr autoMg h80p r1-1 containfit bora-s z4">
            </div>
            <div class="vertiMg flex fld">
                <h2 class="posr txt-n"><?php echo $badgeName;?></h2>
                <p class="posr txt-ms c-lightgray hover-text-white"><?php echo $badgeDesc;?></p>
                <p class="posr txt-ms c-lightgray hover-text-white">obtained <?php echo $badgeDate;?></p>
            </div>
            <a href="acvb.php?type=user&filter=one&ref=<?php echo $uDs;?>&target=<?php echo $favbadge;?>" class="link-cover">.</a>
        </div>
<?php
} else if ($allowEdits == true && $ownedBadges == true && $favbadge === "none") {
?>
            <div class="posr sideMg w95p pad-s-v pad-n-s flex gap10 bg-def-2 box-shad-black-1 border-purple bora-s hover-border-white ovh-s z4">
                <div class="vertiMg flex fld">
                    <h2 class="posr txt-n">You can feature one of your favorite Badges here. Select one from your settings page.</h2>
                </div>
                <a href="settings.php" class="link-cover">.</a>
            </div>
<?php
};
if ($allowEdits == true) {
?>
            <div class="posr topMg-s10 sideMg w95p flex acjc bg-green">
                <p class="w100p pad-n-s pad-s-v border-1">Session Manager</p>
                <a href="session.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr sideMg w95p flex acjc bg-red">
                <p class="w100p pad-n-s pad-s-v border-1">LOG-OUT</p>
                <p onclick="uniDisplaySwitch('confirmElems')" class="link-cover hover-white">.</p>
            </div>
<?php
};
?>
        </div>
    </div>
    <div class="posr pad-s w100p minh40 flex gap-s z2">
        <div class="posr sideMg pad-n-v pad-s-s w70p flex fld z3">
<?php
if ($ownedBadges == true && $privated == false || isset($aidis) && $_GET['user'] === "self" && $ownedBadges == true) {
?>
            <div class="posr <?php echo $themes["accent"];?> w100p flex"><h2 class="rightMg pad-s txt-b <?php echo $themes["color"];?> z3">Badges Showcase</h2></div>
            <div class="posr pad-n-v pad-s-s w100p flex wrap gap10 <?php echo $themes["bg"];?> z3">
<?php
    foreach ($badges as $tempBadge => $value) {
        $badgesIds = $tempBadge;
        $badgeName = $value['badgeName'];
        $badgeType = $value['badgeType'];
        $badgeIcon = $value['badgeIcon'];
        $badgeRefs = $value['badgeRefs'];
        ?>
                <div class="posr icon-s z4">
                    <img src="ab/<?php echo $badgeRefs . "/" . $badgeIcon;?>" alt="" class="wh100p containfit">
                    <a href="acvb.php?type=user&filter=one&ref=<?php echo $uDs;?>&target=<?php echo $badgesIds;?>" class="link-cover hover-white">.</a>
                </div>
<?php
    };
?>
            </div>
<?php
} else {
?>
            <div class="posr w100p hiddeninp">.</div>
<?php
};
$stmt_check_userpost = $connects->prepare("SELECT * FROM forums WHERE ForumCreator = ? AND ForumState = 'Publics' ORDER BY ForumDates DESC LIMIT 10;");
$stmt_check_userpost->bind_param("s", $uDs);
$stmt_check_userpost->execute();
$result_check_userpost = $stmt_check_userpost->get_result();
if ($result_check_userpost->num_rows > 0) {
?>
            <div class="<?php echo $themes["accent"];?> w100p flex"><h2 class="rightMg pad-s txt-b <?php echo $themes["color"];?>">Posted Forum</h2></div>
            <div class="bottomMg pad-n-v pad-s-s w100p w95p minh20 flex wrap <?php echo $themes["bg"];?> z1">
<?php
    while ($value = $result_check_userpost->fetch_assoc()) {
        $ids= $value['ForumIds'];
        $creators = $value['ForumCreator'];
        $titles = $value['ForumTitles'];
        $dates = $value['ForumDates'];
        $contents = $value['ForumContents'];
?>
             <div class="posr pad-s minw15 w50p r16-9 flex fld bg-half-gray border-1 gap5">
                <h2 class="txt-n z3"><?php echo $titles;?></h2>
                <div class="bottomMg-s5 w100p flex space-between z3">
                    <p class="txt-s z3"><?php echo $creators;?></p>
                    <p class="txt-s z3"><?php echo $dates;?></p>
                </div>
                <p class="maxh10 txt-s ovh z3"><?php echo $contents;?></p>
<?php
        if (isset($aidis) && $creators === $aidis && $_GET['user'] === "self") {
?>
                <div class="topMg w100p flex z3">
                    <a href="TS/forum/forum.php?ids=<?php echo $ids;?>" class="posr pad-m-v pad-s-s w50p txtc bgc-white points hover-white trs500ms" target="_blank" rel="noopener noreferrer">
                        <img src="img/open-outline.svg" alt="" class="posr autoMg h10px r1-1 containfit">
                    </a>
                    <div class="posr pad-m-v pad-s-s w50p txtc bgc-red points hover-white trs500ms" onclick="uniDisplaySwitch('postRemoveDiag'); uniLoad(this, 'removepost');" data-foids="<?php echo $ids;?>" data-postname="<?php echo $titles;?>">
                        <img src="img/trash-outline.svg" alt="" class="posr autoMg h10px r1-1 containfit">
                    </div>
                </div>
<?php
        } else {
?>
                    <a href="TS/forum/forum.php?ids=<?php echo $ids;?>" class="posr link-cover hover-white z4">.</a>
<?php
        }
?>
             </div>
<?php
    };
?>
            </div>
<?php
};
?>
        </div>
        <div class="posr sideMg pad-n-v pad-s-s w30p flex fld blurbg z3">
            <div class="bottomMg-s10 w100p flex">
                <h2 class="rightMg pad-s txt-b">Currently <?php echo $oState;?></h2>
            </div>
<?php
if (isset($aidis) && $uDs === "self") {
    $prebind = '"' . $aidis . '"';
} else {
    $prebind = '"' . $uDs . '"';
}
$check_Groups = $connects->prepare("SELECT identification, names, JSON_LENGTH(members) AS member_count, logo FROM ogroup WHERE founder = ? OR JSON_CONTAINS(members, ?);");
$check_Groups->bind_param("ss", $uDs, $prebind);
$check_Groups->execute();
$result_check_Groups = $check_Groups->get_result();
if ($result_check_Groups->num_rows > 0) {
?>
            <div class="w100p flex z4">
                <h2 class="rightMg pad-s-s pad-m-v txt-n z5">Groups</h2>
            </div>
<?php
    while ($value = $result_check_Groups->fetch_assoc()) {
        $groupIds = $value['identification'];
        $groupsName = $value['names'];
        $member_count = $value['member_count'];
        $groupsLogo = $value['logo'];
?>
            <div class="posr bottomMg-s5 pad-m-v pad-s-s w100p flex z4">
<?php
        if (empty($groupsLogo) || $groupsLogo === "empty") {
?>
                <img src="img/business-outline.svg" class="r1-1 w20p flex acjc border-1 containfit bg-half-white z4">
<?php
        } else {
?>
                <img src="Groups/img/<?php echo $groupIds . "/" . $groupsLogo;?>" alt="<?php echo $groupsName;?>" class="r1-1 w20p flex acjc border-1 containfit z4">
<?php
        };
?>
                <div class="posr w80p flex fld">
                    <h2 class="topMg rightMg pad-s-s txt-s"><?php echo $groupsName;?></h2>
                    <h2 class="bottomMg rightMg pad-s-s txt-s c-gray"><?php echo $member_count;?> Members</h2>
                </div>
                <a href="Groups/profile.php?gids=<?php echo $groupIds;?>" class="link-cover hover-white">.</a>
            </div>
<?php
    };
};
?>
        </div>
    </div>
</div>
    <!-- messages alerter -->
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <?php include_once 'footer.php';?>
    <script src="scriptstuff/script.js"></script>
    <script src="scriptstuff/alert.js"></script>
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
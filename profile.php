<?php
require_once 'processes/database.php';
$errors = array();
$allowEdits = false;
$haveBadge = false;
$haveAchievement = false;
$badges = array();
$Achievements = array();
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
$check_profile_data = $connects->prepare("SELECT * FROM profiles WHERE profileTags = ? ;");
$check_profile_data->bind_param("s", $uDs);
$check_profile_data->execute();
$result_check_profile_data = $check_profile_data->get_result();
if ($result_check_profile_data->num_rows == 1) {
    $value = $result_check_profile_data->fetch_assoc();
    $Tags = $value['profileTags'];
    $pfAttachs = $value['profileAttachs'];
    $Names = $value['profileNames'];
    $Bios = $value['profileBios'];
    $JDates = $value['profileJDates'];
    $allowInvite = $value['allowInvite'];
    $oState = $value['oState'];
} else {
    $_SESSION['corsmsg'] = "user account does not exists or on a temporary bans";
    header ('location: index.php');
    exit;
};
$check_profile = $connects->prepare("SELECT Badge, mkot FROM profiles WHERE profileTags = ? ;");
$check_profile->bind_param("s", $uDs);
$check_profile->execute();
$result_check_profile = $check_profile->get_result();
if ($result_check_profile->num_rows == 1) {
    $value = $result_check_profile->fetch_assoc();
    $mkot = $value['mkot'];
    $badgeArr = $value['Badge'];
    $badgeArr = json_decode($badgeArr, true);
    $data = json_decode($mkot, true);
    $markedData = $data['marked'];
    $privated = $data['private'];
    if (!empty($markedData) && $markedData != "empty") {
        $marked = [];
        foreach ($markedData as $markedIndex => $info) {
            $marked[$markedIndex] = [
                "libsIds"  => $info['libsIds'],
                "Hours"    => (int)$info['Hours'],
                "lastLog"  => $info['lastLog']
            ];
        }
    }
    $usrDatTemp[] = [
        "private"   => $privated
    ];
    foreach ($badgeArr as $badgeIndex => $badgeValue) {
        $check_badges = $connects->prepare("SELECT * FROM specialbadge WHERE badgeIds = ? ;");
        $check_badges->bind_param("s", $badgeIndex);
        $check_badges->execute();
        $result_check_badges = $check_badges->get_result();
        if ($result_check_badges->num_rows > 0) {
            while ($value = $result_check_badges->fetch_assoc()) {
                if ($value['badgeType'] === "profile") {
                    $haveBadge = true;
                    $badges[$value['badgeIds']] = [
                        "badgesIds" => $value['badgeIds'],
                        "badgeName" => $value['badgeName'],
                        "badgeType" => $value['badgeType'],
                        "badgeRefs" => $value['badgeRefs'],
                        "badgeIcon" => $value['icon'],
                        "badgeDate" => $badgeValue
                    ];
                }
                if ($value['badgeType'] === "achievement") {
                    $haveAchievement = true;
                    $Achievements[$value['badgeIds']] = [
                        "badgesIds" => $value['badgeIds'],
                        "badgeName" => $value['badgeName'],
                        "badgeType" => $value['badgeType'],
                        "badgeRefs" => $value['badgeRefs'],
                        "badgeIcon" => $value['icon'],
                        "badgeDate" => $badgeValue
                    ];
                }
            };
        };
    }
} elseif ($result_check_profile->num_rows < 0) {
    $no_mkot = true;
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
    <img src="img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity05 z-1">
    <!-- nav -->
    <div class="posr pad-n-s w100p maxw100 minh10 flex gap-s bg-4 blurbg z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="index.php" class="link-cover">.</a>
        </div>
        <div class="posr w60p flex gap-s">
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
            <p class="posr leftMg vertiMg pad-n-s pad-s-v txtc txt-n c-white bg-1 blurbg border-1 bora-s border-hover-orange hover-text-orange points" onclick="uniDisplaySwitch('settings');">Settings</p>
        <?php
        }
        ?>
    </div>
<!-- settings panel -->
    <dialog id="settings" class="posf c0 w100vh minw40 maxw100 maxh100 dp-none fld bg-half-gray blurbg border-1 bora-s ovh-s z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">Settings & Notification</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('settings');">X</p></div>
        <form id="settingForm" class="posr topMg-s10 w100p flex fld gap10" name="settingForm" action="processes/bionic.php" method="post">
            <div class="posr topMg-s10 sideMg pad-n-s w95p flex space-between gap10">
                <label for="privated">Privated profile badge</label>
                <input type="checkbox" name="privated" id="privated" <?php if ($privated == true) { echo "checked"; };?>>
            </div>
            <div class="posr topMg-s10 sideMg pad-n-s w95p flex space-between gap10">
                <label for="allowinvite">Allow Groups Invite?</label>
                <input type="checkbox" name="allowinvite" id="allowinvite" <?php if ($allowInvite === "active") { echo "checked"; };?>>
            </div>
            <div class="posr topMg-s10 sideMg pad-n-s w95p flex">
                <input class="posr leftMg pad-m-v pad-n-s txt-n c-black bgc-gold bora-s" type="submit" name="submit" value="Update Settings">
            </div>
        </form>
        <div class="posr pad-n-v w100p flex fld gap5">
            <h2 class="sideMg w95p txt-b">Invites</h2>
<?php
$stmt_check_invite = $connects->prepare("SELECT inviteToken, og_identification, custom_msg FROM groupinvite WHERE profileTags = ? ;");
$stmt_check_invite->bind_param("s", $aidis);
$stmt_check_invite->execute();
$result_check_invite = $stmt_check_invite->get_result();
if ($result_check_invite->num_rows > 0) {
    while ($tempInvtVAl = $result_check_invite->fetch_assoc()){
        $inviteToken = $tempInvtVAl['inviteToken'];
        $invGids = $tempInvtVAl['og_identification'];
        $cmsg = $tempInvtVAl['custom_msg'];
        $check_orgs = $connects->prepare("SELECT names FROM ogroup WHERE identification = ? ;");
        $check_orgs->bind_param("s", $invGids);
        $check_orgs->execute();
        $result_check_orgs = $check_orgs->get_result();
        if ($result_check_orgs->num_rows > 0) {
            $tempOgVAl = $result_check_orgs->fetch_assoc();
            $gName = $tempOgVAl['names'];
?>
            <form name="<?php echo $gName;?>" action="Groups/access.php" method="post" class="posr topMg-s5 bottomMg-s10 sideMg pad-s w95p flex acjc bg-half-gray box-shad-black-1 border-purple bora-s gap5">
                <h2 class='posr w30p txtnowrap'><?php echo $gName;?></h2>
                <input class="hiddeninp" type="text" name="inviteToken" value="<?php echo $inviteToken;?>" hidden>
                <h2 class="posr leftMg pad-m-v pad-n-s txt-n bg-half-gray border-1 border-hover-white bora-s points" onclick="uniDisplaySwitch('invmessages');uniLoad(this,'invmsgform');" data-invmessage="<?php echo $cmsg;?>">messages</h2>
                <input class="posr pad-m-v pad-n-s txt-n bgc-green border-1 border-hover-white bora-s points" type="submit" name="submit" id="Join" value="Join">
                <input class="posr rightMg-s10 pad-m-v pad-n-s txt-n bgc-red border-1 border-hover-white bora-s points" type="submit" name="submit" id="Dismiss" value="Dismiss">
            </form>
<?php
        }
    }
    $stmt_check_invite->close();
} else {
?>
            <p class="posr sideMg w95p">No invitation for now</p>
<?php
}
?>
        </div>
    </dialog>
<!-- invite messages panel -->
    <dialog id="invmessages" class="posf c0 minw20 maxw50 maxh50 dp-none fld acjc blurbg bg-half-white border-1 bora-s ovh z999">
        <div class="posr w100p flex"><h2 class="posr rightMg pad-s txt-b">Invite messages</h2><p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('invmessages');">X</p></div>
        <form id="invmsgform" class="posr wh100p pad-s flex fld">
            <textarea type="text" name="invmessage" class="posr pad-m sideMg w95p minh10 c-black bora-s ovh-s" placeholder="" auto-complete="off" required></textarea>
        </form>
    </dialog>
<!-- log-out confirm -->
    <div id="confirmElems" class="posf pad-n c0 pad-b-v minw20 maxh50 dp-none fld bg-1 border1 bora-s z999">
        <h2 class="w100p txt-b txtc">Want to Log-Out?</h2>
        <div class="topMg-s10 sideMg flex acjc gap-s">
            <button class="pad-n-s pad-s-v txt-n txtc bg-red border-1 border-hover-white" onclick="linker('processes/logout.php')">YES</button>
            <button class="pad-n-s pad-s-v txt-n txtc c-black border-1 border-hover-white" onclick="uniDisplaySwitch('confirmElems')">NO</button>
        </div>
    </div>
<!-- edit profpic -->
    <dialog id="profilepic" class="posf c0 dp-none fld acjc bg-half-purple blurbg border-none ovh-s z999">
        <div class="posr w100p flexblurbg flex"><h2 class="rightMg pad-s txt-b">Change Profile Picture</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('profilepic')">X</p></div>
        <form class="posr wh100p flexblurbg flex fld gap10" name="profileform" action="processes/bionic.php" method="post" enctype="multipart/form-data">
            <div class="posr topMg-s10 pad-n-s min50 h80p maxh50 r1-1 flex fld acjc">
                <img id="preview" class="posr sideMg wh100p bg-half-gray containfit">
                <input class="posa c0 pad-n-s w100p txtc" type="file" name="profilepic" accept="image/*" onchange="uniLoadFile(event, 'preview');">
            </div>
            <div class="posr w100p bottomMg-s10 pad-n-s flex fld">
                <input class="sideMg pad-m-v pad-n-s w100p txt-n c-black bgc-gold bora-s" type="submit" name="submit" value="Change Profile">
            </div>
        </form>
    </dialog>
<!-- edit bio -->
    <dialog id="editBios" class="posf c0 w50 dp-none fld acjc bg-half-white border-1 bora-s ovh z999">
        <div class="posr wh100p flexblurbg flex"><h2 class="rightMg pad-s txt-b">Edit Bio</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('editBios');">X</p></div>
        <form id="biosForm" class="posr wh100p flexblurbg flex fld gap10" name="BIOS" action="processes/bionic.php" method="post">
            <div class="posr topMg-s10 sideMg w95p flex">
                <textarea type="text" name="bioedits" class="pad-m w100p h50 c-black bora-s ovh-s" placeholder="" auto-complete="off" maxlength="2500" required></textarea>
            </div>
            <div class="sideMg bottomMg-s10 w95p flex">
                <input class="sideMg pad-m-v pad-n-s w100p txt-n c-black bgc-gold bora-s" type="submit" name="submit" value="Update Bio">
            </div>
        </form>
    </dialog>
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
    <div class="posr topMg-s10 w70p h40 flex bg-prf-default blurbg z2">
        <?php
            $iconAlt = ucfirst(substr($Names, 0, 1));
        ?>
        <div class="posr vertiMg r1-1 w20p flex z3">
            <?php
            if (empty($pfAttachs) || $pfAttachs === "empty") {
            ?>
            <img src="img/person.svg" class="autoMg r1-1 h80p flex acjc bgc-purple containfit bora-s z4<?php if ($allowEdits == true) { ?> hover-white" onclick="uniDisplaySwitch('profilepic');"<?php } else { echo '"'; } ?>>
            <?php
            } else {
            ?>
            <img src="zprpic/<?php echo $Tags . "/" . $pfAttachs;?>" alt="<?php echo $Names;?>" class="autoMg r1-1 h80p flex acjc bgc-purple containfit bora-s z4<?php if ($allowEdits == true) { ?> hover-white" onclick="uniDisplaySwitch('profilepic');"<?php } else { echo '"'; } ?>>
            <?php
            };
            ?>
        </div>
        <div class="posr pad-n-v pad-sr w50p h100p flex fld gap5 z4">
            <h2 class="posr topMg w100p txt-l"><?php echo $Names;?></h2>
            <div class="posr w100p flex">
                <p class="posr rightMg txt-s">Joined since <?php echo $JDates;?></p>
            </div>
            <?php
            if ($allowEdits == true) {
            ?>
            <div class="posr pad-s-v w100p minh20 maxh20 txt-s border-t border-b ovh-s"><?php echo $Bios;?></div>
            <button class="posr bottomMg w20p pad-m-v pad-n-s txt-s c-white bgc-purple bora-s" onclick="uniDisplaySwitch('editBios'); uniLoad(this, 'biosForm');" data-bioedits="<?php echo $Bios;?>">Edit Bio</button>
            <?php
            } else {
            ?>
            <div class="posr bottomMg pad-s-v w100p minh20 maxh20 txt-s border-t border-b ovh-s"><?php echo $Bios;?></div>
            <?php
            }
            ?>
        </div>
        <?php
        ?>
        <div class="posr pad-n-v w30p h100p flex fld acjc gap5 z4">
<?php
if ($haveBadge == true && $privated == false || isset($aidis) && $_GET['user'] === "self" && $haveBadge == true ) {
?>
            <div class="posr sideMg w95p r4-1 flex acjc z4">
<?php
    foreach ($badges as $tempBadge => $value) {
        $badgesIds = $tempBadge;
        $badgeName = $value['badgeName'];
        $badgeType = $value['badgeType'];
        $badgeIcon = $value['badgeIcon'];
?>
                <div class="posr pad-m icon-s bg-half-white border-1 bora-s">
                    <img src="img/<?php echo $badgeIcon;?>" alt="<?php echo $badgeIcon;?>" class="wh100p containfit">
                    <a href="#" class="link-cover hover-white">.</a>
                </div>
<?php
        };
?>
            </div>
<?php
};
if ($allowEdits == true) {
?>
            <div class="posr sideMg w95p flex acjc bg-green">
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
    <div class="posr bottomMg-5 pad-s w70p minh40 flex gap-s bg-prf-default blurbg z2">
        <div class="posr sideMg pad-n-v pad-s-s w70p flex fld z3">
<?php
if ($haveAchievement == true && $privated == false || isset($aidis) && $_GET['user'] === "self" && $haveAchievement == true ) {
?>
            <div class="bgc-purple w100p flex"><h2 class="rightMg pad-s txt-b z3">Achievement Showcase</h2></div>
            <div class="posr pad-n-v pad-s-s w100p flex wrap gap10 z3">
<?php
    foreach ($Achievements as $tempAchievement => $value) {
        $AchievementsIds = $tempAchievement;
        $AchievementName = $value['badgeName'];
        $AchievementType = $value['badgeType'];
        $AchievementIcon = $value['badgeIcon'];
        ?>
                <div class="posr pad-m icon-s bg-half-white border-1 bora-s z4">
                    <img src="img/<?php echo $AchievementIcon;?>" alt="<?php echo $AchievementIcon;?>" class="wh100p containfit">
                    <a href="#" class="link-cover hover-white">.</a>
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
            <div class="bgc-purple w100p flex"><h2 class="rightMg pad-s txt-b">Posted Forum</h2></div>
            <div class="bottomMg pad-n-v pad-s-s w100p w95p minh20 flex wrap z1">
<?php
    $uniqueItem = [];
    while ($value = $result_check_userpost->fetch_assoc()) {
        $ids= $value['ForumIds'];
        $creators = $value['ForumCreator'];
        $titles = $value['ForumTitles'];
        $dates = $value['ForumDates'];
        $contents = $value['ForumContents'];
        $attachs = $value['ForumAttachment'];
        if (!in_array($ids, $uniqueItem)) {
?>
            <div class="posr pad-s minw15 w50p r16-9 flex fld bg-half-gray border-1 gap5">
<?php
            if ($attachs != "empty.png" && isset($attachs)) {
?>
                <img src="TS/img/<?php echo $ids . '/' . $attachs;?>" alt="" class="posa c0 w100p r16-9 coverfit opacity3 z2">
<?php
            };
?>
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
$check_orgs = $connects->prepare("SELECT identification, names, JSON_LENGTH(members) AS member_count, logo FROM ogroup WHERE founder = ? OR JSON_CONTAINS(members, ?);");
$check_orgs->bind_param("ss", $uDs, $prebind);
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
                <img src="img/business-outline.svg" class="r1-1 w20p flex acjc border-1 containfit bg-half-white z4">
<?php
            } else {
?>
                <img src="Groups/img/<?php echo $OgIdentific . "/" . $logo;?>" alt="<?php echo $OgName;?>" class="r1-1 w20p flex acjc border-1 containfit z4">
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
    <!-- messages alerter -->
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
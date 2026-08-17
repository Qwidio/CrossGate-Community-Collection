<?php
require_once 'processes/database.php';
require_once 'secureSession.php';
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    header ('location: index.php');
    exit;
};
$check_user = $connects->prepare("SELECT userState FROM user WHERE profileTags = ? ;");
$check_user->bind_param("s", $aidis);
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
$_SESSION['prev_loc'] = "settings.php";
$ownedBadges = false;
$badges = array();
$favbadge = "none";
$check_profile = $connects->prepare("SELECT * FROM profiles WHERE profileTags = ? ;");
$check_profile->bind_param("s", $aidis);
$check_profile->execute();
$result_check_profile = $check_profile->get_result();
if ($result_check_profile->num_rows == 1) {
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
    $_SESSION['corsmsg'] = "user profile cannot be found";
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
    <title>Settings</title>
</head>
<body>
    <img src="img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit opacity05 z-1">
    <div class="posr sideMg wh100p flex fld bg-def-2 blurbg bora-s ovh-s z15">
        <div id="settings" class="posr sideMg w100vh minw40 maxw100 bg-prf-2 h100 flex fld bg-def-1 ovh-s">
            <div class="posr w100p flex">
                <a href="profile.php?user=self" class="posr pad-n txt-b bold hover-text-blue"><<</a>
                <h2 class="pad-n-v txt-b">Settings</h2>
            </div>
            <div class="posr topMg-s10 w100p flex fld gap10">
                <div class="posr sideMg w95p pad-n-s flex acjc">
                    <label for="favbadge">Profile Picture</label>
                    <a class="posr leftMg pad-n-s pad-m-v minw10 txtc bg-def-2 hover-text-blue hover-border-white border-1 bora-s" onclick="uniDisplaySwitch('profilepic');">Change</a>
                </div>
                <div class="posr sideMg w95p pad-n-s flex acjc">
                    <label for="favbadge">Profile Bio</label>
                    <a class="posr leftMg pad-n-s pad-m-v minw10 txtc bg-def-2 hover-text-blue hover-border-white border-1 bora-s" onclick="uniDisplaySwitch('editBios'); uniLoad(this, 'biosForm');" data-bioedits="<?php echo $Bios;?>">Edit</a>
                </div>
                <div class="posr sideMg w95p pad-n-s flex acjc">
                    <label for="favbadge">Favorite Badge</label>
                    <a class="posr leftMg pad-n-s pad-m-v minw10 txtc bg-def-2 hover-text-blue hover-border-white border-1 bora-s" onclick="uniDisplaySwitch('favbadges');">Change</a>
                </div>
                <div class="posr sideMg w95p pad-n-s flex acjc">
                    <label for="favbadge">Background theme</label>
                    <a class="posr leftMg pad-n-s pad-m-v minw10 txtc bg-def-2 hover-text-blue hover-border-white border-1 bora-s" onclick="uniDisplaySwitch('bgtheme');">Change</a>
                </div>
                <div class="posr sideMg w95p pad-n-s flex acjc">
                    <label for="favbadge">Privacy</label>
                    <a class="posr leftMg pad-n-s pad-m-v minw10 txtc bg-def-2 hover-text-blue hover-border-white border-1 bora-s" onclick="uniDisplaySwitch('settingForm');">Open</a>
                </div>
            </div>
            <form id="settingForm" class="posr topMg-s10 sideMg pad-s-v w95p dp-none fld gap10 bg-prf-2 box-shad-black-1 bora-s ovh-s" name="settingForm" action="processes/bionic.php" method="post">
                <input class="hiddeninp" type="text" name="request" value="settings" hidden readonly>
                <div class="posr sideMg pad-st pad-n-s w100p flex space-between gap10">
                    <label for="privated">Privated profile badge</label>
                    <input type="checkbox" name="privated" id="privated" <?php if ($privated == true) { echo "checked"; };?>>
                </div>
                <div class="posr sideMg pad-st pad-n-s w100p flex space-between gap10">
                    <label for="allowinvite">Allow Groups Invite?</label>
                    <input type="checkbox" name="allowinvite" id="allowinvite" <?php if ($allowInvite === "active") { echo "checked"; };?>>
                </div>
                <div class="posr sideMg pad-st pad-n-s w100p flex">
                    <input class="posr leftMg pad-m-v pad-n-s minw10 txt-n c-black bgc-gold bora-s" type="submit" name="submit" value="Update Settings">
                </div>
            </form>
            <div class="posr pad-n-v w100p flex fld gap5">
                <h2 class="posr w100p pad-n-s txt-b">Invites</h2>
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
                <h2 class="posr leftMg pad-m-v pad-n-s txt-n bgc-gray border-1 border-hover-white bora-s points" onclick="uniDisplaySwitch('invmessages');uniLoad(this,'invmsgform');" data-invmessage="<?php echo $cmsg;?>">messages</h2>
                <input class="posr pad-m-v pad-n-s txt-n bgc-green border-1 border-hover-white bora-s points" type="submit" name="submit" id="Join" value="Join">
                <input class="posr rightMg-s10 pad-m-v pad-n-s txt-n bgc-red border-1 border-hover-white bora-s points" type="submit" name="submit" id="Dismiss" value="Dismiss">
            </form>
<?php
        }
    }
    $stmt_check_invite->close();
} else {
?>
            <p class="posr sideMg pad-n-s w95p">No invitation for now</p>
<?php
}
?>
            </div>
        </div>
    </div>
<!-- invite messages panel -->
    <dialog id="invmessages" class="posf c0 minw20 maxw50 maxh50 dp-none fld acjc blurbg bg-prf-1 border-1 bora-s ovh z999">
        <div class="posr w100p flex"><h2 class="posr rightMg pad-s txt-b">Invite messages</h2><p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('invmessages');">X</p></div>
        <form id="invmsgform" class="posr wh100p pad-s flex fld">
            <textarea type="text" name="invmessage" class="posr pad-m sideMg w95p minh10 c-black bora-s ovh-s" placeholder="" auto-complete="off" readonly></textarea>
        </form>
    </dialog>
<!-- bgtheme -->
    <dialog id="bgtheme" class="posf c0 w50 w100vh minw40 maxw100 h100 dp-none fld bg-def-1 ovh-s border-none z999">
        <div class="posr w100p flexblurbg flex"><h2 class="rightMg pad-n txt-b">Featured favorite Badge</h2><p class="pad-n txt-b hover-red" onclick="uniDisplaySwitch('bgtheme')">X</p></div>
        <div id="bgDisplay" class="posr topMg-s10 sideMg w95p h40 flex <?php echo $themes["bg"];?> box-shad-black-1 bora-s z2">
            <div class="posr vertiMg r1-1 w20p flex z3">
                <img class="autoMg r1-1 h80p flex acjc bgc-white coverfit bora-s z4">
            </div>
            <div class="posr vertiMg pad-n-v pad-sr w50p h80p flex fld z4">
                <h2 class="posr topMg w100p txt-l">...</h2>
                <div class="posr w100p flex">
                    <p class="posr rightMg txt-s">_____</p>
                </div>
                <div class="posr topMg-s5 bottomMg pad-s-v w100p h10 txt-s ovh-s">...</div>
            </div>
            <div class="posr leftMg pad-n-v w30p h100p flex fld acjc gap5 z4">
                <div class="posr sideMg w95p pad-n flex gap10 bg-def-2 box-shad-black-1 border-purple bora-s hover-border-white ovh-s z4">
                    <div class="posr vertiMg flex r1-1 h100p">
                        <img class="posr h100p r1-1 flex acjc bgc-white coverfit bora-s z4">
                    </div>
                    <div class="vertiMg flex fld">
                        <h2 class="posr txt-s">...</h2>
                        <p class="posr txt-ms c-lightgray hover-text-white">___</p>
                        <p class="posr txt-ms c-lightgray hover-text-white">___</p>
                    </div>
                </div>
            </div>
        </div>
        <form class="posr w100p flexblurbg flex fld gap10" name="favbadgeform" action="processes/bionic.php" method="post" enctype="multipart/form-data">
            <h2 id="accentDisplay" class="posr topMg-s10 sideMg pad-s w95p <?php echo $themes["accent"];?> <?php echo $themes["color"];?> box-shad-black-1 bora-s txt-n txtc">Tick a checkbox below to change the background and accent themes</h2>
            <div class="posr topMg-s10 sideMg pad-s w95p flex acjc gap10 bg-def-1 box-shad-white-1 bora-s">
                <div class="posr flex fld acjc bg-half-gray border-none bora-s ovh">
                    <img class="posr icon-s bg-prf-1 blurbg border-none ovh">
                    <input class="posa" type="checkbox" name="themescheckbox" id="themesCheckbox" value="0">
                </div>
                <div class="posr flex fld acjc bg-half-gray border-none bora-s ovh">
                    <img class="posr icon-s bg-prf-2 blurbg border-none ovh">
                    <input class="posa" type="checkbox" name="themescheckbox" id="themesCheckbox" value="2">
                </div>
                <div class="posr flex fld acjc bg-half-gray border-none bora-s ovh">
                    <img class="posr icon-s bg-prf-3 blurbg border-none ovh">
                    <input class="posa" type="checkbox" name="themescheckbox" id="themesCheckbox" value="3">
                </div>
                <div class="posr flex fld acjc bg-half-gray border-none bora-s ovh">
                    <img class="posr icon-s bg-prf-4 blurbg border-none ovh">
                    <input class="posa" type="checkbox" name="themescheckbox" id="themesCheckbox" value="4">
                </div>
            </div>
            <input class="hiddeninp" type="text" name="request" value="selectThemes" hidden readonly>
            <input class="hiddeninp" type="text" name="selectedThemes" id="selectedThemes" hidden readonly>
            <div class="posr topMg-s10 sideMg pad-n-s w95p flex">
                <input class="posr leftMg pad-m-v pad-n-s minw10 txt-n c-black bgc-gold" type="submit" name="submit" value="Save Changes">
            </div>
        </form>
    </dialog>
<!-- favbadges -->
    <dialog id="favbadges" class="posf c0 w50 w100vh minw40 maxw100 h100 dp-none fld bg-def-1 ovh-s border-none z999">
        <div class="posr w100p flexblurbg flex"><h2 class="rightMg pad-n txt-b">Featured favorite Badge</h2><p class="pad-n txt-b hover-red" onclick="uniDisplaySwitch('favbadges')">X</p></div>
        <div class="posr topMg-s10 sideMg w95p h40 flex bg-half-gray box-shad-black-1 bora-s z2">
            <div class="posr vertiMg r1-1 w20p flex z3">
                <img class="autoMg r1-1 h80p flex acjc bgc-white coverfit bora-s z4">
            </div>
            <div class="posr vertiMg pad-n-v pad-sr w50p h80p flex fld z4">
                <h2 class="posr topMg w100p txt-l">...</h2>
                <div class="posr w100p flex">
                    <p class="posr rightMg txt-s">_____</p>
                </div>
                <div class="posr topMg-s5 bottomMg pad-s-v w100p h10 txt-s ovh-s">...</div>
            </div>
            <div class="posr leftMg pad-n-v w30p h100p flex fld acjc gap5 z4">
<?php
if ($ownedBadges == true && $favbadge != "none") {
        $badgeVals = $badges[$favbadge];
        $badgeName = $badgeVals['badgeName'];
        $badgeDesc = $badgeVals['badgeDesc'];
        $badgeIcon = $badgeVals['badgeIcon'];
        $badgeRefs = $badgeVals['badgeRefs'];
        $badgeDate = $badgeVals['badgeDate'];
?>
                <div class="posr sideMg pad-n w95p minh10 flex gap10 bg-def-2 box-shad-black-1 border-purple bora-s hover-border-white ovh-s z4">
                    <div class="posr vertiMg flex icon-ts">
                        <img src="ab/<?php echo $badgeRefs . "/" . $badgeIcon;?>" alt="<?php echo $badgeIcon;?>" class="posr h100p r1-1 flex acjc containfit bora-s z4">
                    </div>
                    <div class="vertiMg flex fld">
                        <h2 class="posr txt-s"><?php echo $badgeName;?></h2>
                        <p class="posr txt-ms c-lightgray hover-text-white"><?php echo $badgeDesc;?></p>
                        <p class="posr txt-ms c-lightgray hover-text-white">obtained <?php echo $badgeDate;?></p>
                    </div>
                </div>
<?php
} else if ($ownedBadges == true && $favbadge === "none") {
?>
                <div class="posr sideMg w95p pad-s-v pad-n-s flex gap10 bg-def-2 box-shad-black-1 border-purple bora-s hover-border-white ovh-s z4">
                    <div class="vertiMg flex fld">
                        <h2 class="posr txt-ms">You can feature one of your favorite Badges here. Select one from below.</h2>
                    </div>
                </div>
<?php
};
?>
            </div>
        </div>
        <form class="posr w100p flexblurbg flex fld gap10" name="favbadgeform" action="processes/bionic.php" method="post" enctype="multipart/form-data">
            <div class="posr topMg-s10 pad-s sideMg pad-n-s w95p flex gap5 bg-half-gray box-shad-black-1 bora-s">
                <h2 class="posr vertiMg txt-n txtc">Tick one checkbox of your badges to be featured.</h2>
                <input class="posr leftMg pad-m-v pad-n-s minw10 txt-n c-black bgc-gold bora-s" type="submit" name="submit" value="Save Changes">
            </div>
            <div class="posr sideMg pad-s w95p maxh50 flex fld gap10 bg-half-gray box-shad-black-1 bora-s ovh-s">
<?php
    foreach ($badges as $tempBadge => $value) {
        $badgesIds = $tempBadge;
        $badgeName = $value['badgeName'];
        $badgeType = $value['badgeType'];
        $badgeDesc = $value['badgeDesc'];
        $badgeIcon = $value['badgeIcon'];
        $badgeRefs = $value['badgeRefs'];
        $badgeDate = $value['badgeDate'];
        ?>
                <div class="posr sideMg pad-s w100p minh10 maxh10 flex gap10 bg-def-1 box-shad-black-1 border-purple bora-s hover-border-white z4">
                    <input class="posr" type="checkbox" name="badgescheckbox" id="badgesCheckbox" value="<?php echo $badgesIds;?>">
                    <div class="posr vertiMg flex h5 r1-1">
                        <img src="ab/<?php echo $badgeRefs . "/" . $badgeIcon;?>" alt="<?php echo $badgeIcon;?>" class="posr h100p r1-1 flex acjc containfit bora-s z4">
                    </div>
                    <div class="vertiMg flex fld">
                        <h2 class="posr txt-n"><?php echo $badgeName;?></h2>
                        <p class="posr txt-ms c-lightgray hover-text-white"><?php echo $badgeDesc;?></p>
                        <p class="posr txt-ms c-lightgray hover-text-white">obtained <?php echo $badgeDate;?></p>
                    </div>
                </div>
                <?php
    };
    ?>
            </div>
            <input class="hiddeninp" type="text" name="request" value="selectBadges" hidden readonly>
            <input class="hiddeninp" type="text" name="selectedBadges" id="selectedBadges" hidden readonly>
        </form>
    </dialog>
<!-- edit profpic -->
    <dialog id="profilepic" class="posf c0 w50 w100vh minw40 maxw100 h100 dp-none fld bg-def-1 ovh-s border-none z999">
        <div class="posr w100p flexblurbg flex"><h2 class="rightMg pad-n txt-b">Change Profile Picture</h2><p class="pad-n txt-b hover-red" onclick="uniDisplaySwitch('profilepic')">X</p></div>
        <div class="posr topMg-s10 sideMg w95p h40 flex bg-half-gray box-shad-black-1 bora-s z2">
            <div class="posr vertiMg r1-1 w20p flex z3">
    <?php
    if (empty($pfAttachs) || $pfAttachs === "empty") {
    ?>
                <img  id="preview" src="" class="autoMg r1-1 h80p flex acjc bgc-white containfit bora-s z4">
    <?php
    } else {
    ?>
        <img src="zprpic/<?php echo $Tags . "/" . $pfAttachs;?>" alt="<?php echo $Names;?>" class="autoMg r1-1 h80p flex acjc bgc-white containfit bora-s z4">
    <?php
    };
    ?>
            </div>
            <div class="posr vertiMg pad-n-v pad-sr w50p h80p flex fld z4">
                <h2 class="posr topMg w100p txt-l">...</h2>
                <div class="posr w100p flex">
                    <p class="posr rightMg txt-s">_____</p>
                </div>
                <div class="posr topMg-s5 bottomMg pad-s-v w100p h10 txt-s ovh-s">...</div>
            </div>
            <div class="posr leftMg pad-n-v w30p h100p flex fld acjc gap5 z4">
                <div class="posr sideMg w95p pad-n flex gap10 bg-def-2 box-shad-black-1 border-purple bora-s hover-border-white ovh-s z4">
                    <div class="posr vertiMg flex r1-1 h100p">
                        <img class="posr h100p r1-1 flex acjc bgc-white coverfit bora-s z4">
                    </div>
                    <div class="vertiMg flex fld">
                        <h2 class="posr txt-s">...</h2>
                        <p class="posr txt-ms c-lightgray hover-text-white">___</p>
                        <p class="posr txt-ms c-lightgray hover-text-white">___</p>
                    </div>
                </div>
            </div>
        </div>
        <form class="posr w100p flexblurbg flex fld gap10" name="profilepicform" action="processes/bionic.php" method="post" enctype="multipart/form-data">
            <div class="posr topMg-s10 sideMg pad-s-s pad-m-v w95p flex acjc bg-half-gray box-shad-black-1 bora-s">
                <input class="posr pad-n-s w100p txtc txtnowrap ovh" type="file" name="profilepic" accept="image/*" onchange="uniLoadFile(event, 'preview');">
            </div>
            <div class="posr topMg-s10 sideMg pad-n-s w95p flex">
                <input class="posr leftMg pad-m-v pad-n-s minw10 txt-n c-black bgc-gold" type="submit" name="submit" value="Change Profile">
            </div>
        </form>
    </dialog>
<!-- edit bio -->
    <dialog id="editBios" class="posf c0 w50 w100vh minw40 maxw100 h100 dp-none fld bg-def-1 ovh-s border-none z999">
        <div class="posr w100p flex"><h2 class="rightMg pad-n txt-b">Edit Profile Bio</h2><p class="pad-n txt-b hover-red" onclick="uniDisplaySwitch('editBios');">X</p></div>
        <div class="posr topMg-s10 sideMg w95p h40 flex bg-half-gray box-shad-black-1 bora-s z2">
            <div class="posr vertiMg r1-1 w20p flex z3">
                <img class="autoMg r1-1 h80p flex acjc bgc-white containfit bora-s z4">
            </div>
            <div class="posr vertiMg pad-n-v pad-sr w50p h80p flex fld z4">
                <h2 class="posr topMg w100p txt-l">...</h2>
                <div class="posr w100p flex">
                    <p class="posr rightMg txt-s">_____</p>
                </div>
                <div class="posr topMg-s5 bottomMg pad-s-v w100p h10 txt-s ovh-s"><?php echo $Bios;?></div>
            </div>
            <div class="posr leftMg pad-n-v w30p h100p flex fld acjc gap5 z4">
                <div class="posr sideMg w95p pad-n flex gap10 bg-def-2 box-shad-black-1 border-purple bora-s hover-border-white ovh-s z4">
                    <div class="posr vertiMg flex r1-1 h100p">
                        <img class="posr h100p r1-1 flex acjc bgc-white coverfit bora-s z4">
                    </div>
                    <div class="vertiMg flex fld">
                        <h2 class="posr txt-s">...</h2>
                        <p class="posr txt-ms c-lightgray hover-text-white">___</p>
                        <p class="posr txt-ms c-lightgray hover-text-white">___</p>
                    </div>
                </div>
            </div>
        </div>
        <form id="biosForm" class="posr w100p flex fld gap10" name="BIOS" action="processes/bionic.php" method="post">
            <div class="posr topMg-s10 sideMg w95p flex fld">
                <textarea type="text" name="bioedits" class="pad-m w100p h40 c-black bora-s ovh-s" placeholder="Input new bio here or leave empty if you want it to stay empty" auto-complete="off" maxlength="2500"></textarea>
            </div>
            <div class="sideMg bottomMg-s10 w95p flex">
                <input class="sideMg pad-m-v pad-n-s w100p txt-n c-black bgc-gold bora-s" type="submit" name="submit" value="Update Bio">
            </div>
        </form>
    </dialog>
    <!-- messages alerter -->
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script src="scriptstuff/script.js"></script>
    <script src="scriptstuff/alert.js"></script>
    <script type="text/javascript">
        function uncheckBadges() {
            var checkboxes = document.querySelectorAll('#badgesCheckbox');
            for (var i = 0, length = checkboxes.length; i < length; i++) {
                checkboxes[i].checked = false;
            }
        }
        function badgeChecked() {
            uncheckBadges();
            this.checked = true;
            var getbadges = this.value;
            document.getElementById('selectedBadges').value = getbadges;
            Selecting = true;
        }
        function uncheckThemes() {
            var checkboxes = document.querySelectorAll('#themesCheckbox');
            for (var i = 0, length = checkboxes.length; i < length; i++) {
                checkboxes[i].checked = false;
            }
        }
        function themesChecked() {
            uncheckThemes();
            this.checked = true;
            var themes = this.value;
            document.getElementById('selectedThemes').value = themes;
            if (themes === "2") {
                document.getElementById('bgDisplay').style.background = "linear-gradient(to bottom right, rgba(24, 36, 41, 0.400), rgba(54, 50, 61, 0.507))";
                document.getElementById('accentDisplay').style.background = "rgb(7, 163, 235)";
                document.getElementById('accentDisplay').style.color = "#ffffff";
            } else if (themes === "3") {
                document.getElementById('bgDisplay').style.background = "linear-gradient(to bottom right,  rgba(59, 112, 112, 0.51), rgba(32, 24, 41, 0.400))";
                document.getElementById('accentDisplay').style.background = "rgb(7, 163, 235)";
                document.getElementById('accentDisplay').style.color = "#ffffff";
            } else if (themes === "4") {
                document.getElementById('bgDisplay').style.background = "linear-gradient(to bottom left, rgba(255, 251, 0, 0.267), rgba(50, 57, 61, 0.507))";
                document.getElementById('accentDisplay').style.background = "rgb(232, 213, 4)";
                document.getElementById('accentDisplay').style.color = "#000000ff";
            } else {
                document.getElementById('bgDisplay').style.background = "linear-gradient(to bottom, rgba(49, 0, 128, 0.198), rgba(24, 36, 41, 0.266))";
                document.getElementById('accentDisplay').style.background = "rgb(111, 82, 177)";
                document.getElementById('accentDisplay').style.color = "#ffffff";
            }
            Selecting = true;
        }
        function init() {
            var checkboxes = document.querySelectorAll('#badgesCheckbox');
            for (var i = 0, length = checkboxes.length; i < length; i++) {
                checkboxes[i].addEventListener('click', badgeChecked);
            }
            var checkboxes = document.querySelectorAll('#themesCheckbox');
            for (var i = 0, length = checkboxes.length; i < length; i++) {
                checkboxes[i].addEventListener('click', themesChecked);
            }
        }
        init();
    </script>
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
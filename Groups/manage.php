<?php
require_once '../processes/database.php';
$errors = array();
$root_route = "../";
require_once '../secureSession.php';
require_once 'ReAuth.php';
if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
    $aidis = $_SESSION['profileTags'];
    $gToken = $_SESSION['GroupsToken'];
    $gids = $_SESSION['gids'];
    $ChangerRoles = $_SESSION['roles'];
} else {
    $_SESSION['corsmsg'] = "sign in to access";
    header ('location: ../index.php');
    exit;
}
$gids = $_SESSION['gids'];
$sitesArr = [];
$publishing = false;
$access = false;
$prebind = '"' . $aidis . '"';
$check_orgs = $connects->prepare("SELECT names, about, founded, members, logo, banner, sites FROM ogroup WHERE identification = ? AND founder = ? OR JSON_CONTAINS(members, ?);");
$check_orgs->bind_param("sss", $gids, $aidis, $prebind);
$check_orgs->execute();
$result_check_orgs = $check_orgs->get_result();
if ($result_check_orgs->num_rows > 0) {
    while ($value = $result_check_orgs->fetch_assoc()) {
        $Ognames = $value['names'];
        $about = $value['about'];
        $founded = $value['founded'];
        $members = $value['members'];
        $logo = $value['logo'];
        $banner = $value['banner'];
        $sites = json_decode($value['sites'], true);
        foreach ($sites as $siteIndex => $siteData) {
            $site = $siteData["site"];
            $yt = $siteData["yt"];
        }
    }
} else {
    $_SESSION['corsmsg'] = "You are not allowed to access this page";
    header('location: ../index.php');
    exit;
};
$memberExist = false;
$memberCount = 0;
$tempProfilesArr = [];
$ProfilesArr = [];
$memberslist = json_decode($members);
foreach ($memberslist as $Members => $value) {
    $check_profile = $connects->prepare("SELECT profileTags, profileAttachs, profileNames FROM profiles WHERE profileTags = ? ;");
    $check_profile->bind_param("s", $value);
    $check_profile->execute();
    $result_check_profile = $check_profile->get_result();
    if ($tempCheckProfVAl = $result_check_profile->fetch_assoc()) {
        $tempProfilesArr[$tempCheckProfVAl['profileTags']] = [
            "profileTags"   => $tempCheckProfVAl['profileTags'],
            "profileNames"  => $tempCheckProfVAl['profileNames']
        ];
    }
}
foreach ($tempProfilesArr as $Profiles => $value) {
    $tempTags = $value['profileTags'];
    $stmt_check_access = $connects->prepare("SELECT roles FROM groupaccess WHERE profileTags = ? AND og_identification = ? AND accountState = 'approved'");
    $stmt_check_access->bind_param("ss", $tempTags, $gids);
    $stmt_check_access->execute();
    $result_check_access = $stmt_check_access->get_result();
    if ($result_check_access->num_rows > 0) {
        $tempCheckAcsValue = $result_check_access->fetch_assoc();
        $ProfilesArr[$tempTags] = [
            "profileTags"   => $value['profileTags'],
            "profileNames"  => $value['profileNames'],
            "roles"         => $tempCheckAcsValue['roles']
        ];
        $memberCount++;
    }
    $memberExist = true;
}
$tempTopicArr = [];
$tempLibsArr = [];
$State = "Publics";
$stmt_check_software = $connects->prepare("SELECT libsIds, libsTitles, libsForum FROM libslist WHERE libsPublisher = ? AND libsState = ? ;");
$stmt_check_software->bind_param("ss", $gids, $State);
$stmt_check_software->execute();
$result_check_software = $stmt_check_software->get_result();
if ($result_check_software->num_rows > 0) {
    while ($value = $result_check_software->fetch_assoc()) {
        $ids = $value['libsIds'];
        $titles = $value['libsTitles'];
        $libsForum = $value['libsForum'];
        if (!in_array($ids, $tempLibsArr)) {
            $tempLibsArr[$ids] = [
            "libsIds"     => "$ids",
            "libsTitles"  => "$titles",
            "libsForum"   => "$libsForum"
            ];
        };
    };
    $publishing = true;
};
foreach ($tempLibsArr as $datas) {
    $libsIds = $datas['libsIds'];
    $libsTitles = $datas['libsTitles'];
    $libsForum = $datas['libsForum'];
    $check_topic = $connects->prepare("SELECT topicIds, topicTitles, topicDates FROM topics WHERE topicIds = ? AND topicState = ? AND TopicType = 'publisherOnly' ORDER BY topicDates DESC");
    $check_topic->bind_param("ss", $libsForum, $State);
    $check_topic->execute();
    $result_check_topic = $check_topic->get_result();
    if ($result_check_topic->num_rows > 0) {
        $check_forumcount = $connects->prepare("SELECT COUNT(ForumIds) FROM forums WHERE ForumTopics = ? AND ForumState = 'Publics'");
        $check_forumcount->bind_param("s", $libsForum);
        $check_forumcount->execute();
        $result_check_forumcount = $check_forumcount->get_result();
        if ($result_check_forumcount->num_rows > 0) {
            $rownum = $result_check_forumcount->fetch_assoc();
            $rownum = $rownum['COUNT(ForumIds)'];
        } else {
            $rownum = 0;
        }
        while ($value = $result_check_topic->fetch_assoc()) {
            $topicIds = $value['topicIds'];
            $topicTitles = $value['topicTitles'];
            $topicDates = $value['topicDates'];
            if (!in_array($topicIds, $tempTopicArr)) {
                $tempTopicArr[$libsIds] = [
                "topicIds"       => "$topicIds",
                "libsIds"        => "$libsIds",
                "libsTitles"     => "$libsTitles",
                "topicTitles"    => "$topicTitles",
                "topicDates"     => "$topicDates",
                "rownum"         => "$rownum"
                ];
            };
        };
    } else {
        $publishing = false;
    };
}

$NDAPI = "Click on reset button to obtain";
$NPAPI = "Click on reset button to obtain";
$check_api = $connects->prepare("SELECT * FROM api_keys WHERE og_identification = ? ;");
$check_api->bind_param("s", $gids);
$check_api->execute();
$result_check_api = $check_api->get_result();
if ($result_check_api->num_rows > 0) {
    while ($rca_val = $result_check_api->fetch_assoc()) {
        $tempApiToken = $rca_val['apiId'];
        $scope = $rca_val['useScope'];
        $hashedKeys = $rca_val['hashedKeys'];
        $apiState = $rca_val['active'];
        $addedDate = $rca_val['addedDate'];
        if($scope === "Development"){
            $NDAPI = $tempApiToken . "." . $hashedKeys;
        }
        if($scope === "Production"){
            $NPAPI = $tempApiToken . "." . $hashedKeys;
        };
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styling/pallate.css">
    <link rel="stylesheet" href="../styling/Mindex.css">
    <link rel="stylesheet" href="../styling/footer.css">
    <script>
    function setMember(ReqstData) {
        const form = document.forms.REMOVEMEMBER;
        const values = ReqstData.dataset;
        Object.keys(values).forEach((key) => {
            if (form[key]) 
                form[key].value = values[key];
        });
    };
    function updateMember(ReqstData) {
        const form = document.forms.UPDATEMEMBER;
        const values = ReqstData.dataset;
        Object.keys(values).forEach((key) => {
            if (form[key]) 
                form[key].value = values[key];
        });
    };
    </script>
    <title>Groups Management</title>
</head>
<body class="minh100">
<?php 
if (isset($_SESSION['resetPass']) && $_SESSION['resetPass'] == true) {
?>
    <dialog id="firstChangePass" class="posf c0 wh100 flex fld acjc bg-def-1 border-none z999">
        <form class="posr autoMg pad-n w40p flex fld" action="passkeys.php" method="post">
            <input class="hiddeninp" type="text" name="profiletags" value="<?php echo $aidis;?>" hidden>
            <h2 class="pad-ns pad-sb w100p txt-b txtc">PASSKEY CHANGE REQUIRED</h2>
            <h2 class="pad-nb w100p txt-n txtc">please change your account password or it will get locked out</h2>
            <input type="text" name="newpasskeys" class="inptxt" placeholder="Input the your new passkeys" auto-complete="off" tabindex="1" maxlength="1000" required>
            <input class="inptxt" type="text" id="confirm" name="confirm" placeholder="Confirm PassKeys" autocomplete="off" tabindex="2" required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white bora-s" type="submit" name="submit" value="Reset" tabindex="3">
        </form>
    </dialog>
<?php
} else {
?>
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity3 z1">
    <div class="posr w100p flex blurbg border-b z4">
        <div class="posr rightMg w60p flex border-purple-b">
            <div class="posr pad-n flex fld acjc bgc-purple">
                <h2 class="txt-n txtc semibold">HOME</h2>
                <a href="index.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-white">
                <h2 class="txt-n txtc semibold">DASHBOARD</h2>
                <a href="#" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">PUBLISHES</h2>
                <a href="../publishing/manage.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="profile.php?gids=<?php echo $gids;?>" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../documentation/docs.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">OPTIONS</h2>
                <a class="link-cover hover-white" onclick="uniDisplaySwitch('options');">.</a>
            </div>
        </div>
    </div>
    <!-- settings/option -->
    <dialog id="options" class="posr pad-n-s pad-s-v w100p maxh40 dp-none fld bg-half-gray blurbg border-none border-b z15">
        <div class="posr pad-s-s flex gap5">
            <button onclick="uniDisplaySwitch('addMemberDialog');" class="pad-s txtc txt-s c-white bgc-orange border-purple bora-s box-shad-black-1 hover-text-black">Add Members</button>
            <button onclick="uniDisplaySwitch('editPasskeys')" class="pad-s txtc txt-s c-white bgc-orange border-purple bora-s box-shad-black-1 hover-text-black">Change Account Passkeys</button>
            <button onclick="uniDisplaySwitch('apipanel'); uniLoad(this, 'apiForm');" data-apidevtoken="<?php echo $NDAPI;?>" data-apiprodtoken="<?php echo $NPAPI;?>" class="pad-s txtc txt-s c-white bgc-orange border-purple bora-s box-shad-black-1 hover-text-black">API Panel</button>
        </div>
    </dialog>
    <!-- api -->
    <dialog id="apipanel" class="posf c0 w100vh minw40 maxw100 maxh100 dp-none fld bg-half-orange blurbg border-1 bora-s ovh-s z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">API's</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('apipanel');">X</p></div>
        <form id="apiForm" class="posr pad-s-v topMg-s10 w100p flex fld gap5" name="settingForm" action="api_request.php" method="post">
            <div class="posr topMg-s10 sideMg pad-n-s w95p flex">
                <label for="apidevtoken">Development Token</label>
            </div>
            <div class="posr sideMg pad-n-s w95p flex space-between gap10">
                <div class="posr w95p flex">
                    <input type="text" class="posr pad-s w100p c-white bg-half-gray border-1" name="apidevtoken" id="apidevtoken" readonly>
                    <span class="blur-censor">.</span>
                </div>
                <div class="posr pad-m r1-1 flex bg-half-white border-purple box-shad-black-1 bora-s ovh">
                    <img src="../img/copy.svg" alt="" class="posr wh100p containfit points" onclick="copy('apidevtoken');">
                </div>
                <button class="posr pad-m r1-1 flex bg-half-white border-purple box-shad-black-1 bora-s ovh" type="submit" name="request" value="NDT">
                    <img src="../img/reload-circle.svg" alt="" class="posr wh100p containfit points">
                </button>
            </div>
            <div class="posr topMg-s10 sideMg pad-n-s w95p flex">
                <label for="apiprodtoken">Production Token</label>
            </div>
            <div class="posr sideMg pad-n-s w95p flex space-between gap10">
                <div class="posr w95p flex">
                    <input type="text" class="posr pad-s w100p c-white bg-half-gray border-1" name="apiprodtoken" id="apiprodtoken" readonly>
                    <span class="blur-censor">.</span>
                </div>
                <div class="posr pad-m r1-1 flex bg-half-white border-purple box-shad-black-1 bora-s ovh">
                    <img src="../img/copy.svg" alt="" class="posr wh100p containfit points" onclick="copy('apiprodtoken');">
                </div>
                <button class="posr pad-m r1-1 flex bg-half-white border-purple box-shad-black-1 bora-s ovh" type="submit" name="request" value="NPT">
                    <img src="../img/reload-circle.svg" alt="" class="posr wh100p containfit points">
                </button>
            </div>
            <div class="posr topMg-s10 sideMg pad-n-s w95p flex flex-r gap10">
            </div>
        </form>
    </dialog>
    <!-- the groups data -->
    <div class="posr w100p minh100 flex blurbg z4">
        <div class="posr bottomMg-10 sideMg w100vh minw50 maxw100 flex fld ovh-s">
        <!-- Groups profile display -->
            <div class="posr topMg-5 pad-s-v pad-n-s flex bg-half-gray box-shad-black-1 border-purple bora-s">
                <div class="posr vertiMg r1-1 w20p flex z3">
                    <?php
                    if (empty($logo) || $logo === "empty") {
                        ?>
                    <img src="../img/business-outline.svg" class="autoMg r1-1 h80p flex acjc blurbg containfit bg-half-white border-1 bora-s">
                    <?php
                    } else {
                        ?>
                    <img src="img/<?php echo $gids . "/" . $logo;?>" alt="<?php echo $Ognames;?>" class="autoMg r1-1 h80p flex acjc blurbg containfit border-1 bora-s">
                    <?php
                    };
                    ?>
                </div>
                <div class="posr pad-n-v pad-sr w50p max50p h100p flex fld">
                    <h2 class="topMg w100p txt-l"><?php echo $Ognames;?></h2>
                    <div class="rightMg txt-s ovh-s"><?php echo $founded;?></div>
                    <div class="topMg-s5 w100p minh10 txt-s wrap ovh"><?php echo $about;?></div>
                    <?php
                    if ($ChangerRoles === "founder") {
                        ?>
                    <div class="posr vertiMg pad-s-v flex gap5">
                    <?php
                        if ($site != "") {
                        ?>
                        <a href="https://<?php echo $site;?>" class="pad-m-v pad-n-s txt-s txtc c-white bgc-orange box-shad-black-1 border-purple bora-s hover-text-black">Websites</a>
                    <?php
                        }
                        if ($yt != "") {
                        ?>
                        <a href="https://<?php echo $yt;?>" class="pad-m-v pad-n-s txt-s txtc c-white bgc-red box-shad-black-1 border-purple bora-s hover-text-black">Youtube</a>
                    <?php
                        }
                        ?>
                        <button class="pad-m-v pad-n-s txt-s txtc c-white bgc-orange box-shad-black-1 border-purple bora-s hover-text-black" onclick="uniDisplaySwitch('ogEditDiag'); uniLoad(this, 'formOg');" data-ognames="<?php echo $Ognames;?>" data-about="<?php echo $about;?>" data-site="<?php echo $site;?>" data-yt="<?php echo $yt;?>">Edit Profile</button>
                    </div>
                    <?php
                    } else {
                        ?>
                    <div class="posr vertiMg pad-s-v flex gap5">
                    <?php
                        if ($site != "") {
                        ?>
                        <a href="https://<?php echo $site;?>" class="pad-m-v pad-n-s txt-s txtc c-white bgc-orange box-shad-black-1 border-purple bora-s hover-text-black">Websites</a>
                    <?php
                        }
                        if ($yt != "") {
                        ?>
                        <a href="https://<?php echo $yt;?>" class="pad-m-v pad-n-s txt-s txtc c-white bgc-red box-shad-black-1 border-purple bora-s hover-text-black">Youtube</a>
                    <?php
                        }
                        ?>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php
            if ($memberExist == true && $memberCount > 1) {
            ?>
        <!-- members management if founder were present -->
            <h2 class="posr topMg-s10 pad-n-v w100p txtc txt-b"><?php echo $Ognames;?> Members</h2>
            <?php
                foreach ($ProfilesArr as $Profiles => $value) {
                    $Tags = $value['profileTags'];
                    $Names = $value['profileNames'];
                    $roles = $value['roles'];
                    if ($Tags != $aidis) {
                        ?>
            <div class="posr topMg-s10 pad-s-v pad-n-s flex fld bg-half-gray box-shad-black-1 border-purple bora-s gap5">
                <div class="flex gap5">
            <?php
                        if ($ChangerRoles === "founder") {
            ?>
                    <a href="../profile.php?user=<?php echo $Tags;?>" class="posr rightMg txt-n hover-text-orange"><?php echo $Names;?></a>
                    <span>|</span>
                    <p class="vertiMg c-orange hover-text-white points" onclick="uniDisplaySwitch('editMemberAccess'); uniLoad(this, 'editMemberAccessForm');" data-profiletags="<?php echo $Tags;?>" data-profname="<?php echo $Names;?>">Revoke Access</p>
                    <span>|</span>
                    <p class="vertiMg c-orange hover-text-white points" onclick="uniDisplaySwitch('editMemberPasskeys'); uniLoad(this, 'editMemberForm');" data-profiletags="<?php echo $Tags;?>" data-profname="<?php echo $Names;?>">Edit Passkeys</p>
                    <span>|</span>
                    <p class="vertiMg c-orange hover-text-red points" onclick="uniDisplaySwitch('editMemberDialog'); setMember(this);" data-profstags="<?php echo $Tags;?>" data-pname="<?php echo $Names;?>">Remove</p>
            <?php
                        } else {
            ?>
                    <a href="../profile.php?user=<?php echo $Tags;?>" class="txt-n hover-text-orange"><?php echo $Names;?></a>
            <?php
                        }
            ?>
                </div>
                <p class="txt-s"><?php echo $roles;?></p>
            </div>
            <?php
                    };
                };
            };
            ?>
            <div class="topMg-5 sideMg w100p flex wrap gap10">
                <h2 class="w100p txtc txt-b">Collection Community</h2>
                <?php
                if ($publishing == true) {
                    foreach ($tempTopicArr as $id => $value) {
                        $ids = $value['topicIds'];
                        $libsIds = $value['libsIds'];
                        $libsname = $value['libsTitles'];
                        $titles = $value['topicTitles'];
                        $rownum = $value['rownum'];
                        $dates = $value['topicDates'];
                ?>
                        <div class="posr pad-n-s pad-s-v w100p flex fld bg-half-gray box-shad-black-1 border-purple bora-s hover-white z2">
                            <h2 class="pad-m-v w100p txt-b border-b wrap"><?php echo $libsname;?> / <?php echo $titles;?></h2>
                            <div class="pad-s-v w100p flex space-between">
                                <p class="txt-n"><?php echo $rownum;?> Forum</p>
                                <p class="vertiMg txt-s"><?php echo $dates;?></p>
                            </div>
                            </p>
                            <a href="community.php?lIds=<?php echo $libsIds;?>&lc=<?php echo $ids;?>" class="link-cover">.</a>
                        </div>
                <?php
                    };
                } else {
                ?>
                    <h2 class="sideMg txt-s z3">Something's wrong, no Community found</h2>
                <?php
                };
                ?>
            </div>
        </div>
    </div>
    <!-- edit profile -->
    <dialog id="ogEditDiag" class="posf c0 wh100p dp-none fld acjc bg-half-gray blurbg ovh-s z999">
        <div class="posr w100p flexblurbg flex"><h2 class="posr rightMg pad-s txt-b">Edit Public Profile</h2><p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('ogEditDiag')">X</p></div>
        <form id="formOg" class="posr wh100p flexblurbg flex gap10" action="update_prf.php" method="post" enctype="multipart/form-data">
            <div class="posr vertiMg leftMg rightMg-s10 h30p maxh30 r1-1 flex fld acjc gap5">
                <img id="preview" class="posr sideMg wh100p containfit">
                <input class="posa c0 wh100p bg-fifth-gray txtc" type="file" name="logo" accept="image/*" onchange="uniLoadFile(event, 'preview');">
            </div>
            <div class="vertiMg leftMg-s10 rightMg w60p flex fld gap5">
                <div class="sideMg w100p flex fld">
                    <label for="ognames">Group Names</label>
                    <input type="text" name="ognames" class="inptxt" placeholder="" auto-complete="off" maxlength="500">
                </div>
                <div class="sideMg w100p flex fld">
                    <label for="about">About</label>
                    <textarea class="inptxt h10 border-b ovh-s" type="text" id="about" name="about" placeholder="" autocomplete="off" required></textarea>
                </div>
                <div class="sideMg w100p flex fld">
                    <label for="site">Website</label>
                    <input type="text" name="site" class="inptxt" placeholder="" auto-complete="off" maxlength="500">
                </div>
                <div class="sideMg w100p flex fld">
                    <label for="yt">youtube</label>
                    <input type="text" name="yt" class="inptxt" placeholder="" auto-complete="off" maxlength="500">
                </div>
                <div class="sideMg w100p flex fld">
                    <input class="pad-s txtc txt-s bg-gold c-black" type="submit" name="submit" value="Update">
                </div>
            </div>
        </form>
    </dialog>
    <!-- update passkeys -->
    <dialog id="editPasskeys" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-purple blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" action="passkeys.php" method="post">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Change your Passkeys</h2>
            <input class="hiddeninp" type="text" name="profiletags" value="<?php echo $aidis;?>" hidden required>
            <input class="inptxt border-b ovh-s" type="text" id="newpasskeys" name="newpasskeys" autocomplete="off" placeholder="Input new passkeys" required>
            <input class="inptxt border-b ovh-s" type="text" id="confirm" name="confirm" autocomplete="off" placeholder="confirm passkeys" required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Reset">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('editPasskeys')">Cancel</button>
    </dialog>
    <!-- add new member -->
    <dialog id="addMemberDialog" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-purple blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" action="members.php" method="post">
            <h2 class="pad-n-v w100p txt-b txtc">Invite New Member</h2>
            <select name="profiletags" class="inpselect topMg-s10 bottomMg-s5 txtc" required>
                <?php
                $stmt_get_user = $connects->prepare("SELECT profileTags, profileNames FROM profiles WHERE allowInvite = 'active';");
                $stmt_get_user->execute();
                $result_get_user = $stmt_get_user->get_result();
                if ($result_get_user->num_rows > 0) {
                ?>
                <option value="" selected disabled>Select User</option>
                <?php
                    while ($values =  $result_get_user->fetch_assoc()) {
                        $proflsTags = $values['profileTags'];
                        $profsName = $values['profileNames'];
                        echo "<option name='profiletags' value='$proflsTags' required>$profsName</option>";
                    };
                ?>
            </select>
            <select name="roles" class="inpselect bottomMg-s5 txtc" required>
                <option name='' value='' selected disabled>select roles</option>
                <option name='roles' value='administrator' required>Administrator</option>
                <option name='roles' value='developer' required>Developer</option>
            </select>
            <textarea class="inptxt h10 border-b ovh-s" type="text" id="custom_msg" name="custom_msg" placeholder="(optional) give an invitation message" autocomplete="off" tabindex="1"></textarea>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Add">
                <?php
                } else {
                ?>
                <option value="" selected disabled>No User Available</option>
            </select>
                <?php
                }
                ?>
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('addMemberDialog')">Cancel</button>
    </dialog>
    <!-- edit existing member access -->
    <dialog id="editMemberAccess" class="posf pad-b-s pad-bb c0 minw100px w20 dp-none fld bg-half-purple blurbg border-1 bora-s z999">
        <form id="editMemberAccessForm" class="wh100p flex fld" name="UPDATEMEMBER" action="members.php" method="post">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Change Access</h2>
            <input class="hiddeninp" type="text" name="profiletags" hidden required>
            <input class="pad-s-v txt-b txtc c-white bg-transparent border-none" type="text" name="profname" readonly required>
            <select name="roles" class="inpselect topMg-s10 bottomMg-s5 txtc" required>
                <option name='' value='' selected disabled>select roles</option>
                <option name='roles' value='administrator' required>Administrator</option>
                <option name='roles' value='developer' required>Developer</option>
            </select>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Revoke">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('editMemberAccess')">Cancel</button>
    </dialog>
    <!-- edit existing member passkeys -->
    <dialog id="editMemberPasskeys" class="posf pad-b-s pad-bb c0 minw100px w20 dp-none fld bg-half-purple blurbg border-1 bora-s z999">
        <form id="editMemberForm" class="wh100p flex fld" name="UPDATEMEMBER" action="members.php" method="post">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Change Passkeys for</h2>
            <input class="hiddeninp" type="text" name="profiletags" hidden required>
            <input class="pad-s-v txt-b txtc c-white bg-transparent border-none" type="text" name="profname" readonly required>
            <input class="inptxt border-b ovh-s" type="text" id="newpasskeys" name="newpasskeys" autocomplete="off" placeholder="new passkeys" required>
            <input class="inptxt border-b ovh-s" type="text" id="confirm" name="confirm" autocomplete="off" placeholder="confirm passkeys" required>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-green border-1 border-hover-white" type="submit" name="submit" value="Change">
        </form> 
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('editMemberPasskeys')">Cancel</button>
    </dialog>
    <!-- remove member -->
    <dialog id="editMemberDialog" class="posf pad-n c0 pad-b-v minw100px w20 dp-none fld bg-half-purple blurbg border-1 bora-s z999">
        <form class="wh100p flex fld" name="REMOVEMEMBER" action="members.php" method="post">
            <h2 class="w100p txt-n txtc">Confirm to Remove this Member?</h2>
            <input class="pad-s-v bg-transparent txtc txt-b c-white border-none" type="text" name="pname" readonly>
            <input class="hiddeninp" type="text" name="profstags" hidden>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-red border-1 border-hover-white" type="submit" name="submit" value="REMOVE">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 hover-green hover-text-white" onclick="uniDisplaySwitch('editMemberDialog')">Cancel</button>
    </dialog>
<?php 
    include_once '../extra/footers.php';
}
?>
    <!-- messages alerter -->
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script src="../scriptstuff/script.js"></script>
    <script src="../scriptstuff/alert.js"></script>
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
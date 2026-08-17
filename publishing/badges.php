<?php
require_once "../processes/database.php";
if (!isset($_GET['libsids'])) {
    if (!isset($_SESSION['libsids'])) {
        $_SESSION['corsmsg'] = 'Missing the required input';
        header ('location: manage.php');
        exit;
    } else {
        $libsIds = $_SESSION['libsids'];
    };
} else {
    $libsIds = $_GET['libsids'];
}
$allowChanges = false;
$reservedArray = array();
$badgeGroups= array();
$root_route = "../";
require_once "../secureSession.php";
require_once "../Groups/ReAuth.php";
if (isset($_SESSION['resetPass']) && $_SESSION['resetPass'] == true) {
    header ('location: ../Groups/manage.php');
    exit;
}
if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
    $aidis = $_SESSION['profileTags'];
    $gToken = $_SESSION['GroupsToken'];
    $gids = $_SESSION['gids'];
    $ChangerRoles = $_SESSION['roles'];
    if ($ChangerRoles === "founder" || $ChangerRoles === "developer") {
        $allowChanges = true;
    }
    if ($allowChanges == false) {
        $_SESSION['corsmsg'] = "Unpermited access";
        header ('location: manage.php');
        exit;
    }
} else {
    $_SESSION['corsmsg'] = "sign in to access";
    header ('location: ../index.php');
    exit;
}
$check_software = $connects->prepare("SELECT libsPublisher, libsTitles FROM libslist WHERE libsIds = ? AND libsPublisher = ? ;");
$check_software->bind_param("ss", $libsIds, $gids);
$check_software->execute();
$result_check_software = $check_software->get_result();
if ($result_check_software->num_rows > 0) {
    while ($value = $result_check_software->fetch_assoc()) {
        $libsPublisher = $value['libsPublisher'];
        $libsTitles = $value['libsTitles'];
        if ($gids != $libsPublisher) {
            $_SESSION['corsmsg'] = "Unpermited access";
            header ('location: manage.php');
            exit;
        }
        $check_groupRefs = $connects->prepare("SELECT groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, badgeList, icons FROM badgegroup WHERE libsIds = ? AND state = 'publics';");
        $check_groupRefs->bind_param("s", $libsIds);
        $check_groupRefs->execute();
        $result_check_groupRefs = $check_groupRefs->get_result();
        if ($result_check_groupRefs->num_rows > 0) {
            while ($value = $result_check_groupRefs->fetch_assoc()) {
                if (!in_array($value['groupRefs'], $badgeGroups)) {
                    $badgeGroups[$value['groupRefs']] = [
                        "libsIds"           => $value['libsIds'],
                        "badgeGroupTitle"   => $value['badgeGroupTitle'],
                        "badgeGroupDesc"    => $value['badgeGroupDesc'],
                        "badgeList"         => json_decode($value['badgeList'], true),
                        "icons"             => $value['icons']
                    ];
                }
            };
        }
        foreach ($badgeGroups as $bgIndex => $bgValue) {
            $check_badges = $connects->prepare("SELECT badgeIds, badgeName, badgeDesc, badgeType, badgeRefs, icon FROM badges WHERE badgeType = 'achievement' AND badgeRefs = ? LIMIT 50;");
            $check_badges->bind_param("s", $bgIndex);
            $check_badges->execute();
            $result_check_badges = $check_badges->get_result();
            if ($result_check_badges->num_rows > 0) {
                while($value = $result_check_badges->fetch_assoc()){
                    $reservedArray[$value['badgeIds']] = [
                        "Ids"  => $value['badgeIds'],
                        "Name" => $value['badgeName'],
                        "Desc" => $value['badgeDesc'],
                        "Type" => $value['badgeType'],
                        "Refs" => $value['badgeRefs'],
                        "Icon" => $value['icon']
                    ];
                }
            }
        }
    }
} else {
    $_SESSION['corsmsg'] = "Inexistent collection";
    header ('location: manage.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/cgcclogotrsp.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styling/pallate.css">
    <link rel="stylesheet" href="../styling/Mindex.css">
    <link rel="stylesheet" href="../styling/footer.css">
    <script>
        var Selecting = false;
    </script>
    <title>Badges manager</title>
</head>
<body class="minh100">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity1 z1">
    <div class="posr w100p flex blurbg border-purple-b z4">
        <div class="posr rightMg w60p flex border-purple-b">
            <div class="posr pad-n flex fld acjc bgc-purple">
                <h2 class="txt-n txtc semibold">DASHBOARD</h2>
                <a href="../Groups/manage.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">PUBLISHES</h2>
                <a href="../publishing/manage.php" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-white">
                <h2 class="txt-n txtc semibold">BADGES</h2>
                <a href="#" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../documentation/groupspublishing.php" class="link-cover hover-white">.</a>
            </div>
        </div>
    </div>
    <div class="posr w100p h100 flex ovh-s z4">
        <div id="containerthatcontain" class="posr pad-nt w80p h100p flex fld ovh-s gap10 blurbg z4">
            <div class="posr sideMg pad-s-v pad-n-s w95p flex gap10 bg-def-1 box-shad-black-1 border-purple bora-s">
                <button onclick="uniDisplaySwitch('newBadgeGroupDialog')" class="posr pad-m-v pad-s-s txtc bg-def-1 bora-s box-shad-black-1 points hover-text-orange">New Badge Groups</button>
                <button onclick="uniDisplaySwitch('newBadgeDialog')" class="posr pad-m-v pad-s-s txtc bg-def-1 bora-s box-shad-black-1 points hover-text-orange">New Badges</button>
                <button onclick="uniDisplaySwitch('delBG')" class="posr pad-m-v pad-s-s txtc bg-def-1 bora-s box-shad-black-1 points hover-text-orange">Delete Badge Groups</button>
                <button onclick="uniDisplaySwitch('delB')" class="posr pad-m-v pad-s-s txtc bg-def-1 bora-s box-shad-black-1 points hover-text-orange">Delete Badges</button>
            <?php
            if (!empty($reservedArray)) {
            ?>
                <p class="posr leftMg vertiMg txt-n c-lightgray"><?php echo count($badgeGroups);?> badges groups / </p>
                <p class="posr vertiMg txt-n c-lightgray"><?php echo count($reservedArray);?> badges found</p>
            </div>
            <?php
                foreach ($badgeGroups as $bgIndex => $bgValue) {
                    $bgGroupicons = $bgValue['icons'];
                    $bgGroupTitle = $bgValue['badgeGroupTitle'];
                    $bgGroupDesc = $bgValue['badgeGroupDesc'];
                    $bgGroupList = $bgValue['badgeList'];
                    $reservedDir = "../ab/" . $bgIndex . "/";
            ?>
            <div class="posr sideMg w95p flex fld bg-def-1 box-shad-black-1 border-purple bora-s border-hover-white">
                <div class="posr w100p flex ovh">
                    <h2 class="posr pad-nt pad-s-s pad-sb w100p txt-b txtnowrap border-purple-b ovh"><?php echo $bgGroupTitle;?></h2>
                    <div class='posr pad-s-s flex fld acjc hover-white bg-half-gray ovh-s'
                        data-editbadgegrouptitle="<?php echo $bgGroupTitle;?>" data-editbadgegroupdesc="<?php echo $bgGroupDesc;?>" data-badgegroupids="<?php echo $bgIndex;?>"
                        <?php if (!empty($bgGroupicons)) { ?> 
                        onclick="uniDisplaySwitch('editBadgeGroupDialog'); uniLoad(this, 'editBadgeGroupForm'); uniReloadFile(`<?php echo $reservedDir . $bgGroupicons ; ?>`, 'editbadgegrouppreview');"
                        <?php } else { ?> 
                        onclick="uniDisplaySwitch('editBadgeGroupDialog'); uniLoad(this, 'editBadgeGroupForm'); uniReloadFile(``, 'editbadgegrouppreview');"<?php }; ?>>
                        <img src="../img/create.png" alt="" class="posr icon-t r1-1 containfit points z15">
                    </div>
                    <div class='posr pad-s-s flex fld acjc hover-red bg-half-gray ovh-s'
                            <?php 
                            if (!empty($bgGroupicons)) { ?> 
                                onclick="uniDisplaySwitch('delBadgeGroupDialog'); uniLoad(this, 'delBadgeGroupForm'); uniReloadFile(`<?php echo $reservedDir . $bgGroupicons ; ?>`, 'delbadgegrouppreview');"<?php 
                            } else { ?> 
                                onclick="uniDisplaySwitch('delBadgeGroupDialog'); uniLoad(this, 'delBadgeGroupForm'); uniReloadFile(``, 'delbadgegrouppreview');"<?php 
                            }; ?> 
                            data-delbadgegrouptitle="<?php echo $bgGroupTitle;?>" data-delbadgegroupdesc="<?php echo $bgGroupDesc;?>" data-badgegroupids="<?php echo $bgIndex;?>">
                        <img src="../img/trash-outline.svg" alt="" class="posr icon-t r1-1 containfit points z15">
                    </div>
                </div>
            <?php
                    if (isset($bgGroupList)) {
                        foreach ($bgGroupList as $raIds) {
                            $reservedName = $reservedArray[$raIds]['Name'];
                            $reservedDesc = $reservedArray[$raIds]['Desc'];
                            $reservedType = $reservedArray[$raIds]['Type'];
                            $reservedIcon = $reservedArray[$raIds]['Icon'];
                            $reservedRefs = $reservedArray[$raIds]['Refs'];
                            $reservedDir = "../ab/" . $reservedRefs . "/";
                ?>
                    <div class='posr w100p flex ovh-s'>
                        <div class='posr pad-s-v pad-n-s w100p flex gap10 ovh-s'>
                            <div class="posr r1-1 h10 flex">
                            <?php
                                if (empty($reservedIcon) || $reservedIcon === "empty") {
                            ?>
                                <img src="../img/cgcclogo.png" class="autoMg r1-1 h10 flex acjc bgc-purple containfit bora-s z4">
                            <?php
                                } else {
                            ?>
                                <img src="<?php echo $reservedDir . $reservedIcon;?>" alt="<?php echo $reservedName;?>" class="autoMg r1-1 h10 flex acjc containfit z4">
                            <?php
                                };
                            ?>
                            </div>
                            <div class="vertiMg flex fld">
                                <h2 class="posr txt-b"><?php echo $reservedName;?></h2>
                                <p class="posr txt-s c-lightgray hover-text-white"><?php echo $reservedDesc;?></p>
                            </div>
                        </div>
                        <div class='posr pad-s-s flex fld acjc hover-white bg-half-gray ovh-s'
                            <?php if (!empty($reservedIcon)) { ?>
                                onclick="uniDisplaySwitch('editBadgeDialog'); uniLoad(this, 'editBadgeForm'); uniReloadFile(`<?php echo $reservedDir . $reservedIcon ; ?>`, 'badgepreview');"<?php 
                            } else { ?>
                                onclick="uniDisplaySwitch('editBadgeDialog'); uniLoad(this, 'editBadgeForm'); uniReloadFile(``, 'badgepreview');"<?php 
                            }; ?>
                            data-badgesids="<?php echo $raIds;?>" data-editbadgetitle="<?php echo $reservedName;?>" data-editbadgedesc="<?php echo $reservedDesc;?>" data-editbadgegroup="<?php echo $reservedRefs;?>">
                            <img src="../img/create.png" alt="" class="posr icon-t r1-1 containfit points z15">
                        </div>
                        <div class='posr pad-s-s flex fld acjc hover-red bg-half-gray ovh-s'
                            <?php if (!empty($reservedIcon)) { ?> 
                                onclick="uniDisplaySwitch('deleteBadgeDialog'); uniLoad(this, 'deleteBadgeForm'); uniReloadFile(`<?php echo $reservedDir . $reservedIcon ; ?>`, 'deletebadgepreview');"<?php 
                            } else { ?> 
                                onclick="uniDisplaySwitch('deleteBadgeDialog'); uniLoad(this, 'deleteBadgeForm'); uniReloadFile(``, 'deletebadgepreview');"<?php 
                            }; ?> 
                            data-badgesids="<?php echo $raIds;?>" data-deletebadgetitle="<?php echo $reservedName;?>" data-deletebadgedesc="<?php echo $reservedDesc;?>" data-deletebadgegroup="<?php echo $reservedRefs;?>">
                            <img src="../img/trash-outline.svg" alt="" class="posr icon-t r1-1 containfit points z15">
                        </div>
                    </div>
                <?php
                        }
                    }
            ?>
            </div>
            <?php
                }
            } else {
            ?>
            </div>
            <?php
            }
            ?>
        </div>
        <div class="posr pad-n w30p minh80 h100p blurbg border-purple-l flex fld gap10 ovh-s">
            <h2 class="posr w100p txt-b semibold"><?php echo $libsTitles;?></h2>
            <div id="delBG" class="posr w100p dp-none fld">
                <label for="selectToDel">Select badge groups to delete</label>
                <?php
                if (isset($badgeGroups)) {
                ?>
                <select name="selectToDel" class="inpselect bg-transparent c-white border-purple bora-none" required>
                    <option name="" value="" selected disabled>Select ones</option>
                    <?php
                    foreach ($badgeGroups as $bgIndex => $bgValue) {
                        $bgGroupicons = $bgValue['icons'];
                        $bgGroupTitle = $bgValue['badgeGroupTitle'];
                        $bgGroupDesc = $bgValue['badgeGroupDesc'];
                        $bgGroupList = $bgValue['badgeList'];
                        $reservedDir = "../ab/" . $bgIndex . "/";
                    ?>
                        <option 
                        <?php 
                        if (!empty($bgGroupicons)) { ?> 
                            onclick="uniDisplaySwitch('delBadgeGroupDialog'); uniLoad(this, 'delBadgeGroupForm'); uniReloadFile(`<?php echo $reservedDir . $bgGroupicons ; ?>`, 'delbadgegrouppreview');"<?php 
                        } else { ?> 
                            onclick="uniDisplaySwitch('delBadgeGroupDialog'); uniLoad(this, 'delBadgeGroupForm');"<?php 
                        }; ?> 
                        data-delbadgegrouptitle="<?php echo $bgGroupTitle;?>" data-delbadgegroupdesc="<?php echo $bgGroupDesc;?>" data-badgegroupids="<?php echo $bgIndex;?>"
                        name='selectToDel'><?php echo "$bgGroupTitle - $bgIndex";?></option>;
                    <?php
                    };
                    ?>
                </select>
                <?php
                } else {
                ?>
                <select name="" class="inpselect" required>
                    <option name="" value="" selected disabled>No badges groups found</option>
                </select>
                <?php
                }
                ?>
            </div>
            <div id="delB" class="posr w100p dp-none fld">
                <label for="selectToDel">Select badges to delete</label>
                <?php
                if (!empty($reservedArray)) {
                ?>
                <select name="selectToDel" class="inpselect bg-transparent c-white border-purple bora-none" required>
                    <option name="" value="" selected disabled>Select ones</option>
                <?php
                    foreach ($reservedArray as $rsvIndex => $rsvValue) {
                        $reservedName = $rsvValue['Name'];
                        $reservedDesc = $rsvValue['Desc'];
                        $reservedType = $rsvValue['Type'];
                        $reservedIcon = $rsvValue['Icon'];
                        $reservedRefs = $rsvValue['Refs'];
                        $reservedDir = "../ab/" . $reservedRefs . "/";
                ?>
                        <option 
                        <?php
                        if (!empty($reservedIcon)) { ?> 
                            onclick="uniDisplaySwitch('deleteBadgeDialog'); uniLoad(this, 'deleteBadgeForm'); uniReloadFile(`<?php echo $reservedDir . $reservedIcon ; ?>`, 'deletebadgepreview');"<?php 
                        } else { ?> 
                            onclick="uniDisplaySwitch('deleteBadgeDialog'); uniLoad(this, 'deleteBadgeForm');"<?php 
                        }; ?> 
                        data-badgesids="<?php echo $rsvIndex;?>" data-deletebadgetitle="<?php echo $reservedName;?>" data-deletebadgedesc="<?php echo $reservedDesc;?>" data-deletebadgegroup="<?php echo $reservedRefs;?>"  name='selectToDel'><?php echo "$reservedName - $rsvIndex";?></option>;
                <?php
                    }
                ?>
                </select>
                <?php
                } else {
                ?>
                <select name="" class="inpselect" required>
                    <option name="" value="" selected disabled>No badges groups found</option>
                </select>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <!-- new badge group dialog -->
    <dialog id="newBadgeGroupDialog" class="posf c0 w100vh minw50 maxw100 dp-none fld bg-def-1 blurbg border-purple z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">New Badge Groups</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('newBadgeGroupDialog');">X</p></div>
        <form class="posr pad-n-v wh100p flex" id="newBadgeGroupForm" action="badges_proceed.php" method="post" enctype="multipart/form-data">
            <div class="posr pad-n-s minh30 maxh50 r1-1 flex fld acjc">
                <img id="badgegrouppreview" class="posr sideMg wh100p bg-half-gray containfit">
                <input class="posa c0 pad-n-s w100p txtc" type="file" name="badgesgroupimg" accept="image/*" onchange="uniLoadFile(event, 'badgegrouppreview');">
            </div>
            <div class="posr pad-s-s w100p flex fld acjc gap10">
                <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
                <input class="hiddeninp" type="text" name="request" value="NewBadgeGroups" hidden required>
                <div class="posr w100p flex fld">
                    <label for="badgegrouptitle">Badge group title</label>
                    <input type="text" name="badgegrouptitle" class="inptxt" placeholder="name of the badges" auto-complete="off" required>
                </div>
                <div class="posr w100p flex fld">
                    <label for="badgegroupdesc">Badge group description</label>
                    <input type="text" name="badgegroupdesc" class="inptxt" placeholder="can be empty" auto-complete="off">
                </div>
                <input class="posr pad-s-v w100p txt-n txtc bg-def-1 border-1 border-hover-white hover-text-orange" type="submit" name="submit" value="Create">
            </div>
        </form>
    </dialog>
    <!-- edit badgegroup dialog -->
    <dialog id="editBadgeGroupDialog" class="posf c0 w100vh minw50 maxw100 dp-none fld bg-def-1 blurbg border-purple z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">Edit Badge Groups</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('editBadgeGroupDialog');">X</p></div>
        <form class="posr pad-n-v wh100p flex" id="editBadgeGroupForm" action="badges_proceed.php" method="post" enctype="multipart/form-data">
            <div class="posr pad-n-s minh30 maxh50 r1-1 flex fld acjc">
                <img id="editbadgegrouppreview" class="posr sideMg wh100p bg-half-gray containfit">
                <input class="posa c0 pad-n-s w100p txtc" type="file" name="editBadgeGroupImg" accept="image/*" onchange="uniLoadFile(event, 'editbadgegrouppreview');">
            </div>
            <div class="posr pad-s-s w100p flex fld acjc gap10">
                <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
                <input class="hiddeninp" type="text" name="badgegroupids" hidden required>
                <input class="hiddeninp" type="text" name="request" value="EditBadgeGroups" hidden required>
                <div class="posr w100p flex fld">
                    <label for="editbadgegrouptitle">Badge group title</label>
                    <input type="text" name="editbadgegrouptitle" class="inptxt" placeholder="name of the badges" auto-complete="off" required>
                </div>
                <div class="posr w100p flex fld">
                    <label for="editbadgegroupdesc">Badge group description</label>
                    <input type="text" name="editbadgegroupdesc" class="inptxt" placeholder="can be empty" auto-complete="off">
                </div>
                <input class="posr pad-s-v w100p txt-n txtc bg-def-1 border-1 border-hover-white hover-text-orange" type="submit" name="submit" value="SAVE">
            </div>
        </form>
    </dialog>
    <!-- create dialog -->
    <dialog id="newBadgeDialog" class="posf c0 w100vh minw50 maxw100 dp-none fld bg-def-1 blurbg border-purple z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">Create New Badges</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('newBadgeDialog');">X</p></div>
        <form class="posr pad-n-v wh100p flex" id="newBadgesForm" action="badges_proceed.php" method="post" enctype="multipart/form-data">
            <div class="posr pad-n-s minh30 maxh50 r1-1 flex fld acjc">
                <img id="preview" class="posr sideMg wh100p bg-half-gray containfit">
                <input class="posa c0 pad-n-s w100p txtc" type="file" name="badgesImage" accept="image/*" onchange="uniLoadFile(event, 'preview');">
            </div>
            <div class="posr pad-s-s w100p flex fld acjc gap10">
                <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
                <input class="hiddeninp" type="text" name="request" value="NewBadges" hidden required>
                <div class="posr w100p flex fld">
                    <label for="badgetitle">Badge title</label>
                    <input type="text" name="badgetitle" class="inptxt" placeholder="name of the badges" auto-complete="off" required>
                </div>
                <div class="posr w100p flex fld">
                    <label for="badgedesc">Badge description</label>
                    <input type="text" name="badgedesc" class="inptxt" placeholder="can be empty" auto-complete="off">
                </div>
                <div class="posr w100p flex fld">
                    <label for="badgeGroup">Badge groups</label>
                    <?php
                    if (isset($badgeGroups)) {
                    ?>
                    <select name="badgeGroup" class="inpselect" required>
                        <option name="" value="" selected disabled>Select badge groups</option>
                        <?php
                        foreach ($badgeGroups as $bgIndex => $bgValue) {
                            $ids = $bgIndex;
                            $bgGroupTitle = $bgValue['badgeGroupTitle'];
                            echo "<option name='badgeGroup' value='$ids' required>$bgGroupTitle - $ids</option>";
                        };
                        ?>
                    </select>
                    <?php
                    } else {
                    ?>
                    <select name="" class="inpselect" required>
                        <option name="" value="" selected disabled>No Badge Group found, please create one before making new badges</option>
                    </select>
                    <?php
                    }
                    ?>
                </div>
                <input class="posr pad-s-v w100p txt-n txtc bg-green border-1 hover-text-black" type="submit" name="submit" value="Create">
            </div>
        </form>
    </dialog>
    <!-- edit badges dialog -->
    <dialog id="editBadgeDialog" class="posf c0 w100vh minw50 maxw100 dp-none fld bg-def-1 blurbg border-purple z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">Edit Badges</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('editBadgeDialog');">X</p></div>
        <form class="posr pad-n-v wh100p flex" id="editBadgeForm" action="badges_proceed.php" method="post" enctype="multipart/form-data">
            <div class="posr pad-n-s minh30 maxh50 r1-1 flex fld acjc">
                <img id="badgepreview" class="posr sideMg wh100p bg-half-gray containfit">
                <input class="posa c0 pad-n-s wh100p txtc" type="file" name="editBadgesImage" accept="image/*" onchange="uniLoadFile(event, 'badgepreview');">
            </div>
            <div class="posr pad-s-s w100p flex fld acjc gap10">
                <input class="hiddeninp" type="text" name="badgesids" hidden required>
                <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
                <input class="hiddeninp" type="text" name="request" value="EditBadges" hidden required>
                <div class="posr w100p flex fld">
                    <label for="editbadgetitle">Badge title</label>
                    <input type="text" name="editbadgetitle" class="inptxt" placeholder="name of the badges" auto-complete="off" required>
                </div>
                <div class="posr w100p flex fld">
                    <label for="editbadgedesc">Badge description</label>
                    <input type="text" name="editbadgedesc" class="inptxt" placeholder="can be empty" auto-complete="off">
                </div>
                <div class="posr w100p flex fld">
                    <label for="editbadgegroup">Badge groups</label>
                    <?php
                    if (isset($badgeGroups)) {
                    ?>
                    <select name="editbadgegroup" class="inpselect" disabled readonly>
                        <?php
                        foreach ($badgeGroups as $bgIndex => $bgValue) {
                            $ids = $bgIndex;
                            $bgGroupTitle = $bgValue['badgeGroupTitle'];
                            echo "<option name='editbadgegroup' value='$ids' disabled readonly>$bgGroupTitle - $ids</option>";
                        };
                        ?>
                    </select>
                    <?php
                    } else {
                    ?>
                    <select name="" class="inpselect" disabled readonly>
                        <option name="" value="" selected disabled>No Badge Group found</option>
                    </select>
                    <?php
                    }
                    ?>
                </div>
                <input class="posr pad-s-v w100p txt-n txtc bg-green border-1 hover-text-black" type="submit" name="submit" value="Save Changes">
            </div>
        </form>
    </dialog>
    <!-- remove badges dialog -->
    <dialog id="deleteBadgeDialog" class="posf c0 w100vh minw50 maxw100 dp-none fld bg-def-1 blurbg border-purple z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">Confirm to delete Badges?</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('deleteBadgeDialog');">X</p></div>
        <form class="posr pad-n-v wh100p flex" id="deleteBadgeForm" action="badges_proceed.php" method="post" enctype="multipart/form-data">
            <div class="posr pad-n-s minh30 maxh50 r1-1 flex fld acjc">
                <img id="deletebadgepreview" class="posr sideMg wh100p bg-half-gray containfit">
            </div>
            <div class="posr pad-s-s w100p flex fld acjc gap10">
                <input class="hiddepinp" type="text" name="badgesids" hidden required>
                <input class="hiddepinp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
                <input class="hiddepinp" type="text" name="request" value="DeleteBadges" hidden required>
                <input class="hiddepinp" type="text" name="deletebadgegroup" hidden required>
                <div class="posr w100p flex fld">
                    <label for="deletebadgetitle">Badge title</label>
                    <input type="text" name="deletebadgetitle" class="inptxt" placeholder="" auto-complete="off" readonly>
                </div>
                <div class="posr w100p flex fld">
                    <label for="deletebadgedesc">Badge description</label>
                    <input type="text" name="deletebadgedesc" class="inptxt" placeholder="" auto-complete="off" readonly>
                </div>
                <input class="posr pad-s-v w100p txt-n txtc bg-def-2 border-1 border-hover-red hover-red" type="submit" name="submit" value="Delete">
            </div>
        </form>
    </dialog>
    <!-- edit badgegroup dialog -->
    <dialog id="delBadgeGroupDialog" class="posf c0 w100vh minw50 maxw100 dp-none fld bg-def-1 blurbg border-purple z999">
        <div class="posr wh100p flex"><h2 class="rightMg pad-s txt-b">Confirm to delete Badges Group?</h2><p class="pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('delBadgeGroupDialog');">X</p></div>
        <form class="posr pad-n-v wh100p flex" id="delBadgeGroupForm" action="badges_proceed.php" method="post" enctype="multipart/form-data">
            <div class="posr pad-n-s minh30 maxh50 r1-1 flex fld acjc">
                <img id="delbadgegrouppreview" class="posr sideMg wh100p bg-half-gray containfit">
            </div>
            <div class="posr pad-s-s w100p flex fld acjc gap10">
                <input class="hiddeninp" type="text" name="libsids" value="<?php echo $libsIds;?>" hidden required>
                <input class="hiddeninp" type="text" name="badgegroupids" hidden required>
                <input class="hiddeninp" type="text" name="request" value="DeleteBadgeGroups" hidden required>
                <div class="posr w100p flex fld">
                    <label for="delbadgegrouptitle">Badge group title</label>
                    <input type="text" name="delbadgegrouptitle" class="inptxt" placeholder="" auto-complete="off" required>
                </div>
                <div class="posr w100p flex fld">
                    <label for="delbadgegroupdesc">Badge group description</label>
                    <input type="text" name="delbadgegroupdesc" class="inptxt" placeholder="" auto-complete="off">
                </div>
                <p class="posr topMg-s10 w100p txtc txt-s c-red">
                    DELETING THIS BADGES GROUP WILL ALSO MAKES EVERY BADGES LISTED EFFECTIVELY REMOVED
                </p>
                <input class="posr pad-s-v w100p txt-n txtc bg-def-2 border-1 border-hover-white hover-red" type="submit" name="submit" value="Remove">
            </div>
        </form>
    </dialog>
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script src="../scriptstuff/script.js"></script>
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
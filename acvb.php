<?php
require_once 'processes/database.php';
if (isset($_SESSION['prev_loc'])) {
    $prev_loc = $_SESSION['prev_loc'];
} else {
    $prev_loc = "index.php";
}
if (!isset($_GET['type']) && !isset($_GET['filter']) && !isset($_GET['ref']) && !isset($_GET['target'])) {
    $_SESSION['corsmsg'] = "Invalid request";
    header ('location: indx.php');
    exit;
}
$errors = array();
$reservedArray = array();
$tempGroupRefs= array();
$badgeGroups= array();
$type = $_GET['type'];
$filter = $_GET['filter'];
$reference = $_GET['ref'];
$target = $_GET['target'];
$pages = 1;
if (isset($_GET['page']) && $_GET['page'] > 0) {
    $pages = $_GET['page'];
}
$currentLink = "acvb.php?type=".$type."&filter=".$filter."&ref=".$reference."&target=".$target;
if ($prev_loc != $currentLink) {
    $_SESSION['prev_loc'] = $currentLink;
}
$totalDisplayed = 0;
$currentOffset = 0;
$currentIndex = 1;
$displayLimit = 10;
$totalCount = 0;
if ($pages > 1 && $currentIndex == $displayLimit) {
    $currentOffset = $pages - 1 * $displayLimit;
}

if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
} else {
    $root_route = "";
    require_once 'secureSession.php';
};
if ($type === "user") {
    $check_profile = $connects->prepare("SELECT profileTags, profileAttachs, profileNames, Badge, mkot FROM profiles WHERE profileTags = ? ;");
    $check_profile->bind_param("s", $reference);
    $check_profile->execute();
    $result_check_profile = $check_profile->get_result();
    if ($result_check_profile->num_rows == 1) {
        $value = $result_check_profile->fetch_assoc();
        $Tags = $value['profileTags'];
        $pfAttachs = $value['profileAttachs'];
        $reservedUsername = $value['profileNames'];
        
        $mkot = $value['mkot'];
        $badgeArr = $value['Badge'];
        $badgeArr = json_decode($badgeArr, true);
        $data = json_decode($mkot, true);
        $markedData = $data['marked'];
        $privated = $data['private'];
        if ($privated == true && $reference != $aidis) {
            $_SESSION['corsmsg'] = "user profile are privated";
            header ('location: ' . $prev_loc);
            exit;
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
            $check_badges = $connects->prepare("SELECT * FROM badges WHERE badgeIds = ? ;");
            $check_badges->bind_param("s", $badgeIndex);
            $check_badges->execute();
            $result_check_badges = $check_badges->get_result();
            if ($result_check_badges->num_rows > 0) {
                $value = $result_check_badges->fetch_assoc();
                if ($value['badgeType'] === "profile") {
                    $haveBadge = true;
                    $reservedArray[$value['badgeIds']] = [
                        "Ids"  => $value['badgeIds'],
                        "Name" => $value['badgeName'],
                        "Desc" => $value['badgeDesc'],
                        "Type" => $value['badgeType'],
                        "Refs" => $value['badgeRefs'],
                        "Icon" => $value['icon'],
                        "Date" => $badgeValue
                    ];
                }
                if ($value['badgeType'] === "achievement") {
                    $ownedAchievement = true;
                    $reservedArray[$value['badgeIds']] = [
                        "Ids"  => $value['badgeIds'],
                        "Name" => $value['badgeName'],
                        "Desc" => $value['badgeDesc'],
                        "Type" => $value['badgeType'],
                        "Refs" => $value['badgeRefs'],
                        "Icon" => $value['icon'],
                        "Date" => $badgeValue
                    ];
                }
                if (!in_array($value['badgeRefs'], $tempGroupRefs)) {
                    $tempGroupRefs[$value['badgeRefs']] = [
                        "badgeRefs" => $value['badgeRefs']
                    ];
                }
                $currentIndex++;
                $totalCount++;
            }
        }
        foreach ($tempGroupRefs as $tgrIndex => $tgrValue) {
            $check_groupRefs = $connects->prepare("SELECT 
            groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, badgeList, icons
            FROM badgegroup WHERE groupRefs = ? AND state = 'publics'");
            $check_groupRefs->bind_param("s", $tgrIndex);
            $check_groupRefs->execute();
            $result_check_groupRefs = $check_groupRefs->get_result();
            if ($result_check_groupRefs->num_rows > 0) {
                while ($value = $result_check_groupRefs->fetch_assoc()) {
                    $tempBadgeList = json_decode($value['badgeList'], true);
                    foreach ($reservedArray as $tgrbadgeListIndex => $tgrbadgeListVal) {
                        if (in_array($tgrbadgeListVal['Ids'], $tempBadgeList)) {
                            $badgeList = [$tgrbadgeListVal['Ids']];
                        }
                    }
                    $badgeGroups[$value['groupRefs']] = [
                        "libsIds"           => $value['libsIds'],
                        "badgeGroupTitle"   => $value['badgeGroupTitle'],
                        "badgeGroupDesc"    => $value['badgeGroupDesc'],
                        "badgeList"         => $badgeList,
                        "icons"             => $value['icons']
                    ];
                }
            }
        }
    } else {
        $_SESSION['corsmsg'] = "user account does not exists or on a temporary bans";
        header ('location:' . $prev_loc);
        exit;
    };
} else if ($type === "clts") {
    if ($filter === "one") {
        $totalCount = 1;
    }
    $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, libsTitles FROM libslist WHERE libsState = 'publics' AND libsIds = ? ;");
    $check_software->bind_param("s", $reference);
    $check_software->execute();
    $result_check_software = $check_software->get_result();
    if ($result_check_software->num_rows > 0) {
        while ($value = $result_check_software->fetch_assoc()) {
            $libsIds = $value['libsIds'];
            $libsPublisher = $value['libsPublisher'];
            $libsAttachs = $value['libsAttachs'];
            $libsTitles = $value['libsTitles'];
        };
        $check_groupRefs = $connects->prepare("SELECT groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, badgeList, icons FROM badgegroup WHERE libsIds = ? AND state = 'publics'");
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
            $check_badges = $connects->prepare("SELECT badges.badgeIds, badges.badgeName, badges.badgeDesc, badges.badgeType, badges.badgeRefs, badges.icon
                FROM badges WHERE badgeType = 'achievement' AND badgeRefs = ? LIMIT 50;");
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
                    $totalCount++;
                    $currentIndex++;
                }
            }
        }
    } else {
        $_SESSION['corsmsg'] = "Collection data cannot be found";
        header ('location:' . $prev_loc);
        exit;
    };
} else if ($type === "badges") {
    if ($filter === "one") {
        $totalCount = 1;
    }
    $check_groupRefs = $connects->prepare("SELECT groupRefs, libsIds, badgeGroupTitle, badgeGroupDesc, badgeList, icons FROM badgegroup WHERE groupRefs = ? AND state = 'publics';");
    $check_groupRefs->bind_param("s", $target);
    $check_groupRefs->execute();
    $result_check_groupRefs = $check_groupRefs->get_result();
    if ($result_check_groupRefs->num_rows > 0) {
        $value = $result_check_groupRefs->fetch_assoc();
        $libsIds = $value['libsIds'];
        $badgeGroups[$value['groupRefs']] = [
            "libsIds"           => $value['libsIds'],
            "badgeGroupTitle"   => $value['badgeGroupTitle'],
            "badgeGroupDesc"    => $value['badgeGroupDesc'],
            "badgeList"         => json_decode($value['badgeList'], true),
            "icons"             => $value['icons']
        ];
    }
    $check_badges = $connects->prepare("SELECT badges.badgeIds, badges.badgeName, badges.badgeDesc, badges.badgeType, badges.badgeRefs, badges.icon
        FROM badges WHERE badgeType = 'achievement' AND badgeRefs = ? LIMIT 50;");
    $check_badges->bind_param("s", $target);
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
            $totalCount++;
            $currentIndex++;
        }
    }
    $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsAttachs, libsTitles FROM libslist WHERE libsState = 'publics' AND libsIds = ? ;");
    $check_software->bind_param("s", $libsIds);
    $check_software->execute();
    $result_check_software = $check_software->get_result();
    if ($result_check_software->num_rows > 0) {
        while ($value = $result_check_software->fetch_assoc()) {
            $libsIds = $value['libsIds'];
            $libsPublisher = $value['libsPublisher'];
            $libsAttachs = $value['libsAttachs'];
            $libsTitles = $value['libsTitles'];
        };
    } else {
        $_SESSION['corsmsg'] = "Collection data cannot be found";
        header ('location:' . $prev_loc);
        exit;
    };
};
if (isset($reservedArray)) {
    $totalDisplayed = count($reservedArray);
}
if ($filter === "one" && !$reservedArray[$target]) {
    $_SESSION['corsmsg'] = "Invalid target";
    header ('location: ' . $prev_loc);
    exit;
}
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
    <title>Badges / CGCC</title>
</head>
<body class="minh100 ovh-s z1" id="intro">
    <img src="img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity05 z1">
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
            <?php
            };
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
        <p class="posr leftMg vertiMg pad-n-s pad-s-v txtc txt-n bgc-purple border-1 bora-s border-hover-white">LOGIN
            <a href="connect_it/connect_it.php?state=login" class="link-cover">.</a>
        </p>
        <?php
        };
        ?>
    </div>

    <div class="posr pad-n-v pad-b-s w100vh minw50 maxw100 txt-b bgc-gray flex gap10 box-shad-white-1 border-purple bora-s ovh-s z4">
        <?php
        if ($type === "user") {
        ?>
        <div class="posr r1-1 h10 flex">
            <?php
            if (empty($pfAttachs) || $pfAttachs === "empty") {
            ?>
            <img src="img/person.svg" class="autoMg r1-1 h10 flex acjc border-1 containfit bora-s z4">
            <?php
            } else {
            ?>
            <img src="zprpic/<?php echo $Tags . "/" . $pfAttachs;?>" alt="<?php echo $reservedUsername;?>" class="autoMg r1-1 h10 flex acjc border-1 containfit z4">
            <?php
            };
            ?>
        </div>
        <h2 class='posr vertiMg leftMg-s10 txt-b txtnowrap ovh'>
            <a href="profile.php?user=<?php echo $reference;?>" class="posr txt-b bold txtnowrap ovh hover-text-blue"><?php echo $reservedUsername;?></a>
            >
            <a href="acvb.php?type=<?php echo $type;?>&filter=all&ref=<?php echo $reference;?>&target=<?php echo $reference;?>" class="posr txt-n bold txtnowrap ovh hover-text-blue">Badges</a>
            <?php
            if ($filter === "one") {
                $reservedName = $reservedArray[$target]['Name'];
            ?>
            >
            <a href="acvb.php?type=<?php echo $type;?>&filter=one&ref=<?php echo $reference;?>&target=<?php echo $target;?>" class="posr txt-n bold txtnowrap ovh hover-text-blue"><?php echo $reservedName;?></a>
            <?php
            };
            ?>
        </h2>
        <?php
        } else if ($type === "clts") {
        ?>
        <div class="posr r1-1 h10 flex">
            <?php
            if (empty($libsAttachs) || $libsAttachs === "empty") {
            ?>
            <img src="img/business-outline.svg" class="autoMg r1-1 h10 flex acjc border-1 containfit bora-s z4">
            <?php
            } else {
            ?>
            <img src="Library/libsImg/<?php echo $libsPublisher . "/" . $libsAttachs;?>" alt="<?php echo $libsTitles;?>" class="autoMg r1-1 h10 flex acjc border-1 containfit z4">
            <?php
            };
            ?>
        </div>
        <h2 class='posr vertiMg leftMg-s10 txt-b txtnowrap ovh'>
            <a href="Library/core/view.php?type=clts&ids=<?php echo $libsIds;?>" class="posr txt-b bold txtnowrap ovh hover-text-blue"><?php echo $libsTitles;?></a>
            >
            <a href="acvb.php?type=<?php echo $type;?>&filter=all&ref=<?php echo $reference;?>&target=<?php echo $reference;?>" class="posr txt-n bold txtnowrap ovh hover-text-blue">Badges</a>
            <?php
            if ($filter === "one") {
                $reservedName = $reservedArray[$target]['Name'];
            ?>
            >
            <a href="acvb.php?type=<?php echo $type;?>&filter=one&ref=<?php echo $reference;?>&target=<?php echo $target;?>" class="posr txt-n bold txtnowrap ovh hover-text-blue"><?php echo $reservedName;?></a>
            <?php
            };
            ?>
        </h2>
        <?php
        } else if ($type === "badges") {
        ?>
        <div class="posr r1-1 h10 flex">
            <?php
            if (empty($libsAttachs) || $libsAttachs === "empty") {
            ?>
            <img src="img/business-outline.svg" class="autoMg r1-1 h10 flex acjc border-1 containfit bora-s z4">
            <?php
            } else {
            ?>
            <img src="Library/libsImg/<?php echo $libsPublisher . "/" . $libsAttachs;?>" alt="<?php echo $libsTitles;?>" class="autoMg r1-1 h10 flex acjc border-1 containfit z4">
            <?php
            };
            ?>
        </div>
        <h2 class='posr vertiMg leftMg-s10 txt-b txtnowrap ovh'>
            <a href="Library/core/view.php?libsIds=<?php echo $libsIds;?>" class="posr txt-b bold txtnowrap ovh hover-text-blue"><?php echo $libsTitles;?></a>
            >
            <a href="acvb.php?type=clts&filter=all&ref=<?php echo $reference;?>&target=<?php echo $reference;?>" class="posr txt-n bold txtnowrap ovh hover-text-blue">Badges</a>
            >
            <a href="acvb.php?type=badges&filter=all&ref=<?php echo $reference;?>&target=<?php echo $target;?>" class="posr txt-n bold txtnowrap ovh hover-text-blue"><?php echo $badgeGroups[$target]['badgeGroupTitle'];?></a>
            <?php
            if ($filter === "one") {
                $reservedName = $reservedArray[$target]['Name'];
            ?>
            >
            <a href="acvb.php?type=<?php echo $type;?>&filter=one&ref=<?php echo $reference;?>&target=<?php echo $target;?>" class="posr txt-n bold txtnowrap ovh hover-text-blue"><?php echo $reservedName;?></a>
            <?php
            };
            ?>
        </h2>
        <?php
        }
        ?>
    </div>
    <div id="containerthatcontain" class="posr pad-n-v w100vh minw50 maxw100 minh100 flex fld ovh-s gap10 blurbg z4">
        <?php
        if ($filter === "one") {
            $reservedName = $reservedArray[$target]['Name'];
            $reservedDesc = $reservedArray[$target]['Desc'];
            $reservedType = $reservedArray[$target]['Type'];
            $reservedIcon = $reservedArray[$target]['Icon'];
            $reservedRefs = $reservedArray[$target]['Refs'];
            $badgeGroupsTitle = $badgeGroups[$reservedRefs]['badgeGroupTitle'];
            if ($type === "user") {
                $reservedDate = $reservedArray[$target]['Date'];
            }
            $reservedDir = "ab/" . $reservedRefs . "/";
        ?>
        <div class="posr sideMg w95p flex fld bg-def-1 box-shad-black-1 border-purple bora-s hover-border-white">
            <h2 class='posr pad-nt pad-s-s pad-sb w100p txt-b txtnowrap ovh'><?php echo $badgeGroupsTitle;?></h2>
            <div class='posr pad-s-v pad-n-s w100p flex gap10 border-purple-t ovh-s'>
                <div class="posr r1-1 h10 flex">
                <?php
                if (empty($reservedIcon) || $reservedIcon === "empty") {
                    if ($type != "user") {
                ?>
                    <img src="Library/libsImg/<?php echo $libsPublisher . "/" . $libsAttachs;?>" class="autoMg r1-1 h10 flex acjc blurbg containfit bora-s z4">
                <?php
                    } else {
                ?>
                        <img src="img/cgcclogo.png" class="autoMg r1-1 h10 flex acjc bgc-purple containfit bora-s z4">
                <?php
                    };
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
                    <?php
                    if ($type === "user") {
                    ?>
                    <p class="posr txt-s c-lightgray hover-text-white">obtained <?php echo $reservedDate;?></p>
                    <?php
                    };
                    ?>
                </div>
            </div>
        </div>
        <?php
        } else if ($filter === "all" && !empty($reservedArray)) {
        ?>
        <div class="posr sideMg pad-s-v pad-n-s w95p flex gap10 bg-def-1 box-shad-black-1 border-purple bora-s">
            <h2 class="posr txt-n">All Badges</h2>
            <div class="posr leftMg flex txt-n c-white gap5">
                <p class="posr pad-sl txt-n c-lightgray"><?php echo $totalDisplayed;?> badges found</p>
            </div>
        </div>
        <?php
            foreach ($badgeGroups as $bgIndex => $bgValue) {
                $bgGroupTitle = $bgValue['badgeGroupTitle'];
                $bglibsIds = $bgValue['libsIds'];
                $bgGroupList = $bgValue['badgeList'];
                if (isset($bgGroupList)) {
        ?>
            <div class="posr sideMg w95p flex fld bg-def-1 box-shad-black-1 border-purple bora-s hover-border-white">
                <a href="acvb.php?type=badges&filter=all&ref=<?php echo $bglibsIds;?>&target=<?php echo $bgIndex;?>" class='posr pad-nt pad-s-s pad-sb w100p txt-b txtnowrap border-purple-b ovh hover-text-blue'><?php echo $bgGroupTitle;?></a>
        <?php
                    foreach ($bgGroupList as $raIds) {
                        $reservedName = $reservedArray[$raIds]['Name'];
                        $reservedDesc = $reservedArray[$raIds]['Desc'];
                        $reservedType = $reservedArray[$raIds]['Type'];
                        $reservedIcon = $reservedArray[$raIds]['Icon'];
                        $reservedRefs = $reservedArray[$raIds]['Refs'];
                        if ($type === "user") {
                            $reservedDate = $reservedArray[$raIds]['Date'];
                        }
                        $reservedDir = "ab/" . $reservedRefs . "/";
        ?>
                <a href="acvb.php?type=<?php echo $type;?>&filter=one&ref=<?php echo $reference;?>&target=<?php echo $raIds;?>" class='posr pad-s-v pad-n-s w100p flex gap10 ovh-s hover-white'>
                    <div class="posr r1-1 h10 flex">
                    <?php
                        if (empty($reservedIcon) || $reservedIcon === "empty") {
                            if ($type != "user") {
                        ?>
                            <img src="Library/libsImg/<?php echo $libsPublisher . "/" . $libsAttachs;?>" class="autoMg r1-1 h10 flex acjc blurbg containfit bora-s z4">
                        <?php
                            } else {
                        ?>
                                <img src="img/cgcclogo.png" class="autoMg r1-1 h10 flex acjc bgc-purple containfit bora-s z4">
                        <?php
                            };
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
                        <?php
                        if ($type === "user") {
                        ?>
                        <p class="posr txt-s c-lightgray hover-text-white">obtained <?php echo $reservedDate;?></p>
                        <?php
                        };
                        ?>
                    </div>
                </a>
        <?php
                    }
                }
        ?>
        </div>
        <?php
            }
        }
        ?>
    </div>

    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
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
    <?php include_once 'footer.php';?>
</body>
</html>
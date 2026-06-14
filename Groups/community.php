<?php
require_once '../processes/database.php';
if (!isset($_GET['lIds']) || !isset($_GET['lc'])) {
    $_SESSION['corsmsg'] = "denied request";
    header ('location: manage.php');
    exit;
}
if (isset($_SESSION['resetPass']) && $_SESSION['resetPass'] == true) {
    header ('location: manage.php');
    exit;
}
$errors = array();
$allowChanges = false;
$root_route = "../";
require_once '../secureSession.php';
require_once 'ReAuth.php';
if (isset($_SESSION['profileTags']) && isset($_SESSION['GroupsToken'])) {
    $aidis = $_SESSION['profileTags'];
    $gToken = $_SESSION['GroupsToken'];
    $gids = $_SESSION['gids'];
    $ChangerRoles = $_SESSION['roles'];
    if ($ChangerRoles === "founder" || $ChangerRoles === "administrator") {
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
$libsIds = $_GET['lIds'];
$communityIds = $_GET['lc'];
$State = "Publics";
$posting = false;
$access = false;
$noForum = false;
$forumArr = array();
$prebind = '"' . $aidis . '"';
$check_orgs = $connects->prepare("SELECT names FROM ogroup WHERE identification = ? AND founder = ? OR JSON_CONTAINS(members, ?);");
$check_orgs->bind_param("sss", $gids, $aidis, $prebind);
$check_orgs->execute();
$result_check_orgs = $check_orgs->get_result();
if ($result_check_orgs->num_rows > 0) {
    $tempCheckOgValue = $result_check_orgs->fetch_assoc();
    $Ognames = $tempCheckOgValue['names'];
    $stmt_check_software = $connects->prepare("SELECT libsIds FROM libslist WHERE libsPublisher = ? AND libsIds = ? AND libsForum = ? AND libsState = 'Publics' ;");
    $stmt_check_software->bind_param("sss", $gids, $libsIds, $communityIds);
    $stmt_check_software->execute();
    $result_check_software = $stmt_check_software->get_result();
    if ($result_check_software->num_rows == 0) {
        $_SESSION['corsmsg'] = "Required keys does not match";
        header('location: ../index.php');
        exit;
    };
} else {
    $_SESSION['corsmsg'] = "You are not allowed to access this page";
    header('location: ../index.php');
    exit;
};
$check_topic = $connects->prepare("SELECT * FROM topics WHERE topicIds = ? AND topicState = 'Publics' AND TopicType = 'publisherOnly' ORDER BY topicDates DESC;");
$check_topic->bind_param("s", $communityIds);
$check_topic->execute();
$result_check_topic = $check_topic->get_result();
if ($result_check_topic->num_rows > 0) {
    while ($value = $result_check_topic->fetch_assoc()) {
        $topicIds = $value['topicIds'];
        $topicTitles = $value['topicTitles'];
        $topicDescs = $value['topicContents'];
        $topicDates = $value['topicDates'];
    };
    $posting = true;
} else {
    $_SESSION['corsmsg'] = "community not found";
    header('location: ../index.php');
    exit;
};
if (isset($_GET['filter'])) {
    $FilterReq = $_GET['filter']; 
} else {
    $FilterReq = "none";
}
if (isset($_GET['ids'])) {
    $targetIds = $_GET['ids'];
    if($targetIds != "") {
        $targetIds = htmlspecialchars($targetIds, ENT_QUOTES, 'UTF-8');
        $searchTarget = '%' . $targetIds . '%';
    } else {
        $FilterReq = "none";
        $targetIds = null;
    }
}
switch ($FilterReq) {
    case 'none':
        $check_Forum = $connects->prepare("SELECT * FROM forums WHERE forumTopics = ? AND ForumState = 'Publics' ORDER BY ForumDates DESC;");
        $check_Forum->bind_param("s", $communityIds);
        break;
    case 'oldtonew':
        $check_Forum = $connects->prepare("SELECT * FROM forums WHERE forumTopics = ? AND ForumState = 'Publics' ORDER BY ForumDates ASC;");
        $check_Forum->bind_param("s", $communityIds);
        break;
    case 'search':
        if($targetIds === "empty") {
            $check_Forum = $connects->prepare("SELECT * FROM forums WHERE forumTopics = ? AND ForumState = 'Publics' ORDER BY ForumDates DESC;");
            $check_Forum->bind_param("s", $communityIds);
        } else {
            $check_Forum = $connects->prepare("SELECT * FROM forums WHERE forumTopics = ? AND ForumState = 'Publics' AND ForumTitles LIKE ? ORDER BY ForumDates DESC;");
            $check_Forum->bind_param("ss", $communityIds, $searchTarget);
        }
        break;
    default:
        $_SESSION['corsmsg'] = "Unknown filter";
        header ('location: dashboard.php');
        exit;
        break;
}
$check_Forum->execute();
$result_check_Forum = $check_Forum->get_result();
if ($result_check_Forum->num_rows > 0) {
    while ($value = $result_check_Forum->fetch_assoc()) {
        $forumArr[$value['ForumIds']] = [
            "ForumIds"      => $value['ForumIds'],
            "ForumCreator"  => $value['ForumCreator'],
            "ForumTitles"   => $value['ForumTitles'],
            "ForumDates"    => $value['ForumDates'],
            "ForumContents" => $value['ForumContents'],
            "ForumAttachment" =>  $value['ForumAttachment']
        ];
    }
} else {
    $noForum = true;
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
    function loadForum(ReqstData) {
        const form = document.forms.REMOVE;
        const values = ReqstData.dataset;
        Object.keys(values).forEach((key) => {
            if (form[key]) 
                form[key].value = values[key];
        });
    };
    </script>
    <title>Community Forum</title>
</head>
<body class="minh100">
    <div class="posr w100p flex blurbg border-purple-b z4">
        <div class="posr rightMg w60p flex border-purple-b">
            <div class="posr pad-n flex fld acjc bgc-purple">
                <h2 class="txt-n txtc semibold">DASHBOARD</h2>
                <a href="manage.php" class="link-cover hover-white">.</a>
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
        </div>
        <p class="posr pad-n txt-b hover-red" onclick="linker('index.php')">X</p>
    </div>
    <div class="posr pad-m-v pad-s-s w100p flex gap10 blurbg bg-half-gray border-purple-b z4">
        <a onclick="uniDisplaySwitch('postDialog');" class="pad-s-v pad-nl pad-sr txt-s bg-gold c-black border-purple bora-s box-shad-black-1 hover-text-blue">Post New Announcement</a>
        <a onclick="uniDisplaySwitch('editDialog');" class="pad-s-v pad-nl pad-sr txt-s bg-gold c-black border-purple bora-s box-shad-black-1 hover-text-blue">Edit Detail</a>
    </div>
<!-- posted announcement container -->
    <div class="posr w100p r4-1 flex fld acjc bg-3 border-1">
        <h2 class="w100p txtc txt-30 bold"><?php echo $topicTitles;?></h2>
        <p class="w100p txtc txt-s"><?php echo $topicDescs;?></p>
    </div>
    <div class="sideMg bottomMg w95p minh40 flex wrap gap-s acjc z1">
    <?php
    if ($noForum == false) {
        foreach ($forumArr as $value) {
            $ids = $value['ForumIds'];
            $creators = $value['ForumCreator'];
            $titles = $value['ForumTitles'];
            $dates = $value['ForumDates'];
            $contents = $value['ForumContents'];
            $attachs = $value['ForumAttachment'];
    ?>
        <div class="posr pad-s w20p r16-9 flex fld bg-half-gray border-1 gap5">
    <?php
                if ($attachs != "empty.png" && isset($attachs)) {
    ?>
            <img src="../TS/ArchFiles/<?php echo $attachs;?>" alt="" class="posa c0 w100p r16-9 coverfit opacity3 z2">
    <?php
                };
    ?>
            <h2 class="txt-n z3"><?php echo $titles;?></h2>
            <div class="bottomMg-s5 w100p flex space-between z3">
                <p class="txt-s z3"><?php echo $creators;?></p>
                <p class="txt-s z3"><?php echo $dates;?></p>
            </div>
            <p class="maxh10 txt-s ovh z3"><?php echo $contents;?></p>
            <div class="topMg w100p flex z3">
                <a href="../TS/forum/forum.php?ids=<?php echo $ids;?>" class="posr pad-m-v pad-s-s w50p txtc bgc-white points hover-white trs500ms" target="_blank" rel="noopener noreferrer">
                    <img src="../img/open-outline.svg" alt="" class="posr autoMg h10px r1-1 containfit">
                </a>
                <div class="posr pad-m-v pad-s-s w50p txtc bgc-red points hover-white trs500ms" onclick="uniDisplaySwitch('deleteDialog'); loadForum(this);" data-foids="<?php echo $ids;?>" data-postname="<?php echo $titles;?>">
                    <img src="../img/trash-outline.svg" alt="" class="posr autoMg h10px r1-1 containfit">
                </div>
            </div>
        </div>
    <?php
        };
    } else {
    ?>
            <h2 class="posr vertiMg w100p txtc txt-n z4">no announcement for this topic yet</h2>
    <?php
    };
    ?>
    </div>
    <?php include_once '../extra/footers.php';?>
<!-- post dialog -->
    <dialog id="postDialog" class="posf c0 w88 h90 dp-none fld acjc bg-half-white ovh-s z999">
        <div class="posr w100p bg-half-gray blurbg flex"><h2 class="posr rightMg pad-s txt-b">post new announcement</h2><p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('postDialog')">X</p></div>
        <form class="posr wh100p bg-half-gray blurbg flex" name="ANNOUNCE" action="proceed_community.php" method="post" enctype="multipart/form-data">
            <input type="text" class="hiddeninp" name="lIds" value="<?php echo $libsIds;?>"hidden>
            <input type="text" class="hiddeninp" name="lc" value="<?php echo $communityIds;?>"hidden>    
            <div class="posr autoMg h50p maxh50 r16-9 flex fld acjc gap5">
                <img id="prev" class="posr sideMg wh100p containfit">
                <input class="posa c0 wh100p txtc" type="file" name="file" accept="image/*" onchange="uniLoadFile(event, 'prev')">
            </div>
            <div class="vertiMg w50p flex fld gap5">
                <div class="sideMg w88p flex fld">
                    <label for="ForumTitles">Announcement Titles</label>
                    <input type="text" name="ForumTitles" class="inptxt" placeholder="Make title for the forum" auto-complete="off" maxlength="255" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="ForumDescription">Announcement Description</label>
                    <input type="text" name="ForumDescription" class="inptxt" placeholder="The full description" auto-complete="off" required>
                </div>
                <select name="ForumTopics" class="hiddeninp" hidden>
                    <option class="hiddeninp" name="ForumTopics" value="<?php echo $communityIds;?>" selected hidden>.</option>
                </select>
                <div class="sideMg w88p flex fld">
                    <input class="pad-s txtc txt-s bg-gold c-black border-hover-white" type="submit" name="submit" value="Post">
                </div>
            </div>
        </form>
    </dialog>
<!-- edit detail dialog -->
    <dialog id="editDialog" class="posf c0 minw100px w30p dp-none fld acjc bg-half-white ovh-s z999">
        <div class="posr w100p bg-half-gray blurbg flex"><h2 class="posr rightMg pad-s txt-b">Edit Detail</h2><p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('editDialog')">X</p></div>
        <form class="posr pad-s wh100p bg-half-gray blurbg flex fld" action="update_prf.php" method="post">
            <input type="text" class="hiddeninp" name="libsids" value="<?php echo $libsIds;?>"hidden>
            <input type="text" class="hiddeninp" name="ForumTopics" value="<?php echo $communityIds;?>"hidden>
            <div class="sideMg w100p flex fld">
                <label for="topicdesc">Topic Description</label>
                <textarea type="text" name="topicdesc" class="inptxt minh10" placeholder="" auto-complete="off"required><?php echo $topicDescs;?></textarea>
            </div>
            <div class="sideMg w100p flex fld">
                <input class="pad-s txtc txt-s bg-gold c-black border-hover-white" type="submit" name="submit" value="Change">
            </div>
        </form>
    </dialog>
    <!-- Remove Post -->
    <dialog id="deleteDialog" class="posf pad-n c0 pad-b-v minw20 maxh50 dp-none fld bg-2 border-1 bora-s z999">
        <form class="wh100p flex fld" name="REMOVE" action="proceed_community.php" method="post">
            <h2 class="w100p txt-n txtc">Confirm to Remove this Post?</h2>
            <input type="text" class="hiddeninp" name="lIds" value="<?php echo $libsIds;?>"hidden>
            <input type="text" class="hiddeninp" name="lc" value="<?php echo $communityIds;?>"hidden>
            <input class="pad-s-v bg-transparent maxh10 txtc txt-b c-white border-none ovh" type="text" name="postname" readonly>
            <input class="hiddeninp" type="text" name="foids" hidden>
            <input class="topMg-s10 pad-s-v w100p txt-n txtc bg-red border-1 border-hover-white" type="submit" name="submit" value="REMOVE">
        </form>
        <button class="topMg-s5 pad-s-v w100p txt-n txtc c-black border-1 border-hover-white" onclick="uniDisplaySwitch('deleteDialog')">NO</button>
    </dialog>
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
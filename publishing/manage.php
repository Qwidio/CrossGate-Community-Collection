<?php
require_once '../processes/database.php';
$allowChanges = false;
$root_route = "../";
require_once '../secureSession.php';
require_once '../Groups/ReAuth.php';
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
        header ('location: ../Groups/manage.php');
        exit;
    }
} else {
    $_SESSION['corsmsg'] = "sign in to access";
    header ('location: ../index.php');
    exit;
}
$publishing = false;
$prebind = '"' . $aidis . '"';
$check_orgs = $connects->prepare("SELECT names, about, founded, founder, admins, members, logo, banner, role_publish FROM ogroup WHERE identification = ? AND founder = ? OR JSON_CONTAINS(members, ?);");
$check_orgs->bind_param("sss", $gids, $aidis, $prebind);
$check_orgs->execute();
$result_check_orgs = $check_orgs->get_result();
if ($result_check_orgs->num_rows > 0) {
    while ($value = $result_check_orgs->fetch_assoc()) {
        $Ognames = $value['names'];
        $about = $value['about'];
        $founder = $value['founder'];
        $founded = $value['founded'];
        $admins = $value['admins'];
        $members = $value['members'];
        $logo = $value['logo'];
        $banner = $value['banner'];
        $role = $value['role_publish'];
    }
} else {
    $_SESSION['corsmsg'] = "You are not allowed to access this page";
    header('location: ../index.php');
    exit;
};
$viewState = "publics";
$tempLibsArr = array();
if (isset($_GET['filter'])) {
    $FilterReq = $_GET['filter']; 
} else {
    $FilterReq = "none";
}
if (isset($_GET['view'])) {
    if (isset($_GET['filter'])) {
        $FilterReq = $_GET['filter']; 
    } else {
        $FilterReq = "none";
    }
    $viewState = $_GET['view']; 
} else {
    $viewState = "publics";
}
if (isset($_GET['ids'])) {
    $targetIds = $_GET['ids'];
    if($targetIds != "") {
        $targetIds = htmlspecialchars($targetIds, ENT_QUOTES, 'UTF-8');
        $searchTarget = "%".$targetIds."%";
        $_SESSION['prev_loc'] = "manage.php?filter=" . $FilterReq . "&ids=" . $targetIds;
    } else {
        $FilterReq = "none";
        $targetIds = null;
    }
}
switch ($FilterReq) {
    case 'none':
        $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsVT, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBannersFirst, libsBanners, libsTitles, libsDesc, repolink, libsMD, extlink, addedDates, cltNumbs, libsCategorys, libsType, libsForum, fdrLibs, recspecs, devstats, devstatdesc FROM libslist WHERE libsPublisher = ? AND libsState = ? ORDER BY addedDates DESC;");
        $check_software->bind_param("ss", $gids, $viewState);
        break;
    case 'oldtonew':
        $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsVT, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBannersFirst, libsBanners, libsTitles, libsDesc, repolink, libsMD, extlink, addedDates, cltNumbs, libsCategorys, libsType, libsForum, fdrLibs, recspecs, devstats, devstatdesc FROM libslist WHERE libsPublisher = ? AND libsState = ? ORDER BY addedDates ASC;");
        $check_software->bind_param("ss", $gids, $viewState);
        break;
    case 'search':
        if($targetIds === "empty") {
            $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsVT, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBannersFirst, libsBanners, libsTitles, libsDesc, repolink, libsMD, extlink, addedDates, cltNumbs, libsCategorys, libsType, libsForum, fdrLibs, recspecs, devstats, devstatdesc FROM libslist WHERE libsPublisher = ? AND libsState = ? ORDER BY addedDates DESC;");
        $check_software->bind_param("ss", $gids, $viewState);
        } else {
            $check_software = $connects->prepare("SELECT libsIds, libsPublisher, libsVT, libsAttachs, JSON_EXTRACT(libsBanners, '$[0]') AS libsBannersFirst, libsBanners, libsTitles, libsDesc, repolink, libsMD, extlink, addedDates, cltNumbs, libsCategorys, libsType, libsForum, fdrLibs, recspecs, devstats, devstatdesc FROM libslist WHERE libsTitles LIKE ? AND libsPublisher = ? ORDER BY addedDates DESC;");
            $check_software->bind_param("ss", $gids, $searchTarget);
        }
        break;
    default:
        $_SESSION['corsmsg'] = "Unknown filter";
        header ('location: manage.php');
        exit;
        break;
}
$check_software->execute();
$result_check_software = $check_software->get_result();
if ($result_check_software->num_rows > 0) {
    $publishing = true;
    while ($value = $result_check_software->fetch_assoc()) {
        $ids = $value['libsIds'];
        $libsPublisher = $value['libsPublisher'];
        $libsVT = $value['libsVT'];
        $attachs = $value['libsAttachs'];
        $libsBanners = $value['libsBanners'];
        $BannersFirst = $value['libsBannersFirst'];
        $titles = $value['libsTitles'];
        $Desc = $value['libsDesc'];
        $repolink = $value['repolink'];
        $libsMD = $value['libsMD'];
        $extlink = $value['extlink'];
        $addedDates = $value['addedDates'];
        $cltNumbs = $value['cltNumbs'];
        $libsType = $value['libsType'];
        $category = $value['libsCategorys'];
        $libsForum = $value['libsForum'];
        $fdrLibs = $value['fdrLibs'];
        $recspecs = $value['recspecs'];
        $devstats = $value['devstats'];
        $BannersFirst = json_decode($BannersFirst, true);
        $targetdir = "../vaults/" . $gids . "/" . $fdrLibs;
        if (!file_exists($targetdir)) {
            $fdrLibs = "";
        }
        if (!in_array($ids, $tempLibsArr)) {
            $tempLibsArr[$ids] = [
            "libsIds"           => "$ids",
            "libsPublisher"     => "$libsPublisher",
            "libsVT"            => "$libsVT",
            "libsAttachs"       => "$attachs",
            "libsBanners"       =>  $libsBanners,
            "libsBannersFirst"  => "$BannersFirst",
            "libsTitles"        => "$titles",
            "libsDesc"          => "$Desc",
            "repolink"          => "$repolink",
            "libsMD"            => "$libsMD",
            "extlink"           =>  $extlink,
            "libsType"          => "$libsType",
            "libsCategorys"     => "$category",
            "addedDates"        => "$addedDates",
            "cltNumbs"          =>  $cltNumbs,
            "libsForum"         => "$libsForum",
            "fdrLibs"           => "$fdrLibs",
            "devstats"          => "$devstats",
            "recspecs"          => "$recspecs"
            ];
        };
    };
};
$encodedLibsArr = json_encode($tempLibsArr, JSON_UNESCAPED_SLASHES);
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
    <title>Management Dashboard</title>
</head>
<body class="minh100 gap10">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity05 z1">
    <div class="posr w100p flex blurbg border-purple-b z4">
        <div class="posr rightMg w60p flex">
            <div class="posr pad-n flex fld acjc bgc-purple">
                <h2 class="txt-n txtc semibold">DASHBOARD</h2>
                <a href="../Groups/manage.php" class="link-cover hover-white">.</a>
            </div>
            <?php
            if ($publishing == true) {
            ?>
            <div class="posr pad-n flex fld acjc bg-half-white">
                <h2 class="txt-n txtc semibold">FILE MANAGER</h2>
                <a onclick="uniDisplaySwitch('filemanager');" class="link-cover hover-white">.</a>
            </div>
            <?php
            }
            ?>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="../Groups/profile.php?gids=<?php echo $gids;?>" class="link-cover hover-white">.</a>
            </div>
            <div class="posr pad-n flex fld acjc bg-half-gray">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../documentation/docs.php" class="link-cover hover-white">.</a>
            </div>
        </div>
    </div>
    <div class="posa l0 t0 pad-st w20p minh100 blurbg flex fld border-r z2">
        <div class="posr topMg-5 pad-n w100p maxh30 flex fld border-b gap5">
<?php
if (isset($_GET['view']) || isset($FilterReq) && isset($targetIds)) {
?>
            <a href="manage.php" class="pad-s w100p txtc txt-s bgc-blue border-none bora-s hover-text-black">Reset Filter</a>
<?php
}
?>
            <button onclick="uniDisplaySwitch('newColletionDialog');" class="pad-s w100p txtc txt-s bg-transparent border-orange bora-s hover-text-orange">New Collection</button>
            <a href="manage.php?filter=none&view=draft" class="pad-s w100p txtc txt-s bg-transparent border-blue box-shad-white-2 bora-s hover-text-orange">Drafted Collection</a>
            <a href="manage.php?filter=none&view=archived" class="pad-s w100p txtc txt-s bg-transparent border-green box-shad-white-2 bora-s hover-text-orange">Archived Collection</a>
        </div>
        <!-- search bar -->
        <form id="SearchBar" class="posr pad-n w100p flex gap5 trs500ms border-b" action="manage.php">
            <input type="text" name="ids" placeholder="<?php if(empty($targetIds)) {?>search collection...<?php } else {echo $targetIds;};?>" id="searchbox" class="pad-s w100p txt-s txtnowrap bg-white c-black border-1 bora-s z4" tabindex="1">
            <button type="submit" name="filter" value="search" class="posr vertiMg pad-s flex bg-white c-black border-1 bora-s" tabindex="2"><img src="../img/search.png" alt="" class="icon-rs h100p bg-white containfit points"></button>
        </form>
        <?php
        if ($publishing == true) {
        ?>
        <div class="posr pad-n w100p maxh30 flex fld gap5 ovh-s">
            <p class="w100p txt-n semibold points">Published Collection</p>
            <?php
                foreach ($tempLibsArr as $id => $value) {
                    $BannersFirst = $value['libsBannersFirst'];
                    $ids              = $value['libsIds'];
                    $titles           = $value['libsTitles'];
            ?>
            <a class="posr leftMg-s10 rightMg-s10 pad-m-v pad-s-s w95p txt-s txtnowrap bg-half-gray box-shad-black-1 border-purple hover-white ovh" href="../Library/core/view.php?type=clts&ids=<?php echo $ids;?>"><?php echo $titles;?></a>
            <?php
                };
            ?>
        </div>
        <?php
        }
        ?>
    </div>
    <section class="posr leftMg pad-s-v w79p minh70 flex wrap gap-s z2">
    <?php
        ?>
    <?php
    // the published software
    if (!empty($tempLibsArr) && $publishing == true) {
        foreach ($tempLibsArr as $id => $value) {
            $libsIds        = $value['libsIds'];
            $titles         = $value['libsTitles'];
            $libsPublisher  = $value['libsPublisher'];
            $libsVT         = $value['libsVT'];
            $attachs        = $value['libsAttachs'];
            $BannersFirst   = $value['libsBannersFirst'];
            $desc           = $value['libsDesc'];
            $repolink       = $value['repolink'];
            $libsMD         = $value['libsMD'];
            $extlink        = $value['extlink'];
            $ctype          = $value['libsType'];
            $category       = $value['libsCategorys'];
            $addedDates     = $value['addedDates'];
            $cltNumbs       = $value['cltNumbs'];
            $libsForum      = $value['libsForum'];
            $fdrLibs        = $value['fdrLibs'];
            $recspecs       = $value['recspecs'];
            $devstats       = $value['devstats'];
    ?>
        <div class="posr bottomMg w30p r16-9 flex fld bgc-black bora-s ovh z2">
            <img src="../Library/libsImg/<?php echo $libsPublisher . "/" . $BannersFirst;?>" alt="" class="posa wh100p coverfit opacity5 z1">
            <div class="posr topMg pad-s flex fld bg-half-gray gap5 z3">
                <h2 class="posr txt-n txtnowrap ovh z4"><?php echo $titles;?></h2>
                <p class="posr txt-s txtnowrap ovh z4">Marked: <?php echo $cltNumbs . " | Created on " . $addedDates;?></p>
            </div>
            <div class="sideMg w100p flex z3">
                <!-- <a onclick="uniDisplaySwitch('info'); uniLoad(this, 'stats');" class="pad-s w40p txt-s txtc bgc-purple points hover-text-black z4" data-titles="<?php echo $titles;?>" data-cltnumbs="<?php echo $cltNumbs;?>" data-status="<?php echo $viewState;?>" data-desc="<?php echo $desc;?>" data-md="<?php echo $libsMD;?>" data-devstats="<?php echo $devstats;?>" data-forum="<?php echo $libsForum;?>">Detail</a> -->
                <a href="file_manager.php?libsids=<?php echo $libsIds;?>" class="pad-s w40p txt-s txtc bgc-purple points hover-text-black z4">File Manager</a>
                <button onclick="uniDisplaySwitch('cltEdit'); uniLoad(this, 'editForm'); uniReloadFile('<?php echo '../Library/libsImg/' . $libsPublisher . '/' . $attachs;?>', 'editAttachPrev'); createLinkElem(libsArr.<?php echo $libsIds;?>.extlink, 'extlinkContainer'); createBannerElem(libsArr.<?php echo $libsIds;?>.libsBanners, '<?php echo $libsPublisher;?>', 'bannerContainer2')" class="pad-s w40p txt-s txtc bg-red border-none hover-text-black z4" data-libsids="<?php echo $libsIds;?>" data-libsvt="<?php echo $libsVT;?>" data-newtitle="<?php echo $titles;?>" data-desc="<?php echo $desc;?>" data-ctype="<?php echo $ctype;?>" data-categoryIds="<?php echo $category;?>" data-repolink="<?php echo $repolink;?>" data-md="<?php echo $libsMD;?>">Edit</button>
                <button onclick="uniDisplaySwitch('changeState'); uniLoad(this, 'predata');" class="pad-s w40p txt-s txtc txtnowrap bg-green border-none hover-text-black z4" data-libsids="<?php echo $libsIds;?>" data-titles="<?php echo $titles;?>" data-cltnumbs="<?php echo $cltNumbs;?>" data-devstats="<?php echo $devstats;?>" data-status="<?php echo $viewState;?>">Change State</button>
            </div>
        </div>
    <?php
        };
    } else {
    ?>
        <a href="manage.php" class="posr pad-b-v w100p txtc txt-n hover-text-orange">No collection found</a>
    <?php
    };
    ?>
    </section>
    <!-- publish new dialog -->
    <dialog id="newColletionDialog" class="posf c0 w100p h100p bg-half-gray fld ovh-s z999">
        <div class="posr w100p blurbg flex border-b"><h2 class="posr rightMg pad-s txt-b">Create New Collection</h2><p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('newColletionDialog')">X</p></div>
        <form class="posr w100p blurbg flex fld gap10" action="create_collection.php" method="post" enctype="multipart/form-data">
            <div class="posr sideMg pad-n w88p flex fld acjc gap5 ovh-v">
                <label for="attachPrev" class="txt-b bold">Collection Logo</label>
                <div class="posr r1-1 h50 flex fld acjc gap5">
                    <img id="attachPrev" class="posr sideMg wh100p coverfit bg-half-orange">
                    <input class="posa c0 wh100p txtc" type="file" name="attach" accept="image/*" onchange="uniLoadFile(event, 'attachPrev')" required>
                </div>
                <p class="txt-n bold">(Max size 5MB)</p>
            </div>
            <div class="posr topMg-s10 sideMg w100p flex acjc ovh-v">
                <label class="txt-b bold">Banners</label>
            </div>
            <div id="bannerContainer" class="posr sideMg pad-n w88p flex gap5 border-1 ovh-v">
                <div id="banner1" class="posr r16-9 h50 flex fld acjc gap5">
                    <img id="bannerPrev1" class="posr sideMg wh100p coverfit bg-half-gray">
                    <input class="posa c0 wh100p txtc" type="file" name="banners1" accept="image/*" onchange="uniLoadFile(event, 'bannerPrev1')" required>
                </div>
                <p id="add_btn" class="posr pad-n-s h100p flex acjc txt-30 bg-half-gray hover-white points" onclick="newElemt('bannerContainer','img','clts','add_btn');">+</p>
            </div>
            <div class="posr bottomMg-s10 sideMg w100p flex acjc ovh-v">
                <p class="txt-n bold">(Max size 5MB)</p>
            </div>
            <div class="posr pad-n w100p flex fld gap10">
                <div class="sideMg w88p flex fld">
                    <label for="libsVT">Demo/Trailer Embed link(optional)</label>
                    <input type="text" name="libsVT" class="inptxt" placeholder="example: from 'youtube.com/watch?v=example' to 'youtube.com/embed/example'" auto-complete="off" maxlength="1000">
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="title">Title</label>
                    <input type="text" name="title" class="inptxt" placeholder="what's title?" auto-complete="off" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="desc">Description</label>
                    <textarea type="text" name="desc" class="inptxt h10 ovh-s" placeholder="Your best description of it" auto-complete="off" required></textarea>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="repolink">Repository</label>
                    <input type="text" name="repolink" class="inptxt" placeholder="add your repository Readme.MD link from github/your own git" auto-complete="off" maxlength="1000" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="md">MarkDown Link</label>
                    <input type="text" name="md" class="inptxt" placeholder="add your repository Readme.MD link from github/your own git" auto-complete="off" maxlength="1000" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="cType">type</label>
                    <select name="cType" class="inpselect" required>
                        <option value="" selected disabled>Select collection type</option>
                        <option name="cType" value="software" required>Software</option>
                        <option name="cType" value="game" required>Games</option>
                        <option name="cType" value="mods" required>Mods</option>
                    </select>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="categoryids">category</label>
                    <select name="categoryids" class="inpselect" required>
                        <option value="" selected disabled>Select one category</option>
                        <?php
                        $stmt_get_categoryss = $connects->prepare("SELECT * FROM categorys WHERE categoryState = 'publics';");
                        $stmt_get_categoryss->execute();
                        $result_get_categoryss = $stmt_get_categoryss->get_result();
                        if ($result_get_categoryss->num_rows > 0) {
                            $uniqueT = [];
                            while ($values =  $result_get_categoryss->fetch_assoc()) {
                                $categoryIds = $values['categoryIds'];
                                $categoryTitles = $values['categoryTitles'];
                                if (!in_array($categoryIds, $uniqueT)) {
                                    echo "<option name='categoryids' value='$categoryIds' required>$categoryTitles</option>";
                                    $uniqueT[] = $topicIds;
                                };
                            };
                        };
                        ?>
                    </select>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="devstats">status</label>
                    <select name="devstats" class="inpselect" required>
                        <option value="" selected disabled>Select current development status</option>
                        <option name='devstats' onclick="uniDisplaySwitch('dsd')" value='earlyaccess' required>Early Access</option>
                        <option name='devstats' onclick="uniDisplaySwitch('dsd')" value='beta' required>Beta Access</option>
                        <option name='devstats' onclick="uniDisplaySwitch('dsd')" value='full' required>Full Release</option>
                    </select>
                </div>
                <div id="dsd" class="sideMg w88p dp-none fld">
                    <label for="devstatdesc">Describe current development state</label>
                    <textarea type="text" name="devstatdesc" class="inptxt h10 ovh-s"  placeholder="give an explanation" auto-complete="off" required>-</textarea>
                </div>
                <div class="posr sideMg pad-s w88p flex fld gap5 border-1 bora-s">
                    <p class="rightMg">Links</p>
                    <div id="newExtlinkContainer" class="posr w100p flex fld">
                        <div class="posr pad-m w100p flex fld border-1 bora-s">
                            <input type="text" name="linkname1" class="inptxt" placeholder="Site name" auto-complete="off" maxlength="255" required>
                            <input type="text" name="extlink1" class="inptxt" placeholder="Link" auto-complete="off" maxlength="1000" required>
                        </div>
                    </div>
                    <p class="w100p txt-l txtc bg-1 points" onclick="newElemt('newExtlinkContainer','link','extlink','none');">+</p>
                </div>
                <div class="sideMg w88p flex fld">
                    <input class="pad-s-v txt-n c-black bgc-gold" type="submit" name="submit" value="Create">
                </div>
            </div>
        </form>
    </dialog>
    <!-- open file manager dialog -->
    <dialog id="filemanager" class="posf pad-b-s pad-bb c0 minw100px w20 maxh50 dp-none fld bg-half-gray blurbg border-1 bora-s z999">
        <form class="posr wh100p flex fld gap10">
            <h2 class="pad-nt pad-sb w100p txt-b txtc border-b">Open File Manager</h2>
            <select name="libsids" class="inpselect" required>
                <option value="" selected disabled>Select Collection</option>
                <?php
                if (!empty($tempLibsArr) && $publishing == true) {
                    foreach ($tempLibsArr as $id => $value) {
                        $ids    = $value['libsIds'];
                        $titles = $value['libsTitles'];
                ?>
                <option onclick="linker('file_manager.php?libsids=<?php echo $ids;?>')"><?php echo $titles;?></option>
                <?php
                    };
                };
                ?>
            </select>
        </form>
        <button class="topMg-s10 pad-s-v w100p txt-n txtc c-black border-1 hover-red hover-text-white" onclick="uniDisplaySwitch('filemanager')">Cancel</button>
    </dialog>
    <!-- detail form -->
    <dialog id="info" class="posf c0 minw50 h80p fld bg-half-gray border-purple bora-s ovh-s z999">
        <div class="posr w100p bg-half-gray blurbg flex"><h2 class="posa c0 pad-n txt-b">Detailed Collection Information</h2><p class="posr leftMg pad-n-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('info')">X</p></div>
        <form id="stats" class="posr wh100p bg-half-gray blurbg flex fld gap10" method="post" enctype="multipart/form-data">
            <div class="sideMg pad-s-v w100p flex fld">
                <input type="text" name="titles" class="bg-transparent txt-b txtc c-white border-none" required>
            </div>
            <div class="posr w100p flex gap5 acjc">
                <div class="posr pad-s w30p r16-9 flex fld acjc bg-half-orange box-shad-black-1 border-purple bora-s gap5">
                    <input type="text" name="cltnumbs" class="w100p bg-transparent txtc txt-l c-white border-none" required>
                    <p class="txt-s txtc c-white">Collection MarkOut</p>
                </div>
                <div class="posr pad-s w30p r16-9 flex fld acjc bg-half-orange box-shad-black-1 border-purple bora-s gap5">
                    <select name="devstats" class="inpselect w100p bg-transparent txtc txt-l c-white border-none" required disabled>
                        <option name='devstats' value='earlyaccess' required disabled>Early Access</option>
                        <option name='devstats' value='beta' required disabled>Beta Access</option>
                        <option name='devstats' value='full' required disabled>Full Release</option>
                    </select>
                    <p class="txt-s txtc c-white">Development Status</p>
                </div>
                <div class="posr pad-s w30p r16-9 flex fld acjc bg-half-orange box-shad-black-1 border-purple bora-s gap5">
                    <input type="text" name="status" class="w100p bg-transparent txtc txt-l c-white border-none" required>
                    <p class="txt-s txtc c-white">List Status</p>
                </div>
            </div>
            <div class="wh100p flex fld acjc">
                <p class="txtc txt-b">Stats coming soon...</p>
            </div>
        </form>
    </dialog>
    <!-- edit dialog -->
    <dialog id="cltEdit" class="posf c0 w100p h100p bg-half-gray fld ovh-s z999">
        <div class="posr w100p blurbg flex border-b"><h2 class="posr rightMg pad-s txt-b">Edit Collection</h2><p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('cltEdit')">X</p></div>
        <form id="editForm" class="posr w100p blurbg flex fld gap10" action="edit.php" method="post" enctype="multipart/form-data">
            <input class="hiddeninp" type="text" name="libsids" hidden required>
            <div class="posr sideMg pad-n w88p flex gap10 ovh-v">
                <div class="posr r1-1 h50 flex fld acjc gap5">
                    <img id="editAttachPrev" class="posr sideMg wh100p coverfit bg-half-orange">
                    <input class="posa c0 wh100p txtc hover-white" type="file" name="newattach" accept="image/*" onchange="uniLoadFile(event, 'editAttachPrev')">
                </div>
                <div class="posr pad-n w100p flex fld gap10">
                    <div class="sideMg w100p flex fld">
                        <label for="libsvt">Demo/Trailer Embed link(optional)</label>
                        <input type="text" name="libsvt" class="inptxt" placeholder="example: from 'youtube.com/watch?v=example' to 'youtube.com/embed/example'" auto-complete="off" maxlength="1000">
                    </div>
                    <div class="sideMg w100p flex fld">
                        <label for="newtitle">Title</label>
                        <input type="text" name="newtitle" class="inptxt" placeholder="what's title?" auto-complete="off" required>
                    </div>
                    <div class="sideMg w100p flex fld">
                        <label for="desc">Description</label>
                        <textarea type="text" name="desc" class="inptxt h10 ovh-s" placeholder="Your best description of it" auto-complete="off" required></textarea>
                    </div>
                </div>
            </div>
            <div class="posr topMg-s10 sideMg w100p flex acjc ovh-v">
                <label class="txt-b bold">Banners</label>
            </div>
            <div id="bannerContainer2" class="posr sideMg pad-n w88p flex gap5 border-1 ovh-v">
            </div>
            <div class="posr bottomMg-s10 sideMg w100p flex acjc ovh-v">
                <p class="txt-n bold">(Max size 5MB)</p>
            </div>
            <div class="posr pad-n w100p flex fld gap10">
                <div class="sideMg w88p flex fld">
                    <label for="repolink">Repository</label>
                    <input type="text" name="repolink" class="inptxt" placeholder="add your repository Readme.MD link from github/your own git" auto-complete="off" maxlength="1000">
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="md">MarkDown Link</label>
                    <input type="text" name="md" class="inptxt" placeholder="add your repository Readme.MD link from github/your own git" auto-complete="off" maxlength="1000" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="ctype">type</label>
                    <select name="ctype" class="inpselect" required>
                        <option value="" selected disabled>Select collection type</option>
                        <option name="ctype" value="software" required>Software</option>
                        <option name="ctype" value="game" required>Games</option>
                        <option name="ctype" value="mods" required>Mods</option>
                    </select>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="categoryids">category</label>
                    <select name="categoryids" class="inpselect" required>
                        <option value="" selected disabled>Select one category</option>
                        <?php
                        $stmt_get_categoryss = $connects->prepare("SELECT * FROM categorys WHERE categoryState = 'publics';");
                        $stmt_get_categoryss->execute();
                        $result_get_categoryss = $stmt_get_categoryss->get_result();
                        if ($result_get_categoryss->num_rows > 0) {
                            $uniqueT = [];
                            while ($values =  $result_get_categoryss->fetch_assoc()) {
                                $categoryIds = $values['categoryIds'];
                                $categoryTitles = $values['categoryTitles'];
                                if (!in_array($categoryIds, $uniqueT)) {
                                    echo "<option name='categoryids' value='$categoryIds' required>$categoryTitles</option>";
                                    $uniqueT[] = $topicIds;
                                };
                            };
                        };
                        ?>
                    </select>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="devstats">status</label>
                    <select name="devstats" class="inpselect" required>
                        <option name='devstats' value='earlyaccess' required>Early Access</option>
                        <option name='devstats' value='beta' required>Beta Access</option>
                        <option name='devstats' value='full' required>Full Release</option>
                    </select>
                </div>
                <div id="dsd" class="sideMg w88p flex fld">
                    <label for="devstatdesc">Development state description</label>
                    <textarea type="text" name="devstatdesc" class="inptxt h10 ovh-s"  placeholder="give an explanation" auto-complete="off" required>-</textarea>
                </div>
                <div class="posr sideMg pad-s w88p flex fld gap5 border-1 bora-s">
                    <p class="rightMg">Extra Link</p>
                    <div id="extlinkContainer" class="posr w100p flex fld">
                    </div>
                    <p class="w100p txt-l txtc bg-1 points" onclick="newElemt('extlinkContainer','link','extlink','none');">+</p>
                </div>
                <div class="sideMg w88p flex fld">
                    <input class="pad-s-v txt-n c-black bgc-gold" type="submit" name="submit" value="Update">
                </div>
            </div>
        </form>
    </dialog>
    <!-- changing state form -->
    <dialog id="changeState" class="posf c0 minw50 fld bg-half-gray border-purple bora-s ovh-s z999">
        <div class="posr w100p bg-half-gray blurbg flex"><h2 class="posa c0 pad-n txt-b">Change Collection State</h2><p class="posr leftMg pad-n-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('changeState')">X</p></div>
        <form id="predata" class="posr pad-nb wh100p bg-half-gray blurbg flex fld gap10" action="edit.php" method="post" enctype="multipart/form-data">
            <input type="text" name="libsids" class="hiddeninp" required hidden>
            <div class="sideMg pad-s-v w100p flex fld gap10">
                <input type="text" name="titles" class="bg-transparent txt-l txtc c-white border-none" required disabled>
            </div>
            <div class="posr sideMg w95p flex gap5 acjc">
                <div class="posr pad-s w30p r16-9 flex fld acjc bg-half-orange box-shad-black-1 border-purple bora-s gap5">
                    <input type="text" name="cltnumbs" class="w100p bg-transparent txtc txt-l c-white border-none" required disabled>
                    <p class="txt-s txtc c-white">Collection MarkOut</p>
                </div>
                <div class="posr pad-s w30p r16-9 flex fld acjc bg-half-orange box-shad-black-1 border-purple bora-s gap5">
                    <select name="devstats" class="inpselect w100p bg-transparent txtc txt-l c-white border-none" required disabled>
                        <option name='devstats' value='earlyaccess' required disabled>Early Access</option>
                        <option name='devstats' value='beta' required disabled>Beta Access</option>
                        <option name='devstats' value='full' required disabled>Full Release</option>
                    </select>
                    <p class="txt-s txtc c-white">Development Status</p>
                </div>
                <div class="posr pad-s w30p r16-9 flex fld acjc bg-half-orange box-shad-black-1 border-purple bora-s gap5">
                    <input type="text" name="status" class="w100p bg-transparent txtc txt-l c-white border-none" required disabled>
                    <p class="txt-s txtc c-white">List Status</p>
                </div>
            </div>
            <div class="sideMg w88p flex gap5">
<?php
if ($viewState === "draft") {
?>
                <input class="w100p pad-s-v txt-n c-black bgc-gold bora-s" type="submit" name="submit" value="PUBLISH">
                <input class="w100p pad-s-v txt-n bgc-green bora-s" type="submit" name="submit" value="ARCHIVE">
<?php
} else if ($viewState === "archived") {
?>
                <input class="w100p pad-s-v txt-n bgc-blue bora-s" type="submit" name="submit" value="DRAFT">
<?php
} else {
?>
                <input class="w100p pad-s-v txt-n bgc-blue bora-s" type="submit" name="submit" value="DRAFT">
                <input class="w100p pad-s-v txt-n bgc-green bora-s" type="submit" name="submit" value="ARCHIVE">
<?php
}
?>
            </div>
        </form>
    </dialog>
    <?php include_once '../extra/footers.php';?>
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script>
        const libsArr = <?php echo $encodedLibsArr;?>;
    </script>
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
</body>
</html>
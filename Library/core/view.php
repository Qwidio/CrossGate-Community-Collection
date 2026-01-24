<?php
require_once '../../processes/database.php';
$errors = array();
session_start();
$signed = false;

if (!isset($_GET['type']) || $_GET['type'] === "") {
    $_SESSION['corsmsg'] = "empty view type";
    header ('location: ../../index.php');
    exit;
}
if (!isset($_GET['ids']) || $_GET['ids'] === "") {
    $_SESSION['corsmsg'] = "empty view item";
    header ('location: ../../index.php');
    exit;
}
$Reqtype = $_GET['type'];
$targetIds = $_GET['ids'];
$_SESSION['prev_loc'] = "Library/core/view.php?type=" . $Reqtype . "&ids=" . $targetIds;
if (isset($_SESSION['profileTags'])) {
    $signed = true;
    $aidis = $_SESSION['profileTags'];
} else {
    $signed = false;
}
switch ($Reqtype) {
    case 'category':
        $nolibs = "no";
        $State = "publics";
        $tempLibsArr = [];
        $tempCatgArray = [];
        $stmt_check_category = $connects->prepare("SELECT * FROM categorys WHERE categoryIds = ? AND categoryState = ?;");
        $stmt_check_category->bind_param("ss", $targetIds, $State);
        $stmt_check_category->execute();
        $result_check_category = $stmt_check_category->get_result();
        if ($result_check_category->num_rows > 0) {
            $value = $result_check_category->fetch_assoc();
            $catgIds = $value['categoryIds'];
            $catgTitles = $value['categoryTitles'];
            $uniTitles = $catgTitles;
            $check_software = $connects->prepare("SELECT libsIds, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsTitles, libsDesc, addedDates, cltNumbs FROM libslist WHERE libsCategorys = ? AND libsState = ? ORDER BY addedDates DESC;");
            $check_software->bind_param("ss", $catgIds, $State);
            $check_software->execute();
            $result_check_software = $check_software->get_result();
            if ($result_check_software->num_rows > 0) {
                while ($value = $result_check_software->fetch_assoc()) {
                    $libsIds = $value['libsIds'];
                    $libsBanners = $value['libsBanners'];
                    $libsBanners = str_replace('"', "", $libsBanners);
                    $libsTitles = $value['libsTitles'];
                    $libsDesc = $value['libsDesc'];
                    $addedDates = $value['addedDates'];
                    $cltNumbs = $value['cltNumbs'];
                    if (!in_array($libsIds, $tempLibsArr)) {
                        $tempLibsArr[$libsIds] = [
                        "libsIds"        => "$libsIds",
                        "libsBanners"    => "$libsBanners",
                        "libsTitles"     => "$libsTitles",
                        "libsDesc"       => "$libsDesc",
                        "addedDates"     => "$addedDates",
                        "cltNumbs"       =>  $cltNumbs
                        ];
                    };
                };
            } else {
                $nolibs = "No Collection in this Category";
            };
        } else {
            $_SESSION['corsmsg'] = "category were yet to exist";
            header ('location: ../../index.php');
            exit;
        }
        break;
    case 'clts':
        $State = "publics";
        $tempCatgArray = [];
        $stmt_check_category = $connects->prepare("SELECT * FROM categorys WHERE categoryState = ?;");
        $stmt_check_category->bind_param("s", $State);
        $stmt_check_category->execute();
        $result_check_category = $stmt_check_category->get_result();
        if ($result_check_category->num_rows > 0) {
            while ($value = $result_check_category->fetch_assoc()) {
                $ids = $value['categoryIds'];
                $titles = $value['categoryTitles'];
                if (!in_array($ids, $tempCatgArray)) {
                    $tempCatgArray[$ids] = $titles;
                }
            }
        }
        $check_software = $connects->prepare("SELECT * FROM libslist WHERE libsIds = ? AND libsState = ? ;");
        $check_software->bind_param("ss", $targetIds, $State);
        $check_software->execute();
        $result_check_software = $check_software->get_result();
        if ($result_check_software->num_rows > 0) {
            while ($value = $result_check_software->fetch_assoc()) {
                $ids = $value['libsIds'];
                $libsAttachs = $value['libsAttachs'];
                $libsBanners = $value['libsBanners'];
                $libsTitles = $value['libsTitles'];
                $uniTitles = $libsTitles;
                $libsDesc = $value['libsDesc'];
                $libsMds = $value['libsMD'];
                $addedDates = $value['addedDates'];
                $cltNumbs = $value['cltNumbs'];
                $category = $value['libsCategorys'];
                $fdrLibs = $value['fdrLibs'];
                $libsForum = $value['libsForum'];
                $catgList = $tempCatgArray[$category] ?? null;
            };
        } else {
            $_SESSION['corsmsg'] = "no collection were found";
            header ('location: ../../index.php');
            exit;
        };
        if ($signed == true) {
            $sources = "../../drx/$aidis.json";
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
        }
        break;
    case 'prms':
        # code...
        break;
    
    default:
        $_SESSION['corsmsg'] = "Unknown view type";
        header ('location: ../../index.php');
        exit;
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styling/pallate.css">
    <link rel="stylesheet" href="../../styling/Mindex.css">
    <link rel="stylesheet" href="../../styling/footer.css">
    <title><?php echo $uniTitles;?> || CrossGate Library</title>
</head>
<body class="wh100p bg-2 flex fld">
<!-- the nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <a href="index.php" class="vertiMg pad-s txt-l semibold">CROSSGATE</a>
        <div class="posr w60p flex gap-s">
            <?php
            if (isset($aidis)) {
                ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="markout.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="../../profile.php?user=self" class="link-cover">.</a>
            </div>
            <?php
            }
            ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">CATEGORY</h2>
                <a href="category.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="../../TS/forum/dashboard.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../../documentation/docs.php" class="link-cover">.</a>
            </div>
        </div>
        <?php
        if (!isset($aidis)) {
        ?>
        <div class="leftMg flex acjc gap10">
            <p class="posr pad-n-s pad-s-v txtc txt-n bg-1 border-1 bora-s border-hover-white">LOGIN
                <a href="../../forum-connect/connect_it.php?state=login" class="link-cover">.</a>
            </p>
        </div>
        <?php
        };
        ?>
    </div>
<?php
switch ($Reqtype) {
    case 'category':
?>
    <section class="topMg-5 w100p flex fld">
        <div class="bottomMg-s10 sideMg w75p flex">
            <div class="w100p h30 flex acjc bg-5 border-1">
                <h2 class="w100p txt-30 txtc"><?php echo $catgTitles?></h2>
            </div>
        </div>
        <div class="sideMg w75p minh40 flex fld gap5">
            <?php
            if ($nolibs === "no") {
            ?>
                <h2 class="pad-sb w100p txt-n">New Releases</h2>
                <?php
                foreach ($tempLibsArr as $id => $value) {
                    $libsIds = $value['libsIds'];
                    $libsBanners = $value['libsBanners'];
                    $libsTitles = $value['libsTitles'];
                    $libsDesc = $value['libsDesc'];
                    $addedDates = $value['addedDates'];
                    $cltNumbs = $value['cltNumbs'];
                    ?>
                <div class="posr pad-s w100p flex gap5 border-1">
                    <img src="../libsImg/<?php echo $libsBanners;?>" class="h10 r16-9 objfit">
                    <div class="h100p flex fld">
                        <h2 class="rightMg txt-n"><?php echo $libsTitles;?></h2>
                        <h2 class="rightMg txt-s c-lightgray"><?php echo $libsDesc;?></h2>
                        <p class="topMg rightMg txt-s c-semiwhite"><?php echo $catgTitles;?></p>
                        <a href="view.php?type=clts&ids=<?php echo $libsIds;?>" class="link-cover hover-white">.</a>
                    </div>
                    <div class="leftMg h100p flex fld">
                        <p class="topMg leftMg txt-s c-semiwhite"><?php echo $addedDates;?></p>
                    </div>
                </div>
                <?php
                };
                ?>
            <?php
            } else {
            ?>
            <h2 class="pad-b-v w100p h30 flex acjc"><?php echo $nolibs?></h2>
            <?php
            }
            ?>
        </div>
    </section>
<?php
        break;
    case 'clts':
?>
    <section class="topMg-5 w100p flex fld">
        <div class="sideMg w75p flex">
            <div class="h60 r16-9 flex bg-3 ovh-v ovs-s">
            <?php
            $Banners = json_decode($libsBanners, true);
            foreach ($Banners as $banners) {
            ?>
                <img src="../libsImg/<?php echo $banners;?>" class="posr pad-1 h60 r16-9 objfit" alt="">
            <?php
            };
            ?>
            </div>
            <div class="pad-sl w30p h60 bg-3 flex fld">
                <img src="../libsImg/<?php echo $banners;?>" class="posr bottomMg-s5 w100p r16-9 objfit" alt="">
                <h2 class="pad-nr w100p txt-b"><?php echo $libsTitles?></h2>
                <p class="pad-nr w100p maxh30p txt-s"><?php echo $libsDesc?></p>
                <div class="pad-nr pad-m-v w100p flex">
                    <p class="rightMg-s5 pad-m-v">Tags:</p>
                    <a href="view.php?type=category&ids=<?php echo $category;?>" class="rightMg pad-m c-lightblue"><?php
                        if (isset($catgList)) {
                            echo $catgList;
                        } else {
                            echo "Undefined";
                        };
                        ?></a>
                </div>
                <?php
                if ($signed == true) {
                ?>
                <button class="topMg sideMg bottomMg-s10 pad-s-v w95p bg-5 txt-n bold c-white border-b border-hover-white">MarkOut</button>
                <?php
                } else {
                ?>
                <button class="topMg sideMg bottomMg-s10 pad-s-v w95p bg-1 txt-n bold c-white border-b border-hover-white">SignIn to MarkOut</button>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="sideMg pad-nt w75p flex">
            <github-md class="pad-s w75p bg-3 bora-s" id="markdown-content">
            </github-md>
            <div class="leftMg-s10 pad-s w30p h10 bg-3 flex fld bora-s">
                <div class="posr pad-s w100p flex bg-half-gray">
                    <img src="../../img/logo-github.svg" alt="gh" class="vertiMg rightMg-s10 icon-m objfit">
                    <h2 class="vertiMg w100p txt-s">Github Repository</h2>
                    <a href="<?php echo $libsMds;?>" class="link-cover hover-white">.</a>
                </div>
            </div>
        </div>
    </section>
    <section class="leftMg pad-s w79 h40"></section>
    <script>
        fetch('<?php echo $libsMds;?>')
            .then(response => response.text())
            .then(markdownText => {
                const sanitizedHTML = DOMPurify.sanitize(markdownText);
                document.getElementById('markdown-content').innerHTML = sanitizedHTML;
            })
            .catch(error => {
                console.error('Error loading the Markdown file:', error);
            });
        renderMarkdown();
    </script>
<?php
        break;
    case 'prms':
        # code...
        break;
    
    default:
        break;
}
?>
<!-- messages passer --> 
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <?php include_once '../../extra/footer.php';?>
    <script src="../../scriptstuff/script.js"></script>
    <script src="../../scriptstuff/alert.js"></script>
    <?php
    if (!empty($errors)) {
        echo "<script> ";
        echo "alerter('"; foreach ($errors as $error) {echo $error .";";} echo "')";
        echo "</script>";
    };
    if (!empty($_SESSION['corsmsg'])) {
        $corsmsg = $_SESSION['corsmsg'];
        echo "<script> ";
        echo "alerter('" . $corsmsg . "')";
        echo "</script>";
        $_SESSION['corsmsg'] = "";
    };
    if ($Reqtype === "clts") {
    ?>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.3.1/dist/purify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/showdown@2.1.0/dist/showdown.min.js"></script> 
    <script src="https://cdn.jsdelivr.net/gh/MarketingPipeline/Markdown-Tag/markdown-tag.js"></script>
    <?php
    };
    ?>
</body>
</html>
<?php
require_once '../../processes/database.php';
$errors = array();
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
$root_route = "../../";
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
        } else {
            $_SESSION['corsmsg'] = "category were yet to exist";
            header ('location: ../../index.php');
            exit;
        }
        $check_software = $connects->prepare("SELECT libsIds, JSON_EXTRACT(libsBanners, '$[0]') AS libsBanners, libsPublisher, libsTitles, libsDesc, addedDates, cltNumbs FROM libslist WHERE libsCategorys = ? AND libsState = ? ORDER BY addedDates DESC;");
        $check_software->bind_param("ss", $catgIds, $State);
        $check_software->execute();
        $result_check_software = $check_software->get_result();
        if ($result_check_software->num_rows > 0) {
            while ($value = $result_check_software->fetch_assoc()) {
                $libsIds = $value['libsIds'];
                $libsBanners = $value['libsBanners'];
                $libsPublisher = $value['libsPublisher'];
                $libsBanners = str_replace('"', "", $libsBanners);
                $libsTitles = $value['libsTitles'];
                $libsDesc = $value['libsDesc'];
                $addedDates = $value['addedDates'];
                $cltNumbs = $value['cltNumbs'];
                if (!in_array($libsIds, $tempLibsArr)) {
                    $tempLibsArr[$libsIds] = [
                    "libsIds"        => "$libsIds",
                    "libsPublisher"  => "$libsPublisher",
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
        break;
    case 'clts':
        $specifics = "collection";
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
            $value = $result_check_software->fetch_assoc();
            $libsIds = $value['libsIds'];
            $libsPublisher = $value['libsPublisher'];
            $libsVT = $value['libsVT'];
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
            $recspecs = $value['recspecs'];
            $extlinkArr = json_decode($value['extlink'], true);
            $catgList = $tempCatgArray[$category] ?? null;
            $check_groups = $connects->prepare("SELECT names, sites FROM ogroup WHERE identification = ? ;");
            $check_groups->bind_param("s", $libsPublisher);
            $check_groups->execute();
            $result_check_groups = $check_groups->get_result();
            $result_groups = $result_check_groups->fetch_assoc();
            $libsPNames = $result_groups['names'];
            $sites = json_decode($result_groups['sites'], true);
            foreach ($sites as $siteIndex => $siteData) {
                $sitesArr[0] = [
                    "site"  => $siteData["site"], 
                    "yt"    => $siteData["yt"]
                ];
            }
            if (empty($libsVT) || $libsVT === "" || $libsVT === "empty") {
                $libsVT = null;
            }
        } else {
            $_SESSION['corsmsg'] = "no collection were found";
            header ('location: ../../index.php');
            exit;
        };
        $marked = [];
        if ($signed == true) {
            $check_profile = $connects->prepare("SELECT mkot FROM profiles WHERE profileTags = ? ;");
            $check_profile->bind_param("s", $aidis);
            $check_profile->execute();
            $result_check_profile = $check_profile->get_result();
            if ($result_check_profile->num_rows == 1) {
                $value = $result_check_profile->fetch_assoc();
                $mkot = $value['mkot'];
                $data = json_decode($mkot, true);
                $markedData = $data['marked'];
                if (!empty($markedData) && $markedData != "empty") {
                    foreach ($markedData as $Index => $markedAppend) {
                        $marked[$Index] = [
                            "Hours"    => (int)$markedAppend['Hours'],
                            "lastLog"  => $markedAppend['lastLog']
                        ];
                        $indexes[$Index] = $Index;
                    }
                }
            };
        }
        break;
    case 'prms':
        $stmt_check_prms = $connects->prepare("SELECT * FROM prms WHERE prmsIds = ? AND prmState = 'active';");
        $stmt_check_prms->bind_param("s", $targetIds);
        $stmt_check_prms->execute();
        $result_check_prms = $stmt_check_prms->get_result();
        if ($result_check_prms->num_rows > 0) {
            $value = $result_check_prms->fetch_assoc();
            $prmsIds = $value['prmsIds'];
            $bannerRefImg = $value['bannerRefImg'];
            $prmsArr = $value['prmsArr'];
            $type = $value['type'];
            $refLinks = $value['refLinks'];
            $bannerDates = $value['bannerDates'];
            $uniTitles = $bannerDates;
            if ($type === "sftprms") {
                header ('location: view.php?type=clts&ids='.$refLinks);
            } else if ($type === "client") {
                header ('location: ../../client.php?reqs='.$refLinks);
            }
        } else {
            $_SESSION['corsmsg'] = "The event no longer exist";
            header ('location: ../../index.php');
            exit;
        }
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
    <title><?php echo $uniTitles;?> || CrossGate</title>
</head>
<body class="wh100p">
<img src="../../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity05 z1">
<!-- the nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="../../img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="../../index.php" class="link-cover">.</a>
        </div>
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
                <a href="../../connect_it/connect_it.php?state=login" class="link-cover">.</a>
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
    <section class="topMg-5 w100p flex fld z2">
        <div class="bottomMg-s10 sideMg w75p flex">
            <div class="w100p h30 flex acjc bgc-lightpurple border-1">
                <h2 class="w100p txt-30 txtc"><?php echo $catgTitles?></h2>
            </div>
        </div>
        <div class="sideMg w75p minh40 flex fld gap5">
            <?php
            if ($nolibs === "no") {
            ?>
                <h2 class="pad-sb w100p txt-n">Released on '<?php echo $catgTitles;?>'</h2>
                <?php
                foreach ($tempLibsArr as $id => $value) {
                    $libsIds = $value['libsIds'];
                    $libsPublisher = $value['libsPublisher'];
                    $libsBanners = $value['libsBanners'];
                    $libsTitles = $value['libsTitles'];
                    $libsDesc = $value['libsDesc'];
                    $addedDates = $value['addedDates'];
                    $cltNumbs = $value['cltNumbs'];
                    ?>
                <div class="posr pad-s w100p flex gap5 border-1">
                    <img src="../libsImg/<?php echo $libsPublisher . "/" . $libsBanners;?>" class="h10 r16-9 coverfit">
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
<?php include_once '../../reportTab.php';
if ($signed == true) {
?>
    <div class="posf pad-n b0 r0 flex z999">
        <img src="../../img/warning.svg" alt="" class="posr icon-n containfit bg-half-white opacity3 hover-visible points" onclick="uniDisplaySwitch('reportDialog'); uniLoad(this, 'reportForm');" data-reportsource="collections" data-ids="<?php echo $libsIds;?>">
    </div>
<?php
}
?>
    <section class="topMg-5 w100p flex fld z2">
        <div class="sideMg pad-sb w75p flex border-custom-b">
            <div class="w75p h60 r16-9 flex bg-3 ovh-v">
            <?php
            if (isset($libsVT)) {
            ?>
                <iframe class="posr h100p r16-9" src="https<?php echo $libsVT;?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            <?php
            };
            $Banners = json_decode($libsBanners, true);
            $firstBanners = $Banners[0];
            foreach ($Banners as $banners) {
            ?>
                <img src="../libsImg/<?php echo $libsPublisher . "/" . $banners;?>" class="posr h100p r16-9 coverfit" alt="">
            <?php
            };
            ?>
            </div>
            <div class="pad-sl w30p h60 bg-3 flex fld gap5">
                <img src="../libsImg/<?php echo $libsPublisher . "/" . $firstBanners;?>" class="posr w100p r16-9 coverfit" alt="">
                <h2 class="pad-nr w100p txt-b"><?php echo $libsTitles?></h2>
                <p class="pad-nr w100p maxh30p txt-s"><?php echo $libsDesc?></p>
                <a href="../../Groups/profile.php?gids=<?php echo $libsPublisher;?>" class="pad-st pad-nr w100p maxh30p">By: <span><?php echo $libsPNames?></span></a>
                <div class="pad-nr w100p flex">
                    <p class="rightMg-s5">Tags:</p>
                    <a href="view.php?type=category&ids=<?php echo $category;?>" class="rightMg pad-m-s c-lightpurple">
                    <?php
                        if (isset($catgList)) {
                            echo $catgList;
                        }
                        ?></a>
                </div>
                <?php
                if ($signed == true) {
                    if (isset($indexes) ) {
                        if (!in_array($targetIds, $indexes)) {
                ?>
                <form class="topMg sideMg bottomMg-s10 w95p flex" name="markingout" action="../../processes/markout.php" method="post">
                    <input class="hiddeninp" type="text" name="libsIds" value="<?php echo $targetIds?>" hidden>
                    <input class="pad-s-v w100p txt-n txtc bold bg-1 c-white box-shad-black-1 border-1 bora-s border-hover-white" type="submit" name="MarkOut" value="MarkOut">
                </form>
                <?php
                        } else {
                ?>
                <a href="markout.php" class="topMg sideMg bottomMg-s10 pad-s-v w95p bg-green txt-n txtc bold c-white box-shad-black-1 border-1 bora-s border-hover-white">MarkedOut</a>
                <?php
                        }
                    } else {
                ?>
                <form class="topMg sideMg bottomMg-s10 w95p flex" name="markingout" action="../../processes/markout.php" method="post">
                    <input class="hiddeninp" type="text" name="libsIds" value="<?php echo $targetIds?>" hidden>
                    <input class="pad-s-v w100p txt-n txtc bold bg-1 c-white box-shad-black-1 border-1 bora-s border-hover-white" type="submit" name="MarkOut" value="MarkOut">
                </form>
                <?php
                    }
                } else {
                ?>
                <a href="../../connect_it/connect_it.php?state=login" class="topMg sideMg bottomMg-s10 pad-s-v w95p bg-1 txt-n txtc bold c-white box-shad-black-1 border-1 border-hover-white">SignIn to MarkOut</a>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="posr sideMg pad-st w75p flex">
            <github-md class="posr pad-s w75p bg-3 bora-s" id="markdown-content">
            </github-md>
            <div class="leftMg-s10 bottomMg pad-n-v pad-s-s w30p bg-3 flex fld bora-s">
                <?php
                if ($signed == true) {
                    foreach ($marked as $Index => $data) {
                        if ($targetIds === $Index) {
                            $Hours = $data['Hours'];
                            $lastLog = $data['lastLog'];
                ?>
                <div class="posr bottomMg-s10 pad-s w100p flex fld bgc-purple box-shad-black-1 border-1 bora-s">
                    <h2 class="bottomMg-s5 w100p txt-n bold">MarkedStats</h2>
                    <p class="txt-s">Total Time: <?php echo $Hours;?> Hours</p>
                    <p class="txt-s">Last Login: <?php echo $lastLog;?></p>
                </div>
                <?php
                        }
                    }
                }
                ?> 
                <div class="posr bottomMg-s10 pad-s w100p flex bg-half-gray box-shad-black-1 bora-s ovh">
                    <img src="../../img/logo-github.svg" alt="gh" class="vertiMg rightMg-s10 icon-m containfit">
                    <h2 class="vertiMg w100p txt-s">Github Repository</h2>
                    <a href="<?php echo $libsMds;?>" class="link-cover hover-white" target="_blank" rel="noopener noreferrer">.</a>
                </div>
                <?php
                    foreach ($sitesArr as $sIndex => $value) {
                        $site = $value["site"];
                        $yt = $value["yt"];
                        if ($site != "") {
                ?>
                <div class="posr pad-s bottomMg-s5 w100p flex flex bgc-purple box-shad-black-1 bora-s ovh">
                    <h2 class="vertiMg w100p txt-s"><?php echo $libsPNames;?> Website</h2>
                    <a href="https://<?php echo $site;?>" class="link-cover hover-white" target="_blank" rel="noopener noreferrer">.</a>
                </div>
                <?php
                        }
                    }
                    foreach ($extlinkArr as $sIndex => $vals) {
                        if (!empty($sIndex)) {
                            $linkVal = $vals[0];
                ?>
                <div class="posr pad-s bottomMg-s5 w100p flex flex bgc-purple box-shad-black-1 bora-s ovh">
                    <h2 class="vertiMg w100p txt-s"><?php echo $sIndex;?></h2>
                    <a href="https://<?php echo $linkVal;?>" class="link-cover hover-white" target="_blank" rel="noopener noreferrer">.</a>
                </div>
                <?php
                        }
                    }
                    switch ($recspecs) {
                        case '24a2w11u22':
                ?>
                <div class="posr bottomMg-s10 pad-s w100p flex fld bg-half-gray box-shad-black-1 bora-s ovh">
                    <h2 class="bottomMg-s5 w100p txt-s">Recommended System Spec</h2>
                    <p class="bottomMg-s5 w100p txt-s">CPU: <span>2 Core/Thread</span></p>
                    <p class="bottomMg-s5 w100p txt-s">RAM: <span>4GB DDR4</span></p>
                    <p class="bottomMg-s5 w100p txt-s">GPU: <span>GTX 960 2GB/ RX 460 2GB</span></p>
                </div>
                <div class="posr bottomMg-s10 pad-s w100p flex fld bg-half-gray box-shad-black-1 bora-s ovh">
                    <h2 class="bottomMg-s5 w100p txt-s">Recommended OS</h2>
                    <p class="bottomMg-s5 w100p txt-s">Windows: <span>10/11</span></p>
                    <p class="bottomMg-s5 w100p txt-s">Linux: <span>Ubuntu 22.04.5 LTS</span></p>
                </div>
                <?php
                            break;
                        case '48a4w10u22':
                ?>
                <div class="posr bottomMg-s10 pad-s w100p flex fld bg-half-gray box-shad-black-1 bora-s ovh">
                    <h2 class="bottomMg-s5 w100p txt-s">Recommended System Spec</h2>
                    <p class="bottomMg-s5 w100p txt-s">CPU: <span>4 Core/Thread</span></p>
                    <p class="bottomMg-s5 w100p txt-s">RAM: <span>8GB DDR4</span></p>
                    <p class="bottomMg-s5 w100p txt-s">GPU: <span>GTX 1050 4GB/ RX 570 4GB</span></p>
                </div>
                <div class="posr bottomMg-s10 pad-s w100p flex fld bg-half-gray box-shad-black-1 bora-s ovh">
                    <h2 class="bottomMg-s5 w100p txt-s">Recommended OS</h2>
                    <p class="bottomMg-s5 w100p txt-s">Windows: <span>10</span></p>
                    <p class="bottomMg-s5 w100p txt-s">Linux: <span>Ubuntu 22.04.5 LTS</span></p>
                </div>
                <?php
                            break;
                        default:
                            break;
                    }
                ?>
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
        setTimeout(renderMarkdown(), 1500);
    </script>
<?php
        break;
    case 'prms':
?>
    <section class="w100p minh100 flex fld z2">
        <div class="w100p flex">
            <img src="../libsImg/<?php echo $bannerRefImg;?>" class="w100p containfit">
        </div>
        <div class="w100p flex fld ovh-s ovs-v">
        <?php
        $prmsArr = json_decode($prmsArr, true);
        foreach ($prmsArr as $value) {
        ?>
            <img src="../libsImg/<?php echo $value;?>" class="posr w100p containfit" alt="">
        <?php
        };
        ?>
        </div>
    </section>
<?php
        break;
    default:
        break;
}
?>
<!-- messages alerter --> 
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
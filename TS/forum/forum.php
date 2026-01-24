<?php
require_once '../../processes/database.php';
$errors = array();
session_start();
if (isset($_SESSION['profileTags'])) {
    $aidis = $_SESSION['profileTags'];
};
if (!isset($_GET['ids'])) {
    header ('location: dashboard.php');
    exit;
}
$fids = $_GET['ids'];
$fids = htmlspecialchars($fids, ENT_QUOTES, 'UTF-8');
$page = "forum";
$paramsubpage = "ids";
$subpage = $fids;
$UploadEnabled = "no";
$SearchEnabled = "yes";
$ForumState = "Publics";
if (isset($_GET['item']) && isset($_GET['onsearch'])) {
    $searchTrigger = $_GET['onsearch'];
    $requestedItem = $_GET['item'];
} else {
    $requestedItem = "empty";
};
$requestedItem = htmlspecialchars($requestedItem, ENT_QUOTES, 'UTF-8');
$stmt_check_forums = $connects->prepare("SELECT * FROM forums WHERE ForumState = ? AND ForumIds = ? ORDER BY ForumDates ASC;");
$stmt_check_forums->bind_param("ss", $ForumState, $fids);
$stmt_check_forums->execute();
$result_check_forums = $stmt_check_forums->get_result();
if ($result_check_forums->num_rows == 1) {
    $value = $result_check_forums->fetch_assoc();
    $creators = $value['ForumCreator'];
    $titles = $value['ForumTitles'];
    $topics = $value['ForumTopics'];
    $dates = $value['ForumDates'];
    $descs = $value['ForumContents'];
    $attachs = $value['ForumAttachment'];
} else {
    $_SESSION['corsmsg'] = "The forum you try to open does not exist";
    header('location: dashboard.php');
    exit;
};

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
    <title><?php echo $titles;?></title>
</head>
<body>
<!-- the nav -->
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 blurbg z4">
        <a href="index.php" class="vertiMg pad-s txt-l semibold">CROSSGATE</a>
        <div class="posr w60p flex gap-s">
            <?php
            if (isset($aidis)) {
                ?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="../../Library/core/markout.php" class="link-cover">.</a>
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
                <a href="../../Library/core/category.php" class="link-cover">.</a>
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
<!-- forum content -->
    <div class="pad-n w100p minh100 flex fld">
        <h1 class="sideMg bottomMg-s5 w95p txt-b"><?php echo $titles;?></h1>
        <?php
        $getUser = $connects->prepare("SELECT profileNames FROM profiles WHERE profileTags = ?");
        $getUser->bind_param("s", $creators);
        $getUser->execute();
        $resultGetUser = $getUser->get_result();
        if ($resultGetUser->num_rows == 1) {
            $getname = $resultGetUser->fetch_assoc();
        ?>
            <a href="../../profile.php?user=<?php echo $creators; ?>" class="sideMg w95p txt-s"><?php echo $getname['profileNames'];?> | <?php echo $dates; ?></a>
        <?php
        };
        ?>
        <?php
        if ($attachs != "empty.png" && isset($attachs)) {
        ?>
        <h2 class="sideMg topMg-s10 w95p"><?php echo $descs;?></h2>
        <img src="../ArchFiles/<?php echo $attachs;?>" alt="<?php echo $titles;?>" class="sideMg topMg-s10 w50p r16-9 objfit border-1">
        <?php
        } else {
        ?>
        <h2 class="sideMg topMg-s10 w95p minh30"><?php echo $descs;?></h2>
        <?php
        };
        ?>
        <form action="../component/post_out.php" method="post" class="topMg-s10 pad-s-v w95p sideMg flex border-b border-t gap-s">
            <input type="text" name="fids" value="<?php echo $fids;?>" required hidden>
            <input type="text" name="usrIds" value="<?php echo $aidis;?>" required hidden>
            <input type="text" name="cmtUser" value="<?php echo $name;?>" required hidden>
            <input type="text" name="cmtContnt" class="pad-m w88 c-black bora-s" placeholder="Leave a reply..." auto-complete="off" maxlength="2000" required>
            <input class="w10p c-black bora-s" type="submit" name="submit" value="comment">
        </form>
        <div class="sideMg topMg-s10 w95p flex fld">
        <?php
        if (isset($requestedItem) && isset($searchTrigger)) {
        $stmt_check_comments = $connects->prepare("SELECT * FROM forumcomments WHERE ForumIds = ? AND Comments LIKE '%$requestedItem%' ORDER BY CommentDates DESC;");
        $stmt_check_comments->bind_param("s", $fids);
        } else {
        $stmt_check_comments = $connects->prepare("SELECT * FROM forumcomments WHERE ForumIds = ? ORDER BY CommentDates DESC;");
        $stmt_check_comments->bind_param("s", $fids);
        };
        $stmt_check_comments->execute();
        $result_check_comments = $stmt_check_comments->get_result();
        if ($result_check_comments->num_rows > 0) {
            $uniqueItem = [];
            while ($value = $result_check_comments->fetch_assoc()) {
                $Cids = $value['CommentIds'];
                $Tags = $value['profileTags'];
                $Names = $value['profileNames'];
                $Comments = $value['Comments'];
                $Cdates = $value['CommentDates'];
                if (!in_array($Cids, $uniqueItem)) {
        ?>
            <div class="pad-n-v flex fld gap5">
                <div class="flex">
                    <a href="../../profile.php?user=<?php echo $Tags;?>"><?php echo $Names;?></a>
                    <span>|</span>
                    <p><?php echo $Cdates;?></p>
                </div>
                <p class="comment-content"><?php echo $Comments;?></p>
            </div>
        <?php
                };
            };
        }else{
        ?>
                <h2 class="posr pad-s w100p h20 txtc z2">be the first one to reply this</h2>
        <?php
        };
        ?>
        </div>
    </div>
</div>
<!-- lil bit of messages passer --> 
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
    ?>
</body>
</html>
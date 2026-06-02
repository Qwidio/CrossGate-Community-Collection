<?php
require_once '../processes/database.php';
$errors = array();
$root_route = "../";
if (isset($_SESSION['profileTags'])) {
    require_once '../secureSession.php';
    require_once '../Groups/ReAuth.php';
    $aidis = $_SESSION['profileTags'];
}
if (isset($_SESSION['GroupsToken'])) {
    $gToken = $_SESSION['GroupsToken'];
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
    <title>Documentation</title>
</head>
<body class="gap10 ovh-s z1" id="intro">
    <div class="posr pad-n-s w100p minh10 flex gap-s bg-4 z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="../img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="../index.php" class="link-cover">.</a>
        </div>
        <div class="posr w60p flex gap-s">
            <div class="posr pad-s flex fld acjc">   
                <h2 class="txt-n txtc semibold">BROWSE</h2>
                <a href="../Library/core/list.php" class="link-cover">.</a>
            </div>
<?php
if (isset($aidis)) {
?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">MARKOUT</h2>
                <a href="../Library/core/markout.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">PROFILE</h2>
                <a href="../profile.php?user=self" class="link-cover">.</a>
            </div>
<?php
    $prebind = '"' . $aidis . '"';
    $check_orgs = $connects->prepare("SELECT identification FROM ogroup WHERE founder = ? OR JSON_CONTAINS(members, ?);");
    $check_orgs->bind_param("ss", $aidis, $prebind);
    $check_orgs->execute();
    $result_check_orgs = $check_orgs->get_result();
    if ($result_check_orgs->num_rows > 0) {
        $value = $result_check_orgs->fetch_assoc();
        $identification = $value['identification'];
?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">GROUPS</h2>
                <a href="../Groups/index.php" class="link-cover">.</a>
            </div>
<?php
    }
}
?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="../TS/forum/dashboard.php" class="link-cover">.</a>
            </div>
        </div>
<?php
if (isset($aidis)) {
    $inGroups = false;
    if (isset($_SESSION['GroupsToken']) && $_SESSION['gids']) {
        $check_orgs = $connects->prepare("SELECT og_identification FROM groupaccess WHERE profileTags = ? AND og_identification = ?;");
        $check_orgs->bind_param("ss", $aidis, $_SESSION['gids']);
        $check_orgs->execute();
        $result_check_orgs = $check_orgs->get_result();
        if ($result_check_orgs->num_rows > 0) {
?>
            <div class="leftMg flex acjc gap10">
                <p class="posr pad-n-s pad-s-v txtc txt-n bg-3 border-1 bora-s">Open Dashboard
                    <a href="../Groups/manage.php" class="link-cover">.</a>
                </p>
            </div>
<?php
        }
        $inGroups = true; 
    }
}
?>
    </div>
    <section class="posr sideMg w100vh minw50 maxw100 flex border-1 ovh-s z4">
        <div class="posr w30p minh100 flex fld border-r gap10 ovh-s">
            <a href="#intro" class="pad-n txt-n border-b bold hover-text-orange">Introduction</a>
            <div class="pad-n-s w100p flex fld border-b">
                <a class="pad-sb w100p txt-n bold hover-text-orange" href="#account">Account</a>
                <a href="#accountPrtg" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Profile Tags</a>
                <a href="#settInv" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Settings & Invite</a>
                <a href="#accountSession" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Session</a>
                <a href="#issuesnsln" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Issues and possible solution</a>
            </div>
            <div class="pad-n-s w100p flex fld border-b">
                <a class="pad-sb w100p txt-n bold hover-text-orange" href="#LibForum">Library & Forum</a>
                <a href="#markout" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">MarkOut</a>
                <a href="#forumposting" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Forum posting</a>
            </div>
            <div class="pad-n-s w100p flex fld border-b">
                <a class="pad-sb w100p txt-n bold hover-text-orange" href="#groups">Groups</a>
                <a href="#registrat" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Registration</a>
                <a href="#accessath" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Access authority</a>
                <!-- <a href="#groupmanage" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Management</a> -->
                <a href="#publishing" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Publishing</a>
                <a href="#uploadingfile" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Uploading File</a>
                <a href="#community" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Community</a>
            </div>
            <div class="pad-n-s w100p flex fld">
                <a class="pad-sb w100p txt-n bold hover-text-orange" href="#api">API & Client</a>
                <a href="#cstate" class="pad-s-s pad-r pad-sb txt-s hover-text-orange">Current situation</a>
            </div>
        </div>
        <div class="posr w80p minh100 flex fld gap10 ovh-s">
            <div class="posr w100p flex fld gap5">
                <p class="posr pad-n-v pad-s-s txt-l border-b">CrossGate Documentation</p>
                <h2 class="posr topMg-s5 pad-s-s txt-b">Introduction</h2>
                <p class="posr pad-n-s txt-n">CrossGate is an Software & Game distribution platform with community forum open for everyone. This documentation create to asnwer some of the question and "howto" related to the project</p>
            </div>
            <div class="posr pad-n-v w100p flex fld border-b gap5" id="account">
                <h2 class="posr pad-s-s pad-sb txt-l border-b">Account</h2>
                <h2 class="posr topMg-s5 pad-s-s txt-b" id="accountPrtg">Profile Tags</h2>
                <p class="posr pad-n-s txt-n">"What is this for?": Your account unique identifier used for everything in this website, from forum post to MarkOut collection</p>
                <h2 class="posr topMg-s5 pad-s-s txt-b" id="settInv">Settings & Invites</h2>
                <p class="posr pad-n-s txt-n">Opening settings panel:  make sure you already logged in, go to profile page by clicking "profile" button from the top bar and the page should look like this</p>
                <img src="profile.png" class="posr pad-n-s w100p r16-9 containfit" alt="">
                <img src="profilesettingbutton.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr topMg-s5 pad-n-s txt-n">Click on settings button and it will show settings panel like below,</p>
                <img src="profilesetting.png" class="posr pad-n-s w100p r16-9 containfit" alt="">
                <p class="posr pad-n-s txt-n">The top panel is the settings option, click on "Update Settings" to save settings selection. Bottom panel are invitation from Groups, "message" button allow you to see messages sent by the inviter and Accept or Dismiss the Invites with the "Join" and "Dismiss" button.<br>Notes: It's recommended to only join one Groups at a time as it's never been properly tested to support multiple groups of one user.</p>
                <h2 class="posr topMg-s5 pad-s-s txt-b" id="accountSession">Session</h2>
                <p class="posr pad-n-s txt-n">Session are a persistent login token saved on local data for an extended time period, this ensure that user stayed logged to the website without needing to relogin after closing the browser</p>
                <h2 class="posr topMg-s5 pad-s-s txt-b" id="issuesnsln">Issues and possible solution</h2>
                <img src="kmsi.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">"Your account exceeds the number of session allowed": Tick off the "keep me signed in" checkbox before hitting the "login" button</p>
                <p class="posr topMg-s5 pad-n-s txt-n">"How to add new session?": on your profile page click on "the session manager" button,</p>
                <img src="profilefs.png" class="posr pad-n-s w100p r16-9 containfit" alt="">
                <p class="posr pad-n-s txt-n">click "add new session" button like shown below, if instead it writes "Maximum session allowed" that means you must at least delete one of the existing session before adding new one</p>
                <img src="sessionpg.png" class="posr bottomMg-s10 pad-n-s w100p containfit" alt="">

                <h2 class="posr topMg-s10 pad-s-s pad-sb txt-l border-b">Collection & Forum</h2>
                <h2 class="posr topMg-s5 pad-s-s txt-b" id="markout">MarkOut Collection</h2>
                <img src="markingout.png" class="posr pad-n-s w100p r16-9 containfit" alt="">
                <p class="posr pad-n-s txt-n">"How to add collection to my MarkOut page?": open view page of the said collection, if it shows like this then you can see the "MarkOut" button there. Click it and you'll be directed to MarkOut page after the collection get added</p>
                <p class="posr topMg-s5 pad-n-s txt-n">"What is it used for?": simply put that if you want to download a software/games listed on here, then it must first get added to MarkOut before it will showing up on download manager</p>
                <h2 class="posr topMg-s10 pad-s-s txt-b" id="forumposting">Forum Posting</h2>
                <img src="postnewforum.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">Go to forum dashboard and click "post new forum" button</p>
                <img src="forumdashboard.png" class="posr pad-n-s w100p r16-9 containfit" alt="">
                <p class="posr pad-n-s txt-n">Fill in the title and description, images are optional and aren't needed for posting new forum</p>

                <h2 class="posr topMg-s10 pad-s-s pad-sb txt-l border-b" id="groups">Groups</h2>
                <h2 class="posr topMg-s5 pad-s-s txt-b" id="registrat">Registration</h2>
                <img src="groupsfooter.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">Go to bottom this website footer and click on "Groups" link, click on "Create new Groups" below the "Sign In" button and the page will open the registration form</p>
                <img src="createnewgrouplink.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">Fill all input, name of the groups, description of the groups, the passkeys and the confirm input below it are for your account access so make sure to not forget it.</p>
                <img src="registerform.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr topMg-s5 pad-n-s txt-n">After the registration are complete you'll be directed back to the Login Form, your username are also your new groups account username.</p>
                <h2 class="posr topMg-s10 pad-s-s txt-b" id="accessath">Access authority</h2>
                <img src="dashboard.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr topMg-s5 pad-n-s txt-n">There currently Three[3] access authority applied to the Groups-Flow, Founder, Administrator and Developer with each of the last two get separated access on some page</p>
                <p class="posr pad-n-s txt-n">Founder, Administrator and Developer all can access dashboard but only the Founder given the authority to change groups profile detail, invite new members, revoke access, edit members passkeys and remove members while the rest can see the list and detail but not editing other that their own passkeys.</p>
                <p class="posr pad-n-s txt-n">Administrator and Developer have their unique access to some of the groups changing feature.<br>
                    Administrator access allowed to make announcement post and moderating announcement topic.<br>
                    Developer granted access to nearly all publishing features, from creating new collection, editing detail to file management.</p>
                <h2 class="posr topMg-s10 pad-s-s txt-b" id="publishing">Publishing</h2>
                <p class="posr pad-n-s txt-n">From the groups dashboard click on "publishing" button from the top bar and the page will be redirected to the publishing menu</p>
                <img src="publishing.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">"New collection" does exactly what it says and will open the collection creation panel, the required input in this form are logo, banners, title, short description, repository link(for default readme linking), type, category, and status.</p>
                <img src="publishingcreate.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">Trailer link are for displaying your collection video demo/trailer on the view pages, be aware that currently it's only tested with link for Youtube video embedding format.</p>
                <p class="posr pad-n-s txt-n">Filled out external link "name" and "link" will be displayed your collection view page, up to ten link can exist in one collection.</p>
                <p class="posr pad-n-s txt-n">After successfully created the new collection is saved and visible as "draft" collection, to publish or archive the collection click "change state" button and the two option will be visible. Note that collection software file must uploaded before changing the state to "Publics"</p>
                <h2 class="posr topMg-s10 pad-s-s txt-b" id="uploadingfile">Uploading & managing file</h2>
                <img src="filemanagerbtn.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">Open file manager for the collection that the software wanted to be uploaded, click on "upload" button on the top right and it will shows upload form like below</p>
                <img src="filemanagerupload.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">After uploading the files will automaticatlly set as active to the collection, the current files used by the collection will be marked by a green border.</p>
                <img src="filemanageractive.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">setting another existing file to be the "active" collection file can be done via selecting the file and click on "set active" button. the same goes for removing another file with the note that file must not be currently used by any of your collection</p>
                <p class="posr pad-n-s txt-n">Publishing collection with "Archived" state will require you to draft it first before it can be published</p>
                <h2 class="posr topMg-s10 pad-s-s txt-b" id="community">Community</h2>
                <p class="posr pad-n-s txt-n">Each collection given it's own topic dedicated to the groups members, below top bar are the option to post new annoucement along with changing title and description of the topic</p>
                <img src="communitymanage.png" class="posr pad-n-s w100p containfit" alt="">
                <p class="posr pad-n-s txt-n">If a Collections state set to "Archived" or "Drafted" the community topics binded to the collection will no longer visible on the topic list but still accessible via link</p>
                <h2 class="posr topMg-s10 pad-s-s pad-sb txt-l border-b" id="api">Api & Client</h2>
                <h2 class="posr topMg-s10 pad-s-s txt-b" id="cstate">Current situation</h2>
                <p class="posr pad-n-s txt-n">At the time of writing API and Client were not ready for use until later in the coming month</p>
            </div>
            <div class="posr pad-n-v pad-s-s w100p flex fld gap5">
                <h2 class="w100p txt-b">Reference link</h2>
                <div class="w100p flex wrap gap10">
                    <a href="https://github.com/MarketingPipeline/Markdown-Tag" class="txt-s txt-hlb">Markdown Tag</a>      
                    <a href="https://github.com/cure53/DOMPurify" class="txt-s txt-hlb">DOMPurify</a>
                </div>
            </div>
        </div>
    </section>
    <?php include_once '../extra/footers.php';?>
</body>
</html>
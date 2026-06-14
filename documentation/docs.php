<!-- shameful, I tried to use AI to help me making better docs but it just spit out garbage nonsense that makes me do twice the amount of work -->

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
    <title>CGCC Documentation</title>
    
    <style>
        /* Positioning & Layout */
        .top-95px { top: 95px; }
        .flex-1 { flex: 1; }
        
        /* Sizing */
        .w280px { width: 280px; }
        .h70px { height: 70px; }
        .maxw-1400px { max-width: 1400px; }
        .minh-100vh { min-height: 100vh; }
        .maxh-80vh { max-height: calc(100vh - 120px); }
        .maxh-400px { max-height: 400px; }

        /* Spacing & Margins */
        .margin-auto { margin: 0 auto; }
        .gap-5 { gap: 5px; }
        .gap-40 { gap: 40px; }
        .gap-35 { gap: 35px; }
        .pad-top-75px { padding-top: 75px; }
        .pad-l-l { padding: 30px; }
        .botMg-s60 { margin-bottom: 60px; }
        .botMg-s35 { margin-bottom: 35px; }
        .botMg-s20 { margin-bottom: 20px; }
        .botMg-s15 { margin-bottom: 15px; }
        .botMg-s10 { margin-bottom: 10px; }
        .botMg-s5 { margin-bottom: 5px; }
        .botMg-s2 { margin-bottom: 2px; }
        .topMg-s25 { margin-top: 25px; }

        /* Responsive Wrappers */
        @media (max-width: 900px) {
            .sm-verti { flex-direction: column !important; }
            .sm-w100p { width: 100% !important; max-width: 100% !important; }
            .sm-posr { position: relative !important; top: 0 !important; height: auto !important; }
        }
    </style>
</head>

<body class="pad-bt minh-100vh bg-0" id="intro">
    <div class="posf lt0 pad-n-s w100p minh10 flex gap-s bg-4 z4">
        <div class="posr vertiMg leftMg-s10 rightMg-s10 h5 flex fld acjc">
            <img src="../img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="../index.php" class="link-cover">.</a>
        </div>
        <div class="posr w60p flex gap-s">
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
} else {
?>
            <div class="posr pad-s flex fld acjc">   
                <h2 class="txt-n txtc semibold">BROWSE</h2>
                <a href="../Library/core/list.php" class="link-cover">.</a>
            </div>
<?php
}
?>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">FORUM</h2>
                <a href="../TS/forum/dashboard.php" class="link-cover">.</a>
            </div>
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">CHANGELOG</h2>
                <a href="changelog.php" class="link-cover">.</a>
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
                    <a href="../Groups/manage.php" class="link-cover hover-white">.</a>
                </p>
            </div>
<?php
        }
        $inGroups = true; 
    }
}
?>
    </div>

    <main class="autoMg topMg-10 w95p maxw-1400px botMg-s60 flex gap-40 sm-verti">
        
        <aside class="w280px pos-s top-95px maxh-80vh custom-scrollbar sm-w100p sm-posr pad-s-s ovh-s">
            
            <div class="botMg-s20 pad-bot-s15 flex fld gap5 border-bot-1-subtle-dark">
                <a href="#intro" class="block pad-s bora-s txt-s bold c-highlight trs200ms hover-bg-subtle botMg-s2 hover-pl-16">Introduction</a>
            </div>
            
            <div class="botMg-s20 pad-bot-s15 flex fld border-bot-1-subtle-dark">
                <p class="txt-b uppercase letter-sp-1 c-lightpurp botMg-s10 leftMg-s10">Account Guide</p>
                <a href="#accountPrtg" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Profile Tags</a>
                <a href="#settInv" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Settings & Invites</a>
                <a href="#accountSession" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Sessions</a>
                <a href="#issuesnsln" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Troubleshooting</a>
            </div>
            
            <div class="botMg-s20 pad-bot-s15 flex fld border-bot-1-subtle-dark">
                <p class="txt-b uppercase letter-sp-1 c-lightpurp botMg-s10 leftMg-s10">Library & Forum</p>
                <a href="#markout" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">MarkOut</a>
                <a href="#forumposting" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Forum Posting</a>
            </div>
            
            <div class="botMg-s20 pad-bot-s15 flex fld border-bot-1-subtle-dark">
                <p class="txt-b uppercase letter-sp-1 c-lightpurp botMg-s10 leftMg-s10">Groups Management</p>
                <a href="#registrat" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Registration</a>
                <a href="#accessath" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Access Authority</a>
                <a href="#publishing" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Publishing</a>
                <a href="#uploadingfile" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">File Management</a>
                <a href="#community" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Community Hub</a>
            </div>
            
            <div class="botMg-s20 pad-bot-s15 flex fld border-bot-1-subtle-dark">
                <p class="txt-b uppercase letter-sp-1 c-lightpurp botMg-s10 leftMg-s10">API & Client</p>
                <a href="#cstate" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Current Limitations</a>
                <a href="#obtainapi" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Obtaining API keys</a>
                <a href="#userestric" class="block pad-s bora-s txt-s c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">Usage & restriction</a>
            </div>
        </aside>

        <section class="flex-1 flex fld verti gap-35 sm-w100p">
            
            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m">
                <h2 class="c-lightpurp txt-l border-bot-1-subtle pad-bot-s10 botMg-s20">CrossGate Documentation</h2>
                <p class="c-subtle line-h-1-6 txt-n botMg-s15">CrossGate is an open-source software and game distribution ecosystem featuring an open community forum platform. This documentation serves to solve common issues and question while also providing information about website development workflows & configuration.</p>
            </div>

            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m" id="account">
                <h2 class="c-lightpurp txt-l border-bot-1-subtle pad-bot-s10 botMg-s20">Account</h2>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="accountPrtg">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Profile Tags</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15"><span class="bg-primary-10 c-white pad-s-s bora-s bold">What is this for?</span> Your Profile Tag acts as your unique identity token utilized for relational connections across website and it's services, from forum posting to MarkOut collection</p>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="settInv">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Settings & Invites</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Before opening panel make sure that you've already logged in, go to profile page by clicking "profile" button from the navigation bar and the page display like this</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="profile.png" class="w100p maxh50 containfit bora-s" alt="Profile Preview"></div>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="profilesettingbutton.png" class="w100p maxh50 containfit bora-s" alt="Settings Toggle"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Clicking the system control reveals the extended parameter panels shown below:</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="profilesetting.png" class="w100p maxh50 containfit bora-s" alt="Settings Form"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">The upper module manages your settings preferences. Save changes directly by triggering the <strong>"Update Settings"</strong> buttons. The lower terminal display list of group invitation with actions button to accept or decline the invites.</p>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15"><strong class="c-accent">Important note:</strong> It is highly advised to avoid joining another groups if you already in one since groups checks are currently only expect one groups per users.</p>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="accountSession">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Sessions</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">If "keep me signed in" checkbox ticked on logins, auth instances will leverage persistent local storage to save session for skipping repetitive authentication upon browser restarts.</p>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="issuesnsln">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Troubleshooting</h3>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="kmsi.png" class="w100p maxh50 containfit bora-s" alt="Session Interface"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15"><span class="bg-primary-10 c-white pad-s-s bora-s bold">Error: Exceeded Sessions</span> Uncheck the "Keep me signed in" checkbox before clicking login button.</p>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15"><span class="bg-primary-10 c-white pad-s-s bora-s bold">creating new session instance?</span> Navigate inside your account dashboard and go to session manager page via clicking the session manager button.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="profilefs.png" class="w100p maxh50 containfit bora-s" alt="Instance Router"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Click on "add new session" button to create new session instances. If the message renders "Maximum session allowed", existing session must be removed before new instance can be created.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="sessionpg.png" class="w100p maxh50 containfit bora-s" alt="Logs Management"></div>
                </div>
            </div>

            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m" id="LibForum">
                <h2 class="c-lightpurp txt-l border-bot-1-subtle pad-bot-s10 botMg-s20">Collection Management & Forums</h2>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="markout">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">MarkOut</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15"><span class="bg-primary-10 c-white pad-s-s bora-s bold">Use Case Context</span> any software/games listed that wanted to get downloaded must first get added to user MarkedOut library before it will showing up on the client installer.</p>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15"><span class="bg-primary-10 c-white pad-s-s bora-s bold">How to add a collection into my MarkOut?</span> open view page of the said collection and it will show view page like this, Click the "MarkOut" button and you'll be directed to MarkOut page after the collection get added</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="markingout.png" class="w100p maxh50 containfit bora-s" alt="Markout Action"></div>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="forumposting">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Forum Posting</h3>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="postnewforum.png" class="w100p maxh50 containfit bora-s" alt="New Content Block"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Access forum hub dashboard and click on "Post New Forum" button.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="forumdashboard.png" class="w100p maxh50 containfit bora-s" alt="Feed View"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Fill in the title, description, and bind to the desired topics. Images are optional and aren't needed for posting new forum</p>
                </div>
            </div>

            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m" id="groups">
                <h2 class="c-lightpurp txt-l border-bot-1-subtle pad-bot-s10 botMg-s20">Groups Management</h2>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="registrat">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Groups Registration</h3>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="groupsfooter.png" class="w100p maxh50 containfit bora-s" alt="Navigation Access"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Scroll to the page footer and execute the Groups anchor link click on "Groups" link, click on "Create new Groups" below the "Sign In" button and the page will open the registration form</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="createnewgrouplink.png" class="w100p maxh50 containfit bora-s" alt="Context Link"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Populate necessary registration details fields including name of the groups, description of the groups, and the passkeys are for your account access so make sure to not forget it.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="registerform.png" class="w100p maxh50 containfit bora-s" alt="Setup Form"></div>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="accessath">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Access Authority</h3>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="dashboard.png" class="w100p maxh50 containfit bora-s" alt="Workspace View"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">
                    Administrator and Developer have their unique access to some of the groups changing feature.<br>
                    Administrator access allowed to make announcement post and moderating announcement topic.<br>
                    Developer granted access to nearly all publishing features, from creating new collection, editing detail to file management
                    .</p>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="publishing">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Publishing</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Access the Publishing hub via the navigation bar on Groups dashboard.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="publishing.png" class="w100p maxh50 containfit bora-s" alt="Publishing Pipeline"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">The "New Collection" button opens a creation dialog. Required inputs include logo, banners, title, description, developement status, repository with separate links for mapping readmes manually, and external links.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="publishingcreate.png" class="w100p maxh50 containfit bora-s" alt="Creation Input"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Trailer link are for displaying your collection video demo/trailer on the view pages, be aware that currently it's only tested with link for Youtube video embedding format.</p>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Filled out external link "name" and "link" will be displayed your collection view page, up to ten link can exist in one collection.</p>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">After successfully created the new collection is saved and visible as "draft" collection, to publish or archive the collection click "change state" button and the two option will be visible. Note that collection software file must uploaded before changing the state to "Publics".</p>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Publishing collection with "Archived" state will require you to draft it first before it can be published.</p>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="uploadingfile">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">File Management</h3>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="filemanagerbtn.png" class="w100p maxh50 containfit bora-s" alt="Storage Command"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Open file manager for the collection that the software wanted to be uploaded, click on "upload" button on the top right and it will shows upload form like below.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="filemanagerupload.png" class="w100p maxh50 containfit bora-s" alt="Upload Panel"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">After uploading the files will automaticatlly set as active to the collection, the current files used by the collection will be marked by a green border.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="filemanageractive.png" class="w100p maxh50 containfit bora-s" alt="Active Binary Node"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">setting another existing file to be the "active" collection file can be done via selecting the file and click on "set active" button. the same goes for removing another file with the note that file must not be currently used by any of your collection.</p>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="community">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Community Management</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Each collection given dedicated topics accessible to the groups administrator/founder, below nav bar are the option to post new annoucement, changing title and description of the topic.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="communitymanage.png" class="w100p maxh50 containfit bora-s" alt="community"></div>
                </div>
            </div>

            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m" id="api">
                <h2 class="c-lightpurp txt-l border-bot-1-subtle pad-bot-s10 botMg-s20">API Gateways & Client</h2>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="cstate">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Current Limitation</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">
                        Due to security limitation set by currently used hosting infrastructure, production API gateway will stay disabled until the next hosting migration. Interfacing with API's can still be achieved but requires to host your own CGCC web server.
                        <br>
                        This is also the reason for why CGCC Launcher client is not currently available for the time being aside the development issues being addressed.
                    </p>
                </div>
                
                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark" id="obtainapi">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Obtaining API keys</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Obtaining API auth keys can be done by opening API panel from Options tab inside the Groups dashboard interface.</p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="apipanel.png" class="w100p maxh50 containfit bora-s"></div>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Two version of the API keys are provided each for it's own use, requesting new keys achieved via clicking the reset buttons placed beside the copy button</p>
                </div>

                <div class="topMg-s25 pad-top-s20 border-top-1-subtle-dark flex fld" id="userestric">
                    <h3 class="txt-m-l c-highlight botMg-s10 flex gap-10">Usage and restriction</h3>
                    <p class="c-subtle line-h-1-6 txt-n botMg-s15">Below are the table listing every needed input and output/debug variable</p>
                    <div class="posr botMg-s15 w100p flex fld border-t border-l border-r">
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s w50p txt-n">Input</p>
                            <p class="posr pad-s w50p txt-n border-l">Explanation</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr vertiMg pad-s w50p"><span class="pad-s-s bg-primary-10 txt-n c-white bold bora-s">headers: X-Api-Key</span></p>
                            <p class="posr pad-s w50p txt-n border-l">required to access API service, obtainable via groups management dashboard</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr vertiMg pad-s w50p"><span class="pad-s-s bg-primary-10 txt-n c-white bold bora-s">username</span></p>
                            <p class="posr pad-s w50p txt-n border-l">required account username credential</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr vertiMg pad-s w50p"><span class="pad-s-s bg-primary-10 txt-n c-white bold bora-s">password</span></p>
                            <p class="posr pad-s w50p txt-n border-l">depending on the scope either the main account password for the production client or groups access password for the development client</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr vertiMg pad-s w50p"><span class="pad-s-s bg-primary-10 txt-n c-white bold bora-s">os</span></p>
                            <p class="posr pad-s w50p txt-n border-l">required to provide the information about current operating system used by the user</p>
                        </div>
                    </div>
                    <div class="posr bottomMg-s10 w100p flex fld border-t border-l border-r">
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s w50p txt-n">Variable</p>
                            <p class="posr pad-s w50p txt-n border-l">Explanation</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr vertiMg pad-s w50p"><span class="pad-s-s bg-primary-10 txt-n c-white bold bora-s">useScope: Development</span></p>
                            <p class="posr pad-s w50p txt-n border-l">Development API keys provide full debug information within the returned output and require you groups access passkeys instead of you main account password, with a catch that downloading is forbidden using this API</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr vertiMg pad-s w50p"><span class="pad-s-s bg-primary-10 txt-n c-white bold bora-s">useScope: Production</span></p>
                            <p class="posr pad-s w50p txt-n border-l">Production/public API will use individual public account credential to verify, send back user data and only process status related messages</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr vertiMg pad-s w50p"><span class="pad-s-s bg-primary-10 txt-n c-white bold bora-s">message</span></p>
                            <p class="posr pad-s w50p txt-n border-l">Output variable logging the result/problem from the api processes</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m">
                <h2 class="c-lightpurp txt-l border-bot-1-subtle pad-bot-s10 botMg-s20">References</h2>
                <div class="posr flex gap10">
                    <a href="../Groups/index.php" target="_blank" class="posr pad-m bg-half-gray c-blue bold bora-s hover-text-white">Groups</a>      
                    <a href="https://github.com/MarketingPipeline/Markdown-Tag" target="_blank" class="posr pad-m bg-half-gray c-blue bold bora-s hover-text-white">Markdown-Tag</a>      
                    <a href="https://github.com/cure53/DOMPurify" target="_blank" class="posr pad-m bg-half-gray c-blue bold bora-s hover-text-white">DOMPurify</a>
                </div>
            </div>

        </section>
    </main>

    <?php include_once '../extra/footers.php';?>
</body>
</html>
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
    <link rel="shortcut icon" href="../img/cgcclogotrsp.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styling/pallate.css">
    <link rel="stylesheet" href="../styling/Mindex.css">
    <link rel="stylesheet" href="../styling/footer.css">
    <title>CGCC Documentation</title>
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
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">GROUPS</h2>
                <a href="../Groups/index.php" class="link-cover">.</a>
            </div>
<?php
}
?>
            <div class="posr pad-s flex fld acjc">   
                <h2 class="txt-n txtc semibold">BROWSE</h2>
                <a href="../Library/core/list.php" class="link-cover">.</a>
            </div>
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
    if (isset($_SESSION['GroupsToken']) && $_SESSION['gids']) {
?>
            <div class="leftMg flex acjc gap10">
                <p class="posr pad-n-s pad-s-v txtc txt-n bg-3 border-1 bora-s">Open Dashboard
                    <a href="../Groups/manage.php" class="link-cover hover-white">.</a>
                </p>
            </div>
<?php
    }
}
?>
    </div>

    <main class="sideMg topMg-10 bottomMg-s10 w75 flex gap10">
        <aside class="pos-s t10 pad-s-s minw10 w20p maxw20 maxh80 gap10 ovh-s">
            <div class="bottomMg-s10 pad-s-v flex fld border-purple-b gap5">
                <a href="#intro" class="posr pad-s txt-n c-lightpurple bold bora-s hover-white hover-pad-l16">Introduction</a>
            </div>
            
            <div class="bottomMg-s10 pad-s-v flex fld border-purple-b gap5">
                <p class="leftMg-s10 txt-b c-lightpurple">Account Guide</p>
                <a href="#accountPrtg" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Profile Tags</a>
                <a href="#settInv" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Settings & Invites</a>
                <a href="#accountSession" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Sessions</a>
                <a href="#issuesnsln" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Troubleshooting</a>
            </div>
            
            <div class="bottomMg-s10 pad-s-v flex fld border-purple-b gap5">
                <p class="leftMg-s10 txt-b c-lightpurple">Library & Forum</p>
                <a href="#markout" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">MarkOut</a>
                <a href="#forumposting" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Forum Posting</a>
            </div>
            <div class="bottomMg-s10 pad-s-v flex fld border-purple-b gap5">
                <p class="leftMg-s10 txt-b c-lightpurple">Client Launcher</p>
                <a href="#lclimit" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Notes</a>
                <a href="#lcissues" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">FAQ</a>
            </div>
            
            <div class="bottomMg-s10 pad-s-v flex fld border-purple-b gap5">
                <a href="api.php" class="posr pad-m-v pad-s-s bora-s txt-n c-lightpurple hover-white hover-pad-l16">API & CLIENT</a>
                <a href="groupspublishing.php" class="posr pad-m-v pad-s-s bora-s txt-n c-lightpurple hover-white hover-pad-l16">GROUPS & PUBLISHING</a>
            </div>
        </aside>

        <section class="posr w75p flex fld gap-s">
            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1">
                <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">CGCC Documentation</h2>
                <p class="pad-n-v txt-n">CGCC is an open-source software and game distribution ecosystem featuring an open community forum platform. This documentation serves to solve common issues and question while also providing information about website development workflows & configuration.</p>
            </div>

            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1" id="account">
                <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">Account</h2>
                
                <div class="posr" id="accountPrtg">
                    <h3 class="posr pad-s-v txt-b c-blue">Profile Tags</h3>
                    <p class="pad-n-v txt-n"><span class="posr bg-half-gray c-white pad-s-s bora-s bold">What is this for?</span> Your Profile Tag acts as your unique identity token utilized for relational connections across website and it's services, from forum posting to MarkOut collection</p>
                </div>
                
                <div class="posr border-purple-t" id="settInv">
                    <h3 class="posr pad-s-v txt-b c-blue">Settings & Invites</h3>
                    <p class="pad-n-v txt-n">Before opening panel make sure that you've already logged in, go to profile page by clicking "profile" button from the top navigation bar and the page display like this</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="profile.png" class="w100p maxh50 containfit" alt="Profile Preview"></div>
                    <p class="pad-n-v txt-n">Clicking the settings button will open the settings page where you can personalize your profile page or configure your account privacy:</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="settings.png" class="w100p maxh50 containfit" alt="Settings Form"></div>
                    <p class="pad-n-v txt-n">The upper module manages your settings preferences. Save changes directly by triggering the <strong>"Update Settings"</strong> buttons. The lower terminal display list of group invitation with actions button to accept or decline the invites.</p>
                    <p class="pad-n-v txt-n"><strong class="c-accent">Important note:</strong> It is highly advised to change the password as prompted after successfully joining a group because if not your account will not be accessible unless manually changed by the groups founder.</p>
                </div>
                
                <div class="posr border-purple-t" id="accountSession">
                    <h3 class="posr pad-s-v txt-b c-blue">Sessions</h3>
                    <p class="pad-n-v txt-n">If "keep me signed in" checkbox ticked on logins, auth instances will leverage persistent local storage to save session for skipping repetitive authentication upon browser restarts.</p>
                </div>
                
                <div class="posr border-purple-t" id="issuesnsln">
                    <h3 class="posr pad-s-v txt-b c-blue">Troubleshooting</h3>
                    <div class="posr flex acjc bg-1 border-purple"><img src="kmsi.png" class="w100p maxh50 containfit" alt="Session Interface"></div>
                    <p class="pad-n-v txt-n"><span class="posr bg-half-gray c-white pad-s-s bora-s bold">Error: Exceeded Sessions</span> Uncheck the "Keep me signed in" checkbox before clicking login button.</p>
                    <p class="pad-n-v txt-n"><span class="posr bg-half-gray c-white pad-s-s bora-s bold">creating new session instance?</span> Navigate inside your account dashboard and go to session manager page via clicking the session manager button.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="profilefs.png" class="w100p maxh50 containfit" alt="Instance Router"></div>
                    <p class="pad-n-v txt-n">Click on "add new session" button to create new session instances. If the message renders "Maximum session allowed", existing session must be removed before new instance can be created.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="sessionpg.png" class="w100p maxh50 containfit" alt="Logs Management"></div>
                </div>
            </div>

            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1" id="LibForum">
                <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">Collection Management & Forums</h2>
                
                <div class="posr border-purple-t" id="markout">
                    <h3 class="posr pad-s-v txt-b c-blue">MarkOut</h3>
                    <p class="pad-n-v txt-n"><span class="posr bg-half-gray c-white pad-s-s bora-s bold">Use Case Context</span> any software/games listed that wanted to get downloaded must first get added to user MarkedOut library before it will showing up on the client installer.</p>
                    <p class="pad-n-v txt-n"><span class="posr bg-half-gray c-white pad-s-s bora-s bold">How to add a collection into my MarkOut?</span> open view page of the said collection and it will show view page like this, Click the "MarkOut" button and you'll be directed to MarkOut page after the collection get added</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="markingout.png" class="w100p maxh50 containfit" alt="Markout Action"></div>
                </div>
                
                <div class="posr border-purple-t" id="forumposting">
                    <h3 class="posr pad-s-v txt-b c-blue">Forum Posting</h3>
                    <div class="posr flex acjc bg-1 border-purple"><img src="postnewforum.png" class="w100p maxh50 containfit" alt="New Content Block"></div>
                    <p class="pad-n-v txt-n">Access forum hub dashboard and click on "Post New Forum" button.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="forumdashboard.png" class="w100p maxh50 containfit" alt="Feed View"></div>
                    <p class="pad-n-v txt-n">Fill in the title, description, and bind to the desired topics. Images are optional and aren't needed for posting new forum</p>
                </div>
            </div>

            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1" id="lclimit">
                <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">Client Launcher</h2>
                <div class="posr border-purple-t">
                    <h3 class="posr pad-s-v txt-b c-blue">Note about the launcher</h3>
                    <p class="pad-n-v txt-n">
                        By default client require user to be logged once a month to provide the newest update, the launcher cannot function without connected to the CGCC Web API.
                    </p>
                </div>
                
                <div class="posr border-purple-t" id="lcissues">
                    <h3 class="posr pad-s-v txt-b c-blue">FAQ</h3>
                    <p class="pad-n-v txt-n">
                        <span class="posr bg-half-gray c-white pad-s-s bora-s bold">Collection does not show up in the launcher:</span>
                         check your MarkedOut collection on the website and if it doesn't show up in the MarkOut page that means you haven't added them thus it won't shows in the launcher
                         , be sure that what you logged the same account as the one you're currently logged in the website.
                    </p>
                </div>
            </div>

            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1">
                <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">References</h2>
                <div class="posr flex gap10">
                    <a href="../Groups/index.php" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">Groups-Flow (required to signIn)</a>
                    <a href="groupspublishing.php" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">Groups & Publishing Docs</a>
                    <a href="api.php" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">API Docs</a> 
                </div>
            </div>

        </section>
    </main>

    <?php include_once '../extra/footers.php';?>
</body>
</html>
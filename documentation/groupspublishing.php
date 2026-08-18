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
    <title>Groups & Publishing / CGCC Docs</title>
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
                <a href="docs.php" class="posr pad-m-v pad-s-s bora-s txt-b c-lightpurple hover-white hover-pad-l16">MAIN DOCS</a>
                <a href="api.php" class="posr pad-m-v pad-s-s bora-s txt-b c-lightpurple hover-white hover-pad-l16">API & CLIENT</a>
                <a href="#intro" class="posr pad-s bora-s txt-s bold hover-white hover-pad-l16">Introduction</a>
            </div>

            <div class="bottomMg-s10 pad-s-v flex fld border-purple-b gap5">
                <p class="leftMg-s10 txt-b c-lightpurple">GROUPS MANAGEMENT</p>
                <a href="#registrat" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Registration</a>
                <a href="#accessath" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Access Authority</a>
            </div>

            <div class="botMg-s20 pad-bot-s15 flex fld border-bot-1-subtle-dark">
                <p class="leftMg-s10 txt-b c-lightpurple">PUBLISHING</p>
                <a href="#publishing" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Publishing</a>
                <a href="#uploadingfile" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">File Management</a>
                <a href="#badgemaking" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Badges</a>
                <a href="#community" class="posr pad-s bora-s txt-s hover-white hover-text-blue hover-pad-l16">Community Hub</a>
            </div>
        </aside>

        <section class="posr w75p flex fld gap-s">
            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1">
                <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">Groups & Publishing Guide</h2>
                <p class="pad-n-v txt-n">This page will detail about managing your groups and how to start publishing your sofware</p>
            </div>

            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1" id="groups">
                <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">Groups Management</h2>
                
                <div class="pad-st" id="registrat">
                    <h3 class="posr pad-m-v txt-b c-blue">Groups Sign In and Registration</h3>
                    <p class="pad-n-v txt-n">Click the Groups button on the top bar and it should show the Group-Flow landing page like below</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="groupLanding.png" class="w100p maxh50 containfit" alt=""></div>
                    <p class="pad-n-v txt-n">
                        if you already have created/joined groups and have an active access account to, click on the option input then select the groups you want to sign into and fill the password you'd set for your access account.
                        it is required to logged in the main account before you can accessing the Groups-Flow as the Groups-Flow will use your logged in main account username for verifying your access account.
                        </p>
                    <p class="txt-n">If you haven't created or joined groups, you can click on "Create new Groups" below the "Sign In" button and the page will open the registration form</p>
                    <p class="pad-n-v txt-n">Populate necessary registration details fields including name of the groups, description of the groups, and your founder account passkeys are for your account access so make sure to not forget it.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="registerform.png" class="w100p maxh50 containfit" alt="Setup Form"></div>
                </div>
                
                <div class="pad-st" id="accessath">
                    <h3 class="posr pad-m-v txt-b c-blue">Access Account Authority</h3>
                    <div class="posr flex acjc bg-1 border-purple"><img src="dashboard.png" class="w100p maxh50 containfit" alt="Workspace View"></div>
                    <p class="pad-n-v txt-n">
                    Each access account has their "role" attribute which tell the Groups-Flow component they get access based on it with the "Founder" role granting access to all of component because the role only given to the creator of the Groups 
                    Administrator and Developer have their unique access to some of the groups changing feature.<br>
                    Administrator access allowed to make announcement post and moderating announcement topic.<br>
                    Developer granted access to nearly all publishing features, from creating new collection, editing detail to file management
                    .</p>
                </div>
            </div>
                
            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1" id="publishing">
                <h2  class="pad-s-v txt-l c-lightpurple border-purple-b">Publishing</h2>
                <div class="pad-st">
                    <p class="pad-n-v txt-n">Access the Publishing hub by clicking "Publish" button on the navigation bar.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="publishing.png" class="w100p maxh50 containfit" alt="Publishing Pipeline"></div>
                    <p class="pad-n-v txt-n">The "New Collection" button opens a creation dialog. Required inputs include logo, banners, title, description, developement status, repository with separate links for mapping readmes manually, and external links.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="publishingcreate.png" class="w100p maxh50 containfit" alt="Creation Input"></div>
                    <p class="pad-n-v txt-n">Trailer link are for displaying your collection video demo/trailer on the view pages, be aware that currently it's only tested with link for Youtube video embedding format.</p>
                    <p class="pad-n-v txt-n">Filled out external link "name" and "link" will be displayed your collection view page, up to ten link can exist in one collection.</p>
                    <p class="pad-n-v txt-n">After successfully created the new collection is saved and visible as "draft" collection, to publish or archive the collection click "change state" button and the two option will be visible. Note that collection software file must uploaded before changing the state to "Publics".</p>
                    <p class="pad-n-v txt-n">Publishing collection with "Archived" state will require you to draft it first before it can be published.</p>
                </div>
                
                <div class="pad-st" id="uploadingfile">
                    <h3 class="posr pad-m-v txt-b c-blue">File Management</h3>
                    <div class="posr flex acjc bg-1 border-purple"><img src="filemanagerbtn.png" class="w100p maxh50 containfit" alt="File manager"></div>
                    <p class="pad-n-v txt-n">Open file manager for the collection that the software wanted to be uploaded, click on "upload" button on the top right and it will shows upload form like below.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="filemanagerupload.png" class="w100p maxh50 containfit" alt="Upload Panel"></div>
                    <p class="pad-n-v txt-n">After uploading the files will automaticatlly set as active to the collection, the current files used by the collection will be marked by a green border.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="filemanageractive.png" class="w100p maxh50 containfit" alt="Activating Files"></div>
                    <p class="pad-n-v txt-n">setting another existing file to be the "active" collection file can be done via selecting the file and click on "set active" button. the same goes for removing another file with the note that file must not be currently used by any of your collection.</p>
                </div>
                <div class="pad-st" id="badgemaking">
                    <h3 class="posr pad-m-v txt-b c-blue">Badges Creation & Management</h3>
                    <div class="posr flex acjc bg-1 border-purple"><img src="badgesmanager.png" class="w100p maxh50 containfit" alt="Storage Command"></div>
                    <p class="pad-n-v txt-n">To open the badges manager is the same as Opening file manager, select the collection that wanted to be managed and it will shown UI like above.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="badgegroupcreate.png" class="w100p maxh50 containfit" alt="Upload Panel"></div>
                    <p class="pad-n-v txt-n">If no badges-group exist for the collection click on "New badge groups" to open dialog like below, fill the input accordingly and click create when done</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="badgescreate.png" class="w100p maxh50 containfit" alt="Active Binary Node"></div>
                    <p class="pad-n-v txt-n">When creation succeed the badges group will show up on the badge groups selection input and select badge groups created when creating the new badges.</p>
                </div>
                
                <div class="pad-st" id="community">
                    <h3 class="posr pad-m-v txt-b c-blue">Community Management</h3>
                    <p class="pad-n-v txt-n">Each collection given dedicated topics accessible to the groups administrator/founder, below nav bar are the option to post new annoucement, changing title and description of the topic.</p>
                    <div class="posr flex acjc bg-1 border-purple"><img src="communitymanage.png" class="w100p maxh50 containfit" alt="community"></div>
                </div>
            </div>

            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1">
                <h2  class="posr pad-s-v txt-l c-lightpurple border-purple-b">References</h2>
                <div class="posr pad-s-v flex gap10">
                    <a href="../Groups/index.php" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">Groups-Flow (to signIn)</a>   
                    <a href="api.php" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">API Docs</a> 
                    <a href="https://github.com/MarketingPipeline/Markdown-Tag" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">Markdown-Tag</a>
                    <a href="https://github.com/cure53/DOMPurify" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">DOMPurify</a>
                </div>
            </div>

        </section>
    </main>

    <?php include_once '../extra/footers.php';?>
</body>
</html>
<!-- yes, the styling are inpired from the docs.php, of course because it's AI sourced the code so cooked that I literally restyling it -->

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
    <title>CGCC Changelog</title>
    
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
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="docs.php" class="link-cover">.</a>
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
            <div class="bottomMg-s10 pad-bot-s15 flex fld gap5 border-bot-1-subtle-dark">
                <a href="#intro" class="block pad-s bora-s txt-s bold c-highlight trs200ms hover-bg-subtle botMg-s2 hover-pl-16">Introduction</a>
            </div>

            <div class="bottomMg-s10 pad-bot-s15 flex fld border-bot-1-subtle-dark">
                <p class="pad-m-v txt-b uppercase letter-sp-1 c-lightpurp leftMg-s10">June</p>
                <a href="#26j18t19" class="block pad-n-s pad-m-v bora-s txt-n c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">18th - 19th</a>
                <a href="#26j12t13" class="block pad-n-s pad-m-v bora-s txt-n c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">12th - 13th</a>
                <a href="#26j1t4" class="block pad-n-s pad-m-v bora-s txt-n c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">1st - 4th</a>
            </div>

            <div class="bottomMg-s10 pad-bot-s15 flex fld border-bot-1-subtle-dark">
                <p class="pad-m-v txt-b uppercase letter-sp-1 c-lightpurp leftMg-s10">May</p>
                <a href="#26m16t31" class="block pad-n-s pad-m-v bora-s txt-n c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">16th - 31st</a>
                <a href="#26m1t15" class="block pad-n-s pad-m-v bora-s txt-n c-subtle trs200ms hover-bg-subtle hover-c-highlight botMg-s2 hover-pl-16">1st - 15th</a>
            </div>
        </aside>

        <section class="flex-1 flex fld verti gap-35 sm-w100p">
            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m">
                <h2 class="bottomMg-s10 pad-s-v c-lightpurp txt-l border-bot-1-subtle">Change-Logs</h2>
                <p class="c-subtle line-h-1-6 txt-n botMg-s15">Specifically made to address my forgetfulness by a bit, basically a more organized version of the journals. Note: the date written for update before June/2026 are estimated date +1 to 10 days</p>
            </div>

            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m">
                <h2 class="bottomMg-s10 pad-s-v c-lightpurp txt-l border-bot-1-subtle">June / 2026</h2>
                
                <div class="topMg-s10 pad-s-v flex fld gap10" id="26j18t19">
                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">19th, MarkOut Overhaul</h3>
                    <p class="c-subtle line-h-1-6 txt-n">Reworked MarkOut UI, New detail page for MarkedOut Collection, and Ability to removed a collection from the MarkedOut</p>
                    <p class="c-subtle line-h-1-6 txt-n">Updated styling class & Fixed download API auth check</p>
                </div>
                <div class="topMg-s10 pad-s-v flex fld gap10">
                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">18th, Adding API detail</h3>
                    <p class="c-subtle line-h-1-6 txt-n">API panel now include extra detail about the API in Groups management dashboard</p>
                </div>
                
                <div class="topMg-s10 pad-s-v flex fld gap10" id="26j12t13">
                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">13th, changelog created</h3>
                    <p class="c-subtle line-h-1-6 txt-n">The reason? look at this page introduction, no currently it isn't dynamically retrieved from the database but this haven't get finalized yet.</p>
                </div>
                
                <div class="topMg-s10 pad-s-v flex fld gap10 border-top-1-subtle-dark">
                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">12th, the API auth and download Implemented</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        I've finished the API's for anyone that wanted to test the project locally or developing their own client downloader, noted that the hosting infrastruct currently used for the website doesn't support API's/non-browser processing so this functionallity aren't available on the demo site.
                        <br>
                        The api using X-Api-Keys because the plain token won't do to secure it(I'm sure this ain't enough, at the end of it nothing is secure).
                        <br>
                        Api token can be obtained from the groups api panel page, for reasons I'm already forget because I coded while being heavily sleeply but can't sleep.
                        <br>
                        and yeah, download api does kinda work but it really not the secure way I believed. more detail on <a href="docs.php" class="posr c-orange hover-text-blue">the documentation</a>
                    </p>
                </div>

                <div class="topMg-s10 pad-s-v flex fld gap10 border-top-1-subtle-dark" id="26j1t4">
                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">1st - 4th, fixing the community management tab 403 issue</h3>
                    <p class="c-subtle line-h-1-6 txt-n">The word "announce" were specifically being blocked to be used as directive and after changing the file names the site works again, really got me thinking the code were broken internally.</p>
                </div>
            </div>
            <div class="bg-card-subtle border-1-subtle bora-m pad-l-l shadow-m" id="26m16t31">
                <h2 class="bottomMg-s10 pad-s-v c-lightpurp txt-l border-bot-1-subtle">May / 2026</h2>
                
                <div class="topMg-s10 pad-s-v flex fld gap10">
                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">16th - 31st, report implementents</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        This is a bit of a really late realization when going to ship the project, with no way of reporting at all the community has no way of of reporting user in the forums or "malicious" publisher that published collection with malware/virus. Even though this is not permanent, I'd implement the universal report tab to be placed on forums detail, user profile, groups profile and collection detail. Each of them are gonna be proccessed possibly between 1 to 5 days depending on the case and of course this is why I required a valid email in registration, when things like this happens there will be emails to the suspected user/groups for confirmation & solution.
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">16th - 31st, settings & consent</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        It's related to privated profile info and allow groups invites. The first one was planned to added way back in Dec/2025 but forgoten until now, If it's get ticked then achivement and badge will not be shown to publics but post and currently joined groups will still be visible.
                        <br>
                        user can give consent about allowing groups sending invites and turned on by default until they joined a groups after which they must turned it on again if they wanted an invitation again.
                        <br>
                        all of this contained inside settings panel and the invitation message is shown below the settings.
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">16th - 31st, implemented profile picture</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        Yeah it's known profile picture were the most important things that should've been added a while back, priorities shifting every now and then but it is now implemented. At first I cloudn't decide whether `.gif` should be allowed or not but I figured if the size is still within limit why not just allow it. It's only get shown on profile pages anyway.
                        <br>
                        Noted this is also include the profile picture of the Groups profile pages, the groups profile picture is a must to be implemented though like the other it is also only gonna get shown on the (groups) profile pages (and the desktop app if that counts).
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">16th - 31st, session manager UI update</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        A while back(about 3 weeks ago) The old session page styling made for just the bare minimum, so I change them while borrowing UI styling from other page codes. This time the requested back button included, nothing much changes other than adding the session checker and updating the UI's as of May/11.
                    </p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc">
                        <img src="preStylingSessPage.webp" class="w100p maxh50 containfit bora-s">
                    </div>
                    <p class="c-subtle line-h-1-6 txt-n txtc">
                        old version
                    </p>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc">
                        <img src="sessionpg.png" class="w100p maxh50 containfit bora-s">
                    </div>
                    <p class="c-subtle line-h-1-6 txt-n txtc">
                        new version
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">16th - 31st, updated collection views</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        Some changes needed for help ease on the server-side, most notably external link where they're now a dynamic name and link that support up to 10 link as previously only support one website and youtube link.
                        <br>It's now also support embedded video demo/trailer although it's optional and only been tested with YT video link.
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">16th - 31st, collection file manager</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        Originally this was planned for future updates the file manager is really needed in order to securely upload and update. The paranoid me really didn't want to mess up this very vunerable part of the project... here's a little breakdown of what created/removed:
                        <br>
                        - `post_file.php` were removed and replace with `file_proceed.php`, because the naming doesn't makes sense when it also does processing remove file.
                        <br>
                        - `file_manager.php` are the main pages to manage all the existing file on their vault, it will only list file that exist and will even not list phantom file written in the database. Naturally, because of the capability given to this page making this page having more layered security check just to make sure only one collection at a time allowed to be accessed by user.
                        <br>
                        - `file_proceed.php` is the one that process everything input and request from the file manager
                        <br>
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">8th - 15th, Community Management</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        It's a dedicated page for topics created along with the creation of collection, this is where the publisher admins/founder can post an annoucement about their collection.
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight" id="26m1t15">8th - 15th, collection publishing</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        Dedicated management system to create and administer collection, originally the system can be accessed by a normal user but after the creation of the [groups-flow](#groups-flow) the publishing now can only be accessed by allowed members of a groups, this help to make sure reduce creation spamming. 
                        <br>
                        - the software file and the rest of the collection creation proccessing are inevitably must be separated into `create_collection.php` and `post_file.php` because I don't believe with the real world connection speed and file upload size limit that this will be possible to be done at the same time without compromising the security, the other way this will be possible probably with the introduction of file chunking but currently I just want this to properly work 
                        <br>
                        - `edit.php` handle the collection update, final publishing and archiving. Noted that it just handle the normal image and text data update where the size are still doable for the most cases
                        <br>
                        - if not obvious already, the software that will be shipped to this site must be using `.zip` format and preferably on 'store' mode. the reason were that It will took a significant amount of performance to implement separated file upload instead of this, I might implement a way to make some sort of file Ignore & replace list for the client launcher so that publisher can list which file that should/shouldn't be replaced on the client side each time an updates downloaded
                        <br>
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">7th - 15th, Invitation notification</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        While it is look like a notification, doesn't mean this one is a real notification system. It's function are only for notified a Groups invitation to user and nothing else, for now I'd not have plan to expand the functionality.
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">7th - 15th, groups-flow access system</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        always take a deep breath when i make something and then realizing the security missing something and this is one of them, were do I even begin..
                        <br>
                        - Invite system, now comes with nerfed and limited version of notification. of course this begin when I've got revelation that if I didn't do this, Individual can get added easily by Groups forcibly without consent.
                        <br>
                        - access login & auth, basically a more paranoid login system on steroid and auth system were making sure that every groups pages can only be accessed by identified and approved account by the groups founder.
                        <br>
                        - access system can only be created either for the founder account when a new groups created and when an invited user accepted to join the groups that invited them.
                    </p>
                    
                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">7th - 15th, groups-flow auth sessions</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        Groups-flow auth system were separated from the website main auth. Using more temporary session implementation though the implementation were similiar, at maximum saved for only 1 day before requiring reauthentication. this decision made because of the high security vulnerability on giving access to the groups collection and community management. And another note to when user signed in again: the old session token in database will be replaced with new ones, truthfully most auth and sessions code were reused/refactor from the main sessions code but adjusted a lot to fit the groups-flow usecase.
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">2nd - 15th, groups-flow</h3>
                    <p class="c-subtle line-h-1-6 txt-n">
                        A place where developer can publish and manage their collection, within it developer can invite and manage members account, edit their public profile, create and managing collection along with making announcement about the future update to their audience. If not because of of the budget constraint and a lot of possible security issue, the Groups-Flow would operate in separate domain and database with a sync system to the main website. This might be revisited in near future if needed.
                    </p>

                    <h3 class="topMg-s10 pad-sb txt-m-l c-highlight">1st - 15th, session system</h3>
                    <div class="bg-1 border-1-subtle bora-s pad-s-s vertiMg-s15 flex acjc"><img src="preStylingSessPage.webp" class="w100p maxh50 containfit bora-s"></div>
                    <p class="c-subtle line-h-1-6 txt-n">
                        Thanks to the feedback from reviewer, the website now support using session token on the login system. Noted that when user are exceeding session limit but they can only use temporary session and required to tick off "keep me signed in" in order to access the website and removing unused/expired session. This alone took me a good few days to rewrite the auth and login codes
                    </p>
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
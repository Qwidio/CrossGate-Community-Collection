<?php
require_once '../processes/database.php';
$errors = array();
$root_route = "../";
require_once '../secureSession.php';
require_once '../Groups/ReAuth.php';
if (!isset($_SESSION['profileTags'])) {
    header ('location: ../connect_it/connect_it.php');
    exit;
}
if (isset($_SESSION['GroupsToken'])) {
    $gToken = $_SESSION['GroupsToken'];
}
$aidis = $_SESSION['profileTags'];

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
    function jscd(window) {
        {
        var unknown = '-';
        // screen
        var screenSize = '';
        if (screen.width) {
            width = (screen.width) ? screen.width : '';
            height = (screen.height) ? screen.height : '';
            screenSize += '' + width + " x " + height;
        }
        // browser
        var nVer = navigator.appVersion;
        var nAgt = navigator.userAgent;
        var browser = navigator.appName;
        var version = '' + parseFloat(nVer);
        var nameOffset, verOffset, ix;
        // Yandex Browser
        if ((verOffset = nAgt.indexOf('YaBrowser')) != -1) {
          browser = 'Yandex';
          version = nAgt.substring(verOffset + 10);
        }
        // Samsung Browser
        else if ((verOffset = nAgt.indexOf('SamsungBrowser')) != -1) {
          browser = 'Samsung';
          version = nAgt.substring(verOffset + 15);
        }
        // UC Browser
        else if ((verOffset = nAgt.indexOf('UCBrowser')) != -1) {
          browser = 'UC Browser';
          version = nAgt.substring(verOffset + 10);
        }
            // Opera Next
            else if ((verOffset = nAgt.indexOf('OPR')) != -1) {
                browser = 'Opera';
                version = nAgt.substring(verOffset + 4);
            }
            // Opera
            else if ((verOffset = nAgt.indexOf('Opera')) != -1) {
                browser = 'Opera';
                version = nAgt.substring(verOffset + 6);
                if ((verOffset = nAgt.indexOf('Version')) != -1) {
                    version = nAgt.substring(verOffset + 8);
                }
            }
            // Legacy Edge
            else if ((verOffset = nAgt.indexOf('Edge')) != -1) {
                browser = 'Microsoft Legacy Edge';
                version = nAgt.substring(verOffset + 5);
            } 
            // Edge (Chromium)
            else if ((verOffset = nAgt.indexOf('Edg')) != -1) {
                browser = 'Microsoft Edge';
                version = nAgt.substring(verOffset + 4);
            }
            // MSIE
            else if ((verOffset = nAgt.indexOf('MSIE')) != -1) {
                browser = 'Microsoft Internet Explorer';
                version = nAgt.substring(verOffset + 5);
            }
            // Chrome
            else if ((verOffset = nAgt.indexOf('Chrome')) != -1) {
                browser = 'Chrome';
                version = nAgt.substring(verOffset + 7);
            }
            // Safari
            else if ((verOffset = nAgt.indexOf('Safari')) != -1) {
                browser = 'Safari';
                version = nAgt.substring(verOffset + 7);
                if ((verOffset = nAgt.indexOf('Version')) != -1) {
                    version = nAgt.substring(verOffset + 8);
                }
            }
            // Firefox
            else if ((verOffset = nAgt.indexOf('Firefox')) != -1) {
                browser = 'Firefox';
                version = nAgt.substring(verOffset + 8);
            }
            // MSIE 11+
            else if (nAgt.indexOf('Trident/') != -1) {
                browser = 'Microsoft Internet Explorer';
                version = nAgt.substring(nAgt.indexOf('rv:') + 3);
            }
            // Other browsers
            else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) < (verOffset = nAgt.lastIndexOf('/'))) {
                browser = nAgt.substring(nameOffset, verOffset);
                version = nAgt.substring(verOffset + 1);
                if (browser.toLowerCase() == browser.toUpperCase()) {
                    browser = navigator.appName;
                }
            }
            // trim the version string
            if ((ix = version.indexOf(';')) != -1) version = version.substring(0, ix);
            if ((ix = version.indexOf(' ')) != -1) version = version.substring(0, ix);
            if ((ix = version.indexOf(')')) != -1) version = version.substring(0, ix);
            majorVersion = parseInt('' + version, 10);
            if (isNaN(majorVersion)) {
                version = '' + parseFloat(nVer);
                majorVersion = parseInt(nVer, 10);
            }
            // mobile version
            var mobile = /Mobile|mini|Fennec|Android|iP(ad|od|hone)/.test(nVer);
            // cookie
            var cookieEnabled = (navigator.cookieEnabled) ? true : false;
            if (typeof navigator.cookieEnabled == 'undefined' && !cookieEnabled) {
                document.cookie = 'testcookie';
                cookieEnabled = (document.cookie.indexOf('testcookie') != -1) ? true : false;
            }
            // system
            var os = unknown;
            var clientStrings = [
                {s:'Windows 10', r:/(Windows 10.0|Windows NT 10.0)/},
                {s:'Windows 8.1', r:/(Windows 8.1|Windows NT 6.3)/},
                {s:'Windows 8', r:/(Windows 8|Windows NT 6.2)/},
                {s:'Windows 7', r:/(Windows 7|Windows NT 6.1)/},
                {s:'Windows Vista', r:/Windows NT 6.0/},
                {s:'Windows Server 2003', r:/Windows NT 5.2/},
                {s:'Windows XP', r:/(Windows NT 5.1|Windows XP)/},
                {s:'Windows 2000', r:/(Windows NT 5.0|Windows 2000)/},
                {s:'Windows ME', r:/(Win 9x 4.90|Windows ME)/},
                {s:'Windows 98', r:/(Windows 98|Win98)/},
                {s:'Windows 95', r:/(Windows 95|Win95|Windows_95)/},
                {s:'Windows NT 4.0', r:/(Windows NT 4.0|WinNT4.0|WinNT|Windows NT)/},
                {s:'Windows CE', r:/Windows CE/},
                {s:'Windows 3.11', r:/Win16/},
                {s:'Android', r:/Android/},
                {s:'Open BSD', r:/OpenBSD/},
                {s:'Sun OS', r:/SunOS/},
                {s:'Chrome OS', r:/CrOS/},
                {s:'Linux', r:/(Linux|X11(?!.*CrOS))/},
                {s:'iOS', r:/(iPhone|iPad|iPod)/},
                {s:'Mac OS X', r:/Mac OS X/},
                {s:'Mac OS', r:/(Mac OS|MacPPC|MacIntel|Mac_PowerPC|Macintosh)/},
                {s:'QNX', r:/QNX/},
                {s:'UNIX', r:/UNIX/},
                {s:'BeOS', r:/BeOS/},
                {s:'OS/2', r:/OS\/2/},
                {s:'Search Bot', r:/(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/}
            ];
            for (var id in clientStrings) {
                var cs = clientStrings[id];
                if (cs.r.test(nAgt)) {
                    os = cs.s;
                    break;
                }
            }

            var osVersion = unknown;

            if (/Windows/.test(os)) {
                osVersion = /Windows (.*)/.exec(os)[1];
              if (osVersion == 10 && navigator.userAgentData) {
                    navigator.userAgentData.getHighEntropyValues(["platformVersion"])
                      .then((ua) => window.jscd.osVersion = (parseInt(ua.platformVersion.split('.')[0]) < 13 ? 10 : 11));
                }
                os = 'Windows';
            }

            switch (os) {
                case 'Mac OS':
                case 'Mac OS X':
                case 'Android':
                    osVersion = /(?:Android|Mac OS|Mac OS X|MacPPC|MacIntel|Mac_PowerPC|Macintosh) ([\.\_\d]+)/.exec(nAgt)[1];
                    break;

                case 'iOS':
                    osVersion = /OS (\d+)_(\d+)_?(\d+)?/.exec(nVer);
                    osVersion = osVersion[1] + '.' + osVersion[2] + '.' + (osVersion[3] | 0);
                    break;
            }
        }

        window.jscd = {
            screen: screenSize,
            browser: browser,
            browserVersion: version,
            browserMajorVersion: majorVersion,
            mobile: mobile,
            os: os,
            osVersion: osVersion,
            cookies: cookieEnabled
        };
    }
    jscd(this);
    </script>
    <title>CGCC-GROUPS</title>
</head>
<body class="wh100p minh100 ovh-s ovs-v">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity1 z1">
    <div class="posr pad-n-s w100p minh10 flex gap-s z4">
        <div class="posr vertiMg leftMg-s10 h5 flex acjc">
            <img src="../img/cgcc_logos_widetmp.png" alt="" class="posr h100p containfit">
            <a href="../index.php" class="link-cover">.</a>
        </div>
        <div class="posr rightMg w60p flex gap-s">
            <div class="posr pad-s flex fld acjc">
                <h2 class="txt-n txtc semibold">DOCS</h2>
                <a href="../documentation/docs.php" class="link-cover">.</a>
            </div>
        </div>
<?php
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
                    <a href="manage.php" class="link-cover">.</a>
                </p>
            </div>
<?php
    }
    $inGroups = true; 
}
?>
    </div>
    <div class="posr w100p h90 flex z2">
        <div class="rightMg w50p h100p flex fld z3">
            <h1 class="topMg rightMg leftMg-10 txt-maintext semibold">GROUPS-FLOW</h1>
            <h1 class="bottomMg rightMg leftMg-10 txt-b">Manage & Publish Collection in One Flow</h1>
        </div>
<?php
if (!isset($_SESSION['GroupsToken'])) {
    if (isset($_GET['state'])) {
        $state = $_GET['state'];
    } else {
        $state = "login";
    }
    if ($state === "register" && $inGroups == false) {
?>
        <div class="posr w50p h100p flex fld z3">
            <form class="posr rigthMg vertiMg pad-b-s pad-n-v minw200 w40 h90p flex fld acjc gap10 blurbg border-1 bora-s ovh z4" action="../processes/groupAuth.php" method="post">
                <h1 class="sideMg txtc txt-b bold">CREATE NEW GROUPS</h1>
                <div class="sideMg w88p flex fld">
                    <label for="GName">Name of the new Group</label>
                    <input class="inptxt border-b" type="text" id="GName" name="GName" placeholder="Identify what's the name of your Groups" autocomplete="off" tabindex="1" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="GDescs">About Groups</label>
                    <textarea class="inptxt h10 border-b ovh-s" type="text" id="GDescs" name="GDescs" placeholder="Describe about your Groups" autocomplete="off" tabindex="2" required></textarea>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="passkeys">Access Account PassKeys</label>
                    <input class="inptxt border-b" type="text" id="passkeys" name="passkeys" placeholder="Create a secure PassKeys" autocomplete="off" tabindex="3" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="confirm">Confirm PassKeys</label>
                    <input class="inptxt border-b" type="password" id="confirm" name="confirm" placeholder="Input the same PassKeys as the above" autocomplete="off" tabindex="4" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <button type="submit" class="pad-s bgc-gold txt-n c-black" name="Register" tabindex="5">Register</button>
                </div>
                <div class="sideMg w88p flex fld">
                    <p class="txtc">already joined a Groups? <a href="index.php" class="c-orange hover-text-white" tabindex="6">Sign In</a></p>
                    <p class="pad-m txtc">Having problem getting in?</br><a href="../documentation/docs.php#account" class="c-orange hover-text-white" tabindex="7">check docs</a> or <a href="dmlink" class="c-orange hover-text-white" tabindex="7">dm me</a></p>
                </div>
            </form>
        </div>
<?php
    } else {
?>
        <div class="posr w50p h100p flex fld z3">
            <form class="posr rigthMg vertiMg pad-b-s pad-n-v minw200 w40 h90p flex fld acjc gap10 blurbg border-1 bora-s ovh z4" action="../processes/groupAuth.php" method="post">
                <h1 class="sideMg txtc txt-b bold">CONFIRM IDENTITY</h1>
                <input class="hiddeninp" type="text" id="os" name="os" autocomplete="off" tabindex="999" required readonly>
                <input class="hiddeninp" type="text" id="browser" name="browser" autocomplete="off" tabindex="999" required readonly>
                <input class="hiddeninp" type="text" id="mobile" name="mobile" autocomplete="off" tabindex="999" required readonly>
                <input class="hiddeninp" type="text" id="uad" name="uad" autocomplete="off" tabindex="999" required readonly>
                <div class="sideMg w88p flex fld">
                    <label for="username">Username</label>
                    <input class="inptxt border-b" type="text" id="username" name="username" placeholder="Use your account username" autocomplete="off" tabindex="1" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <label for="passkeys">Groups PassKeys</label>
                    <input class="inptxt border-b" type="password" id="passkeys" name="passkeys" placeholder="Input the correct PassKeys" autocomplete="off" tabindex="2" required>
                </div>
                <div class="sideMg w88p flex fld">
                    <button type="submit" class="pad-s bgc-gold txt-n c-black" name="Login" tabindex="3">Sign in</button>
                </div>
                <div class="sideMg w88p flex fld">
                    <p class="txtc">Haven't joined any Groups? <a href="index.php?state=register" class="c-orange hover-text-white" tabindex="4">Create new Groups</a></p>
                    <p class="pad-m txtc">Having problem getting in?</br><a href="../documentation/docs.php#account" class="c-orange hover-text-white" tabindex="5">check docs</a> or <a href="dmlink" class="c-orange hover-text-white" tabindex="7">dm me</a></p>
                </div>
            </form>
        </div>
<?php
    }
}
?>
    </div>
    <?php include_once '../extra/footers.php';?>
    <script>
        function outputData() {
            const data = {
                os: jscd.os + ' ' + jscd.osVersion,
                browser: jscd.browser + ' ' + jscd.browserMajorVersion + 
                        ' (' + jscd.browserVersion + ')',
                mobile: jscd.mobile,
                uad: navigator.userAgent
            };
            Object.keys(data).forEach((key) => {
                const input = document.getElementById(key);
                if (input) {
                    input.value = data[key];
                }
            });
        }
        window.addEventListener('DOMContentLoaded', outputData);
    </script>
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
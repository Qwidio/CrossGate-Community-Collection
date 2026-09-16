<?php
require_once "../processes/database.php";
if (isset($_GET['state'])) {
    $state = $_GET['state'];
} else {
  $state = "login";
}
$errors = array();
if (isset($_SESSION['profileTags'])) {
    header ('location: ../index.php');
    exit;
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/cgcclogotrsp.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styling/pallate.css">
    <link rel="stylesheet" href="../styling/Mindex.css">
    <script>
        // Updated detector, the output are still same as the main difference that it uses the newer navigator.userAgentData
        function jscd(root = window) {
            const nav = root.navigator;
            const screenObject = root.screen;

            const unknown = "-";
            const userAgent = nav.userAgent || "";
            const platform = nav.platform || "";

            const result = {
                screen: screenObject && screenObject.width && screenObject.height
                    ? `${screenObject.width} x ${screenObject.height}`
                    : unknown,

                browser: unknown,
                browserVersion: unknown,
                browserMajorVersion: 0,

                mobile: /Android|iPhone|iPad|iPod|Mobile/i.test(userAgent),

                os: unknown,
                osVersion: unknown,

                cookies: typeof nav.cookieEnabled === "boolean"
                    ? nav.cookieEnabled
                    : unknown
            };

            function setBrowser(name, version) {
                result.browser = name || unknown;
                result.browserVersion = version || unknown;

                const major = parseInt(String(version).split(".")[0], 10);
                result.browserMajorVersion = Number.isNaN(major) ? 0 : major;
            }

            function versionFromMatch(match) {
                return match && match[1] ? match[1] : unknown;
            }

            function detectBrowserFromUserAgent() {
                let match;
                match = userAgent.match(/EdgA?\/([\d.]+)/i);
                if (match) {
                    setBrowser("Microsoft Edge", match[1]);
                    return;
                }
                match = userAgent.match(/Edge\/([\d.]+)/i);
                if (match) {
                    setBrowser("Microsoft Legacy Edge", match[1]);
                    return;
                }
                match = userAgent.match(/OPR\/([\d.]+)/i);
                if (match) {
                    setBrowser("Opera", match[1]);
                    return;
                }
                match = userAgent.match(/Opera Mini\/([\d.]+)/i);
                if (match) {
                    setBrowser("Opera Mini", match[1]);
                    return;
                }
                match = userAgent.match(/SamsungBrowser\/([\d.]+)/i);
                if (match) {
                    setBrowser("Samsung Internet", match[1]);
                    return;
                }
                match = userAgent.match(/YaBrowser\/([\d.]+)/i);
                if (match) {
                    setBrowser("Yandex", match[1]);
                    return;
                }
                match = userAgent.match(/UCBrowser\/([\d.]+)/i);
                if (match) {
                    setBrowser("UC Browser", match[1]);
                    return;
                }
                match = userAgent.match(/FxiOS\/([\d.]+)/i);
                if (match) {
                    setBrowser("Firefox", match[1]);
                    return;
                }
                match = userAgent.match(/Firefox\/([\d.]+)/i);
                if (match) {
                    setBrowser("Firefox", match[1]);
                    return;
                }
                match = userAgent.match(/CriOS\/([\d.]+)/i);
                if (match) {
                    setBrowser("Google Chrome", match[1]);
                    return;
                }
                match = userAgent.match(/Chrome\/([\d.]+)/i);
                if (match) {
                    setBrowser("Google Chrome", match[1]);
                    return;
                }
                match = userAgent.match(/Trident\/.*rv:([\d.]+)/i);
                if (match) {
                    setBrowser("Microsoft Internet Explorer", match[1]);
                    return;
                }
                match = userAgent.match(/MSIE\s([\d.]+)/i);
                if (match) {
                    setBrowser("Microsoft Internet Explorer", match[1]);
                    return;
                }
                match = userAgent.match(/Version\/([\d.]+).*Mobile\/.*Safari/i);
                if (match) {
                    setBrowser("Safari", match[1]);
                    return;
                }
                match = userAgent.match(/Version\/([\d.]+).*Safari/i);
                if (match) {
                    setBrowser("Safari", match[1]);
                    return;
                }
                // Generic browser fallback
                match = userAgent.match(/([A-Za-z]+)\/([\d.]+)/);
                if (match) {
                    setBrowser(match[1], match[2]);
                }
            }

            function detectOSFromUserAgent() {
                let match;
                if (/iPhone|iPad|iPod/i.test(userAgent)) {
                    result.os = "iOS";
                    match = userAgent.match(/OS (\d+)[._](\d+)(?:[._](\d+))?/i);
                    if (match) {
                        result.osVersion = [
                            match[1],
                            match[2],
                            match[3] || "0"
                        ].join(".");
                    }
                    return;
                }
                match = userAgent.match(/Android[\s/]([\d.]+)/i);
                if (match) {
                    result.os = "Android";
                    result.osVersion = match[1];
                    return;
                }
                if (/Windows/i.test(userAgent)) {
                    result.os = "Windows";
                    if (/Windows NT 10\.0/i.test(userAgent)) {
                        result.osVersion = "10";
                    } else if (/Windows NT 6\.4/i.test(userAgent)) {
                        result.osVersion = "10";
                    } else if (/Windows NT 6\.3/i.test(userAgent)) {
                        result.osVersion = "8.1";
                    } else if (/Windows NT 6\.2/i.test(userAgent)) {
                        result.osVersion = "8";
                    } else if (/Windows NT 6\.1/i.test(userAgent)) {
                        result.osVersion = "7";
                    } else if (/Windows NT 6\.0/i.test(userAgent)) {
                        result.osVersion = "Vista";
                    } else if (/Windows NT 5\.1/i.test(userAgent)) {
                        result.osVersion = "XP";
                    }
                    return;
                }
                if (/CrOS/i.test(userAgent)) {
                    result.os = "Chrome OS";
                    match = userAgent.match(/CrOS [^ ]+ ([\d.]+)/i);
                    if (match) {
                        result.osVersion = match[1];
                    }
                    return;
                }
                match = userAgent.match(/Mac OS X[\s/]([\d._]+)/i);
                if (match) {
                    result.os = "macOS";
                    result.osVersion = match[1].replace(/_/g, ".");
                    return;
                }
                if (/Linux/i.test(userAgent)) {
                    result.os = "Linux";
                    return;
                }
                if (/OpenBSD/i.test(userAgent)) {
                    result.os = "OpenBSD";
                    return;
                }
                if (/FreeBSD/i.test(userAgent)) {
                    result.os = "FreeBSD";
                    return;
                }
                // Use navigator.platform as a final fallback
                if (/Win/i.test(platform)) {
                    result.os = "Windows";
                } else if (/Mac/i.test(platform)) {
                    result.os = "macOS";
                } else if (/Linux/i.test(platform)) {
                    result.os = "Linux";
                }
            }
            function detectBrowserFromClientHints() {
                const uaData = nav.userAgentData;
                if (!uaData) {
                    return Promise.resolve();
                }
                const brands = Array.isArray(uaData.brands)
                    ? uaData.brands
                    : [];
                const findBrand = (...names) => {
                    const brand = brands.find(item =>
                        names.some(name =>
                            item.brand.toLowerCase().includes(name.toLowerCase())
                        )
                    );
                    return brand || null;
                };
                let brand =
                    findBrand("Microsoft Edge") ||
                    findBrand("Opera") ||
                    findBrand("Samsung Internet") ||
                    findBrand("Yandex") ||
                    findBrand("Google Chrome") ||
                    findBrand("Firefox");
                if (!brand) {
                    brand = findBrand("Chromium");
                }
                if (brand) {
                    let browserName = brand.brand;
                    if (/Microsoft Edge/i.test(browserName)) {
                        browserName = "Microsoft Edge";
                    } else if (/Google Chrome/i.test(browserName)) {
                        browserName = "Google Chrome";
                    } else if (/Samsung/i.test(browserName)) {
                        browserName = "Samsung Internet";
                    } else if (/Chromium/i.test(browserName)) {
                        browserName = "Chromium";
                    }
                    setBrowser(browserName, brand.version);
                }
                result.mobile = Boolean(uaData.mobile);
                return uaData
                    .getHighEntropyValues([
                        "platform",
                        "platformVersion",
                        "fullVersionList"
                    ])
                    .then(ua => {
                        // try to get browser version if it there.
                        if (Array.isArray(ua.fullVersionList)) {
                            const fullBrand =
                                ua.fullVersionList.find(item =>
                                    /Microsoft Edge|Google Chrome|Opera|Samsung Internet|Yandex|Firefox/i
                                        .test(item.brand)
                                ) ||
                                ua.fullVersionList.find(item =>
                                    /Chromium/i.test(item.brand)
                                );
                            if (fullBrand) {
                                let browserName = fullBrand.brand;
                                if (/Microsoft Edge/i.test(browserName)) {
                                    browserName = "Microsoft Edge";
                                } else if (/Google Chrome/i.test(browserName)) {
                                    browserName = "Google Chrome";
                                } else if (/Samsung/i.test(browserName)) {
                                    browserName = "Samsung Internet";
                                } else if (/Chromium/i.test(browserName)) {
                                    browserName = "Chromium";
                                }
                                setBrowser(browserName, fullBrand.version);
                            }
                        }
                        if (ua.platform) {
                            const clientPlatform = ua.platform;
                            if (/Windows/i.test(clientPlatform)) {
                                result.os = "Windows";
                                const majorPlatformVersion = parseInt(
                                    String(ua.platformVersion || "").split(".")[0],
                                    10
                                );

                                if (!Number.isNaN(majorPlatformVersion)) {
                                    result.osVersion =
                                        majorPlatformVersion >= 14 ? "11" : "10";
                                }
                            } else if (/Android/i.test(clientPlatform)) {
                                result.os = "Android";
                            } else if (/Chrome OS/i.test(clientPlatform)) {
                                result.os = "Chrome OS";
                            } else if (/Linux/i.test(clientPlatform)) {
                                result.os = "Linux";
                            }
                        }
                    })
                    .catch(() => {
                        if (window.console && typeof console.debug === "function") {
                            console.debug(
                                "User-Agent Client Hints unavailable, User-Agent fallback got used.",
                                error
                            );
                        }
                    });
            }
            detectOSFromUserAgent();
            detectBrowserFromUserAgent();
            root.jscd = result;
            detectBrowserFromClientHints().finally(() => {
                root.jscd = result;
            });
            return result;
        }
        jscd(window);
    </script>
<?php
if ($state === 'login') {
?>
    <title>Login / CGCC</title>
</head>
<body class="h100 row bg-def-1">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity1 z1">
    <form class="rightMg pad-b-s pad-n-v minw200 w40 h100p flex fld acjc gap10 bgc-gray border-custom-l border-custom-r z5" action="../processes/connect_login.php" method="post">
        <h1 class="sideMg txtc txt-b bold">LOGIN</h1>
        <input class="hiddeninp" type="text" id="os" name="os" autocomplete="off" tabindex="999" required readonly>
        <input class="hiddeninp" type="text" id="browser" name="browser" autocomplete="off" tabindex="999" required readonly>
        <input class="hiddeninp" type="text" id="mobile" name="mobile" autocomplete="off" tabindex="999" required readonly>
        <input class="hiddeninp" type="text" id="uad" name="uad" autocomplete="off" tabindex="999" required readonly>
        <div class="sideMg w88p flex fld">
            <label for="username">Username</label>
            <input class="inptxt border-b" type="text" id="username" name="username" placeholder="Use your account username" autocomplete="off" tabindex="1" required>
        </div>
        <div class="sideMg w88p flex fld">
            <label for="password">Password</label>
            <input class="inptxt border-b" type="password" id="password" name="password" minlength="8" placeholder="Give the correct password" autocomplete="off" tabindex="2" required>
        </div>
        <div class="sideMg pad-m-v w88p flex gap5">
            <input class="border-b" type="checkbox" checked="checked" id="sessionless" name="sessionless" autocomplete="off" tabindex="3">
            <label for="sessionless">Keep me Signed in</label>
        </div>
        <div class="sideMg w88p flex fld">
            <button type="submit" class="pad-s bgc-gold txt-n c-black" name="Login" tabindex="4">Sign in</button>
        </div>
        <div class="sideMg w88p flex fld">
          <p class="txtc">Don't have an Account? <a href="connect_it.php?state=register" class="c-orange hover-text-white" tabindex="5">Register here</a></p>
          <p class="pad-m txtc">Having problem with your account?</br><a href="../documentation/docs.php#account" class="c-orange hover-text-white" tabindex="7">check docs</a> or <a href="#" class="c-orange hover-text-white" tabindex="7">dm me</a></p>
        </div>
    </form>
    <p class="posr w60 h100p blurbg z5"></p>
<?php
} else if ($state === 'register') {
?>
  <title>Register new account / CGCC</title>
</head>
<body class="h100 row bg-def-1">
    <img src="../img/contour3bw.png" alt="" class="posf ins0 wh100 coverfit filInvert opacity1 z1">
    <form class="rightMg pad-b-s pad-n-v minw200 w40 h100p flex fld acjc gap10 bgc-gray border-custom-l border-custom-r z5" action="../processes/connect_regist.php" method="post" onsubmit="return checkMail()">
        <h1 class="sideMg txtc txt-b bold">REGISTER</h1>
        <div class="form-input-row sideMg w88p flex fld">
          <label for="email">Email</label>
          <input class="inptxt" type="text" id="email" name="email" placeholder="Your mail for validation" autocomplete="off" tabindex="1" required>
        </div>
        <div class="form-input-row sideMg w88p flex fld">
          <label for="username">Username</label>
          <input class="inptxt" type="text" id="username" name="username" placeholder="Write the desired username" autocomplete="off" tabindex="3" required>
        </div>
          <div class="form-input-row sideMg w88p flex fld">
          <label for="password">Password</label>
        <input class="inptxt" type="password" id="password" name="password" minlength="8" placeholder="Choose a good password" autocomplete="off" tabindex="4" required>
        </div>
          <div class="form-input-row sideMg w88p flex fld">
          <button type="submit" class="pad-s bgc-gold txt-n c-black" name="Register" tabindex="4">Register</button>
        </div>
        <div class="sideMg w88p flex fld">
          <p class="txtc">Already have Account? <a href="connect_it.php?state=login" class="c-orange hover-text-white" tabindex="7">then Log-In</a></p>
          <p class="pad-m txtc">Having problem with your account?</br><a href="../documentation/docs.php#account" class="c-orange hover-text-white" tabindex="7">check docs</a> or <a href="#" class="c-orange hover-text-white" tabindex="7">dm me</a></p>
        </div>
    </form>
    <p class="posr w60 h100p blurbg z5"></p>
<?php
} else {
  header ('location: connect_it.php?state=login');
  exit;
}
?>
    <div id="alertcard">
        <p id="alertcontent"></p>
        <div id="borderanimate"></div>
    </div>
    <script>
        function outputData() {
            const info = window.jscd || {};
            const os = [info.os, info.osVersion]
                .filter(value => value && value !== "-")
                .join(" ");
            const browserVersion =
                info.browserVersion && info.browserVersion !== "-"
                    ? ` (${info.browserVersion})`
                    : "";
            const majorVersion =
                info.browserMajorVersion && info.browserMajorVersion !== 0
                    ? ` ${info.browserMajorVersion}`
                    : "";
            const data = {
                os: os || "-",
                browser: `${info.browser || "-"}${majorVersion}${browserVersion}`,
                mobile: Boolean(info.mobile),
                uad: navigator.userAgent || "-"
            };
            Object.keys(data).forEach((key) => {
                const input = document.getElementById(key);
                if (input) {
                    input.value = String(data[key]);
                }
            });
        }

        function clientData() {
            jscd(window);
            outputData();
            setTimeout(outputData, 250);
        }
        if (document.readyState === "loading") {
            window.addEventListener("DOMContentLoaded", clientData);
        } else {
            clientData();
        }
        function checkMail() {
            const email = document.getElementById("email");
            if (!email) {
                return false;
            }
            const at = email.value.indexOf("@");
            if (at === -1) {
                alerter("Not a valid e-mail!");
                return false;
            }
            return true;
        }
    </script>
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
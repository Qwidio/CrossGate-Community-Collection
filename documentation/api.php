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
    <title>API & Client / CGCC Docs</title>
</head>

<body class="pad-bt minh100 def-1" id="intro">
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
                <a href="groupspublishing.php" class="posr pad-m-v pad-s-s bora-s txt-b c-lightpurple hover-white hover-pad-l16">GROUPS & PUBLISHING</a>
            </div>

            <div class="bottomMg-s10 pad-s-v flex fld border-purple-b gap5">
                <p class="leftMg-s10 txt-b c-lightpurple">Web API</p>
                <a href="#Auth" class="posr pad-s bora-s txt-s hover-white hover-pad-l16">Auth</a>
            </div>
            <div class="bottomMg-s10 pad-s-v flex fld border-purple-b gap5">
                <p class="leftMg-s10 txt-b c-lightpurple">Client Launcher</p>
                <a href="#ACQ" class="posr pad-s bora-s txt-s hover-white hover-pad-l16">Note</a>
                <a href="#clar" class="posr pad-s bora-s txt-s hover-white hover-pad-l16">Launcher API</a>
                <a href="#impl" class="posr pad-s bora-s txt-s hover-white hover-pad-l16">Example</a>
            </div>
        </aside>
        <section class="posr w75p flex fld gap-s">
            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1">
                <div class="posr flex fld gap10">
                    <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">Web API Guide</h2>
                    <p class="txt-n">Before starting, it should be noted that all WebAPI's require "API keys" that can be obtained on your Group-Flow Dashboard API panel(Panel is accessible only to Founder & Developer)</p>
                    <p class="txt-n">On every WebAPI request must include the "X-Api-Key" header with value of your groups API keys, see table & example using pororoca below</p>
                    <div class="posr sideMg w95p flex fld border-1">
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Enabled</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">True</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Header Name</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">X-Api-Key</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Value</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">
                                yourgroupsapi.token0987
                            </p>
                        </div>
                        <div class="posr flex acjc bg-1 border-purple"><img src="xapikey.png" class="w100p maxh50 containfit"></div>
                    </div>
                </div>

                <div class="posr flex fld gap10" id="Auth">
                    <h2 class="pad-s-v txt-b c-lightpurple border-purple-b">Authentication</h2>
                    <p class="txt-n">Base URL: <span class="posr bg-half-gray c-white pad-s-s bora-s">https://thedomain.com/api</span></p>
                    <h3 class="posr txt-n c-blue">Login and Session check</h3>
                    <p class="pad-n-s txt-n">Method:<span class="posr bg-half-gray c-white pad-m-s bora-s">POST</span>, route:<span class="posr bg-half-gray c-white pad-m-s bora-s">/auth.php</span></p>
                    <div class="posr sideMg w95p flex fld border-1">
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Content Type: application/json</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">
                                {<br>
                                    "username": "username",<br>
                                    "password": "password",<br>
                                    "os": "osName",<br>
                                    "address": "ipaddress-or-osids",<br>
                                    "sessionless": true<br>
                                }<br>
                            </p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Response(200 OK)</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">
                                {<br>
                                    "message": "success",<br>
                                    "profileTags": "usernames#0987",<br>
                                    "profileAttachs": "img_0987.png",<br>
                                    "profileNames": "usernames",<br>
                                    "profileJDates": "1/1/1111",<br>
                                    "profileBadge": {
                                        "campaign_badge_group_id": ["badge_stage_1", "badge_stage_2"], "multiplayer_badge_group_id": ["badge_first_win"]
                                    }<br>
                                    "profileMarkOut": {}<br>
                                }<br>
                            </p>
                        </div>
                    </div>
                    <p class="pad-n-s txt-n">Method:<span class="posr bg-half-gray c-white pad-m-s bora-s">PUT</span>, route:<span class="posr bg-half-gray c-white pad-m-s bora-s">/reauth.php</span></p>
                    <div class="posr sideMg w95p flex fld border-1">
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Content Type: application/json</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">
                                {<br>
                                    "tokens": "sessiontokens",<br>
                                    "os": "osName",<br>
                                    "address": "ipaddress-or-osids"<br>
                                }<br>
                            </p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Response(200 OK)</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">
                                {<br>
                                    "message": "Session Valid",<br>
                                    "profileTags": "usernames#0987",<br>
                                    "profileAttachs": "img_0987.png",<br>
                                    "profileNames": "usernames",<br>
                                    "profileJDates": "1/1/1111",<br>
                                    "profileBadge": {
                                        "campaign_badge_group_id": ["badge_stage_1", "badge_stage_2"], "multiplayer_badge_group_id": ["badge_first_win"]
                                    }<br>
                                    "profileMarkOut": {}<br>
                                }<br>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="posr topMg-s10 pad-n bg-thin-gray border-purple bora-m box-shad-black-1" id="ACQ">
                <h2 class="pad-s-v txt-l c-lightpurple border-purple-b">Client technical detail</h2>
                <p class="pad-n-v txt-n">
                    The client is not an absolute rules on how and what to build when interfacing with the API,
                    but rather a reference point of what the "minimum" usability that user will expect when using your launcher.
                </p>
                <p class="pad-n-v txt-n">
                    Rather than compiling language-specific C/C++ SDK wrappers, It is decided that
                    software running from the CGCC Client Launcher should communicates directly with an embedded local HTTP server running inside the launcher process.
                </p>

                <div class="posr flex fld gap10" id="clar">
                    <h2 class="pad-s-v txt-b c-lightpurple border-purple-b">Launcher API Reference</h2>
                    <p class="txt-n">Base URL: <span class="posr bg-half-gray c-white pad-s-s bora-s">[http://127.0.0.1](http://127.0.0.1):{CGCC_PORT}</span></p>
                    <p class="pad-n-s txt-n">Method:<span class="posr bg-half-gray c-white pad-m-s bora-s">GET</span>, route:<span class="posr bg-half-gray c-white pad-m-s bora-s">/get_user_info</span></p>
                    <p class="pad-n-s txt-n">Retrieves public profile details and a list of all unlocked badge IDs grouped under their respective badge group references.</p>
                    <div class="posr sideMg w95p flex fld border-1">
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Headers</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">application/json</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Response(200 OK)</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">
                                {<br>
                                    "status": "success",<br>
                                    "profileTags": "usernames#0987",<br>
                                    "username": "usernames",<br>
                                    "obtainedBadges": {
                                        "campaign_badge_group_id": ["badge_stage_1", "badge_stage_2"], "multiplayer_badge_group_id": ["badge_first_win"]
                                    }<br>
                                }<br>
                            </p>
                        </div>
                    </div>
                    <p class="pad-n-s txt-n">Method:<span class="posr bg-half-gray c-white pad-m-s bora-s">POST</span>, route:<span class="posr bg-half-gray c-white pad-m-s bora-s">/unlock_badge</span></p>
                    <p class="pad-n-s txt-n">Triggers an badge unlock request. CGCC validates the request, updates the backend server (/api/updateBadges.php), refreshes the UI, and pops up a desktop the popup toast.</p>
                    <div class="posr sideMg w95p flex fld border-1">
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Headers</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">application/json</p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Body Payload</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">
                                {<br>
                                "badge_id": "badge_stage_1",<br>
                                "group_ref": "group_campaign"<br>
                                }
                            </p>
                        </div>
                        <div class="posr w100p flex border-b">
                            <p class="posr pad-s-v pad-n-s w50p txt-n border-r">Response(200 OK)</p>
                            <p class="posr pad-s-v pad-n-s w50p txt-n bg-3 ovh-s">
                                {<br>
                                "status": "queued",<br>
                                "badge_id": "badge_stage_1"<br>
                                }
                            </p>
                        </div>
                    </div>
                </div>

                <div class="posr topMg-s10 flex fld gap10" id="impl">
                    <h2 class="pad-s-v txt-b c-lightpurple border-purple-b">Implementation Examples</h2>
                    <h3 class="posr txt-n c-blue">Via cURL / Testing CLI</h3>
                    <p class="pad-n-s txt-n">Fetch User Info</p>
                    <p class="posr leftMg-s10 rightMg-s10 pad-s-v pad-n-s w100p bg-3 border-1">
                        curl -X GET "http://127.0.0.1:$CGCC_PORT/get_user_info"
                    </p>
                    <p class="pad-n-s txt-n">Unlock Badge</p>
                    <p class="posr leftMg-s10 rightMg-s10 pad-s-v pad-n-s w100p bg-3 border-1">
                        curl -X POST "http://127.0.0.1:$CGCC_PORT/unlock_badge" \<br>
                        <span class="posr leftMg-s10 c-white bg-transparent">-H "Content-Type: application/json" \</span><br>
                        <span class="posr leftMg-s10 c-white bg-transparent">-d '{"badge_id": "badge_stage_1", "group_ref": "group_campaign"}'</span>
                    </p>

                    <h3 class="posr topMg-s10 txt-n c-blue">C#(.NE/Unity)</h3>
                    <pre class="posr leftMg-s10 rightMg-s10 pad-n-s w100p bg-3 c-white border-1">
                        <code class="posr w100p bg-transparent c-white">
using System;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

public class CGCCIntegration
{
    private readonly string _baseUrl;
    private readonly HttpClient _httpClient = new HttpClient();

    public CGCCIntegration()
    {
        string port = Environment.GetEnvironmentVariable("CGCC_PORT") ?? "5000";
        _baseUrl = $"http://127.0.0.1:{port}";
    }

    /// <summary>
    /// Fetches public profile details and unlocked badges.
    /// </summary>
    public async Task<string> GetUserInfoAsync()
    {
        try
        {
            HttpResponseMessage response = await _httpClient.GetAsync($"{_baseUrl}/get_user_info");
            return await response.Content.ReadAsStringAsync();
        }
        catch (Exception ex)
        {
            Console.WriteLine($"CGCC API Error: {ex.Message}");
            return null;
        }
    }

    /// <summary>
    /// Unlocks a badge/achievement.
    /// </summary>
    public async Task<bool> UnlockBadgeAsync(string badgeId, string groupRef)
    {
        try
        {
            string jsonBody = $"{{\"badge_id\":\"{badgeId}\",\"group_ref\":\"{groupRef}\"}}";
            var content = new StringContent(jsonBody, Encoding.UTF8, "application/json");

            HttpResponseMessage response = await _httpClient.PostAsync($"{_baseUrl}/unlock_badge", content);
            return response.IsSuccessStatusCode;
        }
        catch (Exception ex)
        {
            Console.WriteLine($"CGCC Unlock Failed: {ex.Message}");
            return false;
        }
    }
}
                        </code>
                    </pre>

                    <h3 class="posr topMg-s10 txt-n c-blue">Godot(GDScript)</h3>
                    <pre class="posr leftMg-s10 rightMg-s10 pad-n-s w100p bg-3 c-white border-1">
                        <code class="posr w100p bg-transparent c-white">
extends Node

var cgcc_port: String = ""
var base_url: String = ""

func _ready():
cgcc_port = OS.get_environment("CGCC_PORT")
if cgcc_port == "":
    cgcc_port = "5000" # Fallback port for local testing
base_url = "http://127.0.0.1:" + cgcc_port

## Fetch public user info and obtained badges
func get_user_info(callback: Callable):
var http_request = HTTPRequest.new()
add_child(http_request)

http_request.request_completed.connect(
    func(result, response_code, headers, body):
        if response_code == 200:
            var json = JSON.new()
            if json.parse(body.get_string_from_utf8()) == OK:
                callback.call(json.get_data())
        else:
            push_error("CGCC: Failed to fetch user info. Code: %d" % response_code)
        http_request.queue_free()
)

http_request.request(base_url + "/get_user_info")

## Unlock a badge achievement
func unlock_badge(badge_id: String, group_ref: String):
var http_request = HTTPRequest.new()
add_child(http_request)

var payload = {
    "badge_id": badge_id,
    "group_ref": group_ref
}
var json_data = JSON.stringify(payload)
var headers = ["Content-Type: application/json"]

http_request.request_completed.connect(
    func(result, response_code, headers, body):
        if response_code == 200:
            print("CGCC: Badge unlock sent successfully!")
        else:
            push_error("CGCC: Failed to unlock badge. Code: %d" % response_code)
        http_request.queue_free()
)

http_request.request(base_url + "/unlock_badge", headers, HTTPClient.METHOD_POST, json_data)
                        </code>
                    </pre>
                </div>
            </div>

            <div class="posr pad-n bg-thin-gray border-purple bora-m box-shad-black-1">
                <h2  class="posr pad-s-v txt-l c-lightpurple border-purple-b">References</h2>
                <div class="posr pad-s-v flex gap10">
                    <a href="https://pororoca.io/" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">Pororoca</a>
                    <a href="groupspublishing.php" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">Groups & Publishing Docs</a>
                    <a href="../Groups/index.php" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">Groups-Flow (to signIn)</a>   
                    <a href="../Publishing/Manage.php" target="_blank" class="posr pad-m bg-half-gray bold bora-s hover-white">Publishing Dashboard</a> 
                </div>
            </div>
        </section>
    </main>

    <?php include_once '../extra/footers.php';?>
</body>
</html>
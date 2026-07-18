<?php
if (!isset($root_route) || empty($root_route) || $root_route === "") {
    $root_route = "";
}
if (!isset($specifics) || empty($specifics) || $specifics === "") {
    $specifics = "";
}
?>
<dialog id="reportDialog" class="posf c0 w100vh minw50 maxw100 h100p dp-none fld bg-def-1 ovh-s z999">
    <div class="posr w100p blurbg flex">
        <h2 class="posr pad-s w100p txtc txt-b">
            Report 
            <?php if ($specifics != "collection") { ?>
                Form
            <?php } else { ?>
                Collection
            <?php } ?>
        </h2>
        <p class="posr pad-s-v pad-n-s txt-b hover-red" onclick="uniDisplaySwitch('reportDialog')">X</p>
    </div>
    <form id="reportForm" class="posr pad-n-v wh100p blurbg flex fld gap10" action="<?php echo $root_route;?>report.php" method="post" enctype="multipart/form-data">
        <input class="hiddeninp" type="text" name="reportsource" hidden required>
        <input class="hiddeninp" type="text" name="ids" hidden required>
        <div class="posr topMg-s10 sideMg w100p flex acjc ovh-v">
            <label class="txt-n bold">Screen capture(not required)</label>
        </div>
        <div class="posr sideMg maxw100 flex gap5 border-1 ovh-v">
            <img id="reportscreencapture" class="posr r16-9 h40 flex fld acjc gap5 bg-white">
            <input class="posa c0 wh100p txtc c-black" type="file" name="file" accept="image/*" onchange="uniLoadFile(event, 'reportscreencapture')">
        </div>
        <div class="posr sideMg w88p flex fld">
            <label for="reportReason">Reason</label>
            <select name="reportReason" class="inpselect w100p" required>
                <option value="" selected disabled>select one</option>
                <?php
                if ($specifics != "collection") {
                ?>
                <option name="reportReason" value="spambot" required>spam/bot</option>
                <option name="reportReason" value="aislop" required>AI Slop</option>
                <option name="reportReason" value="inappropriate" required>Inappropriate Content</option>
                <option name="reportReason" value="misinformation" required>Spreading Misinformation</option>
                <option name="reportReason" value="hatespeech" required>Hate Speech</option>
                <option name="reportReason" value="copysteal" required>Stealing works & taking credits without any modification</option>
                <?php
                }
                ?>
                <?php
                if ($specifics === "collection") {
                ?>
                <option name="reportReason" value="fraud" required>Fraud</option>
                <option name="reportReason" value="defamatory" required>Defamatory</option>
                <option name="reportReason" value="broken" required>Broken</option>
                <option name="reportReason" value="copyright" required>Legal Violation</option>
                <option name="reportReason" value="malicious" required>Harmful - contain Malware/Virus</option>
                <?php
                }
                ?>
            </select>
        </div>
        <div class="posr sideMg w88p flex fld">
            <label for="fullcontext">Enter additional information that you feel is relevant here:</label>
            <textarea class="inptxt h20 border-b ovh-s" type="text" id="fullcontext" name="fullcontext" placeholder="e.g. the detailed context if needed" autocomplete="off" required></textarea>
        </div>
        <div class="posr sideMg w88p flex fld">
            <input class="pad-s txtc txt-n bgc-purple border-1 border-hover-white hover-text-orange points" type="submit" name="submit" value="Report">
        </div>
    </form>
</dialog> 
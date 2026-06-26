<?php
if (!isset($root_route) || empty($root_route) || $root_route === "") {
    $root_route = "";
}
if (isset($_SESSION['prev_loc'])) {
    $prev_loc = $_SESSION['prev_loc'];
} else {
    $prev_loc = "index.php";
};
require_once $root_route . 'processes/database.php';
if (isset($_POST['submit'])) {
    $errors = array();
    require_once $root_route . 'secureSession.php';
    if (isset($_SESSION['profileTags'])) {
        $aidis = $_SESSION['profileTags'];
    } else {
        header ('location: index.php');
        exit;
    }
    $reportedIds = $_POST['ids'];
    $reportSource = $_POST['reportsource'];
    $reportReason = $_POST['reportReason'];
    $fullcontext = $_POST['fullcontext'];
    $rsc = "";
    $reportSource = htmlspecialchars($reportSource, ENT_QUOTES, 'UTF-8');
    $reportReason = htmlspecialchars($reportReason, ENT_QUOTES, 'UTF-8');
    $fullcontext = htmlspecialchars($fullcontext, ENT_QUOTES, 'UTF-8');
    if (isset($_FILES["file"]["name"]) && $_FILES['file']['name'][0] != "") {
        $targetdir = $root_route . "zreport/";
        $rsc = basename($_FILES["file"]["name"]);
        $tarfilepath = $targetdir . strtolower($rsc);
        $fileType = pathinfo($tarfilepath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'svg', 'png', 'jpeg', 'webp');
        if($_FILES["file"]["size"] < 5242880) {
            if(in_array($fileType, $allowTypes)) {
                $random = bin2hex(random_bytes(8));
                $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "", $rsc);
                $createfromformat = DateTime::createFromFormat('Y/m/d', date('Y/m/d'));
                $unixdate = $createfromformat->getTimestamp();
                $rsc = $aidis . '_' . $unixdate . '_' . $random . '_' . $clean_name;
                $tempPath = $_FILES["file"]["tmp_name"];
                $targetPath = $targetdir . $rsc;
                if(move_uploaded_file($tempPath, $targetPath)) {
                    chmod($targetPath, 0644);
                    $stmt_insert_reports = $connects->prepare("INSERT INTO reports (reporters, reportedIds, reportSource, reportReason, fullcontext, dates, capture) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt_insert_reports->bind_param("sssssss", $aidis, $reportedIds, $reportSource, $reportReason, $fullcontext, date('d/m/Y'), $rsc);
                    if($stmt_insert_reports->execute()){
                        if (!empty($errors)){
                            $_SESSION['corsmsg'] = $reportSource . ' reported, ' . $errors;
                        } else {
                            $_SESSION['corsmsg'] = 'reported ' . $reportSource;
                        }
                        $stmt_insert_reports->close();
                        header('location: ' . $root_route . $prev_loc);
                        exit;
                    } else {
                        $_SESSION['corsmsg'] = 'failed to make report. ' . $stmt_insert_reports->error_get_last;
                        $stmt_insert_reports->close();
                        header('location: ' . $root_route . $prev_loc);
                        exit;
                    };
                } else {
                    $_SESSION['corsmsg'] = 'An error occured when uploading report attachment';
                    header('location: ' . $root_route . $prev_loc);
                    exit;
                };
            } else {
                $_SESSION['corsmsg'] = 'only jpg, jpeg, png, webp, & gif format allowed for the report attachment';
                header('location: ' . $root_route . $prev_loc);
                exit;
            };
        } else {
            $rsc = "";
        };
    };
    $stmt_insert_reports = $connects->prepare("INSERT INTO reports (reporters, reportedIds, reportSource, reportReason, fullcontext, dates, capture) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_insert_reports->bind_param("sssssss", $aidis, $reportedIds, $reportSource, $reportReason, $fullcontext, date('d/m/Y'), $rsc);
    if($stmt_insert_reports->execute()){
        if (!empty($errors)){
            $_SESSION['corsmsg'] = $reportSource . ' reported, ' . $errors;
        } else {
            $_SESSION['corsmsg'] = 'reported ' . $reportSource;
        }
        $stmt_insert_reports->close();
        header('location: ' . $root_route . $prev_loc);
        exit;
    } else {
        $_SESSION['corsmsg'] = 'failed to make report. ' . $stmt_insert_reports->error_get_last;
        $stmt_insert_reports->close();
        header('location: ' . $root_route . $prev_loc);
        exit;
    };
} else {
    header('location: ' . $root_route . $prev_loc);
    exit;
};
?>
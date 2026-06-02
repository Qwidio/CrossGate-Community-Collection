<?php
$hosts = "localhost";
$names = "root";
$passw = "";
$dbase = "cgbackup";
$connects = new mysqli($hosts, $names, $passw, $dbase);
if ($connects->connect_error) {
    die("Connection Failed: " . $connects->connect_error);
}
session_start();
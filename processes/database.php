<?php
$hosts = "localhost";
$names = "root";
$passw = "";
$dbase = "db_cgcc";
$connects = new mysqli($hosts, $names, $passw, $dbase);
if ($connects->connect_error) {
    die("Connection Failed: " . $connects->connect_error);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
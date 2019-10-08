<?php
require_once 'vendor/autoload.php';
session_start();

date_default_timezone_set('UTC');
error_reporting(E_ALL);

if (!function_exists('dd')) {
    function dd($x) {
        dump($x);
        die;
    }
 }

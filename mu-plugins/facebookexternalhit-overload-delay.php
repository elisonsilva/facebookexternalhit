<?php
/*
Plugin Name: facebookexternalhit
Plugin URI: #
Version: 1.0
Description: Sobrecarga de facebookexternalhit tentar novamente depois
Author: Welison Silva
Author URI: https://www.welisonsilva.com/
License: GPLv2
*/

// To prevent calling the plugin directly
if (!function_exists('add_action')) {
    echo 'Please don&rsquo;t call the plugin directly. Thanks :)';
    exit;
}


//Add your code here

// Número de solicitações permitidas para rastreador do Facebook por segundo.
const FACEBOOK_REQUEST_THROTTLE = 5;
const FACEBOOK_REQUESTS_JAR = __DIR__ . '/../uploads/cache/.fb_requests';
const FACEBOOK_REQUESTS_LOCK = __DIR__ . '/../uploads/cache/.fb_requests.lock';

function handle_lock($lockfile)
{
    flock(fopen($lockfile, 'w'), LOCK_EX);
}

$ua = $_SERVER['HTTP_USER_AGENT'] ?? false;
if ($ua && strpos($ua, 'facebookexternalhit') !== false) {
    handle_lock(FACEBOOK_REQUESTS_LOCK);

    $jar = @file(FACEBOOK_REQUESTS_JAR);
    $currentTime = time();
    $timestamp = $jar[0] ?? time();
    $count = $jar[1] ?? 0;

    if ($timestamp == $currentTime) {
        $count++;
    } else {
        $count = 0;
    }

    file_put_contents(FACEBOOK_REQUESTS_JAR, "$currentTime\n$count");

    if ($count >= FACEBOOK_REQUEST_THROTTLE) {
        header("HTTP/1.1 429 Too Many Requests", true, 429);
        header("Retry-After: 60");
        die;
    }
}

// Tudo sob esse comentário acontece apenas se a solicitação for "legítima".

// $filePath = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'];
// if (is_readable($filePath)) {
//     header("Content-Type: image/png");
//     readfile($filePath);
// }

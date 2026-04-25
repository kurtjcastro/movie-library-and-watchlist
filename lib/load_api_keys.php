<?php
// kc784 | 4/17
// load api keys locally and from environment variables

$env_keys = ["MOVIES_API_KEY"];

$ini = @parse_ini_file(".env");
$API_KEYS = [];
foreach ($env_keys as $key) {
    if ($ini && isset($ini[$key])) {
        $API_KEY = $ini[$key];
        $API_KEYS[$key] = $API_KEY;
    } else {
        $API_KEY = getenv($key);
        $API_KEYS[$key] = $API_KEY;
    }
    if (!isset($API_KEYS[$key]) || !$API_KEYS[$key]) {
        error_log("Failed to load api key for env key $key");
    }
    unset($API_KEY);
}
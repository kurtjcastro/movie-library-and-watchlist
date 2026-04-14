<?php
ob_start();
// kc784 | 4/13
// test file for MoviesDatabase API verification of connection + response
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);
$apiKey = $_ENV["MOVIES_API_KEY"];

$url = "https://moviesdatabase.p.rapidapi.com/titles?list=most_pop_movies&limit=5";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "x-rapidapi-host: moviesdatabase.p.rapidapi.com",
        "x-rapidapi-key: $apiKey"
    ]
]);

$response = curl_exec($curl);
$err = curl_error($curl);

if ($err) {
    $data = json_decode($response, true);
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

?>
<?php require(__DIR__ . "/../../partials/flash.php"); ?>
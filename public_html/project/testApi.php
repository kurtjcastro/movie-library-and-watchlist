<?php
// kc784 | 4/13
// test file for MoviesDatabase API verification of connection + response
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$url = "https://moviesdatabase.p.rapidapi.com/titles";
$data = ["list" => "top_rated_english_250", "limit" => 5];

try {
    $result = get($url, "MOVIES_API_KEY", $data, true, "moviesdatabase.p.rapidapi.com");
    $data = json_decode($result["response"], true);
    echo "<pre>";
    print_r($data);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
<?php require(__DIR__ . "/../../partials/flash.php"); ?>
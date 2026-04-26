<?php
// kc784 | 4/19
// delete page to remove movie from db
// fetch movie by id, check perms, delete, redirect

require(__DIR__ . "/../../partials/nav.php"); 
is_logged_in(true);

$id = se($_GET, "id", -1, false); // grabs id from url

if ($id < 1) { // check if id valid
    flash("Invalid movie id", "danger"); 
    header("Location: list_movies.php"); // redirect to list
    die();
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM Movies WHERE id = :id"); // check if movie exists
$stmt->execute([":id" => $id]);
$movie = $stmt->fetch();

if (!$movie) { // movies not found
    flash("Movie not found", "danger");
    header("Location: list_movies.php");
    die();
}

// delete movies
$stmt = $db->prepare("DELETE FROM Movies WHERE id = :id");
try {
    $stmt->execute([":id" => $id]);
    flash("Movie deleted successfully", "success"); // delete success message
} catch (Exception $e) {
    flash("Error deleting movie", "danger"); // generic user friendly message
    error_log("Delete movie error: " . var_export($e, true)); // logs technical error message
}

// redirect to list
$redirect = "list_movies.php";
if (isset($_GET["title"]) || isset($_GET["title_type"]) || isset($_GET["sort"]) || isset($_GET["order"]) || isset($_GET["limit"])) {
    $params = [];
    foreach(["title", "title_type", "sort", "order", "limit"] as $key) {
        if (isset($_GET[$key]) && $_GET[$key] !== "") {
            $params[$key] = $_GET[$key];
        }
    }
    if (!empty($params)) {
        $redirect .= "?" . http_build_query($params);
    }
}
header("Location: $redirect");
die();
?>
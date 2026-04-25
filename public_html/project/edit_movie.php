<?php
// kc784 | 4/19
// edit page for updating movie
// fetch movie by id + prefill with existing data
// validate client + server side
// update in db
// redirect if invalid id or missing

require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$id = se($_GET, "id", -1, false); // get id from url

if ($id < 1) { // check if valid id
    flash("Invalid movie id", "danger");
    header("Location: list_movies.php"); // redirect to list
    die();
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM Movies WHERE id = :id"); // fetch movie by id
$stmt->execute([":id" => $id]);
$movie = $stmt->fetch(); // grabs single row

if (!$movie) { // if movie not found
    flash("Movie not found", "danger");
    header("Location: list_movies.php");
    die();
}
// uses POST data if a form was submitted or existing data for prefill
$title = se($_POST, "title", $movie["title"], false);
$release_year = se($_POST, "release_year", $movie["release_year"], false);
$image_url = se($_POST, "image_url", $movie["image_url"], false);
$title_type = se($_POST, "title_type", $movie["title_type"], false);
?>

<h3>Edit Movie</h3>
<!-- copied from create_movie.php -->
<form onsubmit="return validate(this)" method="POST"> <!-- js validation before submission --> 
    <div>
        <label for="title">Title</label>
        <!-- for sticky value from post -->
        <input type="text" id="title" name="title" required maxlength="255" value="<?php echo htmlspecialchars($title); ?>" />
    </div>
    <div>
        <label for="release_year">Release Year</label>
        <input type="number" id="release_year" name="release_year" min="1888" max="2030" value="<?php echo htmlspecialchars($release_year); ?>" />
    </div>
    <div> 
        <label for="image_url">Image URL</label>
        <input type="url" id="image_url" name="image_url" maxlength="500" value="<?php echo htmlspecialchars($image_url); ?>" />
    </div>
    <div> 
        <label for="title_type">Title Type</label>
        <!-- dropdown type selection --> 
         <select id="title_type" name="title_type"> 
            <option value="">-- Select --</option> 
            <option value="movie" <?php if ($title_type === 'movie') echo 'selected'; ?>>Movie</option> 
            <option value="tvSeries" <?php if ($title_type === 'tvSeries') echo 'selected'; ?>>TV Series</option> 
            <option value="short" <?php if ($title_type === 'short') echo 'selected'; ?>>Short</option> 
            <option value="tvMovie" <?php if ($title_type === 'tvMovie') echo 'selected'; ?>> TV Movie </option>
            <option value="tvSpecial" <?php if ($title_type === 'tvSpecial') echo 'selected'; ?>>TV Special</option> 
        </select>
    </div>
    <input type="submit" value="Save Changes" /> <!-- submit --> 
</form>

<script>
    // kc784 | 4/19
    // client side validation edit form
    // title required, year in range, url starts with http

function validate(form) {
    let title = form.title.value.trim(); // grab + trim title input
    let releaseYear = form.release_year.value; // release year input
    let imageUrl = form.image_url.value.trim(); // grab + trim image url input
    let isValid = true;

    if (title.length === 0) {
        flash("Title is required", "warning"); // title field cant be empty
        isValid = false;
    }
    if (releaseYear && (parseInt(releaseYear) < 1888 || parseInt(releaseYear) > 2030)) {
        flash("Release year must be between 1888 and 2030", "warning");
        isValid = false;
    }
    if (imageUrl && !imageUrl.startsWith("http")) { // url has to start with http
        flash("Image URL must be a valid URL", "warning");
        isValid = false;
    }
    return isValid;
}
</script>

<?php 
// kc784 | 4/19
// server side validation + update

if (isset($_POST["title"])) {
    $title = se($_POST, "title", "", false);
    $release_year = se($_POST, "release_year", "", false);
    $image_url = se($_POST, "image_url", "", false);
    $title_type = se($_POST, "title_type", "", false);

    // copied from create_movie.php
    $hasError = false; // track for validation fails

        // validates title
        if (empty(trim($title))) {
            flash("Title is required", "danger");
            $hasError = true;
        }
        // validates release year 
        if (!empty($release_year)) {
            $release_year = (int)$release_year; // casts to int
            if ($release_year < 1888 || $release_year > 2030) {
                flash("Release year must be between 1888 and 2030", "danger");
                $hasError = true;
            }
        } else {
            $release_year = null; // set null if empty
        }
        // validate image url 
        if (!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
            flash("Invalid image URL", "danger");
            $hasError = true;
        }
        // validate title type
        $valid_types = ["movie", "tvSeries", "short", "tvMovie", "tvSpecial", ""];
        if (!in_array($title_type, $valid_types)) {
            flash("Invalid title type", "danger");
            $hasError = true;
        }

        if (!$hasError) { // only updates if all has been validated 
            $stmt = $db->prepare( // prepares updates statement 
                "UPDATE Movies SET title = :title, release_year = :release_year, image_url = :image_url, title_type = :title_type WHERE id = :id"
            ); 
            try {
                $stmt->execute([
                    ":title" => $title,
                    ":release_year" => $release_year,
                    ":image_url" => !empty($image_url) ? $image_url : null, // stores null if empty 
                    ":title_type" => !empty($title_type) ? $title_type : null, // stores null if empty also
                    ":id" => $id 
                ]);
                flash("Movie updated successfully!", "success"); // update success message
            } catch (Exception $e) {
                flash("Error updating movie", "danger"); // user friendly generic error 
                error_log("Edit movie error " . var_export($e, true)); // logs technical error
            }
        }
    }
    ?>
    <?php require (__DIR__ . "/../../partials/flash.php"); ?> 


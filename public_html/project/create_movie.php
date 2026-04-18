<?php
// kc784 | 4/17
// create page for manual addition of movies to database
// build form with title, release year, image URL, and title type
// validate input on client and server side
// add data to movies table 
// use sticky form

require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true); 
?>

<h3>Add Movie</h3>  <!-- movie form --> 
<form onsubmit="return validate(this)" method="POST"> <!-- js validation before submission --> 
    <div>
        <label for="title">Title</label>
        <!-- for sticky value from post -->
        <input type="text" id="title" name="title" required maxlength="255" value="<?php se($_POST, 'title'); ?>" />
    </div>
    <div>
        <label for="release_year">Release Year</label>
    </div>
    <div> 
        <label for="image_url">Image URL</label>
        <input type="url" id="image_url" name="image_url" maxlength="500" value="<?php se($_POST, 'image_url'); ?>" />
    </div>
    <div> 
        <label for="title_type">Title Type</label>
        <!-- dropdown type selection --> 
         <select id="title_type" name="title_type"> 
            <option value="">-- Select --</option> 
            <option value="movie" <?php if (se($_POST, 'title_type', '', false) === 'movie') echo 'selected'; ?>>Movie</option> 
            <option value="tvSeries" <?php if (se($_POST, 'title_type','', false) === 'tvSeries') echo 'selected'; ?>>TV Series</option> 
            <option value="short" <?php if (se($_POST, 'title_type', '', false) === 'short') echo 'selected'; ?>>Short</option> 
            <option value="tvMovie" <?php if (se($_POST, 'title_type', '', false) === 'tvMovie') echo 'selected'; ?>> TV Movie </option>
            <option value="tvSpecial" <?php if (se($_POST, 'title_type', '', false) === 'tvSpecial') echo 'selected'; ?>>TV Special</option> 
        </select>
    </div>
    <input type="submit" value="Add Movie" /> <!-- submit --> 
</form>

<script>
    // kc784 | 4/17
    // movie form client side validation 
    // check for not empty title, release year range, http at start of image url
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
        if (imageUrl && !imageUrl.startWith("http")) { // url has to start with http
            flash("Image URL must be a valid URL", "warning");
            isValid = false;
        }
        return isValid;
    }

</script>

<?php 
// kc784 | 4/17
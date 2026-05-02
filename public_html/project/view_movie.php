<?php
// kc784 | 4/19 
// detail page for viewing details of a movie
// fetch movie by id + display details
// edit + delete links
// redirect if id invalid or missing

require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

// kc784 | 4/19
$id = se($_GET, "id", -1, false); // grab id from url or default -1

if ($id < 1) { // check id validity
    flash("Invalid movie id", "danger");
    header("Location: list_movies.php"); // redirect back to list
    die();
}
$db = getDB(); 
$stmt = $db->prepare("SELECT * FROM Movies WHERE id = :id"); // grab movie by id
$stmt->execute([":id" => $id]);
$movie = $stmt->fetch(); // grabs single row

if (!$movie) { // if movie not found
    flash("Movie not found", "danger");
    header("Location: list_movies.php");
    die();
}

// kc784 | 5/2
// handles remove and add from user list

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"], $_POST["movie_id"])) {
    $action = $_POST["action"];
    $movie_id = (int)$_POST["movie_id"];
    $user_id = get_user_id();

    if ($action === "add") {
        $stmt = $db->prepare("SELECT id, is_active FROM UserMovies WHERE user_id = :user_id AND movie_id = :movie_id");
        $stmt->execute([":user_id" => $user_id, ":movie_id" => $movie_id]);
        $existing = $stmt->fetch();

        if ($existing && $existing["is_active"] == 0) {
            $stmt = $db->prepare("UPDATE UserMovies SET is_active = 1 WHERE id = :id");
            $stmt->execute([":id" => $existing["id"]]);
            flash("Movie added to your list.", "success");
        } else if (!$existing) {
            $stmt = $db->prepare("INSERT INTO UserMovies (user_id, movie_id) VALUES (:user_id, :movie_id)");
            try {
                $stmt->execute([":user_id" => $user_id, ":movie_id" => $movie_id]);
                flash("Movie added to your list.", "success");
            } catch (Exception $e) {
                flash("Error adding movie to list.", "danger");
                error_log("Add to list error: " . var_export($e, true));
            }
        } else {
            flash("Movie already in list.", "warning");
        }
    } else if ($action === "remove") {
        $stmt = $db->prepare("UPDATE UserMovies SET is_active = 0 WHERE user_id = :user_id AND movie_id = :movie_id");
        $stmt->execute([":user_id" => $user_id, ":movie_id" => $movie_id]);
        flash("Movie removed from your list", "success");
    }
}
?>

<!-- kc784 | 4/19 -->
<h3><?php se($movie, 'title'); ?></h3> <!-- title as heading -->

<div>
    <p><strong>Release Year:</strong> <?php echo se($movie, 'release_year', 'N/A', false); ?></p>
    <p><strong>Type:</strong> <?php echo se($movie, 'title_type', 'N/A', false); ?></p>
    <p><strong>Source:</strong> <?php echo $movie['is_api'] ? 'API' : 'Manual'; ?></p>
    <p><strong>API ID:</strong> <?php echo se($movie, 'api_id', 'N/A', false); ?></p>
    <p><strong>Added:</strong> <?php se($movie, 'created'); ?></p>
    <p><strong>Last Modified:</strong> <?php se($movie, 'modified'); ?></p>

    <?php if (!empty($movie['image_url'])) : ?> <!-- shows image if available -->
        <p><img src="<?php se($movie, 'image_url'); ?>" alt="<?php se($movie, 'title'); ?>" style="max-width: 200px;" /></p>
    <?php endif; ?>
</div>

<?php
// kc784 | 5/2
// check if user has movie in list
$user_id = get_user_id();
$stmt = $db->prepare("SELECT id FROM UserMovies WHERE user_id = :user_id AND movie_id = :movie_id AND is_active = 1");
$stmt->execute([":user_id" => $user_id, ":movie_id" => $id]);
$isFavorite = $stmt->fetch();
?>

<?php if ($isFavorite) : ?>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="remove" />
        <input type="hidden" name="movie_id" value="<?php se($movie, 'id'); ?>" />
        <input type="submit" value="Remove from My List" />
    </form>
<?php else : ?>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="movie_id" value="<?php se($movie, 'id'); ?>" />
        <input type="submit" value="Add to My List" />
    </form>
<?php endif; ?>

<div>
    <!-- links -->
     <a href="edit_movie.php?id=<?php se($movie, 'id'); ?>">Edit</a> |
     <a href="delete_movie.php?id=<?php se($movie, 'id'); ?>" onclick="return confirm('Are you sure?')">Delete</a> |
     <a href="list_movies.php">Back to List</a>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>
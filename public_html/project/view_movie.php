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

<?php endif; ?>

<div>
    <!-- links -->
     <a href="edit_movie.php?id=<?php se($movie, 'id'); ?>">Edit</a> |
     <a href="delete_movie.php?id=<?php se($movie, 'id'); ?>" onclick="return confirm('Are you sure?')">Delete</a> |
     <a href="list_movies.php">Back to List</a>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>
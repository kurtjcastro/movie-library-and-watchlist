<?php
// kc784 | 5/2
// my movies page
// shows all movies in user's list
// filters/sorts/limits with count
// link to view, remove, and remove all
ob_start();
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$user_id = get_user_id();
$db = getDB();
?>

<h3>My Movies</h3>

<!-- kc784 | 5/2 filter/sort form --> 
 <form method="GET">
    <div>
        <label for="title">Search Title</label>
        <input type="text" id="title" name="title" value="<?php se($_GET, 'title'); ?>" />
    </div>
    <div>
        <label for="title_type">Type</label>
        <select id="title_type" name="title_type">
            <option value="">All</option>
            <option value="movie" <?php if (se($_GET, 'title_type', '', false) === 'movie') echo 'selected'; ?>>Movie</option>
            <option value="tvSeries" <?php if (se($_GET, 'title_type', '', false) === 'tvSeries') echo 'selected'; ?>>TV Series</option>
            <option value="short" <?php if (se($_GET, 'title_type', '', false) === 'short') echo 'selected'; ?>>Short</option>
            <option value="tvMovie" <?php if (se($_GET, 'title_type', '', false) === 'tvMovie') echo 'selected'; ?>>TV Movie</option>
            <option value="tvSpecial" <?php if (se($_GET, 'title_type', '', false) === 'tvSpecial') echo 'selected'; ?>>TV Special</option>
        </select>
    </div>
    <div>
        <label for="sort">Sort By</label>
        <select id="sort" name="sort">
            <option value="title" <?php if (se($_GET, 'sort', '', false) === 'title') echo 'selected'; ?>>Title</option>
            <option value="release_year" <?php if (se($_GET, 'sort', '', false) === 'release_year') echo 'selected'; ?>>Release Year</option>
            <option value="created" <?php if (se($_GET, 'sort', '', false) === 'created') echo 'selected'; ?>>Date Added</option>
        </select>
    </div>
    <div>
        <label for="order">Order</label>
        <select id="order" name="order">
            <option value="ASC" <?php if (se($_GET, 'order', '', false) === 'ASC') echo 'selected'; ?>>Ascending</option>
            <option value="DESC" <?php if (se($_GET, 'order', '', false) === 'DESC') echo 'selected'; ?>>Descending</option>
        </select>
    </div>
    <div>
        <label for="limit">Results Per Page </label>
        <input type="number" id="limit" name="limit" min="1" max="100" value="<?php echo se($_GET, 'limit', '10', false); ?>" />
    </div>
    <input type="submit" value="Filter" />
 </form>

 <?php
 // kc784 | 5/2
 // handles remove all
 if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_all"])) {
    $stmt = $db->prepare("UPDATE UserMovies SET is_active = 0 WHERE user_id = :user_id AND is_active = 1");
    $stmt->execute([":user_id" => $user_id]);
    flash("All movies removed from your list.", "success");
 }

 // single remove
 if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_movie_id"])) {
    $remove_id = (int)$_POST["remove_movie_id"];
    $stmt = $db->prepare("UPDATE UserMovies SET is_active = 0 WHERE user_id = :user_id AND movie_id = :movie_id");
    $stmt->execute([":user_id" => $user_id, ":movie_id" => $remove_id]);
    flash("Movie removed from your list.", "success");
 }

 // total count of user's movies
 $stmt = $db->prepare("SELECT COUNT(*) as total FROM UserMovies WHERE user_id = :user_id AND is_active = 1");
 $stmt->execute([":user_id" => $user_id]);
 $totalCount = $stmt->fetch()["total"];

 // grab filter vals
 $title = se($_GET, "title", "", false);
 $title_type = se($_GET, "title_type", "", false);
 $sort = se($_GET, "sort", "title", false);
 $order = se($_GET, "order", "ASC", false);
 $limit = (int)se($_GET, "limit", "10", false);

 // validate sort
 if ($sort !== "title" && $sort !== "release_year" && $sort !== "created") {
    $sort = "title";
 }
 if ($order !== "ASC" && $order !== "DESC") {
    $order = "ASC";
 }
 if ($limit < 1 || $limit > 100) {
    $limit = 10;
 }

 // query (join Movies with UserMovies)
 $query = "SELECT Movies.id, Movies.title, Movies.release_year, Movies.title_type, Movies.is_api FROM Movies JOIN UserMovies ON Movies.id = UserMovies.movie_id WHERE UserMovies.user_id = :user_id AND UserMovies.is_active = 1";
 $params = [":user_id" => $user_id];

 if (!empty($title)) {
    $query .= " AND Movies.title LIKE :title";
    $params[":title"] = "%$title%";
 }
 if (!empty($title_type)) {
    $query .= " AND Movies.title_type = :title_type";
    $params[":title_type"] = $title_type;
 }

 $query .= " ORDER BY Movies.$sort $order";
 $query .= " LIMIT :limit";
 $params[":limit"] = $limit;

 $stmt = $db->prepare($query);
 foreach ($params as $key => $value) {
    if ($key === ":limit") {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
 }
 $stmt->execute();
 $results = $stmt->fetchAll();
 $shownCount = count($results);
 ?>

 <!-- kc784 | 5/2 result stats --> 
 <p>Total movies in your list: <?php echo $totalCount; ?> | Showing: <?php echo $shownCount; ?></p> 

 <!-- remove all buttons --> 
 <form method="POST" style="display:inline;">
    <input type="hidden" name="remove_all" value="1" />
    <input type="submit" value="Remove All" onclick="return confirm( 'Remove all movies from your list?')" />
 </form>

 <?php if (empty($results)) : ?>
    <p>No results available</p>
<?php else : ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Year</th>
                <th>Type</th>
                <th>Source</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row) :?>
                <tr>
                    <td><?php se($row, 'title'); ?></td>
                    <td><?php se($row, 'release_year'); ?></td>
                    <td><?php se($row, 'title_type'); ?></td>
                    <td><?php echo $row['is_api'] ? 'API' : 'Manual'; ?></td>
                    <td>
                        <a href="view_movie.php?id=<?php se($row, 'id'); ?>">View</a> |
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="remove_movie_id" value="<?php se($row, 'id'); ?>" />
                            <input type="submit" value="Remove" onclick="return confirm('Remove this movie?')" />
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require(__DIR__ ."/../../partials/flash.php"); ?>
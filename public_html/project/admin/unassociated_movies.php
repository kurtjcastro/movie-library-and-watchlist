<?php
// kc784 | 5/2
// admin page showing movies not associated with any user
// filter/sort/limit
// links to each movie
ob_start();
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);

// admins only
if (!has_role("Admin")) {
    flash("You do not have permission to view this page.", "danger");
    header("Location: ../list_movies.php");
    die();
}

$db = getDB();
?>

<h3>Unassociated Movies</h3>

<!-- kc784 | 5/2 filter form --> 
<form method="GET">
    <div>
        <label for="title">Search Title</label>
        <input type="text" id="title" name="title" value="<?php se($_GET, 'title'); ?>" />
    </div>
    <div>
        <label for="sort">Sort By</label>
        <select id="sort" name="sort"> 
            <option value="title" <?php if (se($_GET, 'sort', '', false) === 'title') echo 'selected'; ?>>Title</option>
            <option value="release_year" <?php if (se($_GET, 'sort', '', false) === 'release_year') echo 'selected'; ?>>Release Year</option>
        </select>
    </div>
    <div>
        <label for="order">Order</label>
        <select id="order" name="order">
            <option value="ASC" <?php if (se($_GET, 'order', '', false) === "ASC") echo 'selected'; ?>>Ascending</option>
            <option value="DESC" <?php if (se($_GET, 'order', '', false) === "DESC") echo 'selected'; ?>>Descending</option>
        </select>
    </div>
    <div>
        <label for="limit">Results Per Page</label>
        <input type="number" id="limit" name="limit" min="1" max="100" value="<?php echo se($_GET, 'limit', '10', false); ?>" />
    </div>
    <input type="submit" value="Filter" />
</form>

<?php
// kc784 | 5/2
// grab filter vals
$title = se($_GET, "title", "", false);
$sort = se($_GET, "sort", "title", false);
$order = se($_GET, "order", "ASC", false);
$limit = (int)se($_GET, "limit", "10", false);

// validate sort
if ($sort !== "title" && $sort !== "release_year") {
    $sort = "title";
}
if ($order !== "ASC" && $order !== "DESC") {
    $order = "ASC";
}
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

// count of unassociated movies
$countQuery = "SELECT COUNT(*) as total FROM Movies
               LEFT JOIN UserMovies ON Movies.id = UserMovies.movie_id AND UserMovies.is_active = 1
               WHERE UserMovies.id IS NULL";
$countParams = [];
if (!empty($title)) {
    $countQuery .= " AND Movies.title LIKE :title";
    $countParams[":title"] = "%$title%";
}
$stmt = $db->prepare($countQuery);
$stmt->execute($countParams);
$totalCount = $stmt->fetch()["total"];

// main query for left join to find movies with no associations
$query = "SELECT Movies.id, Movies.title, Movies.release_year, Movies.title_type, Movies.is_api
          FROM Movies
          LEFT JOIN UserMovies ON Movies.id = UserMovies.movie_id AND UserMovies.is_active = 1
          WHERE UserMovies.id IS NULL";
$params = [];

if (!empty($title)) {
    $query .= " AND Movies.title LIKE :title";
    $params[":title"] = "%$title%";
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
<p>Total Unassociated Movies: <?php echo $totalCount; ?> | Showing: <?php echo $shownCount; ?></p>

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
            <?php foreach ($results as $row) : ?>
                <tr>
                    <td><?php se($row, 'title'); ?></td>
                    <td><?php se ($row, 'release_year'); ?></td>
                    <td><?php se ($row, 'title_type'); ?></td>
                    <td><?php echo $row['is_api'] ? 'API' : 'MANUAL'; ?></td>
                    <td>
                        <a href="../view_movie.php?id=<?php se($row, 'id'); ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
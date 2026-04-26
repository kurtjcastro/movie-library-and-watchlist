<?php
// kc784 | 4/17
// list page for displaying all movies (api + manual)
// fetch movies from db with optional filters
// sorting by title, release year, or creation date
// limit results between 1-100 (default 10)
// each links to view, edit, delete pages

require(__DIR__ . "/../../partials/nav.php"); 
is_logged_in(true); 
?>

<h3>Movies</h3>

<!-- kc784 | 4/17 - filters and sorts form --> 
 <form method="GET">
    <div>
        <label for="title">Search Title</label>
        <input type="text" id="title" name="title" value="<?php se($_GET, 'title'); ?>" />
    </div>
    <div>
        <label for="title_type">Type</label>
        <select id="title_type" name="title_type">
            <option value="">All</option>
            <option value="movie" <?php if (se($_GET, 'title_type','', false) === 'movie') echo 'selected'; ?>>Movie</option>
            <option value="tvSeries" <?php if (se($_GET, 'title_type', '', false) === 'tvSeries') echo 'selected'; ?>>TV Series</option>
            <option value="short" <?php if (se($_GET, 'title_type', '', false) === 'short') echo 'selected'; ?>>Short</option>
            <option value="tvMovie" <?php if (se($_GET, 'title_type', '', false) === 'tvMovie') echo 'selected'; ?>>TV Movie</option>
            <option value="tvSpecial" <?php if (se($_GET, 'title_type', '' , false) === 'tvSpecial') echo 'selected'; ?>>TV Special</option>
        </select>
    </div>
    <div>
        <label for="sort">Sort By</label>
        <select id="sort" name="sort">
            <option value="title" <?php if (se($_GET,'sort', '', false) === 'title') echo 'selected'; ?>>Title</option>
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
        <label for="limit">Results Per Page</label>
        <input type="number" id="limit" name="limit" min="1" max="100" value="<?php echo se($_GET, 'limit', '10', false); ?>" />
    </div>
    <input type="submit" value="Filter" />
 </form>

 <?php
 // kc784 | 4/19 
 // grab filter values + query db

 $title = se($_GET, "title", "", false);
 $title_type = se($_GET, "title_type", "", false);
 $sort = se($_GET, "sort", "title", false);
 $order = se($_GET, "order", "ASC", false);
 $limit = (int)se($_GET, "limit", "10", false);

 // ensures sort is valid column
 if ($sort !== "title" && $sort !== "release_year" && $sort !== "created") {
    $sort = "title";
 }
 // ensures order is valid
if ($order !== "ASC" && $order !== "DESC") {
    $order = "ASC";
}
// kc784 | 4/19
// ensure limit is in range
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$db = getDB();
$params = [];

// build query based on filters
$query = "SELECT id, title, release_year, title_type, is_api FROM Movies";
$filters = [];

if (!empty($title)) {
    $filters[] = "title LIKE :title";
    $params[":title"] = "%$title%";
}
if (!empty($title_type)) {
    $filters[] = "title_type = :title_type";
    $params[":title_type"] = $title_type;
}
// WHERE if filters are presents
if (count($filters) > 0) {
    $query .= " WHERE " . implode(" AND ", $filters);
}

$query .= " ORDER BY $sort $order";
$query .= " LIMIT :limit";
$params[":limit"] = $limit;

$stmt = $db->prepare($query);

// bind parameters
foreach ($params as $key => $value) {
    if ($key === ":limit") {
        $stmt->bindValue($key, $value, PDO::PARAM_INT); // int limit
    } else {
        $stmt->bindValue($key, $value);
    }
}

// kc784 | 4/19
$stmt->execute();
$results = $stmt->fetchAll();
?> 

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
                    <td><?php se($row, 'release_year'); ?></td>
                    <td><?php se($row, 'title_type'); ?></td>
                    <td><?php echo $row['is_api'] ? 'API' : 'Manual'; ?></td>
                    <td>
                        <a href="view_movie.php?id=<?php se($row, 'id'); ?>">View</a> |
                        <a href="edit_movie.php?id=<?php se($row, 'id'); ?>">Edit</a> |
                        <a href="delete_movie.php?id=<?php se($row, 'id'); ?>&<?php echo http_build_query($_GET); ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>
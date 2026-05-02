<?php
// kc784 | 5/2
// admin page for user-movie associations
// filter by username, sort, limit
// shows movies associated with each user
// links to view movie, remove association, user profile
ob_start();
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);

// admins only
if (!has_role("Admin")) {
    flash("You do not have the permission to view this page.", "danger");
    header("Location: ../list_movies.php");
    die();
}

$db = getDB();
?>

<h3>All User Movie Associations</h3>

<!-- kc784 | 5/2 --> 
 <form method="GET">
    <div>
        <label for="username">Filter by Username</label>
        <input type="text" id="username" name="username" value="<?php se($_GET, 'username'); ?>" />
    </div>
    <div>
        <label for="sort">Sort By</label>
        <select id="sort" name="sort">
            <option value="username" <?php if (se($_GET, 'sort', '', false) === 'username') echo 'selected'; ?>>Username</option>
            <option value="title" <?php if (se($_GET, 'sort', '', false) === 'title') echo 'selected'; ?>>Title</option>
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
 // kc784 | 5/2
 // handles remove association
 if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_assoc_id"])) {
    $assoc_id = (int)$_POST["remove_assoc_id"];
    $stmt = $db->prepare("UPDATE UserMovies SET is_active = 0 WHERE id = :id");
    $stmt->execute([":id" => $assoc_id]);
    flash("Association removed", "success");
 }

// handles remove all for filtered user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_all_user"])) {
    $remove_username = $_POST["remove_all_user"];
    // gets user id from username
    $stmt = $db->prepare("SELECT id FROM Users WHERE username = :username");
    $stmt->execute([":username" => $remove_username]);
    $targetUser = $stmt->fetch();
    if ($targetUser) {
        $stmt = $db->prepare("UPDATE UserMovies SET is_active = 0 WHERE user_id = :user_id AND is_active = 1");
        $stmt->execute([":user_id" => $targetUser["id"]]);
        flash("All associations removed for $remove_username", "success");
    }
}

// grab filter vals
$username = se($_GET, "username", "", false);
$sort = se($_GET, "sort", "username", false);
$order = se($_GET, "order", "ASC", false);
$limit = (int)se($_GET, "limit", "10", false);

// validate sort 
if ($sort !== "username" && $sort !== "title") {
    $sort = "username";
}
if ($order !== "ASC" && $order !== "DESC") {
    $order = "ASC";
}
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

// get total count of associations
$countQuery = "SELECT COUNT(*) as total FROM UserMovies JOIN Users ON UserMovies.user_id = Users.id WHERE UserMovies.is_active = 1";
$countParams = [];
if (!empty($username)) {
    $countQuery .= " AND Users.username LIKE :username";
    $countParams[":username"] = "%$username%";
}
$stmt = $db->prepare($countQuery);
$stmt->execute($countParams);
$totalCount = $stmt->fetch()["total"];

// main query for joining UserMovies, Users, Movies
$query = "SELECT UserMovies.id as assoc_id, Users.username, Users.id as uid,
          Movies.id as movie_id, Movies.title, Movies.release_year, Movies.title_type,
          (SELECT COUNT(*) FROM UserMovies um2 WHERE um2.movie_id = Movies.id AND um2.is_active = 1) as user_count
          FROM UserMovies
          JOIN Users ON UserMovies.user_id = Users.id
          JOIN Movies ON UserMovies.movie_id = Movies.id
          WHERE UserMovies.is_active = 1";
$params = [];

// filter by username
if (!empty($username)) {
    $query .= " AND Users.username LIKE :username";
    $params[":username"] = "%$username%";
}

// sort
if ($sort === "username") {
    $query .= " ORDER BY Users.username $order";
} else {
    $query .= " ORDER BY Movies.title $order";
}

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
<p>Total Associations: <?php echo $totalCount; ?> | Showing: <?php echo $shownCount; ?></p>

<?php if (!empty($username)) : ?>
    <!-- remove all for filtered --> 
     <form method="POST" style="display:inline;"> 
        <input type="hidden" name="remove_all_user" value="<?php echo htmlspecialchars($username); ?>" />
        <input type="submit" value="Remove All for '<?php echo htmlspecialchars($username); ?>'" onclick="return confirm('Remove all associations for this user?')" />
    </form>
<?php endif; ?>

<?php if (empty($results)) : ?>
    <p>No results available</p>
<?php else : ?>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Movie</th>
                <th>Year</th>
                <th>Type</th>
                <th>Total Users</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row) : ?>
                <tr>
                    <td><a href="../profile.php?id=<?php echo $row['uid']; ?>"><?php echo htmlspecialchars($row['username']); ?></a></td>
                    <td><?php se($row, 'title'); ?></td>
                    <td><?php se($row, 'release_year'); ?></td>
                    <td><?php se($row, 'title_type'); ?></td>
                    <td><?php echo $row['user_count']; ?></td>
                    <td>
                        <a href="../view_movie.php?id=<?php se($row, 'movie_id'); ?>">View</a> |
                        <form method="POST" style="display:inline;"> 
                            <input type="hidden" name="remove_assoc_id" value="<?php echo $row['assoc_id']; ?>" />
                            <input type="submit" value="Remove" onclick="return confirm('Remove this association?')" />
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
<?php
// kc784 | 5/2
// admin page to associate movies with users
// search for movies + users by partial match
// checkboxes to toggle associations
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

// kc784 | 5/2
// handle association toggle
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset ($_POST["apply"])) {
    $movie_ids = isset($_POST["movie_ids"]) ? $_POST["movie_ids"] : [];
    $user_ids = isset($_POST["user_ids"]) ? $_POST["user_ids"] : [];

    if(empty($movie_ids) || empty($user_ids)) {
        flash("Please select at least one movie and one user", "warning");
    } else {
        $count = 0;
        foreach ($movie_ids as $movie_id) {
            foreach ($user_ids as $uid) {
                $movie_id = (int)$movie_id;
                $uid = (int)$uid;

                // check if association exists
                $stmt = $db->prepare("SELECT id, is_active FROM UserMovies WHERE user_id = :user_id AND movie_id = :movie_id");
                $stmt->execute([":user_id" => $uid, ":movie_id" => $movie_id]);
                $existing = $stmt->fetch();

                if ($existing) {
                    // toggle is_active
                    $newStatus = $existing["is_active"] == 1 ? 0 : 1;
                    $stmt = $db->prepare("UPDATE UserMovies SET is_active = :status WHERE id = :id");
                    $stmt->execute([":status" => $newStatus, ":id" => $existing["id"]]); 
                } else {
                    // new association
                    $stmt = $db->prepare("INSERT INTO UserMovies (user_id, movie_id) VALUES (:user_id, :movie_id)");
                    try {
                        $stmt->execute([":user_id" => $uid, ":movie_id" => $movie_id]);
                    } catch (Exception $e) {
                        error_log("Assign movie error: " . var_export($e, true));
                    }
                }
                $count++;
            }
        }
        flash("$count associations toggled", "success");
    }
}

// get search vals
$movie_search = se($_POST, "movie_search", "", false);
$user_search = se($_POST, "user_search", "", false);
?>

<h3>Assign Movies to Users</h3>

<!-- kc784 | 5/2 search form --> 
<form method="POST">
    <div>
        <label for="movie_search">Search Movies</label>
        <input type="text" id="movie_search" name="movie_search" value="<?php echo htmlspecialchars($movie_search); ?>" />
    </div>
    <div>
        <label for="user_search">Search Users</label>
        <input type="text" id="user_search" name="user_search" value="<?php echo htmlspecialchars($user_search); ?>" />
    </div>
    <input type="submit" name="search" value="Search" />
</form>

<?php if (!empty($movie_search) || !empty($user_search)) : ?>
    <?php
    // fetch matching movies
    $movies = [];
    if (!empty($movie_search)) {
        $stmt = $db->prepare("SELECT id, title, release_year FROM Movies WHERE title LIKE :search LIMIT 25");
        $stmt->execute([":search" => "%$movie_search%"]);
        $movies = $stmt->fetchAll();
    }

    // fetch matching users
    $users = [];
    if (!empty($user_search)) {
        $stmt = $db->prepare("SELECT id, username FROM Users WHERE username LIKE :search LIMIT 25");
        $stmt->execute([":search" => "%$user_search%"]);
        $users = $stmt->fetchAll();
    }

    // existing associations for check state
    $existingAssoc = [];
    if (!empty($movies) && !empty($users)) {
        $stmt = $db->prepare("SELECT user_id, movie_id FROM UserMovies WHERE is_active = 1");
        $stmt->execute();
        $allAssoc = $stmt->fetchAll();
        forEach ($allAssoc as $a) {
            $existingAssoc[$a["user_id"] . "_" . $a["movie_id"]] = true;
        }
    }
    ?>

    <form method="POST">
        <!-- pass search vals for persistence --> 
         <input type="hidden" name="movie_search" value="<?php echo htmlspecialchars($movie_search); ?>" />
         <input type="hidden" name="user_search" value="<?php echo htmlspecialchars($user_search); ?>" />

         <div style="display: flex; gap: 40px;">
            <!-- movies column --> 
             <div>
                <h4>Movies</h4>
                <?php if (empty($movies)) : ?>
                    <p>No movies found</p>
                <?php else : ?>
                    <?php foreach ($movies as $m) : ?>
                        <div>
                            <label>
                                <input type="checkbox" name="movie_ids[]" value="<?php echo $m['id']; ?>" />
                                <?php se($m, 'title'); ?> (<?php se($m, 'release_year'); ?>)
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
             </div>

         <!-- users column --> 
         <div>
             <h4>Users</h4>
             <?php if (empty($users)) : ?>
                 <p>No users found</p>
             <?php else : ?>
                 <?php foreach ($users as $u) : ?>
                     <div>
                         <label>
                             <input type="checkbox" name="user_ids[]" value="<?php echo $u['id']; ?>" />
                             <?php se($u, 'username'); ?>
                         </label>
                     </div>
                 <?php endforeach; ?>
             <?php endif; ?>
         </div>
     </div>

     <input type="submit" name="apply" value="Apply Associations" />
    </form>

    <?php if (!empty($movies) && !empty($users)) : ?>
        <h4>Current Associations</h4>
        <table>
            <thead>
                <tr>
                    <th>Movie</th>
                    <?php foreach ($users as $u) : ?>
                        <th><?php se($u, 'username'); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movies as $m) : ?>
                    <tr>
                        <td><?php se($m, 'title'); ?></td>
                        <?php foreach ($users as $u) : ?>
                            <td>
                                <?php
                                $key = $u['id'] . "_" . $m['id'];
                                echo isset($existingAssoc[$key]) ? "&#10003;" : "";
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php endif; ?>

<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
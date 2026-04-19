<?php
// kc784 | 4/19
// API fetch page to grab movies + save to db
// submit form to trigger
// map API response to table
// handle duplicates by checking api_id
// only accessible when logged in

require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);
?>

<h3>Fetch Movies from API</h3>

<!-- kc784 | 4/19 | trigger API fetch --> 
 <form method="POST">
    <div>
        <label for="list">List</label>
        <select id="list" name="list">
            <option value="top_rated_english_250">Top Rated English 250</option>
            <option value="top_rated_250">Top Rated 250</option>
            <option value="most_pop_movies">Most Popular Movies</option>
            <option value="top_boxoffice_200">Top Box Office 200</option>
        </select>
    </div>
    <div>
        <label for="limit">How Many</label>
        <!-- limit amount to fetch to save quota --> 
         <input type="number" id="limit" name="limit" min="1" max="50" value="5" />
    </div>
    <input type="submit" value="Fetch Movies" />
</form>

<?php
// kc784 | 4/19 
// server side fetch from API
// map result to movies table columns
// skip duplicates by checking api_id

if ($_SERVER["REQUEST_METHOD"] === "POST") { // runs only on form submit
    $list = se($_POST, "list", "top_rated_english_250", false); // determines which list to fetch
    $limit = (int)se($_POST, "limit", "5", false); // amount to fetch

    // ensure limit is within range + feasible
    if ($limit < 1 || $limit > 50) {
        $limit = 5;
    }

    $url = "https://moviesdatabase.p.rapidapi.com/titles";
    $data = ["list" => $list, "limit" => $limit]; // API parameters

    try {
        $result = get($url, "MOVIES_API_KEY", $data, true, "moviesdatabase.p.rapidapi.com");
        $response = json_decode($result[$response], true); // json to array

        if (!isset($response["results"]) || empty($response["results"])) {
            flash("No results from API", "warning");
        } else {
            $db = getDB();
            $inserted = 0; // counts added movies

            foreach ($response["results"] as $movie) {
                // grab the fields from API response
                $api_id = isset($movie["id"]) ? $movie["id"] : null;
                $title = isset($movie["titleText"]["text"]) ? $movie["titleText"]["text"] : null;
                $release_year = isset($movie["releaseYear"]["year"]) ? $movie["releaseYear"]["year"] : null;
                $image_url = isset($movie["primaryImage"]["url"]) ? $movie["primaryImage"]["url"] : null;
                $title_type = isset($movie["titleType"]["id"]) ? $movie["titleType"]["id"] : null;

                // skips if there is no title or api_id
                if (empty($title) || empty($api_id)) {
                    continue;
                }

                // check for duplicate with api_id
                $stmt = $db->prepare("SELECT id FROM Movies WHERE api_id = :api_id");
                $stmt->execute([":api_id" => $api_id]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    continue; // skip if already in db
                }

                // inserts new movie
                $stmt = $db->prepare(
                    "INSERT INTO Movies (api_id, title, release_year, image_url, title_type, is_api)
                    VALUS (:api_id, :title, :release_year, :image_url, :title_type, 1)"
                );
                try {
                    $stmt->execute([
                        ":api_id" => $api_id,
                        ":title" => $title,
                        ":release_year" => $release_year,
                        ":image_url" => $image_url,
                        ":title_type" => $title_type,
                    ]);
                    $inserted++;
                } catch (Exception $e) {
                    error_log("Error inserting movie $api_id: " var_export($e, true));
                }
            }
            flash("$inserted movies added", "success");
        }
    } catch (Exception $e) {
        flash("Error fetching from API", "danger");
        error_log("API fetch error: " . var_export($e, true));
    }
}
?>

<?php require(__DIR__ . "/../../partials/flash.php");
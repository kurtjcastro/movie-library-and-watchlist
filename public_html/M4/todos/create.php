<?php
require_once(__DIR__ . "/../../../lib/db.php"); ?>

<?php
// don't edit - this
$expected_fields = ["task", "due", "assigned"];
$diff = array_diff($expected_fields, array_keys($_GET));

if (empty($diff)) {

    // data variables, don't edit
    $task = $_GET["task"];
    $due = $_GET["due"]; //hint: must be a valid MySQL date format
    $assigned = $_GET["assigned"]; // Must be "self" or a valid format (not empty or equivalent)

    $is_valid = true;
    // TODO Validate the incoming data for correct format based on the SQL table definition.
    // When not valid, provide a user-friendly message of what specifically was wrong and set $is_valid to false.
    // Assigned should check for "self" if a valid format/value isn't provided.
    // Start validations
    // can edit here

    // kc784 | 3/24
    // plan
    // 1. form req task, due, and assigned
    // 2. validate each field so nothing is null with real dates and correct formatting
    // 3. create insert query to pass data
    // 4. handle unique error separately instead of generic error message

    if (empty(trim($task))) { // check if task is empty after trimming ws
        echo "<p>Task cannot be empty.</p>";
        $is_valid = false; // flag if invalid
    } elseif (strlen($task) > 128) {
        echo "<p>Task must be 128 chars or fewer.</p>"; // checks varchar limit
        $is_valid = false;
    }

    if (empty($due) || !strtotime($due) || date('Y-m-d', strtotime($due)) !== $due) { // checks if due is empty and parses as date in year,month,date format
        echo "<p>Due date must be a valid date (YYYY-MM-DD).</p>";
        $is_valid = false;
    }

    if (empty(trim($assigned))) { // default is set to self if assigned is blank
        $assigned = "self";
    }

    // End validations

    
    if ($is_valid) {
        /*
        Design a query to insert the incoming data to the proper columns.
        Ensure valid and proper PDO named placeholders are used.
        https://phpdelusions.net/pdo
        */
        $query = "INSERT INTO M4_Todos (task, due, assigned) VALUES (:task, :due, :assigned)"; // edit this
        $params = [":task" => $task, ":due" => $due, ":assigned" => $assigned]; // Apply the proper PDO placeholder to variable mapping here
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);
            if ($r) {
                echo "Inserted new Todo with id " . $db->lastInsertId();
            } else {
                echo "Failed to insert";
            }
        } catch (PDOException $e) {
            // kc784 | 3/24
            // extra credit
            // check if the exception was related to a unique constraint
            // provide an appropriate user-friendly message for this scenario
            // Otherwise show the default message below
            if ($e->getCode() == 23000) { // unique constraint
                echo "A todo with that task + due date already exists."; // notifies user of already existing task + date 
            } else {
                echo "There was an error inserting the record; check the logs (terminal)"; // generic message
            }
            error_log("Insert Error: " . var_export($e, true)); // shows in the terminal
        }
    } else {
        error_log("Creation input wasn't valid");
    }
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Create ToDo </h2>
        <form>
            <!-- design the form with proper labels and input fields with the correct types based on the SQL table.
             Wrap each label/input pair in a div tag.
             For "Assigned" ensure the default value is "self". -->

            <!-- kc784 | 3/24 -->
            <div>
                <label for="task">Task</label>
                <input type="text" id="task" name="task" /> <!-- maxlength limit varchar to 128 limit -->
            </div>
            <div>
                <label for="due">Due Date</label>
                <input type="date" id="due" name="due" /> <!-- outputs proper date format year, month, day -->
            </div>
            <div>
                <label for="assigned">Assigned</label>
                <input type="text" id="assigned" name="assigned" value="self" /> <!-- fills field with default self -->
            </div>
            <div>
                <input type="submit" />
            </div>
        </form>
    </section>
</body>
</body>

</html>
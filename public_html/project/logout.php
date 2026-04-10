<?php
// kc784 | 4/9
// handles logout + message
// require functions.php to pull in flash()
require(__DIR__ . "/../../lib/functions.php");
reset_session(); // clear session data and start a new session
flash("You have been logged out","success");
header("Location: " . get_url("login.php")); // redirect back to login
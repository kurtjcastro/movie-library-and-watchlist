<?php
// kc784 | 4/19
// delete page to remove movie from db
// fetch movie by id, check perms, delete, redirect

require(__DIR__ . "/../../partials/nav.php"); 
is_logged_in(true);

$id = se($_GET, "id", -1, false); // grabs id from url
<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

if (isset($_GET["pullGenre"])) {
    $temp = fetch_genres();
    $temp = map_platform_data($temp);
    defaultInsert($temp, "Genres", ["update_duplicate" => true, "api" => true]);
}


//build search form
$form = [
    ["type" => "number", "name" => "id", "placeholder" => "Genre ID", "label" => "Genre ID", "include_margin" => false],
    ["type" => "text", "name" => "name", "placeholder" => "Genre Title", "label" => "Genre Name", "include_margin" => false],

    ["type" => "date", "name" => "date_min", "placeholder" => "Min Date", "label" => "Min Date", "include_margin" => false],
    ["type" => "date", "name" => "date_max", "placeholder" => "Max Date", "label" => "Max Date", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["name" => "Name", "modified" => "Last Modified Date", "is_api" => "If API", "is_active" => "Active"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],
    ["type" => "select", "name" => "viewAll", "label" => "See Disabled", "options" => ["false" => "No", "true" => "Yes"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "include_margin" => false],
];
error_log("Form data: " . var_export($form, true));



$query = "SELECT id, name as `Name`,  IF(is_api=1, 'Yes', 'No') as `Is API`, IF(is_active=1, 'Active', 'Disabled')  as `Active`, created, modified   FROM `Genres` WHERE 1=1";
$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    die(header("Location: " . $session_key));
} else {
    $session_data = session_load($session_key);
}

if (count($_GET) == 0 && isset($session_data) && count($session_data) > 0) {
    if ($session_data) {
        $_GET = $session_data;
    }
}

// Lets you show whats on 
$viewAll = se($_GET, "viewAll", "false", false);
if ($viewAll == "false") {
    $query .= " AND is_active=1";
}

if (count($_GET) > 0) {
    session_save($session_key, $_GET);
    $keys = array_keys($_GET);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }

    //id
    $GenreId = se($_GET, "id", "", false);
    if (!empty($tempId)) {
        $query .= " AND id = :tempId";
        $params[":tempId"] = (int)"$tempId";
    }
    //name
    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $query .= " AND name like :name";
        $params[":name"] = "%$name%";
    }

    //date range
    $date_min = se($_GET, "date_min", "", false);
    if (!empty($date_min) && $date_min != "") {
        $query .= " AND modified >= :date_min";
        $params[":date_min"] = $date_min;
    }
    $date_max = se($_GET, "date_max", "-1", false);
    if (!empty($date_max) && $date_max > -1) {
        $query .= " AND modified <= :date_max";
        $params[":date_max"] = $date_max;
    }

    //sort and order
    $sort = se($_GET, "sort", "name", false);
    if (!in_array($sort, ["name", "modified", "is_active"])) {
        $sort = "firstReleaseDate";
    }
    $order = se($_GET, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "desc";
    }

    //IMPORTANT make sure you fully validate/trust $sort and $order (sql injection possibility)
    $query .= " ORDER BY $sort $order";
    //limit
    try {
        $limit = (int)se($_GET, "limit", "10", false);
    } catch (Exception $e) {
        $limit = 10;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
        flash("Limit can be 1-100, set to default 10", "warning");
    }
    //IMPORTANT make sure you fully validate/trust $limit (sql injection possibility)
    $query .= " LIMIT $limit";
}

$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching Genres " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = [
    "data" => $results, "title" => "Current Genres", // "ignored_columns" => ["id"],
    "edit_url" => get_url("admin/edit_genre.php"),
    "delete_url" => get_url("admin/delete_genre.php"),
    "delete_label" => "Toggle Active"
];
?>
<div class="container-fluid">
    <h3>List Genres</h3>
    <form method="GET">
        <div class="row mb-3" style="align-items: flex-end;">

            <?php foreach ($form as $k => $v) : ?>
                <div class="col">
                    <?php render_input($v); ?>
                </div>
            <?php endforeach; ?>

        </div>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Filter"]); ?>
        <a href="?clear" class="btn btn-secondary">Clear</a>

    </form>
    <a href="?pullPlatform" class="btn custBtn">Pull Genres</a>
    <?php render_table($table); ?>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>
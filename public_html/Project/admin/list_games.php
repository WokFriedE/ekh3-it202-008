<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

// Pull popular games
if (isset($_GET['popular'])) {
    $popRes = fetch_popular();
    // $popRes = fetch_json("popularRes");
    $popRes = map_popular_data($popRes, 1);

    try {
        $opts = ["debug" => true, "update_duplicate" => true,  "columns_to_update" => []];
        $popRes = insert("Games", $popRes, $opts);

        if (!$popRes) {
            flash("Unhandled Error", "warning");
        } else {
            flash("Created record with id " . var_export($popRes, true), "success");
        }
    } catch (InvalidArgumentException $e1) {
        error_log("Invalid arg" . var_export($e1, true));
        flash("Invalid data passed", "danger");
    } catch (PDOException $e2) {
        if ($e2->errorInfo[1] == 1062) {
            flash("An entry for this game already exists for today", "warning");
        } else {
            error_log("Database error" . var_export($e2, true));
            flash("Database error", "danger");
        }
    } catch (Exception $e3) {
        error_log("Invalid data records" . var_export($e3, true));
        flash("Invalid data records", "danger");
    }
}

if (isset($_GET["pullGenre"])) {
    $temp = fetch_genres();
    $temp = map_genre_data($temp);
    defaultInsert($temp, "Genres");
}

if (isset($_GET["pullPlatform"])) {
    $temp = fetch_platforms();
    $temp = map_platform_data($temp);
    defaultInsert($temp, "Platforms");
}

//build search form
$form = [
    ["type" => "number", "name" => "id", "placeholder" => "Game ID", "label" => "Game ID", "include_margin" => false],
    ["type" => "text", "name" => "name", "placeholder" => "Game Title", "label" => "Game Name", "include_margin" => false],

    ["type" => "text", "name" => "publisher", "placeholder" => "Publisher", "label" => "Publisher", "include_margin" => false],
    ["type" => "text", "name" => "developer", "placeholder" => "Developer", "label" => "Developer", "include_margin" => false],

    ["type" => "number", "name" => "score_min", "placeholder" => "Score Min", "label" => "Score Min", "include_margin" => false],
    ["type" => "number", "name" => "score_max", "placeholder" => "Score Max", "label" => "Score Max", "include_margin" => false],

    ["type" => "date", "name" => "date_min", "placeholder" => "Min Date", "label" => "Min Date", "include_margin" => false],
    ["type" => "date", "name" => "date_max", "placeholder" => "Max Date", "label" => "Max Date", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["name" => "Name", "topCriticScore" => "Score", "firstReleaseDate" => "Date", "is_api" => "If API"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],
    ["type" => "select", "name" => "viewAll", "label" => "See Disabled", "options" => ["false" => "No", "true" => "Yes"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "include_margin" => false],
];
error_log("Form data: " . var_export($form, true));



$query = "SELECT id, name, publisher, developer, topCriticScore, firstReleaseDate, is_api, created, modified, is_active as `Active`  FROM `Games` WHERE 1=1";
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
    $gameId = se($_GET, "id", "", false);
    if (!empty($gameId)) {
        $query .= " AND id = :gameId";
        $params[":gameId"] = (int)"$gameId";
    }
    //name
    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $query .= " AND name like :name";
        $params[":name"] = "%$name%";
    }
    //publisher
    $publisher = se($_GET, "publisher", "", false);
    if (!empty($publisher)) {
        $query .= " AND publisher like :publisher";
        $params[":publisher"] = "%$publisher%";
    }
    //developer
    $developer = se($_GET, "developer", "", false);
    if (!empty($developer)) {
        $query .= " AND developer like :developer";
        $params[":developer"] = "%$developer%";
    }
    //score range
    $score_min = se($_GET, "score_min", "-1", false);
    if (!empty($score_min) && $score_min > -1) {
        $query .= " AND topCriticScore >= :score_min";
        $params[":score_min"] = $score_min;
    }
    $score_max = se($_GET, "score_max", "-1", false);
    if (!empty($score_max) && $score_max > -1) {
        $query .= " AND topCriticScore <= :score_max";
        $params[":score_max"] = $score_max;
    }

    //date range
    $date_min = se($_GET, "date_min", "", false);
    if (!empty($date_min) && $date_min != "") {
        $query .= " AND firstReleaseDate >= :date_min";
        $params[":date_min"] = $date_min;
    }
    $date_max = se($_GET, "date_max", "-1", false);
    if (!empty($date_max) && $date_max > -1) {
        $query .= " AND firstReleaseDate <= :date_max";
        $params[":date_max"] = $date_max;
    }

    //sort and order
    $sort = se($_GET, "sort", "date", false);
    if (!in_array($sort, ["topCriticScore", "firstReleaseDate"])) {
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

// echo $query;

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
    error_log("Error fetching games " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = [
    "data" => $results, "title" => "Current Games", // "ignored_columns" => ["id"],
    "view_url" => get_url("admin/view_game.php"),
    "edit_url" => get_url("admin/edit_game.php"),
    "delete_url" => get_url("admin/delete_game.php")
];
?>
<div class="container-fluid">
    <h3>List Games</h3>
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
    <a href="?popular" class="btn custBtn">Pull Popular Games</a>
    <a href="?pullGenre" class="btn custBtn">Pull Genres</a>
    <a href="?pullPlatform" class="btn custBtn">Pull Platforms</a>
    <?php render_table($table); ?>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>
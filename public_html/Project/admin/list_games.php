<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

//build search form
$form = [
    ["type" => "text", "name" => "name", "placeholder" => "Game Title", "label" => "Game Name", "include_margin" => false],

    ["type" => "text", "name" => "publisher", "placeholder" => "Publisher", "label" => "Publisher", "include_margin" => false],
    ["type" => "text", "name" => "developer", "placeholder" => "Developer", "label" => "Developer", "include_margin" => false],

    ["type" => "number", "name" => "score_min", "placeholder" => "Score Min", "label" => "Score Min", "include_margin" => false],
    ["type" => "number", "name" => "score_max", "placeholder" => "Score Max", "label" => "Score Max", "include_margin" => false],

    ["type" => "date", "name" => "date_min", "placeholder" => "Min Date", "label" => "Min Date", "include_margin" => false],
    ["type" => "date", "name" => "date_max", "placeholder" => "Max Date", "label" => "Max Date", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["score" => "Score", "date" => "Date"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "include_margin" => false],
];
error_log("Form data: " . var_export($form, true));



$query = "SELECT id, name, publisher, developer, topCriticScore, firstReleaseDate, is_api, created, modified  FROM `Games` WHERE 1=1";
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
if (count($_GET) > 0) {
    session_save($session_key, $_GET);
    $keys = array_keys($_GET);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }
    //publisher
    $publisher = se($_GET, "publisher", "", false);
    if (!empty($publisher)) {
        $query .= " AND publisher like :publisher";
        $params[":publisher"] = "%$publisher%";
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
        $query .= " AND latest >= :date_min";
        $params[":date_min"] = $date_min;
    }
    $date_max = se($_GET, "date_max", "-1", false);
    if (!empty($date_max) && $date_max > -1) {
        $query .= " AND latest <= :date_max";
        $params[":date_max"] = $date_max;
    }

    //sort and order
    $sort = se($_GET, "sort", "date", false);
    if (!in_array($sort, ["Date", "Score"])) {
        $sort = "date";
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
    }
    //IMPORTANT make sure you fully validate/trust $limit (sql injection possibility)
    $query .= " LIMIT $limit";
}





echo $query;
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
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = [
    "data" => $results, "title" => "Latest Stocks", "ignored_columns" => ["id"],
    "edit_url" => get_url("admin/edit_stock.php"),
    "delete_url" => get_url("admin/delete_stock.php")
];
?>
<div class="container-fluid">
    <h3>List Stocks</h3>
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
    <?php render_table($table); ?>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>
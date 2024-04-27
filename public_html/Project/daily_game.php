<?php
require(__DIR__ . "/../../partials/nav.php");

$is_admin = false;
if (has_role("Admin")) {
    $is_admin = true;
}

// Generate a new daily
if (isset($_GET["generate"])) {
    $db = getDB();
    $query = "SELECT id FROM `Games` WHERE `firstReleaseDate` IS NOT NULL OR `is_api`=1 ORDER BY RAND() LIMIT 5";
    $insertQuery = "INSERT INTO `DailyGame` (gameId, dailyDate) VALUES (:gameId, :dailyDate)";
    $params[":dailyDate"] = date("Y-m-d");
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $r = $stmt->fetchAll();
        if ($r) {
            foreach ($r as $item => $val) {
                $params[":gameId"] = $val["id"];
                try {
                    $stmt = $db->prepare($insertQuery);
                    $stmt->execute($params);
                    flash("Daily Created", "success");
                    break;
                } catch (PDOException $e) {
                    if ($e->errorInfo[1] == 1062) {
                        continue;
                    }
                }
            }
        } else {
            flash("Didn't find any saved games", "danger");
        }
    } catch (PDOException $e) {
        error_log("Something broke with the select query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}


//build search form
$form = [
    ["type" => "text", "name" => "name", "placeholder" => "Game Name", "label" => "Game Name", "include_margin" => false],


    ["type" => "date", "name" => "date_min", "placeholder" => "Min Date", "label" => "Min Date", "include_margin" => false],
    ["type" => "date", "name" => "date_max", "placeholder" => "Max Date", "label" => "Max Date", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["name" => "Name", "rarity" => "Rarity", "life" => "Life", "power" => "Power", "defense" => "Defense", "stonks" => "Stonks (Combat Effectiveness)", "created" => "Created", "modified" => "Modified"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "include_margin" => false],
];
error_log("Form data: " . var_export($form, true));



$query = "SELECT d.id, dailyDate as `date`, g.name, IF(c.`userId`=:currUser, 1,0) AS `Completed`, c.attempts, c.timeTaken, g.sqrImgURL FROM 
        ((`DailyGame` d JOIN `Games` g on d.`gameId`=g.id) LEFT JOIN `Completed_Games` c on c.`DailyGameID`=d.id) WHERE 1=1";
$params = [];
$params[":currUser"] = get_user_id();
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    redirect($session_key);
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
    //name
    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $query .= " AND name like :name";
        $params[":name"] = "%$name%";
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
    $sort = se($_GET, "sort", "name", false);
    if (!in_array($sort, ["name", "date"])) {
        $sort = "name";
    }
    //tell mysql I care about the data from table "b"
    if ($sort === "created" || $sort === "modified") {
        $sort = "g." . $sort;
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
foreach ($results as $index => $Game) {
    foreach ($Game as $key => $value) {
        if (is_null($value) && $key === "sqrImgURL") {
            $results[$index][$key] = missingURL();
        } else if (is_null($value)) {
            $results[$index][$key] = "N/A";
        }
    }
}

$table = [
    "data" => $results, "title" => "Games", "ignored_columns" => ["id"],
    "view_url" => get_url("Game.php"),
];
?>
<div class="container-fluid">
    <h3>Games</h3>
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
    <?php if ($is_admin) : ?>
        <a href="?generate" class="btn custBtn">Pull Popular Games</a>
    <?php endif; ?>
    <div class="row w-100 row-cols-auto row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-4">
        <?php foreach ($results as $Game) : ?>
            <div class="col">

                <?php render_game_card($Game); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>
<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}


$uid = get_user_id();
if (isset($_GET["id"])) {
    $uid = se($_GET, "id", get_user_id(), false);
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
                    unset($_GET["generate"]);
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

if (isset($_GET["reset"])) {
    $query = "UPDATE Completed_Games SET is_active = 0";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Reset fail: " . var_export($e, true));
        flash("An error occurred", "danger");
    }
}
if (isset($_GET["enable"])) {
    $query = "UPDATE Completed_Games SET is_active = 1";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Re-enable fail: " . var_export($e, true));
        flash("An error occurred", "danger");
    }
}

//build search form
$form = [
    ["type" => "text", "name" => "name", "placeholder" => "Game Name", "label" => "Game Name", "include_margin" => false],


    ["type" => "date", "name" => "date_min", "placeholder" => "Min Date", "label" => "Min Date", "include_margin" => false],
    ["type" => "date", "name" => "date_max", "placeholder" => "Max Date", "label" => "Max Date", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["date" => "Date", "attempts" => "Attempts ", "timeTaken" => "Time Taken", "completed" => "Completed", "name" => "Name"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "include_margin" => false],
];
error_log("Form data: " . var_export($form, true));



$query = "SELECT d.id, d.gameId, dailyDate as `date`, g.name, g.`sqrImgURL`, d.is_active
FROM ((`DailyGame` d LEFT JOIN (SELECT * FROM `Completed_Games` WHERE `userId`=:uid and is_active=1) cg ON d.id = cg.DailyGameID) LEFT JOIN `Games` g on d.gameId = g.id) WHERE cg.`userId` is NULL AND 1=1";

$params = [];
$params[":uid"] = $uid;
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
        $query .= " AND dailyDate >= :date_min";
        $params[":date_min"] = $date_min;
    }
    $date_max = se($_GET, "date_max", "-1", false);
    if (!empty($date_max) && $date_max > -1) {
        $query .= " AND dailyDate <= :date_max";
        $params[":date_max"] = $date_max;
    }

    //sort and order
    $sort = se($_GET, "sort", "date", false);
    if (!in_array($sort, ["name", "date", "attempts", "timeTaken", "completed"])) {
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

// Used to get total count of non associated data
$query = "SELECT count(1) as `totalCount` FROM `DailyGame` WHERE id not in (SELECT DISTINCT DailyGameID FROM Completed_Games WHERE is_active = 1)";
try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $r = $stmt->fetch();
    if ($r) {
        $tableTotal = (int)$r["totalCount"];
    }
} catch (PDOException $e) {
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
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
    <a href="?generate" class="btn custBtn">Generate New Challenge</a>
    <a href="?reset" class="btn custBtn">Reset All Associations</a>
    <a href="?enable" class="btn custBtn">Reenable All Associations</a>
    <?php
    // Sets up the counter
    echo "<h5>" . count($results) . "/" . $tableTotal . "</h5>";
    if (count($results) == 0) {
        echo "<h4>No Results Available</h4>";
    }
    ?>
    <div class="row w-100 row-cols-auto row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-4">

        <?php foreach ($results as $Game) : ?>
            <div class="col">
                <?php
                $Game["adminView"] = true;
                render_game_card($Game);
                ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>
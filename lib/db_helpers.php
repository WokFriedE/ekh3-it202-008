<?php

/**
 * Inserts a single record or multiple records into a specified database table.
 * Throws exceptions for invalid input or database errors.
 * Important: Intentionally doesn't support JSON fields, only basic data types.
 *
 * @param string $table_name The sanitized name of the database table.
 * @param array $data An associative array for a single record or an array of associative arrays for multiple records.
 * @param array $opts Options including 'debug' flag, 'update_duplicate', and 'columns_to_update'.
 * @return array The last insert ID and number of rows affected for insert.
 * @throws InvalidArgumentException If input data is not valid.
 * @throws Exception For database-related errors.
 * 
 * @author Matt Toegel
 * @version 0.2 04/17/2024
 */
// wants indexed array of associative array --> transform data to proper form first
function insert($table_name, $data, $opts = ["debug" => false, "update_duplicate" => false, "columns_to_update" => []])
{
    if (!is_array($data)) {
        throw new InvalidArgumentException("Data must be an array");
    }
    if (empty($data)) {
        throw new InvalidArgumentException("Data cannot be empty");
    }
    if (empty($table_name)) {
        throw new InvalidArgumentException("Table name cannot be empty");
    }
    if (!is_string($table_name)) {
        throw new InvalidArgumentException("Table name must be a string");
    }

    $is_debug = isset($opts["debug"]) && $opts["debug"];
    $update_duplicate = isset($opts["update_duplicate"]) && $opts["update_duplicate"];
    $columns_to_update = isset($opts["columns_to_update"]) ? $opts["columns_to_update"] : [];
    $sanitized_table_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $table_name);
    $is_indexed = array_keys($data) === range(0, count($data) - 1);

    // Check data structure before proceeding
    if ($is_indexed) {
        // if entity is not an array using an indexed array then return an issue 
        foreach ($data as $index => $entity) {
            if (!is_array($entity)) {
                throw new Exception("Each item in the data array must be an associative array when using bulk insert.");
            }
            // cant have an index array of index array --> list of objects 
            if (array_keys($entity) === range(0, count($entity) - 1)) {
                throw new Exception("Nested array of data cannot be an indexed array");
            }
        }
    } else {
        // forces primitives 
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                throw new Exception("The keys of the nested associative array must be strings");
            }
            if (is_array($value) || is_object($value)) {
                throw new Exception("The values of the nested associative array must be basic data types (not arrays or objects)");
            }
        }
    }

    // Sort keys and prepare columns and values clause
    $firstItem = $is_indexed ? $data[0] : $data;
    $sortedKeys = array_keys($firstItem);
    sort($sortedKeys); // Sort keys to ensure consistency
    $columns = join(", ", $sortedKeys);
    $valuesClause = [];
    $updateClause = [];

    if ($is_indexed) {
        foreach ($data as $index => $entity) {
            ksort($entity); // Sort array by key to match column order
            $placeholders = join(", ", array_map(fn ($key) => ":{$key}_{$index}", array_keys($entity)));
            $valuesClause[] = "($placeholders)";
        }
    } else {
        ksort($data); // Sort array by key to ensure correct order
        $placeholders = join(", ", array_map(fn ($key) => ":$key", array_keys($data)));
        $valuesClause[] = "($placeholders)";
    }

    $query = "INSERT INTO `$sanitized_table_name` ($columns) VALUES " . join(", ", $valuesClause);

    // allows for duplicating data, treat any duplicates as an update 
    if ($update_duplicate) {
        if (empty($columns_to_update)) {
            $columns_to_update = $sortedKeys; // Use sorted keys if no specific columns provided
        }
        foreach ($columns_to_update as $column) {
            $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
            $updateClause[] = "`$column`=VALUES(`$column`)";
        }
        $query .= " ON DUPLICATE KEY UPDATE " . join(", ", $updateClause);
    }

    $db = getDB(); // Assume getDB is a function that returns your PDO instance
    $stmt = $db->prepare($query);
    if ($is_debug) {
        error_log("Query: " . $query);
    }

    try {
        if ($is_indexed) {
            foreach ($data as $index => $entity) {
                foreach ($entity as $key => $value) {
                    // bind key and index
                    $stmt->bindValue(":{$key}_{$index}", $value);
                    if ($is_debug) {
                        error_log("Binding value for :{$key}_{$index}: $value");
                    }
                }
            }
        } else {
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
                if ($is_debug) {
                    error_log("Binding value for :$key: $value");
                }
            }
        }
        $stmt->execute();
        //note: this will likely return 0 for both values if it performs an "on duplicate key update"
        // got here did something
        return ["rowCount" => $stmt->rowCount(), "lastInsertId" => $db->lastInsertId()];
    } catch (PDOException $e) {
        throw $e;
    } catch (Exception $e) {
        throw $e;
    }
    // what ever calls this must handle these exceptions 

}

// Simple insert
function defaultInsert($data, $table, $update_duplicate = true)
{
    try {
        $opts = ["debug" => true, "update_duplicate" => $update_duplicate,  "columns_to_update" => []];
        $result = insert($table, $data, $opts);

        if (!$result) {
            flash("Unhandled Error", "warning");
        } else {
            flash("Created record with id " . var_export($result, true), "success");
        }
    } catch (InvalidArgumentException $e1) {
        error_log("Invalid arg" . var_export($e1, true));
        flash("Invalid data passed", "danger");
    } catch (PDOException $e2) {
        if ($e2->errorInfo[1] == 1062) {
            flash("An entry for this item already exists for today", "warning");
        } else {
            error_log("Database error" . var_export($e2, true));
            flash("Database error", "danger");
        }
    } catch (Exception $e3) {
        error_log("Invalid data records" . var_export($e3, true));
        flash("Invalid data records", "danger");
    }
}

// Inserts more game data on load
function insertGame($gameMap, $opts = ["addAll" => false, "addPlat" => false, "addGenre" => false])
{
    $addAll = $opts["addAll"];
    $addPlat = $opts["addPlat"];
    $addGenre = $opts["addGenre"];

    // Adds platform relations
    if (isset($gameMap["Platforms"])) {
        $platforms = $gameMap["Platforms"];
        // Lazy load platforms if needed (trades api call for sql call)
        if ($addAll || $addPlat) {
            defaultInsert($platforms, "Platforms", false);
        }
        foreach ($platforms as $index => $ent) {
            if (isset($platforms[$index]["id"])) {
                $platforms[$index]["platformId"] = $ent["id"];
                $platforms[$index]["gameId"] = $gameMap["id"];
            } else {
                continue;
            }
            foreach ($ent as $key => $val) {
                if (!in_array($key, ["platformId", "gameId"]))
                    unset($platforms[$index][$key]);
            }
        }
        defaultInsert($platforms, "PlatformGame", false);
        unset($gameMap["Platforms"]);
    }

    // Adds genre relations
    if (isset($gameMap["Genres"])) {
        $Genres = $gameMap["Genres"];
        // Lazy load Genres if needed (trades api call for sql call)
        if ($addPlat || $addGenre) {
            defaultInsert($Genres, "Genres", false);
        }
        foreach ($Genres as $index => $ent) {
            if (isset($Genres[$index]["id"])) {
                $Genres[$index]["genreId"] = $ent["id"];
                $Genres[$index]["gameId"] = $gameMap["id"];
            } else {
                continue;
            }
            foreach ($ent as $key => $val) {
                if (!in_array($key, ["genreId", "gameId"]))
                    unset($Genres[$index][$key]);
            }
        }
        defaultInsert($Genres, "GameGenre", false);
        unset($gameMap["Genres"]);
    }


    try {
        $opts = ["debug" => true, "update_duplicate" => true,  "columns_to_update" => []];
        $result = insert("Games", $gameMap, $opts);

        if (!$result) {
            flash("Unhandled Error", "warning");
        } else {
            flash("Created record with id " . var_export($result, true), "success");
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


// returnID: yes => provides genre and platform ID as associative, no => just names
// maybe use GROUP_CONCAT in the future to improve performance, add a segment for active only ( AND Games.is_active = 1)
function selectGameInfo($gameId, $returnID = false, $active_only = false)
{
    $db = getDB();
    $query = "SELECT Games.*,Platforms.id as `PlatformID`,Platforms.name as `Platform`,Genres.id as `GenreID`,Genres.name as `Genre`
    FROM
        (
            (
                (
                    (
                        `Games` LEFT JOIN `PlatformGame` p ON Games.`id` = p.`gameID`
                    ) LEFT JOIN `Platforms` ON `platformId` = Platforms.id
                ) LEFT JOIN `GameGenre` g ON Games.id = g.`gameID`
            ) LEFT JOIN `Genres` ON `genreId` = Genres.id
        )
    WHERE Games.id = :gameID";
    if ($active_only)
        $query .= "AND Games.is_active = 1";
    $params[":gameID"] = $gameId;
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $queryRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($queryRes) {
            $res = [];
            // take all the queries and ports the genres and platforms into a single output
            foreach ($queryRes as $index => $record) {
                if ($index == 0) {
                    $res = $record;
                    unset($res["Genre"]);
                    unset($res["GenreID"]);
                    unset($res["Platform"]);
                    unset($res["PlatformID"]);
                    $res["Genres"] = $returnID ? [$record["GenreID"] => $record["Genre"]] : [$record["Genre"]];
                    $res["Platforms"] = $returnID ? [$record["PlatformID"] => $record["Platform"]] : [$record["Platform"]];
                } elseif ($returnID) {
                    if (!in_array($record["GenreID"], $res["Genres"]))
                        $res["Genres"][$record["GenreID"]] = $record["Genre"];
                    if (!in_array($record["PlatformID"], $res["Platforms"]))
                        $res["Platforms"][$record["PlatformID"]] = $record["Platform"];
                } else {
                    if (!in_array($record["Genre"], $res["Genres"]))
                        array_push($res["Genres"], $record["Genre"]);
                    if (!in_array($record["Platform"], $res["Platforms"]))
                        array_push($res["Platforms"], $record["Platform"]);
                }
            }


            return $res;
        } else {
            return [];
        }
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}


// Function used to generate a list of active, intention is to
// call is expected to have id and name which correlate to ID of relationship
function getRelation($table, $data)
{
    if (empty($table)) {
        flash("An error has occured", "danger");
        error_log("Form generate failed");
        return [];
    }
    $sanitized_table_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $table);
    // Get active Genres
    $db = getDB();
    $active_items = [];
    $stmt = $db->prepare("SELECT id, name FROM `$sanitized_table_name` WHERE is_active=1");
    try {
        $stmt->execute();
        $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
        if ($results) {
            $active_items = $results;
        }
    } catch (PDOException $e) {
        flash(var_export($e->errorInfo, true), "danger");
    }

    $form = [];
    foreach ($active_items as $k => $v) {
        if (isset($data[$table]) || !empty($data[$table])) {
            $within = (in_array($v["id"], array_keys($data[$table])));
        } else {
            $within = false;
        }
        $val = $v["name"] . ($within ? " (active)" : " (inactive)");
        $checkbox = ["type" => "checkbox", "id" => strtolower($table) . "_" . $v["id"], "name" => strtolower($table) . "[]", "label" => $val, "value" => $v["id"]];
        array_push($form, $checkbox);
    }

    return $form;
}

/*
// used for testing via the cli (note: normally you'd used something like PHPUnit for proper test cases)
if (php_sapi_name() == "cli") {
    // Define the cli-only here
    class MockPDOStatement
    {
        public $queryString;

        public function __construct($queryString = '')
        {
            $this->queryString = $queryString;
        }

        public function bindValue($parameter, $value, $type = PDO::PARAM_STR)
        {
            // Optionally, print the bindValue calls for verification
            echo "bindValue called with: Parameter: $parameter, Value: $value, Type: $type\n";
        }

        public function execute()
        {
            // Simulate query execution
            echo "Execute called on query: $this->queryString\n";
            // Return true to simulate a successful execution
            return true;
        }

        public function rowCount()
        {
            // Return a simulated row count
            return 1;
        }

        public function fetch()
        {
            // Simulate fetching data
            return false; // Adjust based on needs
        }
    }

    class MockPDO
    {
        public function prepare($query)
        {
            echo "Prepare called with query: $query\n";
            // Return a new mock PDOStatement with the query
            return new MockPDOStatement($query);
        }

        public function lastInsertId()
        {
            // Simulate retrieving the last insert ID
            return 42; // Example ID
        }
    }
    function getDB()
    {
        // Return the mock PDO object instead of a real PDO object
        return new MockPDO();
    }
    // test suite
    function test_insert()
    {
        echo "Test 1: Expect pass " . PHP_EOL;
        try {
            insert('users', ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
            echo 'Test 1 Passed: Valid single record inserted successfully.' . PHP_EOL;
        } catch (Exception $e) {
            echo 'Test 1 Failed: ' . $e->getMessage() . PHP_EOL;
        }
        echo "Test 2: Expect pass " . PHP_EOL;
        try {
            insert('users', [
                ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
                ['id' => 3, 'name' => 'Alice', 'email' => 'alice@example.com']
            ]);
            echo 'Test 2 Passed: Valid multiple records inserted successfully.' . PHP_EOL;
        } catch (Exception $e) {
            echo 'Test 2 Failed: ' . $e->getMessage() . PHP_EOL;
        }
        echo "Test 3: Expect fail " . PHP_EOL;
        try {
            insert('users', [
                'id', 'name', 'email'
            ]); // Incorrect structure
            echo 'Test 3 Passed: Incorrect data type handled.' . PHP_EOL;
        } catch (Exception $e) {
            echo 'Test 3 Failed: ' . $e->getMessage() . PHP_EOL;
        }
        echo "Test 4: Expect fail " . PHP_EOL;
        try {
            insert('users', []);
            echo 'Test 4 Passed: Empty array handled.' . PHP_EOL;
        } catch (Exception $e) {
            echo 'Test 4 Failed: ' . $e->getMessage() . PHP_EOL;
        }
        echo "Test 5: Expect fail " . PHP_EOL;
        try {
            insert(['users'], [
                'id' => 4, 'name' => 'Bob', 'email' => 'bob@example.com'
            ]);
            echo 'Test 5 Passed: Invalid table name type handled.' . PHP_EOL;
        } catch (Exception $e) {
            echo 'Test 5 Failed: ' . $e->getMessage() . PHP_EOL;
        }
        echo "Test 6: Expect fail " . PHP_EOL;
        try {
            insert('users', [[1, 2, 3], [4, 5, 6]]); // Incorrect batch insert data
            echo 'Test 6 Passed: Non-associative array in batch insert handled.' . PHP_EOL;
        } catch (Exception $e) {
            echo 'Test 6 Failed: ' . $e->getMessage() . PHP_EOL;
        }
    }
    // Call the function to execute
    test_insert();
}
*/
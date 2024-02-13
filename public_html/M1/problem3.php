<?php
$a1 = [-1, -2, -3, -4, -5, -6, -7, -8, -9, -10];
$a2 = [-1, 1, -2, 2, 3, -3, -4, 5];
$a3 = [-0.01, -0.0001, -.15];
$a4 = ["-1", "2", "-3", "4", "-5", "5", "-6", "6", "-7", "7"];

function bePositive($arr)
{
    echo "<br>Processing Array:<br><pre>" . var_export($arr, true) . "</pre>";
    echo "<br>Positive output:<br>";

    // Ethan Ho - ekh3 - due: 2/5/24 - last worked: 2/5/24
    // assumptions: the conversion must be in place

    //note: use the $arr variable, don't directly touch $a1-$a4
    //TODO use echo to output all of the values as positive (even if they were originally positive) and maintain the original datatype
    foreach ($arr as $key => $num) {
        if ($num < 0) {
            $arr[$key] = $num * -1;
            settype($arr[$key], gettype($num));
        }
    }

    foreach ($arr as $num) {
        echo "$num - " . gettype($num) . " <br>";
    }
    // end

    //hint: may want to use var_dump() or similar to show final data types
}
echo "Problem 3: Be Positive<br>";
?>
<table>
    <thread>
        <th>A1</th>
        <th>A2</th>
        <th>A3</th>
        <th>A4</th>
    </thread>
    <tbody>
        <tr>
            <td>
                <?php bePositive($a1); ?>
            </td>
            <td>
                <?php bePositive($a2); ?>
            </td>
            <td>
                <?php bePositive($a3); ?>
            </td>
            <td>
                <?php bePositive($a4); ?>
            </td>
        </tr>
</table>
<style>
    table {
        border-spacing: 2em 3em;
        border-collapse: separate;
    }

    td {
        border-right: solid 1px black;
        border-left: solid 1px black;
    }
</style>
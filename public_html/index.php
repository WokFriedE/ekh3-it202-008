<?php echo "<h1>It works!</h1>";

function getDirFiles($folder, $indent)
{
    $path = $folder;
    if ($handle = opendir($path)) {

        while (false !== ($entry = readdir($handle))) {
            $route = $path . "/" . $entry;
            //ignores parent and curr dir
            if ($entry != "." && $entry != ".." && $route != "./index.php") {
                // if a directory then expand
                if (is_dir($route)) {
                    echo "<a style=\"margin: 0em; margin-left:$indent" . "em\"><b>$entry</b></a><br>";
                    getDirFiles($route, $indent + 2);
                    // if a file, specifically php, then make it a link
                } else if (explode(".", $entry)[1] == "php" || explode(".", $entry)[1] == "html") {
                    echo "<a style=\"margin: 0em; margin-left:$indent" . "em\" href=\"$route\">$entry</a><br>";
                }
            }
        }
        closedir($handle);
    }
}

getDirFiles("./", 0);

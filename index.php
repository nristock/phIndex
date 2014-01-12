<html>
<head>
    <style type="text/css">
        table {
            font-family: monospace;
            line-height: 1em;
        }

        tr > td:nth-child(1) {
            min-width: 250px;
            padding-right: 50px;
        }
        tr > td:nth-child(2) {
            min-width: 200px;
        }
        tr > td:nth-child(3) {
            padding-left: 30px;
            text-align: right;
        }
    </style>
</head>

<body>
<?php
# We don't want our index.php or relative links to show up
$excludes = ['index.php', '.', '..'];

# Change this if needed
date_default_timezone_set('Europe/Berlin');

# Get our modification time so we know if we have to update child files
$lastModified = filemtime(__FILE__);

/**
 * Updates the index.php of a directory if needed.
 * @param $dir string The directory name to update.
 */
function updateDirectory($dir)
{
    global $lastModified;

    if ($directory = opendir($dir)) {
        $indexFile = null;

        while (false !== ($file = readdir($directory))) {
            if ($file == 'index.php') {
                $indexFile = $file;
                break;
            }
        }

        if ($indexFile == null || (filemtime("$dir/$indexFile") < $lastModified)) {
            updateIndex($dir);
        }

        closedir($directory);
    }
}

/**
 * Does the actual update of an index.php file in a directory.
 * @param $dir string Name of directory to update.
 */
function updateIndex($dir)
{
    # Copy ourselves
    $lines = file(__FILE__);

    file_put_contents("$dir/index.php", $lines);
}

# Start of actual script


if ($directory = opendir(__DIR__)) {
    $files = array();
    $directories = array();

    # Iterate through all files/directories in the current directory, match excludes
    while (false !== ($file = readdir($directory))) {
        $isExcluded = false;
        foreach ($excludes as $exclude) {
            if (fnmatch($exclude, $file)) {
                $isExcluded = true;
            }
        }
        if ($isExcluded) {
            continue;
        }

        if (is_dir($file)) {
            $directories[count($directories)] = $file;
        } elseif (is_file($file)) {
            $files[count($files)] = $file;
        }
    }

    # Get our current path
    $currentPath = rtrim($_SERVER['PHP_SELF'], '/index.php');
    echo "<h1>Index of $currentPath/</h1><hr>";

    sort($files, SORT_NATURAL);
    sort($directories, SORT_NATURAL);

    echo '<table><tbody>';

    # Print a 'Got to parent directory' link
    if (!empty($currentPath)) {
        echo '<tr><td><a href="' . dirname($currentPath) . '">../</a></td><td></td><td></td></tr>';
    }

    # Print files
    foreach ($files as $file) {
        echo "<tr><td><a href=\"" . basename($file) . "\">$file</a></td><td>" . date('d-M-Y H:i', filemtime($file)) . "</td><td>" . filesize($file) . "</td></tr>";
    }
    echo '</tbody></table>';


    # Print directories
    echo '<table><tbody>';
    foreach ($directories as $dir) {
        updateDirectory($dir);

        echo "<tr><td><a href=\"" . basename($dir) . "\">$dir</a></td><td>" . date('d-M-Y H:i', filemtime($dir)) . "</td><td>" . filesize($dir) . "</td></tr>";
    }
    echo '</tbody></table><hr>';

    closedir($directory);
}
?>
</body>
</html>

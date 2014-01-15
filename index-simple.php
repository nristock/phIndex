<?php
# Some configuration options
$showDirectoryFileSize = false;
$dateFormat = 'd-M-Y H:i';

$cssListBorderRadius = '8px';

# We don't want our index.php or relative links to show up ('go one up'-link will be generated automatically)
$excludes = ['index.php', '.', '..'];

# Change this if needed
date_default_timezone_set('Europe/Berlin');

# Get our modification time so we know if we have to update child files
$lastModified = filemtime(__FILE__);

# Disable client side caching
header("cache-control: private, max-age=60, no-cache", true);

/**
 * Updates the index.php of a directory if needed.
 * @param $dir string The directory name to update
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

/**
 * Creates human readable file size strings.
 * @param $fileSize integer
 * @return string A pretty format file size.
 */
function formatSize($fileSize)
{
    $sizeInBytes = sprintf('%u', $fileSize);

    if ($sizeInBytes > 0) {
        $unitIndex = intval(log($sizeInBytes, 1024));
        $units = array('B', 'KB', 'MB', 'GB');

        if (array_key_exists($unitIndex, $units) === true) {
            return round($sizeInBytes / pow(1024, $unitIndex)) . " $units[$unitIndex]";
        }
    }

    return $sizeInBytes;
}

?>
<?php
$indexGenerationFailed = false;

if ($directory = opendir(__DIR__)) {
    $files = array();
    $directories = array();

# Iterate through all files/directories in the current directory, ignore excludes
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
            updateDirectory($file);
        } elseif (is_file($file)) {
            $files[count($files)] = $file;
        }
    }

    sort($files, SORT_NATURAL);
    sort($directories, SORT_NATURAL);

    closedir($directory);
} else {
    $indexGenerationFailed = true;
}

# Get our current path
$currentPath = rtrim($_SERVER['PHP_SELF'], '/index.php');
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
<div class="container">
    <h1>Index of <?= $currentPath ?>/</h1>
    <hr>

    <table>
        <tbody>
        <!-- Print a 'Got to parent directory' link -->
        <?php if (!empty($currentPath)) { ?>
            <tr>
                <td><a href="<?= dirname($currentPath) ?>">../</a></td>
                <td></td>
                <td></td>
            </tr>
        <?php } ?>

        <!-- Print files -->
        <?php foreach ($files as $file) { ?>
            <tr>
                <td><a href="<?= basename($file) ?>"><?= $file ?></a></td>
                <td><?= date($dateFormat, filemtime($file)) ?></td>
                <td><?= formatSize(filesize($file)) ?></td>
            </tr>
        <?php } ?>
        <?php foreach ($directories as $dir) { ?>
            <tr>
                <td><a href="<?= basename($dir) ?>"><?= $dir ?></a></td>
                <td><?= date($dateFormat, filemtime($dir)) ?></td>
                <td><?= $showDirectoryFileSize ? formatSize(filesize($dir)) : 'DIR' ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <hr>

    <div class="text-right">
        Index powered by <a href="https://github.com/Monofraps/phIndex">phIndex</a>.
    </div>
</div>
</body>
</html>

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
        body {
            background-color: white;

            color: #505353;
        }

        a {
            text-decoration: none;
        }

        .text-right {
            text-align: right;
        }

        .container {
            margin-left: auto;
            margin-right: auto;

            padding-left: 20px;
            padding-right: 20px;
        }

        .col-filename {
            font-family: monospace;
            font-size: 16px;
        }

        .col-filesize {
            text-align: right;
        }

        .list-head > div {
            font-family: serif;
            font-size: 19px;
            font-weight: bold;
        }

        .col-left {
            float: left;
        }

        .col-right {
            float: right;
        }

        .fluid-row:nth-of-type(even) {
            background-color: #f8f8ff;
        }

        .fluid-row:nth-of-type(odd) {
            background-color: #e3ebf5;
        }

        .fluid-row {
            padding: 5px 10px;

            border: 1px solid rgba(0, 0, 0, 0.15);
        }

        .fluid-row:first-of-type {
            border-top-right-radius: <?= $cssListBorderRadius ?>;
            border-top-left-radius: <?= $cssListBorderRadius ?>;
        }

        .fluid-row:last-of-type {
            border-bottom-left-radius: <?= $cssListBorderRadius ?>;
            border-bottom-right-radius: <?= $cssListBorderRadius ?>;
        }

        .file-list {
            margin: 20px 0;
        }

        /*
         * Meant for mobile devices
         */
        @media (max-width: 769px) {
            .col-datetime {
                float: left !important;
            }

            .col-filesize {
                text-align: left;
                float: right !important;
            }

            .col-controls {
                clear: both;
                padding-bottom: 15px;
            }

            .hidden-small {
                visibility: hidden;
                display: none;
            }

            .fluid-row:nth-of-type(2) {
                border-top-right-radius: <?= $cssListBorderRadius ?>;
                border-top-left-radius: <?= $cssListBorderRadius ?>;
            }

            .col-left, .col-right {
                float: none;
            }
        }

        /*
         * Small screens
         */
        @media (min-width: 770px) {
            .container {
                width: 730px;
            }

            .col-filename {
                width: 350px;
            }

            .col-datetime {
                width: 150px;
            }

            .col-filesize {
                width: 50px;
            }

            .fluid-row:nth-of-type(n+2) {
                border-top: none;
            }

            .fluid-row:after {
                content: " ";
                display: block;
                height: 0;
                clear: both;
                visibility: hidden;
            }
        }

        /*
         * Medium screens
         */
        @media (min-width: 1000px) {
            .container {
                width: 960px;
            }

            .col-filename {
                width: 400px;
            }

            .col-datetime {
                width: 200px;
            }

            .col-filesize {
                width: 50px;
            }
        }

        /*
         * Large screens
         */
        @media (min-width: 1200px) {
            .container {
                width: 1160px;
            }

            .col-filename {
                width: 450px;
            }

            .col-datetime {
                width: 300px;
            }

            .col-filesize {
                width: 100px;
            }
        }
    </style>
</head>

<body>
<div class="container">
    <h1>Index of <?= $currentPath ?>/</h1>
    <hr>

    <div class="file-list">
        <article class="fluid-row list-head hidden-small">
            <div class="col-left col-filename">Filename</div>
            <div class="col-left col-datetime">Last modified</div>
            <div class="col-left col-filesize">Size</div>
        </article>
        <?php if (!empty($currentPath)) { ?>
            <article class="fluid-row">
                <div class="col-left col-filename"><a href="<?= dirname($currentPath) ?>">/..</a></div>
            </article>
        <?php } ?>
        <?php foreach ($files as $file) { ?>
            <article class="fluid-row">
                <div class="col-left col-filename"><a href="<?= basename($file) ?>"><?= $file ?></a></div>
                <div class="col-left col-datetime"><?= date($dateFormat, filemtime($file)) ?></div>
                <div class="col-left col-filesize"><?= formatSize(filesize($file)) ?></div>
                <div class="col-right col-controls"><a href="<?= basename($file) ?>">Download</a></div>
            </article>
        <?php } ?>
        <?php foreach ($directories as $dir) { ?>
            <article class="fluid-row">
                <div class="col-left col-filename"><a href="<?= basename($dir) ?>"><?= $dir ?></a></div>
                <div class="col-left col-datetime"><?= date($dateFormat, filemtime($dir)) ?></div>
                <div class="col-left col-filesize"><?= $showDirectoryFileSize ? formatSize(filesize($dir)) : 'DIR' ?></div>
                <div class="col-right col-controls"><a href="<?= basename($file) ?>">Open</a></div>
            </article>
        <?php } ?>
    </div>

    <hr>

    <div class="text-right">
        Index powered by <a href="https://github.com/Monofraps/phIndex">phIndex</a>.
    </div>
</div>
</body>
</html>

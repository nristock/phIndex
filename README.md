phIndex
=======

Simply copy index.php into the directory you want to index. Change excludes and formatting as needed. The script will take care
of replicating itself in the sub-directory tree.<br>
The visual style of the generated index apes nginx behaviour. - Feel free to change the Stylesheets, though ;)

If you ever want to update the script, simply update the one that's at the root of your index hierarchy. The script will
compare timestamps of index.php files in sub-directories and replace them if needed.


## Why?!
Nginx is the web server of my choice. However, the HttpAutoindexModule which is used to display directory indexes neither
supports an option to exclude files nor does it sort numerical names correctly.<br>
`10` would be placed after `1` but before `2` - this script resolves this issue.

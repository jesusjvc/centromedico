<?php

if (!defined('ROOT_PATH')) {
  die("Security violation");
}



// User levels
define('GUEST', -1);
define('USER_AWAITING', 1);
define('USER', 2);
define('ADMIN', 10);


// Permission levels
define('AUTH_ALL', 0);
define('AUTH_USER', 2);
define('AUTH_ACL', 3);
define('AUTH_ADMIN', 9);


// Group types
define('GROUPTYPE_GROUP', 1);
define('GROUPTYPE_SINGLE', 2);


// Chmod for files and directories created by 4images
define('CHMOD_FILES', 0644);
define('CHMOD_DIRS', 0755);


// Will be used to replace the {xxx} tage if the value is empty.
// Netscape Browser sometimes need this to display table cell background colors.
define('REPLACE_EMPTY', '&nbsp;');


// Time offset for your website. Sometimes usefull if your server is located
// in other timezones.
define('TIME_OFFSET', 0);


// All words <= MIN_SEARCH_KEYWORD_LENGTH and >= MAX_SEARCH_KEYWORD_LENGTH
// are not added to the search index
define('MIN_SEARCH_KEYWORD_LENGTH', 3);
define('MAX_SEARCH_KEYWORD_LENGTH', 25);

// If you set this to 1, admins will authenticated additionally with cookies.
// If you use "User Integration", you should set this to 0.
define('ADMIN_SAFE_LOGIN', 0);


// If you use GD higher 2.0.1 and PHP higher 4.0.6 set this to 1.
// Your thumbnails will be created with better quality
//define('CONVERT_IS_GD2', 0);


// Check existence of remote image files.
// If you choose 1, you could get sometimes timeout errors
define('CHECK_REMOTE_FILES', 0);


// Allow execution of PHP code in templates
define('EXEC_PHP_CODE', 1);

// Data paths
define('MEDIA_DIR', 'data/media');
define('ICON_DIR', 'data/icons');
define('THUMB_DIR', 'data/thumbnails');
define('MEDIA_TEMP_DIR', 'data/tmp_media');
define('THUMB_TEMP_DIR', 'data/tmp_thumbnails');
define('DATABASE_DIR', 'data/database');
define('TEMPLATE_DIR', 'temas');
define('ICON_PATH', 'img/icons');



// Debug contants
// define("PRINT_STATS", 1);
// define("PRINT_QUERIES", 1);
// define('PRINT_CACHE_MESSAGES', 1);

?>
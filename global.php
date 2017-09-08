<?php
/* 
---------------------------------------------------
Archivo de Configuración Global
---------------------------------------------------
Consultorio 1.0
Desarrollado por: Esbrillante Estudio Digital
https://esbrillante.mx
---------------------------------------------------
*/

if (!defined('ROOT_PATH')) {
  die("Security violation");
}
// Errores de ejecucion simples
// error_reporting(E_ERROR | E_WARNING | E_PARSE);
// Notificar todos los errores de PHP (ver el registro de cambios)
 error_reporting(E_ALL);
//error_reporting(0);
$start_time = microtime();

function addslashes_array($array) {
  foreach ($array as $key => $val) {
    $array[$key] = (is_array($val)) ? addslashes_array($val) : addslashes($val);
  }
  return $array;
}

if (!isset($_GET)) {
  $_GET    = &$_GET;
  $_POST   = &$_POST;
  $_COOKIE = &$_COOKIE;
  $_FILES  = &$_FILES;
  $HTTP_SERVER_VARS = &$_SERVER;
  $HTTP_ENV_VARS    = &$_ENV;
}

if (isset($_GET['GLOBALS']) || isset($_POST['GLOBALS']) || isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
	// Try to exploit PHP bug
	die("Security violation");
}

if (get_magic_quotes_gpc() == 0) {
  $_GET    = addslashes_array($_GET);
  $_POST   = addslashes_array($_POST);
  $_COOKIE = addslashes_array($_COOKIE);
}

$cat_cache = array();
$cat_parent_cache = array();
$new_image_cache = array();
$session_info = array();
$user_menu = array();
$user_info = array();
$user_access = array();
$config = array();
$lang = array();
$mime_type_match = array();
$additional_image_fields = array();
$additional_user_fields = array();
$additional_urls = array();
$global_info = array();
$auth_cat_sql = array();
unset($self_url);
unset($url);
unset($script_url);
//Horario Local
 date_default_timezone_set('America/Mexico_City');

// Initialize cache configuration
$cache_enable          = 0;
$cache_lifetime        = 3600; // 1 hour
$cache_path            = ROOT_PATH.'cache';
$cache_page_index      = 0;
$cache_vergalerias			=1;
$cache_page_categories = 1;
$cache_page_top        = 0;
$cache_page_rss        = 1;

@include(ROOT_PATH.'configuracion.php');

if (!$cache_enable) {
  $cache_page_index      = 0;
  $cache_page_categories = 0;
  $cache_page_top        = 0;
  $cache_page_rss        = 0;
}

// Incluir idioma por default
include_once(ROOT_PATH.'includes/constants.php');
include_once(ROOT_PATH.'includes/functions.php');

function clean_array($array) {
  $search = array(
    // Remove any attribute starting with "on" or xmlns
    '#(<[^>]+[\x00-\x20\"\'])(on|xmlns)[^>]*>#iUu',
    // Remove javascript: and vbscript: protocol
    '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*)[\\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu',
    '#([a-z]*)[\x00-\x20]*=([\'\"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu',
    //<span style="width: expression(alert('Ping!'));"></span>
    // Only works in ie...
    '#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*expression[\x00-\x20]*\([^>]*>#iU',
    '#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*behaviour[\x00-\x20]*\([^>]*>#iU',
    '#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*>#iUu'
  );

  $replace = array(
    "$1>",
    '$1=$2nojavascript...',
    '$1=$2novbscript...',
    "$1>",
    "$1>",
    "$1>"
  );

  // Remove all control (i.e. with ASCII value lower than 0x20 (space),
  // except of 0x0A (line feed) and 0x09 (tabulator)
  $search2 =
      "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0B\x0C\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";
  $replace2 = //str_repeat("\r", strlen($search2));
      "\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D\x0D";

  foreach ($array as $key => $val) {
    if (is_array($val)) {
      $val = clean_array($val);
    } else {
      $val = preg_replace($search, $replace, $val);

      $val = str_replace("\r\n", "\n", $val);
      $val = str_replace("\r",   "\n", $val);
      $val = strtr($val, $search2, $replace2);
      $val = str_replace("\r", '', $val);  // \r === \x0D

      do {
        $oldval = $val;
        $val = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $val);
      } while ($oldval != $val);
    }

    $array[$key] = $val;
  }

  return $array;
}

if (!defined('IN_CP')) {
  $_GET    = clean_array($_GET);
  $_POST   = clean_array($_POST);
  $_COOKIE = clean_array($_COOKIE);
  $_FILES  = clean_array($_FILES);
}

//-----------------------------------------------------
//--- Useful Stuff ------------------------------------
//-----------------------------------------------------
if (isset($_GET['action']) || isset($_POST['action'])) {
  $action = (isset($_POST['action'])) ? stripslashes(trim($_POST['action'])) : stripslashes(trim($_GET['action']));
  $action = preg_replace("/[^a-z0-9_-]+/i", "", $action);
}
else {
  $action = "";
}

if (isset($_GET['mode']) || isset($_POST['mode'])) {
  $mode = (isset($_POST['mode'])) ? stripslashes(trim($_POST['mode'])) : stripslashes(trim($_GET['mode']));
  $mode = preg_replace("/[^a-z0-9_-]+/i", "", $mode);
}
else {
  $mode = "";
}

if (isset($_POST['show_result']) || isset($_GET['show_result'])) {
  $show_result = 1;
}
else {
  $show_result = 0;
}

if (isset($_POST['q']) || isset($_GET['q'])) {
  $search_keywords = (isset($_POST['q'])) ? trim($_POST['q']) : trim($_GET['q']);
  if ($search_keywords != "") {
    $show_result = 1;
  }
}
else {
  $search_keywords = "";
}

if (isset($_POST['search_user']) || isset($_GET['search_user'])) {
  $search_user = (isset($_POST['search_user'])) ? trim($_POST['search_user']) : trim($_GET['search_user']);
  if ($search_user != "") {
    $show_result = 1;
  }
}
else {
  $search_user = "";
}

if (isset($_POST['search_new_images']) || isset($_GET['search_new_images'])) {
  $search_new_images = 1;
  $show_result = 1;
}
else {
  $search_new_images = 0;
}

if (empty($PHP_SELF)) {
  if (!empty($HTTP_SERVER_VARS['PHP_SELF'])) {
    $PHP_SELF = $HTTP_SERVER_VARS["PHP_SELF"];
  }
  elseif (!empty($HTTP_ENV_VARS['PHP_SELF'])) {
    $PHP_SELF = $HTTP_ENV_VARS["PHP_SELF"];
  }
	elseif (!empty($HTTP_SERVER_VARS['PATH_INFO'])) {
    $PHP_SELF = $HTTP_SERVER_VARS['PATH_INFO'];
  }
  else {
    $PHP_SELF = getenv("SCRIPT_NAME");
  }
}

$self_url = basename($PHP_SELF);
if (empty($self_url) || !preg_match("/\.php$/", $self_url)) {
  $self_url = "index.php";
}

//if (getenv("QUERY_STRING")) {
//  $self_url .= "?".getenv("QUERY_STRING");
//  $self_url = preg_replace(array("/([?|&])action=[^?|&]*/", "/([?|&])mode=[^?|&]*/", "/([?|&])phpinfo=[^?|&]*/", "/([?|&])printstats=[^?|&]*/", "/[?|&]".URL_ID."=[^?|&]*/", "/[?|&]l=[^?|&]*/", "/[&?]+$/"), array("", "", "", "", "", "", ""), $self_url);
//}
//else {
  if (preg_match("/info.php/", $self_url) && !preg_match("/[?|&]".URL_IMAGE_ID."=[^?|&]*/", $self_url) && $image_id) {
    $self_url .= "?".URL_IMAGE_ID."=".$image_id;
  }
  elseif (preg_match("/galerias.php/", $self_url) && !preg_match("/[?|&]".URL_CAT_ID."=[^?|&]*/", $self_url)) {
    $self_url .= "?".URL_CAT_ID."=".$cat_id;
  }
  if (isset($show_result) && $show_result) {
    $self_url .= preg_match("/\?/", $self_url) ? "&amp;" : "?";
    $self_url .= "show_result=1";
  }

//}

if (isset($_GET['url']) || isset($_POST['url'])) {
  $url = (isset($_GET['url'])) ? trim($_GET['url']) : trim($_POST['url']);
}
else {
  $url = "";
}
if (empty($url)) {
  $url = get_basefile(getenv("HTTP_REFERER"));
  $obtenurl  = getenv("HTTP_REFERER"); // Obteniendo la url completa de donde se encuestra el usuario
}
else {
  if ($url == getenv("HTTP_REFERER")) {
    $url = "index.php";
  }
}

if (defined("SCRIPT_URL") && SCRIPT_URL != "") {
  $script_url = SCRIPT_URL;
}
else {
  $port = (!preg_match("/^(80|443)$/", getenv("SERVER_PORT"), $port_match)) ? ":".getenv("SERVER_PORT") : "";
  $script_url  = (isset($port_match[1]) && $port_match[1] == 443) ? "https://" : "http://";
  $script_url .= (!empty($HTTP_SERVER_VARS['HTTP_HOST'])) ? $HTTP_SERVER_VARS['HTTP_HOST'] : getenv("SERVER_NAME");
  $script_url .= $port;

  $dirname = str_replace("\\", "/", dirname($PHP_SELF));
  $script_url .= ($dirname != "/") ? $dirname : "";
}

//-----------------------------------------------------
//--- Conexión a la Base de Datos 
//-----------------------------------------------------

include_once('includes/class.db.php');
// include_once(ROOT_PATH.'includes/db_mysql.php');
//setErrorCallbackFunction Method Declaration
// public function setErrorCallbackFunction($errorCallbackFunction, $errorMsgFormat="html") { }

//The error message can then be displayed, emailed, etc within the callback function.
function myErrorHandler($error) {
  echo $error;
}


$db = new db("mysql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_password);
$db->run('SET NAMES utf8');
$db->setErrorCallbackFunction("myErrorHandler");

$msg = "";

if (isset($modo_de_acceso)) {
  define('TEMPLATE_PATH', "temas/".$tema_actual);
}
else
define('TEMPLATE_PATH', ROOT_PATH."temas/".$tema_actual);


//-----------------------------------------------------
//--- Templates / Temas ---------------------------------------
//-----------------------------------------------------
include_once(ROOT_PATH.'includes/plantilla.php');
$site_template = new Template(TEMPLATE_PATH);
?>
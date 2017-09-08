<?php
/* 
---------------------------------------------------
Configuración de Sesiones
---------------------------------------------------
Consultorio 1.0
Desarrollado por: Esbrillante Estudio Digital
https://esbrillante.mx
---------------------------------------------------
*/

if (!defined('ROOT_PATH')) {
  die("Security violation");
}

//-----------------------------------------------------
//--- Start Configuration -----------------------------
//-----------------------------------------------------

define('SESSION_NAME', 'sessionid');

$user_table_fields = array(
  "id_user" => "id_user",
  "nombre" => "nombre",
  "apellidos" => "apellidos",
  "usuario" => "usuario",
  "password" => "password",
  "email" => "email",
  "tipo_cuenta" => "tipo_cuenta" 
);

//-----------------------------------------------------
//--- End Configuration -------------------------------
//-----------------------------------------------------

function get_user_table_field($add, $user_field) {
  global $user_table_fields;
  return (!empty($user_table_fields[$user_field])) ? $add.$user_table_fields[$user_field] : "";
}

class Session {

  var $session_id;
  var $session_key;
  var $current_time;
  var $session_timeout;
  var $mode = "get";
  var $session_info = array();
  var $user_info = array();

  function Session() {
    //Duracion de la sesión 
    $this->session_timeout = 30 * 60;
    $this->current_time = time();

    if (defined('SESSION_KEY') && SESSION_KEY != '') {
        $this->session_key = SESSION_KEY;
    } else {
        $this->session_key = md5('LogosCDA' . realpath(ROOT_PATH));
    }
    // Stop adding SID to URLs
    @ini_set('session.use_trans_sid', 0);
    //@ini_set('session.cookie_lifetime', $this->session_timeout);
    session_name(urlencode(SESSION_NAME));
    @session_start();

    $this->demand_session();
  }

  function set_cookie_data($name, $value, $permanent = 1) {
    $cookie_expire = ($permanent) ? $this->current_time + 60 * 60 * 24 * 365 : 0;
    $cookie_name = COOKIE_NAME.$name;
    setcookie($cookie_name, $value, $cookie_expire, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE);
    $HTTP_COOKIE_VARS[$cookie_name] = $value;
  }

  function read_cookie_data($name) {
    global $HTTP_COOKIE_VARS;
    $cookie_name = COOKIE_NAME.$name;
    return (isset($HTTP_COOKIE_VARS[$cookie_name])) ? $HTTP_COOKIE_VARS[$cookie_name] : false;
  }

  function get_session_id() {
    if (SID == '') {
      $this->mode = "cookie";
    }

    if (preg_match('/[^a-z0-9]+/i', session_id())) {
      @session_regenerate_id();
    }

    $this->session_id = session_id();
  }

  function demand_session() {
    $this->get_session_id();
    if (!$this->load_session_info()) {
      $user_id = ($this->read_cookie_data("userid")) ? intval($this->read_cookie_data("userid")) : GUEST;
      $this->start_session($user_id);
    }
    else {
      $this->user_info = $this->load_user_info($this->session_info['session_user_id']);     
    }
  }

  function start_session($user_id = GUEST, $login_process = 0) {

    $this->user_info = $this->load_user_info($user_id);
    if ($this->user_info['id_user'] != GUEST && !$login_process) {
      if ($this->read_cookie_data("userpass") == $this->user_info['user_password'] && $this->user_info['user_level'] > USER_AWAITING) {
        $this->set_cookie_data("userpass", $this->user_info['user_password']);
      }
      else {
        $this->set_cookie_data("userpass", "", 0);
        $this->user_info = $this->load_user_info(GUEST);
      }
    }    

    $this->session_info['session_user_id'] = $this->user_info['id_user'];   
    $this->set_cookie_data("userid", $this->user_info['id_user']);
    return true;
  }
  
  //inicio de sesión al sistema
  function login($user_name = "", $user_password = "", $auto_login = 0, $set_auto_login = 1) {
    global $db, $user_table_fields, $salt;

    if (empty($user_name) || empty($user_password)) {
      return false;
    } 
    $bind = array(
        ":userName" => $user_name
    );        
    $rs = $db->select("usuarios", "usuario = :userName", $bind, "id_user, password");
    
    
    if (empty($rs)) 
      return false;
    else
      $row = $rs[0];

    $user_password = crypt($user_password, $salt);    
   
      if ($row['password'] == $user_password) {       
        if ($set_auto_login) {       
          $this->set_cookie_data("userpass", ($auto_login) ? $user_password : "");
        }

        $this->start_session($row['id_user'], 1);
        return true;
      }
    
    return false;
  }

  function logout($user_id) {
    $this->set_cookie_data("userpass", "", 0);
    $this->set_cookie_data("userid", GUEST);
    $this->session_info = array();
    return true;
  }

  function return_session_info() {
    return $this->session_info;
  }

  function return_user_info() {
    return $this->user_info;
  }

  function freeze() {
    return;
  }

  function load_session_info() {
    if (@ini_get('register_globals')) {
      session_register($this->session_key);

      if (!isset($GLOBALS[$this->session_key])) {
        $GLOBALS[$this->session_key] = array();
      }

      $this->session_info = &$GLOBALS[$this->session_key];

    } else {
      if (isset($_SESSION)) {
        if (!isset($_SESSION[$this->session_key])) {
          $_SESSION[$this->session_key] = array();
        }

        $this->session_info = &$_SESSION[$this->session_key];

      } else {
        if (!isset($GLOBALS['HTTP_SESSION_VARS'][$this->session_key])) {
          $GLOBALS['HTTP_SESSION_VARS'][$this->session_key] = array();
        }

        $this->session_info = &$GLOBALS['HTTP_SESSION_VARS'][$this->session_key];
      }
    }

    if ($this->mode == "get") {
      if (function_exists('session_regenerate_id')) {
        @session_regenerate_id();
      }
      $this->get_session_id();
      $this->session_info = array();
      return false;
    }

    return $this->session_info;
  }

  function load_user_info($user_id = GUEST) {
      global $db, $user_table_fields;

    if (empty($user_id)) {
      $user_id = GUEST;
    }
    if ($user_id != GUEST) {
      $sql = "SELECT * FROM usuarios WHERE id_user = $user_id";
      $rs = $db->run($sql);  
      $user_info = $rs[0];
    }
    if (empty($user_info['id_user'])) {
      $user_info = array();
      $user_info['id_user'] = GUEST;      
    }
    foreach ($user_table_fields as $key => $val) {
      if (isset($user_info[$val])) {
        $user_info[$key] = $user_info[$val];
      }
      elseif (!isset($user_info[$key])) {
        $user_info[$key] = "";
      }
    }
    return $user_info;
  }

  function set_session_var($var_name, $value) {
    $this->session_info[$var_name] = $value;
    return true;
  }
  
  function get_session_var($var_name) {
    if (isset($this->session_info[$var_name])) {
      return $this->session_info[$var_name];
    }

    return '';
  }

  function url($url, $amp = "&amp;") {
    global $l;
    $dummy_array = explode("#", $url);
    $url = $dummy_array[0];

    if ($this->mode == "get" && strpos($url, $this->session_id) === false) {
      $url .= strpos($url, '?') !== false ? $amp : "?";
      $url .= SESSION_NAME."=".$this->session_id;
    }

    if (!empty($l)) {
      $url .= strpos($url, '?') !== false ? $amp : "?";
      $url .= "l=".$l;
    }

    $url .= (isset($dummy_array[1])) ? "#".$dummy_array[1] : "";
    return $url;
  }
} //end of class

//-----------------------------------------------------
//--- Start Session -----------------------------------
//-----------------------------------------------------
define('COOKIE_NAME', 'ConsultorioOnline_');
define('COOKIE_PATH', '');
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', '0');

$site_sess = new Session();

// Get Userinfo
$session_info = $site_sess->return_session_info();
$user_info = $site_sess->return_user_info();

?>
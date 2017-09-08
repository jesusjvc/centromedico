<?php
/* 
---------------------------------------------------
Login de usuario
---------------------------------------------------
Desarrollo por: Esbrillante.mx
---------------------------------------------------
*/


define('ROOT_PATH', './');
include(ROOT_PATH.'global.php');
require(ROOT_PATH.'includes/sessions.php');

$error = 0;
if (empty($_POST['username']) || empty($_POST['password'])) {
  if (!preg_match("index.php", $url) && !preg_match("login.php", $url) && !preg_match("nuevo_usuario.php", $url) && !preg_match("usuario.php", $url)) {
    redirect($obtenurl);
  }
  else {
    redirect("index.php");
  }
}
else {
//Se reciben las variables del inicio de sesión
  $user_name = trim($_POST['username']);
  $user_password = trim($_POST['password']);
  $auto_login = (isset($_POST['auto_login']) && $_POST['auto_login'] == 1) ? 1 : 0;
  
  //echo $user_name . "   ".  $user_password. "   ".  $user_url . " " .$obtenurl; 

  if ($site_sess->login($user_name, $user_password, $auto_login)) {
    if (!preg_match('/index.php/', $url) && !preg_match("/login.php/", $url) && !preg_match("/nuevo_usuario.php/", $url) && !preg_match("/usuario.php/", $url)) {
      //redirect($obtenurl);
    }
    else {
      redirect("index.php");
    }
  }
  else {
    $error = "Error al iniciar sesión";
  }
}
if ($error) {
  $plantilla = 'login';
  include(ROOT_PATH.'includes/page_header.php');
  
  $msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> El usuario o la contraseña son incorrectos.</div>';

  $site_template->register_vars(array(
    "msg" => $msg
  ));

  $site_template->print_template($site_template->analizar_plantilla($plantilla));
}
else{
  redirect("index.php");
}

?>
<?php
/* 
---------------------------------------------------
Terminar la sesión del usuario
---------------------------------------------------
Desarrollo por: Esbrillante.mx
---------------------------------------------------
*/

$main_template = 0;

$nozip = 1;
define('ROOT_PATH', './');
include(ROOT_PATH.'global.php');
require(ROOT_PATH.'includes/sessions.php');

$site_sess->logout($user_info['id_cliente']);
if (!preg_match("/index.php/", $url) && !preg_match("/login.php/", $url)) {
  redirect($obtenurl);
}
else {
  redirect("index.php");
}
?>
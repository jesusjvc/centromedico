<?php
/* 
---------------------------------------------------
Página principal
---------------------------------------------------
Consultorio 1.0
Desarrollado por: Esbrillante Estudio Digital
https://esbrillante.mx
---------------------------------------------------
*/


define('ROOT_PATH', './');
include(ROOT_PATH.'global.php');
require(ROOT_PATH.'includes/sessions.php');
include(ROOT_PATH.'includes/page_header.php');

//Verificar si ya inicio sesión el cliente
if ($user_info['tipo_cuenta'] == "doctor") {
	$plantilla = "index";
}
else{
	$plantilla = 'login';
}


$site_template->print_template($site_template->analizar_plantilla($plantilla));
?>
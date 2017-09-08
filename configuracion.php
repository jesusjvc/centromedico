<?php
/* 
---------------------------------------------------
Ajustes del sistema
---------------------------------------------------
Consultorio 1.0
Desarrollado por: Esbrillante Estudio Digital
https://esbrillante.mx
---------------------------------------------------
*/

// -------------------------------------------------
// Configuración de la base de datos
// -------------------------------------------------

$db_servertype = "mysql";
$db_port = 3306;
$db_host = "localhost";
$db_name = "admin_consultorio";
$db_user = "admin_jesusjvc";
$db_password = "JGIflZyV1e";
$db_charset ="utf8";
$table_prefix = "";

// -------------------------------------------------
// Plantilla HTML del Sistema
// -------------------------------------------------

$tema_actual = "consultorio";

// -------------------------------------------------
// Prefijo para las contraseñas
// -------------------------------------------------

$salt = '#EsBrillanteMX#';

// -------------------------------------------------
// Configuración de servidor de email
// -------------------------------------------------

$servidor_smtp = "air2.jetthost.net";
$port_smtp = 26;
$usermail_name = "sistema@cerofilas.com.mx";
$usermail_pass = "esbrillante2015#";


$config['site_name'] = "CeroFilas";

// -------------------------------------------------
// Configuración de subida de imágenes
// -------------------------------------------------
$config['max_thumb_width'] = 400;
$config['max_thumb_height'] = 400;
$config['max_thumb_size'] = 20;
$config['max_media_size'] = 20;
$config['upload_mode'] = 2;
?>
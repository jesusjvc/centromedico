<?php
/* 
---------------------------------------------------
Header
---------------------------------------------------
Consultorio 1.0
Desarrollado por: Esbrillante Estudio Digital
https://esbrillante.mx
---------------------------------------------------
*/

if (!defined('ROOT_PATH')) die("Hijo, que estás haciendo");

//-----------------------------------------------------
//--- Register Global Vars ----------------------------
//-----------------------------------------------------
$idioma = "spanish";

$site_template->register_vars(array(
    "template_url" => TEMPLATE_PATH,
    "template_image_url" => TEMPLATE_PATH . "/images",    
    "tagbase" => obtenURL(),
    "template_lang_image_url" => TEMPLATE_PATH . "/images_" . $idioma,

));


if (!empty($additional_urls)) {
    $register_array = array();
    foreach ($additional_urls as $key => $val) {
        $register_array[$key] = $site_sess->url($val);
    }
    $site_template->register_vars($register_array);
}

// Replace Globals in $lang
$lang = $site_template->parse_array($lang);


//-----------------------------------------------------
//--- User Box ----------------------------------------
//-----------------------------------------------------

if ($user_info['tipo_cuenta'] == "doctor") {

    $nivel = "Cuenta de Doctor";
    
    //Avatar el usuario

   $avatar = "resize-image.php?url=./fotos-usuarios/".$user_info['usuario'].".jpg&width=200&i=1";
    $site_template->register_vars(array(
        "nombre_usuario" => $user_info['nombre'],
        "avatar" => $avatar,
        "apellidos_usuario" => $user_info['apellidos'],
        "email" => $user_info['email'],
        "usuario" => $user_info['usuario'],
        "id_user" => $user_info['id_user'],
        "nivel" => $nivel
    ));

   
    $site_template->register_vars(array(
        "top_navigation" =>$site_template->analizar_plantilla("top-navigation"),
        "sidebar" =>$site_template->analizar_plantilla("sidebar"),
        "footer" =>$site_template->analizar_plantilla("footer"),
        "user_loggedin" => 1,      
        "user_loggedout" => 0,
        "boton_panel" => ($user_info['tipo_cuenta'] == ADMIN) ? 1 : 0,
        "is_admin" => ($user_info['tipo_cuenta'] == ADMIN) ? 1 : 0
    ));
    unset($user_box);
} else {
    //$user_box = $site_template->analizar_plantilla("user-form-login");
    $site_template->register_vars(array(
        //"user_box" => $user_box,
        "user_loggedin" => 0,
        "user_loggedout" => 1,
        "boton_panel" => 0,
        "is_admin" => 0
    ));
    $site_template->un_register_vars("user-form-login");
    unset($user_box);
}
?>
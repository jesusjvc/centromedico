<?php
/* 
---------------------------------------------------
Recuperar Contraseña
---------------------------------------------------
Desarrollo por: Esbrillante.mx
---------------------------------------------------
*/

$plantilla = 'recoverpw';
define('ROOT_PATH', './');
include(ROOT_PATH.'global.php');
require(ROOT_PATH.'includes/sessions.php');
include(ROOT_PATH.'includes/page_header.php');


if ($action  == "updatepassword") {
	$plantilla = "login";

	$usuario = $_POST['usuario'];
	$email = $_POST['email'];
	$password = $_POST['password'];

	$password =  crypt($password, $salt);

	 $sql = "UPDATE clientes
            SET password = '$password'
            WHERE email = '$email'";
   if ($rs = $db->run($sql)){        
       $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> ¡Listo tu contraseña se actualizó! Puedes ahora iniciar sesión</div>';     
   }
    else{
      $msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un problema al procesar tu solicitud.</div>';
    }

    $site_template->register_vars(array(
				"msg" => $msg				
			));
}


if ($action == "resetpassword") {
	$plantilla = 'resetpassword';

	$usuario = $_GET['usuario'];
	$email = $_GET['email'];

	$site_template->register_vars(array(
				"usuario" => $usuario,
				"email" => $email				
			));
}

if ($action == "recuperar") {
	$email = $_POST['email'];

	//verificar que exista el usuario

	$sql = "SELECT usuario, email, nombre FROM clientes WHERE email= '$email'";
	if (!$rs = $db->run($sql)) {
			$error = '<div class="alert alert-danger"><strong>Oh no!</strong> El usuario no existe.</div>';
			$site_template->register_vars(array(
				"msg" => $error				
			));
		}
		else{		
		$usuario = $rs[0]['usuario'];
		$nombre = $rs[0]['nombre'];
	// Generar Token
			
	//Enviar Email

	require 'includes/PHPMailerAutoload.php';   
					$url= 'http://'.$_SERVER['SERVER_NAME']. dirname($_SERVER['REQUEST_URI'])."/recovery.php?action=resetpassword&usuario=$usuario&email=$email&tipo=cliente";
				    $message = file_get_contents('email-templates/recuperar-password.html'); 

				    $message = str_replace('%email%', $email, $message);
				    $message = str_replace('%nombre%', $nombre, $message);
				    $message = str_replace('%url%', $url, $message);

				    $mail = new PHPMailer;
				    $mail->isSMTP();                                      // Set mailer to use SMTP
				    //$mail->SMTPDebug = 0;
				    $mail->Host = $servidor_smtp;                        // Specify main and backup server
				    $mail->SMTPAuth = true;
				    $mail->Port = $port_smtp;
				    $mail->Username = $usermail_name;                // SMTP username
				    $mail->Password = $usermail_pass;                 // SMTP password
				    $mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

				    $mail->From = 'sistema@esbrillante.mx';
				    $mail->FromName = 'CeroFilas';
				    $mail->AddAddress($email, $nombre);  // Add a recipient
				    $mail->CharSet = 'UTF-8';
				    $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
				    $mail->isHTML(true);                                  // Set email format to HTML

				    $mail->Subject = 'Recupera contraseña';
				    $mail->Body    = $message;
				    $mail->AltBody = $message;

				   //send the message, check for errors
					if (!$mail->send()) { $msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un problema al intentar enviarte un correo.</div>'; } 
					else {$msg = '<div class="alert alert-success"><strong>¡Listo!</strong> Te hemos enviado un correo electrónico para cambiar tu contraseña.</div>'; }

					
					$site_template->register_vars(array(
						"msg" => $msg
					));
}

}
//----------------------------------------
// Imprimir página
//----------------------------------------
$site_template->register_vars(array(

));
$site_template->print_template($site_template->analizar_plantilla($plantilla));
?>
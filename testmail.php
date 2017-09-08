<?php 
define('ROOT_PATH', './');
include(ROOT_PATH.'global.php');


$email = "jesus.jvc@gmail.com";
$nombre =  "jesus";
$usuario = "jesusjvc";
require 'includes/PHPMailerAutoload.php';   
					$url= 'http://'.$_SERVER['SERVER_NAME']. dirname($_SERVER['REQUEST_URI'])."/confirmar-registro.php?usuario=$usuario&email=$email";
				    $message = file_get_contents('email-templates/confirmar-email.html'); 

				    $message = str_replace('%email%', $email, $message);
				    $message = str_replace('%nombre%', $nombre, $message);
				    $message = str_replace('%url%', $url, $message);

				    $mail = new PHPMailer;
				    $mail->isSMTP();                                      // Set mailer to use SMTP
				    $mail->SMTPDebug = 2;
				    $mail->Host = 'air2.jetthost.net';                    // Specify main and backup server
				    $mail->SMTPAuth = true;
				    $mail->Port = 26;
				    $mail->Username = 'sistema@cerofilas.com.mx';                // SMTP username
				    $mail->Password = 'esbrillante2015#';                 // SMTP password
				    $mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

				    $mail->From = 'sistema@esbrillante.mx';
				    $mail->FromName = 'CeroFilas';
				    $mail->AddAddress($email, $nombre);  // Add a recipient
				    $mail->CharSet = 'UTF-8';
				    $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
				    $mail->isHTML(true);                                  // Set email format to HTML

				    $mail->Subject = 'Confirma tu correo electrónico';
				    $mail->Body    = $message;
				    //$mail->AltBody = $message;

				   //send the message, check for errors
					if (!$mail->send()) { $msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un problema al intentar enviarte un correo.</div>'; } 
					else {$msg = '<div class="alert alert-success"><strong>¡Listo!</strong> Te hemos enviado un correo electrónico para verificar tu cuenta.</div>'; }

					echo $msg;

 ?>
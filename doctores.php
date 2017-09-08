<?php
/* 
---------------------------------------------------
Página de doctores
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

	if ($action == "nuevo"){
		$plantilla = "doctores-nuevo";
	}

	elseif ($action == "agregar_usuario") {
		//Agregar doctor
		$nombre = $_POST['nombre'];
		$apellidos = $_POST['apellidos'];
		$cargo = $_POST['cargo'];
		$sexo = $_POST['sexo'];

		$date = explode('/', $_POST['fecha_nacimiento']);
		$time = mktime(0,0,0,$date[0],$date[1],$date[2]);
		$fecha_nacimiento = date( 'Y-m-d', $time );

		$usuario = $_POST['usuario'];
		$password = $_POST['password'];
		$tipo_cuenta = $_POST['tipo_cuenta'];

		$calle = $_POST['calle'];
		$numero = $_POST['numero'];
		$colonia = $_POST['colonia'];
		$cp = $_POST['cp'];
		$ciudad = $_POST['ciudad'];
		$estado = $_POST['estado'];

		
		$tel_casa = $_POST['tel_casa'];
		$tel_oficina = $_POST['tel_oficina'];
		$tel_celular = $_POST['tel_celular'];
		$email = $_POST['email'];
		
		//validar que el usuario o cuenta de email no exista

if (!empty($usuario) && !empty($password)) {
	$plantilla = "doctores-nuevo";
	//verificar que no exista el usuario o email

	$sql = "SELECT usuario, email FROM usuarios WHERE usuario= '$usuario'";
	$sql2 = "SELECT usuario, email FROM usuarios WHERE email= '$email'";
	if ($rs = $db->run($sql) || $rs2 = $db->run($sql2)) {
			
			$error = '<div class="alert alert-danger"><strong>¡Ocurrió un problema!</strong> El usuario o email que quieres utilizar ya existe.</div>';
			
			$site_template->register_vars(array(
				"msg" => $error,
				"usuario" => $usuario,
				"email" => $email,
				"nombre_usuario" => $nombre,
				"apellidos_usuario" => $apellidos,
				"tel_casa" => $tel_casa,
				"tel_oficina" => $tel_oficina,
				"tel_celular" => $tel_celular
			));
		}
		else{
			$password =  crypt($password, $salt);

			//subir foto de perfil
		//  $foo = new Upload($_FILES['fotografia']); 
		// 		if ($foo->uploaded) {
		  
		 
		//    // save uploaded image with a new name,
		//    // resized to 100px wide
		//    $foo->file_new_name_body = $usuario;
		//    $foo->image_resize = false;
		//    $foo->file_overwrite = true;
		//    //$foo->image_convert = gif;
		//    $foo->image_x = 600;
		//    $foo->image_ratio_y = true;
		//    $foo->Process('fileros/fotos');
		//    if ($foo->processed) {		     
		//      $foo->Clean();
		//      //exit();
		//    } else {
		//      //echo 'error : ' . $foo->error;
		//    } 
		// } 


			$sql = "INSERT INTO usuarios (nombre, apellidos, sexo, fecha_nacimiento, usuario, password, calle, numero, colonia, cp, ciudad, estado, tel_casa, tel_oficina, tel_celular, email, cargo, tipo_cuenta) 
			VALUES ('$nombre', '$apellidos', '$sexo', '$fecha_nacimiento', '$usuario', '$password', '$calle', '$numero', '$colonia', '$cp', '$ciudad', '$estado', '$tel_casa', '$tel_oficina', '$tel_celular', '$email', '$cargo', '$tipo_cuenta')";
			
			if ($rs = $db->run($sql)) {		   		   
	   		   $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> Hemos registrado un nuevo usuario del sistema</div>';				
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un al procesar tu solicitud</div>';	
			}

			$plantilla = "doctores";
			$site_template->register_vars(array(
						"msg" => $msg
			));	
		}

}

	}
	// -----------------------------------
	// Editar información del usuario
	// -----------------------------------
	elseif ($action == "editar_informacion" && !empty($_GET['id_user'])) {
		$plantilla = 'doctores-editar';

		$id_user = $_GET['id_user'];
	
		$usuario = $db->select("usuarios", "id_user = '$id_user'");

		if ($usuario) {
			foreach ($usuario as $key => $value) {
				
				//Fix Date
				$phpdate = strtotime( $value['fecha_nacimiento'] );
				$mysqldate = date( 'dmY', $phpdate );
				$value['fecha_nacimiento'] = $mysqldate;		
				$site_template->register_vars($value);
				
				
			}
		}

	}
	// -----------------------------------
	// Agregar horario del doctor
	// -----------------------------------
	elseif ($action == "agregar_horario") {
		$plantilla = 'doctores-editar';
		
		$id_user = $_POST['id_user'];
		$hora_inicio = $_POST['hora_inicio'];
		// $hora_fin = $_POST['hora_fin'];
		$tipo_horario = $_POST['tipo_horario'];

		$sql = "INSERT INTO horarios (id_user, hora_inicio , tipo_horario) 
			VALUES ('$id_user', '$hora_inicio', '$tipo_horario')";
			
			if ($rs = $db->run($sql)) {		   		   
	   		   $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> Se agregó el horario</div>';				
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un error al procesar tu solicitud</div>';	
			}

		

			//echo $msg;

			$sql = "SELECT * FROM horarios  WHERE id_user = '$id_user' ORDER BY hora_inicio ASC";
				if ($rs = $db->run($sql)){		  
				$lista_horarios = ' ';
				$ficha = 1;                        
				   foreach ($rs as $row) {
				   		$lista_horarios .='<tr class="even pointer"><td class="a-center ">
                              <input type="checkbox" class="flat" name="table_records">
                            </td>
                            <td class=" ">'.$ficha.'</td>
                            <td class=" ">'.$row['hora_inicio'].'</td>
                            <td class=" ">'.$row['tipo_horario'].'</td>
                            <td class=" "><a class="eliminar" href="#">Eliminar</a></td>                        
                            </td>
                          </tr>';
                          $ficha++;
				   }
				
				echo $lista_horarios;				   

				}

			exit();
	}
	else{
		//----------------------------------
		// Sección principal de doctores
		//-----------------------------------
		$plantilla = "doctores";

		$sql = "SELECT * FROM usuarios ORDER BY id_user DESC";
		if ($rs = $db->run($sql)){		  
		$lista_doctores = "";
		   foreach ($rs as $row) {
				
				$lista_doctores .= '<div class="col-md-4 col-sm-4 col-xs-12 profile_details">
                        <div class="well profile_view">
                          <div class="col-sm-12">
                            <h4 class="brief"><i>'.$row['cargo'].'</i></h4>
                            <div class="left col-xs-7">
                              <h2>'.$row['nombre'].' '.$row['apellidos'].'</h2>
                              
                              <ul class="list-unstyled">
                              <li>Usuario: '.$row['usuario'].'</li>
                              <li><i class="fa fa-phone"></i> Celular: '.$row['tel_celular'].' </li>
                              <li><i class="fa fa-user"></i> Tipo de cuenta: '.$row['tipo_cuenta'].' </li>
                              </ul>
                            </div>
                            <div class="right col-xs-5 text-center">
                              <img src="'.TEMPLATE_PATH.'/images/user.png" alt="" class="img-circle img-responsive">
                            </div>
                          </div>
                          <div class="col-xs-12 bottom text-center">
                            
                            <div class="col-xs-12 col-sm-12 emphasis">                              
                              <a href="doctores.php?action=editar_informacion&id_user='.$row['id_user'].'" class="btn btn-primary btn-xs">
                                <i class="fa fa-user"> </i> Editar información
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>';		   	
		   }

		   $site_template->register_vars(array(
				"lista_doctores" => $lista_doctores
			));
		}

	}
}
else{
	$plantilla = 'login';
}


$site_template->print_template($site_template->analizar_plantilla($plantilla));
?>
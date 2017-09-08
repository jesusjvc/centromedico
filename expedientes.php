<?php
/* 
---------------------------------------------------
Modulo de expedientes
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
		$plantilla = "expediente-nuevo";
	}
	// --------------------------------
	// Nuevo tratamiento
	// --------------------------------
	elseif ($action == "nuevo_tratamiento"  && !empty($_GET['id_expediente'])) {
		$plantilla = "expediente_nuevo_tratamiento";

		$id_expediente = $_GET['id_expediente'];
	
		$expediente = $db->select("expedientes", "id_expediente = '$id_expediente'");

		if ($expediente) {
			foreach ($expediente as $key => $value) {				
				//Fix Date
				$phpdate = strtotime( $value['fecha_nacimiento'] );
				$mysqldate = date( 'dmY', $phpdate );
				$value['fecha_nacimiento'] = $mysqldate;		
				$site_template->register_vars($value);				
			}
		}

		// Cargar los programas o tratamientos

		$sql = "SELECT * FROM programas ORDER BY id_tratamiento ASC";
		if ($rs = $db->run($sql)){
		$programas = "";		  
		$lista_tratamientos = "";
		   foreach ($rs as $row) {
				
				// Leer medicamentos del tratamiento
		   		$id_tratamiento = $row['id_tratamiento'];

		   		$query = "SELECT id_medicamento FROM programas_relaciones WHERE id_tratamiento = '$id_tratamiento'";
		   		
		   			$medicamentos = " ";
		   			if ($result = $db->run($query)){
		   				
		   				foreach ($result as $columna => $valor) {			   			 	
			   			 	$id_medicamento = $valor['id_medicamento'];

			   			 	//Obtener los nombres
			   			 	$consulta = "SELECT nombre FROM medicamentos WHERE id_medicamento = '$id_medicamento'";
			   			 	$resultado = $db->run($consulta, "", true);
			   			 	$medicamentos .= "<li>".strtoupper($resultado['nombre'])."</li>";

			   				}
			   			}
			   			$programas .= "<option value='".$row['id_tratamiento']."'>".$row['nombre']."</option>";
			   			$lista_tratamientos .= '<div class="col-md-4 col-sm-4 col-xs-12 profile_details">
                        <div class="well profile_view">
                          <div class="col-sm-12">
                            <h2 class="brief"><i>'.$row['id_tratamiento'].') '.strtoupper($row['nombre']).'</i></h2>
                            <div class="left col-xs-12">                          
                              
                              <ul class="list-unstyled">'.$medicamentos.'</ul>
                            </div>                           
                          </div>
                          <div class="col-xs-12 bottom text-center">                          
                            <div class="col-xs-12 col-sm-12 emphasis">                              
                            
                              <a href="tratamientos.php?action=editar_tratamiento&id_tratamiento='.$row['id_tratamiento'].'" class="btn btn-primary btn-xs">
                                <i class="fa fa-edit"> </i> Editar tratamiento
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>';		   	
		   			

				
		   }

		   // Listado de usuarios

		   $sql = "SELECT * FROM usuarios WHERE tipo_cuenta = 'doctor' ORDER BY id_user DESC";
				if ($rs = $db->run($sql)){		  
				$lista_doctores = "";
				   foreach ($rs as $row) {
				   	if ($user_info['id_user'] == $row['id_user']) {
				   		$selected = "selected";
				   	}
				   	else
				   		$selected = "";
				   	$lista_doctores .= '<option  value="'.$row['id_user'].'" '.$selected.'>'.$row['nombre'].' '.$row['apellidos'].'</option>';
				   }
				}

				if (isset($_GET['msg'])) {
					$msg = $_GET['msg'];
				if ($msg == "true") {		
				 $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> Se agregó correctamente el tratamiento Homeopático</div>';				
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un al procesar tu solicitud</div>';	
			}
		}
		   $site_template->register_vars(array(
		   		"msg" => $msg,
		   		"programas" => $programas,
		   		"lista_doctores" => $lista_doctores,
				"lista_tratamientos" => $lista_tratamientos
			));
		}

	}
	// --------------------------------
	// Formulario nueva historia clinica al paciente
	// --------------------------------
	elseif ($action == "nuevo_hc"  && !empty($_GET['id_expediente'])) {
		$plantilla = "expediente_nuevo_hc";

		$id_expediente = $_GET['id_expediente'];
	
		$expediente = $db->select("expedientes", "id_expediente = '$id_expediente'");

		if ($expediente) {
			foreach ($expediente as $key => $value) {				
				//Fix Date
				$phpdate = strtotime( $value['fecha_nacimiento'] );
				$mysqldate = date( 'dmY', $phpdate );
				$value['fecha_nacimiento'] = $mysqldate;		
				$site_template->register_vars($value);				
			}
		}

	}
	// --------------------------------
	// Agregar nueva historia clinica al paciente
	// --------------------------------
	elseif ($action == "agregar_hc"  && !empty($_POST['id_expediente'])) {
		$plantilla = "expediente_nuevo_hc";
		$id_expediente = $_POST['id_expediente'];
		$tipo = $_POST['tipo'];
		$descripcion = $_POST['descripcion'];
		$observaciones = $_POST['observaciones'];

		$sql = "INSERT INTO historia_clinica (id_expediente, tipo, descripcion, observaciones) 
			VALUES ('$id_expediente', '$tipo', '$descripcion', '$observaciones')";
			
			if ($rs = $db->run($sql)) {	

	   		   redirect("expedientes.php?action=mostrar_expediente&id_expediente=$id_expediente&msg=true");		
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un al procesar tu solicitud</div>';	
			}

			


	}
	// --------------------------------
	// Agregar nuevo expediente
	// --------------------------------
	elseif ($action == "agregar_expediente") {
		
		//información personal
		$nombre = $_POST['nombre'];
		$apellidos = $_POST['apellidos'];
		$responsable = $_POST['responsable'];
		$ocupacion = $_POST['ocupacion'];
		$religion = $_POST['religion'];
		$signo_zodiacal = $_POST['signo_zodiacal'];
		$lugar_nacimiento = $_POST['lugar_nacimiento'];
		$date = explode('/', $_POST['fecha_nacimiento']);
		$time = mktime(0,0,0,$date[0],$date[1],$date[2]);
		$fecha_nacimiento = date( 'Y-m-d', $time );
		$sexo = $_POST['sexo'];
		$peso = $_POST['peso'];

		//dirección
		$calle = $_POST['calle'];
		$numero = $_POST['numero'];
		$colonia = $_POST['colonia'];
		$cp = $_POST['cp'];
		$ciudad = $_POST['ciudad'];
		$estado = $_POST['estado'];

		//datos de contacto
		$tel_casa = $_POST['tel_casa'];
		$tel_oficina = $_POST['tel_oficina'];
		$tel_celular = $_POST['tel_celular'];
		$email = $_POST['email'];

		$sql = "INSERT INTO expedientes (nombre, apellidos, responsable, ocupacion, religion, signo_zodiacal, lugar_nacimiento, fecha_nacimiento, sexo, peso, calle, numero, colonia, cp, ciudad, estado, tel_casa, tel_oficina, tel_celular, email) 
			VALUES ('$nombre', '$apellidos', '$responsable', '$ocupacion', '$religion', '$lugar_nacimiento', '$signo_zodiacal', '$fecha_nacimiento', '$sexo', '$peso', '$calle', '$numero', '$colonia', '$cp', '$ciudad', '$estado', '$tel_casa', '$tel_oficina', '$tel_celular', '$email')";
			
			if ($rs = $db->run($sql)) {		   		   
	   		   $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> El expediente se ha dado de alta correctamente en el sistema</div>';				
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un al procesar tu solicitud</div>';	
			}

			$plantilla = "expedientes";
			$site_template->register_vars(array(
						"msg" => $msg
			));	


	}
	// -----------------------------------
	// Mostrar expediente
	// -----------------------------------
	elseif ($action == "mostrar_expediente" && !empty($_GET['id_expediente'])) {
		$plantilla = "expediente_mostrar";
		$id_expediente = $_GET['id_expediente'];

		if (isset($_GET['msg'])) {
			$msg = $_GET['msg'];
				if ($msg == "true") {
					 $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> El expediente se ha actualizado</div>';
				}
			}

		$expediente = $db->select("expedientes", "id_expediente = '$id_expediente'");

		if ($expediente) {
			foreach ($expediente as $key => $value) {				
				//Fix Date
				$phpdate = strtotime( $value['fecha_nacimiento'] );
				$mysqldate = date( 'dmY', $phpdate );
				$value['fecha_nacimiento'] = $mysqldate;		
				$site_template->register_vars($value);				
			}

			// Edad
			$edad = calcular_edad(date( 'd-m-Y', $phpdate));

			//sexo

			if ($value['fecha_nacimiento'] == "M") {
				$sexo = "Masculino";
				$icon_sexo = "fa-male";
			}
			else{
				$sexo = "Femenino";
				$icon_sexo = "fa-female";
			}

	
		
			

			// Cargar historia clinica
			$historiaClinica = $db->select("historia_clinica", "id_expediente = '$id_expediente'");

			$hc = "";
			 foreach ($historiaClinica as $row) {
			 	if (!empty($row['observaciones'])) {
			 		$observaciones = '<p>
									Observaciones: '.$row['observaciones'].'
                                  </p>';
			 	}
			 	else{
			 		$observaciones = "";
			 	}
			 	$hc .= ' <li>                            
                                <div class="message_date">
                                  <!--<h3 class="date text-info">24</h3>
                                  <p class="month">May</p> -->
                                </div>
                                <div class="message_wrapper">
                                  <h4 class="heading">'.$row['tipo'].'</h4>
                                  <blockquote class="message">'.$row['descripcion'].'</blockquote>
                                  
                                '.$observaciones.'
                                </div>
                              </li>  ';
			 }
		}

		$site_template->register_vars(array(				
				"edad" => $edad,
				"sexo" => $sexo,
				"icon_sexo" => $icon_sexo,
				"msg" => $msg,
				"hc" => $hc
			));

	}
	// -----------------------------------
	// Editar expediente
	// -----------------------------------
	elseif ($action == "editar_expediente" && !empty($_GET['id_expediente'])) {
		$plantilla = "expediente_editar";
		$id_expediente = $_GET['id_expediente'];
	
		$expdiente = $db->select("expedientes", "id_expediente = '$id_expediente'");

		if ($expdiente) {
			foreach ($expdiente as $key => $value) {				
				//Fix Date
				$phpdate = strtotime( $value['fecha_nacimiento'] );
				$mysqldate = date( 'dmY', $phpdate );
				$value['fecha_nacimiento'] = $mysqldate;		
				$site_template->register_vars($value);				
			}
		}
	}
	// -----------------------------------
	// Actualizar expediente
	// -----------------------------------
	elseif ($action == "actualizar_expediente" && !empty($_POST['id_expediente'])) {
		//información personal		
		$fecha_nacimiento = $_POST['fecha_nacimiento'];
		$fecha = explode('/', $fecha_nacimiento);
		$fecha_nacimiento = $fecha[2]."/".$fecha[1]."/".$fecha[0];
		//Actualizar datos
		$update = array(
		  	'nombre' => $_POST['nombre'],
			'apellidos' => $_POST['apellidos'],
			'responsable' => $_POST['responsable'],
			'ocupacion' => $_POST['ocupacion'],
			'religion' => $_POST['religion'],
			'signo_zodiacal' => $_POST['signo_zodiacal'],
			'lugar_nacimiento' => $_POST['lugar_nacimiento'],
			'fecha_nacimiento' => $fecha_nacimiento,
			'sexo' => $_POST['sexo'],
			'peso' => $_POST['peso'],
			'calle' => $_POST['calle'],
			'numero' => $_POST['numero'],
			'colonia' => $_POST['colonia'],
			'cp' => $_POST['cp'],
			'ciudad' => $_POST['ciudad'],
			'estado' => $_POST['estado'],
			'tel_casa' => $_POST['tel_casa'],
			'tel_oficina' => $_POST['tel_oficina'],
			'tel_celular' => $_POST['tel_celular'],
			'email' => $_POST['email']
		);

		$id_expediente = $_POST['id_expediente'];
		

		if ($rs = $db->update("expedientes", $update, "id_expediente = '$id_expediente'")) {		   		   
	   		   $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> El expediente se ha actualizado correctamente en el sistema</div>';

  				$expediente = $db->select("expedientes", "id_expediente = '$id_expediente'");
				
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un al procesar tu solicitud</div>';
				$expediente = $db->select("expedientes", "id_expediente = '$id_expediente'");
	
			}

		$site_template->register_vars(array(
						"msg" => $msg
			));	

		$plantilla = "expediente_editar";
			

		if ($expediente) {
			foreach ($expediente as $key => $value) {	
				//Fix Date
				$phpdate = strtotime( $value['fecha_nacimiento'] );
				$mysqldate = date( 'dmY', $phpdate );
				$value['fecha_nacimiento'] = $mysqldate;		
				$site_template->register_vars($value);
			}
		}
	}
	// -----------------------------------
	// Agregar tratamiento Homeopatico
	// -----------------------------------
	elseif ($action == "agregar_tratamiento_homeopatico" && !empty($_POST['id_expediente'])) {
		
		$id_expediente = $_POST['id_expediente'];

		$dosis = $_POST['dosis'];
		$antes_despues = (isset($_POST['antes_despues']) ? boolval($_POST['antes_despues']) : false);
		$botes_grandes = (isset($_POST['botes_grandes']) ? boolval($_POST['botes_grandes']) : false);
		$tratamiento_doble = (isset($_POST['tratamiento_doble']) ? boolval($_POST['tratamiento_doble']) : false);
		$sin_alcohol = (isset($_POST['sin_alcohol']) ? boolval($_POST['sin_alcohol']) : false);
		$mitad_alcohol = (isset($_POST['mitad_alcohol']) ? boolval($_POST['mitad_alcohol']) :false);
		$imprimir_receta_medicamentos = (isset($_POST['imprimir_receta_medicamentos']) ? boolval($_POST['imprimir_receta_medicamentos']) : false);
		$imprimir_receta_general = (isset($_POST['imprimir_receta_general']) ? boolval($_POST['imprimir_receta_general']) : false);
		$sin_rx = (isset($_POST['sin_rx']) ? boolval($_POST['sin_rx']) : false);
		$notas = $_POST['notas'];
		$id_user = $_POST['doctor_asignado'];
		$valor = $_POST['valor'];

		$medicamentos = $_POST['medicamentos'];
		$medicamentos = json_encode($medicamentos);

		$sql = "INSERT INTO tratamiento_homeopatico (id_user, id_expediente, dosis, antes_despues, botes_grandes, tratamiento_doble, mitad_alcohol, sin_alcohol, notas, imprimir_receta_medicamentos, imprimir_receta_general, sin_rx, valor, medicamentos) 
			VALUES ('$id_user', '$id_expediente', '$dosis', '$antes_despues', '$botes_grandes', '$tratamiento_doble', '$mitad_alcohol', '$sin_alcohol', '$notas', '$imprimir_receta_medicamentos', '$imprimir_receta_general', '$sin_rx', '$valor', '$medicamentos')";
			
			if ($rs = $db->run($sql)) {		   		   
	   		   $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> Se agregó correctamente el tratamiento Homeopático</div>';				
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un al procesar tu solicitud</div>';	
			}

		$plantilla = "expediente_nuevo_tratamiento";	
		
		redirect("expedientes.php?action=nuevo_tratamiento&id_expediente=$id_expediente&msg=true");	

	}
	elseif ($action == "buscar_expediente" || empty($action)) {
		
	 	//----------------------------------
		// Expedientes
		//-----------------------------------
		$plantilla = "expedientes";
		$total_expedientes = 0;
		$totalM = 0;
		$totalF = 0;
		if (!empty($_GET['busqueda'])) {
			$busqueda = $_GET['busqueda'];
			$sql = "SELECT * FROM expedientes WHERE nombre = '$busqueda' ORDER BY id_expediente DESC";
		}
		else{
			$sql = "SELECT *, count(*) as total FROM expedientes ORDER BY id_expediente DESC";
		}
		
		$rs = $db->run($sql);		
		if (!empty($rs)){		  
		$lista_expedientes = "";	


		   foreach ($rs as $row) {
				
			           $lista_expedientes .= '<tr>
                          <td>'.$row['id_expediente'].'</td>
                          <td>'.$row['nombre'].'</td>
                          <td>'.$row['apellidos'].'</td>
                          <td>'.$row['tel_celular'].'</td>
                          <td>'.$row['tel_casa'].'</td>
                          <td>'.$row['ocupacion'].'</td>                                                 
                          <td></td>
                          <td><a href="expedientes.php?action=mostrar_expediente&id_expediente='.$row['id_expediente'].'" class="btn btn-success btn-xs">
                                <i class="fa fa-eye"> </i> Ver expediente
                              </a></td>
                           <td> <a href="expedientes.php?action=editar_expediente&id_expediente='.$row['id_expediente'].'" class="btn btn-primary btn-xs">
                                <i class="fa fa-edit"> </i> Editar expediente
                              </a></td>
                          </tr>';

                      // Contadores

                      $total_expedientes++;
                     
                      if ($row['sexo'] == "F") {
                       		$totalF++;
                      }
                      elseif ($row['sexo'] == "M") {
                       	 $totalM++;
                      } 	
		   }

		   }

		   else{
		   		$lista_expedientes = '<div class="alert alert-warning">No hay resultados que mostrar</div>';
		   }

		   
		  

		   $site_template->register_vars(array(
		   		"total_expedientes" => $total_expedientes,
		   		"total_hombres" => $totalM,
		   		"total_mujeres" => $totalF,
				"lista_expedientes" => $lista_expedientes
			));
		
	}
}
else{
	$plantilla = 'login';
}


$site_template->print_template($site_template->analizar_plantilla($plantilla));
?>
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

	// -------------------------------------------
	// Medicamentos
	// --------------------------------------------

	if ($action == "nuevo"){
		$plantilla = "medicamentos-nuevo";
	}
	elseif ($action == "agregar_medicamento") {
		//Agregar nuevo medicamento
		$nombre = $_POST['nombre_medicamento'];
		$descripcion = $_POST['descripcion'];	
	
	if (!empty($nombre)) {
	$plantilla = "medicamentos-nuevo";
	//verificar que no exista el usuario o email

	$sql = "SELECT nombre, descripcion FROM medicamentos WHERE nombre= '$nombre'";
	if ($rs = $db->run($sql)) {
			
			$error = '<div class="alert alert-danger"><strong>¡Ocurrió un problema!</strong> El medicamento que intentas agregar ya existe.</div>';
			
			$site_template->register_vars(array(
				"msg" => $error,				
				"descripcion" => $descripcion,				
			));
		}
		else{	


			$sql = "INSERT INTO medicamentos (nombre, descripcion) 
			VALUES ('$nombre', '$descripcion')";
			
			if ($rs = $db->run($sql)) {		   		   
	   		   $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> Medicamento agregado a la lista</div>';				
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un al procesar tu solicitud</div>';	
			}

			$plantilla = "medicamentos-nuevo";
			$site_template->register_vars(array(
						"msg" => $msg
			));	
		}

}

	}
	// -----------------------------------
	// Editar información del medicamento
	// -----------------------------------
	elseif ($action == "editar_medicamento" && !empty($_GET['id_medicamento'])) {
		$plantilla = 'medicamentos-editar';

		$id_medicamento = $_GET['id_medicamento'];
	
		$medicamentos = $db->select("medicamentos", "id_medicamento = '$id_medicamento'");

		if ($medicamentos) {
			foreach ($medicamentos as $key => $value) {						
				$site_template->register_vars($value);	
			}
		}

	}
	// -----------------------------------
	// Actualizar información del medicamento
	// -----------------------------------
	elseif ($action =="actualizar_medicamento" && !empty($_POST['id_medicamento'])) {
		$id_medicamento = $_POST['id_medicamento'];		
		$descripcion = $_POST['descripcion'];

		$update = array(
		  	'nombre' => $_POST['nombre_medicamento'],
			'descripcion' => $_POST['descripcion']
			);
		if ($rs = $db->update("medicamentos", $update, "id_medicamento = '$id_medicamento'")) {		   		   
	   		   $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> El expediente se ha actualizado correctamente en el sistema</div>';

  				$medicamentos = $db->select("medicamentos", "id_medicamento = '$id_medicamento'");
				
				}
			else{
				$msg = '<div class="alert alert-danger"><strong>¡oh no!</strong> Ocurrió un al procesar tu solicitud</div>';
				$medicamentos = $db->select("medicamentos", "id_medicamento = '$id_medicamento'");
	
			}

		if ($medicamentos) {
			foreach ($medicamentos as $key => $value) {						
				$site_template->register_vars($value);	
			}
		}

		$site_template->register_vars(array(
						"msg" => $msg
			));	

		$plantilla = 'medicamentos-editar';

	}
	elseif ($action == "ver_medicamentos") {
		//----------------------------------
		// Sección principal medicamentos
		//-----------------------------------
		$plantilla = "medicamentos";

		$sql = "SELECT * FROM medicamentos ORDER BY nombre DESC";
		if ($rs = $db->run($sql)){		  
		$lista_medicamentos = "";
		   foreach ($rs as $row) {
				
				$lista_medicamentos .= '<tr>
                          <td><a href="tratamientos.php?action=editar_medicamento&id_medicamento='.$row['id_medicamento'].'"><strong>'.$row['nombre'].'</strong></a></td>
                          <td>'.$row['descripcion'].'</td>                       
                        </tr> ';		   	
		   }

		   $site_template->register_vars(array(
				"lista_medicamentos" => $lista_medicamentos
			));
		}

	}
	// -------------------------------
	// Ver tratamientos
	// -------------------------------
	elseif ($action == "ver_tratamientos") {
		$plantilla = "tratamientos";

		$sql = "SELECT * FROM programas ORDER BY id_tratamiento ASC";
		if ($rs = $db->run($sql)){		  
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

		   $site_template->register_vars(array(
				"lista_tratamientos" => $lista_tratamientos
			));
		}
	}
	// -------------------------------
	// Nuevo tratamiento
	// -------------------------------
	elseif ($action == "nuevo_tratamiento") 
	{
		$plantilla = "tratamientos-nuevo";
		// Crear autocomplete
		$sql = "SELECT * FROM medicamentos ORDER BY nombre ASC";

		if ($rs = $db->run($sql)){		  
		$lista_medicamentos = "";
		   foreach ($rs as $row) {
				
				$lista_medicamentos .= ' <option value="' .$row['id_medicamento'].'">'.strtoupper($row['nombre']).'</option> ';		   	
		   }

		   $site_template->register_vars(array(
				"lista_medicamentos" => $lista_medicamentos
			));
		}


		

	}
	// -------------------------------
	// Editar tratamiento
	// -------------------------------
	elseif ($action == "editar_tratamiento" && !empty($_GET['id_tratamiento']) ) {
		$msg = "";
		if (isset($_GET['msg'])) {
			$msg = $_GET['msg'];
				if ($msg == "ok") {
					 $msg = '<div class="alert alert-success"><strong>¡Listo!</strong> El expediente se ha actualizado correctamente en el sistema</div>';
				}
			}		
		
		$plantilla = "tratamientos-editar";

		$id_tratamiento = $_GET['id_tratamiento'];
		$sql = "SELECT * FROM programas WHERE id_tratamiento= '$id_tratamiento'";

		$rs = $db->run($sql, "", true);
		$nombre_tratamiento =  $rs['nombre'];

		// Leer medicamentos del tratamiento
		$query = "SELECT id_medicamento FROM programas_relaciones WHERE id_tratamiento = '$id_tratamiento'";
		$id_medicamentos = array();
		   			if ($result = $db->run($query)){
		   				$medicamentos = " ";
		   				foreach ($result as $columna => $valor) {	
		   					$id_medicamento = $valor['id_medicamento'];		   			 	
			   			 	$id_medicamentos[$id_medicamento] = "selected";

			   			}
					}
		// Crear autocomplete
		$sql = "SELECT * FROM medicamentos ORDER BY nombre ASC";
		$lista_medicamentos = array();
		if ($rs = $db->run($sql)){		  
		$lista_medicamentos = "";
		   foreach ($rs as $row) {
		   		// Seleccionar medicamentos
		   		$id_medicamento = $row['id_medicamento'];
		   		if (isset($id_medicamentos[$id_medicamento])) {
		   				$lista_medicamentos .= ' <option selected value="' .$row['id_medicamento'].'">'.strtoupper($row['nombre']).'</option> ';	
		   			}
		   			else{
		   				$lista_medicamentos .= ' <option value="' .$row['id_medicamento'].'">'.$row['nombre'].'</option> ';	
		   			}				
					   	
		   }
		}

		$site_template->register_vars(array(
				"id_tratamiento" => $id_tratamiento,
				"nombre_tratamiento" =>$nombre_tratamiento,
				"lista_medicamentos" => $lista_medicamentos,
				"msg" => $msg
			));

	}
	// ---------------------------------
	// Agregar tratamiento
	// ---------------------------------
	elseif ($action == "agregar_tratamiento") {
		$plantilla = "tratamientos-nuevo";

		//variables
		$nombre_tratamiento = $_POST['nombre_tratamiento'];
		$medicamentos = $_POST['medicamentos'];

		//Agregar tratamiento
		$sql = "INSERT INTO tratamientos (nombre) 
			VALUES ('$nombre_tratamiento')";

		if ($rs = $db->run($sql)) {
			 $id_tratamiento = $db->lastInsertId();

			 // Agregar los medicamentos del tratamiento

			 foreach ($medicamentos as $key => $id_medicamento) {
					$query = "INSERT INTO programas_relaciones (id_tratamiento, id_medicamento) 
							VALUES ('$id_tratamiento', '$id_medicamento')";
					$result = $db->run($query);
				}
				$msg_status = "ok";
			}
		
		redirect("tratamientos.php?action=ver_tratamientos&msg=$msg_status");
	}
	// ---------------------------------
	// Actualizar tratamiento
	// ---------------------------------
	elseif ($action == "actualizar_tratamiento" && !empty($_POST['id_tratamiento'])) {
		
		//variables
		$id_tratamiento = $_POST['id_tratamiento'];
		$nombre_tratamiento = $_POST['nombre_tratamiento'];
		$medicamentos = $_POST['medicamentos'];

		//Actualizar nombre del tratamiento
		$update = array(
		  	'nombre' => $nombre_tratamiento,			
			);
		if ($rs = $db->update("tratamientos", $update, "id_tratamiento = '$id_tratamiento'")) {	
				$msg_status = "ok";
			}

			// Actualizar medicamentos
			$result = $db->delete("programas_relaciones", "id_tratamiento = $id_tratamiento");

			
				 foreach ($medicamentos as $key => $id_medicamento) {
					$query = "INSERT INTO programas_relaciones (id_tratamiento, id_medicamento) 
							VALUES ('$id_tratamiento', '$id_medicamento')";
					$result = $db->run($query);
				}
				$msg_status = "ok";
			


		redirect("tratamientos.php?action=editar_tratamiento&id_tratamiento=$id_tratamiento&msg=$msg_status");

	}

}

else{
	$plantilla = 'login';
}


$site_template->print_template($site_template->analizar_plantilla($plantilla));
?>
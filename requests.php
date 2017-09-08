<?php
/* 
---------------------------------------------------
Peticiones para Ajax
---------------------------------------------------
Consultorio 1.0
Desarrollado por: Esbrillante Estudio Digital
https://esbrillante.mx
---------------------------------------------------
*/

if (!isset($_POST['action'])) {
   die();
}

define('ROOT_PATH', './');
include(ROOT_PATH.'global.php');
require(ROOT_PATH.'includes/sessions.php');
include(ROOT_PATH.'includes/page_header.php');
require 'includes/PHPMailerAutoload.php';

if ($action == 'load_tratamiento') {
              
        $lista_tratamientos = "";         
                
                // Leer medicamentos del tratamiento
                $id_tratamiento = $_POST['id_tratamiento'];
                $resultado_array = array();
                $query = "SELECT id_medicamento FROM programas_relaciones WHERE id_tratamiento = '$id_tratamiento'";             

                    if ($result = $db->run($query)){
                        
                        foreach ($result as $columna => $valor) {                           
                            $id_medicamento = $valor['id_medicamento'];

                            //Obtener los nombres
                            $consulta = "SELECT nombre FROM medicamentos WHERE id_medicamento = '$id_medicamento'";
                            $resultado = $db->run($consulta, "", true);
                            $resultado_array[] = $resultado;
                           

                            }
                            header('Content-type: application/json; charset=utf-8');
                            echo json_encode($resultado_array);
                            exit();
                        }
            
}
else if ($action == 'load_medicamentos') {
    $medicamentos_array = array();
    $query = "SELECT nombre FROM medicamentos"; 
     if ($result = $db->run($query)){
        foreach ($result as $key => $value) {
            $medicamentos_array[] = $value['nombre'];
        }
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($medicamentos_array);
        exit();
     }
}


?>
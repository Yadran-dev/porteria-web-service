<?php

date_default_timezone_set('America/Santiago');

include_once("db2.php");
include_once("db3.php");
if(isset($_GET['rut'])){
    try {


        $con1 = new SqlSrv2();
		$con2 = new SqlSrv3();

		//busca si existe algun rut similar al entregado
		$queryBusqueda = "select top 1 NAME,Badgenumber,CardNo from USERINFO where Badgenumber like '%".$_GET['rut']."%'";
		$dataSetBusqueda = $con1->fetchArray($queryBusqueda);
		
		//verifica que exista el registro
		if($dataSetBusqueda){
			
			//verifica que sea tipo ingreso o salida
			
			if($_GET['tipo']==1){
				$query = "insert into acc_monitor_log (status,log_tag,time,pin,device_id,device_name,verified,state,event_type,event_point_type,event_point_id,event_point_name,card_no,description) values (0,0,getdate()," . $dataSetBusqueda[0]['Badgenumber']. ",3,'Torniquete Entrada',6,1,0,0,2,'Torniquete Entrada-2','".$dataSetBusqueda[0]['CardNo']."','')";
			}else{
				$query = "insert into acc_monitor_log (status,log_tag,time,pin,device_id,device_name,verified,state,event_type,event_point_type,event_point_id,event_point_name,card_no,description) values (0,0,getdate()," . $dataSetBusqueda[0]['Badgenumber']. ",4,'Torniquete Salida',6,1,0,0,2,'Torniquete Salida-2','".$dataSetBusqueda[0]['CardNo']."','')";
		
			}

			

			//añade codigo para insertar temperatura
			if(isset($_GET['temp'])){

					//busca si el registro ya existe hoy para guardarlo
					$queryBusqueda = "select top 1 * from registros_temperatura where rut ='".$_GET['rut']."' and convert(varchar,fecha,103) = convert(varchar,getdate(),103) and prueba=0";
					$dataSetBusquedaTemp = $con2->fetchArray($queryBusqueda);

					if($dataSetBusquedaTemp){
						$response = false;
					}else{
                        $querytemperature = "insert into registros_temperatura (rut,temperatura,fecha) values ('".$_GET['rut']."',".$_GET['temp'].",getdate())";
                        $response = $con2->query($querytemperature);
					}

			}else{
				$response = $con1->query($query);
			}


			if ($response) {
				echo "true";
				$query2 = "select top 1 NAME from USERINFO where Badgenumber = '".$dataSetBusqueda[0]['Badgenumber']."'";
				
				$dataSet = $con1->fetchArray($query2);
				$response2 =  "Sin Nombre";
				
				
				if($dataSet){
					$response2 = $dataSet[0]['NAME'];
				}
				echo "/".$response2;


                if(isset($_GET['temp'])){
                   echo "/temperatura";
                }else{
                    echo "/ingreso";
                }
				
			} else {
				echo "false";

                if(isset($_GET['temp'])){
                    echo "/registro duplicado hoy";
                }
			}
			
			//si no pillo rut que retorne false y que pasa nico ql xd
		}else {
			echo "false";
		}
    }
    catch(Exception $e){
    Echo $e;
    }
}else{
    echo "Faltan Datos";
}

?>

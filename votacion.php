<?php
require_once("funciones.php");
require_once("conexionBD.php");
$link=conectarse();

//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['DataVota'])) {
	if (isset($_POST['envia_voto'])) {
		$VotoID = $_POST['idvoto'];
        //Lista de grados
        
		$resp5 = mysqli_query($link, "SELECT * FROM categorias");
while ($row5 = mysqli_fetch_array($resp5)) {
    $categorias[$row5["id"]] = $row5["descripcion"];
}

		//Verificar que se haya seleccionado el(los) candidato(s) para registrar la votaci�n		
        $veri_cat = explode(',', $_POST['catarj']);
		foreach($veri_cat as $valor) { 
			if (!isset($_POST['categoria'.$valor])) {
				include_once("encabezado.html");
				print "<strong>No ha seleccionado su voto para ".$categorias[$valor]."<br />";
				print "<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
				exit;
			}
		}
		

		//***** VALIDAMOS QUE EL ESTUDIANTE NO HAYA VOTADO*****
		$resp = mysqli_query($link, sprintf("SELECT id FROM voto WHERE id_estudiante=%d", $VotoID));

if ($row = mysqli_fetch_array($resp)) {
    // Registro de intento de duplicación
    $ffecha = date("Y-m-d");
    $fhora = date("G:i:s");
    $fip = $_SERVER['REMOTE_ADDR'];
    $faccion = "Intento-DuplicarVoto";

    $cons_sql5 = "INSERT INTO control(c_fecha, c_hora, c_ip, c_accion, c_idest) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $cons_sql5);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ssssd', $ffecha, $fhora, $fip, $faccion, $VotoID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        die("Error al preparar la consulta de control: " . mysqli_error($link));
    }

    include_once("encabezado.html");
    print "<strong>Su voto ya se encuentra registrado en el sistema.</strong><br />";
    print "<br /><strong><a href='salir.php'>Finalizar</a></strong></div></body></html>";
    exit;
}

				//****Registrar votaci�n****
		      //****Registrar votación****
foreach ($veri_cat as $valor) { 
    $cons_sql = sprintf("INSERT INTO voto(id_estudiante, candidato) VALUES(%d, %d)", $VotoID, $_POST['categoria' . $valor]);
    mysqli_query($link, $cons_sql);
}

//******Guardamos los datos de control ******
$ffecha = date("Y-m-d");
$fhora = date("G:i:s");
$fip = $_SERVER['REMOTE_ADDR'];
$faccion = "Voto-Registrado";
$cons_sql5 = sprintf("INSERT INTO control(c_fecha, c_hora, c_ip, c_accion, c_idest) VALUES(%s, %s, %s, %s, %d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion), $VotoID);
mysqli_query($link, $cons_sql5);

include_once("encabezado2.html");
print "<strong>....</strong><br />";
print "<br /><strong><a href='salir.php'>Finalizar</a></strong></div></body></html>";

	}
}
mysqli_close($link);
?>


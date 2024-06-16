<?php
require_once("funciones.php");
require_once("conexionBD.php");
$link = conectarse();

//****Verificar si el sistema se encuentra activo*****
$estado = mysqli_query($link, "SELECT * FROM general");
$leer = mysqli_fetch_array($estado);

if ($leer['activo'] == "S") {

    if (!isset($_POST['envia_consulta'])) {
        include_once("ingresa.html");
    } else {

        //******VALIDACION DE INGRESO AL SISTEMA******
        if (!empty($_POST['documento'])) {
            $DocEst = $_POST['documento'];
        } else {
            include_once("encabezado.html");
            print "<strong>No ha escrito el número de documento<br />";
            print "<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
            exit;
        }

       
//******Funcion para guardar los datos de control ******
	function LogControl($faccion2, $idest2) {
    require_once("conexionBD.php");
    $link = conectarse();

    if (!$link) {
        die("Error al conectar a la base de datos: " . mysqli_connect_error());
    }

    $ffecha = date("Y-m-d");
    $fhora = date("G:i:s");
    $fip = $_SERVER['REMOTE_ADDR'];
    $cons_sql = "INSERT INTO control (c_fecha, c_hora, c_ip, c_accion, c_idest) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $cons_sql);

    if (!$stmt) {
        die("Error al preparar la consulta: " . mysqli_error($link));
    }

    mysqli_stmt_bind_param($stmt, 'ssssd', $ffecha, $fhora, $fip, $faccion2, $idest2);

    if (!mysqli_stmt_execute($stmt)) {
        die("Error al ejecutar la consulta: " . mysqli_error($link));
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
}


//***** VALIDAMOS QUE EL ESTUDIANTE NO HAYA VOTADO*****
$resp2 = mysqli_query($link, "SELECT id_estudiante FROM voto, estudiantes WHERE documento='" . mysqli_real_escape_string($link, $DocEst) . "' AND id_estudiante=estudiantes.id");

if ($row2 = mysqli_fetch_array($resp2)) {
    $faccion = "Intento-IngresoDuplicado";
    LogControl($faccion, $row2['id_estudiante']);
    include_once("encabezado.html");
    print "<strong>No puede ingresar</strong><br />Su voto ya está registrado en el sistema.<br />";
    print "<br /><strong><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
    exit;
}


$resp = mysqli_query($link, "SELECT id, nombres, apellidos, grado FROM estudiantes WHERE documento='" . mysqli_real_escape_string($link, $DocEst) . "'");

if ($row = mysqli_fetch_array($resp)) {
    $IdEncrip = md5($row['id']);
		
        //**** Creamos la cookie
		setcookie("DataVota", $DocEst, time()+3600);
		
        //**** Creamos la cookie es para pones ESpañol
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html lang="es">';
		echo '<head>';


        //** titulu modificar mañana 29/11 es tl titulo de la cabecera

		//echo '<title>'.$leer['institucion'].' - Tarjetón de votación</title>';
		
        //*** enlaces y css
        echo '<meta charset="UTF-8" />';
            echo '<meta name="viewport" content="width=device-width, initial-scale=1.0" />';
            echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous"/>';
            echo '<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.13.0/css/all.css"/>';
            echo '<link rel="stylesheet" href="css/vote.css"/>';
            echo '<title>Vota AG 2023</title>';

        //**
		echo '</head>';
		echo '<body class="text-center">';
		
        echo '<div class="row mx-3 bg-white px-2 shadow my-auto" id="body">';

		$faccion="Ingreso-".$DocEst;

		LogControl($faccion,$row['id']);
        
            echo '<h1 class="mt-3 mb-3 font-weight-150">ESTUDIANTE: '.$row['nombres'].' '.$row['apellidos'].'</h1>';
            echo '<hr class="mx-auto"> </hr>';
        

        

        //Variable que guarda las categorias que se muestran en el tarjetón
        $catarj="";
		echo '<form name="votacion" action="votacion.php" method="post">';
				
        
		echo '<table>';						
		echo '<thead><tr><th>CEDULA DE SUFRAGIO</th></tr></thead>';		
		echo '<tr>';
		echo '<div>';
        //Leemos la lista de categorías que apareceran en el tarjeton
		
//Leemos la lista de categorías que aparecerán en el tarjetón
$resp5 = mysqli_query($link, "SELECT * FROM categorias ORDER BY id");

while ($row5 = mysqli_fetch_array($resp5)) {
    $vrep = 0;

    $resp9 = mysqli_query($link, "SELECT * FROM grados");

    while ($row9 = mysqli_fetch_array($resp9)) {
        $grados[$row9["id"]] = $row9["grado"];

        // Realiza la comparación sin cambiar a mayúsculas
        if ($row5['nombre'] == $row9['grado']) {
            $vrep = 1;
        }
    }









        //Se muestran los candidatos por grado (pertenecen al mismo grado del estudiante) o de otras categor?s
        if ($grados[$row['grado']] == $row5['nombre'] || $vrep == 0) {
    //*****Contar el total de candidatos por categoria******
   $resp8 = mysqli_query($link, sprintf("select count(nombres) from candidatos where representante=%d", $row5['id']));
$row8 = mysqli_fetch_array($resp8);
if ($row8[0] > 0) {
    $catarj = $catarj . $row5['id'] . ",";
    echo '<div align="center">';
    echo '<table>';
    echo '<thead><tr><th colspan="' . $row8[0] . '" class="vto">' . $row5['descripcion'] . '</th></tr></thead>';
    echo '<tr>';
    // Resto del código



        # MOSTRAR CANDIDATOS
       $resp3 = mysqli_query($link, sprintf("select * from candidatos where representante=%d order by apellidos DESC", $row5['id']));

while ($row3 = mysqli_fetch_array($resp3)) {
    echo '<td class="cen cd">';
    if ((file_exists('fotos/' . $row3['id'] . '.jpg')) || (file_exists('fotos/' . $row3['id'] . '.png')) || (file_exists('fotos/' . $row3['id'] . '.gif'))) {
        if (file_exists('fotos/' . $row3['id'] . '.jpg')) {
            echo '<img src="fotos/' . $row3['id'] . '.jpg" width="100%" height:"300px" alt="Candidato" onClick = "document.getElementById (\'candidato' . $row3['id'] . '\').checked = true;" /><br />';
        } elseif (file_exists('fotos/' . $row3['id'] . '.png')) {
            echo '<img src="fotos/' . $row3['id'] . '.png" width="100%" height:"300px" alt="Candidato" onClick = "document.getElementById (\'candidato' . $row3['id'] . '\').checked = true;" /><br />';
        } elseif (file_exists('fotos/' . $row3['id'] . '.gif')) {
            echo '<img src="fotos/' . $row3['id'] . '.gif" width="100%" height:"300px" alt="Candidato" onClick = "document.getElementById (\'candidato' . $row3['id'] . '\').checked = true;" /><br />';
        }
    } else {
        echo '<img src="fotos/sinfoto.png" alt="Candidato" onClick = "document.getElementById (\'candidato' . $row3['id'] . '\').checked = true;" /><br />';
    }

    echo '<input type="radio" name="categoria' . $row5['id'] . '" id ="candidato' . $row3['id'] . '" value="' . $row3['id'] . '" />';
    echo '<strong>' . $row3['nombres'] . ' ' . $row3['apellidos'] . '</strong>';
    echo '</td>';
}


        echo '</tr>';
        echo '</table>';

        echo '<section id="submit" class="mb-3 mt-5">';
        echo '<div class="form-check my-auto text-center">';
        echo '<input class="form-check-input" type="checkbox" id="inlineFormCheck" required>';
        echo '<label class="form-check-label ml-1" for="inlineFormCheck">He revisado mi voto y estoy listo para emitirlos.</label>';
        echo '</div>';
        echo '<button class="mt-3 btn btn-purple px-4 mx-auto" type="submit" name="envia_voto" value="Votar" >VOTAR</button>';
        echo '</section>';
        echo '</div>';
        echo '</div>';


        echo '</div12>';
        echo '<br />';
    }
}
}

		//***Si el tarjet? no tiene candidatos se muestra un mensaje
        if ($catarj=="") {
            echo '<strong>No existen candidatos para votar, por favor comuníquese con el administrador del sistema.</strong>';
            echo '</div>';		
    		echo '</tr>';
	    	echo '</table>';
		    echo '</div><br />';
        }
        else {
            echo '</td>';		
	    	echo '</tr>';
		    echo '</table>';
    		echo '</div><br/>';
	    	echo '<div class="cen">';
		    echo '<input type="hidden" name="idvoto" value="'.$row['id'].'">';
    		//Eliminamos la ?ltima coma "," de la lista de categor?s
            $catarj=trim($catarj,',');
            echo '<input type="hidden" name="catarj" value="'.$catarj.'">';
    		//echo '<input type="submit" name="envia_voto" value="Votar" title="Registrar voto" />';
            
            //echo '<section id="submit" class="mb-3 mt-5">';
            //echo '<div class="form-check my-auto text-center">';
            //echo '<input class="form-check-input" type="checkbox" id="inlineFormCheck" required>';
            //echo '<label class="form-check-label ml-1" for="inlineFormCheck">He revisado mis votos y estoy listo para emitirlos.</label>';
            //echo '</div>';
            //echo '<button class="mt-3 btn btn-purple px-4 mx-auto" type="submit" name="envia_voto" value="Votar" >VOTAR</button>';
	    	//echo '</section>';
            //echo '</div>';
            //echo '</div>';
        }
		echo '</form>';
		echo '</body>';
		echo '</html>';
	}
	else {
		setcookie("DataVota", "", time()-3600);
		include_once("encabezado.html");
		$faccion="IngresoFallido-".$DocEst;
		LogControl($faccion,0);
		echo '<table>';
		echo '<tr><td class="cen" colspan="2"><strong>El documento escrito no está registrado en el sistema<br /><br />';
		print "<strong><a href='javascript:history.go(-1)'>Volver a intentarlo</a></strong></td></tr>";
		echo '</table></div></body></html>';
	}
}
}
else {
    include_once("encabezado.html");
	print "<strong>EL SISTEMA DE VOTACIÓN ESTA INACTIVO</strong><br />";
    print "(Comuniquese con el administrador del sistema)</div></body></html>";
}
mysqli_close($link);
?>
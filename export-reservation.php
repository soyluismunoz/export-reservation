<?php

/**

* Plugin Name: export reservation

* Plugin URI: https://github.com/soyluismunoz

* Description: Sencillo plugin para exportar un pdf a partir de la reservacion del plugin amelia booking

* Version: 1.0

* Author: Luis Munoz

* Author URI: https://github.com/soyluismunoz

* License: GPLv2 or later

*/
ini_set("session.auto_start", 0);

require('fpdf/fpdf.php');

function boot_session() {
  session_start();
}

add_action('wp_loaded','boot_session');

add_action( 'admin_menu', 'exportReservationMenu' );

function exportReservationMenu() {

	add_menu_page( 	__( 'Exportar reservacion', 'export-wp' ),
         			__( 'Exportar reservacion', 'export-wp' ), 
			        'administrator', 
			        'export',
			        'get_date'
				);
}

function exportBooking($d, $t){
	global $wpdb;
	$hora = date('h:i A', strtotime($t));
	$fecha = $d;

	$date = date("Y-m-d", strtotime($d));
	$time = date('H', strtotime($t));
	$time = $time + 5;
	
	$appointment = $wpdb->prefix . 'amelia_appointments';
	$booking = $wpdb->prefix . 'amelia_customer_bookings';

	$query = " SELECT `bookingStart`,`bookingEnd`, `customFields`, `info` FROM $appointment INNER JOIN $booking ON $appointment.`ID` = $booking.`appointmentId` WHERE `bookingStart` LIKE '%$date $time%' ";

	$resQuery =  $wpdb->get_results($query);

	$fullTime = "{$fecha} {$hora}";

	class PDF extends FPDF{
		// Cabecera de página
		function Header(){
			$this->SetFont('Times','', 8);
			$this->Cell(30, 5, 'Reserva', 1);
			$this->Cell(35, 5, 'Nombre', 1);
			$this->Cell(30, 5, 'Telefono', 1);
			$this->Cell(55, 5, 'Direccion', 1);
			$this->Cell(35, 5, 'CC/CE/PASAPORTE', 1);
			$this->Cell(10, 5, 'Edad', 1);
			$this->Ln();
		}

		// Pie de página
		function Footer(){
		    // Posición: a 1,5 cm del final
		    $this->SetY(-15);
		    // Arial italic 8
		    //$this->SetFont('Arial','I',8);
		    // Número de página
		    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		}
	}

	if ($resQuery) {
			$pdf = new PDF();
			$pdf->AliasNbPages();
			$pdf->AddPage();
			$pdf->SetFont('Arial', '', 6);
			$pdf->SetY(15);
			foreach($resQuery as $d){
				$customFields = json_decode($d->customFields);
				$info = json_decode($d->info);
				$pdf->Cell(30, 5, $fullTime, 1);
				$pdf->Cell(35, 5, "{$info->firstName} {$info->lastName}", 1);
				$pdf->Cell(30, 5, $info->phone, 1);

				foreach ($customFields as $c) {
					if ($c->label == "Dirección") {
						$pdf->Cell(55, 5, $c->value, 1);
					}

					if ($c->label == "CC/CE/PASAPORTE") {
						$pdf->Cell(35, 5, $c->value, 1);
					}

					if ($c->label == "Edad") {
						$pdf->Cell(10, 5, $c->value, 1);
					}
				}
				
				$pdf->Ln();
			}
			
			$pdf->Output("reservas_{$fullTime}.pdf", 'd');
	}else{
		echo "No hay reservaciones para la fecha indicada";
	}

}

function medicalStatement($d, $t){
	global $wpdb;
	$hora = date('h:i A', strtotime($t));
	$fecha = $d;

	$date = date("Y-m-d", strtotime($d));
	$time = date('H', strtotime($t));
	$time = $time + 5;
	
	$appointment = $wpdb->prefix . 'amelia_appointments';
	$booking = $wpdb->prefix . 'amelia_customer_bookings';

	$query = " SELECT `bookingStart`,`bookingEnd`, `customFields`, `info` FROM $appointment INNER JOIN $booking ON $appointment.`ID` = $booking.`appointmentId` WHERE `bookingStart` LIKE '%$date $time%' ";

	$resQuery =  $wpdb->get_results($query);

	class PDF extends FPDF{
		// Cabecera de página
		function Header(){
			$this->SetFont('Times', '', 8);
			$this->Ln(5);
			$this->Cell(50, 4, '1-. ¿Tienes malestar o dolor de garganta?', 0);
			$this->Cell(65, 4, '2-. ¿Tienes sensacion de fatiga o cansancio muscular?', 0);
			$this->Cell(35, 4, '3-. ¿Tienes fiebre? (+38°C)', 0);
			$this->Cell(45, 4, '4-. ¿Tienes tos seca y persistente?', 0);
			$this->Cell(35, 4, '5-. ¿Dificultad para respirar?', 0);
			$this->Ln();
			$this->Cell(55, 4, '6-. ¿Tienes secreciones o congestión nasales?', 0);
			$this->Cell(50, 4, '7-. ¿Tienes pérdida del olfato y/o el gusto?', 0);
			$this->Cell(85, 4, '8-. ¿Vives con alguien sospechoso o confirmado de tener COVID-19?', 0);
			$this->Ln();
			$this->Cell(120, 4, '9-. ¿En los últimos 14 días has tenido contacto estrecho con alguien sospechoso o confirmado de tener COVID-19?', 0);
			$this->Ln();
			$this->SetFont('Arial', 'B', 8);
			$this->Ln();
			$this->Cell(35, 4, 'Nombre', 1);
			$this->Cell(28, 4, 'Fecha Eucaristía', 1);
			$this->Cell(28, 4, 'Hora Eucaristía', 1);
			$this->Cell(25, 4, 'Telefono', 1);
			$this->Cell(15, 4, 'Campo 1', 1);
			$this->Cell(15, 4, 'Campo 2', 1);
			$this->Cell(15, 4, 'Campo 3', 1);
			$this->Cell(15, 4, 'Campo 4', 1);
			$this->Cell(15, 4, 'Campo 5', 1);
			$this->Cell(15, 4, 'Campo 6', 1);
			$this->Cell(15, 4, 'Campo 7', 1);
			$this->Cell(15, 4, 'Campo 8', 1);
			$this->Cell(15, 4, 'Campo 9', 1);
			$this->Cell(30, 4, 'Firma', 1);
			$this->Ln();
		}

		// Pie de página
		function Footer(){
		    // Posición: a 1,5 cm del final
		    $this->SetY(-15);
		    // Arial italic 8
		    $this->SetFont('Arial','I',8);
		    // Número de página
		    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		}
	}


	if ($resQuery) {
			$pdf = new PDF('L');
			$pdf->AliasNbPages();
			$pdf->AddPage();
			$pdf->SetY(35);
			$pdf->SetFont('Times', '', 8);
			foreach($resQuery as $d){
				$info = json_decode($d->info);
				$pdf->Cell(35, 5, "{$info->firstName} {$info->lastName}", 1);
				$pdf->Cell(28, 5, $fecha, 1);
				$pdf->Cell(28, 5, $hora, 1);
				$pdf->Cell(25, 5, $info->phone, 1);	
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(15, 5, '', 1);		
				$pdf->Cell(30, 5, '', 1);			
				$pdf->Ln();
			}

			$pdf->Output("declaracion_medica{$fecha}_{$hora}.pdf", 'd');
	}else{
		echo "No hay reservaciones para la fecha indicada";
	}
}

function get_date() {

	$date =	$_POST['fecha'];
	$time = $_POST['hora'];

	echo "<form method='post'>

		<h2>Exportar Reservaciones</h2>
		
		<label for='date'> Dia</label>
		<input type='date' name='fecha' id='date' required>

		<label for='time'> Hora</label>
		<input type='time' name='hora' id='time' required>";

	
	$btn = submit_button('Enviar');
	echo "{$btn}</form>";

	
	if ($_POST['fecha'] && $_POST['hora']) {
		echo "<hr> <p class='submit'> <a class='button button-primary' target='_blank' href='?page=export&date={$date}&time={$time}&book=true'>Exportar reservaciones</a> <a class='button button-primary' target='_blank' href='?page=export&date={$date}&time={$time}&medi=true'>Declaracion Medica</a> 
			<a class='button button-primary' href='?page=export'>Limpiar</a></p>";
	}
	
	if (isset($_GET['book'])) {
	   	exportBooking($_GET['date'], $_GET['time']);
	}

	if (isset($_GET['medi'])) {
		medicalStatement($_GET['date'], $_GET['time']);
	}
}
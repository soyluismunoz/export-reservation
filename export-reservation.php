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

function get_date() {

	global $wpdb;

	$date = date("Y-m-d", strtotime($_POST['fecha']));
	$time = date('H', strtotime($_POST['hora']));
	
	$appointment = $wpdb->prefix . 'amelia_appointments';
	$booking = $wpdb->prefix . 'amelia_customer_bookings';

	$query = " SELECT `bookingStart`,`bookingEnd`, `customFields`, `info` FROM $appointment INNER JOIN $booking ON $appointment.`ID` = $booking.`appointmentId` WHERE `bookingStart` LIKE '%$date $time%' ";


	$resQuery =  $wpdb->get_results($query);
	
?>

	<form action='<?php echo get_site_url ();?>/wp-admin/admin.php?page=export' method='post'>

		<h2>Exportar Reservaciones</h2>
		
		<label for="date"> Dia</label>
		<input type="date" name="fecha" id="date" required>

		<label for="time"> Hora</label>
		<input type="time" name="hora" id="time" required>

		<?php
			submit_button('Exportar Reservaciones');
		?>

	</form>
	
	<?php  
		if($resQuery){
			ob_start();
			$pdf = new FPDF();
			$pdf->AddPage();
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->Cell(35, 5, 'Cedula', 1);
			$pdf->Cell(45, 5, 'Nombre', 1);
			$pdf->Cell(45, 5, 'Direccion', 1);
			$pdf->Cell(45, 5, 'Telefono', 1);
			$pdf->Cell(25, 5, 'Edad', 1);
			$pdf->Ln(10);

			foreach($resQuery as $d){
				$customFields = json_decode($d->customFields);
				$customFields = json_decode(json_encode($customFields), true);
				$info = json_decode($d->info);
				$fullName = $info->firstName . " " . $info->lastName;

				$pdf->Cell(35, 5, $customFields[2]['value'], 0);
				$pdf->Cell(45, 5, $fullName, 0);
				$pdf->Cell(45, 5, $customFields[1]['value'], 0);
				$pdf->Cell(45, 5, $info->phone, 0);
				$pdf->Cell(25, 5, $customFields[3]['value'], 0);
				$pdf->Ln(10);
			}

			$pdf->Output($date . "-" . $time . ".pdf", 'd');
			ob_end_flush();
		}else{
			echo "no hay resultados para la fecha indicada.";
		}


	?>	
	

	<?php
}
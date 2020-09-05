<?php
	require('../../../wp-load.php');
	require('fpdf/fpdf.php');

	$d = $_GET['date']; 
	$t = $_GET['time'];

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
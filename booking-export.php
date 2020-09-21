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
$user = $wpdb->prefix . 'amelia_users';

$query = "SELECT $booking.`id`, `bookingStart`,`bookingEnd`, `customFields`, `info`, `email` FROM $appointment, $booking, $user WHERE $appointment.`ID` = $booking.`appointmentId` AND $booking.`customerId` = $user.`id` AND `bookingStart` LIKE '%$date $time%' ORDER BY $booking.`customerId` ASC  ";

$resQuery =  $wpdb->get_results($query);

$fullTime = "{$fecha} {$hora}";

class PDF extends FPDF{
	// Cabecera de página
	function Header(){
		global $title;
		$this->SetFont('Times', '', 14);
		$this->Cell(80);	   
    	$this->Cell(30,10, $title[0],0,0,'C');
    	$this->Ln(5);
	    $this->SetFont('Times', '', 10);
	    $this->Cell(80);
	    $this->Cell(30,10, $title[1],0,0,'C');
	    $this->SetFont('Times','', 8);
	    $this->Ln(15);
	    $this->Cell(35, 5, 'Fecha Eucaristia', 1);
	    $this->Cell(30, 5, $title[2], 1);
	    $this->Ln();
		$this->Cell(35, 5, 'Correo', 1);
		$this->Cell(30, 5, 'Nombre', 1);
		$this->Cell(23, 5, 'Telefono', 1);
		$this->Cell(55, 5, 'Direccion', 1);
		$this->Cell(30, 5, 'CC/CE/PASAPORTE', 1);
		$this->Cell(10, 5, 'Edad', 1);
		$this->Cell(12, 5, 'Temp...', 1);
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

$title = get_bloginfo('name');
$description = get_bloginfo('description');

$title = [$title, $description, $fullTime];

$pdf = new PDF();
$pdf->AliasNbPages();
//$pdf->SetTitle($title);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 6);
foreach($resQuery as $d){
	$customFields = json_decode($d->customFields);
	$info = json_decode($d->info);
	$pdf->Cell(35, 5, $d->email, 1);
	$pdf->Cell(30, 5, substr("{$info->firstName} {$info->lastName}", 0, 20), 1);
	$pdf->Cell(23, 5, $info->phone, 1);

	if($customFields){
		foreach ($customFields as $c) {
			if ($c->label == "Direccion") {
				$pdf->Cell(55, 5, substr($c->value, 0, 30), 1);
			}

			if ($c->label == "CC/CE/PASAPORTE") {
				$pdf->Cell(30, 5, $c->value, 1);
			}

			if ($c->label == "Edad") {
				$pdf->Cell(10, 5, $c->value, 1);
			}

			if ($c->label == "Temperatura") {
				$pdf->Cell(12, 5, $c->value, 1);
			}
		}
	}else{
		$pdf->Cell(55, 5, " ", 1);
		$pdf->Cell(35, 5, " ", 1);
		$pdf->Cell(10, 5, " ", 1);
		$pdf->Cell(10, 5, " ", 1);
	}
	
	$pdf->Ln();
}
$pdf->Output();

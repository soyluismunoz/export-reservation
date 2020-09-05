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

function boot_session() {
  session_start();
}

add_action('wp_loaded','boot_session');

add_action( 'admin_menu', 'exportReservationMenu' );

function exportReservationMenu() {

	add_menu_page( 	__( 'Exportar reservacion', 'export-wp' ),
         			__( 'Exportar reservacion', 'export-wp' ), 
			        'wpamelia-manager',
			        'export',
			        'get_data'
				);
}

function get_data() {
	global $wpdb;


	$fecha =	$_POST['fecha'];
	$hora = $_POST['hora'];
	$IdToUpdate = $_POST['id'];
	$NewTemp = $_POST['temp'];
?>
	<link rel="stylesheet" href="/wp-content/plugins/export-reservation/assets/style.css">
	<form method='post' action='?page=export'>

		<h2>Exportar Reservaciones</h2>
		
		<label for='date'> Dia</label>
		<input type='date' name='fecha' id='date' required>

		<label for='time'> Hora</label>
		<input type='time' name='hora' id='time' required>

		<?php echo submit_button('Enviar');?>
	</form>

<?php
	$blogName = get_bloginfo('name');
	
	if ($fecha && $hora) {
	?>
		 <hr> 
		 <p class='submit'> 
		 	<a class='button button-success' target='_blank' href='/wp-content/plugins/export-reservation/booking-export.php<?php echo "?date=$fecha&time=$hora";?>'>
		 		Exportar reservaciones
		 	</a>
		 </p>

	<?php
		$date = date("Y-m-d", strtotime($_POST['fecha']));
		$time = date('H', strtotime($_POST['hora']));
		$time = $time + 5;

		$appointment = $wpdb->prefix . 'amelia_appointments';
		$booking = $wpdb->prefix . 'amelia_customer_bookings';
		$user = $wpdb->prefix . 'amelia_users';

		$query = " SELECT $booking.`id`, `bookingStart`,`bookingEnd`, `customFields`, `info`, `email` FROM $appointment, $booking, $user WHERE $appointment.`ID` = $booking.`appointmentId` AND $booking.`customerId` = $user.`id` AND `bookingStart` LIKE '%$date $time%' ORDER BY $booking.`customerId` ASC  ";

		$resQuery =  $wpdb->get_results($query);
	?>

	

		<ul>
			<?php foreach ($resQuery as $b) {
				$customFields = json_decode($b->customFields);
				$info = json_decode($b->info);
			?>
				<li>
					<b>Nombre:</b> <?php echo "{$info->firstName} {$info->lastName}";?>
				</li>
				<li>
					<b>Telefono:</b> <?php echo  $info->phone;?>
				</li>
				<li>
					<b>Email:</b> <?php echo  $b->email;?>
				</li>
				<?php foreach ($customFields as $c) { 

					if ($c->label == "Direccion") {
						echo "<li><b>Dirección:</b> $c->value</li>";
					}

					if ($c->label == "CC/CE/PASAPORTE") {
						echo "<li><b>CC/CE/PASAPORTE:</b> $c->value</li>";
					}

					if ($c->label == "Edad") {
						echo "<li><b>Edad:</b> $c->value</li>";
					}

					if ($c->label == "Temperatura") {
						$temperatura = $c->value;
					?>

						<li>
							<b>Temperatura:</b> 
							<?php
								if($IdToUpdate ==  $b->id){  
									echo $NewTemp; 
								}else{ 
									echo $c->value;
								}
							?>
						</li>
					<?php
					}
					
				} ?>
			<p class='submit'> 
				<a class="thickbox button button-primary btn" href="#open-modal-<?php echo $b->id;?>">
					Actualizar Temperatura
				</a>
			</p>

			<div id="open-modal-<?php echo $b->id;?>" class="modal-window">
				<div>
					<a href="#" title="Close" class="modal-close">Cerrar</a>
					<h1>Agregar temperatura</h1>
				    <form action="?page=export" method="post">
				    	<label for='temp'> Temperatura </label>
						<input type='number' step="0.1" name='temp' id='temp'>
						<!--<input type="hidden" value="<?php echo $temperatura; ?>" name="curerentTemp">-->
						<input type="hidden" value="<?php echo $b->id; ?>" name="id">
						<input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
						<input type="hidden" name="hora" value="<?php echo $hora;?>">
						<!--<input type="hidden" value="<?php echo json_encode($customFields); ?>" name="booking">-->
						<?php echo submit_button('Actualizar');?>
				    </form>
				</div>
		    </div>

			<?php 
				if ($IdToUpdate ==  $b->id) {
					updateBooking($b->id, $temperatura, $NewTemp, json_encode($customFields));
				}
			?>

			 <hr>
			<?php } ?>
		</ul>

<?php	
	}
}

function updateBooking($id, $currentTemp, $temp, $booking){
	global $wpdb;
	$table = $wpdb->prefix . 'amelia_customer_bookings';

	$update = str_replace('"Temperatura","value":"'. $currentTemp .'"', '"Temperatura","value":"' . $temp . '"', $booking);
	
	$queryUpdate = "UPDATE $table SET `customFields` = '$update' WHERE $table.`id` = $id";

	$newResult = $wpdb->get_results($queryUpdate);

	return $newResult;
}

/*$boo = updateBooking(20, 50, $resQuery[0]->customFields);
			print_r($boo);*/
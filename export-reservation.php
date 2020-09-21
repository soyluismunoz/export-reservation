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

require_once('class-list-table.php');

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
	add_submenu_page( 'export',
                    __( 'Editar', 'export-wp' ),
                    __( 'Editar', 'export-wp' ), 
                   'wpamelia-manager', 
                   'edit-temp',
                   'edit_temp'
                );
}

function get_data() {
	global $wpdb;

	$date = date("Y-m-d", strtotime($_GET['fecha']));
	$time = date('H', strtotime($_GET['hora']));
	$time = $time + 5;
	$fecha = $_GET['fecha'];
	$hora = $_GET['hora'];
?>
	<link rel="stylesheet" href="/wp-content/plugins/export-reservation/assets/style.css">
	
	<div class="wrap">
		<form method='get'>

			<h2>Exportar Reservaciones</h2>
			<input type="hidden" name="page" value="<?php echo $_GET['page'];?>" />
			<label for='date'> Dia</label>
			<input type='date' name='fecha' id='date' required>

			<label for='time'> Hora</label>
			<input type='time' name='hora' id='time' required>

			<?php echo submit_button('Enviar');?>
		 	<p class="submit">
		 		<a class='button button-success' href='/wp-admin/admin.php?page=export'>
			 		Limpiar
			 	</a>
		 		<a class='button button-success' target='_blank' href='/wp-content/plugins/export-reservation/booking-export.php<?php echo "?date=$fecha&time=$hora";?>'>
			 		Exportar reservaciones
			 	</a>
		 	</p>
		</form>
		
		<form  method="get">
  			<input type="hidden" name="page" value="export" />
  			<input type='hidden' name='fecha' value="<?php echo $_GET['fecha'];?>">
			<input type='hidden' name='hora' value="<?php echo $_GET['hora'];?>">
			<input type='text' name='pas' placeholder='pasaporte/cc/ci' value='<?php echo $_GET['pas'];?>'>
			<?php
				$listBooking = new booking_list_assistant($date, $time);
			    $listBooking->prepare_items();
			    $listBooking->search_box('buscar', 'search_nombre');
				echo $listBooking->display();
			?>
		</form>
	</div>
	<?php
}


function edit_temp(){
	global $wpdb;

	$id = $_GET['id'];
	$fecha = $_GET['fecha'];
	$hora = $_GET['hora'];
	$temperatura = $_GET['temp'];
	$newTemp = $_POST['newTemp'];

	$table = $wpdb->prefix . 'amelia_customer_bookings';
	
	$getCustomField = "SELECT `customFields` FROM $table WHERE `id` = $id LIMIT 1";
	$getCustomField = $wpdb->get_results($getCustomField);

	if ($newTemp) {

		$update = str_replace('"Temperatura","value":"'. $temperatura .'"', '"Temperatura","value":"' . $newTemp . '"', $getCustomField[0]->customFields);
		$queryUpdate = "UPDATE $table SET `customFields` = '$update' WHERE $table.`id` = $id";
		$queryUpdate = $wpdb->get_results($queryUpdate);

		$temperatura = $newTemp;
		?> 
			Temperatura actualizada. <a href="<?php echo get_site_url() . "/wp-admin/admin.php?page=export&fecha={$fecha}&hora={$hora}";?>">Regresar</a>
		<?php
	}
?>
	<h2>Editar Temperatura</h2>
	<form method="post">
    	<label for='temp'> Temperatura </label>
		<input type='number' step="0.1" name='newTemp' id='newTemp' value="<?php echo $temperatura; ?>">
		<input type="hidden" value="<?php echo $temperatura; ?>" name="curerentTemp">
		<input type="hidden" value="<?php echo $id; ?>" name="id">
		<input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
		<input type="hidden" name="hora" value="<?php echo $hora;?>">
		<!--<input type="hidden" value="<?php echo json_encode($customFields); ?>" name="booking">-->
		<?php echo submit_button('Actualizar');?>
    </form>
<?php
}


/*function updateBooking($id, $currentTemp, $temp, $booking){
	global $wpdb;
	$table = $wpdb->prefix . 'amelia_customer_bookings';

	$update = str_replace('"Temperatura","value":"'. $currentTemp .'"', '"Temperatura","value":"' . $temp . '"', $booking);
	
	$queryUpdate = "UPDATE $table SET `customFields` = '$update' WHERE $table.`id` = $id";

	$newResult = $wpdb->get_results($queryUpdate);

	return $newResult;
}*/
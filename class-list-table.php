<?php 
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class booking_list_assistant extends WP_List_Table
{
    //put your code here
    var $date = "";
    var $time = "";

    public function booking_list_assistant($dated, $timed)
    {
        parent::__construct();
        $this->date = $dated;
        $this->time = $timed;
    }


    function column_nombre($item) {
        $actions = array(
            'edit'      => sprintf('<a href="?page=edit-temp&id=' . $item['id'] . '&fecha='. $_GET['fecha'] .'&hora='. $_GET['hora'] .'&temp='. $item['temperatura'] .' ">Editar</a>',$_REQUEST['page'],'edit',$item['id'] )
        );

        return sprintf('%1$s %2$s', $item['nombre'], $this->row_actions( $actions ) );
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();

        if ($data) {
        	usort( $data, array( &$this, 'sort_data' ) );
        	$totalItems = count($data);
        }else{
        	$totalItems = 0;
        }

        $perPage = 30;
        $currentPage = $this->get_pagenum();
        

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        if ($data) {
        	$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
    	}
    	
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'id'            => 'id',
            'nombre'        => 'nombre',
            'telefono'      => 'telefono',
            'email'         => 'email',
            'direccion'     => 'direccion',
            'edad'          => 'edad',
            'pasaporte'     => 'pasaporte',
            'temperatura'   => 'temperatura'
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('nombre' => array('nombre', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {   
        global $wpdb;
        $date = $this->date;
        $time = $this->time;
        $s = $_GET['s'];
        $pas = $_GET['pas'];

        $appointment = $wpdb->prefix . 'amelia_appointments';
        $booking = $wpdb->prefix . 'amelia_customer_bookings';
        $user = $wpdb->prefix . 'amelia_users';

        $query = " SELECT $booking.`id`, `bookingStart`,`bookingEnd`, `customFields`, `info`, `email` FROM $appointment, $booking, $user WHERE $appointment.`ID` = $booking.`appointmentId` AND $booking.`customerId` = $user.`id` AND `bookingStart` LIKE '%$date $time%' AND `firstName` LIKE '%$s%' AND `customFields` LIKE '%$pas%' ORDER BY $booking.`customerId` ASC  ";

        $resQuery =  $wpdb->get_results($query);

        foreach ($resQuery as $b) {
            $customFields = json_decode($b->customFields);
            $info = json_decode($b->info);

            foreach ($customFields as $c) { 

                if ($c->label == "Direccion") {
                    $direccion = $c->value;
                }

                if ($c->label == "CC/CE/PASAPORTE") {
                    $pasaporte = $c->value;
                }

                if ($c->label == "Edad") {
                    $edad = $c->value;
                }

                if ($c->label == "Temperatura") {
                    $temperatura = $c->value;
                }        
            }

            $data[] = array(
                'id'            =>  $b->id,
                'nombre'        =>  "{$info->firstName} {$info->lastName}",
                'telefono'      =>  $info->phone,
                'email'         =>  $b->email,
                'direccion'     =>  $direccion,
                'edad'          =>  $edad,
                'pasaporte'     =>  $pasaporte,
                'temperatura'   =>  $temperatura
            );
            
        }        
        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'nombre':
            case 'telefono':
            case 'email':
            case 'direccion':
            case 'edad':
            case 'pasaporte':
            case 'temperatura':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'nombre';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }

    function no_items() {
        _e( 'No hay reservas en esta fecha' );
    }
}
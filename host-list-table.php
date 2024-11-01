<?php
/**
 * Host Manager List Table class.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 * @access private
 */
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class wpwhoosh_host_list_table extends WP_List_Table {


	private $slug;
	
 	function __construct($admin_url) {
 		$this->slug = $admin_url; 
  		parent::__construct( array('plural' => 'hosts') );
 	}

 	function get_url($action, $id='') {
   		return $this->slug.'&amp;action=' . $action . (!empty($id) ? ('&amp;id='.$id) : '');
 	}

 	function ajax_user_can() {
  		return current_user_can( 'read' );
 	}

 	function get_list() {
		$list = array();
		$hosts = WPWhooshUtils::get_hosts();
		foreach ($hosts as $id => $data) $list[$id]= array( $data['host_name']);
		return $list;
 	}

 	function prepare_items() {
 		$search = isset( $_REQUEST['s'])  ? $_REQUEST['s'] : '';
		$subs = WPWhooshUtils::get_hosts (false);
		if ($search) {
			$search_keys = array_fill_keys(array('host_id','host_name','cpanel_provider','cpanel_url','cpanel_user','cpanel_status','cpanel_verified_date','number_sites'),1);
			$subset = array();
			foreach ($subs as $id => $data) {
				$found = false;
				foreach ($data as $key => $val) 
					if (array_key_exists($key,$search_keys) && (strpos($val,$search) !== FALSE)) { $found = true; break; }
				if ($found) $subset[$id] = $data;
			}
			$this->items = $subset;
		} else
  			$this->items = $subs;
  	    if ( isset( $_REQUEST['orderby'])) uksort($this->items, array(&$this,'compare_items'));	
 	}
    
    function compare_items($a,$b) {
    	$key=$_REQUEST['orderby'];
    	$direction = $_REQUEST['order']=='desc' ?  -1 : 1;
    	$aval = $this->items[$a][$key]; 
    	$bval = $this->items[$b][$key]; 
		if (is_numeric($aval) && is_numeric($bval))
 			return ($aval < $bval ? -1 : 1) * $direction;
 		else
  			return strcmp($aval,$bval)*$direction;
    }

 	function no_items() {
  		_e( 'No hosts found.' );
 	}

 	function get_bulk_actions() {
  		$actions = array();
  		$actions['delete'] = __( 'Delete' );
  		return $actions;
 	}

 	function get_columns() {
  		return array('cb' => '',
  		 	'host_name' => __( 'Host Name' ),
   			'cpanel_provider' => __( 'Provider' ),   
   			'cpanel_status' => __( 'cPanel Status' ),
   			'cpanel_verified_date' => __( 'Last verified' ),
   			'number_sites' => __( 'Sites' )
      		);
 	}

 	function get_sortable_columns() {
  		return array(
   			'host_name'  => 'host_name',
   			'cpanel_provider' => 'cpanel_provider',   
   			'cpanel_status' => 'cpanel_status',
   			'cpanel_verified_date' => 'cpanel_verified_date',
   			'number_sites' => 'number_sites' 
  			);
 	}

 	function display_rows() {
 		$providers = new wpwhoosh_provider_list();
   		$columns = $this->get_columns();
   		$alt = 0; 
   		foreach ( $this->items as $host ) {
   			$host_id = esc_attr( $host['host_id'] );
   			$host_name = esc_attr( $host['host_name'] ); 
   			$host['cpanel_url'] = esc_html( $host['cpanel_url'] );   
   			$host['cpanel_provider']= esc_attr( $providers->get_name($host['cpanel_provider']));      
   			$style = ( $alt++ % 2 ) ? '' : ' class="alternate"';
   			$edit_host = $this->get_url('edit', $host_id);
   			if ($host['number_sites'] ==0) {
   				$delete_host = $this->get_url('delete', $host_id) . '&noheader=true&_wpnonce=' . wp_create_nonce( 'delete-host_' . $host_id ) ;   
				$can_delete = true;
				$check_disabled = '';
			} else {
   				$delete_host = '';
				$can_delete = false;
				$check_disabled = ' disabled="disabled"';
			}
   			echo('<tr id="host-'.$id.'" valign="middle"'.$style.'>');
			foreach ( $columns as $column_name => $column_display_name ) {
    			$class = "class='column-$column_name'";
    			$style = '';
    			//if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
    			$attributes = $class . $style;
    			switch ( $column_name ) {
			 		case 'cb':
						echo '<th scope="row" class="check-column"><input type="checkbox" name="cb[]"'.$check_disabled.' value="'. esc_attr( $host_id ) .'" /></th>';
						break;    
     				case 'host_name':
      					echo ('<td '.$attributes.'><strong><a class="row-title" href="'.$edit_host.'" title="' . 
      					esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $host['host_name'] ) ) . 
      						'">'.$host_name.'</a></strong><br />');
      					$actions = array();
      					$actions['edit'] = '<a href="' . $edit_host . '">' . __( 'Edit' ) . '</a>';
      					$message = sprintf('You are about to delete host `%1$s` \ Press Cancel to stop, OK to delete.',$host_name);
      					$js = esc_js(sprintf('if (confirm("%1$s")) { return true;} return false;',$message) );
      					if ($host['number_sites'] ==0) $actions['delete'] = "<a class='submitdelete' href='". $delete_host."' onclick='".$js."'>" . __( 'Delete' ) . "</a>";
      					echo $this->row_actions( $actions );
					//echo '<br/>'.gmdate("Y-m-d\TH:i:s\Z",$host['cpanel_updated']);
      					echo '</td>';
      					break;
     				default:
       					echo ('<td '.$attributes.'>'. $host[$column_name] .'</td>');
       					break;
    			} //end switch
   			} //end for each column
  			echo('</tr>');
		} // end for each hosts
  	} //end function
} //class
?>

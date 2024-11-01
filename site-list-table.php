<?php
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class wpwhoosh_site_list_table extends WP_List_Table {


	private $slug;
	
 	function __construct($slug) {
 		$this->slug = $slug; 
  		parent::__construct( array('plural' => 'sites') );
 	}

 	function get_slug() {
   		return $this->slug;
 	}

 	function ajax_user_can() {
  		return current_user_can( 'read' );
 	}
 	
 	function get_list() {
		$list = array();
		$sites = WPWhooshUtils::get_sites();
		foreach ($sites as $id => $data) $list[$id]= array( sprintf('%1$s(%2$s)',$data['site_name'],$data['site_url']));
		return $list;
 	}

 	function prepare_items() {
 		$search = isset( $_REQUEST['s'])  ? $_REQUEST['s'] : '';
		$sites = WPWhooshUtils::get_sites (false);
		if ($search) {
			$search_keys = array_fill_keys(
				array('site_id','site_url','site_name','site_status','site_updated'),1);
			$subset = array();
			foreach ($sites as $id => $data) {
				$found = false;
				foreach ($data as $key => $val) 
					if (array_key_exists($key,$search_keys) && (strpos($val,$search) !== FALSE)) { $found = true; break; }
				if ($found) $subset[$id] = $data;
			}
			$this->items = $subset;
		} else
  			$this->items = $sites;
  	    $this->filter_sites(array_key_exists('sitefilter',$_REQUEST) ? $_REQUEST['sitefilter'] : '');
  	    if ( isset( $_REQUEST['orderby'])) uksort($this->items, array(&$this,'compare_items'));	
 	}
 	
 	function filter_sites($filter) {
		$items = array();
		foreach ($this->items as $id => $data)
			switch ($filter) {
			case 'archive':	 if ('ARCHIVE'== $data['site_status']) $items[$id] = $data; break;
			case 'live':	 if ('LIVE'== $data['site_status']) $items[$id] = $data; break;
			case 'progress': if (('LIVE'!= $data['site_status']) && ('ARCHIVE'!= $data['site_status'])) $items[$id] = $data; break;
			default:	if ('ARCHIVE'!= $data['site_status']) $items[$id] = $data; break;
			}
 		$this->items = $items;
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
  		_e( 'No sites found.' );
 	}

 	function get_bulk_actions() {
  		$actions = array();
  		$actions['remove'] = __( 'Remove' );
  		return $actions;
 	}

 	function get_columns() {
  		return array('cb' => '',
   			'site_name' => __( 'Site Name' ),
   			'site_url' => __( 'URL' ),
   			'template' => __( 'Template' ),  
   			'host' => __( 'Host' ),     			   			   
   			'site_updated' => __( 'Date last updated' ),
   			'site_status' => __( 'Status' )
   		);
 	}

 	function get_sortable_columns() {
  		return array(
   			'site_name' => 'site_name',
   			'site_url' => 'site_url',   
   			'template' => 'template',   
   			'host' => 'host',			
   			'site_updated' => 'site_updated',      			
      		'site_status' => 'site_status'
  			);
 	}

    function extra_tablenav($which) {
		if ( 'top' == $which ) {
			$site_filter = array_key_exists('sitefilter',$_REQUEST) ? $_REQUEST['sitefilter'] : 'current';
			$is_current = 'current'==$site_filter ? 'selected="selected"' : '';
			$is_archive = 'archive'==$site_filter ? 'selected="selected"' : '';
			$is_live = 'live'==$site_filter ? 'selected="selected"' : '';
			$is_progress = 'progress'==$site_filter ? 'selected="selected"' : '';
			echo ('<div class="alignright sitefilter">');
			print <<< FILTER_SELECT
<select name="sitefilter" onchange="document.forms[1].submit();">
<option {$is_current} value="current">Current Sites</option>
<option {$is_progress} value="progress">Sites in Progress</option>
<option {$is_live} value="live">Live Sites</option>
<option {$is_archive} value="archive">Archived Sites</option>
</select>
FILTER_SELECT;
			$this->search_box( __( 'Search Sites' ), 'site' );	
			echo('</div>');
		}
    }

    private function make_action_link($id, $warning, $action) {
    	$action_url = WPWhooshUtils::admin_url($this->get_slug(), $action, $id, true, true, 'site');
      	$confirm_message = $warning."\n\n Click Cancel to stop, OK to remove.";
      	$js = esc_js(sprintf('if (confirm("%1$s")) { return true;} return false;',$confirm_message));
      	return sprintf('<a class="submitdelete" href="%1$s" onclick=\'%2$s\'>%3$s</a>', $action_url, $js, __(ucwords($action)));
    }

	private function make_archive_link($site_id, $site_url) {
		return  $this->make_action_link ($site_id,
			sprintf(__('You are about to archive the entry for site %1$s'),$site_url), 'archive' );
	}
	
	private function make_remove_link($site_id, $site_url) {
		return  $this->make_action_link ($site_id,
			sprintf(__('You are about to remove the site %1$s from the dashboard'),$site_url), 'remove');
	}

	private function make_delete_link($site_id, $site_url) {
		return  $this->make_action_link ($site_id,
			sprintf(__('You are about to try and delete the site %1$s from your host. If you just want to remove the entry for the site from this dashboard then click on Cancel and then use the Archive facility instead.'),$site_url), 
			'delete');
	}

    private function site_status($status_code) {
		switch ($status_code) {
			case "DRAFT": $class='red'; $status = 'Not ready. Data incomplete'; break;
			case "READY": $class='orange'; $status = 'Ready for checking'; break;
			case "VERIFIED": $class='green'; $status = 'Ready for installation'; break;
			case "INVALID": $class='orange'; $status = 'Failed checks'; break;
			case "PAID": $class='green'; $status = 'Paid and Ready for installation'; break;
			case "UNPAID": $class='orange'; $status = 'Insufficient funds for Installation'; break;
			case "FAILED": $class='orange'; $status = 'Previous Installation Failed'; break;
			case "LIVE": $class='green'; $status = 'Live'; break;
			case "ARCHIVE": $class='gray'; $status = 'Archived'; break;
		}
		return sprintf('<span style="color:%1$s">%2$s</span>',$class, $status);
    }

 	function display_rows() {
 		$templates = new wpwhoosh_template_list();
 		$hosts = new wpwhoosh_host_list();

   		$columns = $this->get_columns();
   		$alt = 0; 
   		foreach ( $this->items as $site ) {
    		$site_status = $site['site_status'] ;   
   			$site_id = esc_attr( $site['site_id'] );
   			$site_name = esc_attr( $site['site_name'] ); 
   			$site['site_url'] = esc_html( $site['site_url'] );    			  
   			$site['host']= esc_attr( $hosts->get_name($site['host']));      
   			$site['template']= esc_attr( $templates->get_name($site['template'])); 
   			$style = ( $alt++ % 2 ) ? '' : ' class="alternate"';
   			$edit_site = WPWhooshUtils::admin_url($this->get_slug(), 'edit', $site_id);
   			echo('<tr id="site-'.$site_id.'" valign="middle"'.$style.'>');
			foreach ( $columns as $column_name => $column_display_name ) {
    			$class = "class='column-$column_name'";
    			$style = '';
    			$attributes = $class . $style;
    			switch ( $column_name ) {
			 		case 'cb':
						echo '<th scope="row" class="check-column"><input type="checkbox" name="cb[]" value="'. esc_attr( $site_id ) .'" /></th>';
						break;    
     				case 'site_name':
     					$default_action = (('ARCHIVE'==$site_status) || ('LIVE'==$site_status)) ? 'View' : 'Edit';
      					echo ('<td '.$attributes.'><strong><a class="row-title" href="'.$edit_site.'" title="' . 
      					esc_attr( sprintf( __( '%1$s &#8220;%2$s&#8221;' ), $default_action, $site['site_description'] ) ) . 
      						'">'.$site_name.'</a></strong><br />');
      					$actions = array();
      					$actions[strtolower($default_action)] = '<a href="' . $edit_site . '">' . __( $default_action ) . '</a>';
						switch ($site_status) {
						case 'LIVE' : 
							$actions['archive'] = $this->make_archive_link($site_id,$site['site_url']);	
							//$actions['delete'] = $this->make_delete_link($site_id,$site['site_url']);	
							break;
						default: 
							$actions['remove'] = $this->make_remove_link($site_id,$site['site_url']);	
							break;
      					}
      					echo $this->row_actions( $actions );
      					echo '</td>';
      					break;
     				case 'site_status': 
        				printf ('<td %1$s>%2$s</td>', $attributes, $this->site_status($site[$column_name]));
     					break;
     				default:
       					echo ('<td '.$attributes.'>'. $site[$column_name] .'</td>');
       					break;
    			} //end switch
   			} //end for each column
  			echo('</tr>');
		} // end for each sites
  	} //end function
} //class
?>
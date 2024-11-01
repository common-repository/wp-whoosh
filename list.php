<?php
global $wpwhoosh_lists;
$wpwhoosh_lists = array();

class wpwhoosh_list {

    private $listname;
	function __construct($listname, $array, $filter=false, $fld=0, $chkfld=0) {
	    $extra = false;
		$fld2='';
		$fld3='';		
		if (empty($chkfld)) $chkfld = $fld;
	    if (strpos($fld,',')) {
	    	$flds= explode(',',$fld);
	    	$fld= $flds[0];
	    	if (count($flds) > 1) $fld2= $flds[1];
	    	if (count($flds) > 2) $fld3= $flds[2];
			$extra = true;
	    }
	    global $wpwhoosh_lists;
		$this->listname = empty($filter) ? $listname : ($listname.'_'.str_replace(' ','_',$filter));
		if (! array_key_exists($this->listname,$wpwhoosh_lists)) { 
			$list = array();
	    	if (empty($filter)) 
	    		$list = (array)$array;
	    	else
	    		foreach ($array as $key => $vals) if ($filter == $vals[1]) $list[$key] = (array)$vals;
			$wpwhoosh_lists[$this->listname]['fld'] = $fld;
			$wpwhoosh_lists[$this->listname]['array'] = $list;
	    	$wpwhoosh_lists[$this->listname]['select'] = $this->build_selected($list,$fld,$extra,$fld2);
	    	$wpwhoosh_lists[$this->listname]['checkbox'] = $this->build_checkboxes($list,$fld,$extra,$fld2);
	    	$wpwhoosh_lists[$this->listname]['radio'] = $this->build_radios($list,$fld,$extra,$fld2,$fld3);
			$a = array();
			if ($filter) {
				foreach ($list as $key => $vals)  $a[$key] = $vals[$fld]; 
			} else {
				foreach ($list as $key => $vals)  $a[$key] = $vals[$fld]. (count($vals) ==2 ? (' ('. $vals[1].')') : ''); 
	    	}
	    	$wpwhoosh_lists[$this->listname]['list'] = $a;
			
		}
	}

	function get_subarray($keys) {
		global $wpwhoosh_lists;
		$a = $wpwhoosh_lists[$this->listname]['list'];
		return array_intersect_key($a,array_fill_keys($keys,1));
	}


	function get_list() {
		global $wpwhoosh_lists;
		return $wpwhoosh_lists[$this->listname];
	}

	function get_dropdown($selected=false, $disabled=false) {
		global $wpwhoosh_lists;
		$list = $wpwhoosh_lists[$this->listname]['select'];
	    if ($selected && ! empty($selected)) 
	    	$list = str_replace('value="'.$selected.'"', 'selected value="'.$selected.'"', $list);
		if ($disabled) $list = str_replace('<select', '<select disabled="disabled"', $list);
	    return $list;
	}
	
	function get_checkboxes($selected=false,$hidden=false) {
		global $wpwhoosh_lists;
		$list = $wpwhoosh_lists[$this->listname]['checkbox'];
	    if ($selected && ! empty($selected)) {
	    	if (is_array($selected)) {
	    		$replace_this = array();
	    		$replace_that = array();
				foreach ($selected as $selection) {
					$replace_this[] = 'value="'.$selection.'"';
					$replace_that[] = 'checked="checked" value="'.$selection.'"';
				}
	        	$list =  str_replace($replace_this, $replace_that, $list);
	    	} else 
	        	$list = str_replace('value="'.$selected, 'checked="checked" value="'.$selected, $list);
	    } 
	    if ($hidden && ! empty($hidden)) {
	        $input = '<input type="checkbox" name="'.$this->listname.'[]" value="';
	    	if (is_array($hidden)) {
	    		$replace_this = array();
	    		$replace_that = array();
				foreach ($hidden as $selection) {
					$replace_this[] = '<li>'.$input.$selection.'"';
					$replace_that[] = '<li class="hidden">'.$input.$selection.'"';
				}
	        	$list =  str_replace($replace_this, $replace_that, $list);
	    	} else 
	        	$list = str_replace('<li>'.$input.$hidden, '<li class="hidden">'.$input.$hidden, $list);
	    } 
	    return $list;
	}
	
	function get_radios($selected=false,$hidden=false) {
		global $wpwhoosh_lists;
		$list = $wpwhoosh_lists[$this->listname]['radio'];
	    if ($selected && ! empty($selected)) {
	        $list = str_replace('value="'.$selected, 'checked="checked" value="'.$selected, $list);
	    } 
	    if ($hidden && ! empty($hidden)) {
	        $input = '<input type="radio" name="'.$this->listname.'" value="';
	    	if (is_array($hidden)) {
	    		$replace_this = array();
	    		$replace_that = array();
				foreach ($hidden as $selection) {
					$replace_this[] = '<li>'.$input.$selection.'"';
					$replace_that[] = '<li class="hidden">'.$input.$selection.'"';
				}
	        	$list =  str_replace($replace_this, $replace_that, $list);
	    	} else 
	        	$list = str_replace('<li>'.$input.$hidden, '<li class="hidden">'.$input.$hidden, $list);
	    } 
	    return $list;
	}	
	
	function get_item($id) {
		global $wpwhoosh_lists;
		if (empty($id) || is_array($id)) return false;
		$list = $wpwhoosh_lists[$this->listname]['array'];
	    return array_key_exists($id,$list) ? $list[$id] : false;
	}
	
	function get_name($id) {
		global $wpwhoosh_lists;
		$fld = $wpwhoosh_lists[$this->listname]['fld'];		
		if ($item = $this->get_item($id)) 
			return $item[$fld];
		else
			return false;
	}	
	
	private function build_selected($list,$fld,$extra=false,$fld2='') {
	    $s= '';
	    //if (count($list) > 1) 
	    	$s .= sprintf('<option value="">%1$s</option>',__('Please select'));
	    if (is_array($list)) foreach ($list as $key => $vals)  {
	    	if (is_array($vals) && array_key_exists($fld,$vals))
				if ($extra && array_key_exists($fld2,$vals) && !empty($vals[$fld2])) 
					$val = $vals[$fld] . ' ('. ucwords(strtolower($vals[$fld2])) .')';
				else 
					$val = $vals[$fld] ;
			else
				$val = $vals;
	    	$s .= sprintf('<option value="%1$s">%2$s</option>',$key,$val);
	  	}
	    return sprintf('<select name="%1$s" id="%1$s" class="required">%2$s</select>',$this->listname,$s);
	}
	
	private function build_checkboxes($list,$fld,$extra=false,$fld2='') {
		if (is_array($list))  {
			foreach ($list as $key => $vals) {
	    		if (is_array($vals) && array_key_exists($fld,$vals))
					if ($extra && array_key_exists($fld2,$vals) && !empty($vals[$fld2])) 
						$val = $vals[$fld] . ' - '. $vals[$fld2];
					else 
						$val = $vals[$fld] ;
				else
					$val = $vals;
	    		$c .= sprintf('<li><input type="checkbox" name="%1$s[]" value="%2$s"/>%3$s</li>', $this->listname, $key, $val);
			}
			return sprintf('<ul>%1$s</ul>',$c);
		}
	}

	private function build_radios($list,$fld,$extra=false,$fld2='',$fld3='') {
	  if (is_array($list))  {
		foreach ($list as $key => $vals) {
	    	if (is_array($vals) && array_key_exists($fld,$vals))
				if ($extra && array_key_exists($fld2,$vals) && !empty($vals[$fld2])) 
					$val = $vals[$fld] . ' - '. $vals[$fld2] . 
						((array_key_exists($fld3,$vals) && !empty($vals[$fld3]))?(' - '.$vals[$fld3]):'');
				else 
					$val = $vals[$fld] ;
			else
				$val = $vals;
	    	$c .= sprintf('<li><input type="radio" name="%1$s" value="%2$s"/>%3$s</li>', $this->listname, $key, $val);
		}
		return sprintf('<ul class="%2$s">%1$s</ul>',$c,$this->listname.'s');
      }
	}

}
?>
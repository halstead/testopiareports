<?php

class TR_Colors {

	/**
	* Colors
	**/
	
	#
	# Test Case Run Status (Ploticus)
	#
	private $rep_cols_stat = array(
					"ERROR"   => "red",
					"IDLE"    => "purple",
					"PASSED"  => "green",
					"FAILED"  => "orange",
					"RUNNING" => "blue",
					"PAUSED"  => "skyblue",
					"BLOCKED" => "yellow",
					"UNDEF"   => "pink");
	#
	# Test Case Run Status (Google Charts)
	#
	private $rep_cols_stat_html = array(
					"ERROR"   => "FF0000",
					"IDLE"    => "990066",
					"PASSED"  => "00FF00",
					"FAILED"  => "FF9933",
					"RUNNING" => "0000FF",
					"PAUSED"  => "3366FF",
					"BLOCKED" => "FFFF66",
					"UNDEF"   => "FF3399");
	#
	# Test Case Status (Ploticus)
	#
	private $rep_cols_stat2 = array(
					"PROPOSED"  => "blue",
					"CONFIRMED" => "green",
					"DISABLED"  => "purple",
					"UNDEF"     => "pink");
	#
	# Test Case Status (Google Charts)
	#
	private $rep_cols_stat2_html = array(
					"PROPOSED"  => "0000FF",
					"CONFIRMED" => "00FF00",
					"DISABLED"  => "990066",
					"UNDEF"     => "FF3399");
	#
	# Test Case Priorities (Ploticus)
	#
	private $rep_cols_prio = array(
					""          => "red",
					"P2"          => "orange",
					"P3"          => "yellow",
					"P4"          => "skyblue",
					"P5"          => "blue",
					"ENHANCEMENT" => "green",
					"UNDEF"       => "pink");
	#
	# Test Case Priorities (Google Charts)				
	#
	private $rep_cols_prio_html = array(
					"P1"          => "FF0000",
					"P2"          => "FF9933",
					"P3"          => "FFFF66",
					"P4"          => "3366FF",
					"P5"          => "0000FF",
					"ENHANCEMENT" => "00FF00",
					"UNDEF"       => "FF3399");

	#
	# Test Case Status (Google Charts)				
	#
	private $rep_cols_tstatus_html = array(
					"PROPOSED"	=> "FFFF66",
					"CONFIRMED"	=> "66DD66",
					"DISABLED"	=> "FF0000",
					"UNDEF"     => "FF3399");
					
	#
	# Bug Status (Ploticus)
	#
	private $rep_cols_bug_status = array(
					"NEW"	      => "blue",
					"ACCEPTED"    => "skyblue",
					"IN PROGRESS DESIGN"    => "orange", 
					"REOPENED"    => "red",
					"RESOLVED"    => "green",
					"VERIFIED"    => "yellow",
					"CLOSED"      => "grey",
					"IN PROGRESS DESIGN COMPLETE"      => "purple",
					"IN PROGRESS REVIEW" => "dark green",
					"NEEDINFO" => "orange",
					"WaitForUpstream" => "black",
					"UNDEF"       => "pink");
	#					
	# Bug Status (Google Charts)				
	#
	private $rep_cols_bug_status_html = array(
					"NEW"         => "0000FF",
					"ACCEPTED"    => "3366FF",
					"IN PROGRESS DESIGN"    => "FF9933",
					"REOPENED"    => "FF0000",
					"RESOLVED"    => "33FF33",
					"VERIFIED"    => "00FF00",
					"CLOSED"      => "#C0C0C0",
                                        "IN PROGRESS DESIGN COMPLETE" => "FF00FF",
					"IN PROGRESS REVIEW" => "669933",
					"NEEDINFO" => "FF9933",
					"WaitForUpstream" => "000000",
					"UNDEF"       => "FF3399");
	#
	# Completition (Ploticus)
	#
	private $rep_cols_compl = array(
					"COMPLETED"     => "blue",
					"NOT COMPLETE"  => "yellow",
					"BLOCKED"       => "orange", 
					"FAILED"        => "red",
					"PASSED"        => "green",
					"UNDEF"         => "pink");
	#					
	# Completition (Google Charts)				
	#
	private $rep_cols_compl_html = array(
					"COMPLETED"     => "0000FF",
					"NOT COMPLETE"  => "3366FF",
					"BLOCKED"       => "FF9933", 
					"FAILED"        => "FF0000",
					"PASSED"        => "00FF00",
					"UNDEF"         => "FF3399");	
	
	/****************************************************************/
	
	public function getColorPloticus($val, $colors, $type) {
		$newColor = $colors;

		$arr=array();
		switch ($type) {
			case "tcrs"   : $arr=$this->rep_cols_stat; break;
			case "tcs"    : $arr=$this->rep_cols_stat2; break;
			case "prio"   : $arr=$this->rep_cols_prio; break;
			case "bs"     : $arr=$this->rep_cols_bug_status; break;
			case "compl"  : $arr=$this->rep_cols_compl; break;
			case "tstatus": $arr=$this->rep_cols_tstatus; break;
		}
		
		if (array_key_exists(strtoupper($val), $arr)) {
			$newColor .= $arr[strtoupper($val)];
		} else {
			$newColor .= $arr["UNDEF"];
		}		
		$newColor.=" ";
		
		$arr=null;
		return $newColor;		
	}	
	
	public function getColorHTML($val, $type) {
		$arr=array();
		switch ($type) {
			case "tcrs" : $arr=$this->rep_cols_stat_html; break;
			case "tcs"  : $arr=$this->rep_cols_stat2_html; break;
			case "prio" : $arr=$this->rep_cols_prio_html; break;
			case "bs"   : $arr=$this->rep_cols_bug_status_html; break;
			case "compl": $arr=$this->rep_cols_compl_html; break;
			case "tstatus": $arr=$this->rep_cols_tstatus_html; break;
		}
		
		if (array_key_exists(strtoupper($val), $arr)) {
			return $arr[strtoupper($val)];
		} else {
			return $arr["UNDEF"];
		}		
		
	}

	
	function autoColorHTML($colorRange, $idx, $steps) {
		switch ($colorRange) {
			case "rainbow":
				$hexstart = "00FF00";
				$hexend   = "0000FF";		
				break;
								
			case "gray":
				$hexstart = "222222";
				$hexend   = "EEEEEE";		
				break;
								
			case "yellow":
				$hexstart = "885522";
				$hexend   = "FFEE55";		
				break;
				
			case "orange":
				$hexstart = "883311";
				$hexend   = "FF9944";
			case "brown":				
				$hexstart = "441100";
				$hexend   = "FFAA44";		
				break;
			
			case "red":
				$hexstart = "330000";
				$hexend   = "FF0000";
				break;

			case "green":
				$hexstart = "003300";
				$hexend   = "00FF00";
				break;					

			case "blue":
				$hexstart = "000033";
				$hexend   = "00FFFF";
				break;					
				
				
			default: 
				$hexstart = "FF9933";
				$hexend   = "FF9900";
		}
		
		$start = array();
		$end = array();
		
	    $start['r'] = hexdec(substr($hexstart, 0, 2));
	    $start['g'] = hexdec(substr($hexstart, 2, 2));
	    $start['b'] = hexdec(substr($hexstart, 4, 2));
	
	    $end['r'] = hexdec(substr($hexend, 0, 2));
	    $end['g'] = hexdec(substr($hexend, 2, 2));
	    $end['b'] = hexdec(substr($hexend, 4, 2));
	    
	    $step['r'] = ($start['r'] - $end['r']) / ($steps); //-1
	    $step['g'] = ($start['g'] - $end['g']) / ($steps); //-1
	    $step['b'] = ($start['b'] - $end['b']) / ($steps); //-1
	    
	    $gradient = array();

	    $rgb['r'] = floor($start['r'] - ($step['r'] * $idx));
	    $rgb['g'] = floor($start['g'] - ($step['g'] * $idx));
        $rgb['b'] = floor($start['b'] - ($step['b'] * $idx));
        
        $hex['r'] = sprintf('%02x', ($rgb['r']));
        $hex['g'] = sprintf('%02x', ($rgb['g']));
        $hex['b'] = sprintf('%02x', ($rgb['b']));
        
        $gradient = implode(NULL, $hex);
	    
	    return $gradient;
	} 
	
	private function add2str($str, $sep, $val) {
		if ($str == "") {
			return $val;
		} else {
			return $str.=$sep.$val;
		}
	}	
}

?>
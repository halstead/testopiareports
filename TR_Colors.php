<?php

class TR_Colors {

	/**
	*  Colors
	**/
	# Test Case Run Status
	private $rep_cols_stat = array(
					"ERROR"   => "red",
					"IDLE"    => "purple",
					"PASSED"  => "green",
					"FAILED"  => "orange",
					"RUNNING" => "blue",
					"PAUSED"  => "skyblue",
					"BLOCKED" => "yellow",
					"UNDEF"   => "pink");
	# Test Case Run Status
	private $rep_cols_stat_html = array(
					"ERROR"   => "FF0000",
					"IDLE"    => "990066",
					"PASSED"  => "00FF00",
					"FAILED"  => "FF9933",
					"RUNNING" => "0000FF",
					"PAUSED"  => "3366FF",
					"BLOCKED" => "FFFF66",
					"UNDEF"   => "FF3399");
	# Test Case Status
	private $rep_cols_stat2 = array(
					"PROPOSED"  => "blue",
					"CONFIRMED" => "green",
					"DISABLED"  => "purple",
					"UNDEF"     => "pink");
	# Test Case Status
	private $rep_cols_stat2_html = array(
					"PROPOSED"  => "0000FF",
					"CONFIRMED" => "00FF00",
					"DISABLED"  => "990066",
					"UNDEF"     => "FF3399");
	# Test Case Priorities
	private $rep_cols_prio = array(
					"P1"          => "red",
					"P2"          => "orange",
					"P3"          => "yellow",
					"P4"          => "skyblue",
					"P5"          => "blue",
					"ENHANCEMENT" => "green",
					"UNDEF"       => "pink");
	# Test Case Priorities					
	private $rep_cols_prio_html = array(
					"P1"          => "FF0000",
					"P2"          => "FF9933",
					"P3"          => "FFFF66",
					"P4"          => "3366FF",
					"P5"          => "0000FF",
					"ENHANCEMENT" => "00FF00",
					"UNDEF"       => "FF3399");
	# Bug Status
	private $rep_cols_bug_status = array(
					"NEW"		  => "blue",
					"UNCONFIRMED" => "skyblue",
					"ASSIGNED"    => "orange", 
					"REOPENED"    => "red",
					"RESOLVED"    => "green",
					"VERIFIED"    => "yellow",
					"CLOSED"      => "green",
					"UNDEF"       => "pink");					
	# Bug Status					
	private $rep_cols_bug_status_html = array(
					"NEW"		  => "0000FF",
					"UNCONFIRMED" => "3366FF",
					"ASSIGNED"    => "FF9933",
					"REOPENED"    => "FF0000",
					"RESOLVED"    => "33FF33",
					"VERIFIED"    => "00FF00",
					"CLOSED"      => "00FF00",
					"UNDEF"       => "FF3399");
					
	public function getColorPloticus($val, $colors, $type) {
		$newColor = $colors;

		$arr=array();
		switch ($type) {
			case "tcrs" : $arr=$this->rep_cols_stat; break;
			case "tcs"  : $arr=$this->rep_cols_stat2; break;
			case "prio" : $arr=$this->rep_cols_prio; break;
			case "bs"   : $arr=$this->rep_cols_bug_status; break;
		}
		
		if (array_key_exists($val, $arr)) {
			$newColor .= $arr[$val];
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
		}
		
		if (array_key_exists($val, $arr)) {
			return $arr[$val];
		} else {
			return $arr["UNDEF"];
		}		
		
	}

	function add2str($str, $sep, $val) {
		if ($str == "") {
			return $val;
		} else {
			return $str.=$sep.$val;
		}
	}	
}

?>
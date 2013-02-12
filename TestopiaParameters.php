<?php
/**
 * The testopia parameters object
 */

/**
 * Copyright (C) 2009 - Andreas Müller
 *
 * This program is free software; you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) 
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for    
 * more details.
 * 
 * You should have received a copy of the GNU General Public License along 
 * with this program; if not, see <http://www.gnu.org/licenses>.
 */
 
class TestopiaParameters {
	
	private $context;
	private $error;
	
	# assigned Parameter values
	private $parameters=array();
	# specific report options (individual per report)
	private $reportOptions= array();
	# default values for general parameters
	private $defaults=array(
				"versions"    => "",
				"zebra"       => "true",
				"total"       => "true",
				"sortable"    => "false",
				"hidetable"   => "false",
				"debug"       => "false",
				"chartheight" => 120,
				"chartwidth"  => "auto",
				"nudechart"   => "false",
				"chartpos"	  => "right",
				"chartlayout" => "vertical",
				"showhide"    => "false",
				"hidden"      => "false",
				"title"		  => "true",
				"roundperc"	  => 0
			);
	# allowed parameters values / types	
	private $values= array(
				"versions"		=> array("value" => "alphanumeric"),#EDITED: Added version variable
				"report_id"		=> array("value" => "alphanumeric"),
				"run_id"		=> array("value" => "numeric"),
				"plan_id"		=> array("value" => "numeric"),
				"zebra"			=> array("value" => "boolean"),
				"total"			=> array("value" => "boolean"),
				"sortable"		=> array("value" => "boolean"),
				"charttype"		=> array("value" => "list"),
				"chart"			=> array("value" => "alphanumeric"),
				"debug"			=> array("value" => "boolean"),
				"hidetable"		=> array("value" => "boolean"),
				"chartheight"	=> array("value" => "numeric"),
				"chartwidth"	=> array("value" => "numeric"),
				"nudechart"		=> array("value" => "boolean"),
				"chartpos"		=> array("value" => "list"),
				"chartlayout"	=> array("value" => "list"),
				"showhide"		=> array("value" => "boolean"),
				"hidden"		=> array("value" => "boolean"),
				"title"			=> array("value" => "boolean"),
				"reportoptions" => array("value" => "alphanumeric"),
				"roundperc"		=> array("value" => "numeric"),
			);		
	# allowed parameters (general)			
	private $supported_parameters=array(
				"report_id",
				"run_id",
				"plan_id",
				"zebra",
				"total",
				"sortable",
				"charttype",
				"chart",	
				"debug",
				"hidetable",
				"chartheight",
				"chartwidth",
				"nudechart",
				"chartpos",
				"chartlayout",
				"showhide",
				"hidden",
				"title",
				"reportoptions",
				"roundperc",
				"versions"
			);
	
	# Constants for accessing the parameters
	public static $Param_RunID  = "run_id";
	public static $Param_PlanID = "plan_id";
	public static $Param_Versions = "versions";
	/**
	 * 	Constructor
	 */
	function TestopiaParameters( $args, $context) {
		$this->setContext( $context );
		$this->extractOptions( $args );	
	}
	
	/**
	 *	Set the context of the parameter class
	 */		
	function setContext($context) {
		$this->context = $context;
	}
	
	/**
	 *	Get error for parameter class
	 */		
	function getError() {
		return $this->error;
	}

	/**
	 *	Set error for parameter class
	 */		
	function setError( $error) {
		$this->error = $error;
	}

	/**
	 *	Get the default value for one parameter
	 */	
	function getDefault( $param ) {
		if (array_key_exists($param,$this->defaults)) {
			return $this->defaults[$param];
		} 
		return "";
	}
	
	/**
	 *	Checking if the given value for a parameter is valid
	 */	
	private function allowedValue($name, $value) {
		$allowedValue = strtolower($this->values[$name]["value"]);
		
		switch ($allowedValue) {
			case "boolean": if ($value == "true" or $value == "false") {
								return true;
							} else { 
								return false;
							} 
							break;
			case "numeric": foreach(explode(",",$value) as $val) {
								if (!is_numeric($val)) {
									return false;
								}
							}
							return true;
							break;								 
			default: return true;
		}
	}
	
	/**
	 * Extract options from arguments
	 *
	 * @param string $args function arguments
	 */
	private function extractOptions( $arguments ) {
		foreach( $arguments as $line ) {
			if( strpos( $line, '=' ) == false ) continue;
			list( $name, $value ) = explode( '=', $line, 2 );
			$value=strtolower(trim($value));
			$name=strtolower(trim($name));
			
			if (!in_array($name,$this->supported_parameters)) {
				$this->setError($this->context->getErrorMessage('trReport_unsupported_parameter',$name,$this->getParameterList()));
			} else {
				if ($this->allowedValue($name,$value)) {
					$this->set($name,$value);
					$this->context->getDebug()->add("extractOptions:".$name."=".$value);	
					
					# if reportoptions extract them
					if ($name == "reportoptions") {
						$this->getReportOptions($value);
					}
				} else {
					# incorrect value
					$this->setError($this->context->getErrorMessage('trReport_wrong_value',$value,$name));
				}			
			}
		}
		
		$this->setDefaults();
	}	
	
	private function getReportOptions($value) {
		$options = explode(",",$value);
		foreach ($options as $option) {
			$optionValue = explode("=",$option);
			if (count($optionValue) == 2) {
				$this->set($optionValue[0], $optionValue[1], true);
				$this->context->getDebug()->add("getReportOptions:".$optionValue[0]."=".$optionValue[1]);	
			} else {
				$this->setError($this->context->getErrorMessage('trReport_error_report_option',$option));	
			}
		}
	}
	
	/**
	 *	Setting the default values for parameters that were not provided by the user
	 */
	private function setDefaults() {
		foreach ($this->supported_parameters as $param) {
			if (!array_key_exists($param,$this->parameters) and array_key_exists($param,$this->defaults)) {	
				$this->set($param, $this->defaults[$param]);
				$this->context->getDebug()->add("setDefaults:".$param."=".$this->defaults[$param]);
			}
		}	
	}
	
	/**
	 *	Getting a list (for user output) of valid parameters
	 */
	private function getParameterList() {
		$list="";
		foreach ($this->supported_parameters as $param) {
			if ($list=="") {
				$list.=$param;
			} else {
				$list.=", ".$param;
			}
		}
		
		return $list;
	}
	
	/**
	 *	Set parameter value
	 */
	public function set($name,$value,$reportOption = false) {
		if (!$reportOption) {
			$this->parameters[strtolower($name)]=$value;
		} else {
			$this->reportOptions[strtolower($name)]=$value;
		}
	}	

	/**
	 *	Get parameter value
	 */
	public function get($name,$reportOption = false,$defaultreportoption = NULL) {
		if (!$reportOption) {
			if (array_key_exists(strtolower($name),$this->parameters)) {
				return $this->parameters[strtolower($name)];				
			} else {
				return NULL;
			}
		} else {
			if (array_key_exists(strtolower($name),$this->reportOptions)) {
				return $this->reportOptions[strtolower($name)];				
			} else {
				return $defaultreportoption;
			}		
		}
	}	
} 
?>
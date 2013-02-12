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
	
	#
	# Parameter values
	#
	private $parameters=array();
	private $defaults=array(
				"zebra"       => true,
				"total"       => true,
				"sortable"    => true,
				"hidetable"   => false,
				"debug"       => false,
				"chartheight" => 100,
				"chartwidth"  => "auto"
			);
	private $supported_parameters=array(
				"report_id",
				"run_id",
				"plan_id",
				"zebra",
				"total",
				"sortable",
				"chart",
				"debug",
				"hidetable",
				"chartheight",
				"chartwidth",
				"charts"
			);
	/**
	* Constructor
	*/
	function TestopiaParameters( $args, $context) {
		$this->setContext( $context );
		$this->extractOptions( $args );	
	}
	
	function setContext($context) {
		$this->context = $context;
	}
	
	function getError() {
		return $this->error;
	}

	function setError( $error) {
		$this->error = $error;
	}

	function getDefault( $param ) {
		return $this->defaults[$param];
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
				$this->set($name,$value);
			}
		}
		
		$this->setDefaults();
	}	
	
	private function setDefaults() {
		foreach ($this->supported_parameters as $param) {
			if (!array_key_exists($param,$this->parameters) and array_key_exists($param,$this->defaults)) {
				$this->set($param, $this->defaults[$param]);
			}
		}	
	}
	
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
	
	#
	# Set parameter value
	#
	public function set($name,$value) {
		$this->parameters[$name]=$value;
	}	

	#
	# Get parameter value
	#
	public function get($name) {
		if (array_key_exists($name,$this->parameters)) {
			return $this->parameters[$name];				
		} else {
			return NULL;
		}
	}	
} 
?>
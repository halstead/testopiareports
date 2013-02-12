<?php
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
 
class TestopiaDebug {
	
	private $debugs = array();
	private $debugOn = false;
	
	public function TestopiaDebug() {
		$this->debugOn = true;
	}
	
	public function add( $msg ) {
		if ($this->debugOn) {
			$this->debugs[] = $msg;
		}
	}
	
	public function debugOn( $debug ) {
		$this->debugOn = $debug;
	}
	
	public function toHTML() {
		$output = "";
		if ($this->debugOn) {
			foreach ($this->debugs as $debug) {
				$output.="<li>".$debug."</li>";	
			}
		}
		return $output;
	}
}
 
?>
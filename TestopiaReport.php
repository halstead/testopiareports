<?php
/**
 * The testopia report objects
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
 
class TestopiaReport {
	
	#
	# Default max rows for a report 			
	#
	var $maxrowsFromConfig;
	var $maxrowsFromConfigDefault=100;
	
	#
	# db variables
	#
	var $dbdriverDefault="mysql";
	public $dbuser,$bzserver;
	public $database,$host,$password;
	public $dbdriver;
	public $dbencoding;
	public $instanceNameSpace;
	public $parser;
	
	#
	# supported reports
	#
	var $supportedReports=array();
	var $listSupportedReports=array();

	private $params;
	
	function TestopiaReport( &$parser, $arguments ) {
		$this->parser =& $parser;

		$args = $arguments;
		array_shift( $args );
		$this->params = new TestopiaParameters( $args, $this );
	}
	
	/**
	* Dynamically determines which reports are available
	* to add a new report write a new class which is extending TR_Template.php. The name of the new class file must be TR_rep<name>.php. 
	* <name> will be the value for the parameter report_id.
	**/
	private function getReports() {
		global $wgAutoloadClasses, $wgScriptPath, $wgTestopiaReportsIncludes;
	
		$entries = scandir($wgTestopiaReportsIncludes);
		
		foreach ($entries as $entry) {
			if (strstr($entry, "TR_rep")) {
				$len=strlen($entry)-4;
				#cutting of .php
				$parameter=substr($entry,0,$len);
				$class=$parameter;
				#cutting of TR_rep
				$parameter=substr($entry,strlen("TR_rep"),$len-strlen("TR_rep"));
				$parameter=strtolower($parameter);
				$this->supportedReports[$parameter] = $class;
				$this->listSupportedReports[] = $parameter;
				
				$wgAutoloadClasses[$class] = $wgTestopiaReportsIncludes.$entry;
			}
		}
	}
	
	private function getValidReportIDs() {
		$list="";
		foreach ($this->listSupportedReports as $report) {
			if ($list != "") {
				$list.=", ".$report;
			} else {
				$list.=$report;
			}
		}
		return $list;
	}	
	
	public function render() {		
		global $wgTestopiaReports;
		global $wgDBserver,$wgDBname,$wgDBuser,$wgDBpassword;
		
		#
		# first: check for parameter errors
		#
		if ($this->params->getError()) {
			return $this->params->getError();
		}		
		
		#
		# Initialise DB connection
		#
		$this->dbdriver=$this->getProperty("dbdriver",$this->dbdriverDefault);
		$this->dbuser=$this->getProperty("user",$wgDBuser);
		$this->bzserver=$this->getProperty("bzserver", null);
		$this->params->set("bzserver",$this->bzserver);
		$this->dbencoding=$this->getProperty("dbencoding", "UTF-8");
		$this->database=$this->getProperty("database");
		$this->host=$this->getProperty("host");
		$this->password=$this->getProperty("password");
		$this->params->set("ploticus", $this->getProperty("ploticus"));
		$this->params->set("chartdevice", $this->getProperty("chartdevice"));
				
		$connector;
		switch ($this->dbdriver) {
			case "pg" :
				$connector=new TR_PGConnector($this);				
				break;
			default :
				$connector=new TR_MysqlConnector($this);
		}

		#
		# invalidate the cache
		#
		$this->parser->disableCache();
		
		#
		# get reports
		#
		$this->getReports();
		
		#
		# get and set class for report
		#
		$report_id = $this->params->get("report_id");
		if ($report_id != "") {
			if (array_key_exists($report_id, $this->supportedReports)) {
				$report_cls=$this->supportedReports[$report_id];
			} else {
				return $this->getErrorMessage('trReport_unsuported_report',$report_id,$this->getValidReportIDs());
			}
		} else {
			return $this->getErrorMessage('trReport_missing_report',$this->getValidReportIDs());
		}
		
		$report = new $report_cls();
		$report->setConnector( $connector );
		$report->setArgs( $this->params );
		$report->setContext( $this );
		
		#
		#
		#
		global $wgScriptPath;
		$this->parser->mOutput->addHeadItem('<link rel="stylesheet" type="text/css" media="screen, projection" href="' . $wgScriptPath . '/extensions/TestopiaReports/skins/tr_main.css" />');
		
		#
		# render report
		#
		return $report->render();
	}
	
	/**
	* get property from global config parameter
	*/
	function getProperty($name,$default="") {
		global $wgTestopiaReports;
		$value;
		if ($this->instanceNameSpace != null &&
			array_key_exists($this->instanceNameSpace.":".$name,$wgTestopiaReports)) {
			$value=$wgTestopiaReports[$this->instanceNameSpace.":".$name];	
		} elseif (array_key_exists($name,$wgTestopiaReports)) {
			$value=$wgTestopiaReports[$name];
		} else {
			$value=$default;
		}
		return $value;
	}		
	
    public function getErrorMessage($key) {
		$args = func_get_args();
		array_shift( $args );	
		wfLoadExtensionMessages( 'TestopiaReports' );
		return '<strong class="error">TestopiaReports : '. 
			wfMsgForContent($key,$args).'</strong>';	
	}

	public function getMessageText($key) {
		$args = func_get_args();
		array_shift( $args );	
		wfLoadExtensionMessages( 'TestopiaReports' );
		return wfMsgForContent($key,$args);
	}
}
?>
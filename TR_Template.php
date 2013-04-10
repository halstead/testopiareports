<?php
#EDITED: This file was modified to add Test Plan names inside the reports generated for Test Runs.
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED" .

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

abstract class TR_Template {
			
	# connector class
	private $connector;
	# Instance of TestopiaParameters
	private $arguments;
	private $context;
	
	#others
	private $total; 		    #for total line
	private $msgNothingFound;	#if nothing was found - customized message; could be set by report
	private $defaultColorRange = "yellow";
	
	private $OSWin;				# operating system of the server; if Windows = true else false
	private $newline;			# new line character for file output	
	
	# report dimensions
	public $numOfCols;
	public $numOfRows;
	public $columnNames; // array of column names
	public $previousLine;
	
	# local copy of the header; used only if the report is hidden (will be displayed along with the show button)
	public $header; 
	# unique IDs for the div areas for hiding and showing the report
	public $hideID = array(); 
	
	/**
	 * Constructor
	 */
	function TR_Template () {
		$this->OSWin = $this->checkWindows();
		
		if ($this->OSWin == true) {
			$this->setNewline("\r\n");
		} else {
			$this->setNewline("\n");
		}
	}
	
	/**
	 * @param $context = context of this class = TestopiaReport
	 * @return -
	 */
	public function setContext( $context ) {
		$this->context = $context;
	}
	
	/**
	 * @return context class = TestopiaReport
	 */
	public function getContext() {
		return $this->context;
	}
	
	private function checkWindows() {
		if ( strtoupper(substr(php_uname(), 0, 3)) === 'WIN' ) {
			return true;
		} else {
			return false;
		}
	}	
	
	private function setNewline( $str ) {
		$this->newline= $str;
	}

	public function getNewline() {
		return $this->newline;
	}
	
	/**
	 * @param $total = sets the total value for a report
	 */
	private function setTotal( $total ) {
		$this->total = $total;
	}
	
	/**
	 * @return the total value of a report
	 */
	public function getTotal() {
		return $this->total;
	}
	
	function setConnector( $db_connector ) {
		$this->connector=$db_connector;
	}
	
	function getConnector() {
		return $this->connector;
	}
	
	function setArgs( $param ) {
		$this->arguments=$param;
	}

	function getVersions() {
		return $this->arguments->get(TestopiaParameters::$Param_Versions);
	}
	function getArgs() {
		return $this->arguments;
	}
	
#TO-DO: This needs documenting:
	function getRunID() {
		$runid = $this->arguments->get(TestopiaParameters::$Param_RunID);
		if ($runid != "-1") { return $runid; }
		else {
			$products =  explode(", ", $this->getProduct());
			$products_counter = 0;
			$testplans =  explode(", ", $this->getTestplan());
			$testplans_counter = 0;
			$environments =  explode(", ", $this->getEnvironment());
			$environments_counter = 0;
			$builds =  explode(", ", $this->getBuild());
			$builds_counter = 0;
			$insummarys =  explode(", ", $this->getInSummary());
			$insummarys_counter = 0;
$sql = new TR_SQL;
$sql->setConnector($this->getConnector());
$sql->setFrom("test_runs");
$sql->addField("test_runs", "summary", "max_summary", "MAX($1)");
$sql->addWhere("test_runs", "summary", " REGEXP ", "\"^20[0-9]{2}\"");
$sql->addWhere("test_runs", "summary", " NOT LIKE ", "\"%TEMPLATE%\"", "AND");
$result_ =  $this->getConnector()->execute($sql->toSQL());
$result = $this->getConnector()->fetch($result_);
$max_summary = substr($result["max_summary"],0,9);

$sql = new TR_SQL;
$sql->setConnector($this->getConnector());
$sql->setFrom("test_runs");
$sql->addField("test_runs", "run_id");
$sql->addJoin("Inner", "=", "test_plans", "plan_id", "test_runs", "plan_id");
$sql->addJoin("Inner", "=", "products", "id", "test_plans", "product_id");
$sql->addJoin("Inner", "=", "test_environments", "environment_id", "test_runs", "environment_id");
$sql->addJoin("Inner", "=", "test_builds", "build_id", "test_runs", "build_id");
$where_counter = 0;
while ($products_counter < count($products)) {
if ($where_counter == 0) { $operator = ""; $where_counter ++; } else { $operator = "OR"; }
$sql->addWhere("products", "name", " LIKE ", "\"%$products[$products_counter]%\"", $operator);
$sql->addWhere("test_plans", "name", " LIKE ", "\"%$testplans[$testplans_counter]%\"", "AND");
$sql->addWhere("test_environments", "name", " LIKE ", "\"%$environments[$environments_counter]%\"", "AND");
$sql->addWhere("test_builds", "name", " LIKE ", "\"%$builds[$builds_counter]%\"", "AND");
if (strcmp($insummarys[$insummarys_counter],"max") == 0) {
$sql->addWhere("test_runs", "summary", " LIKE ", "\"%$max_summary%\"", "AND");
} else {
$sql->addWhere("test_runs", "summary", " LIKE ", "\"%$insummarys[$insummarys_counter]%\"", "AND");
}
$insummarys_counter ++;
    if ($insummarys_counter > (count($insummarys)-1)) { $builds_counter ++; $insummarys_counter = 0; }
    if ($builds_counter > (count($builds)-1)) { $environments_counter ++; $builds_counter = 0; }
    if ($environments_counter > (count($environments)-1)) { $testplans_counter ++; $environments_counter = 0; }
    if ($testplans_counter > (count($testplans)-1)) { $products_counter ++; $testplans_counter = 0; }
}
$results =  $this->getConnector()->execute($sql->toSQL());
$runid = "";
while ($result = $this->getConnector()->fetch($results)) {
$runid .= $result["run_id"].", ";
}
if ($runid == NULL) {return "0";}
else {
return substr($runid, 0, -2);
}
}
}
	function getProduct() {
		return $this->arguments->get(TestopiaParameters::$Param_Product);
	}
	function getTestplan() {
		return $this->arguments->get(TestopiaParameters::$Param_Testplan);
	}
	function getEnvironment() {
		return $this->arguments->get(TestopiaParameters::$Param_Environment);
	}
	function getBuild() {
	return $this->arguments->get(TestopiaParameters::$Param_Build);
	}
	function getInSummary() {
		return $this->arguments->get(TestopiaParameters::$Param_InSummary);
	}
	function getPlanID() {
		return $this->arguments->get(TestopiaParameters::$Param_PlanID);
	}
	
	public function setMsgNoResultsFound( $msg ) {
		echo $this->msgNothingFound;
	}
	
	/**
	 * @return Message if nothing was found for a report
	 */
	public function emptyResult() {
		if ( $this->msgNothingFound == "" ) {
			return "<p>".$this->context->getMessageText("trReport_no_results_found")."</p>";
		} else {
			return "<p>".$this->msgNothingFound."</p>";
		}
	}
	
	/**
	 * Central function for percentage calculation, takes the parameter "roundperc" for 
	 * rounding the percentage
	 * @param $value = the current value
	 * @param $total = the total value; if -1 the value will be fetched using getTotal()
	 * @param $addperc = optional. If set to true "%" will be added to the output
	 * @return the percentage
	 */
	public function getPerc($value, $total = -1, $addPerc = true) {
		if ($total == -1) {
			$total = $this->getTotal();
		}
		if ($total == 0) {
			$perc = 0;
		} else {
			$round = $this->getArgs()->get("roundperc");
			$perc = round($value/$total * 100, $round);
		}
		
		if ($addPerc) {
			$perc.="%";
		}
		
		return $perc;
	}
	
	/**
	 * Converts a given SQL result set into an array
	 * @param $result = SQL result set
	 * @return array
	 */
	private function convertResultToArray($result) {
		$output = array();
		$this->numOfRows=0;
		$done = false;
		while ($line = $this->connector->fetch($result)) {
			$output[] = $line;
			$this->numOfRows++;
			
			if (!$done) {
				$this->numOfCols = count($line);
				$this->columnNames = array();
				while (current($line) !== false) {
					$this->columnNames[] = key($line);
					next($line);
				}
				reset($line);
				$done = true;
			}
		}

		return $output;
	}
	
	/**
	 * Main routine that will be called to produce the report
	 * @return the rendered report
	 */
	public function render() {		
		#init of HTML output
		$output = "";
		
		# init the db connection
		$connected = $this->getConnector()->connect();
		if (!$connected) {
			return $this->getConnector()->getError();
		}
		
		#some more init stuff for the report
		$this->init();
		
		# get SQL for query
		# must be implemented in the class extending this
		$this->sql = $this->getSQL();

		$this->context->getDebug()->add($this->sql);
		
		# execute the query
		$result = $this->getConnector()->execute($this->sql);
		
		if ($this->getConnector()->getError() != "") {
			return $this->getConnector()->getError();
		}
		
		if (!$result or $this->getConnector()->getRowCount($result)==0) {
			$output .= $this->emptyResult();
		} else {
			# convert the result set into an array
			$resultArr = $this->convertResultToArray($result);
			
			#clean up
			$this->getConnector()->free($result);		
			
			# get the output for the report
			# must be implemented in the class extending this class
			$output .= $this->getReport( $resultArr );			
		}
		
		#local copy of the header; used only if the report is hidden (will be displayed along with the show button)
		$this->header = $this->getReportHeader();
		
		#close the connection
		$this->getConnector()->close();	
		
		#returning the output
		return $output;	
	}
	
	public function calcTotal( $result ) {		
		# field for calculating total
		$fldTotal = $this->arguments->get("total");
		if (!$fldTotal) {
			return;
		}

		if ($this->arguments->get("total") == "rowcount") {
			$numTotal=$this->numOfRows;
		} else {
			$numTotal = 0;	
			foreach ($result as $line) {
				$i=0;
				foreach ($line as $col_value) {
					$field_name = $this->columnNames[$i];
					
					# for calculating the total
					if ($fldTotal == $field_name) {
						$numTotal += $col_value;
					}
					
					$i++;
				}
			}
		}

		$this->setTotal($numTotal);
	}

	/**
	 *	renders the header of the report
	 */	
	private function renderPlainHTMLHeader( $result ) {
		return $this->getReportHeader();
	}
		
	/**
	 *	get translated field name; if no translation is found the field name will be used instead
	 */
	private function getFieldName($meta) {
		$msgKey = "trField_".$meta->table.".".$meta->name;
		$msg = $this->getContext()->getMessageText($msgKey);
		if (strpos($msg, "trField_") != false) {
			$msg = $meta->name;
		}
		return $msg;
	}
	
	/**
	 *	renders the body of the report (data table)
	 */	
	public function renderPlainHTMLBody( $result ) {
		# somehting to output?
		if ($this->numOfRows == 0) {
			return $output;
		}
		
		#get column formats
		$cformat = $this->getColumnFormats();
		
		#init of output
		$output = "";
		
		# field for calculating total
		$fldTotal = $this->arguments->get("total");
		$numTotal = 0; 		
			
		
		# wrap the body in a div
		$output.="<div>"; 
		
		#
		# start of inner table (data table)
		#
		if ($this->arguments->get("sortable")=="true") {
			$output .= "<table class=\"testopia_Table_Data sortable\" style=\"margin:10px\">";
		} else {
			$output .= "<table class=\"testopia_Table_Data\" style=\"margin:10px\">";
		}
				
		#
		# output of headings for data table
		#
		$output .= "<tr>";
		for($i = 0; $i < $this->numOfCols; $i++) {
			
			# get translated field name
			//$fieldname = $this->getFieldName($meta);
			
			$cell=$this->renderCell("header",$i, $this->columnNames[$i], $this->columnNames[$i], 0, null);
			if ($cell=="") {
				$output .= "<th><b>".htmlentities($this->columnNames[$i])."</b></th>";
			} else {
				$output .= $cell;
			}			
		}
		$output .= "</tr>"; 
		
		#
		# Create table data rows
		#
		$even=false;
		$color="";
		$lineNo=0;
		foreach($result as $line) {
			#alternating row colors needed?
			if (($even) and ($this->getArgs()->get("zebra"))) {
				$color="class=\"testopia_CellDark\"";
			} else {
				$color="class=\"testopia_CellLight\"";
			}
			$even=!$even;

			#call custom function for inserting new lines
			$output.=$this->newDataLine($line);
			
			#call custom function for starting new lines
			$newLine = $this->newLineBegin($line);
			if ($newLine == "") {
				$output .= "<tr ".$color.">";
			} else {
				$output .= $newLine;
			}
			
			# save the old line in a public variable for comparison
			$this->previousLine = $line;
			
			$i=0;			
			foreach ($line as $col_value) {
				$field_name = $this->columnNames[$i];
			    
				#
				# call custom function for cell rendering
				#
				$cell=$this->renderCell("body",$i, $field_name, $col_value, $lineNo, $line);
				if ($cell == "") {
					#get formatting for column
					$format = "";
					if (isset($cformat) and array_key_exists($field_name, $cformat)) {
						$format = $cformat[$field_name];
					}
					$output .= "<td style=\"".$format."\">".htmlentities($col_value)."</td>";
				} else {
					$output .= $cell;
				}
				
				# for calculating the total
				if ($fldTotal == $field_name) {
					$numTotal += $col_value;
				}
				$i++;
			}
			$output .= "</tr>";
					
			$lineNo++;
		}
		
		#insert totals if requiered
		if ($this->arguments->get("total") and $this->numOfRows > 0) {		
			if ($this->arguments->get("total") == "rowcount") {
				$total=$this->numOfRows;
				$output .= "<tr class=\"sortbottom\"><td class=\"testopia_Total\" colspan=\"".$this->numOfCols."\"><b>Total: ".$total."</b></td></tr>";
				# set total value
				$this->setTotal($numTotal);
			} else {
				$output .= "<tr class=\"sortbottom\"><td class=\"testopia_Total\" colspan=\"".$this->numOfCols."\"><b>Total: ".$numTotal."</b></td></tr>";
				# set total value
				$this->setTotal($numTotal);
			}
		}			
		
		# End of body inner table tag
		$output .= "</table>";	

		return $output;
	}
	
	private function renderPlainHTMLFooter( $result ) {
		return $this->getReportFooter();
	}
	
	private function renderPlainHTMLChart( $result ) {
		$output = "";
		if ($this->getArgs()->get("chart") != "") {
			$charts = explode(",",$this->getArgs()->get("chart"));
		} else {
			$charts = array();
		}
		$types = explode(",",$this->getArgs()->get("charttype"));
		$device = $this->getArgs()->get("chartdevice");
		
		# chart required
		if ($this->getArgs()->get("charttype") != "") {
			
			# charts are supported
			if (!$this->areChartsSupported($device)) {
				return $this->context->getErrorMessage('trReport_charts_not_supported');
			}
			
			# check if the types are valid
			foreach ($types as $type) {
				if (!$this->isChartTypeSupported($device, $type)) {
					return $this->context->getErrorMessage('trReport_chart_type_not_supported',$type,$this->getSupportedChartTypes($device));
				}				
			}
			# check if the charts are supported
			if (empty($charts) == false) {
				foreach ($charts as $chart) {
					if (strlen($chart) > 0 and !$this->isChartSupported($chart)) {
						return $this->context->getErrorMessage('trReport_chart_not_supported',$chart,$this->getSupportedCharts());
					}				
				}
			}
		} else {
			return "";
		}
	
		$output .= $this->renderChart( $result, $types, $charts ); 
		
		return $output;
	}
	
	/**
	* for rendering the HTML output
	*/
	public function renderPlainHTML( $result ) {		
		# Init output
		$output = "";
		$footer = "";
		$header = "";
		$body   = "";
		$chart  = "";
		$table  = false;
		
		#
		#layout parameters
		#
		$hide = $this->getArgs()->get("hidetable");
		$nude = $this->getArgs()->get("nudechart");
		if ($nude == "true") {
			$hide = "false";
		}
		
		#
		#get the elements
		#
		if ($nude == "false") { 
			$header = $this->renderPlainHTMLHeader( $result );
			
			if ($hide == "false") { 
				$body = $this->injectBody( $result ); 
			}
			
			$footer = $this->renderPlainHTMLFooter( $result );
		}
		$chart = $this->renderPlainHTMLChart( $result );

		#
		# define the body part of the table
		#
		$colspan=1;
		$bodypart="";
		if ($chart != "") {
			if ($body == "") {
				if ($nude == "true") {
					$bodypart = $chart;
				} else {
					$bodypart = "<tr><td class=\"testopia_Background\">".$chart."</td></tr>";
				}
			} else {
				switch ($this->getArgs()->get("chartpos")) {
					case "left"   : $bodypart="<tr><td class=\"testopia_Background\">".$chart."</td><td class=\"testopia_Background\">".$body."</td></tr>"; $colspan=2; break;
					case "top"    : $bodypart="<tr><td class=\"testopia_Background\">".$chart."</td></tr><tr><td class=\"testopia_Background\">".$body."</td></tr>";  break;
					case "right"  : $bodypart="<tr><td class=\"testopia_Background\">".$body."</td><td class=\"testopia_Background\">".$chart."</td></tr>"; $colspan=2; break;
					case "bottom" : $bodypart="<tr><td class=\"testopia_Background\">".$body."</td></tr><tr><td class=\"testopia_Background\">".$chart."</td></tr>"; break;
					case "default": $bodypart="<tr><td class=\"testopia_Background\">".$body."</td><td class=\"testopia_Background\">".$chart."</td></tr>"; $colspan=2; break;
				}
			}
		} else {
			$bodypart = "<tr><td>".$body."</td></tr>";
		}

		#start of table
		if ($header != "" or $footer != "") {
			$output .= "<table class=\"testopia_Table\">";
			if ($header != "") {
				if ($this->getArgs()->get("showhide") == "false") {
					$output.= "<tr><th colspan=\"".$colspan."\">".$header."</th></tr>";
				} else {
					#calculate the GUIDs for div for showing/hiding
					$this->hideID[1] = md5(uniqid(rand()));
					$this->hideID[2] = md5(uniqid(rand()));
					$this->hideID[3] = md5(uniqid(rand()));
					$this->hideID[4] = md5(uniqid(rand()));
					
					if ($this->getArgs()->get("hidden") == "true") {
						$display = "none";
					} else {
						$display = "inline";
					}
					
					$output.= "<tr><th colspan=\"".$colspan."\"><div id=\"".$this->hideID[1]."\"";
					$output.= "style=\"display:".$display."\" align=\"left\"><div style=\"float:left;\"><input type=button ";
					$output.="onClick=\"hide('".$this->hideID[3]."');hide('".$this->hideID[1]."');";
					$output.="show('".$this->hideID[2]."');show('".$this->hideID[4]."');\" ";
					$output.="value=\"".$this->context->getMessageText("trReport_hide")."\"></div>";
					$output.= "<div style=\"float:right; width:90%; text-align:center\">".$header."</div></div></th></tr>";
				}
			}
		}		
		
		$output.=$bodypart;
		
		#end of table
		if ($header != "" or $footer != "") {
			if ($footer != "") {
				$output.= "<tr><td colspan=\"".$colspan."\">".$footer."</td></tr>";
			}
			$output .= "</table>";
		}		

		return $output;
	}
		
	function renderChart( $result, $types, $charts ) {
		$output = "";
		
		if (!$types) {
			return $this->context->getErrorMessage('trReport_chart_no_type');
		}
				
		if ($this->getArgs()->get("chartdevice") == "google") {
			$output = $this->renderGoogle( $result, $types, $charts );
		}
		
		return $output;
	}
		
	function renderGoogle( $result, $types, $charts ) {
		$output = "";
		$link = "";
		$chartType = "";
		$heights = explode(",",$this->getArgs()->get("chartheight"));
		$widths = explode(",",$this->getArgs()->get("chartwidth")); 
		$setTitle = $this->getArgs()->get("title");
		
		if (empty($charts) == false) {
			$chartlayout = $this->getArgs()->get("chartlayout");
			
			$columns = 0;				
			if ($chartlayout != "horizontal" and $chartlayout != "vertical" and is_numeric($chartlayout)) {
				$columns = $chartlayout;
			}
			
			if ($chartlayout == "horizontal" or $columns > 0) {
				$output .= "<table class=\"testopia_Table_Chart\"><tr>";
				$chartlayout = "horizontal";
			}			
			
			$columncount = 1;
			$index = 0;
			foreach ($charts as $chart) {
				$link="";
				
				#get chart type
				if (array_key_exists($index, $types)) {
					$type = $types[$index];
				} else {
					$type = end($types);
				}
				
				#get chart width
				if (array_key_exists($index, $widths)) {
					$width = $widths[$index];
				} else {
					$width = $this->getArgs()->getDefault("chartwidth");
				}	
				
				#get chart height
				if (array_key_exists($index, $heights)) {
					$height = $heights[$index];
				} else {
					$height = $this->getArgs()->getDefault("chartheight");
				}
				
				$index++;
				
				switch ($type) {
					case "bar"   : $chartType = "bvg"; break;
					case "pie"   : $chartType = "p"; break;
					case "pie3"  : $chartType = "p3"; break;
					case "meter" : $chartType = "gom"; break;
				}
				
				# setup the chart
				$google = new GoogleChart;
				$google->setType($chartType);
				$google->setDefaultHeight($this->getArgs()->getDefault("chartheight"));
				$google->setDefaultWidth($this->getArgs()->getDefault("chartwidth"));	
				$google->setWidth($width);
				$google->setHeight($height);				
				
				$suppCharts = $this->getSupportedChartsArr();
				if ($setTitle == "true") {
					$google->setTitle($suppCharts[$chart]["title"]);
				}
				$google->setTooltip($suppCharts[$chart]["tooltip"]);
				$google->setAltText($suppCharts[$chart]["alttext"]);
				
				if ($type == "bar") { $google->hideLabels( true ); }
				if ($type == "pie" or $type == "pie3") { $google->hideLegend( true ); }					
				
				$chart=strtolower(trim($chart));
				$this->getGoogleChartLink($google, $result, $type, $chart);
				
				# debug
				$this->context->getDebug()->add("renderGoogle:".$google->toURL());
				
				# create the final link
				$link=$google->toImg();
				
				switch ($chartlayout) {
					case "horizontal": 
						$output.="<td>".$link."</td>"; 
						$columncount++; 
						if ($columns > 0) {
							if ($columncount > $columns) {
								$columncount = 1;
								$output.="</tr><tr>";
							}
						}
						break;
					case "vertical"  : $output.=$link."<br />"; break;
				}
				
				$google = null;
			}
			
			if ($chartlayout == "horizontal") {
				$output .= "</tr></table>";
			}
			
		} else {

			$type = $types[0];
			
			switch ($type) {
				case "bar"   : $chartType = "bvg"; break;
				case "pie"   : $chartType = "p"; break;
				case "pie3"  : $chartType = "p3"; break;
				case "meter" : $chartType = "gom"; break;
				case "line"  : $chartType = "ls"; break;
			}
						
			$google = new GoogleChart;
			$google->setType($chartType);
			$google->setDefaultHeight($this->getArgs()->getDefault("chartheight"));
			$google->setDefaultWidth($this->getArgs()->getDefault("chartwidth"));	
			$google->setWidth($this->getArgs()->get("chartwidth"));
			$google->setHeight($this->getArgs()->get("chartheight"));
		
			if ($type == "bar") { $google->hideLabels( true ); }
			if ($type == "pie" or $type == "pie3") { $google->hideLegend( true ); }				
			
			$this->getGoogleChartLink( $google, $result, $type, null);
			
			# debug
			$this->context->getDebug()->add("renderGoogle:".$google->toURL());
			
			# create the final link
			$output=$google->toImg();
			
			$google = null;
		}
		
		return $output;
	}
	
	function getGoogleChartOneRowCount( &$google, $result, $type, $chart, $field, $field2count, $col, $colorrange) {
		$color = new TR_Colors;
		
		$label="";
		$total=0;
		
		if ($col == "auto" and $colorrange == "") {
			$colorrange = $this->defaultColorRange;
		}
		
		if (!$this->getTotal()) {
			$this->calcTotal( $result );
			$total=$this->getTotal();
		} else {
			$total = $this->getTotal();
		}
		
		$this->context->getDebug()->add("getGoogleChartOneRowCount:".$total);
		
		$bars=array();
		$legend=array();
		$numOfBars = 0;
		foreach ($result as $line) {
			$counter = $line[$field];
			
			if (array_key_exists($counter,$bars)) {
				if ($field2count != "") {
					$bars[$counter] = $bars[$counter] + $line[$field2count];
				} else {
					$bars[$counter]++;
				}
			} else {
				if ($field2count != "") {
					$bars[$counter] = $line[$field2count];
				} else {
					$bars[$counter]=1;
				}
				$legend[]=$counter;
				if ($col != "" and $col != "auto") {
					$google->addColor($color->getColorHTML($counter, $col));			
				}
				$numOfBars++;
			}
		}
		
		$google->setDataMinRange(0);
		
		$i=0;
		foreach ($bars as $bar) {
			$google->addData($bar);
			$label=$legend[$i]."(".$bar.")";
			$label.=$this->getPerc($bar,$total);
			if ($col == "auto") {
				$google->addColor($color->autoColorHTML($colorrange, $i, $numOfBars));
			}
			$google->addLabel($label);
			$google->addLegend($label);
			$i++;
		}		
	}		
	
	function areChartsSupported( $device ) {
		$devices = $this->getSupportedChartTypesArr();
		
		if (array_key_exists( $device, $devices )) {
			return true;
		} else {
			return false;
		}
	}

	function isChartTypeSupported( $device, $type ) {
		$devices = $this->getSupportedChartTypesArr();
		
		if (array_key_exists( $device, $devices )) {
			$types = $devices[$device];
			if (in_array( $type, $types )) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function getSupportedChartTypes( $device ) {
		$output="";
		$devices = $this->getSupportedChartTypesArr();
		if (array_key_exists( $device, $devices )) {
			$types = $devices[$device];
			foreach ($types as $type) {
				$output = $this->add2str($output, ", ", $type);
			}
		} else {
			return "-";
		}
	}
	
	function isChartSupported( $chart ) {
		$charts = $this->getSupportedChartsArr();
		if (array_key_exists( $chart, $charts )) {
			return true;
		} else {
			return false;
		}
	}
	
	function getSupportedCharts () {
		$charts = $this->getSupportedChartsArr();
		$output="";
		if ($charts) {
			foreach ($charts as $chart) {
				$output = $this->add2str($output, ", ", $chart["parameter"]." (".$chart["title"].")");
			}
		} else {
			return "-";
		}
		
		return $output;
	}
	
	public function getStandardRunHeader() {
####################
#EDITED: This next block gets the test plan name and introduces it in the test run header
		$run_id = $this->getArgs()->get("run_id");
if ((strpos($run_id, ",") !== false) || ($run_id < 0)) {
	return $this->getReportName()."<br />";
}
else {
		$sql="SELECT ".$this->getConnector()->getTable("test_plans").".name FROM ".$this->getConnector()->getTable("test_plans");
                $sql.=" JOIN ".$this->getConnector()->getTable("test_runs");
                $sql.=" ON ".$this->getConnector()->getTable("test_runs").".plan_id = ".$this->getConnector()->getTable("test_plans").".plan_id";
                $sql.=" WHERE run_id = ".$run_id.";";
		$_result = $this->getConnector()->execute($sql);
		if ($_result) {
			$_line = $this->getConnector()->fetch($_result);
			$_title = htmlentities($_line["name"]);
			$this->getConnector()->free($_result);
		} else {
			$_title = "";
			$_line = "";
		}
###################





#		$run_id = $this->getArgs()->get("run_id");
		
		$sql = "SELECT summary FROM ".$this->getConnector()->getTable("test_runs")." WHERE run_id = ".$run_id.";";
		$result = $this->getConnector()->execute($sql);
		
		if ($result) {
			$line = $this->getConnector()->fetch($result);
			$title = htmlentities($line["summary"]);
			$this->getConnector()->free($result);
		} else {
			$title = "";
			$line = "";
		}
		
#		return $this->getReportName()."<br />"."<a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$run_id."\">Test Run ".$run_id." - ".$title."</a>";
		return $this->getReportName()."<br />"."<a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$run_id."\">".$_title." - ".$title."</a>";#EDITED
}
	}
	
	public function getStandardPlanHeader() {
		$plan_id = $this->getPlanID();
		
		$sql="SELECT ".$this->getConnector()->getTable("test_plans").".name FROM ".$this->getConnector()->getTable("test_plans")." WHERE plan_id = ".$plan_id.";";		
		$result = $this->getConnector()->execute($sql);
		
		if ($result) {
			$line = $this->getConnector()->fetch($result);
			$title = htmlentities($line["name"]);
			$this->getConnector()->free($result);
		} else {
			$title = "";
			$line = "";
		}
		
#		return $this->getReportName()."<br />"."<a href=\"".$this->getArgs()->get("bzserver")."/tr_show_plan.cgi?plan_id=".$plan_id."\">Test Plan ".$plan_id." - ".$title."</a>";
		return $this->getReportName()."<br />"."<a href=\"".$this->getArgs()->get("bzserver")."/tr_show_plan.cgi?plan_id=".$plan_id."\">Test Plan  - ".$title."</a>";

	}	
	
	function add2str($str, $sep, $val) {
		if ($str == "") {
			return $val;
		} else {
			return $str.=$sep.$val;
		}
	}	
	
	#
	# interface rountines (interfaces)
	#
	
	# report stuff
	public function init() {}
	public function newDataLine( $line ) {}
	public function newLineBegin( $line ) {}
	public function getColumnFormats() {}
	public function injectBody( $result ) { return $this->renderPlainHTMLBody($result); }
	
	abstract function getSQL();
	abstract function getReport( $result );
	abstract function getReportHeader();
	abstract function getReportFooter();
	abstract function getReportName();
	public function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {}
	
	# chart stuff
	abstract function getSupportedChartsArr ();
	abstract function getSupportedChartTypesArr ();
	public function getGoogleChartLink( &$google, $result, $type, $chart ) {}
}

?>
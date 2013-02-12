<?php

abstract class TR_Template {
			
	/**
	* Variables
	*/
	# connector class
	private $connector;
	# Instance of TestopiaParameters
	private $arguments;
	private $context;
	
	# Windows?
	private $OSWin;
	private $newline;
	private $total;
	
	private $report_params = array (
		"FIELD_RUN_ID"  => "run_id",
		"FIELD_PLAN_ID" => "plan_id"
	);	
	
	function TR_Template () {
		$this->OSWin = $this->checkWindows();
		
		if ($this->OSWin == true) {
			$this->setNewline("\r\n");
		} else {
			$this->setNewline("\n");
		}
	}
	
	public function setContext( $context ) {
		$this->context = $context;
	}
	
	private function setNewline( $str ) {
		$this->newline= $str;
	}

	public function getNewline() {
		return $this->newline;
	}
	
	private function setTotal( $total ) {
		$this->total = $total;
	}
	
	public function getTotal() {
		return $this->total;
	}
	
	private function checkWindows() {
		if ( strtoupper(substr(php_uname(), 0, 3)) === 'WIN' ) {
			return true;
		} else {
			return false;
		}
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

	function getArgs() {
		return $this->arguments;
	}
	
	function getRunID() {
		return $this->arguments->get($this->report_params["FIELD_RUN_ID"]);
	}

	function getRunIDforWhere() {
		$where="";
		$i=0;
		foreach (explode(",",$this->getRunID()) as $singleValue) {
			if ($i > 0) {
				$where.=" OR run_id = ".$singleValue;
			} else {
				$where = " run_id = ".$singleValue;
			}
		
			$i++;
		}
	}

	function getPlanID() {
		return $this->arguments->get($this->report_params["FIELD_PLAN_ID"]);
	}
	
	function getPlanIDforWhere() {
		$where="";
		$i=0;
		foreach (explode(",",$this->getPlanID()) as $singleValue) {
			if ($i > 0) {
				$where.=" OR plan_id = ".$singleValue;
			} else {
				$where = " plan_id = ".$singleValue;
			}
			
			$i++;
		}
	}
	
	function emptyResult() {
		return "<p>No results found for query!</p>";
	}
	
	/**
	* main routine that will be called to produce the report
	**/
	function render() {
		#init of HTML output
		$output = "";
		
		# init the db connection
		$connected = $this->getConnector()->connect();
		if (!$connected) {
			return $this->getConnector()->getError();
		}

		# get SQL for query
		# must be implemented in the class extending this
		$sql = $this->getSQL();

		# debug output of the SQL query if requested by the "debug=true" parameter
		if ($this->getArgs()->get("debug")) {
			$output = "<p>".$sql."</p>";
		}
		
		# execute the query
		$result = $this->getConnector()->execute($sql);
			
		# get the output for the report
		# must be implemented in the class extending this
		$output = $this->getReport( $result );
		
		#clean up
		$this->getConnector()->free($result);
		$this->getConnector()->close();
		
		#returning the output
		return $output;
	    #return array($output, 'noparse' => true, 'isHTML' => true);	
	}
	
	public function calcTotal( $result ) {
		# seek to the beginning
		$this->connector->seek( $result, 0);
		
		# field for calculating total
		$fldTotal = $this->arguments->get("total");
		if (!$fldTotal) {
			return;
		}

		if ($this->arguments->get("total") == "rowcount") {
			$numTotal=$this->connector->getRowCount($result);
		} else {
			$numTotal = 0;	
			while ($line = $this->connector->fetch($result)) {
				$i=0;
				foreach ($line as $col_value) {
					$field_name = $this->connector->getField($result, $i)->name;
					
					# for calculating the total
					if ($fldTotal == $field_name) {
						$numTotal += $col_value;
					}
					
					$i++;
				}
			}
		}

		$this->setTotal($numTotal);
		
		# seek to the beginning
		$this->connector->seek( $result, 0);
	}
	
	/**
	* for rendering the HTML output
	*/
	public function renderPlainHTML( $result ) {
		#step back to the beginning of the result set
		$this->getConnector()->seek( $result, 0);
	
		# chart requiered
		if ($this->getArgs()->get("chart")) {
			$device = $this->getArgs()->get("chartdevice");
			if ($this->areChartsSupported($device)) {
				$chart = $this->getArgs()->get("chart");
				if (!$this->isChartTypeSupported($device, $chart)) {
					return $this->context->getErrorMessage('trReport_chart_not_supported',$chart,$this->getSupportedChartTypes($device));
				}
			} else {
				return $this->context->getErrorMessage('trReport_charts_not_supported');
			}
		} else {
			$chart = null;
		}
		
		# hide data table
		if ($this->getArgs()->get("hidetable") == "true") {
			$hide=true;
			if (!$chart) {
				$hide=false;
			}
		} else {
			$hide=false;
		}
		
		# field for calculating total
		$fldTotal = $this->arguments->get("total");
		$numTotal = 0; 
		
		# seek to the beginning
		$this->connector->seek( $result, 0);
		# number of columns
		$numCols = $this->connector->getFieldCount($result);
		# number of rows
		$numRowCount = $this->connector->getRowCount( $result );
		
		# Init output
		$output = "";
		
		# start external table for header and footer
		$header=$this->getReportHeader();
		if ($header != "") {
			$wraptable = true;
		} else {
			$wraptable = false;
		}
		
		if ($wraptable) {
			$output .= "<table class=\"testopia_Table\">";
			if ($header != "") {
				$output .= "<tr><th><center>".$header."</center></th></tr>";
			}
			$output .= "<tr><td class=\"testopia_Background\">";
		}
				
		# if chart is requiered introduce an other table for layout
		if ($chart and $hide==false) {
			$output.="<table><tr><td>";
		}
	
		# empty result?
		if ( $numRowCount == 0) {
			$output .= $this->emptyResult();
		} else {
			# hide the data table?
			if ($hide==false) {
				#
				# start of inner table (data table)
				#
				if ($this->arguments->get("sortable")=="true") {
					$output .= "<table class=\"sortable\" style=\"margin:10px\">";
				} else {
					$output .= "<table class=\"testopia_Table\" style=\"margin:10px\">";
				}
				$output .= "<tr>";
				
				#
				# output of headings for data table
				#
				$i = 0;
				while ($i < $numCols) {
					$meta = $this->connector->getField($result, $i);
					$output .= "<th><b>$meta->name</b></th>";

					#if ($meta->name == $fldTotal) {
				#		$idxTotal = $i;
				#	}
					$i++;
				}
				$output .= "</tr>";
				
				#
				# Create table data rows
				#
				$even=false;
				$color="";
				$lineNo=0;
				while ($line = $this->connector->fetch($result)) {
					if (($even) and ($this->getArgs()->get("zebra"))) {
						$color="class=\"testopia_CellDark\"";
					} else {
						$color="class=\"testopia_CellLight\"";
					}
					$even=!$even;

					$output .= "<tr ".$color.">";
					$i=0;			
					foreach ($line as $col_value) {
						$field_name = $this->connector->getField($result, $i)->name;
					
						$cell=$this->renderCell($i, $field_name, $col_value, $lineNo, $line);
						if ($cell == "") {
							$output .= "<td>$col_value</td>";
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
				if ($this->arguments->get("total") and $this->connector->getRowCount( $result ) > 0) {		
					if ($this->arguments->get("total") == "rowcount") {
						$total=$this->connector->getRowCount($result);
						$output .= "<tr><td class=\"testopia_Total\" colspan=\"".$numCols."\"><b>Total: ".$total."</b></td></tr>";
						# set total value
						$this->setTotal($numTotal);
					} else {
						$output .= "<tr><td class=\"testopia_Total\" colspan=\"".$numCols."\"><b>Total: ".$numTotal."</b></td></tr>";
						# set total value
						$this->setTotal($numTotal);
					}
				}			
				
				# End of inner table tag
				$output .= "</table>";
			}
		}
		
		# if chart is requiered finish the cell for the data table
		if ($chart and $hide==false) {
			$output.="</td>";
		}
					
		# render and output chart
		if ($numRowCount > 0 and $chart and $this->getArgs()->get("ploticus")) {
			if ($hide==false) {
				$output.="<td>";
			}

			$output .= $this->renderChart( $result, $chart );
			
			if ($hide==false) {
				$output.="</td></tr></table>";
			}
		}	
		
		# close outer table and footer
		if ($wraptable) {
			$output .= "</td></tr>";
			
			# footer
			
			# close table
			$output .= "</table>";			
		}
	
		return $output;
	}
		
	function renderChart( $result, $type ) {
		$this->getConnector()->seek( $result, 0);
		$output = "";
	
		if ($this->getArgs()->get("chartdevice") == "ploticus") {
			$output = $this->renderPloticus( $result, $type );
		}
		
		if ($this->getArgs()->get("chartdevice") == "google") {
			$output = $this->renderGoogle( $result, $type );
		}
		
		return $output;
	}
	
	function renderPloticus( $result, $type ) {
		global $wgScriptPath;
		
		$output ="";
		
		# setup the filenames for creating the ploticus chart
		$basefile = $this->getPloticusFileBasename()."_".$type;
		# the ploticus file
		$filenamePLO=strtolower($basefile.".plo");
		$plotFile  = "images/testopia_reports/".$filenamePLO;
		# the name of image file for the rendered chart
		$filenameGIF=strtolower($basefile.".gif");
		$imgFile   = "images/testopia_reports/".$filenameGIF;
		
		# delete old stuff if existing
		if (file_exists($plotFile)) {
			unlink($plotFile);
		}
		if (file_exists($imgFile)) {
			unlink($imgFile);
		}
		$plotFileH = fopen($plotFile, 'w') or die("can't open file");
		
		# callback to function for getting the plot data
		$plot = $this->getPloticusData( $result, $type );
		
		# no chart to plot
		if ($plot == null) {
			return "";
		}
		
		#output the plot data
		foreach ($plot as $line){
			fwrite($plotFileH,$line.$this->getNewline());
		}		
		
		fclose($plotFileH);	
		
		# render the chart using ploticus
		shell_exec($this->getArgs()->get("ploticus")." ".$plotFile." -gif");
		
		# create the href for showing the chart
		$src=$wgScriptPath."/".$imgFile;
		$output = "<img src=\"".$src."\" alt=\"Chart could be generated!\">"; 	
		
		return $output;
	}
	
	function renderGoogle( $result, $type ) {
		$output = "";
		$link = "";
		$chartType = "";
		
		$charts = $this->getArgs()->get("charts");
		
		switch ($type) {
			case "bar" : $chartType = "bvg"; break;
			case "pie" : $chartType = "p"; break;
			case "pie3": $chartType = "p3"; break;
		}
		
		if ($charts) {
			foreach (explode(",",$charts) as $chart) {
				if (!$this->isChartSupported( $chart )) {
					return $this->context->getErrorMessage('trReport_chart_not_supported',$chart,$this->getSupportedCharts());
				}
				$google = new GoogleChart;
				$google->setType($chartType);
				$google->setDefaultHeight($this->getArgs()->getDefault("chartheight"));
				$google->setDefaultWidth($this->getArgs()->getDefault("chartwidth"));	
				$google->setWidth($this->getArgs()->get("chartwidth"));
				$google->setHeight($this->getArgs()->get("chartheight"));				
				
				if ($type == "bar") { $google->hideLabels( true ); }
				if ($type == "pie" or $type == "pie3") { $google->hideLegend( true ); }					
				
				$chart=strtolower(trim($chart));
				$this->getGoogleChartLink($google, $result, $type, $chart);
				
				# create the final link
				$link="<img src=\"".$google->toURL()."\" alt=\"Google Chart!\">";
				$output.=$link."<br />";
				$google = null;
			}
		} else {
			$google = new GoogleChart;
			$google->setType($chartType);
			$google->setDefaultHeight($this->getArgs()->getDefault("chartheight"));
			$google->setDefaultWidth($this->getArgs()->getDefault("chartwidth"));	
			$google->setWidth($this->getArgs()->get("chartwidth"));
			$google->setHeight($this->getArgs()->get("chartheight"));
		
			if ($type == "bar") { $google->hideLabels( true ); }
			if ($type == "pie" or $type == "pie3") { $google->hideLegend( true ); }				
			
			$this->getGoogleChartLink( $google, $result, $type, null);
			
			# create the final link
			$output="<img src=\"".$google->toURL()."\" alt=\"Google Chart!\">";
			
			$google = null;
		}
		
		return $output;
	}
	
	function getGoogleChartOneRowCount( &$google, $result, $type, $chart ) {
		$field="";
		$col="";
		
		switch ($chart) {
			case "prio"   : 
					$field="Priority";
					$col="prio";
					break;
			case "bstatus" :
					$field="Status";
					$col="bs";
					break;
			case "status" :
					$field="Status";
					$col="tcrs";
					break;					
			default: return;
		}
		
		$this->getConnector()->seek( $result, 0);
		$color = new TR_Colors;
		
		$label="";
		$total=0;
		
		if (!$this->getTotal()) {
			$this->calcTotal( $result );
			$total=$this->getTotal();
		} else {
			$total = $this->getTotal();
		}
		
		$bars=array();
		$legend=array();
		while ($line = $this->getConnector()->fetch($result)) {
			$prio = $line[$field];
			
			if (array_key_exists($prio,$bars)) {
				$bars[$prio]++;
			} else {
				$bars[$prio]=1;
				$legend[]=$prio;
				$google->addColor($color->getColorHTML($prio, $col));				
			}
		}
		
		$google->setDataMinRange(0);
		
		$i=0;
		foreach ($bars as $bar) {
			$google->addData($bar);
			$label=$legend[$i]."(".$bar.")";
			if ($total) {
				$label.=round($bar/$total*100);
				$label.="%";				
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
				$output = $this->add2str($output, ",", $type);
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
				$output = $this->add2str($output, ",", $chart);
			}
		} else {
			return "-";
		}
		
		return $output;
	}
	
	/**
	* Flat CSV output
	**/	
	function renderCSV( $result ) {
		$output = "";
		
		# create a header row
		$i = 0;
		$output .= "<p>"; 
		while ($i < $this->connector->getFieldCount($result)) {
			$meta = $this->connector->getField($result, $i);
			
			if ($i > 0) {
				$output .= ",".$meta->name;
			} else {
				$output .= $meta->name;
			}
			$i++; 
		}
		$output .= "</p>";

		# Create data rows
		while ($line = $this->connector->fetch($result)) {
			$output .= "<p>";
			$i=0;
			foreach ($line as $col_value){
				
				if ($i > 0) {
					$output .= ",".$col_value;
				} else {
					$output .= $col_value;
				}
				$i++;
			}
			$output .= "</p>";
		}
		
		return $output;
	}	
	
	function getStandardRunHeader() {
		$run_id = $this->getArgs()->get("run_id");
		
		$sql = "SELECT summary FROM ".$this->getConnector()->getTable("test_runs")." WHERE run_id = ".$run_id.";";
		$result = $this->getConnector()->execute($sql);
		
		if ($result) {
			$line = $this->getConnector()->fetch($result);
			$title = $line["summary"];
		}

		return $this->getReportName()."<br />"."<a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$run_id."\">Test Run ".$run_id." - ".$title."</a>";
	}
	
	function add2str($str, $sep, $val) {
		if ($str == "") {
			return $val;
		} else {
			return $str.=$sep.$val;
		}
	}	
	
	#
	# abstract rountines (interfaces)
	#
	
	# report stuff
	abstract function getSQL();
	abstract function getReport( $result );
	abstract function getReportHeader();
	abstract function getReportName();
	abstract function renderCell($colNo, $field_name, $value, $lineNo, $line);
	
	# chart stuff
	abstract function getSupportedChartsArr ();
	abstract function getSupportedChartTypesArr ();
	abstract function getPloticusFileBasename();
	abstract function getPloticusData( $result, $type );
	abstract function getGoogleChartLink( &$google, $result, $type, $chart );
}

?>
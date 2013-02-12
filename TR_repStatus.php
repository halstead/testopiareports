<?php

class TR_repStatus extends TR_Template{

	var $supportedCharts = array();
	
	var $supportedChartTypes = array(
		"google"   => array( "bar", "pie", "pie3" ),
		"ploticus" => array( "bar", "pie" )
	);

	function getSupportedChartsArr() {
		return $this->supportedCharts;
	}
	
	function getSupportedChartTypesArr() {
		return $this->supportedChartTypes;
	}

	/**
	* implementation of the abstract function of the super class
	*/
	function getReport( $result ) {
		# check and set some parameters
		# field for calculating the total number; could be set to a field that is included in the field list of the select statement or to rowcount, which will simply count number of rows	
		if ($this->getArgs()->get("total") == "true") {
			$this->getArgs()->set("total", "Count");
		}
	
		$output = $this->renderPlainHTML( $result );	
		
		#returning the output
	    return $output;
	}
		
	function getReportName() {
		return "Test Case Status Report";
	}
		
	/**
	* Create the report header
	**/
	function getReportHeader() {
		return $this->getStandardRunHeader();
	}

	function renderCell($colNo, $field_name, $value, $lineNo, $line) {
		return "";
	}	
	
	function getSQL() {		
		$con=$this->getConnector();
		
		$sql="";	
		$sql ="SELECT ".$con->getTable("test_case_run_status").".name as Status, count(distinct ".$con->getTable("test_case_runs").".case_id) as Count ";
		$sql.=" FROM ".$con->getTable("test_cases");
		# link the test cases to the run		
		$sql.=" INNER JOIN ".$con->getTable("test_case_runs");  		
		$sql.=" ON ".$con->getTable("test_cases").".case_id = ".$con->getTable("test_case_runs").".case_id";
		# get value for test case run status
		$sql.=" INNER JOIN ".$con->getTable("test_case_run_status");
		$sql.=" ON ".$con->getTable("test_case_runs").".case_run_status_id = ".$con->getTable("test_case_run_status").".case_run_status_id";
	
		$runID = $this->getRunID();
		if ($runID) {
			$sql.=" WHERE run_id = ".$runID;
			$sql.=" GROUP BY name";
		} else {
			$sql.=" GROUP BY run_id, name";
		}
		
		$sql.=" ORDER BY Status";
		$sql.=";";		
		return $sql;
	}	
	
	function getPloticusFileBasename() {
		$name = "Testopia_".$this->getArgs()->get("run_id")."_".get_class($this); 
		
		return $name;
	}

	function getPloticusData( $result, $type ) {
		$plot = array();
		
		$colors = "";
		$color = new TR_Colors;
		
		if ($type == 'pie' or $type == 'pie3') {
			array_push($plot, "#proc getdata");
			array_push($plot, "data:");
			$labels = "labels: ";
			
			while ($line = $this->getConnector()->fetch($result)) {
				$plotData = "";
				$i=0;
				foreach ($line as $col_value) {					
					#labels and colors
					if ($i==0) {
						#$colors = $color->getTestCaseRunStatusColor($col_value, $colors);
						$colors = $color->getColorPloticus($col_value, $colors, "tcrs");
						$labels .= $col_value."\\n(@@PCT%)".$this->getNewline();
					}
					
					#data
					if ($i==1) {
						$plotData .= $col_value." ";
					}
					
					$i++;
				}
				array_push($plot, $plotData);	
			}
			
			array_push($plot, "");
			array_push($plot, "#proc pie");
			array_push($plot, "datafield: 1");
			array_push($plot, "labelmode: line+label");
			array_push($plot, "outlinedetails: none "); #color=black
			array_push($plot, "center: 2 2");
			array_push($plot, "radius: 1");
			array_push($plot, "colors: ".$colors);   # could be also "auto"
			array_push($plot, "pctformat: %.0f");
			array_push($plot, $labels);
		}
		
		if ($type == 'bar') {
			array_push($plot, "#proc areadef");
			array_push($plot, "title: ".$this->getReportName());
			$rows = $this->getConnector()->getRowCount($result)+1;
			array_push($plot, "rectangle: 1 1 ".$rows." 2");
			array_push($plot, "xrange: 0 ".$rows);
			
			$plotData1 = "";
			$plotData2 = "";
			$maxVal = 1;
			while ($line = $this->getConnector()->fetch($result)) {
				$i=0;
				foreach ($line as $col_value) {
					# labels + colors
					if ($i == 0) {
						$plotData1 .= $col_value.$this->getNewline();
						#$colors = $color->getTestCaseRunStatusColor($col_value, $colors);
						$colors = $color->getColorPloticus($col_value, $colors, "tcrs");
					}
					# values
					if ($i == 1) {
						$plotData2 .= $col_value." ";
						
						if ($col_value > $maxVal) {
							$maxVal = $col_value;
						}
					}					
					$i++;
				}
			}

			array_push($plot, "yrange: 0 ".$maxVal);
			array_push($plot, "yaxis.stubs: incremental 1");
			array_push($plot, "yaxis.grid: color=gray(0.9)");
			array_push($plot, "yaxis.label: No. of cases"); 
			array_push($plot, "xaxis.stubs: text");
			
			array_push($plot, $plotData1);
			array_push($plot, "");
			array_push($plot, "#proc getdata");
			array_push($plot, "data: ".$plotData2);
			
			array_push($plot, "#proc processdata");
			array_push($plot, "action: rotate");
			array_push($plot, "");
					
			array_push($plot, "#proc bars");
			array_push($plot, "lenfield: 1");
			array_push($plot, "barwidth: 0.5");
			array_push($plot, "colorlist: ".$colors);
			array_push($plot, "crossover: 0");
		}
		
		return $plot;
	}
		
	function getGoogleChartLink( &$google, $result, $type, $chart ) {
		$color = new TR_Colors;
		
		$label="";
		$data="";
		$href="";
		$total=0;
		
		if (!$this->getTotal()) {
			$this->calcTotal( $result );
			$total=$this->getTotal();
		} else {
			$total = $this->getTotal();
		}
		
		$google->setDataMinRange(0);
		
		while ($line = $this->getConnector()->fetch($result)) {
			$plotData = "";
			$i=0;
			foreach ($line as $col_value) {					
				#labels and colors
				if ($i==0) {
					$label=$col_value;
					$google->addColor($color->getColorHTML($col_value, "tcrs"));
				}
				
				#data
				if ($i==1) {
					$label.="(".$col_value.")";
					if ($total) {
						$label.= "+".round($col_value/$total*100)."%"; 
					}
					
					$google->addLabel($label);
					$google->addLegend($label);
					$google->addData($col_value);
				}
				
				$i++;
			}
		}
		
		if ($type == "bar") { $google->hideLabels( true ); }
		if ($type == "pie" or $type == "pie3") { $google->hideLegend( true ); }		
	}	
}

?>
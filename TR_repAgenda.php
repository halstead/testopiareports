<?php

#EDITED: This file was modified to adapt and fix some issues. I removed all dependency chain, duration-related functionalities and graph generation(charts still work)
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"
ini_set("memory_limit","128M"); #EDITED: added a memory limit. The reason is that when wrongly selecting plan_id instead of run_id when creating an Agenda report, we get some infinite loops.
#EDITED: TODO: Fix the above issue!
class TR_repAgenda extends TR_Template{

	private $startHour;
	private $startMinute;
	private $endHour;
	private $endMinute;
	private $currHour;
	private $currMinute;
	
	private $numOfDays;
	private $numOfHours;
	private $numOfMinutes;
	
	private $startDate;
	private $currentDate;
	private $ignoreWeekend;
	private $today;
	private $highlightResourceConflicts;
	private $dependencyGraphOnly;
	
	# true = more than one root case was found
	private $moreRoots;
	# true = only cases with dependencies / false = cases with and without dependencies
	private $withDependency;
	
	private $createNewLine;
	private $numDay;
	private $currDay;
	
	private $dependencies = array();
	private $endTimes = array();
	private $caseList = array();
	private $caseData = array();
	private $conflicts = array();
	
	private $supportedCharts = array();

	private $supportedChartTypes = array(
		"google"   => array(),
		"ploticus" => array()
	);
	
	private $columnFormats = array(
		"Category" => "text-align:center"
	); 
	
	public function getSupportedChartsArr() {
		return $this->supportedCharts;
	}
	
	public function getSupportedChartTypesArr() {
		return $this->supportedChartTypes;
	}

	public function getColumnFormats() {
		return $this->columnFormats;
	}
	
	/**
	 *	Creating the date for a direct graph for graphviz
	 */
	private function digraph() {
		$output[] = "digraph G {";
		while ($dependency = current($this->dependencies)) {
			foreach ($dependency as $node) {
				$output[]=$node."->".key($this->dependencies);
			}
			next($this->dependencies);
		}
		$output[]= "}";
		return $output;
	}	
	
	public function init()  {
		# get report options
		$this->startHour 					= $this->getArgs()->get("starthour", true, 8);
		$this->startMinute 					= $this->getArgs()->get("startminute", true ,0);
		$this->endHour 						= $this->getArgs()->get("endhour", true, 18);
		$this->endMinute			 		= $this->getArgs()->get("endminute", true, 0);
		$this->startDate					= $this->getArgs()->get("startdate", true, date("Y-m-j",strtotime("now")));
		$this->ignoreWeekend 			 	= $this->getArgs()->get("ignoreweekends", true, "true");
		$this->withDependency 				= $this->getArgs()->get("withdependency", true, "true");
$this->withDependency="false"; #EDITED: introduce manually the "false" value to avoid errors
		$this->highlightResourceConflicts 	= $this->getArgs()->get("highlightresconfl", true, true);
		$this->dependencyGraphOnly 			= $this->getArgs()->get("dependencygraphonly", true, false);
		
		$this->currentDate = $this->startDate;
#		
#		if ($this->ignoreWeekend == "false") {
#			$now = getDate(strtotime($this->currentDate));
#			switch ($now["wday"]) {
#				case 0: $this->currentDate = date ( 'Y-m-j' , strtotime ( '+1 day' , strtotime ( $this->currentDate ) ) ); break;
#				case 6: $this->currentDate = date ( 'Y-m-j' , strtotime ( '+2 day' , strtotime ( $this->currentDate ) ) ); break;
#			}	
#		}
		
#		#for highlighting the correct day
#		$now = getDate();
#		$this->today = date('Y-m-j' , strtotime($now["year"]."-".$now["mon"]."-".$now["mday"]));
		
		# initial new line
		$this->createNewLine = true;
#		# counter for the days
#		$this->numDay = 0;
#		# current counter for days
#		$this->currDay = 0;
				
		#get dependencies
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		
		$sql->setFrom("test_case_runs");
		$sql->addField("test_case_dependencies","blocked");		
		$sql->addField("test_case_dependencies","dependson");
		$sql->addJoin("Inner","=","test_case_dependencies","blocked","test_case_runs","case_id");
		$sql->addWhere("test_case_runs","run_id","=",$this->getRunID());
		$sql->addGroupSort("Order","test_case_dependencies","blocked");
		$result = $this->getConnector()->execute($sql->toSQL()); 

		while ($line = $this->getConnector()->fetch($result)) {
			if (array_key_exists($line["dependson"],$this->dependencies) == false) {
				$this->dependencies[$line["dependson"]] = array();
			}
			$this->dependencies[$line["dependson"]][] = $line["blocked"]; 
		}
		
		$this->numOfDays = 1;
		$this->numOfHours = 0;
		$this->numOfMinutes = 0;
	}
	
	public function newDataLine($line) {
		$output = "";

		if (isset($this->previousLine)) {
			$time2 = explode(":", $line["Start Time"]);
			$time2 = $time2[0]*60+$time2[1];
			$time1 = explode(":", $this->previousLine["Start Time"]);
			$time1 = $time1[0]*60+$time1[1];
			if ($time1 > $time2) {
				$this->createNewLine = true;
			} else {
				$this->createNewLine = false;
			}
		} else {
			$this->createNewLine = true;
		}		
		
#		if ($this->createNewLine) {
#			$this->currDay++;
#			$output = "<tr><td class=\"testopia_NewDay\" colspan=\"11\"><b>"."Day ".$this->currDay."/".$this->numDay." - ".$this->currentDate."</b></td></tr>";
#		}
		
		return $output;
	}
	
	public function newLineBegin($line) {
		$output = "";
		# resource conflict?
		if (isset($this->previousLine) and $this->highlightResourceConflicts == "true") {
			if (array_key_exists($line["Test Case"], $this->conflicts)) {
				return "<tr class=\"testopia_RowResourceConflict\">";
			}
		}
		return $output;
	}
	
	public function injectBody( $result ) {
#		if ($this->dependencyGraphOnly == "true") {
#			return $this->renderDigraph("img");
#		} else {
			return $this->renderPlainHTMLBody($result);
#		}
	}
	
#	private function recurseList($list, $key) {
#		if (array_key_exists($key, $list)) {
#			$next = $list[$key];
#
#			foreach ($next as $elem) {
#				#make sure that the element is not inserted twice due to a former dependency
#				$key = array_search($elem, $this->caseList);
#				while ($key) {
#					unset($this->caseList[$key]);
#					$key = array_search($elem, $this->caseList);
#				}
#		
#				$this->caseList[] = $elem;
#			}
#			foreach ($next as $elem) {
#				$this->recurseList($list, $elem);			
#			}
#		}
#	}
	/**
	* custom function for sorting the test cases according to the start time --> modified it to use the Category field
	*/
	private function sortCases($a, $b) {
		if ($a["Category"] == $b["Category"]) {
			return 0;
		}
		return ($a["Category"] < $b["Category"]) ? -1 : 1;
	}	
	
	/**
	* implementation of the abstract function of the super class
	*/
	function getReport( $result ) {
		# check and set some parameters
		# field for calculating the total number; could be set to a field that is included in the field list of the select statement or to rowcount, which will simply count number of rows	
		if ($this->getArgs()->get("total") == "true") {
			$this->getArgs()->set("total", "");
		}
		
##		#get the depending test cases for the test run
##		$sqlStr = "";
##		$sqlStr.= "select * from ".$this->getConnector()->getTable("test_case_dependencies")." where ";
##		$sqlStr.= " blocked in (select case_id from ".$this->getConnector()->getTable("test_case_runs")." where run_id = ".$this->getRunID().")";
##		$sqlStr.= " or dependson in (select case_id from ".$this->getConnector()->getTable("test_case_runs")." where run_id = ".$this->getRunID().")";
##		$result = $this->getConnector()->execute($sqlStr);
##		
##		$cases = array();
##		while ($line = $this->getConnector()->fetch($result)) {
##			$case = array("id" => $line["blocked"], "dependson" => $line["dependson"]); 
##			$cases[] = $case;
##		}
##		
##		#finding the first case (root) of the agenda
##		$root = -1;
##		$rootId = 0;
##		$idx = 0;
##		$this->moreRoots = true;
##		foreach($cases as $case) {
##			$found=false;
##			foreach($cases as $case2) {
##				if ($case["id"] == $case2["dependson"]) {
##					$found=true;
##					break;
##				}
##			}
##			
##			if (!$found) {
##				if ($root != -1 and $cases[$root] != $rootId) {
##					#more than one root present -> error
##					$this->moreRoots = true;
##				} else {
##					$root = $idx;
##					$rootId = $cases[$root];
##				}
##			}
##			$idx++;
##		}
##		$root = $cases[$root]["id"];
##
##		
##		$list = array();
##		foreach ($cases as $case) {
##			if (array_key_exists($case["id"], $list)){
##				$next = $list[$case["id"]];
##				$next[] = $case["dependson"];
##				$list[$case["id"]] = $next;
##			} else {
##				$next = array($case["dependson"]);
##				$list[$case["id"]] = $next;
##			}
##		}
##		
##		#serializing the dependent cases of the agenda
##		$this->caseList[] = $root;
##		$this->recurseList($list, $root);
##		
		#selecting the rest of the test case without dependencies 
		if ($this->withDependency == "false") {
			$sqlStr = "select case_id from ".$this->getConnector()->getTable("test_case_runs")." ";
			$sqlStr.= "where run_id = ".$this->getRunID()." ";
$sqlStr.= "and iscurrent = 1"; # EDITED: added this line to select only current build and environment case runs
#			$sqlStr.= " and case_id not in (select blocked from ".$this->getConnector()->getTable("test_case_dependencies").")";
#			$sqlStr.= " and case_id not in (select dependson from ".$this->getConnector()->getTable("test_case_dependencies").")";
			$result = $this->getConnector()->execute($sqlStr);
			
			$cases = array();
			while ($line = $this->getConnector()->fetch($result)) {
				$cases[] = $line["case_id"];
			}		
			$this->caseList = array_merge($cases, $this->caseList);
		}
		
		#select the test case data
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		
		$sql->setFrom("test_case_runs");
		$sql->addField("test_case_runs", "case_id", "Test Case");
		$sql->addField("test_cases","summary", "Summary");
		$sql->addField("test_case_categories","name", "Category");
		$sql->addField("test_case_run_status", "name", "Status");
###		$sql->addField("test_cases", "estimated_time", "Duration");
###		$sql->addField("test_case_runs", "running_date", "Run Date");
		$sql->addField("test_case_runs", "close_date", "Close Date");
		$sql->addField("profiles", "login_name", "Tester");
		
		$sql->addJoin("Inner", "=", "test_cases", "case_id", "test_case_runs", "case_id");
		$sql->addJoin("Inner", "=", "test_case_categories", "category_id", "test_cases", "category_id");
		$sql->addJoin("Inner", "=", "test_case_run_status", "case_run_status_id", "test_case_runs", "case_run_status_id");
		$sql->addJoin("Inner", "=", "profiles", "userid", "test_cases", "default_tester_id");
		
		$sql->addWhere("test_case_runs", "run_id", "=", $this->getRunID(),"");
		$sql->addWhere("test_cases", "case_id", "=", "idPlaceholder", "AND");		
$sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND"); # EDITED: added this line to select only current build and environment case runs
		
		$sqlStr = $sql->toSQL();

		$this->caseData = array();		
		foreach ($this->caseList as $case) {
			$sqlStr2 = str_replace("idPlaceholder",$case,$sqlStr); 
			$result = $this->getConnector()->execute($sqlStr2);
		
			while ($line = $this->getConnector()->fetch($result)) {
#				if ($line["Run Date"] == "") { 
#					$line["Run Date"] = "-"; 
#				}
#				if ($line["Close Date"] == "") { 
#					$line["Close Date"] = "-"; 
#				}
				
#				# get start/end times				
#				$times = $this->handleDurationTimes($line["Test Case"], $line["Duration"]);
				
				$this->caseData[] = array(
					"Test Case" => $line["Test Case"],
					"Summary"	=> $line["Summary"],
					"Category" 	=> $line["Category"],
#					"Duration"	=> $line["Duration"],	
#					"Start Time"=> $times[0],
#					"End Time"	=> $times[1],
					"Status"	=> $line["Status"],
#					"Run Date"	=> $line["Run Date"],
					"Close Date"=> $line["Close Date"],
					"Tester"	=> $line["Tester"],
					"Depends on"=> "-",
#					"StartTimestamp"=> $times[2]
				);	
			}		
		}
		
		# sorting the test cases using the field "Category"
		usort($this->caseData, array("TR_repAgenda", "sortCases"));	
		
#		# as this sort could not be stable if start time and tester are equal we have to do 
#		# some more for effort for getting resource conflicts
#		$this->conflicts = array();
#		foreach ($this->caseData as $case) {
#			foreach ($this->caseData as $case2) {
#				if ($case["StartTimestamp"] == $case2["StartTimestamp"] and 
#				    $case["Test Case"] != $case2["Test Case"] and
#				    $case["Tester"] == $case2["Tester"]) {
#					$this->conflicts[$case2["Test Case"]] = $case["Test Case"];
#				}
#			} 
#		}
#		
#		#removing the element "StartTimestamp" as it is no longer needed
#		foreach ($this->caseData as $case) {
#			unset($case["StartTimestamp"]);
#			$casestmp[] = $case;
#		}
#		$this->caseData = $casestmp;
#		unset($casestmp);
		
		# set the field counter
		$this->numOfRows = count($this->caseData);
		# set the field names
		$this->columnNames = array();
		while (current($this->caseData[0]) !== false) {
			$this->columnNames[] = key($this->caseData[0]);
			next($this->caseData[0]);
		}
		reset($this->caseData[0]);
		$this->numOfCols = count($this->columnNames);
		
		$output = $this->renderPlainHTML( $this->caseData );	
	
		#returning the output
	    return $output;
	}
	
	function getReportName() {
		return "Test Run Agenda";
	}
		
	function getReportHeader() {
		return $this->getStandardRunHeader();
	}

	function getReportFooter() {
#		if ($this->dependencyGraphOnly == "true") {
#			return;
#		}
#		
#		if (!$this->moreRoots) {
#			$output = "<div><p>";
#			$output.="Day starts at ".date("H:i",mktime($this->startHour,$this->startMinute,0));
#			$output.=" and ends at ".date("H:i",mktime($this->endHour,$this->endMinute,0));
#			$output.=". Weekends are ignored: ".$this->ignoreWeekend;
#			$output.="</p></div>"; 
#			$output.="<div><p>Number of test cases:".count($this->caseList);
#			$output.=" - Total days:".$this->numDay;
#			$output.=" - Total time:".$this->numOfHours.":".$this->numOfMinutes;
#			$output.=" - ".$this->renderDigraph();
#			$output.="</p></div>";
#		} else {
#			$output = "<div style=\"text-align:center\"><b>".$this->renderDigraph()."</b></div>";
#		}
#		
#		return $output;
return; #EDIT: added a simple return for this function
	}	

#	private function handleDurationTimes($caseid, $value) {		
#		#if running in parallel to another case get the start id of this case
#		if (array_key_exists($caseid, $this->dependencies)) {
#			$dependencies = $this->dependencies[$caseid];
#			$max = 0;
#			foreach ($dependencies as $dependency) {
#				if ($max < $this->endTimes[$dependency]) {
#					$max = $this->endTimes[$dependency];
#				}
#			}
#			$h=explode(":", date("H:i", $max));
#			$this->currHour = $h[0];
#			$this->currMinute = $h[1];
#		} else {
#			$max=mktime($this->startHour,$this->startMinute,0,0,0,0);
#			$h=explode(":", date("H:i", $max));
#			$this->currHour = $h[0];
#			$this->currMinute = $h[1];		
#		}
#				
#		$startTime="";
#		$endTime="";
#		
#		#calc the start time in minutes 
#		$start = $this->currHour * 60 + $this->currMinute;
#		$startTime=date("H:i",mktime($this->currHour,$this->currMinute,0,0,0,0));
#		
#		#calc the start timestamp
#		$cdate = explode("-",$this->currentDate);
#		$startTimestamp = mktime($this->currHour,$this->currMinute,0,$cdate[1],$cdate[2],$cdate[0]);		
#		
#	    #add the duration
#		$duration=explode(":",$value);
#		$this->currHour+=$duration[0];
#		$this->currMinute+=$duration[1];
#		#handle minutes >= 60
#		if ($this->currMinute >= 60) {
#			$this->currHour+=1;
#			$this->currMinute-=60;
#		}		
#		
#		#add total time
#		$this->numOfHours+=$duration[0];;
#		$this->numOfMinutes+=$duration[1];
#		if ($this->numOfMinutes >= 60) {
#			$this->numOfHours+=1;
#			$this->numOfMinutes-=60;
#		}	
#		
#		#calc the end time in minutes
#		$end = $this->currHour * 60 + $this->currMinute;
#		#format the end time 
#		$endTime.=date("H:i",mktime($this->currHour,$this->currMinute,0,0,0,0));
#								
#		#reset the time counters if needed
#		if ($this->currHour >= $this->endHour) {
#			$max=mktime($this->startHour,$this->startMinute,0,0,0,0);
#			$h=explode(":", date("H:i", $max));
#			$this->currHour = $h[0];
#			$this->currMinute = $h[1];			
#				
#			$this->numDay++;
#			
#			$this->currentDate = date ( 'Y-m-j' , strtotime ( '+1 day' , strtotime ( $this->currentDate ) ) );
#			if ($this->ignoreWeekend == "false") {
#				$now = getDate(strtotime($this->currentDate));
#				switch ($now["wday"]) {
#					case 0: $this->currentDate = date ( 'Y-m-j' , strtotime ( '+1 day' , strtotime ( $this->currentDate ) ) ); break;
#					case 6: $this->currentDate = date ( 'Y-m-j' , strtotime ( '+2 day' , strtotime ( $this->currentDate ) ) ); break;
#				}
#			}
#			
#			#add total number of days
#			$this->numOfDays++;
#		}       		
#		
#		#set the end time for this case; needed for cases running in parallel
#		$cdate = explode("-",$this->currentDate);
#		$this->endTimes[$caseid] = mktime($this->currHour,$this->currMinute,0,$cdate[1],$cdate[2],$cdate[0]);
#		
#		return array($startTime, $endTime, $startTimestamp);
#	}
#	
#	private function handleTime($caseid, $value) {
#		$key = 0;
#		foreach($this->caseData as $case) {
#			if ($case["Test Case"] == $caseid) {
#				break;
#			}
#			$key++;
#		}
#		
#		$start = explode(":",$this->caseData[$key]["Start Time"]);
#		$end   = explode(":",$this->caseData[$key]["End Time"]);
#		$start = $start[0]*60+$start[1];
#		$end = $end[0]*60+$end[1];		
#		
#		#mark the cases that are currently due
#		$now = getDate();
#	    $nowTime = $now["hours"]*60+$now["minutes"];
#	    if ($start <= $nowTime and $end >= $nowTime and $this->currentDate == $this->today) {
#	    	$class = "testopia_TestCaseDue";
#	    } else {
#	    	$class = "testopia_TestCaseNotDue";
#	    }		    
#	    
#		$output="<td class=\".$class.\">".htmlentities($value)."</td>";
#		
#		return $output;
#	}
		
	function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {
		$output = "";
		
		# for getting the field names
		$sql = new TR_SQL;
		
		if ($type=="body") {
			switch ($field_name) {
				case "Test Case": 
						$output="<td style=\"text-align:center\"><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_case.cgi?case_id=".$value."\">".$value."</a></td>";
						break;
				case "Status":
						$class = "";
						$class = "testopia_TestCase".$value;					
						$output = "<td class=\"".$class."\">".$value."</td>";
						break;
#				case "Duration":
#						$output = $this->handleTime($line["Test Case"], $value);
#						break;
#				case "Start Time":
#						$output = $this->handleTime($line["Test Case"], $value);
#						break;
#				case "End Time":
#						$output = $this->handleTime($line["Test Case"], $value);
#						break;						
				case "Tester":
						$output.="<td><a href=\"mailto:".$value."\">".$value."</td>";
						break;
				case "Depends on":
						$output = "<td>";
						$caseid = $line["Test Case"];
						if (array_key_exists($caseid, $this->dependencies)) {
							$dependencies = $this->dependencies[$caseid];
							$i = 0;
							foreach ($dependencies as $dependency) {
								if ($i > 0) { 
									$output.=", "; 
								}
								$output.="<a href=\"".$this->getArgs()->get("bzserver")."/tr_show_case.cgi?case_id=".$dependency."\">".$dependency."</a>";
								$i++;
							}
							$output.= "</td>";
						}
						break;
			}
		}
		
		unset($sql);
		
		return $output;
	}	
	
	function getSQL() {		
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		
		$sql->setFrom("test_case_runs");
		$sql->addField("test_case_runs", "case_id", "Test Cases");	
		$sql->addWhere("test_case_runs", "run_id", "=", $this->getRunID(),"");		
		return $sql->toSQL();
	}	
	
	/**
	 * Renders a directed graph based on the dependencies of a test run using graphviz
	 * @return link to the generated graph file
	 */
#	function renderDigraph($type = "link") {
#		if ($this->getArgs()->get("graphviz") == "") {
#			return;
#		}
#	
#		global $wgScriptPath;
#	
#		$output = "";
#		$result = $this->digraph();
#		
#		$basefile = "agenda_".md5($this->getRunID()." ".$this->withDependency);		
#		# the plot file
#		$filenamePLO=strtolower($basefile.".txt");
#		$plotFile  = "images/testopia_reports/".$filenamePLO;
#		# the name of image file for the rendered chart
#		$filenamePNG=strtolower($basefile.".png");
#		$imgFile   = "images/testopia_reports/".$filenamePNG;		
#		
#		$plotFileH = fopen($plotFile, 'w') or die("can't open file");
#		#output the plot data
#		foreach ($result as $line){
#			fwrite($plotFileH,$line.$this->getNewline());
#		}		
#		fclose($plotFileH);
#		
#		while (!file_exists($plotFile)) {
#			sleep(1);
#		}
#		
#		$output = system(escapeshellcmd($this->getArgs()->get("graphviz")." -Tpng ".$plotFile." -o ".$imgFile),$ret);
#		
#		if ($ret == 0) {
#			# create the href for showing the chart
#			$src=$wgScriptPath."/".$imgFile;
#			if ($type == "link") {
#				$output = "<a href=\"".$src."\">"."Dependency Graph"."</a>";
#			} elseif ($type == "img") {
#				$output = "<img src=\"".$src."\" alt=\"Dependency Graph\" title=\"Dependency Graph\">";
#			}
#		} else {
#			$output = "<p>".$ret."-".$output."-".escapeshellcmd($this->getArgs()->get("graphviz")." -Tpng ".$plotFile." -o ".$imgFile)."</p>";
#		}
#		return $output;		
#	}
}

?>
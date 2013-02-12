<?php
/**
 * Main class for google charts
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
 
class GoogleChart {

	private $baselink="http://chart.apis.google.com/chart?";
	private $autodim="auto";
	
	# coeficient for determining the chart size
	private $coef           = 3.1;
	private $coefp3         = 3.6;
	private $coefmeter      = 2.5;
	private $default_height = 100;
	private $default_width  = "auto";
	
	private $interval    = "auto";
	private $pxPerNumber = 20;
	private $pxPerLabel = 25;
	
	private $height;
	private $width;
	private $title;
	private $data = array();
	private $labels = array();
	private $labelsHide = false;
	private $legend = array();
	private $legendPos = "";
	private $legendHide = false;
	private $colors = array();
	private $type;
	private $tooltip;
	private $altText;
	
	private $dataMin;
	private $dataMax;
	private $dataMinRangeSet = false;
	private $dataMaxRangeSet = false;
	private $dataMinRange;
	private $dataMaxRange;
	
	public function setTitle( $title ) {
		$this->title = $title;
		$this->title = str_replace(" ", "+", $this->title);
		$this->title = str_replace("\n", "|", $this->title);
		$this->title = htmlentities($this->title);
	}
	
	public function setType( $type ) {
		$this->type = $type;
	}

	public function getType() {
		return $this->type;
	}
	
	public function setDefaultHeight( $default ) {
		$this->default_height = $default;
	}

	public function setDefaultWidth( $default ) {
		$this->default_width = $default;
	}
	
	public function setHeight( $height ) {
		$this->height = $height;
	}

	public function setWidth( $height ) {
		$this->width = $height;
	}
	
	public function setDimCoef( $coef ) {
		$this->coef = $coef;
	}
	
	public function setDimCoef3D( $coef ) {
		$this->coefp3 = $coef;
	}

	public function setDimCoefMeter( $coef ) {
		$this->coefmeter = $coef;
	}
	
	public function setInterval($axis, $interval) {
		#axis: 0 = y-left, x = 1, y-right
		$this->interval=$interval;
	}
	
	private function calcChartDimension() {
		$coef=$this->coef;
		if ( $this->type == "p3" ) {
			$coef=$this->coefp3;
		}
		if ( $this->type == "gom" ) {
			$coef=$this->coefmeter; 
		}
			
		if ($this->width == "") {
			$this->width=$this->default_width;
		}
		if ($this->height == "") {
			$this->height=$this->default_height;
		}
		
		if ($this->width==$this->autodim and $this->height!=$this->autodim) {
			$this->width=abs($coef * $this->height);
		} elseif ($this->width!=$this->autodim and $this->height==$this->autodim) {
			if ($this->type == "bvg" and $this->labelsHide == true) {
				$this->height=$this->pxPerLabel * count($this->labels);
			} else {
				$this->height=abs($width / $coef);
			}
		} elseif ($this->width==$this->autodim and $this->height==$this->autodim) {
			$this->width=$this->default_width;				
			$this->height=$this->default_height;				
		}
	}

	public function getChartSize() {
		$this->calcChartDimension();
		return "&chs=".$this->width."x".$this->height;
	}
	
	public function setAltText( $text ) {
		$this->altText = $text;
	}
	
	public function getAltText() {
		return htmlentities($this->altText);
	}
	
	public function setTooltip( $tip ) {
		$this->tooltip = $tip;
	}
	
	public function getTooltip() {
		return htmlentities($this->tooltip);	
	}
	
	public function setChartType( $type ) {
		$this->type = $type;
	}
	
	public function addLabel( $label ) {
		$this->labels[] = htmlentities($label);
	}

	public function addLegend( $legend ) {
		$this->legend[] = htmlentities($legend);
	}
	
	public function setLegendPos($pos, $orientation) {
		switch (strtolower($pos)) {
			case "top": 
				$this->legendPos="t";
				if (strtolower($orientation) == "vertical") {
					$this->legendPos.="v"; 
				}
				break;
			case "bottom":
				$this->legendPos="b";
				if (strtolower($orientation) == "vertical") {
					$this->legendPos.="v"; 
				}
				break;				
			case "left":
				$this->legendPos="l";
			case "right":
				$this->legendPos="r";
		}
	}
	
	public function addColor( $color ) {
		$this->colors[] = $color;
	}
	
	public function addData( $data ) {
		$this->data[] = $data;
		if (!$this->dataMin) {
			$this->dataMin = $data;
		} elseif ($this->dataMin > $data) {
			$this->dataMin = $data;
		}
		if (!$this->dataMax) {
			$this->dataMax = $data;
		} elseif ($this->dataMax < $data) {
			$this->dataMax = $data;
		}
		
		if ($this->dataMaxRangeSet) {
			$this->dataMax = $this->dataMaxRange;
		}
		if ($this->dataMinRangeSet) {
			$this->dataMin = $this->dataMinRange;
		}	
	}

	public function addData2($set, $data) {
		if (!array_key_exists($set, $data)) {
			$data[$set] = array();
		}
		$subset = $data[$set];
		$subset[] = $data;
		$this->data[$set] = $subset;
	}
	
	public function setDataMinRange( $min ) {
		$this->dataMinRangeSet = true;
		$this->dataMinRange = $min;
	}

	public function setDataMaxRange( $ax ) {
		$this->dataMaxRangeSet = true;
		$this->dataMaxRange = $ax;
	}
	
	public function getData() {
		$output="";
		
		foreach ($this->data as $data) {
			$output=$this->add2str($output,",",$data);
		}
		
		if ($this->data) {
			$output="&chd=t:".$output;
		}
		
		return $output;
	}
	
	public function getColors() {
		$output="";
				
		foreach ($this->colors as $color) {
			$output=$this->add2str($output,"|",$color);
		}
		
		if ($this->colors) {
			$output="&chco=".$output;
		}	
		
		return $output;	
	}

	public function getLabels() {
		$output="";
		
		if ($this->labelsHide == true) { return $output; }
		
		foreach ($this->labels as $label) {
			$output=$this->add2str($output,"|",$label);
		}
		
		if ($this->labels) {
			$output="&chl=".$output;
		}
		
		return $output;	
	}
	
	public function hideLabels( $hide ) {
		$this->labelsHide = $hide;
	}
	
	public function getLegend() {
		$output="";
		
		if ($this->legendHide == true) { return $output; }
		
		foreach ($this->legend as $legend) {
			$output=$this->add2str($output,"|",$legend);
		}
		
		#add the legend
		if ($output != "") {
			$output="&chdl=".$output;
		}
		
		#add the position
		if ($this->legendPos != "") {
			$output="&chdlp=".$this->legendPos;
		}
		
		return $output;	
	}	
	
	public function hideLegend( $hide ) {
		$this->legendHide = $hide;
	}	
	
	private function getURL() {
		$url=$this->baselink;
		$url.="cht=".$this->type;
		
		#get the size
		$url.=$this->getChartSize();
		
		#get the interval
		if ($this->interval == "auto") {
			#y-axis left
			$count = $this->dataMax - $this->dataMin;
			$interval = round($count / round($this->height / $this->pxPerNumber));
		} else {
			$interval = $this->interval;
		}
		
		#do something for the bar chart
		if ($this->type == "bvg") {
			$url.="&chbh=a";
			$url.="&chds=".$this->dataMin.",".$this->dataMax; #data scale for bar chart
			$url.="&chxt=y"; #axis type -> here only y will be rendered
			$url.="&chxr=0,".$this->dataMin.",".$this->dataMax.",".$interval; #axis range for bar chart <axis index>, <range start>, <range end>, <interval>
		}
		
		$url.="&chd=t:".$this->getData();
		$url.=$this->getColors();
		$url.=$this->getLegend();
		$url.=$this->getLabels();
		
		if ($this->title != "") {
			$url.="&chtt=".$this->title;
		}
		
		return $url;
	}
	
	public function toURL() {
		return $this->getURL();
	}	
	
	public function toImg() {
		return "<img src=\"".$this->toURL()."\" alt=\"".$this->getAltText()."\" title=\"".$this->getTooltip()."\">";
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
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
	private $coef  =3.1;
	private $coefp3=3.6;
	private $default_height = 100;
	private $default_width  = 200;
	
	private $height;
	private $width;
	private $title;
	private $data = array();
	private $labels = array();
	private $labelsHide = false;
	private $legend = array();
	private $legendHide = false;
	private $colors = array();
	private $type;
	
	private $dataMin;
	private $dataMax;
	private $dataMinRangeSet = false;
	private $dataMaxRangeSet = false;
	private $dataMinRange;
	private $dataMaxRange;
	
	public function setType( $type ) {
		$this->type = $type;
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
	
	private function calcChartDimension() {
		$coef=$this->coef;
		if ( $this->type == "p3" ) {
			$coef=$this->coefp3;
		}
			
		if ($this->width==$this->autodim and $this->height!=$this->autodim) {
			$this->width=abs($coef * $this->height);
		} elseif ($this->width!=$this->autodim and $this->height==$this->autodim) {
			$this->height=abs($width / $coef);
		} elseif ($this->width==$this->autodim and $this->height==$this->autodim) {
			$this->width=$this->getArgs()->default_width;				
			$this->height=$this->default_height;				
		}
	}

	public function getChartSize() {
		$this->calcChartDimension();
		return "&chs=".$this->width."x".$this->height;
	}
	
	public function setChartType( $type ) {
		$this->type = $type;
	}
	
	public function setTitle( $title ) {
		$this->title = $title;
	}
	
	public function addLabel( $label ) {
		$this->labels[] = $label;
	}

	public function addLegend( $legend ) {
		$this->legend[] = $legend;
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
		
		if ($this->legend) {
			$output="&chdl=".$output;
		}
		
		return $output;	
	}	
	
	public function hideLegend( $hide ) {
		$this->legendHide = $hide;
	}	
	
	private function getURL() {
		$url=$this->baselink;
		$url.="cht=".$this->type;
		
		if ($this->type == "bvg") {
			$url.="&chbh=a";
			$url.="&chds=".$this->dataMin.",".$this->dataMax; #data scale for bar chart
			$url.="&chxt=y"; #axis type -> here only y will be rendered
			$url.="&chxr=0,".$this->dataMin.",".$this->dataMax.",1"; #axis range for bar chart <axis index>, <range start>, <range end>, <interval>
		}
		
		$url.="&chd=t:".$this->getData();
		$url.=$this->getColors();
		$url.=$this->getLegend();
		$url.=$this->getLabels();
		
		$url.=$this->getChartSize();
		
		return $url;
	}
	
	public function toURL() {
		return $this->getURL();
	}	
	
}
 
 ?>
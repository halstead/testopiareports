<?php
/**
 * Internationalisation file for extension Testopia Reports.
 */

/**
 * Copyright (C) 2009- Andreas Mueller
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

$messages = array();

$messages['en'] = array(
	'trReport_noconnection'					=> 'Can\'t get database connection ($1@$2) : $3',
	'trReport_nodb'							=> 'Database not found : $1',
	'trReport_sqlerror'						=> 'Error running the sql : $1',
	'trReport_unsuported_report'			=> 'Unsupported report: $1. Supported reports: $2.',
	'trReport_missing_report'				=> 'Missing parameter report_id. Supported reports: $1.',
	'trReport_chart_not_supported'			=> 'Chart $1 not supported. Supported charts by this report are: $2.',
	'trReport_charts_not_supported'			=> 'Chart feature is not supported by this report.',
	'trReport_unsupported_parameter'		=> 'Parameter $1 is not supported. Only the following parameters are allowed: $2.'
	
);
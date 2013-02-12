<?php
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"

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
	// DB errors
	'trReport_noconnection'					=> 'Can\'t get database connection ($1@$2) : $3',
	'trReport_nodb'							=> 'Database not found : $1',
	'trReport_sqlerror'						=> 'Error running the sql : $1',
	'trReport_no_results_found'				=> '**No results**', #EDITED: Modified this not to give of the impression of an error
	
	// Parameter checking
	'trReport_unsuported_report'			=> 'Unsupported report: $1. Supported reports: $2.',
	'trReport_missing_report'				=> 'Missing parameter report_id. Supported reports: $1.',
	'trReport_chart_not_supported'			=> 'Chart "$1" not supported. Supported charts by this report are: $2.',
	'trReport_chart_type_not_supported'     => 'Chart type "$1" not supported. Supported chart types by this report are: $2.',
	'trReport_charts_not_supported'			=> 'Chart feature is not supported by this report.',
	'trReport_unsupported_parameter'		=> 'Parameter "$1" is not supported. Only the following parameters are allowed: $2.',
	'trReport_wrong_value'					=> 'Wrong value $1 for parameter $2.',
	'trReport_error_report_option'			=> 'Error in report option $1.',
	'trReport_chart_no_type'				=> 'No type specified for chart!',

	// Buttons
	'trReport_hide'							=> 'Hide',
    'trReport_show'							=> 'Show',

	// Testopia - do not translate or change unless your installation is maintaining differen values
	'trReport_Testopia_Disabled' 			=> "DISABLED",
	'trReport_Testopia_Proposed'  			=> "PROPOSED",
	'trReport_Testopia_Confirmed'			=> "CONFIRMED",		

	// For report Agenda
	'trReport_more_than_one_root'			=> 'More than root found for starting the agenda for test run $1. Check the dependency graph. '. 
											   'Please specify exactly one test case as the start of the agenda.'	
);
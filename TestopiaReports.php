<?php
/**
 * See README for installation and usage
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

if ( !defined( 'MEDIAWIKI' ) and !defined('TESTOPIAREPORTS')  ) {
	die('This file is a MediaWiki extension, it is not a valid entry point' );
}
$wgTestopiaReportsIncludes = dirname(__FILE__) . '/';

/**
* Set up autoloading
*/
$wgAutoloadClasses['TestopiaReport']     = $wgTestopiaReportsIncludes."TestopiaReport.php";
$wgAutoloadClasses['TestopiaParameters'] = $wgTestopiaReportsIncludes."TestopiaParameters.php";
$wgAutoloadClasses['TR_MysqlConnector']  = $wgTestopiaReportsIncludes."TR_MysqlConnector.php";
$wgAutoloadClasses['TR_PGConnector']     = $wgTestopiaReportsIncludes."TR_PGConnector.php";
$wgAutoloadClasses['TR_Colors']          = $wgTestopiaReportsIncludes."TR_Colors.php";
$wgAutoloadClasses['TR_Template']        = $wgTestopiaReportsIncludes."TR_Template.php";
$wgAutoloadClasses['GoogleChart']        = $wgTestopiaReportsIncludes."GoogleChart.php";

/**
* Extension setup
*/
$wgExtensionCredits['parserhook'][] = array(
        'name' => 'TestopiaReports',
        'version' => '0.1',
        'url' => 'http://www.mediawiki.org/wiki/Extension:Testopia_Reports',
        'author' => 'Andreas Mueller',
        'description' => 'Providing of [http://www.mozilla.org/projects/testopia/ Testopia] reports and charts'
);

$wgExtensionFunctions[] = 'efTestopiaReportsSetup';
$wgExtensionMessagesFiles['TestopiaReports'] = $wgTestopiaReportsIncludes.
	'/TestopiaReports.i18n.php';

$wgHooks['LanguageGetMagic'][]       = 'efTestopiaReportsMagic';

/**
 * Register the function hook
 */
function efTestopiaReportsSetup() {
	global $wgParser;
	$wgParser->setFunctionHook( 'testopia', 'efTestopiaReportsRender' );
}
 
/**
 * Register the magic word
 */
function efTestopiaReportsMagic( &$magicWords, $langCode ) {
	$magicWords['testopia'] = array( 0, 'testopia' );
	return true;
}
 
/**
 * Call to render the testopia report
 */
function efTestopiaReportsRender( &$parser) {
	$output="";
	$args = func_get_args();
	$testopiaReport = new TestopiaReport( $parser, $args );
	$output = $testopiaReport->render();
	return $parser->insertStripItem( $output , $parser->mStripState );
}

?>

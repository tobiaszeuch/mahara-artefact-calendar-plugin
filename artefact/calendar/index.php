<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-calendar
 * @author     Angela Karl, Uwe Boettcher
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Technische Universitaet Darmstadt, Germany
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'calendar');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'calendar');
define('SECTION_PAGE', 'index');

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(dirname(dirname(dirname(__FILE__))).'/artefact/calendar/lib.php')      ;
require_once(dirname(dirname(dirname(__FILE__))).'/artefact/plans/lib.php')  ;
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'artefact/lib.php');

define('TITLE', get_string('calendar', 'artefact.calendar'));

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$plans = ArtefactTypePlan::get_plans($offset, $limit);

ArtefactTypeCalendar::build_calendar_html($plans);

//javascript
$javascript = <<< JAVASCRIPT
	function toggle_ajax(linkid, colorid, taskid, status, planid, date){//calls the toggle function and also saves status to db with ajax

		if (window.XMLHttpRequest)// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  
		else// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

	 	toggle(linkid, colorid, taskid);

		xmlhttp.open("GET","index.php?"+date+"&status="+status+"&plan="+planid+"&ajax=true",true);
		xmlhttp.send();
	}

	function toggle(linkid, colorid, taskid){ //toggles tasks
	if(document.getElementById(linkid).style.opacity == '0.5' || document.getElementById(linkid).style.filter == 'alpha(opacity=20)')
		{	
			document.getElementById(linkid).style.opacity = '1'; 
			document.getElementById(linkid).style.filter = 'alpha(opacity=100)';
			var p = document.getElementsByName(taskid);
			
			for (var i=0; i < p.length; i++) {
					 p[i].style.display = 'block'; 
			}
			document.getElementById(colorid).style.display = 'block'; 
		}
		else
		{	
			document.getElementById(linkid).style.opacity ='0.5'; 
			document.getElementById(linkid).style.filter = 'alpha(opacity=20)';
			document.getElementById(colorid).style.display = 'none';
			var p = document.getElementsByName(taskid);
			
			for (var i=0; i < p.length; i++) {
					p[i].style.display = 'none'; 
			}

		}

	}
	

	function hide_overlay(){

		document.getElementById('aufgabenoverlay').style.display = 'none'; 
		document.getElementById('done').style.display = 'none';
		document.getElementById('done_sw').style.display = 'none';
	}

	function toggle_color_picker(taskid){
		if(document.getElementById(taskid).style.display == 'none')
			document.getElementById(taskid).style.display = 'block';
		else 
			document.getElementById(taskid).style.display = 'none';
	}

JAVASCRIPT;

$smarty = smarty(array('paginator'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign_by_ref('plans', $plans);
$smarty->assign('PAGEHEADING', hsc(get_string("calendar", "artefact.calendar")));
if(!($_GET["ajax"] == true))
	$smarty->display('artefact:calendar:index.tpl');

?>

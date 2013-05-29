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
require_once(dirname(dirname(dirname(__FILE__))).'/lib/license.php')  ;
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'artefact/lib.php');

define('TITLE', get_string('calendar', 'artefact.calendar'));

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 100);

$plans = ArtefactTypePlan::get_plans($offset, $limit);

ArtefactTypeCalendar::build_calendar_html($plans);

//javascript
$javascript = <<< JAVASCRIPT

	function toggle_ajax(linkid, colorid, taskid, status, planid, grayid, tasks_per_day){//calls the toggle function and also saves status to db with ajax

		if (window.XMLHttpRequest)// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  
		else// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

	 	toggle(linkid, colorid, taskid, grayid, tasks_per_day);
	 	var new_status;
	 	if(status == 1)
	 		new_status = 0;
	 	else new_status = 1;
	 	document.getElementById("onclick"+planid).onclick = function() {toggle_ajax(linkid, colorid, taskid, new_status, planid, grayid, tasks_per_day)};

		xmlhttp.open("GET","index.php?status="+status+"&plan="+planid+"&ajax=true",true);
		xmlhttp.send();
	}

	function toggle(linkid, colorid, taskid, grayid, tasks_per_day){ //toggles tasks
		if(document.getElementById(linkid).style.opacity == '0.5' || document.getElementById(linkid).style.filter == 'alpha(opacity=20)')
		{	
			document.getElementById(linkid).style.opacity = '1'; 
			document.getElementById(linkid).style.filter = 'alpha(opacity=100)';
			var p = document.getElementsByName(taskid);
			
			for (var i=0; i < p.length; i++) {
					 p[i].style.display = ''; 
			}
			document.getElementById(colorid).style.display = 'block'; 
			document.getElementById(grayid).style.display = 'none'; 

			increase_task_count(tasks_per_day);
		}
		else
		{	
			document.getElementById(linkid).style.opacity ='0.5'; 
			document.getElementById(linkid).style.filter = 'alpha(opacity=20)';
			document.getElementById(colorid).style.display = 'none';
			document.getElementById(grayid).style.display = 'block'; 

			var p = document.getElementsByName(taskid);
			
			for (var i=0; i < p.length; i++) {
					p[i].style.display = 'none'; 
			}

			decrease_task_count(tasks_per_day);

		}

	}

	//decreases the number of tasks for each day (which is only displayed if more than 3 per day

	function decrease_task_count(tasks_per_day){
		
		var days = tasks_per_day.length;

		for (var i=1; i <= days; i++) {
			var oldValue = document.getElementById('number_tasks'+i).value;
			newValue = oldValue - tasks_per_day[i-1];
			document.getElementById('number_tasks'+i).value = newValue;

			document.getElementById('display_number_calendar'+i).innerHTML = newValue;
			document.getElementById('display_number_overlay'+i).innerHTML = newValue;

			
			if(newValue < 4){
				document.getElementById('link_number_tasks'+i).style.display = 'none';
			}
			else {
				document.getElementById('link_number_tasks'+i).style.display = 'block';
			}



		}
	}

	//increases the number of tasks for each day (which is only displayed if more than 3 per day

	function increase_task_count(tasks_per_day){
		var days = tasks_per_day.length;

		for (var i=1; i <= days; i++) {
			var oldValue = document.getElementById('number_tasks'+i).value;
			newValue = Number(oldValue) + Number(tasks_per_day[i-1]);
			document.getElementById('number_tasks'+i).value = newValue;

			document.getElementById('display_number_calendar'+i).innerHTML = newValue;
			document.getElementById('display_number_overlay'+i).innerHTML = newValue;

			if(newValue < 4){
				document.getElementById('link_number_tasks'+i).style.display = 'none';
			}
			else {
				document.getElementById('link_number_tasks'+i).style.display = 'block';
			}
		}
	}


	function hide_overlay(){

		document.getElementById('aufgabenoverlay').style.display = 'none'; 
		
	}

	function toggle_color_picker(picker, planid, oldcolor){
			document.getElementById(picker).style.display = 'block';
			document.getElementById("close_color_picker").onclick = function() {close_color_picker(picker, planid, '')};
			document.getElementById("color_picker_id").value = planid;
			var old = document.getElementById("old_color").value;
			if(old != ""){
				document.getElementById(old).style.border = '0px';
				document.getElementById(old).style.width = '16px';
				document.getElementById(old).style.height = '16px';
			}
			document.getElementById(oldcolor).style.border = '2px dotted black';//marks old color
			document.getElementById(oldcolor).style.width = '12px';
			document.getElementById(oldcolor).style.height = '12px';
			document.getElementById("old_color").value = oldcolor;
	}

	function close_color_picker(picker, planid, oldcolor){
	 	document.getElementById(picker).style.display = 'none';
		document.getElementById("color_picker_id").value = '';

		var old = document.getElementById("old_color").value;
		if(old != ""){
			document.getElementById(old).style.border = '0px';
			document.getElementById(old).style.width = '16px';
			document.getElementById(old).style.height = '16px';
	 	}
	}

	function save_color(planid, color){

		if (window.XMLHttpRequest)// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  
		else// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

		document.getElementById('color'+planid).style.backgroundColor = "#"+color;
		document.getElementById('color_button'+planid).style.backgroundColor = "#"+color;
		document.getElementById('saved_color'+planid).value = "#"+color;
		close_color_picker('picker',planid,'');

		//tasks in calendar view of one plan

		var p = document.getElementsByName('task'+planid);
			
			for (var i=0; i < p.length; i++) {
					p[i].style.backgroundColor = "#"+color;
			}

		var p = document.getElementsByName(planid);
			
			for (var i=0; i < p.length; i++) {
					p[i].name = 'color';
			}

		xmlhttp.open("GET","index.php?color="+color+"&picker="+planid+"&ajax=true",true);
		xmlhttp.send();
		
	}

	function toggle_notification_settings(){
		if(document.getElementById('set_notification').style.display == 'block')
			document.getElementById('set_notification').style.display = 'none';
		else
			document.getElementById('set_notification').style.display = 'block';
	}

	function toggle_feed_settings(){
		if(document.getElementById('feed_settings').style.display == 'block')
			document.getElementById('feed_settings').style.display = 'none';
		else
			document.getElementById('feed_settings').style.display = 'block';
	}

	function toggle_feed_url(toggle){
		if(toggle == 'off'){
			document.getElementById('feed_url').style.display = 'none';
			document.getElementById('feed').innerHTML = document.getElementById('feed_url_base').value; //url is reset
		}
		else
			document.getElementById('feed_url').style.display = 'block';
	}

	function generate_feed_url(){
		if(document.getElementById('export_old').checked == true){
			document.getElementById('feed').innerHTML += '&export_old=0';
			var months = document.getElementById('feed_months').value;
			document.getElementById('feed').innerHTML += "&export_months="+months;
		} 
		else
			document.getElementById('feed').innerHTML += '&export_old=1'; 

		if(document.getElementById('export_done').checked == true)
			document.getElementById('feed').innerHTML += '&export_done=0'; 
		else
			document.getElementById('feed').innerHTML += '&export_done=1'; 

		if(document.getElementById('export_event').checked == true)
			document.getElementById('feed').innerHTML += '&type=event'; 
		else if(document.getElementById('export_task').checked == true)
			document.getElementById('feed').innerHTML += '&type=task'; 
		else
			document.getElementById('feed').innerHTML += '&type=task'; 
	}

	function set_reminder_date_ajax(reminder_value, plan, prefix, reminder_strings){//changes the reminder settings
		if (window.XMLHttpRequest)// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  
		else// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	 	
	 	for (var i = 0; i < reminder_strings.length; ++i){ //changes text to new reminder date
	 		if(reminder_strings[i][0] == reminder_value){
	 			var newText = prefix+reminder_strings[i][1];
	 			document.getElementById('reminder').innerHTML = newText;
	 		}
	 	}
	 	toggle_notification_settings();
		xmlhttp.open("GET","index.php?reminder_date="+reminder_value+"&reminder="+plan+"&ajax=true",true);
		xmlhttp.send();
	}


	function toggle_reminder_ajax(planid, status){//changes the reminder settings

		if (window.XMLHttpRequest)// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  
		else// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

	 	if(status == 0)
	 		new_status = 1;
	 	else new_status = 0;

	 	if(new_status == 1){
	 		var enable = 'reminder_enabled'+planid;
	 		var disable = 'reminder_disabled'+planid;
	 	}
	 	else{
	 		var enable = 'reminder_disabled'+planid;
	 		var disable = 'reminder_enabled'+planid;
	 	}	
	 	document.getElementById(disable).style.display = 'none';
	 	document.getElementById(enable).style.display = 'inline';
	 	
		
		xmlhttp.open("GET","index.php?reminder_status="+new_status+"&reminder="+planid+"&ajax=true",true);
		xmlhttp.send();
	}

	function toggle_all_reminders(plan_ids, status){
		if (window.XMLHttpRequest)// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  
		else// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		
	 	if(status == 0)
	 		new_status = 1;
	 	else new_status = 0;

	 	if(new_status == 1){
	 		for (var i = 0; i < plan_ids.length; ++i){
		 		var enable = 'reminder_enabled'+plan_ids[i];
		 		var disable = 'reminder_disabled'+plan_ids[i];
		 		document.getElementById(disable).style.display = 'none';
	 			document.getElementById(enable).style.display = 'inline';
	 		}

	 	}
	 	else{
	 		for (var i = 0; i < plan_ids.length; ++i){
		 		var enable = 'reminder_disabled'+plan_ids[i];
		 		var disable = 'reminder_enabled'+plan_ids[i];
		 		document.getElementById(disable).style.display = 'none';
	 			document.getElementById(enable).style.display = 'inline';
		 	} 	
	 	}	
		
		xmlhttp.open("GET","index.php?reminder_status="+new_status+"&reminder=all&ajax=true",true);
		xmlhttp.send();
	}

	function choose_color_new_plan(color){
		var last = document.getElementById('newplan_color').value; //remove highlighting of last chosen color
		if(last != '')
			document.getElementById(last).className = 'thumb';

		document.getElementById('newplan_color').value = color;	//set color to chosen one
		document.getElementById(color).className += ' borderblack'; //highlight chosen color
	}
	
JAVASCRIPT;

$smarty = smarty(array('paginator'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign_by_ref('plans', $plans);
$smarty->assign('PAGEHEADING', hsc(get_string("calendar", "artefact.calendar")));
if(!($_GET["ajax"] == true))
	$smarty->display('artefact:calendar:index.tpl');

?>

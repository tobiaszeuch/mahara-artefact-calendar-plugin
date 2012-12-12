<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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

defined('INTERNAL') || die();

require_once('activity.php');

class PluginArtefactCalendar extends PluginArtefact {

  private static $max_reminder_days = 28;

	public static function get_artefact_types() {
		return array(
		'calendar',
		);
	}
	
	public static function get_block_types() {
		return array();
	}
	
	public static function get_plugin_name() {
		return 'calendar';
	}
	
	public static function menu_items() {
		return array(
			array(
			'path' => 'calendar',
			'url' => 'artefact/calendar/',
			'title' => get_string('calendar', 'artefact.calendar'),
			'weight' => 60,
			),
		);
	}

  public static function get_activity_types() {
        return array();
    }

  public static function get_cron() {
    return array(
      (object)array(
      'callfunction' => 'remind_all_users',
      'hour' => '2',
      'minute' => '00',
      ));
  }

  /**
  * Reminds all users about their tasks
  */

  public static function remind_all_users(){

    $users_to_be_reminded = PluginArtefactCalendar::get_users_to_be_reminded();
    PluginArtefactCalendar::notify_users($users_to_be_reminded);

  }


   /**
  * Returns an array of user ids and the name/date of tasks
  */

  private static function get_users_to_be_reminded(){

    $users_to_be_reminded = array(); //two dimensional array with user ids and all their tasks

    for($i = 0; $i <= self::$max_reminder_days; $i++){ // get all plans with reminder set to x days ahead    
      ($results = get_records_sql_array("SELECT id, owner FROM {artefact} 
        JOIN {artefact_calendar_calendar} ON artefact.id = artefact_calendar_calendar.plan WHERE artefacttype = 'plan' AND reminder_status = '1' AND reminder_date = '$i';", array()))
            || ($results = array()); //get plans and user ids
      
      if (!empty($results[0])) {
        
        foreach ($results as $result) {
          $userid = $result->owner;
          $planid = $result->id;
          $tasks = PluginArtefactCalendar::get_task_in_x_days($i,$planid);//get tasks of plans

          if(count($tasks)){

            $day = array_keys($tasks);
            $day = $day[0]; //first key is the date of the tasks
           
            for ($j = 0; $j < count($tasks[$day]); $j++){ 
              $num = count($users_to_be_reminded[$userid][$day]);
              $users_to_be_reminded[$userid][$day][$num] = $tasks[$day][$j];  
            }

          }      
        }
      }
    }
    return $users_to_be_reminded;
  }

  /**
  * Returns titles of uncompleted tasks of a specific plan that happen in x days (0 = today)
  */

  private static function get_task_in_x_days($num_days, $plan_id){

    $all_tasks = array();
    $date = date("Y-m-d",strtotime('+'.$num_days. ' days')); //date in x days, format YYYY-MM-DD

    ($results = get_records_sql_array("SELECT title FROM {artefact_plans_task} JOIN {artefact} ON {artefact}.id = {artefact_plans_task}.artefact 
      WHERE artefacttype = 'task' AND parent = '$plan_id' AND completiondate = '$date' AND completed = '0';", array()))
            || ($results = array()); //get plans and user ids

    if (!empty($results[0])) {
      $date = date(get_string('display_format', 'artefact.calendar'),strtotime('+'.$num_days. ' days'));
      $all_tasks[$date] = array();
      foreach ($results as $result) { 
        array_push($all_tasks[$date], $result->title);
      }
    }
    return $all_tasks;
  }


  /**
  * Notifys all users about their tasks by email
  *
  * Structure of $users:
  * Array with user id
  *   Array with date as key and another array with tasks titles as value 
  *       ex. (user = 2, Tasks on 2012/11/26 and 2012/11/27): 
  *       Array ( [2] => Array ( [26.11.2012] => Array ( [0] => Task Titel 1 [1] => Task Title 2 ) [27.11.2012] => Array ( [0] => Task Title 2 [1] => Task Title 4 ) )
  */

  private static function notify_users($users_to_be_reminded){
    $message = new StdClass;
    $users = array_keys($users_to_be_reminded);
    
    foreach ($users as $user){
      $message->users = array($user);
      $message->subject = get_string('subject', 'artefact.calendar');
      $message_body = "";   
      foreach (array_keys($users_to_be_reminded[$user]) as $day){
        $message_body .=  get_string('on', 'artefact.calendar').' '.$day.": \n";
        foreach ($users_to_be_reminded[$user][$day] as $task)
          $message_body .= $task."\n";
        $message_body .= "\n\n";
      }
     
      $message->message = get_string('message', 'artefact.calendar')."\n\n".$message_body;
      activity_occurred('maharamessage', $message);
    }
  }


}


class ArtefactTypeCalendar extends ArtefactType {
	
  /**
  * To add a color in the color picker simply add it here, and increase $color_num
  */
  private static $available_colors = array('660000',
                                          '006600',
                                          '000066',
                                          '666600',
                                          '660066',
                                          '006666',
                                          '990000',
                                          '009900',
                                          '000099',
                                          '999900',
                                          '990099',
                                          '009999',
                                          'dd0000',
                                          '00dd00',
                                          '0000dd',
                                          'dddd00',
                                          'dd00dd',
                                          '00dddd',
                                          'F900F9',
                                          'DD75DD',
                                          'BD5CFE',
                                          'AE70ED',
                                          '9588EC',
                                          '6094DB',
                                          '44B4D5',
                                          'C27E3A',
                                          'C47557',
                                          'B05F3C',
                                          'C17753',
                                          'B96F6F',
                                          'D73E68',
                                          'B300B3',
                                          '8D18AB',
                                          '5B5BFF',
                                          '25A0C5');

  private static $color_num = 35; //number of available colors 

  private static $available_dates = array('-1',
                                         '0',
                                         '1',
                                         '2',
                                         '3',
                                         '4',
                                         '5',
                                         '6',
                                         '7',
                                         '14',
                                         '21',
                                         '28');

	public function render_self($options) {
		return get_string('calendar', 'artefact.calendar');
	}
	
	public static function get_icon($options=null) {
		
	}
	
	public static function is_singular() {
		return false;
	}
	
	public static function get_links($id) {
		
	}

  



  /**
   * Builds the plans calendar
   *
   * @param plans (reference)
   */
  public static function build_calendar_html(&$plans) {

    global $SESSION,$USER;

     //if status is changed
    if(isset($_GET['ajax'])){

      if(isset($_GET['status']))//status gets changed
        ArtefactTypeCalendar::save_status_to_db($_GET['plan'], $_GET['status']);

      else if (isset($_GET['color'])) //color gets changed
        ArtefactTypeCalendar::save_color_to_db($_GET['picker'], $_GET['color']);

      else if (isset($_GET['reminder'])){
        if(isset($_GET['reminder_status']))
          ArtefactTypeCalendar::save_reminder_status_to_db($_GET['reminder'],$_GET['reminder_status'], $plans);
        else 
          ArtefactTypeCalendar::save_reminder_date_to_db($_GET['reminder'],$_GET['reminder_date'], $plans);
        }
    }
    else{
      
      $plan_count = count($plans['data']);
     	$dates = ArtefactTypeCalendar::get_calendar_dates(); //function that calculates all dates
      
      $new_task = $_GET['new_task']; //is set to 1 if new task is added
      $parent = $_GET['parent'];

      if(isset($_GET['task_info']))
        $task_info = $_GET['task_info']; //is set to task id if info overlay needs to be shown
      else $task_info = 0;

      if(isset($_GET['edit'])) //is set to task id if task is edited
        $edit = param_integer('edit');
      else
        $edit = $task_info;

      if($edit != 0){ //if task needs to be edited, get form
        $form = ArtefactTypeCalendar::get_task_form($edit);
      }     
      else 
          $form = 0;    

      if(isset($_GET['edit_plan'])){
        $edit_plan = param_integer('edit_plan');
        $edit_plan_tasks = ArtefactTypeTask::get_tasks($edit_plan,0,100); //if plan needs to be edited, get form
      }
      else 
          $edit_plan_tasks = 0;

      $task_count = array(); //array with number of tasks per plan
      $task_count_completed = array(); //array with number of completed tasks per plan

      for($i = 0; $i < $plan_count; $i++){ //loop through all plans

        $id = $plans['data'][$i]->id; //get ids
        $tasks = ArtefactTypeTask::get_tasks($id,0,100);
        $task_count[$id] = $tasks['count']; 
        $task_count_completed[$id] = 0;

        for($j = 0; $j < $task_count[$id]; $j++){
           if($tasks['data'][$j]->completed == 1)
            $task_count_completed[$id]++;
        }

         //get title and description of edited plan 
        if($id == $edit_plan){ //plan is edited plan
             $edit_plan_title = $plans['data'][$i]->title;
             $edit_plan_description = $plans['data'][$i]->description;
           }
      }

      if(isset($_GET['edit_plan_itself']))
        $edit_plan_itself = isset($_GET['edit_plan_itself']);      

      if(isset($_GET['title'])){
        ArtefactTypeCalendar::submit_task($dates, $task_info); //if edit task form was send, submit the task
      }

      //if edit plan form was send, get data

      if(isset($_GET['plan_title'])){
        $plan_id = (int) $_GET['edit_plan'];
        $artefact = new ArtefactTypePlan($plan_id);
        $artefact->set('title', $_GET['plan_title']);
        $artefact->set('description', $_GET['plan_description']);
        $artefact->commit();
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit_plan='.$plan_id);
      }

      //if new plan form was send, get data
      
      if(isset($_GET['newplan_title'])){
        $artefact = new ArtefactTypePlan();
        $artefact->set('owner', $USER->get('id'));
        $artefact->set('title', $_GET['newplan_title']);
        $artefact->set('description', $_GET['newplan_description']);
        $artefact->commit();
        $new_plan_id = $artefact->get('id');
        if(isset($_GET['newplan_color']) && $_GET['newplan_color'] != "")
          ArtefactTypeCalendar::save_color_to_db($new_plan_id, $_GET['newplan_color']);
        if(isset($_GET['newplan_reminder'])){
          echo 'active';
          ArtefactTypeCalendar::save_reminder_status_to_db($new_plan_id, 1);
        }
        else{
          echo 'inactive';
          ArtefactTypeCalendar::save_reminder_status_to_db($new_plan_id, 0);
        }
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit_plan='.$new_plan_id);
      }

      // if plan is finally to be deleted

      if(isset($_GET['delete_plan_final'])){
        $delete_plan_id = $_GET['delete_plan_final'];
        $todelete = new ArtefactTypePlan($delete_plan_id);
        
        if (!$USER->can_edit_artefact($todelete)) 
          throw new AccessDeniedException(get_string('accessdenied', 'error'));
        
        $todelete->delete();
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year']);
      }

       // if task is finally to be deleted

      if(isset($_GET['delete_task_final'])){
        $delete_task_id = $_GET['delete_task_final'];
        $todelete = new ArtefactTypeTask($delete_task_id);
        
        if (!$USER->can_edit_artefact($todelete)) 
          throw new AccessDeniedException(get_string('accessdenied', 'error'));
        
        $todelete->delete();
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit_plan='.$edit_plan);
      }
    
      $plans_status = ArtefactTypeCalendar::get_status_of_plans($plans);//status for all plans

      $task_per_day = ArtefactTypeCalendar::build_task_per_day($dates, $plans); // get all tasks, check which tasks happen this month 
      
      $number_of_tasks_per_day = array(); //if more than 3, displayed in calendar
      $full_format = get_string('full_format', 'artefact.calendar'); //full date format
      $full_format = str_replace('$month_name', $dates['month_name'], $full_format); //month name and year can directly be replaced
      $full_format = str_replace('$year', $dates['year'], $full_format);
      $full_dates = array(); //full date for each day

      for($j = 1; $j <= count($task_per_day); $j++){   
        $full_dates[$j] = str_replace('$day', $j, $full_format);
        $number_of_tasks_per_day[$j] = count($task_per_day[$j]);
      }
      
      $number_of_tasks_per_plan_per_day = ArtefactTypeCalendar::get_number_of_tasks_per_plan_per_day($plans, $dates); //array of javascript arrays with number of tasks per day for each plan

      $calendar = ArtefactTypeCalendar::build_calendar_array($dates);  //calendar is filled with dates

      $colors = ArtefactTypeCalendar::get_colors($plans);     //colors for each plan
      $available_colors = self::$available_colors; //available colors for color picker

      $reminder_status_per_plan = ArtefactTypeCalendar::get_reminder_status($plans);

      $reminder_date_per_plan = ArtefactTypeCalendar::get_reminder_date($plans);

      $available_dates = self::$available_dates;
      $reminder_dates = array();
      $reminder_strings = 'new Array('; //javascript array of reminder strings
      $num_dates = count($available_dates);

      for($u = 0; $u < $num_dates; $u++){
        $reminder_string = get_string('reminder_date'.$available_dates[$u], 'artefact.calendar');
        $reminder_dates[$available_dates[$u]] = $reminder_string; //php array
        $reminder_strings .= 'new Array('.$available_dates[$u].',"'.$reminder_string.'")'; //javascript array
        if($u < $num_dates - 1)
          $reminder_strings .= ',';
      }
      $reminder_strings .= ')';
      
      $planids_js = 'new Array('; //javascript array of plan ids
      for($m = 0; $m < $plan_count; $m++){ //loop through all plans
        
        $id = $plans['data'][$m]->id;
        $planids_js .= '"'.$id.'"';
        if($m < $plan_count - 1)
          $planids_js .= ',';
      }
      $planids_js .= ")";
    
      $plan_short_titles = array(); //short titles for plans, if plan title is too long

      for($m = 0; $m < $plan_count; $m++){ //loop through all plans
        
        $id = $plans['data'][$m]->id;
        $plan_title = $plans['data'][$m]->title;
        if(strlen($plan_title) > 12){ //shortens title (long titles kill calendar view)
          mb_internal_encoding("UTF-8");
          $plan_short_titles[$id] = mb_substr($plan_title,0,11).'…';
        }
        else {
          $plan_short_titles[$id] = $plan_title;
        }
      }

      /**
      * assigns for smarty
      */

      $smarty = smarty_core();
     
      // plans
      $smarty->assign_by_ref('plans', $plans);
      $smarty->assign_by_ref('plan_count', $plan_count);
      $smarty->assign_by_ref('plan_short_titles', $plan_short_titles);
      $smarty->assign_by_ref('task_count', $task_count);
      $smarty->assign_by_ref('task_count_completed', $task_count_completed); 
      $smarty->assign_by_ref('number_of_tasks_per_day', $number_of_tasks_per_day);
      $smarty->assign_by_ref('number_of_tasks_per_plan_per_day', $number_of_tasks_per_plan_per_day);

      //reminder
      $smarty->assign_by_ref('planids_js', $planids_js);
      $smarty->assign_by_ref('reminder_status_per_plan', $reminder_status_per_plan);
      $smarty->assign_by_ref('reminder_date_per_plan', $reminder_date_per_plan);
      $smarty->assign_by_ref('reminder_dates', $reminder_dates);
      $smarty->assign_by_ref('reminder_strings', $reminder_strings);

      // form for 'edit task' and elements for 'edit plan', 'new task' and 'delete task'
      $smarty->assign_by_ref('form', $form);
      $smarty->assign_by_ref('edit_id', $edit);
      $smarty->assign_by_ref('edit_plan_id', $edit_plan);
      $smarty->assign_by_ref('edit_plan_itself', $edit_plan_itself);
      $smarty->assign_by_ref('edit_plan_tasks', $edit_plan_tasks);
      $smarty->assign_by_ref('edit_plan_title', $edit_plan_title);
      $smarty->assign_by_ref('edit_plan_description', $edit_plan_description);
      $smarty->assign_by_ref('parent_id', $parent);
      $smarty->assign_by_ref('new_task', $new_task);
      $smarty->assign_by_ref('task_info', $task_info);

      // colors and status
      $smarty->assign_by_ref('colors', $colors);
      $smarty->assign_by_ref('available_colors', $available_colors);
      $smarty->assign_by_ref('plans_status', $plans_status);

      // dates
      $smarty->assign_by_ref('year', $dates['year']);
      $smarty->assign_by_ref('month', $dates['month']);
      $smarty->assign_by_ref('today', $dates['today']);
      $smarty->assign_by_ref('num_days', $dates['num_days']);
      $smarty->assign_by_ref('next_month',$dates['next_month']);
     	$smarty->assign_by_ref('next_month_year', $dates['next_month_year']);
     	$smarty->assign_by_ref('this_month',$dates['this_month']);
     	$smarty->assign_by_ref('this_year', $dates['this_year']);
     	$smarty->assign_by_ref('past_month', $dates['past_month']);
     	$smarty->assign_by_ref('past_month_year', $dates['past_month_year']);
      $smarty->assign_by_ref('month_name', $dates['month_name']);
      $smarty->assign_by_ref('task_per_day', $task_per_day);
      $smarty->assign_by_ref('week_start', $dates['week_start']);
      $smarty->assign_by_ref('full_dates', $full_dates);
      $smarty->assign_by_ref('calendar', $calendar);

      // smarty fetch
      $plans['tablerows'] = $smarty->fetch('artefact:calendar:calendar.tpl');
    }
  }


  /**
 * Calculates all dates for build_calendar_html
 */
 private static function get_calendar_dates(){
  
  if(isset($_GET['month'])){ //date is specified in URL 
  
    $month = $_GET['month'];
        
    if(isset($_GET['year']))
      $year = $_GET['year'];
    else 
       $year = date('Y',time());  
       
    if(($month != date('n',time())) || ($year != date('Y',time())))
      $today = -1; 
    else 
      $today = date('d',time());
  }
  
  else{ //this month
    $today = date('d',time()); //used for marking today (only if it's this month)
      $month = date('n',time());
      $year = date('Y',time());  
  }
  
  $this_month = date('n',time());
  $this_year = date('Y',time());
   
  $weekday = date('w', mktime(0,0,0,$month,1,$year)); //numeric day of the week the month started (0 = sunday, 6 = saturday)
  $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
  
  $week_start = get_string('week_start', 'artefact.calendar');  //week starts monday/sunday, depending on language, filled with empty days, depending on day the month starts with

  if($week_start == 1){ //monday is start day of each week
    if($weekday == 0) 
      $empty_days = 6;
    else $empty_days = $weekday - 1;
  }
  else {//sunday is start of each week
    $empty_days = $weekday;
  }

  $days_total = $empty_days + $num_days;

  //number of days of past month, number of past month, number of year of past month
  if($month == 1)
    {
      $past_month = 12;
      $num_days_past = 31;
      $past_month_year = $year - 1;  //the year of the past month         
    }   
  else
    {
      $past_month = $month - 1;
      $past_month_year = $year; //the year of the past month   
      $num_days_past = cal_days_in_month(CAL_GREGORIAN, date($past_month,time()), $past_month_year);
      
    }         
  $end_of_last_month = $num_days_past.'.'.$past_month.'.'.$past_month_year;
 
  if($month == 12){
    $next_month = 1;
    $next_month_year = $year + 1; //the year of next month
  }
  else {
    $next_month = $month+1;
    $next_month_year = $year;   
  }

  $month_name = get_string($month, 'artefact.calendar'); //name of the month    

$return = array('today' => $today, 
        'month' => $month, 
        'year' => $year, 
        'weekday' => $weekday, 
        'num_days' => $num_days,
        'empty_days' => $empty_days,
        'days_total' => $days_total,
        'end_of_last_month' => $end_of_last_month,
        'next_month' => $next_month,
        'next_month_year' => $next_month_year,
        'past_month' => $past_month,
        'past_month_year' => $past_month_year,
        'this_month' => $this_month,
        'this_year' => $this_year,
        'month_name' => $month_name,
        'week_start' => $week_start);
        
return $return;
}

  /**
  * Gets all data for task form
  */

  private static function get_task_form($edit){

    $edit_task = new ArtefactTypeTask($edit);
    $form_elements = (ArtefactTypeTask::get_taskform_elements($edit_task->parent, $edit_task));
    $form_title = $form_elements['title']['defaultvalue'];
    $form_date = $form_elements['completiondate']['defaultvalue'];
    $form_date_display = date(get_string('display_format', 'artefact.calendar'), $form_date);
    $form_date_hidden = date('Y/m/d', $form_date);
    $form_description = $form_elements['description']['defaultvalue'];
    $form_completed = $form_elements['completed']['defaultvalue'];
    $form = array(
      'title' => $form_title, 
      'completiondate_display' => $form_date_display, 
      'completiondate' => $form_date_hidden, 
      'description' => $form_description, 
      'completed' => $form_completed);

    return $form;
  }

  /**
  * Submits the task (see the submit function of plans plugin)
  */

  private static function submit_task($dates, $task_info){
    global $USER;

    $parent =  $_GET['parent'];
    $id = (int) $_GET['task'];

    if ($id != 0) 
      $artefact = new ArtefactTypeTask($id);
    else {
        $artefact = new ArtefactTypeTask();
        $artefact->set('owner', $USER->get('id'));
        $artefact->set('parent', $parent);
    }
    $artefact->set('title', $_GET['title']);
    $artefact->set('description', $_GET['description']);
    $artefact->set('completed', $_GET['completed'] ? 1 : 0);
    $artefact->set('completiondate', $_GET['completiondate']);
    $artefact->commit();

    if($parent != 0)
      redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit_plan='.$parent);
    elseif ($task_info != 0) 
      redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&task_info='.$id);
    else 
      redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year']);
  }

  /**
  * Fills the task_per_day array which all tasks that happen on each day in the specific month
  */

  private static function build_task_per_day($dates, $plans){

    $task_per_day = array(); //array with all tasks of one day

    for($m = 1; $m <= $dates['num_days']; $m++) //two-dimensional array for every day of the month
      $task_per_day[$m] = array(); //array for each day
        
    for($i = 0; $i < count($plans['data']); $i++){ //loop through all plans

      $id = $plans['data'][$i]->id; //get id
      $task[$i] = ArtefactTypeTask::get_tasks($id,0,100); //get all tasks
      $task_count = $task[$i]['count'];

      for($j = 0; $j < $task_count; $j++){  

        $title = $task[$i]['data'][$j]->title; //task title
        $full_title = $title; //full title, other title will be shortened

        if(strlen($title) > 8){ //shortens title (long titles kill calendar view)
          mb_internal_encoding("UTF-8");
          $title = mb_substr($title,0,7).'…';
        }

        $task_id = $task[$i]['data'][$j]->id; //id of the task
        $completed = $task[$i]['data'][$j]->completed; // check if task is completed
        $completiondate = $task[$i]['data'][$j]->completiondate; // completiondate
        $parent_id = $task[$i]['data'][$j]->parent;  //id of tasks parent

        $description = $task[$i]['data'][$j]->description; //task description
        if($description == '') 
          $description = get_string('nodescription', 'artefact.calendar');

        //the get_tasks functions gets the completiondate with month name written out, which leads to problems in other languages, therefore we use a different function to get the timestamp
        $completiondate_task = new ArtefactTypeTask($task_id);
        $completiondate_task_elements = (ArtefactTypeTask::get_taskform_elements($completiondate_task->parent, $completiondate_task));
        
        $timestamp_completion = $completiondate_task_elements['completiondate']['defaultvalue'];
        $timestamp_start_month = strtotime(date(($dates['end_of_last_month']),time()));
        $timestamp_end_month = strtotime(date('1.'.$dates['next_month'].'.'.$dates['next_month_year'],time()));         

        if(($timestamp_completion >  $timestamp_start_month) && ($timestamp_completion < $timestamp_end_month)) { //check if completiondate is in this month
          $day_of_completion = date('j', $timestamp_completion);
          $num_tasks = count($task_per_day[$day_of_completion]); //calculates how many tasks happen on this day
          $task_completiondate = date('Y/m/d', $timestamp_completion);// completiondate in YYYY/MM/DD format       
          
          $task_per_day[$day_of_completion][$num_tasks] = array('title' => $title, 
                                                                'task_id' => $task_id,
                                                                'parent_id' => $parent_id, 
                                                                'completed' => $completed, 
                                                                'full_title' => $full_title, 
                                                                'description' => $description, 
                                                                'task_completiondate' =>$task_completiondate);
        }
      }
    }

    return $task_per_day;
  }

  /**
  * Builds a two-dimensional calendar array, each week contains an array with 7 dates 
  */

  private static function build_calendar_array($dates){

    $week = array();

    if($dates['empty_days'] > 0)
      $week = array_fill(0, $dates['empty_days'], ''); //calendar starts monday, filled with empty days, depending on day of the week the month started with

    $calender = array();
    $i = 1;
    $row = 0;
    while($i <= $dates['num_days']){ 
      while(count($week) < 7){
        if($i <= $dates['num_days']){//number of date is pushed into the calendar, two-dimensional array, inner array per week
          array_push($week, $i);
        }
        else array_push($week, '');
        $i++;
      }
      $calendar[$row] = $week;
      $week = array();
      $row++;
    }
    return $calendar;
  }

  /**
  * Returns an array with the number of tasks each plan has on each day
  */

  private static function get_number_of_tasks_per_plan_per_day($plans, $dates){
    $plan_count = count($plans['data']);

    for($i = 0; $i < $plan_count; $i++){
      $id = $plans['data'][$i]->id;
      $tasks_per_plan_per_day[$id] = array(); //array with plans as keys and arrays of days as values
      for($m = 1; $m <= $dates['num_days']; $m++) //two-dimensional array for every day of the month
        $tasks_per_plan_per_day[$id][$m] = 0; //array for each day, initialized with 0
    }
    
    for($i = 0; $i < $plan_count; $i++){ //loop through all plans

      $id = $plans['data'][$i]->id; //get id
      $task[$i] = ArtefactTypeTask::get_tasks($id,0,100); //get all tasks
      $task_count = $task[$i]['count'];

      for($j = 0; $j < $task_count; $j++){  

        $task_id = $task[$i]['data'][$j]->id; //id of the task

        $completiondate_task = new ArtefactTypeTask($task_id);
        $completiondate_task_elements = (ArtefactTypeTask::get_taskform_elements($completiondate_task->parent, $completiondate_task));
        
        $timestamp_completion = $completiondate_task_elements['completiondate']['defaultvalue'];
        $timestamp_start_month = strtotime(date(($dates['end_of_last_month']),time()));
        $timestamp_end_month = strtotime(date('1.'.$dates['next_month'].'.'.$dates['next_month_year'],time()));         

        if(($timestamp_completion >  $timestamp_start_month) && ($timestamp_completion < $timestamp_end_month)) { //check if completiondate is in this month
          $day_of_completion = date('j', $timestamp_completion);   
          $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
        }
      }

      //turn into javascript array so number can be dynamically changed later on
      $temp = $tasks_per_plan_per_day[$id];
      $tasks_per_day = 'new Array('; //javascript array of plan ids

      for($u = 1; $u <= count($temp); $u++){
          $tasks_per_day .= $temp[$u];
          if($u < count($temp))
            $tasks_per_day .= ',';
        }
        $tasks_per_day .= ")";
        $tasks_per_plan_per_day[$id] = $tasks_per_day;
      }
    return $tasks_per_plan_per_day;
  }

  /**
  * Gets stored colors for each plan
  * Random color for each plan, if no color is stored in db
  */

  private static function get_colors($plans){

    $colors = array();
   
    for($i = 0; $i < count($plans['data']); $i++){
      $id = $plans['data'][$i]->id;

      ($result = get_records_sql_array("
            SELECT plan, status, color
                FROM {artefact_calendar_calendar} WHERE plan = '$id'", array()))
            || ($result = array());

      if (!empty($result[0])) 
        $colors[$plans['data'][$i]->id] = $result[0]->color;
      else { //if there is no color stored for the plan a random color is picked
        $rand = rand(0,self::$color_num-1);
        $available_colors = self::$available_colors;
        $colors[$plans['data'][$i]->id] = $available_colors[$rand]; //random hex color
        ArtefactTypeCalendar::save_color_to_db($plans['data'][$i]->id, $available_colors[$rand]); //choosen color is saved to db
      }
    }

    return $colors;
  } 

  /**
  * Color is stored in db
  */

  private static function save_color_to_db($plan, $color){

    db_begin();

    $status = ArtefactTypeCalendar::get_status($plan);
  
    $data = (object)array(
            'plan'  => $plan,
            'status' => $status,
            'color' => $color,
        );
    
    ($result = get_records_sql_array("
            SELECT plan, status, color
                FROM {artefact_calendar_calendar} WHERE plan = '$plan'", array()))
            || ($result = array());
    
    if (!empty($result[0])) 
      update_record('artefact_calendar_calendar', $data, 'plan'); //update table
    else 
      insert_record('artefact_calendar_calendar', $data); //insert into table

    db_commit();
  }

  /**
  * Gets stored status for each plan
  */

   private static function get_status_of_plans($plans){
    $plans_status = array();

    for($i = 0; $i < count($plans['data']); $i++){
      $id = $plans['data'][$i]->id;
      $plans_status[$id] = ArtefactTypeCalendar::get_status($id);
    }

    return $plans_status;
   }

  /**
  * Gets stored status
  */

   private static function get_status($plan){

    ($result = get_records_sql_array("
                SELECT status
                FROM {artefact_calendar_calendar} WHERE plan = '$plan'", array()))
            || ($result = array());

    if (!empty($result[0])) 
      $status = $result[0]->status;
    else  //if there is no status stored for the plan, status is 1 (active) by default
      $status = 1;

    return $status;
  }

  /**
  * Status is stored in db
  */

  private static function save_status_to_db($plan, $status){

    db_begin();
  
    $data = (object)array(
            'plan'  => $plan,
            'status' => $status,
        );
    
    ($result = get_records_sql_array("
            SELECT plan, status, color
                FROM {artefact_calendar_calendar} WHERE plan = '$plan'", array()))
            || ($result = array());
    
    if (!empty($result[0])) 
      update_record('artefact_calendar_calendar', $data, 'plan'); //update table
    else 
      insert_record('artefact_calendar_calendar', $data); //insert into table

    db_commit();
  }

  /**
  * Gets stored reminder status for each plan
  * Set to 1 if new plan, reminder date is set according to other plans
  */

  private static function get_reminder_status($plans){

    $reminder_status_per_plan = array();
   
    for($i = 0; $i < count($plans['data']); $i++){
      $id = $plans['data'][$i]->id;

      ($result = get_records_sql_array("
            SELECT plan, reminder_status
                FROM {artefact_calendar_calendar} WHERE plan = '$id'", array()))
            || ($result = array());

      if (!empty($result[0])) 
        $reminder_status_per_plan[$id] = $result[0]->reminder_status;
      else  //if there is no status stored for the plan, status is 1 (active) by default
        $reminder_status_per_plan[$id] = 1;
    }

    return $reminder_status_per_plan;
  } 

  private static function save_reminder_status_to_db($reminder,$reminder_status, $plans){
    db_begin();
    
    if($reminder == 'all'){
        for($i = 0; $i < count($plans['data']); $i++){ //loop through all plans
          $id = $plans['data'][$i]->id;
          $data = (object)array(
            'plan'  => $id,
            'reminder_status' => $reminder_status,
            );
          update_record('artefact_calendar_calendar', $data, 'plan'); //update table
        }
    }
    else{
      $data = (object)array(
          'plan'  => $reminder,
          'reminder_status' => $reminder_status,
          );
        update_record('artefact_calendar_calendar', $data, 'plan'); //update table
     }
    db_commit();
  }

  /**
  * Gets stored reminder status for each plan
  * Set to 1 if new plan, reminder date is set according to other plans
  */

  private static function get_reminder_date($plans){

    $reminder_date_per_plan = array();
    $plan_count = count($plans['data']);

    if($plan_count == 0)
      $reminder_date_per_plan = -1;

    for($i = 0; $i < $plan_count; $i++){
      $id = $plans['data'][$i]->id;

      ($result = get_records_sql_array("
            SELECT plan, reminder_date
                FROM {artefact_calendar_calendar} WHERE plan = '$id' AND reminder_date IS NOT NULL", array()))
            || ($result = array());

      if (!empty($result[0])) 
        $reminder_date_per_plan[$id] = $result[0]->reminder_date;
      else { //if there is no date stored for the plan, date is set according to other plans, if no other plans then -1 (inactive)       
        $found = false;
        for($j = 0; $j < $plan_count && $found == false; $j++){
          $other_plan_id = $plans['data'][$j]->id;

          ($result = get_records_sql_array("
                SELECT plan, reminder_date
                    FROM {artefact_calendar_calendar} WHERE plan = '$other_plan_id' AND reminder_date IS NOT NULL", array()))
                || ($result = array());

          if (!empty($result[0])) {
            $found = true;
            $reminder_date_per_plan[$id] = $result[0]->reminder_date;
          }
        }
        if(!$found)
           $reminder_date_per_plan[$id] = -1;
         ArtefactTypeCalendar::save_reminder_date_to_db($id, $reminder_date_per_plan[$id]);
      }
    }
    return $reminder_date_per_plan;
  } 


  private static function save_reminder_date_to_db($reminder,$reminder_date, $plans){
     db_begin();
    
    if($reminder == 'all'){
        for($i = 0; $i < count($plans['data']); $i++){ //loop through all plans
          $id = $plans['data'][$i]->id;
          $data = (object)array(
            'plan'  => $id,
            'reminder_date' => $reminder_date,
            );
          update_record('artefact_calendar_calendar', $data, 'plan'); //update table
        }
    }
    else{
      $data = (object)array(
          'plan'  => $reminder,
          'reminder_date' => $reminder_date,
          );
        update_record('artefact_calendar_calendar', $data, 'plan'); //update table
     }
    db_commit();
  }

}

?>
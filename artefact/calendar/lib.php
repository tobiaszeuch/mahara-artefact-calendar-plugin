
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

  private static $max_reminder_days = 365;

	public static function get_artefact_types() {
		return array(
		'calendar','event'
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
      ),
      (object)array(
      'callfunction' => 'clean_db_table',
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

    //users that chose reminders for individual plans
    for($i = 0; $i <= self::$max_reminder_days; $i++){ // get all plans with reminder set to x days ahead    
      ($results = get_records_sql_array("SELECT id, owner FROM {artefact} 
        JOIN {artefact_calendar_calendar} ON artefact.id = artefact_calendar_calendar.plan WHERE artefacttype = 'plan' AND reminder_date =  '$i';", array()))
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

    //users that chose reminders for all plans
    for($k = 0; $k <= self::$max_reminder_days; $k++){ // get all plans with reminder set to x days ahead    
      ($results = get_records_sql_array("SELECT id, owner FROM artefact_calendar_reminder JOIN artefact ON artefact.owner = artefact_calendar_reminder.user WHERE artefacttype = 'plan' and reminder_type = '1' AND reminder_date = '$k';", array()))
            || ($results = array()); //get plans and user ids

      if (!empty($results[0])) {
        
        foreach ($results as $result) {
          $userid = $result->owner;
          $planid = $result->id;
          $tasks = PluginArtefactCalendar::get_task_in_x_days($k,$planid);//get tasks of plans

          if(count($tasks)){

            $day = array_keys($tasks);
            $day = $day[0]; //first key is the date of the tasks
           
            for ($l = 0; $l < count($tasks[$day]); $l++){ 
              $num = count($users_to_be_reminded[$userid][$day]);
              $users_to_be_reminded[$userid][$day][$num] = $tasks[$day][$l];  
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

  /**
  * Database table is cleaned of obsolete rows (deleted plans)
  */

  public static function clean_db_table(){
    ($results = get_records_sql_array("SELECT plan FROM {artefact_calendar_calendar};", array()))
            || ($results = array()); //get plans and user ids

    if (!empty($results[0])) {
      foreach ($results as $result) { 
        ($temp_results = get_records_sql_array("SELECT * FROM {artefact} WHERE id = $result->plan AND artefacttype = 'plan' LIMIT 1;", array()))
            || ($temp_results = array());
        if (empty($temp_results[0]))
          delete_records('artefact_calendar_calendar', 'plan', $result->plan);
      }
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
    if(isset($_GET['ajax']))
      ArtefactTypeCalendar::ajax_handling($plans);

    else{
      $dates = ArtefactTypeCalendar::get_calendar_dates(); //function that calculates all dates
      if(isset($_POST['reminder_submit']))
          ArtefactTypeCalendar::save_reminder_settings($plans);
      if(isset($_GET['title'])){ //if edit task/event form was send, submit the task/event
          if(($_GET['type']) == 'task')
            ArtefactTypeCalendar::submit_task($dates, $cal_variables['task_info']); 
          else if (($_GET['type']) == 'event')
            ArtefactTypeEvent::submit_event($dates);
        }
      else if(isset($_GET['plan_title'])) //if edit plan form was sent
       ArtefactTypeCalendar::edit_plan_handler($dates);

      else if(isset($_GET['newplan_title']))  //if new plan form was sent
        ArtefactTypeCalendar::new_plan_handler($dates);
      
      else if(isset($_GET['delete_plan_final']))// if plan is to be deleted
        ArtefactTypeCalendar::delete_plan_handler($dates);

      else if(isset($_GET['delete_task_final']))  // if task is to be deleted
        ArtefactTypeCalendar::delete_task_handler($dates);

      else if(isset($_GET['delete_event_final']))  // if task is to be deleted
        ArtefactTypeCalendar::delete_event_handler($dates);
      
      else if(isset($_GET['regenerate'])){ // if feed url needs to be regenerated
        if($_GET['regenerate'] == 1){
          ArtefactTypeCalendar::generate_feed_url($USER->id, 0);
          redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&newfeed=1');
        }
      }
      else{

        $cal_variables = ArtefactTypeCalendar::get_cal_variables(); 
        
        $form = 0;
        $edit_task_id = $cal_variables['edit_task_id'];
        $edit_event_id = $cal_variables['edit_event_id'];
        if($edit_task_id != 0) //if task needs to be edited, get form
          $form = ArtefactTypeCalendar::get_task_form($edit_task_id);
        else if($edit_event_id != 0) 
          $form = ArtefactTypeEvent::get_event_form($edit_event_id);
        else if($_GET['missing_title'] == '1' || $_GET['missing_date'] == '1') { //if no title or date is specified for a new task/plan, error message is displayed and fields are refilled    
          $form = ArtefactTypeCalendar::get_missing_field_info(); //handling for tasks
          if(isset($_GET['missing_field_description']))//handling for plans
            $missing_field_description = $_GET['missing_field_description']; 
          else $missing_field_description = "";   
        }

        $plan_count = count($plans['data']);
        $calendar_weeks = ArtefactTypeCalendar::get_calendar_weeks($dates['month'], $dates['year']);
        $edit_plan_info = ArtefactTypeCalendar::get_edit_plan_info($plans, $missing_field_description);
        $task_count_info = ArtefactTypeCalendar::get_task_count_info($plans);
        $feed_url = ArtefactTypeCalendar::get_feed_url();
        $plans_status = ArtefactTypeCalendar::get_status_of_plans($plans);//status for all plans
        $task_per_day = ArtefactTypeCalendar::build_task_per_day($dates, $plans); // get all tasks, check which tasks happen this month
        $event_per_day = ArtefactTypeEvent::build_event_per_day($dates, $plans); 
        $full_format = get_string('full_format', 'artefact.calendar'); //full date format
        $full_format = str_replace('$month_name', $dates['month_name'], $full_format); //month name and year can directly be replaced
        $full_format = str_replace('$year', $dates['year'], $full_format);
        
        $full_dates = array(); //full date for each day
        $number_of_tasks_and_events_per_day = array(); //if more than 3, displayed in calendar
        
        for($j = 1; $j <= count($task_per_day); $j++){   
          $full_dates[$j] = str_replace('$day', $j, $full_format);
          $number_of_tasks_and_events_per_day[$j] = count($task_per_day[$j]) + count($event_per_day[$j]);
        }
        
        $number_of_tasks_per_plan_per_day = ArtefactTypeCalendar::get_number_of_tasks_per_plan_per_day($plans, $dates); //array of javascript arrays with number of tasks per day for each plan
        $calendar = ArtefactTypeCalendar::build_calendar_array($dates);  //calendar is filled with dates
        $colors = ArtefactTypeCalendar::get_colors($plans);     //colors for each plan
        $available_colors = self::$available_colors; //available colors for color picker
        $reminder_date_per_plan = ArtefactTypeCalendar::get_reminder_date_per_plan($plans);
        $reminder_date_all = ArtefactTypeCalendar::get_reminder_date_all();
        $reminder_dates = ArtefactTypeCalendar::get_reminder_array();
        $reminder_type = ArtefactTypeCalendar::get_reminder_type();
        $plan_ids_js = ArtefactTypeCalendar::get_plan_ids_js($plans);
        $short_plan_titles = ArtefactTypeCalendar::get_short_plan_titles($plans);
        /**
        * assigns for smarty
        */
  
        $smarty = smarty_core();
       
        // plans
        $smarty->assign_by_ref('plans', $plans);
        $smarty->assign_by_ref('plan_count', $plan_count);
        $smarty->assign_by_ref('short_plan_titles', $short_plan_titles);
        $smarty->assign_by_ref('task_count', $task_count_info['task_count']);
        $smarty->assign_by_ref('task_count_completed', $task_count_info['task_count_completed']); 
        $smarty->assign_by_ref('number_of_tasks_and_events_per_day', $number_of_tasks_and_events_per_day);
        $smarty->assign_by_ref('number_of_tasks_per_plan_per_day', $number_of_tasks_per_plan_per_day);
        $smarty->assign_by_ref('new', $_GET['new']);

        //reminder
        $smarty->assign_by_ref('plan_ids_js', $plan_ids_js);
        $smarty->assign_by_ref('reminder_date_per_plan', $reminder_date_per_plan);
        $smarty->assign_by_ref('reminder_date_all', $reminder_date_all);
        $smarty->assign_by_ref('reminder_dates', $reminder_dates);
        $smarty->assign_by_ref('reminder_type', $reminder_type);

        // form for 'edit task' and elements for 'edit plan', 'new task' and 'delete task'
        $smarty->assign_by_ref('form', $form);
        $smarty->assign_by_ref('edit_task_id', $edit_task_id);
        $smarty->assign_by_ref('edit_event_id', $edit_event_id);
        $smarty->assign_by_ref('edit_plan_id', $edit_plan_info['edit_plan_id']);
        $smarty->assign_by_ref('edit_plan_itself', $cal_variables['edit_plan_itself']);
        $smarty->assign_by_ref('edit_plan_tasks_and_events', $edit_plan_info['edit_plan_tasks_and_events']);
        $smarty->assign_by_ref('edit_plan_title', $edit_plan_info['edit_plan_title']);
        $smarty->assign_by_ref('edit_plan_description', $edit_plan_info['edit_plan_description']);
        $smarty->assign_by_ref('parent_id', $cal_variables['parent_id']);
        $smarty->assign_by_ref('specify_parent', $cal_variables['specify_parent']);
        $smarty->assign_by_ref('new_task', $cal_variables['new_task']);
        $smarty->assign_by_ref('new_event', $cal_variables['new_event']);
        $smarty->assign_by_ref('task_info', $cal_variables['task_info']);
        $smarty->assign_by_ref('event_info', $cal_variables['event_info']);

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
        $smarty->assign_by_ref('event_per_day', $event_per_day);
        $smarty->assign_by_ref('week_start', $dates['week_start']);
        $smarty->assign_by_ref('am_pm', $dates['am_pm']);
        $smarty->assign_by_ref('years', $dates['years']);
        $smarty->assign_by_ref('hours', $dates['hours']);
        $smarty->assign_by_ref('minutes', $dates['minutes']);
        $smarty->assign_by_ref('full_dates', $full_dates);
        $smarty->assign_by_ref('calendar', $calendar);
        $smarty->assign_by_ref('calendar_weeks', $calendar_weeks);

        //feed
        $smarty->assign_by_ref('uid', $USER->id);
        $smarty->assign_by_ref('feed_url', $feed_url);
        $smarty->assign_by_ref('newfeed', $ $cal_variables['newfeed']);

        //missing title or date
        $smarty->assign_by_ref('missing_title', $_GET['missing_title']);
        $smarty->assign_by_ref('missing_date', $_GET['missing_date']);
        $smarty->assign_by_ref('wrong_date', $_GET['wrong_date']);
        $smarty->assign_by_ref('missing_repeat', $_GET['missing_repeat']);

        // smarty fetch
        $plans['tablerows'] = $smarty->fetch('artefact:calendar:calendar.tpl');
      }
    }
  }

  /**
  * Gets all calendar variables
  */
  private static function get_cal_variables(){
    $new_task = $newfeed = $task_info = $event_info = $edit_plan_itself = $specify_parent = 0;
    $parent_id = "";

    if(isset($_GET['new_task']))
      $new_task = $_GET['new_task']; //is set to 1 if new task is added
    if(isset($_GET['new_event']))
      $new_event = $_GET['new_event']; //is set to 1 if new event is added
    if(isset($_GET['parent_id']))
      $parent_id = $_GET['parent_id'];
    if(isset($_GET['newfeed']))
      $newfeed = $_GET['newfeed'];
    if(isset($_GET['task_info']))
      $task_info = $_GET['task_info']; //is set to task id if info overlay needs to be shown
    if(isset($_GET['event_info']))
      $event_info = $_GET['event_info']; //is set to event id if info overlay needs to be shown
    if(isset($_GET['edit_task_id'])) //is set to task id if task is edited
      $edit_task_id = param_integer('edit_task_id');
    else
      $edit_task_id  = $task_info;
    if(isset($_GET['edit_event_id'])) //is set to event id if task is edited
      $edit_event_id = param_integer('edit_event_id');
    else
      $edit_event_id  = $event_info;
    if(isset($_GET['edit_plan_itself']))
      $edit_plan_itself = $_GET['edit_plan_itself'];  
    if(isset($_GET['specify_parent']))
      $specify_parent = $_GET['specify_parent'];  

    return array("new_task" => $new_task,
                 "new_event" => $new_event,
                 "parent_id" => $parent_id,
                 "newfeed" => $newfeed,
                 "task_info" => $task_info,
                 "event_info" => $event_info,
                 "edit_task_id" => $edit_task_id,
                 "edit_event_id" => $edit_event_id,
                 "edit_plan_itself" => $edit_plan_itself,
                 "specify_parent" => $specify_parent); 
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

    //years for quick navigation -5 years to +5 years from now
    $years = array($year-5, $year-4, $year-3, $year-2, $year-1, $year, $year+1, $year+2, $year+3, $year+4, $year+5);

    //time for events according to language settings
    $am_pm = get_string('am_pm', 'artefact.calendar');

    $hours = array();
    for($i = 0; $i < 24; $i++)
      $hours[$i] = date('H', mktime($i,0,0,1,1,$year));
    $minutes = array();
    for($j = 0; $j < 60; $j+=5)
      $minutes[$j] = date('i', mktime(0,$j,0,1,1,$year));

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
          'week_start' => $week_start,
          'am_pm' => $am_pm,
          'years' => $years, 
          'hours' => $hours,
          'minutes' => $minutes);
          
    return $return;
  }

  /**
  * Returns array with number of calender weeks in specific month
  */

  private static function get_calendar_weeks($month, $year){
    $start = mktime(0,0,0,$month,1,$year);
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $calendar_weeks = array();

    for($i = 1; $i <= $num_days; $i++){
      $day = mktime(0,0,0,$month,$i,$year);
      if(date('w', $day) == 1) //weekday is monday
        $calendar_weeks[$i] = date('W', $day);
    }
   return $calendar_weeks;
  }

  /**
  * Handles db actions if ajax was used
  */
  private static function ajax_handling($plans){
    if(isset($_GET['status']))//status gets changed
      ArtefactTypeCalendar::save_status($_GET['plan'], $_GET['status']);
    else if (isset($_GET['color'])) //color gets changed
        ArtefactTypeCalendar::save_color($_GET['picker'], $_GET['color']);
  }

  /**
  * Commits plan information
  */

  private static function edit_plan_handler($dates){
    $plan_id = (int) $_GET['edit_plan'];
    if($_GET['plan_title'] != ""){
      $artefact = new ArtefactTypePlan($plan_id);
      $artefact->set('title', $_GET['plan_title']);
      $artefact->set('description', $_GET['plan_description']);
      $artefact->commit();
      redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit_plan='.$plan_id);
    }
    redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit_plan='.$plan_id.'&edit_plan_itself=1&missing_title=1&missing_field_description='.$_GET['plan_description']);
  }

  /**
  * Commits new plan information
  */

  private static function new_plan_handler($dates){
    global $USER;

    if($_GET['newplan_title'] != ""){
      $artefact = new ArtefactTypePlan();
      $artefact->set('owner', $USER->get('id'));
      $artefact->set('title', $_GET['newplan_title']);
      $artefact->set('description', $_GET['newplan_description']);
      $artefact->commit();
      $new_plan_id = $artefact->get('id');
      if(isset($_GET['newplan_color']) && $_GET['newplan_color'] != "")
        ArtefactTypeCalendar::save_color($new_plan_id, $_GET['newplan_color']);
      else{
        ArtefactTypeCalendar::save_random_color($new_plan_id);
      }
      redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit_plan='.$new_plan_id);
    }
    else
      redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&missing_title=1&new=1&missing_field_description='.$_GET['newplan_description']);  
  }

  /**
  * Delete plan
  */

  private static function delete_plan_handler($dates){
    global $USER;
    $delete_plan_id = $_GET['delete_plan_final'];
    $todelete = new ArtefactTypePlan($delete_plan_id);
    
    if (!$USER->can_edit_artefact($todelete)) 
      throw new AccessDeniedException(get_string('accessdenied', 'error'));
    
    $todelete->delete();
    redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year']);
  }

  /**
  * Delete task
  */

  private static function delete_task_handler($dates){
    global $USER;
    $delete_task_id = $_GET['delete_task_final'];
    $todelete = new ArtefactTypeTask($delete_task_id);
    
    if (!$USER->can_edit_artefact($todelete)) 
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
      
    $todelete->delete();
    redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year']);
  }

  /**
  * Delete event
  */

  private static function delete_event_handler($dates){
    global $USER;
    $delete_event_id = $_GET['delete_event_final'];
    $todelete = new ArtefactTypeEvent($delete_event_id);
    
    if (!$USER->can_edit_artefact($todelete)) 
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
      
    $todelete->delete();
    redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year']);
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
  * If a field is missing when a new task/plan is created, the information of the other fields is preserved
  */
  private static function get_missing_field_info(){

    $display_format = get_string('display_format', 'artefact.calendar');

    if($_GET['new_event'] == 1 || $_GET['edit_event_id'] != ""){
      if($_GET['missing_field_begin'] != ""){
        if($display_format == 'Y/m/d')
          $begin_display = $_GET['missing_field_begin'];
        else {
          $begin_parts = explode('/', $_GET['missing_field_begin']);
          $begin_display = $begin_parts[2].'.'.$begin_parts[1].'.'.$begin_parts[0];
        }
      }
      else
        $begin_display = "";
      
      return array(
          'title' => $_GET['missing_field_title'],
          'description' => $_GET['missing_field_description'],
          'begin' => $_GET['missing_field_begin'],
          'begin_display' => $begin_display);
    }
    else{
      if($_GET['missing_field_completiondate'] != ""){
        if($display_format == 'Y/m/d')
          $completiondate_display = $_GET['missing_field_completiondate'];
        else {
          $completiondate_parts = explode('/', $_GET['missing_field_completiondate']);
          $completiondate_display = $completiondate_parts[2].'.'.$completiondate_parts[1].'.'.$completiondate_parts[0];
        }
      }
      else
        $completiondate_display = "";
          
      return array(
          'title' => $_GET['missing_field_title'],
          'description' => $_GET['missing_field_description'],
          'completed' => $_GET['missing_field_completed'],
          'completiondate' => $_GET['missing_field_completiondate'],
          'completiondate_display' => $completiondate_display);
    }
  }

  /**
  * Submits the task (see the submit function of plans plugin)
  */

  private static function submit_task($dates, $task_info){
   
    global $USER;

    $parent =  $_GET['parent_id'];
    $id = (int) $_GET['task'];

    $title = $_GET['title'];
    $description = $_GET['description'];
    $completiondate = $_GET['completiondate'];
    $completed = $_GET['completed'] ? 1 : 0;

    if($title == "" || $completiondate == ""){
      if($title == "")
        $missing_title = "&missing_title=1";
      if($completiondate == "")
        $missing_date = "&missing_date=1";
    }
    else{
      if ($id != 0) 
        $artefact = new ArtefactTypeTask($id);
      else {
          $artefact = new ArtefactTypeTask();
          $artefact->set('owner', $USER->get('id'));
          $artefact->set('parent', $parent);
      }

      $artefact->set('title', $title);
      $artefact->set('description', $description);
      $artefact->set('completed', $completed);
      $artefact->set('completiondate', $completiondate);
      $artefact->commit();

      if ($task_info != 0) 
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&task_info='.$id);
      else 
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year']);
    }//no title or date were specified
    if($id != 0)
      redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit='.$id.$missing_title.'&missing_field_title='.$title.'&missing_field_description='.$description.'&missing_field_completiondate='.$completiondate.'&missing_field_completed='.$completed.'&parent='.$parent);
    else
      redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&specify_parent=1&new_task=1'.$missing_title.$missing_date.'&missing_field_title='.$title.'&missing_field_description='.$description.'&missing_field_completiondate='.$completiondate.'&missing_field_completed='.$completed.'&parent='.$parent);
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
      $task[$i] = ArtefactTypeTask::get_tasks($id,0,1000); //get all tasks
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

    $timestamp_start_month = strtotime(date(($dates['end_of_last_month']),time()));
    $timestamp_end_month = strtotime(date('1.'.$dates['next_month'].'.'.$dates['next_month_year'],time()));  
    
    for($i = 0; $i < $plan_count; $i++){ //loop through all plans

      $id = $plans['data'][$i]->id; //get id
      $task[$i] = ArtefactTypeTask::get_tasks($id,0,1000); //get all tasks
      $task_count = $task[$i]['count'];

      for($j = 0; $j < $task_count; $j++){  

        $task_id = $task[$i]['data'][$j]->id; //id of the task

        $completiondate_task = new ArtefactTypeTask($task_id);
        $completiondate_task_elements = (ArtefactTypeTask::get_taskform_elements($completiondate_task->parent, $completiondate_task));
        
        $timestamp_completion = $completiondate_task_elements['completiondate']['defaultvalue'];
       
        if(($timestamp_completion >  $timestamp_start_month) && ($timestamp_completion < $timestamp_end_month)) { //check if completiondate is in this month
          $day_of_completion = date('j', $timestamp_completion);   
          $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
        }
      }

      $event[$i] = ArtefactTypeEvent::get_events($id,0,1000); //get all events
      $event_count = $event[$i]['count'];

      for($j = 0; $j < $event_count; $j++){  

        $event_id = $event[$i]['data'][$j]->id; //id of the event
        $begin = $event[$i]['data'][$j]->begin; 
        $repeat_type = $event[$i]['data'][$j]->repeat_type;
        $end_date = $event[$i]['data'][$j]->end_date;
        $ends_after = $event[$i]['data'][$j]->ends_after;
        $repeats_every = $event[$i]['data'][$j]->repeats_every;

        if(($begin > $timestamp_start_month) && ($begin < $timestamp_end_month)) { //check if event is in this month
          $day_of_completion = date('j', $begin);   
          $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
        }
       //repeat is activated
        if($repeat_type == 1){//repeats daily
          if($end_date != 0){//repeat ends on date
            if($end_date > $timestamp_start_month){
              $begin_temp = $begin;
              $begin_temp += 86400;
              while($begin_temp < $end_date){
                if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                  $day_of_completion = date('j', $begin_temp);
                  $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
                }
                $begin_temp += 86400;
              }
            }
          }
          else if($ends_after != 0){//repeat ends after x times
            $begin_temp = $begin;
            for($l = 0; $l < $ends_after-1; $l++){
              $begin_temp += 86400;
              if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                $day_of_completion = date('j', $begin_temp);
                $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
              }
            }
          }
        }
        else if($repeat_type == 2){//repeats every x days
          if($end_date != 0){//repeat ends on date
            if($end_date > $timestamp_start_month){
              $begin_temp = $begin;
              $begin_temp += $repeats_every*86400;
              while($begin_temp < $end_date){   
                if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                  $day_of_completion = date('j', $begin_temp);
                  $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
                }
                $begin_temp += $repeats_every*86400;
              }
            }
          }
          else if($ends_after != 0){//repeat ends after x times
            $begin_temp = $begin;
            for($l = 0; $l < $ends_after-1; $l++){
              $begin_temp += $repeats_every*86400;
              if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                $day_of_completion = date('j', $begin_temp);
                $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
               }
            }
          }
        }
        else if($repeat_type == 3){//repeats every x weeks
          if($end_date != 0){//repeat ends on date
            if($end_date > $timestamp_start_month){
              $begin_temp = $begin;
              $begin_temp += $repeats_every*604800;
              while($begin_temp < $end_date){
                if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                  $day_of_completion = date('j', $begin_temp);
                  $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
                }
                $begin_temp += $repeats_every*604800;
              }
            }
          }
          else if($ends_after != 0){//repeat ends after x times
            $begin_temp = $begin;
            for($l = 0; $l < $ends_after-1; $l++){
              $begin_temp += $repeats_every*604800;
              if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                $day_of_completion = date('j', $begin_temp);
                $tasks_per_plan_per_day[$id][$day_of_completion] = $tasks_per_plan_per_day[$id][$day_of_completion]+1;
              }
            }
          }
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
  * Gets information about the plan that will be edited
  */

  private static function get_edit_plan_info($plans, $edit_plan_old_description){
    $edit_plan_id = $edit_plan_tasks_and_events = 0;
    $edit_plan_title = "";
    $edit_plan_description = $edit_plan_old_description;

    if(isset($_GET['edit_plan'])){
      $edit_plan_id = param_integer('edit_plan');
      $edit_plan_tasks_and_events = ArtefactTypeEvent::get_tasks_and_events($edit_plan_id,0,1000); //if plan needs to be edited, get form

      $plan_count = count($plans['data']);
      for($i = 0; $i < $plan_count; $i++){ //loop through all plans

        $id = $plans['data'][$i]->id; //get ids

         //get title and description of edited plan 
        if($id == $edit_plan_id){ //plan is edited plan
          $edit_plan_title = $plans['data'][$i]->title;
          if($edit_plan_old_description == "")
            $edit_plan_description = $plans['data'][$i]->description;
          else 
            $edit_plan_description = $edit_plan_old_description;
        }
      }
    }

    return array("edit_plan_id" => $edit_plan_id,
                 "edit_plan_tasks_and_events" => $edit_plan_tasks_and_events,
                 "edit_plan_title" => $edit_plan_title,
                 "edit_plan_description" => $edit_plan_description);
  }


  /**
  * Get information about task count
  */

  private static function get_task_count_info($plans){
    $plan_count = count($plans['data']);
    $task_count = array(); //array with number of tasks per plan
    $task_count_completed = array(); //array with number of completed tasks per plan

    for($i = 0; $i < $plan_count; $i++){ //loop through all plans

      $id = $plans['data'][$i]->id; //get ids
      $tasks = ArtefactTypeTask::get_tasks($id,0,1000);
      $task_count[$id] = $tasks['count']; 
      $task_count_completed[$id] = 0;

      for($j = 0; $j < $task_count[$id]; $j++){
         if($tasks['data'][$j]->completed == 1)
          $task_count_completed[$id]++;
      }
    }
    return array("task_count" => $task_count,
                 "task_count_completed" => $task_count_completed);
  }

  /**
  * Gets plan titles and shortens them
  **/

  private static function get_short_plan_titles($plans){
    $short_plan_titles = array(); //short titles for plans, if plan title is too long
    $plan_count = count($plans['data']);

    for($m = 0; $m < $plan_count; $m++){ //loop through all plans
      
      $id = $plans['data'][$m]->id;
      $plan_title = $plans['data'][$m]->title;
      if(strlen($plan_title) > 15){ //shortens title (long titles kill calendar view)
        mb_internal_encoding("UTF-8");
        $short_plan_titles[$id] = mb_substr($plan_title,0,13).'…';
      }
      else {
        $short_plan_titles[$id] = $plan_title;
      }
    }
    return $short_plan_titles;
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
        $colors[$plans['data'][$i]->id] = ArtefactTypeCalendar::save_random_color($id);
      }
    }
    return $colors;
  } 

  /**
  * If no color is picker for a new plan, random color is saved to db
  */
  private static function save_random_color($plan){
    $rand = rand(0,self::$color_num-1);
    $available_colors = self::$available_colors;
    ArtefactTypeCalendar::save_color($plan, $available_colors[$rand]); //choosen color is saved to db
    return $available_colors[$rand]; //random hex color
  }

  /**
  * Color is stored in db
  */

  private static function save_color($plan, $color){

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

  private static function save_status($plan, $status){

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
  * Saves reminder settings
  **/

  private static function save_reminder_settings($plans){
    $reminder_type = $_POST['reminder_setting'];
    ArtefactTypeCalendar::set_reminder_type($reminder_type);
    if($reminder_type == 0){ // no reminders
      ArtefactTypeCalendar::reset_reminder_dates($plans);
      ArtefactTypeCalendar::set_reminder_date('all', '-1');
    }
    else if($reminder_type == 1){ // reminder for all plans
      ArtefactTypeCalendar::set_reminder_date('all', $_POST['reminder']);
      ArtefactTypeCalendar::reset_reminder_dates($plans);
    }
    else if($reminder_type == 2){ // reminder for individual plans
      ArtefactTypeCalendar::set_reminder_date_individually($plans);
      ArtefactTypeCalendar::set_reminder_date('all', '-1');
    }
  }


   /**
  * Sets reminder date for all plans individually
  **/

  private static function set_reminder_date_individually($plans){
    $plan_count = count($plans['data']);
    for($i = 0; $i < $plan_count; $i++){
      $plan = $plans['data'][$i]->id;
      if(isset($_POST['reminder_date_plan_'.$plan]))
        ArtefactTypeCalendar::set_reminder_date($plan, $_POST['reminder_date_plan_'.$plan]);
    }
  }

  /**
  * Resets reminder dates
  **/

  private static function reset_reminder_dates($plans){
    $plan_count = count($plans['data']);
    for($i = 0; $i < $plan_count; $i++){
      $plan = $plans['data'][$i]->id;
      ArtefactTypeCalendar::set_reminder_date($plan, '-1');
    }
  }


  /**
  * Gets stored reminder date for each plan
  * Reminder date is set according to other plans, if new plan
  */

  private static function get_reminder_date_per_plan($plans){

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
    }
    return $reminder_date_per_plan;
  } 

  /**
  * Gets stored reminder date (if reminder setting is set to all plans)
  */

  private static function get_reminder_date_all(){
    
    global $USER;
    $id = $USER->id;

    ($result = get_records_sql_array("SELECT reminder_date
                  FROM {artefact_calendar_reminder} WHERE user = '$id';", array()))
              || ($result = array());
    if (!empty($result[0])) 
      return $result[0]->reminder_date;
    else return -1;
  } 
  
  /**
  * Sets reminder date either for one plan or all plans
  **/

  private static function set_reminder_date($plan ,$reminder_date){
     db_begin();
    
    if($plan == 'all'){
      global $USER;
      $id = $USER->id;

      ($result = get_records_sql_array("
              SELECT *
                  FROM {artefact_calendar_reminder} WHERE user = '$id';", array()))
              || ($result = array());
    
      $data = (object)array(
              'user'  => $id,
              'reminder_date' => $reminder_date
          );
    
    if (!empty($result[0])) 
      update_record('artefact_calendar_reminder', $data, 'user'); //update table
    else 
      insert_record('artefact_calendar_reminder', $data); //insert into table
    }
    else{
      ($result = get_records_sql_array("
              SELECT *
                  FROM {artefact_calendar_calendar} WHERE plan = '$plan';", array()))
              || ($result = array());

      $data = (object)array(
          'plan'  => $plan,
          'reminder_date' => $reminder_date,
          );
      if (!empty($result[0]))
        update_record('artefact_calendar_calendar', $data, 'plan'); //update table
      else
        insert_record('artefact_calendar_calendar', $data); //insert into table
     }
    db_commit();
  }


  /**
  * Returns array with reminder dates, and javascript array with reminder dates (string)
  **/

  private static function get_reminder_array(){
    $reminder_dates = array(); //array of reminder dates

    $reminder_dates["-1"] = get_string('never', 'artefact.calendar');
    $reminder_dates["0"] = get_string('same_day', 'artefact.calendar');
    $reminder_dates["1"] = "1 ".get_string('day_ahead', 'artefact.calendar');
    
    for($i = 2; $i < 7; $i++)
      $reminder_dates[$i] = $i." ".get_string('days_ahead', 'artefact.calendar');

    $reminder_dates[7] = "1 ".get_string('week_ahead', 'artefact.calendar');
    for($j = 2; $j < 12; $j++)
      $reminder_dates[7*$j] = $j." ".get_string('weeks_ahead', 'artefact.calendar');

    $reminder_dates[78] = "3 ".get_string('months_ahead', 'artefact.calendar');
    for($k = 4; $k <= 12; $k++)
      $reminder_dates[30*$k] = $k." ".get_string('months_ahead', 'artefact.calendar');
    
    return $reminder_dates;
  }

  /**
  * Returns the reminder type this user has set (1 = reminder for all plans, 2 = reminder for individual plans, 0 = no reminders)
  **/

  private static function get_reminder_type(){
    global $USER;
    $id = $USER->id;


      ($result = get_records_sql_array("
            SELECT reminder_type
                FROM {artefact_calendar_reminder} WHERE user = '$id';", array()))
            || ($result = array());

      if (!empty($result[0]))
        return $result[0]->reminder_type;
      else{
        ArtefactTypeCalendar::set_reminder_type(0); // set to "no reminders" by default
        return 0;
      }
  }

  /**
  * Sets the reminder type this user has chosen (1 = reminder for all plans, 2 = reminder for individual plans, 0 = no reminders)
  **/

  private static function set_reminder_type($reminder_type){
    global $USER;
    $id = $USER->id;

     db_begin();

    ($result = get_records_sql_array("
            SELECT reminder_type
                FROM {artefact_calendar_reminder} WHERE user = '$id';", array()))
            || ($result = array());
  
    $data = (object)array(
            'user'  => $id,
            'reminder_type' => $reminder_type
        );
    
    if (!empty($result[0])) 
      update_record('artefact_calendar_reminder', $data, 'user'); //update table
    else 
      insert_record('artefact_calendar_reminder', $data); //insert into table

    db_commit();
  }

  /**
  * Returns javascript array of plan ids (string)
  **/

  private static function get_plan_ids_js($plans){
    $plan_ids_js = 'new Array('; //javascript array of plan ids
    $plan_count = count($plans['data']);

    for($m = 0; $m < $plan_count; $m++){ //loop through all plans
      $id = $plans['data'][$m]->id;
      $plan_ids_js .= '"'.$id.'"';
      if($m < $plan_count - 1)
        $plan_ids_js .= ',';
    }
    $plan_ids_js .= ")";

    return $plan_ids_js;
  }

  /**
  *
  *
  *  FEED
  *
  *
  */

  /**
  *   Builds array of tasks for feed
  */
  public static function build_feed(&$plans, $user, $userkey) {

    if(!ArtefactTypeCalendar::check_userkey($user, $userkey))
      echo get_string('accessdenied', 'error');
    else{

      $feed_todos = array();
      $count = 0;
      $export_old = 0;
      $export_done = 0;

      if(isset($_GET['export_old']) && isset($_GET['export_months'])){
        $export_old = $_GET['export_old'];
        if($export_old == '0')
          $export_range_timestamp = ArtefactTypeCalendar::calculate_export_timestamp($_GET['export_months']);
        else
          $export_range_timestamp = 0;
      }
      if(isset($_GET['export_done']))
        $export_done = $_GET['export_done'];

      for($i = 0; $i < count($plans['data']); $i++){ //loop through all plans

        $id = $plans['data'][$i]->id; //get id
        $task[$i] = ArtefactTypeTask::get_tasks($id,0,10000); //get all tasks
        $task_count = $task[$i]['count'];

        for($j = 0; $j < $task_count; $j++){  

          $task_id = $task[$i]['data'][$j]->id;
          $summary = $task[$i]['data'][$j]->title; //task title
          $description = $task[$i]['data'][$j]->description; //task description
          $completed = $task[$i]['data'][$j]->completed; // check if task is completed        
          
          //the get_tasks functions gets the completiondate with month name written out, which leads to problems in other languages, therefore we use a different function to get the timestamp
          $due_task = new ArtefactTypeTask($task_id);
          $due_date_timestamp = (ArtefactTypeTask::get_taskform_elements($due_task->parent, $due_task));
          
          $due_date_timestamp = $due_date_timestamp['completiondate']['defaultvalue'];
            if(!($export_old == 0 && ($due_date_timestamp < $export_range_timestamp))){ //only export this task, if either the user wants to export old tasks or the task isn't too old
              if(!($completed == 1 && $export_done == 0)){//only export this task, if either the user wants to export tasks which are completed or the task isn't completed
            
              $dtstart = date('Ymd', $due_date_timestamp);// format for feed
              $due = date('Ymd', $due_date_timestamp).'T235959';// format for feed
              $dtend =date('Ymd', $due_date_timestamp);
              $uid = $task_id.date('Ymd', $due_date_timestamp);//unique identifier for each task
             
              $feed_todos[$count] = array('uid' => $uid,
                                          'summary' => $summary,
                                          'description' => $description,
                                          'completed' => $completed,
                                          'dtstart' => $dtstart,
                                          'dtend' => $dtend,
                                          'due' => $due);
              $count++;
            }
         }
        }
      } 
      if(isset($_GET['type'])){
        if($_GET['type'] == 'event')
          return ArtefactTypeCalendar::ical_feed_events($feed_todos);
        else 
          return ArtefactTypeCalendar::ical_feed($feed_todos);
      }
    }
  }

  /**
  * Transforms array of tasks to ical feed
  */ 

  private static function ical_feed($feed_todos){

    $wwwroot = get_config('wwwroot');
    $wwwroot = str_replace('https://', '', $wwwroot);
    $prodid = $wwwroot;

    $feed = "BEGIN:VCALENDAR\n"; 
    $feed .= "VERSION:2.0\n";
    $feed .= "PRODID:".$prodid."\n";

    $task_count = count($feed_todos);

    for($i = 0; $i < $task_count; $i++){  //each task is represented by a vtodo element
      $uid = $feed_todos[$i]['uid'];
      $summary = $feed_todos[$i]['summary'];
      $description = $feed_todos[$i]['description'];
      $completed = $feed_todos[$i]['completed'];
      $dtstart = $feed_todos[$i]['dtstart'];
      $due = $feed_todos[$i]['due'];

      $feed .= "BEGIN:VTODO\n";
      $feed .= 'UID:'.$uid.'@'.$wwwroot."\n";
      $feed .= 'SUMMARY:'.$summary."\n";
      if($description)
        $feed .= 'DESCRIPTION:'.$description."\n";
      if($completed == 1){
        $feed .= "STATUS:COMPLETED\n";
        $feed .= "PERCENT-COMPLETE:100\n";
        $feed .= "COMPLETED:19700101T235959Z\n";//iCal doesn't display tasks as completed without this field
      }
      $feed .= 'DUE:'.$due."\n";
      $feed .= "END:VTODO\n";
    }
    $feed .= "END:VCALENDAR\n";
    
    return $feed;
  }


  /**
  * Transforms array of tasks to ical feed of events
  */ 

  private static function ical_feed_events($feed_todos){

    $wwwroot = get_config('wwwroot');
    $wwwroot = str_replace('https://', '', $wwwroot);
    $prodid = $wwwroot;

    $feed = "BEGIN:VCALENDAR\n"; 
    $feed .= "VERSION:2.0\n";
    $feed .= "PRODID:".$prodid."\n";

    $task_count = count($feed_todos);

    for($i = 0; $i < $task_count; $i++){  //each task is represented by a vtodo element
      $uid = $feed_todos[$i]['uid'];
      $summary = $feed_todos[$i]['summary'];
      $description = $feed_todos[$i]['description'];
      $completed = $feed_todos[$i]['completed'];
      $dtstart = $feed_todos[$i]['dtstart'];
      $dtend = $feed_todos[$i]['dtend'];
      $due = $feed_todos[$i]['due'];

      $feed .= "BEGIN:VEVENT\n";
      $feed .= 'UID:'.$uid.'@'.$wwwroot."\n";
      $feed .= 'SUMMARY:'.$summary."\n";
      if($description)
        $feed .= 'DESCRIPTION:'.$description."\n";
      $feed .= 'DTSTART:'.$dtstart."\n";
      $feed .= 'DTEND:'.$dtend."\n";
      $feed .= "END:VEVENT\n";
    }
    $feed .= "END:VCALENDAR\n";

    return $feed;
  }

  /**
  * Returns the feed url for the specific user
  */

  private static function get_feed_url(){
    
    global $USER;
    $id = $USER->id;

    ($result = get_records_sql_array("
            SELECT userkey
                FROM {artefact_calendar_feed} WHERE user = '$id';", array()))
            || ($result = array());
    if(!empty($result[0]))
      return $result[0]->userkey;
    else{ //new feed url is generated
      return ArtefactTypeCalendar::generate_feed_url($id);
    }
  }

  /**
  * Generates feed url for given user and saves it to db
  */

  private static function generate_feed_url($user, $new = '1'){

    $userkey = '';
    //generate random string with length 30 to append to userkey

    $letters = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = '';
    for($i=0; $i < 35; $i++)
      $rand .= $letters[mt_rand(0, 29)];

    $userkey .= $rand; //append random string to userkey

    $data = (object)array(
            'user'  => $user,
            'userkey' => $userkey);
    if($new)
      insert_record('artefact_calendar_feed', $data); //insert into table
    else
      update_record('artefact_calendar_feed', $data, 'user'); //insert into table
    return $userkey;
  }

  /**
  * Checks if userkey and user match (and exist)
  */

  private static function check_userkey($user, $userkey){
    ($result = get_records_sql_array("SELECT * FROM {artefact_calendar_feed} WHERE user = '$user' AND userkey = '$userkey' LIMIT 1;", array())) || ($result = array());
    if(!empty($result[0]))
      return true;
    else 
      return false;
  }

  /**
  * See get_plans in Artefact Plans, returns plans for given user
  */

  public static function get_plans_of_user($user, $offset, $limit, $plan = 0){
    if($plan != 0) //only one specific plan
      ($plans = get_records_sql_array("SELECT * FROM {artefact} WHERE owner = '$user' AND artefacttype = 'plan' and id='$plan'", array())) || ($plans = array());
    else
     ($plans = get_records_sql_array("SELECT * FROM {artefact} WHERE owner = '$user' AND artefacttype = 'plan' ORDER BY id", array())) || ($plans = array());
    
    $result = array(
            'count'  => count_records('artefact', 'owner', $user, 'artefacttype', 'plan'),
            'data'   => $plans,
            'offset' => $offset,
            'limit'  => $limit,
        );
      return $result;
  }

  /**
  * If user doesn't want to export old tasks, this function calculates the timestamp that marks the according date (older tasks won't be exported) 
  */

  private static function calculate_export_timestamp($export_months){
    $timestamp_now = time();
    $timestamp_export_months = $export_months * 31 * 24 * 60 * 60; //number of months in milliseconds
    return $timestamp_now - $timestamp_export_months;
  }

}

/**
* ArtefactTypeEvent
*/

class ArtefactTypeEvent extends ArtefactType {
  

  public function render_self($options) {
    return get_string('event', 'artefact.calendar');
  }
  
  public static function get_icon($options=null) {
    
  }
  
  public static function is_singular() {
    return false;
  }
  
  public static function get_links($id) {
  }

  /**
  * Submits the event (see the submit function of plans plugin)
  */

  public static function submit_event($dates, $event_info){
   
    global $USER;

    if(isset($_GET['parent_id']))
      $parent = $_GET['parent_id'];
    if(isset($_GET['event']))
      $id = (int) $_GET['event'];
    else $id = 0;

    $begin_am_pm = $end_am_pm = '';

    $title = $_GET['title'];
    $description = $_GET['description'];
    $begin = $_GET['begin'];

    if(isset($_GET['begin_minute']))
      $begin_minute = $_GET['begin_minute'];
    if(isset($_GET['begin_hour']))
      $begin_hour = $_GET['begin_hour'];
    if(isset($_GET['begin_am_pm']))
      $begin_am_pm = $_GET['begin_am_pm'];
    if(isset($_GET['end_minute']))
      $end_minute = $_GET['end_minute'];
    if(isset($_GET['end_hour']))
      $end_hour = $_GET['end_hour'];
    if(isset($_GET['end_am_pm']))
      $end_am_pm = $_GET['end_am_pm'];
    if(isset($_GET['whole_day']))
    $whole_day = $_GET['whole_day'];
    $repeat_type = $_GET['repeat_type'];

    if($whole_day == 1){
      $begin_time = $begin;
      $end_time = $begin;
    }
    else{
      if(strlen($begin_hour) == 1) 
        $begin_hour = "0".$begin_hour;
       if(strlen($end_hour) == 1) 
        $end_hour = "0".$end_hour;
      $begin_time = $begin.' '.$begin_hour.':'.$begin_minute.' '.$begin_am_pm;
      $end_time = $begin.' '.$end_hour.':'.$end_minute.' '.$end_am_pm;
    }

    $begin_time = strtotime($begin_time);//timestamp of begin date
    $end_time = strtotime($end_time);//timestamp of end date

    $missing = "";
    if($title == ""){
        $missing .= "&missing_title=1";
    }
    if($begin == ""){
        $missing .= "&missing_date=1";
    }
    if($begin_time > $end_time){
        $missing .= '&wrong_date=1';
    }

    if($repeat_type != 0){//repetition is activated
      if($repeat_type == 2)
          $repeats_every = $_GET['repeat_every_days'];
      else if ($repeat_type == 3)
          $repeats_every = $_GET['repeat_every_weeks'];
      if(isset($_GET['repetition_end'])){
        if($_GET['repetition_end'] == "on"){ //repetition ends on date
          if(isset($_GET['end_date'])){
            $end_date = $_GET['end_date'];
            $end_date = strtotime($end_date);
          }
          else 
            $missing .= "&missing_repeat=1";
        }
        else if ($_GET['repetition_end'] == "after"){ //repetition ends after x times
          if(isset($_GET['ends_after']))
            $ends_after = $_GET['ends_after'];
          else 
            $missing .= "&missing_repeat=1";
        }
      }
      else 
        $missing .= "&missing_repeat=1";  
    }

    if($missing == ""){
      if ($id != 0) 
        $artefact = new ArtefactTypeEvent($id);
      else {
          $artefact = new ArtefactTypeEvent();
          $artefact->set('owner', $USER->get('id'));
          $artefact->set('parent', $parent);
      }

      $artefact->set('title', $title);
      $artefact->set('description', $description);

      $artefact->commit();

      ArtefactTypeEvent::submit_event_additional_info($artefact->get('id'), $begin_time, $end_time, $whole_day, $repeat_type, $repeats_every, $end_date, $ends_after);

      if ($event_info != 0) 
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&event_info='.$id);
      else 
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year']);
    }
    else{ //no title or date were specified
      if($id != 0)
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&edit_event_id='.$id.$missing.'&missing_field_title='.$title.'&missing_field_description='.$description.'&missing_field_begin='.$begin.'&parent_id='.$parent);
      else
        redirect('/artefact/calendar/index.php?month='.$dates['month'].'&year='.$dates['year'].'&specify_parent=1&new_event=1'.$missing.'&missing_field_title='.$title.'&missing_field_description='.$description.'&missing_field_begin='.$begin.'&parent_id='.$parent);
    }  
  }

  /**
  * Submits additional information about the event
  */
  private static function submit_event_additional_info($id, $begin_time, $end_time, $whole_day, $repeat_type, $repeats_every, $end_date, $ends_after){

    db_begin();

    // additional information about the event itself
    ($result = get_records_sql_array("
            SELECT *
                FROM {artefact_calendar_event} WHERE eventid = '$id';", array()))
            || ($result = array());
  
    $data = (object)array(
            'eventid'  => $id,
            'begin' => $begin_time,
            'end' => $end_time,
            'whole_day' => $whole_day,
            'repeat_type' => $repeat_type,
            'repeats_every' => $repeats_every,
            'end_date' => $end_date,
            'ends_after' => $ends_after
        );
    
    if (!empty($result[0])) 
      update_record('artefact_calendar_event', $data, 'eventid'); //update table
    else 
      insert_record('artefact_calendar_event', $data); //insert into table

    db_commit();
  }

   /**
  * Fills the event_per_day array which all tasks that happen on each day in the specific month
  */

  public static function build_event_per_day($dates, $plans){
    $event_per_day = array(); //array with all events of one day

    for($m = 1; $m <= $dates['num_days']; $m++) //two-dimensional array for every day of the month
      $event_per_day[$m] = array(); //array for each day
        
    for($i = 0; $i < count($plans['data']); $i++){ //loop through all plans

      $id = $plans['data'][$i]->id; //get id
      $event[$i] = ArtefactTypeEvent::get_events($id,0,1000); //get all events
      $event_count = $event[$i]['count'];

      for($j = 0; $j < $event_count; $j++){  

        $title = $event[$i]['data'][$j]->title; //event title
        $full_title = $title; //full title, other title will be shortened

        if(strlen($title) > 10){ //shortens title (long titles kill calendar view)
          mb_internal_encoding("UTF-8");
          $title = mb_substr($title,0,8).'…';
        }

        $event_id = $event[$i]['data'][$j]->id; //id of the event
        $parent_id = $event[$i]['data'][$j]->parent;  //id of events parent
        $description = $event[$i]['data'][$j]->description; //event description

        if($description == '') 
          $description = get_string('nodescription', 'artefact.calendar');

        $begin = $event[$i]['data'][$j]->begin; 
        $end = $event[$i]['data'][$j]->end; 
        $whole_day = $event[$i]['data'][$j]->whole_day; 
        $repeat_type = $event[$i]['data'][$j]->repeat_type; 
        $repeats_every = $event[$i]['data'][$j]->repeats_every; 
        $end_date = $event[$i]['data'][$j]->end_date;
        $ends_after = $event[$i]['data'][$j]->ends_after;
        
        $begin_hour = date('H', $begin);
        $begin_minute = date('i', $begin);
        $end_hour = date('H', $end);
        $end_minute = date('i', $end);

        $timestamp_start_month = strtotime(date(($dates['end_of_last_month']),time()));
        $timestamp_end_month = strtotime(date('1.'.$dates['next_month'].'.'.$dates['next_month_year'],time()));         

        if(($begin >  $timestamp_start_month) && ($begin < $timestamp_end_month)) { //check if event is in this month
          $day_of_completion = date('j', $begin);
          $num_events = count($event_per_day[$day_of_completion]); //calculates how many events happen on this day        
          $event_per_day[$day_of_completion][$num_events] = array('title' => $title, 
                                                                'event_id' => $event_id,
                                                                'parent_id' => $parent_id, 
                                                                'full_title' => $full_title, 
                                                                'description' => $description, 
                                                                'begin' => $begin,
                                                                'end' => $end,
                                                                'whole_day' => $whole_day,
                                                                'repeat_type' => $repeat_type,
                                                                'repeats_every' => $repeats_every,
                                                                'ends_after' => $ends_after,
                                                                'begin_hour' => $begin_hour,
                                                                'begin_minute' => $begin_minute,
                                                                'end_hour' => $end_hour,
                                                                'end_minute' => $end_minute);
       }
       //repeat is activated
      if($repeat_type == 1){//repeats daily
        if($end_date != 0){//repeat ends on date
          if($end_date > $timestamp_start_month){
            $begin_temp = $begin;
            $end_temp = $end;
            $begin_temp += 86400;
            $end_temp += 86400;
            while($begin_temp < $end_date){
              if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                $day_of_completion = date('j', $begin_temp);
                $num_events = count($event_per_day[$day_of_completion]); //calculates how many events happen on this day        
                $event_per_day[$day_of_completion][$num_events] = array('title' => $title, 'event_id' => $event_id, 'parent_id' => $parent_id, 'full_title' => $full_title, 'description' => $description, 'begin' => $begin_temp,'end' => $end_temp,'whole_day' => $whole_day,'repeat_type' => $repeat_type,'repeats_every' => $repeats_every,'ends_after' => $ends_after,'begin_hour' => $begin_hour,'begin_minute' => $begin_minute,'end_hour' => $end_hour,'end_minute' => $end_minute);
              }
              $begin_temp += 86400;
              $end_temp += 86400;
            }
          }
        }
        else if($ends_after != 0){//repeat ends after x times
          $begin_temp = $begin;
          $end_temp = $end;
          for($l = 0; $l < $ends_after-1; $l++){
            $begin_temp += 86400;
            $end_temp += 86400;
            if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
              $day_of_completion = date('j', $begin_temp);
              $num_events = count($event_per_day[$day_of_completion]); //calculates how many events happen on this day        
              $event_per_day[$day_of_completion][$num_events] = array('title' => $title, 'event_id' => $event_id, 'parent_id' => $parent_id, 'full_title' => $full_title, 'description' => $description, 'begin' => $begin_temp,'end' => $end_temp,'whole_day' => $whole_day,'repeat_type' => $repeat_type,'repeats_every' => $repeats_every,'ends_after' => $ends_after,'begin_hour' => $begin_hour,'begin_minute' => $begin_minute,'end_hour' => $end_hour,'end_minute' => $end_minute);
            }
          }
        }
      }
      else if($repeat_type == 2){//repeats every x days
        if($end_date != 0){//repeat ends on date
          if($end_date > $timestamp_start_month){
            $begin_temp = $begin;
            $end_temp = $end;
            $begin_temp += $repeats_every*86400;
            $end_temp += $repeats_every*86400;
            while($begin_temp < $end_date){   
              if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                $day_of_completion = date('j', $begin_temp);
                $num_events = count($event_per_day[$day_of_completion]); //calculates how many events happen on this day        
                $event_per_day[$day_of_completion][$num_events] = array('title' => $title, 'event_id' => $event_id, 'parent_id' => $parent_id, 'full_title' => $full_title, 'description' => $description, 'begin' => $begin_temp,'end' => $end_temp,'whole_day' => $whole_day,'repeat_type' => $repeat_type,'repeats_every' => $repeats_every,'ends_after' => $ends_after,'begin_hour' => $begin_hour,'begin_minute' => $begin_minute,'end_hour' => $end_hour,'end_minute' => $end_minute);
              }
              $begin_temp += $repeats_every*86400;
              $end_temp += $repeats_every*86400;
            }
          }
        }
        else if($ends_after != 0){//repeat ends after x times
          $begin_temp = $begin;
          $end_temp = $end;
          for($l = 0; $l < $ends_after-1; $l++){
            $begin_temp += $repeats_every*86400;
            $end_temp += $repeats_every*86400;
            if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
              $day_of_completion = date('j', $begin_temp);
              $num_events = count($event_per_day[$day_of_completion]); //calculates how many events happen on this day        
              $event_per_day[$day_of_completion][$num_events] = array('title' => $title, 'event_id' => $event_id, 'parent_id' => $parent_id, 'full_title' => $full_title, 'description' => $description, 'begin' => $begin_temp,'end' => $end_temp,'whole_day' => $whole_day,'repeat_type' => $repeat_type,'repeats_every' => $repeats_every,'ends_after' => $ends_after,'begin_hour' => $begin_hour,'begin_minute' => $begin_minute,'end_hour' => $end_hour,'end_minute' => $end_minute);
            }
          }
        }
       }
      else if($repeat_type == 3){//repeats every x weeks
        if($end_date != 0){//repeat ends on date
          if($end_date > $timestamp_start_month){
            $begin_temp = $begin;
            $end_temp = $end;
            $begin_temp += $repeats_every*604800;
            $end_temp += $repeats_every*604800;
            while($begin_temp < $end_date){
              if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
                $day_of_completion = date('j', $begin_temp);
                $num_events = count($event_per_day[$day_of_completion]); //calculates how many events happen on this day        
                $event_per_day[$day_of_completion][$num_events] = array('title' => $title, 'event_id' => $event_id, 'parent_id' => $parent_id, 'full_title' => $full_title, 'description' => $description, 'begin' => $begin_temp,'end' => $end_temp,'whole_day' => $whole_day,'repeat_type' => $repeat_type,'repeats_every' => $repeats_every,'ends_after' => $ends_after,'begin_hour' => $begin_hour,'begin_minute' => $begin_minute,'end_hour' => $end_hour,'end_minute' => $end_minute);
              }
              $begin_temp += $repeats_every*604800;
              $end_temp += $repeats_every*604800;
            }
          }
        }
        else if($ends_after != 0){//repeat ends after x times
          $begin_temp = $begin;
          $end_temp = $end;
          for($l = 0; $l < $ends_after-1; $l++){
            $begin_temp += $repeats_every*604800;
            $end_temp += $repeats_every*604800;
            if(($begin_temp >  $timestamp_start_month) && ($begin_temp < $timestamp_end_month)) { //check if event is in this month
              $day_of_completion = date('j', $begin_temp);
              $num_events = count($event_per_day[$day_of_completion]); //calculates how many events happen on this day        
              $event_per_day[$day_of_completion][$num_events] = array('title' => $title, 'event_id' => $event_id, 'parent_id' => $parent_id, 'full_title' => $full_title, 'description' => $description, 'begin' => $begin_temp,'end' => $end_temp,'whole_day' => $whole_day,'repeat_type' => $repeat_type,'repeats_every' => $repeats_every,'ends_after' => $ends_after,'begin_hour' => $begin_hour,'begin_minute' => $begin_minute,'end_hour' => $end_hour,'end_minute' => $end_minute);
            }
          }
        }
      }
    }
  }

    return $event_per_day;
  }

  /**
  * This function returns a list of the current plans events, see artefact plans
  *
  * @param limit how many events to display per page
  * @param offset current page to display
  * @return array (count: integer, data: array)
  */
  public static function get_events($plan, $offset=0, $limit=10) {

     ($results = get_records_sql_array("SELECT id, title, description, parent, begin, end, whole_day, repeat_type, repeats_every, end_date, ends_after FROM {artefact} a JOIN {artefact_calendar_event} ace ON a.id = ace.eventid WHERE artefacttype = 'event' AND parent = '$plan';")) || ($results = array());

    $result = array(
        'count'  => count_records('artefact', 'artefacttype', 'event', 'parent', $plan),
        'data'   => $results,
        'offset' => $offset,
        'limit'  => $limit,
        'id'     => $plan,
    );

    return $result;
  } 

  /**
  * This function returns a list of the current plans events and tasks, see artefact plans
  *
  * @param limit how many events/tasks to display per page
  * @param offset current page to display
  * @return array (count: integer, data: array)
  */

  public static function get_tasks_and_events($plan, $offset=0, $limit=10) {
        $datenow = time(); // time now to use for formatting tasks by completion

        ($results = get_records_sql_array("SELECT a.id, a.artefacttype, a.title, a.description, ".db_format_tsfield('completiondate').",at.completed, Null as end, Null as whole_day FROM artefact a JOIN artefact_plans_task at ON at.artefact = a.id WHERE a.artefacttype = 'task' AND a.parent = '$plan' 
                                          UNION
                                          SELECT a.id, a.artefacttype, a.title, a.description, ae.begin, Null, ae.end, ae.whole_day FROM artefact a JOIN artefact_calendar_event ae ON ae.eventid = a.id WHERE a.artefacttype='event' AND a.parent = '$plan' 
                                          ORDER BY completiondate ASC"))
            || ($results = array());

        $tasks_and_events = array();
        $display_format = get_string('display_format', 'artefact.calendar');

        if (!empty($results)) {
            foreach ($results as $result) {
              $date_timestamp = $result->completiondate;
              $date = date($display_format, $date_timestamp);
              $artefacttype = $result->artefacttype;
              $end = $result->end;

              $whole_day = $result->whole_day;
              $begin_hour = $begin_minute = $end_hour = $end_minute = 0;

              if($artefacttype == 'event'){
                if($whole_day != '1'){ //timestamps are converted to hours/minutes
                  $begin_hour = date('H', $date_timestamp);
                  $begin_minute = date('i', $date_timestamp);
                  $end_hour = date('H', $end);
                  $end_minute = date('i', $end);
                  $begin_hour_am_pm = date('h', $date_timestamp);
                  $end_hour_am_pm = date('h', $end);
                  $begin_am_pm = date('a', $date_timestamp); 
                  $end_am_pm = date('a', $end);
                }
              }

              array_push($tasks_and_events, array('id' => $result->id, 
                                                    'artefacttype' => $artefacttype,
                                                    'title' => $result->title,
                                                    'description' => $result->description,
                                                    'date' => $date,
                                                    'completed' => $result->completed,
                                                    'begin_hour' => $begin_hour,
                                                    'begin_hour_am_pm' => $begin_hour_am_pm,
                                                    'begin_minute' => $begin_minute,
                                                    'end_hour' => $end_hour,
                                                    'end_hour_am_pm' => $end_hour_am_pm,
                                                    'begin_am_pm' => $begin_am_pm,
                                                    'end_am_pm' => $end_am_pm,
                                                    'end_minute' => $end_minute,
                                                    'whole_day' => $whole_day
                ));
            }
        }    
        return $tasks_and_events;
    }

   public static function get_event_form($edit){
    ($results = get_records_sql_array("SELECT id, title, description, parent, begin, end, whole_day, repeat_type, repeats_every, end_date, ends_after FROM {artefact} a JOIN {artefact_calendar_event} ace ON a.id = ace.eventid WHERE id='$edit';")) || ($results = array());

    if(!empty($results[0])){

    $begin = $results[0]->begin;
    $end = $results[0]->end;
    $whole_day = $results[0]->whole_day;
    $display_format = get_string("display_format", 'artefact.calendar');
    $begin_date = date('Y/m/d', $begin);
    $begin_display = date($display_format, $begin);

    $begin_hour = $begin_minute = $end_hour = $end_minute = 0;
    if($whole_day == '0'){ //timestamps are converted to hours/minutes
      $begin_hour = date('H', $begin);
      $begin_minute = date('i', $begin);
      $end_hour = date('H', $end);
      $end_minute = date('i', $end);
      $begin_hour_am_pm = date('h', $begin);
      $end_hour_am_pm = date('h', $end);
      $begin_am_pm = date('a', $begin);
      $end_am_pm = date('a', $end);
    }

    $end_result =  $results[0]->end_date;
    $end_date_display = $end_date = "";
    if($end_result != 0){
      $end_date = date('Y/m/d', $end_result);
      $end_date_display = date($display_format, $end_result);
    }

    $form = array('event_id' => $results[0]->id,
                 'title' => $results[0]->title,
                 'description' => $results[0]->description,
                 'parent' => $results[0]->parent,
                 'begin' => $begin,
                 'begin_date' => $begin_date,
                 'begin_display' => $begin_display,
                 'begin_hour' => $begin_hour,
                 'begin_hour_am_pm' => $begin_hour_am_pm,
                 'begin_minute' => $begin_minute,
                 'end' => $end,
                 'end_hour' => $end_hour,
                 'end_hour_am_pm' => $end_hour_am_pm,
                 'end_minute' => $end_minute,
                 'whole_day' => $whole_day,
                 'repeat_type' => $results[0]->repeat_type,
                 'repeats_every' => $results[0]->repeats_every,
                 'end_date' => $end_date,
                 'end_date_display' => $end_date_display,
                 'ends_after' => $results[0]->ends_after, 
                 'begin_am_pm' => $begin_am_pm,
                 'end_am_pm' => $end_am_pm
                ); 
    return $form;
    }
  }
}


?>
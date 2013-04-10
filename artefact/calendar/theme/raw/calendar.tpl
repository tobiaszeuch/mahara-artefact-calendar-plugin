{assign var=cal value="artefact/calendar/"}
{assign var=img value="theme/raw/static/images/"}

{* date picker plugin *}
<script type="text/javascript" src="{$WWWROOT}{$cal}jquery-ui-1.9.0.custom/js/jquery-1.8.2.js"></script>
<script type="text/javascript" src="{$WWWROOT}{$cal}jquery-ui-1.9.0.custom/js/jquery-ui-1.9.0.custom.js"></script>
<script type="text/javascript" src="{$WWWROOT}{$cal}jquery-ui-1.9.0.custom/js/jquery-ui-1.9.0.custom.min.js"></script>
<link rel="stylesheet" href="{$WWWROOT}{$cal}jquery-ui-1.9.0.custom/css/ui-lightness/jquery-ui-1.9.0.custom.css" />
<link rel="stylesheet" href="{$WWWROOT}{$cal}jquery-ui-1.9.0.custom/css/ui-lightness/jquery-ui-1.9.0.custom.min.css" />
<link rel="stylesheet" href="{$WWWROOT}{$cal}jquery-ui-1.9.0.custom/css/smoothness/jquery-ui-1.9.0.custom.css" />
<link rel="stylesheet" href="{$WWWROOT}{$cal}jquery-ui-1.9.0.custom/css/smoothness/jquery-ui-1.9.0.custom.min.css" />

{* includes for overlay windows *}
{if $edit_plan_tasks != '0' && $new_task != '1'}
	{include file="edit_plan.tpl"} 
{elseif $new_task == '1'}
	{include file="edit_task.tpl"}
{/if}
{if $task_info != '0'}
	{include file="task_info.tpl"}
{elseif $form != '0'  && $new != 1 && $edit_plan_itself != 1 && $new_task != 1}
	{include file="edit_task.tpl"}
{/if}

{include file="new_plan.tpl"}
{if $new == 1}
	<script language="JavaScript">
		document.getElementById('planoverlay').style.display='block';
	</script>
{/if}

{* task list overlay each day with more than three tasks*}
{foreach from=$calendar item=week}
	{foreach from=$week item=day}
		{if $day != ""}
			<input id="number_tasks{$day}" type="hidden" value="{$number_of_tasks_per_day[$day]}"></input>
					{include file="task_list_day.tpl"}
		{/if}
	{/foreach}
{/foreach}

{* calendar *}
<tr class="bgday">
	<td style="width:75%;">
		<table>
			<tr >
				<th colspan='7'>
					<a class="flright" href="{$WWWROOT}{$cal}index.php?month={$this_month}&amp;year={$this_year}" title="{str section="artefact.calendar" tag='this_month'}">	{str section="artefact.calendar" tag='this_month'}		
					</a>
				</th>
			</tr>
			<tr >
				<th colspan='7' class="txtcenter">
					<h3>
						<a href="{$WWWROOT}{$cal}index.php?month={$past_month}&amp;year={$past_month_year}" title="{str section="artefact.calendar" tag='last_month'}" class="pdright20">
							<img src='{$WWWROOT}{$cal}theme/raw/static/images/arrow-left.gif' alt='back' /></a>
						{$month_name} {$year}
						<a href="{$WWWROOT}{$cal}index.php?month={$next_month}&amp;year={$next_month_year}" title="{str section="artefact.calendar" tag='next_month'}" class="pdleft20">
							<img src='{$WWWROOT}{$cal}theme/raw/static/images/arrow-right.gif' alt='next' /></a>
				</h3>
					
				</th>
			</tr>
			<tr>
				{if $week_start == 0}
					<th class="calendar_th">{str section="artefact.calendar" tag='sunday'}</th>
				{/if}
				<th class="calendar_th">{str section="artefact.calendar" tag='monday'}</th>
				<th class="calendar_th">{str section="artefact.calendar" tag='tuesday'}</th>
				<th class="calendar_th">{str section="artefact.calendar" tag='wednesday'}</th>
				<th class="calendar_th">{str section="artefact.calendar" tag='thursday'}</th>
				<th class="calendar_th">{str section="artefact.calendar" tag='friday'}</th>
				<th class="calendar_th">{str section="artefact.calendar" tag='saturday'}</th>
				{if $week_start == 1}
					<th class="calendar_th">{str section="artefact.calendar" tag='sunday'}</th>
				{/if}
			</tr>

			{foreach from=$calendar item=week}
				<tr class="bgday">
					
					{counter start=0 assign=week_count}
					{foreach from=$week item=day}						
						{if $day == $today}
							<td class="day bggrey bordergrey">
								<div class="day">
							<b>&ensp;{$day}</b>
						{elseif $day == ""}
							<td class="day bgwhite" style="border-bottom: 2px solid #f4f4f4;">
								<div class="day">
						{elseif (($week_count == 0 or $week_count == 6) and $week_start == 0) or (($week_count == 5 or $week_count == 6) and $week_start == 1)}
							<td class="day borderwhite bgweekend">
								<div class="day">
							&ensp;{$day}
						{else}
							<td class="day bordergrey bgday">
								<div class="day">
							&ensp;{$day}
						{/if}
						{if $calendar_weeks[$day] != "" && $day != ""}{*start of new calendar week*}
							<div class="calendar_week flright">	{$calendar_weeks[$day]}</div>
						{/if}
						{counter}{* counts the week days*}

						<br/>

						{if  $day != ""}
							
							
							{foreach from=$task_per_day[$day] item=task}
								{assign var=p_id value=$task['parent_id']}

								{* The name tag has to be in p tag and each child tag, so IE toggels the tasks correctly *}
								
								<a name="task{$task['parent_id']}" class="taskname" href='{$WWWROOT}{$cal}index.php?month={$month}&amp;year={$year}&amp;task_info={$task['task_id']}' title="{$task['full_title']}" style="background-color:#{$colors[$p_id]};">{$task['title']}
									{if $task['completed'] == '1'}
										<img name="task{$task['parent_id']}" class="sub" src='{$WWWROOT}theme/raw/static/images/success.gif' alt='done' />	
									{/if}</a>
									

							{/foreach}
						{/if}							
							
							<div id="link_number_tasks{$day}" class="description description_overlay
							{if $day == $today}
								bggrey
							{elseif (($week_count == 0 or $week_count == 6) and $week_start == 0) or (($week_count == 5 or $week_count == 6) and $week_start == 1)}
								bgweekend
							{else}
								bgday
							{/if}
							"
							{if $day == "" || $number_of_tasks_per_day[$day] < 4}
								style="display:none;"
							{/if}>
								<a class="cursor_pointer" onclick="document.getElementById('task_list_day{$day}').style.display='block';">
									<div id="display_number_calendar{$day}" style="display:inline;">{$number_of_tasks_per_day[$day]}</div> {str section="artefact.plans" tag='tasks'}
								</a>
							</div>
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>
	</td>
	<td>
		<div class="overflow" style="height:520px;">
			<table>
				{counter start=0 assign=plan_count}
				{foreach from=$plans.data item=plan}
				{assign var=id value=$plan->id}
				{counter}	
					<tr>
				    	<td class="plan">	

							{if $plans_status[$id] == '0'}
								{assign var=stat value='1'}										
							{else}
								{assign var=stat value='0'}
							{/if}

							<a id="onclick{$id}" onclick="toggle_ajax('link{$id}', 'color{$id}', 'task{$id}', '{$stat}', '{$id}', 'gray{$id}', {$number_of_tasks_per_plan_per_day[$id]});" class="deco_none" title="{$plan->title}">
							<div id='color{$id}' class="planbox" style='background-color:#{$colors[$id]};'>
							</div>					
							<div id="gray{$id}" class="planbox bggrey disp_none">
							</div>
								<h3 id='link{$id}' style="position:relative;">
									{$short_plan_titles[$id]}
								</h3>
								{if $plans_status[$id] == '0'}
								
									<script language="JavaScript">
											toggle('link{$id}', 'color{$id}', 'task{$id}', 'gray{$id}', {$number_of_tasks_per_plan_per_day[$id]});
									</script>
								{/if}					
							<div  class="description" style="margin:0px;">
								{$task_count[$id]}
								{if $task_count[$id] != 1}
									{str section="artefact.plans" tag='tasks'}
								{else}
									{str section="artefact.plans" tag='task'}
								{/if}	
								({$task_count_completed[$id]} {str section="artefact.plans" tag='completed'})			
									
							</div>
							</a>
					    </td>
					    <td class="plan_controls">
				        	<a class="cursor_default" href="{$WWWROOT}{$cal}index.php?month={$month}&amp;year={$year}&amp;edit_plan={$id}" >
								<img src='{$WWWROOT}{$cal}theme/raw/static/images/edit.gif' alt='edit'></a>

								<a id="reminder_enabled{$id}" onclick="toggle_reminder_ajax('{$id}',1);"
								{if $reminder_status_per_plan[$id] == 0}
									class="disp_none"
								{/if} title="{str section='artefact.calendar' tag='reminder_enabled_tooltip'}">
								<img src='{$WWWROOT}{$cal}theme/raw/static/images/clock_green.gif' alt='reminder'  ></a>
								
								<a id="reminder_disabled{$id}" onclick="toggle_reminder_ajax('{$id}',0);"
								{if $reminder_status_per_plan[$id] == 1}
									class="disp_none"
								{/if}  title="{str section='artefact.calendar' tag='reminder_disabled_tooltip'}">
								<img src='{$WWWROOT}{$cal}theme/raw/static/images/clock.gif' alt='reminder'></a>
								<input type="hidden" id="saved_color{$id}" value="#{$colors[$id]}"></input>
								<a onclick="toggle_color_picker('picker','{$id}', document.getElementById('saved_color{$id}').value);" ><img id="color_button{$id}" src="{$WWWROOT}{$cal}theme/raw/static/images/color_button.gif" style="background-color:#{$colors[$id]};" /></a>
				    	</td>
					</tr>		
				{/foreach}
			</table>
		</div>
		{include file="color_picker.tpl"}
		<p  class="description txtcenter">{$plan_count} 
			
		{if $plan_count != 1}
			{str section="artefact.plans" tag='plans'}
		{else}
			{str section="artefact.plans" tag='plan'}
		{/if}
		{if $plan_count != 0}	
				<a class="deco_none pdleft20" id="reminder_enabled_all" onclick="toggle_all_reminders({$plan_ids_js},0);" title="{str section='artefact.calendar' tag='reminder_enable_all_tooltip'}">{str section="artefact.calendar" tag='all'}  <img src='{$WWWROOT}{$cal}theme/raw/static/images/clock_green.gif' alt='reminder' title=''>
				</a> / 
				<a class="deco_none" id="reminder_disabled_all" onclick="toggle_all_reminders({$plan_ids_js},1);" title="{str section='artefact.calendar' tag='reminder_disable_all_tooltip'}">{str section="artefact.calendar" tag='all'}  <img src='{$WWWROOT}{$cal}theme/raw/static/images/clock.gif' alt="reminder"></a>
		{/if}
		</p>
		{if $plan_count != 0}
			<p>
				<a class="cursor_pointer" onclick='toggle_notification_settings();' id="reminder">{str section="artefact.calendar" tag='set_reminder'}: 
				{* Shows reminder date of first plan, display of date for each plan not yet implemented*}
				
					{assign var=time value=$reminder_date_per_plan[$plans.data[0]->id]}
					{foreach key=date_key item=date_string from=$reminder_dates}
						{if $time == $date_key}
							{$date_string}
						{/if}
					{/foreach}			
				</a>
			</p>
			<div id='set_notification' class="disp_none">
				{str section="artefact.calendar" tag='remind_me'}
				<select name="reminder" onchange="set_reminder_date_ajax(this.value,'all','{str section="artefact.calendar" tag='set_reminder'}: ',{$reminder_strings});">
					{foreach key=date_key item=date_string from=$reminder_dates}
						<option value='{$date_key}' 
							{if $time == $date_key} 
								selected 
							{/if}>{$date_string}
						</option>
					{/foreach}
				</select>	
				<p class="description">{str section="artefact.calendar" tag='disable_reminder1'}{str section="artefact.plans" tag='plan'}{str section="artefact.calendar" tag='disable_reminder2'}</p>
			</div>
			<p>
				<a class="cursor_pointer" onclick='toggle_feed_settings();toggle_feed_url("off");'>
					{str section="artefact.calendar" tag='feed'} <img class="sub" src="{$WWWROOT}{$cal}theme/raw/static/images/ical.gif" />	
				</a>
			</p>
			<div id='feed_settings' class="disp_none">
				<table>
					<tr>
						<td>
						<input type="checkbox" id="export_old" />
						</td>
						<td class="description">{str section="artefact.calendar" tag='export_old'}
							<select id="feed_months">
							{counter start=0 assign=month_count}
								{section name=months loop=6} 
								    {$smarty.section.months.iteration} 
								    {counter}
								    <option value="{$month_count}">
									    {if $month_count == 1}
									    	{$month_count} {str section="artefact.calendar" tag='month'}
									    {else}
									    	{$month_count} {str section="artefact.calendar" tag='month_plural'}
									    {/if}
									</option>
								{/section}
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" id="export_done" />
						</td>
						<td class="description"> 
							{str section="artefact.calendar" tag='export_done'}
						</td>
					</tr>
					<tr>
						<td>
							<input type="radio" name="export_type" id="export_task" value="task" checked/>
						</td>
						<td class="description">
							{str section="artefact.calendar" tag='feed_description'}
						</td>
					</tr>
					<tr>
						<td>
						   <input type="radio" name="export_type" id="export_event" value="event" />
						</td>
						<td class="description">
							{str section="artefact.calendar" tag='feed_description_event'}
						</td>
					</tr>
				</table>
				<button onclick='toggle_feed_url("off");toggle_feed_url("on");generate_feed_url();'>{str section="artefact.calendar" tag='generate'}</button>
				
				{if $newfeed == 1}
					<script language="JavaScript">
						toggle_feed_settings();
						toggle_feed_url("off");
					</script>
				{/if}
				<p class="description">{str section="artefact.calendar" tag='regenerate'} <br/><a href="{$WWWROOT}{$cal}index.php?regenerate=1">{str section="artefact.calendar" tag='regenerate_link'}</a></p>
			</div>
			<div id='feed_url' class="disp_none">			
				<textarea rows='5' id="feed" style="width:100%;">{$WWWROOT}{$cal}feed.php?uid={$uid}&amp;fid={$feed_url}</textarea>
				<input type="hidden" id="feed_url_base" value="{$WWWROOT}{$cal}feed.php?uid={$uid}&amp;fid={$feed_url}">
			</div>

			</div>
			{/if}
	</td>
</tr>


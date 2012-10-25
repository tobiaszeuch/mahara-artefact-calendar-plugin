<script type="text/javascript" src="{$WWWROOT}artefact/calendar/jquery-ui-1.9.0.custom/js/jquery-1.8.2.js"></script>
<script type="text/javascript" src="{$WWWROOT}artefact/calendar/jquery-ui-1.9.0.custom/js/jquery-ui-1.9.0.custom.js"></script>
<script type="text/javascript" src="{$WWWROOT}artefact/calendar/jquery-ui-1.9.0.custom/js/jquery-ui-1.9.0.custom.min.js"></script>
<link rel="stylesheet" href="{$WWWROOT}artefact/calendar/jquery-ui-1.9.0.custom/css/ui-lightness/jquery-ui-1.9.0.custom.css" />
<link rel="stylesheet" href="{$WWWROOT}artefact/calendar/jquery-ui-1.9.0.custom/css/ui-lightness/jquery-ui-1.9.0.custom.min.css" />

{if $edit_plan_tasks != '0' && $new_task != '1'}
	{include file="edit_plan.tpl"} 
{elseif $new_task == 1}
	{include file="edit_task.tpl"}
{/if}
{if $task_info != '0'}
	{include file="task_info.tpl"}
{elseif $form != '0'}
	{include file="edit_task.tpl"}
{/if}

<tr class="{cycle values='r0,r1'}">
	<td style="width:75%;">
		<a style="float:left;" href="{$WWWROOT}artefact/calendar/index.php?month={$past_month}&year={$past_month_year}">
	<img src='{$WWWROOT}artefact/calendar/theme/raw/static/images/arrow-left.gif' alt='back' /> 
	{str section="artefact.calendar" tag='last_month'}
</a>

<a style="padding-left:25%;padding-right:25%;" href="{$WWWROOT}artefact/calendar/index.php?month={$this_month}&year={$this_year}">			{str section="artefact.calendar" tag='this_month'}
	<img src='{$WWWROOT}artefact/calendar/theme/raw/static/images/arrow-up.gif' alt='this' />
</a>

<a style="float:right;" href="{$WWWROOT}artefact/calendar/index.php?month={$next_month}&year={$next_month_year}">
	{str section="artefact.calendar" tag='next_month'}
	<img src='{$WWWROOT}artefact/calendar/theme/raw/static/images/arrow-right.gif' alt='next' />
</a>
		<table>
			<tr >
				<th colspan='7' style="text-align:center;"><h3>{$month_name} {$year}</h3></td>
			</tr>
			<tr>
				{if $week_start == 0}
					<th style="width:14%;text-align:center;">{str section="artefact.calendar" tag='sunday'}</th>
				{/if}
				<th style="width:14%;text-align:center;">{str section="artefact.calendar" tag='monday'}</th>
				<th style="width:14%;text-align:center;">{str section="artefact.calendar" tag='tuesday'}</th>
				<th style="width:14%;text-align:center;">{str section="artefact.calendar" tag='wednesday'}</th>
				<th style="width:14%;text-align:center;">{str section="artefact.calendar" tag='thursday'}</th>
				<th style="width:14%;text-align:center;">{str section="artefact.calendar" tag='friday'}</th>
				<th style="width:14%;text-align:center;">{str section="artefact.calendar" tag='saturday'}</th>
				{if $week_start == 1}
					<th style="width:14%;text-align:center;">{str section="artefact.calendar" tag='sunday'}</th>
				{/if}
			</tr>

			{foreach from=$calendar item=week}
				<tr class="r0">
					{counter start=0 assign=week_count}
					{foreach from=$week item=day}						
						
						{if $day == $today}
							<td style="background-color:lightgray;padding: 2px;height:96px;border:1px solid white;">
							<b>&ensp;{$day}</b>
						{elseif $day == ""}
							<td style="background-color:white;height:96px;">
						{elseif (($week_count == 0 or $week_count == 6) and $week_start == 0) or (($week_count == 5 or $week_count == 6) and $week_start == 1)}
							<td style="padding: 2px;height:96px;background-color:#F3F7EC;border:1px solid white;">
							&ensp;{$day}
						{else}
							<td style="height:96px;padding: 2px;border:1px solid white;">
							&ensp;{$day}
						{/if}

						{counter}{* counts the week days*}

						<br/>

						{if  $day != ""}

							{foreach from=$task_per_day[$day] item=task}
								{assign var=p_id value=$task['parent_id']}

								{* The name tag has to be in p tag and each child tag, so IE toggels the tasks correctly *}
								
								<a name="task{$task['parent_id']}" class="taskname" href='{$WWWROOT}artefact/calendar/index.php?month={$month}&year={$year}&task_info={$task['task_id']}' style="background-color:#{$colors[$p_id]};padding-left:3px;margin: 2px;display:block;">{$task['title']}
									{if $task['completed'] == '1'}
										<img name="task{$task['parent_id']}" src='{$WWWROOT}theme/raw/static/images/success.gif' alt='done' />	
									{/if}</a>
									

							{/foreach}
						{/if}
							</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>
	</td>
	<td>
		<table>
			{foreach from=$plans.data item=plan}
			{assign var=id value=$plan->id}
				<tr>
				    <td>
				        	
							{if $plans_status[$id] == '0'}
								{assign var=stat value='1'}										
							{else}
								{assign var=stat value='0'}
							{/if}

							<a id="onclick{$id}" onclick="toggle_ajax('link{$id}', 'color{$id}', 'task{$id}', '{$stat}', '{$id}');" >
							<div id='color{$id}' style='position:absolute;width:10px;height:16pt;background-color:#{$colors[$id]};float:left;margin-right:3px;z-index:2;'>
							</div>
							<div style='position:absolute;width:10px;height:16pt;background-color:lightgray;float:left;margin-right:3px;z-index:1;'>
							</div> 
								<h3 id='link{$id}' style="position:relative;left:12px;">
									{$plan->title}
								</h3>
								{if $plans_status[$id] == '0'}
									<script language="JavaScript">
											toggle('link{$id}', 'color{$id}', 'task{$id}');
									</script>
								{/if}
							</a> 
						
				    </td>
				    <td style="min-width:40px;">
				        	<a href="{$WWWROOT}artefact/calendar/index.php?month={$month}&year={$year}&edit_plan={$id}" >
								<img src='{$WWWROOT}artefact/calendar/theme/raw/static/images/edit.gif' alt='edit'></a>
								{include file="color_picker.tpl"}
				    </td>
				</tr>
			{/foreach}
		</table>
	</td>
</tr>


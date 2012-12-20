<div id='task_list_day{$day}' class="disp_none">
	<div id='overlay'></div>
	<div id='overlay_window' class="overlay">
		<div class="overlay_control" style="min-width:0;">
		    <a onclick="document.getElementById('task_list_day{$day}').style.display='none';"> 
		    	<img src="{$WWWROOT}{$img}remove-block.png" alt="X"/>
		    </a>
		</div>
		<div id="overlay_content">
			<h3>{$full_dates[$day]}</h3>
			<div class="overflow" style="height:300px;">
				<hr>
				{foreach from=$task_per_day[$day] item=task}
					{assign var=p_id value=$task['parent_id']}
					<a class="taskname" name="task{$task['parent_id']}" href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&task_info={$task['task_id']}' title="{$task['full_title']}" style="background-color:#{$colors[$p_id]};">{$task['full_title']}
						{if $task['completed'] == '1'}
							<img name="task{$task['parent_id']}" src='{$WWWROOT}theme/raw/static/images/success.gif' alt='done' />	
						{/if}
					</a>
					<hr name="task{$task['parent_id']}">
				{/foreach}
				<div class="description description_overlay
					{if $day == $today}
						bggrey
					{elseif (($week_count == 0 or $week_count == 6) and $week_start == 0) or (($week_count == 5 or $week_count == 6) and $week_start == 1)}
						bgweekend
					{else}
						bgday
					{/if}
					">		
				</div>
			</div>
			<div class="description txtcenter" ><div id="display_number_overlay{$day}" style="display:inline;" >{$number_of_tasks_per_day[$day]}</div> {str section="artefact.plans" tag='tasks'}</div>
		</div>
	</div>
</div>
<div class="overlay_control" style="min-width:36px;">
    <a class="flright" href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}'>
		    	<button class="close deletebutton" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
    </a>	
</div>
<div id="overlay_content">
	<h3>{$edit_plan_title} 
		<a class="deco_none" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_plan={$edit_plan_id}&edit_plan_itself=1"><img src='{$WWWROOT}{$cal}{$img}edit.gif' alt='edit'></a>
		<a class="deco_none" onclick="document.getElementById('delete_plan').style.display='block';"><img src="{$WWWROOT}{$cal}{$img}delete.png" alt="X"/>
	    </a> 
    </h3>
	<div class="disp_none" id="delete_plan" style="position:relative;">
  	    <div class="red delete" style="left:0;">
        	{str section="artefact.calendar" tag='deleteconfirm'}
        	<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_plan_final={$edit_plan_id}&edit_plan={$edit_plan_id}">
        		{str section="artefact.calendar" tag='yes'}
        	</a>
        	<a class="flright" onclick="document.getElementById('delete_plan').style.display='none';">
        		{str section="artefact.calendar" tag='no'}
        	</a>
        </div>
	</div>
	<div style="padding-bottom:20px;">{$edit_plan_description}<br/>
		<a class="flright" style='text-decoration:none;' href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&new_event=1&parent_id={$edit_plan_id}'> 
			<button type="button"  class="submitcancel submit" onclick="window.location.href = '{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&new_task=1&parent_id={$edit_plan_id}';">{str section="artefact.calendar" tag='new_event'}</button>
		</a>
		<a class="flright" style='text-decoration:none;' href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&new_task=1&parent_id={$edit_plan_id}'> 
			<button type="button"  class="submitcancel submit" onclick="window.location.href = '{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&new_task=1&parent_id={$edit_plan_id}';">{str section="artefact.calendar" tag='newtask'}</button>
		</a>
	</div>
	<br/>
	<div class="overflow" style="height:300px;">
		{foreach from=$edit_plan_tasks_and_events item=task_event}
		<hr>

			{if $task_event['artefacttype'] == 'task'}
			    {if $task_event['completed'] != '1'}
			    	<p class="tasklist bgpink red">
			    {else}
			    	<p class="tasklist">
			    {/if}
			            <b>{$task_event['date']}</b>
			            {$task_event['title']}
			            {if $task_event['completed'] == '1'}
								<img src='{$WWWROOT}theme/raw/static/images/success.gif' alt='done' />	
						{/if}
			            <a class="flright" onclick="document.getElementById('delete{$task_event['id']}').style.display='block';">
				            	<img src="{$WWWROOT}{$cal}{$img}delete.png" alt="X"/>
				            </a> 
				            <a class="flright pdright2" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_task_id={$task_event['id']}&parent_id={$edit_plan_id}"><img src='{$WWWROOT}{$cal}{$img}edit.gif' alt='edit'></a>
			        	</p>
			        	<div class="flright disp_none" id="delete{$task_event['id']}" style="position:relative;">
			          	    <div  class="red delete">
				            	{str section="artefact.calendar" tag='deleteconfirm'}
				            	<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task_final={$task_event['id']}&edit_plan={$edit_plan_id}">
				            		{str section="artefact.calendar" tag='yes'}
				            	</a>
				            	<a class="flright" onclick="document.getElementById('delete{$task_event['id']}').style.display='none';">
				            		{str section="artefact.calendar" tag='no'}
				            	</a>
				            </div>
				   		</div>
			{elseif $task_event['artefacttype'] == 'event'}
				<p class="tasklist">
					<b>{$task_event['date']}</b>
					{$task_event['title']} 
					{if $task_event['whole_day'] == '1'}
		        		({str section="artefact.calendar" tag="whole_day"})
		        	{else}
			        	{if $am_pm == '0'}
			        		({$task_event['begin_hour']}:{$task_event['begin_minute']} - {$task_event['end_hour']}:{$task_event['end_minute']})
			        	{else}
			        		({$task_event['begin_hour_am_pm']}:{$task_event['begin_minute']} {$task_event['begin_am_pm']} - {$task_event['end_hour_am_pm']}:{$task_event['end_minute']} {$task_event['end_am_pm']})
			        	{/if}
				    {/if}
				    <a class="flright" onclick="document.getElementById('delete{$task_event['id']}').style.display='block';">
				            	<img src="{$WWWROOT}{$cal}{$img}delete.png" alt="X"/>
				            </a> 
				            <a class="flright pdright2" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_event_id={$task_event['id']}&parent_id={$edit_plan_id}"><img src='{$WWWROOT}{$cal}{$img}edit.gif' alt='edit'></a>
		        	</p>
		        	<div class="flright disp_none" id="delete{$task_event['id']}" style="position:relative;">
		          	    <div  class="red delete">
			            	{str section="artefact.calendar" tag='deleteconfirm'}
			            	<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_event_final={$task_event['id']}&edit_plan={$edit_plan_id}">
			            		{str section="artefact.calendar" tag='yes'}
			            	</a>
			            	<a class="flright" onclick="document.getElementById('delete{$task_event['id']}').style.display='none';">
			            		{str section="artefact.calendar" tag='no'}
			            	</a>
			            </div>
			   		</div>
				</p>
			{/if}
	{/foreach}
	</div>
	<p class="description txtcenter">{$task_count[$edit_plan_id]}
		{if $task_count[$edit_plan_id] != 1}
			{str section="artefact.plans" tag='tasks'}
		{else}
			{str section="artefact.plans" tag='task'}
		{/if}
		({$task_count_completed[$edit_plan_id]} {str section="artefact.plans" tag='completed'})
	</p>
</div>
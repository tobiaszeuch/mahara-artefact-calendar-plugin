<div class="overlay_control" style="min-width:0;">
	<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_plan={$edit_plan_id}&edit_plan_itself=1">
		<img src='{$WWWROOT}{$cal}{$img}edit.gif' alt='edit'></a>
    <a href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}'> 
    	<img src="{$WWWROOT}{$img}remove-block.png" alt="X"/>
    </a>
</div>
<div id="overlay_content">
	<h3>{$edit_plan_title} </h3>
	<div style="padding-bottom:20px;">{$edit_plan_description}<br/>
		<a class="flright" style='text-decoration:none;' href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&new_task=1&parent={$edit_plan_id}'> 
			<button type="button"  class="submitcancel submit" onclick="window.location.href = '{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&new_task=1&parent={$edit_plan_id}';">{str section="artefact.calendar" tag='newtask'}</button>
		</a>
	</div>
	<div class="overflow" style="height:300px;">
		{foreach from=$edit_plan_tasks.data item=task}
		<hr>
		    {if $task->completed == -1}
		    	<p class="tasklist bgpink red">
		    {else}
		    	<p class="tasklist">
		    {/if}
		            <b>{$task->completiondate}</b>
		            {$task->title}
		            <a class="flright" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task={$task->task}&edit_plan={$edit_plan_id}">
			            	<img src="{$WWWROOT}{$cal}{$img}delete.png" alt="X"/>
			            </a> 
			            <a class="flright pdright2" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit={$task->task}&parent={$edit_plan_id}"><img src='{$WWWROOT}{$cal}{$img}edit.gif' alt='edit'></a>
		        	</p>
		            {if $delete_task eq $task->task}					               
			        	<div class="flright" style="position:relative;">
			          	    <div  class="red delete">
				            	{str section="artefact.calendar" tag='deleteconfirm'}
				            	<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task_final={$task->task}&edit_plan={$edit_plan_id}">
				            		{str section="artefact.calendar" tag='yes'}
				            	</a>
				            	<a class="flright" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_plan={$edit_plan_id}">
				            		{str section="artefact.calendar" tag='no'}
				            	</a>
				            </div>
				   		</div>
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
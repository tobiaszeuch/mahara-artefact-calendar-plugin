<div class="overlay_control" style='position: absolute;right: 0;top: 0;'>
	<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_plan={$edit_plan_id}&edit_plan_itself=1">
		<img src='{$WWWROOT}{$cal}theme/raw/static/images/edit.gif' alt='edit'></a>
    <a href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}'> 
    	<img src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" alt="X"/>
    </a>
</div>
<div id="overlay_content">
	<h3>{$edit_plan_title} </h3>
	<div style="padding-bottom:20px;">{$edit_plan_description}<br/>
		<a style='float:right;text-decoration:none;' href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&new_task=1&parent={$edit_plan_id}'> 
			<button type="button"  class="submitcancel submit" onclick="window.location.href = '{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&new_task=1&parent={$edit_plan_id}';">{str section="artefact.calendar" tag='newtask'}</button>
		</a>
	</div>
	<div class="overflow" style="height:300px;">
		{foreach from=$edit_plan_tasks.data item=task}
		    {if $task->completed == -1}
		    	<hr>
		        <p style='padding:3px;margin:0px;background-color:pink;color:#CD2626;'>
		            <b>{$task->completiondate}</b>
		            {$task->title}
		            {if $delete_task eq $task->task}					           
			            <a style='color:#CD2626;float:right;' href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task_final={$task->task}&edit_plan={$edit_plan_id}">
			            	<img src="{$WWWROOT}{$cal}theme/raw/static/images/delete.png" class="deletebutton" alt="X"/>
			            	 
			            </a> 
			            <a style="float:right;padding-right:2px;" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit={$task->task}&parent={$edit_plan_id}"><img src='{$WWWROOT}{$cal}theme/raw/static/images/edit.gif' alt='edit'></a>
		        	</p>
		        	<div style="z-index:3;float:right;position:relative;">
		          	    <div style="display: block; width: 100px; position: absolute; border: 4px solid #EEE; background: white;text-align: left; margin-left: auto; margin-right: auto; padding: 1px 5px; color:#CD2626;right:10px;z-index:3;">
			            	{str section="artefact.calendar" tag='deleteconfirm'}
			            	<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task_final={$task->task}&edit_plan={$edit_plan_id}">
			            		{str section="artefact.calendar" tag='yes'}
			            	</a>
			            	<a style="float:right;" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_plan={$edit_plan_id}">{str section="artefact.calendar" tag='no'}</a>
			            </div>
			   		</div>
		            {else}
			            <a style='color:#CD2626;float:right' href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task={$task->task}&edit_plan={$edit_plan_id}">
			            	<img src="{$WWWROOT}{$cal}theme/raw/static/images/delete.png" class="deletebutton" alt="X"/>
			            </a> 
			            <a style="float:right;padding-right:2px;" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit={$task->task}&parent={$edit_plan_id}"><img src='{$WWWROOT}{$cal}theme/raw/static/images/edit.gif' alt='edit'></a>
		       		 </p>
		            {/if}			         
		    {else}
		    	<hr>
		        <p style='padding:3px;margin:0px;'>
		            <b>{$task->completiondate}</b>
		            {$task->title}
		            {if $task->completed == 1}
		             	<img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" />
		            {/if}
		            {if $delete_task eq $task->task}
			            <a style='color:#CD2626;float:right;' href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task_final={$task->task}&edit_plan={$edit_plan_id}">
							<img src="{$WWWROOT}{$cal}theme/raw/static/images/delete.png" class="deletebutton" alt="X"/>
			            </a> 
			            <a style="float:right;padding-right:2px;" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit={$task->task}&parent={$edit_plan_id}"><img src='{$WWWROOT}{$cal}theme/raw/static/images/edit.gif' alt='edit'></a>
			    </p>
       			<div style="z-index:3;float:right;position:relative;">
	          	    <div style="display: block; width: 100px; position: absolute; border: 4px solid #EEE; background: white;text-align: left; margin-left: auto; margin-right: auto; padding: 1px 5px; color:#CD2626;right:10px;z-index:3;">
		            	{str section="artefact.calendar" tag='deleteconfirm'}
		            	<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task_final={$task->task}&edit_plan={$edit_plan_id}">
			            		{str section="artefact.calendar" tag='yes'}
			            	</a>
		            	<a style="float:right;" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_plan={$edit_plan_id}">{str section="artefact.calendar" tag='no'}</a>
		            </div>
	   			</div>
		            {else}
			            <a style='color:#CD2626;float:right;' href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&delete_task={$task->task}&edit_plan={$edit_plan_id}">
			            	<img src="{$WWWROOT}{$cal}theme/raw/static/images/delete.png" class="deletebutton" alt="X"/>
			            </a> 
			            <a style="float:right;padding-right:2px;" href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit={$task->task}&parent={$edit_plan_id}"><img src='{$WWWROOT}{$cal}theme/raw/static/images/edit.gif' alt='edit'></a>
		        </p>
		            {/if}
	    	{/if}
	{/foreach}
	</div>
	<p  class="description" style="text-align:center;">{$task_count[$edit_plan_id]}
		{if $task_count[$edit_plan_id] != 1}
			{str section="artefact.plans" tag='tasks'}
		{else}
			{str section="artefact.plans" tag='task'}
		{/if}
		({$task_count_completed[$edit_plan_id]} {str section="artefact.plans" tag='completed'})
	</p>
</div>
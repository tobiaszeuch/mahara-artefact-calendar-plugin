<div id='aufgabenoverlay'>
		<div id='overlay' style='z-index:3;'></div>
			<div id='overlay_window' style="display: block; width: 500px; left: 100px; top: 50px; position: absolute; z-index: 3; border: 7px solid #EEE; background: white;text-align: left; margin-left: auto; margin-right: auto; padding: 10px;">
				{if $edit_plan_itself == '1'}
					{include file="edit_plan_form.tpl"}
				{else}
					{include file="show_task_list.tpl"}
				{/if}
	        </div>
	   </div>
	</div>
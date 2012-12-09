<div id='aufgabenoverlay'>
		<div id='overlay'></div>
			<div id='overlay_window' class="overlay">
				{if $edit_plan_itself == '1'}
					{include file="edit_plan_form.tpl"}
				{else}
					{include file="task_list_plan.tpl"}
				{/if}
	        </div>
	   </div>
	</div>
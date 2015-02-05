<div id='planoverlay'>
	<div id='overlay'></div>
	<div id='overlay_window' class="overlay">
		{if $edit_plan_itself == '1'}
			{include file="artefact:calendar:edit_plan_form.tpl"}
		{else}
			{include file="artefact:calendar:task_list_plan.tpl"}
		{/if}
    </div>
   </div>
</div>
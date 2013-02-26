<div class="overlay_control" style="min-width:0;">
    <a href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}'> 
    	<img src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" alt="X"/>
    </a>
</div>
<div id="overlay_content">
	<form name="editplan" method="get" action="" id="editplan"> 
		{if $missing_title == 1}
			<p class="errmsg">{str section="artefact.calendar" tag='missing_title'}</p>
		{/if}
		<p>
			<label for="editplan_title">{str section="artefact.calendar" tag='title'}</label>
			<span class="requiredmarker">*</span><br/>
			<input type="text" class="required text autofocus" id="editplan_title" name="plan_title" size="30" tabindex="1" value="{$edit_plan_title}">
		</p>
		<p>
		 	<label for="editplan_description">{str section="artefact.calendar" tag='description'}</label><br/>
		 	<textarea rows="5" cols="50" class="textarea" id="editplan_description" name="plan_description" tabindex="1">{$edit_plan_description}</textarea>
		</p>		
		<p>
			<input type="hidden" name="edit_plan" value="{$edit_plan_id}"/>
			<input type="hidden" name="month" value="{$month}"/>
			<input type="hidden" name="year" value="{$year}"/>
		</p>
		<p>
			<input type="submit" class="submitcancel submit" id="editplan_submit" name="submit" tabindex="1" value="{str section="artefact.plans" tag='saveplan'}">
		</p>
	</form> 
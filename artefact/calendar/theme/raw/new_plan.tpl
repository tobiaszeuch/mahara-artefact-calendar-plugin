<div id='planoverlay' class="disp_none">
	<div id='overlay'></div>
	<div id='overlay_window' class="overlay">
		<div class="overlay_control" style="min-width:0;">
		    <a href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}'> 
		    	<img src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" alt="X"/>
		    </a>
		</div>
		<div id="overlay_content">
				<form name="newplan" method="get" action="" id="newplan"> 
					{if $missing_title == 1}
						<p class="errmsg">{str section="artefact.calendar" tag='missing_title'}</p>
					{/if}
					<p>
						<label for="newplan_title">{str section="artefact.calendar" tag='title'}</label>
						<span class="requiredmarker">*</span><br/>
						<input type="text" class="required text autofocus" id="newplan_title" name="newplan_title" size="30" tabindex="1" value="{$edit_plan_title}"></input>
					</p>
					<p>
					 	<label for="newplan_description">{str section="artefact.calendar" tag='description'}</label><br/>
					 	<textarea rows="5" cols="50" class="textarea" id="newplan_description" name="newplan_description" tabindex="1">{$edit_plan_description}</textarea>
					</p>		
					
					<p>
						<label for="newplan_color">{str section="artefact.calendar" tag='color'}</label><br/>
						{*color count makes sure that there are max. three colors in one row*}
						{counter start=0 assign=color_count}
						{foreach from=$available_colors item=color}
								<a class="deco_none" onclick="choose_color_new_plan('{$color}');"><img id="{$color}" class="thumb" style="background-color:#{$color};display:inline-block;"> </img></a>
							{if $color_count % 10 == 9}	
								<br/>
							{/if}

							{counter}
						{/foreach}
						<input type="hidden" id="newplan_color" name="newplan_color" value=""></input>
					</p>
					<p>
					 	<label for="newplan_reminder">{str section="artefact.calendar" tag='reminder'}</label>
					 	<input type="checkbox" name="newplan_reminder" value="active" checked></input>
					</p>
					<p>
						<input type="hidden" name="month" value="{$month}"/>
						<input type="hidden" name="year" value="{$year}"/>
					</p>
					<p>
						<input type="submit" class="submitcancel submit" id="newplan_submit" name="submit" tabindex="1" value="{str section="artefact.calendar" tag='saveplan'}">
					</p>
			</form> 
		</div>
	</div>
</div>
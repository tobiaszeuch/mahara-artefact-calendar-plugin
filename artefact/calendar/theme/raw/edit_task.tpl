
<script>
    $(function() {
        $("#datepicker").datepicker({
        	showOn: "button",
            buttonImage: "{$WWWROOT}theme/raw/static/images/calendar.gif",
            buttonImageOnly: true,
            dateFormat: "{str section='artefact.calendar' tag='datepicker_format'}",
            altField: "#alternate",
            altFormat: "yy/mm/dd",
            numberOfMonths: 2,
            constrainInput: true,
            firstDay:  "{str section='artefact.calendar' tag='datepicker_firstday'}",
            dayNamesMin: 	[ "{str section='artefact.calendar' tag='sunday_short'}", 
            				"{str section='artefact.calendar' tag='monday_short'}", 
            				"{str section='artefact.calendar' tag='tuesday_short'}", 
            				"{str section='artefact.calendar' tag='wednesday_short'}", 
            				"{str section='artefact.calendar' tag='thursday_short'}", 
            				"{str section='artefact.calendar' tag='friday_short'}", 
            				"{str section='artefact.calendar' tag='saturday_short'}" ],
           monthNames: 		[ "{str section='artefact.calendar' tag='1'}", 
           					"{str section='artefact.calendar' tag='2'}", 
           					"{str section='artefact.calendar' tag='3'}", 
           					"{str section='artefact.calendar' tag='4'}", 
           					"{str section='artefact.calendar' tag='5'}", 
           					"{str section='artefact.calendar' tag='6'}", 
           					"{str section='artefact.calendar' tag='7'}", 
           					"{str section='artefact.calendar' tag='8'}", 
           					"{str section='artefact.calendar' tag='9'}", 
           					"{str section='artefact.calendar' tag='10'}", 
           					"{str section='artefact.calendar' tag='11'}", 
           					"{str section='artefact.calendar' tag='12'}" ]
        });
    });
</script>
<div id='aufgabenoverlay'>
		<div id='overlay'></div>
			<div id='overlay_window' class="overlay">
				<div class="overlay_control" style='min-width:0;'>
		            	<a href='{$WWWROOT}{$cal}index.php?month={$month}&year={$year}'> 
		            		<img src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" alt="X" onclick='hide_overlay();'/>
		            	</a>
		        </div>
		        <div id="overlay_content">
					<form name="edittask" method="get" action="" id="edittask"> 
						<p>
							<label for="edittask_title">{str section="artefact.calendar" tag='title'}</label>
							<span class="requiredmarker">*</span><br/>
							<input type="text" class="required text autofocus" id="edittask_title" name="title" size="30" tabindex="1" value="{$form['title']}">
						</p>
						<p>
						<label for="edittask_completiondate">{str section="artefact.calendar" tag='completiondate'}</label> 
						<span class="requiredmarker">*</span><br/>
						<input type="text" id="datepicker" value="{$form['completiondate_display']}">
						<input type="hidden" name="completiondate" id="alternate" value="{$form['completiondate']}">
						</p>

						<p class="description">{str section="artefact.calendar" tag='format'}</p> 
						<p>
						 	<label for="edittask_description">{str section="artefact.calendar" tag='description'}</label><br/>
						 	<textarea rows="5" cols="50" class="textarea" id="edittask_description" name="description" tabindex="1">{$form['description']}</textarea>
						</p>		
						<p>
							<label for="edittask_completed">{str section="artefact.calendar" tag='completed'}</label>
							<input type="checkbox" class="checkbox" id="edittask_completed" name="completed" tabindex="1"
							{if $form['completed'] == '1'}
								checked
							{/if}
							>
							<input type="hidden" name="task" value="{$edit_id}"/>
							<input type="hidden" name="parent" value="{$parent_id}"/>
							<input type="hidden" name="task_info" value="{$edit_id}"/>
							<input type="hidden" name="month" value="{$month}"/>
							<input type="hidden" name="year" value="{$year}"/>
						</p>
						<p>
							<input type="submit" class="submitcancel submit" id="edittask_submit" name="submit" tabindex="1" value="{str section="artefact.calendar" tag='savetask'}">
						</p>
					</form> 
				</div>
			</div>
		</div>	
	</div>
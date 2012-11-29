<div id='aufgabenoverlay'>
		<div id='overlay' style='z-index:3;'></div>
		<div id='overlay_window' style="display: block; width: 500px; left: 100px; top: 50px; position: absolute; z-index: 3; border: 7px solid #EEE; background: white;text-align: left; margin-left: auto; margin-right: auto; padding: 10px;">
	      	<div id="overlay_header">
	         	<h4 id='overlay_title'>{$form['title']}</h4>
		     		<div class="overlay_control" style='position: absolute;right: 0;top: 0;min-width:54px;'>
		     				<form action="" method="get" id="done_form">
		     					
		     					{if $form['completed'] == 0}
		     						{assign var=status value=1}
									<input type="image" id="done_sw" src='{$WWWROOT}{$cal}theme/raw/static/images/done_sw.gif' alt='done' />
								{else}
									{assign var=status value=0}
									<input type="image" id="done" src='{$WWWROOT}{$cal}theme/raw/static/images/done_gruen.gif' alt='done' />
								{/if}
				        	
				     			<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit={$task_info}">
									<img src='{$WWWROOT}{$cal}theme/raw/static/images/edit.gif' alt='edit'></a>			
				            	<img src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" alt="X" onclick='hide_overlay();' />

		            			<input type="hidden" name="task" value="{$task_info}" />
								<input type="hidden" name="title" value="{$form['title']}" />
					        	<input type="hidden" name="description" value="{$form['description']}" />
					        	<input type="hidden" name="completiondate" value="{$form['completiondate']}" />
					        	<input type="hidden" name="completed" value="{$status}" />
					        	<input type="hidden" name="month" value="{$month}" />
					        	<input type="hidden" name="year" value="{$year}" />
		            		</form>
		        	</div>
			        <div id="overlay_content">
			        	{if $form['description'] == ""}
			        		{str section="artefact.calendar" tag='nodescription'}
			        	{else}
			        		<p>{$form['description']}</p>
			        	{/if}
			        </div>		    	
	        </div>
	   </div>
	</div>
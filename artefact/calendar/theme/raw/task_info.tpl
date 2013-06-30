<div id="infooverlay">
		<div id="overlay"></div>
		<div id="overlay_window" class="overlay">
	      	<div id="overlay_header">
	         	<h4 id="overlay_title">{$form['title']}</h4>
		     		<div class="overlay_control">
		     				<form action="" method="get" id="done_form">
		     					
		     					{if $form["completed"] == 0}
		     						{assign var=status value=1}
									<input type="image" id="done_sw" src="{$WWWROOT}{$cal}theme/raw/static/images/done_sw.gif" alt="done" />
								{else}
									{assign var=status value=0}
									<input type="image" id="done" src="{$WWWROOT}{$cal}theme/raw/static/images/done_gruen.gif" alt="done" />
								{/if}
				        	
				     			<a href="{$WWWROOT}{$cal}index.php?month={$month}&year={$year}&edit_task_id={$task_info}">
									<img src="{$WWWROOT}{$cal}theme/raw/static/images/edit.gif" alt="edit"></a>			
				            	<img src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" alt="X" onclick="hide_overlay('infooverlay');" />

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
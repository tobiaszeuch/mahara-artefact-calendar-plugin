
<a style="z-index:1;" onclick="toggle_color_picker('picker{$id}');"><img src="{$WWWROOT}artefact/calendar/theme/raw/static/images/color_button.gif" style="background-color:#{$colors[$id]};" /></a>
			<div id='picker{$id}' style="display: none; position:absolute; z-index: 3; border: 3px solid #EEE; background: white; margin-left: auto; margin-right: auto; padding: 5px; right:20px;">
				<div class="overlay_control" style='position: absolute;right: 1px;top: 1px;'>
            		<img src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" style="width:12px;" alt="X" onclick="toggle_color_picker('picker{$id}');"/>
		        </div>
				<table style="margin-top:10px;">
					{*color count makes sure that there are max. three colors in one row*}
					{counter start=0 assign=color_count}
					{foreach from=$available_colors item=color}
						
						{if $color_count % 3 == 0}
							<tr>
						{/if}
								<td>
									<a onclick="save_color('{$id}','task{$id}','{$color}');" ><div class="thumb" style="background-color:#{$color};"> </div></a>
								</td>
						
						{if $color_count % 3 == 2}
							</tr>
						{/if}

						{counter}

					{/foreach}
				</table>	
	        </div>

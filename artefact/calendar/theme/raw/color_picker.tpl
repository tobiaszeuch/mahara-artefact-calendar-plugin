
<a style="z-index:1;" onclick="toggle_color_picker('picker{$id}');"><img src="{$WWWROOT}artefact/calendar/theme/raw/static/images/color_arrow.gif" /></a>
			<div id='picker{$id}' style="display: none; position:absolute; z-index: 3; border: 3px solid #EEE; background: white; margin-left: auto; margin-right: auto; padding: 5px; right:20px;">
				<div class="overlay_control" style='position: absolute;right: 1px;top: 1px;'>
            		<img src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" style="width:12px;" alt="X" onclick="toggle_color_picker('picker{$id}');"/>
		        </div>
				<table style="margin-top:10px;">
					<tr>
						<td><a onclick="save_color('{$id}','task{$id}','660000');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/660000.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','006600');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/006600.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','000066');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/000066.png" /></a></td>
					</tr>					
					<tr>
						<td><a onclick="save_color('{$id}','task{$id}','990000');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/990000.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','009900');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/009900.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','000099');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/000099.png" /></a></td>
					</tr>			
					<tr>
						<td><a onclick="save_color('{$id}','task{$id}','dd0000');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/dd0000.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','00dd00');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/00dd00.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','0000dd');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/0000dd.png" /></a></td>
					</tr>
					<tr>
						<td><a onclick="save_color('{$id}','task{$id}','666600');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/666600.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','660066');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/660066.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','006666');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/006666.png" /></a></td>
					</tr>
					<tr>
						<td><a onclick="save_color('{$id}','task{$id}','999900');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/999900.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','990099');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/990099.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','009999');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/009999.png" /></a></td>
					</tr>
					<tr>
						<td><a onclick="save_color('{$id}','task{$id}','dddd00');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/dddd00.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','dd00dd');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/dd00dd.png" /></a></td>
						<td><a onclick="save_color('{$id}','task{$id}','00dddd');"><img class='thumb' src="{$WWWROOT}artefact/calendar/theme/raw/static/images/colors/00dddd.png" /></a></td>
					</tr>
				</table>	
	        </div>

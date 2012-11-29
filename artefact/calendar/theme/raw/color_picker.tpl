
<div id='picker' style="display: none; position:absolute; z-index: 3; border: 3px solid #EEE; background: white; margin-left: auto; margin-right: auto; padding: 5px; right:-100px;top:40px;">
	<div class="overlay_control" style='position: absolute;right: 1px;top: 1px;'>
		<img id="close_color_picker" src="{$WWWROOT}theme/raw/static/images/remove-block.png" class="deletebutton" style="width:12px;" alt="X"/>
    </div>
    <input type="hidden" id="color_picker_id"></input>
    <input type="hidden" id="old_color"></input>
	<table style="margin-top:10px;">
		{*color count makes sure that there are max. three colors in one row*}
		{counter start=0 assign=color_count}
		{foreach from=$available_colors item=color}
			
			{if $color_count % 3 == 0}
				<tr>		
			{/if}	
				<td>
					<a onclick="save_color(getElementById('color_picker_id').value,'{$color}');" ><img   src="" id="#{$color}" class="thumb" style="background-color:#{$color};"> </img></a>
				</td>
			{if $color_count % 3 == 2}	
				</tr>
			{/if}

			{counter}

		{/foreach}
	</table>	
</div>

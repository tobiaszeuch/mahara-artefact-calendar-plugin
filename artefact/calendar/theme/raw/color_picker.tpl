
<div id='picker' class="color_picker">
	<div class="overlay_control mini" style="display:block;">
		<img id="close_color_picker" src="{$WWWROOT}theme/raw/static/images/btn_close.png" class="deletebutton" style="width:12px;" alt="X"/>
    </div>
    <input type="hidden" id="color_picker_id">
    <input type="hidden" id="old_color" value="">
	<table style="margin-top:10px;">
		{*color count makes sure that there are max. three colors in one row*}
		{counter start=0 assign=color_count}
		{foreach from=$available_colors item=color}
			
			{if $color_count % 3 == 0}
				<tr>		
			{/if}	
				<td>
					<a onclick="save_color(getElementById('color_picker_id').value,'{$color}');" ><img id="#{$color}" class="thumb" style="background-color:#{$color};"></a>
				</td>
			{if $color_count % 3 == 2}	
				</tr>
			{/if}

			{counter}

		{/foreach}
	</table>	
</div>

{include file="header.tpl"}
<div id="planswrap">
    <div class="rbuttons">
    	<a class="btn" href="{$WWWROOT}artefact/calendar/index.php?new_task=1&amp;specify_parent=1&amp;month={$month}&amp;year={$year}">{str section="artefact.plans" tag="newtask"}</a>
        <a class="btn" onclick="document.getElementById('planoverlay').style.display='block';">{str section="artefact.plans" tag="newplan"}</a>
    </div>
	<table id="planslist" class="fullwidth listing">
	    <tbody>
	        {$plans.tablerows|safe}
	    </tbody>
	</table>
</div>
{include file="footer.tpl"}

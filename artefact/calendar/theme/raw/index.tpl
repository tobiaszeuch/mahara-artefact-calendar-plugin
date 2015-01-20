{include file="header.tpl"}
{if $plans.count <= 0}
    {str section="artefact.calendar" tag="error_no_plan"}
    <a class="btn" onclick="document.getElementById('planoverlay').style.display='block';">{str section="artefact.plans" tag="newplan"}</a>
    {include file="artefact:calendar:new_plan.tpl"}
{else}
  <div id="planswrap">
    <div class="rbuttons">
    	<a class="btn" href="{$WWWROOT}artefact/calendar/index.php?new_task=1&amp;specify_parent=1&amp;month={$month}&amp;year={$year}">{str section="artefact.plans" tag="newtask"}</a>
    	<a class="btn" href="{$WWWROOT}artefact/calendar/index.php?new_event=1&amp;specify_parent=1&amp;month={$month}&amp;year={$year}">{str section="artefact.calendar" tag="new_event"}</a>
        <a class="btn" onclick="document.getElementById('planoverlay').style.display='block';">{str section="artefact.plans" tag="newplan"}</a>
    </div>
    {$plans.tablerows|safe}
  </div>
{/if}
{include file="footer.tpl"}

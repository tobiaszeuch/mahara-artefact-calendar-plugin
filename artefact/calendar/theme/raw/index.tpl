{include file="header.tpl"}
<div id="planswrap">
    <div class="rbuttons">
        <a class="btn" href="{$WWWROOT}artefact/plans/new.php">{str section="artefact.plans" tag="newplan"}</a>
    </div>
	<table id="planslist" class="fullwidth listing">
	    <tbody>
	        {$plans.tablerows|safe}
	    </tbody>
	</table>
</div>
{include file="footer.tpl"}

<script type="text/javascript" src="{$WWWROOT}{$cal}js/editevent.js"></script>
<div id="event_info_overlay">
    <div id="overlay"></div>
    <div id="overlay_window" class="overlay">
        <div id="overlay_header">
            <h4 id="overlay_title">{$form['title']}</h4>
            <div class="overlay_control">
                <button class="close" type="button" data-dismiss="modal" aria-label="Close" onclick='hide_overlay("event_info_overlay");'>
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div id="overlay_content">
                <p>
                    <label>{str section="artefact.calendar" tag="date"}</label>: {$form['begin_date']}
                </p>
                    {if $form['whole_day'] == 1}
                        <p>
                            ({str section="artefact.calendar" tag="whole_day"})
                        </p>
                    {else}
                        <p>
                            {$form['begin_hour']}:{$form['begin_minute']} {if get_string('am_pm', 'artefact.calendar') == '1'}{$form['begin_am_pm']}{/if}
                             - 
                            {$form['end_hour']}:{$form['end_minute']} {if get_string('am_pm', 'artefact.calendar') == '1'}{$form['end_am_pm']}{/if}
                        </p>
                    {/if}
                {if $form['repeat_type'] > 0}
                    {if $form['repeat_type'] == 1}
                        {str section="artefact.calendar" tag="repeat_daily"}
                    {else}
                        {str section="artefact.calendar" tag="repeat_every"}
                        {if $form['repeat_type'] == 2}
                            {$form['repeats_every']} {str section="artefact.calendar" tag="repeat_x_days"}
                        {else}
                            {$form['repeats_every']} {str section="artefact.calendar" tag="repeat_x_weeks"}
                        {/if}
                    {/if}
                {/if}
                {if ($form['ends_after'] > 0 || $form['end_date'] !== "")}
                    <p>
                        {str section="artefact.calendar" tag="end"}: 
                        <span>
                            {if $form['ends_after'] > 0}
                                {str section="artefact.calendar" tag="after"}{$form['ends_after']}
                            {else}
                                {str section="artefact.calendar" tag="on"}{$form['end_date']}
                            {/if}
                        </span>
                    </p>
                {/if}
                <hr/>
                {if $form['description'] == ""}
                    {str section="artefact.calendar" tag='nodescription'}
                {else}
                    <p>{$form['description']}</p>
                {/if}
            </div>
            <div class="element_control">
                <form action="{$WWWROOT}{$cal}index.php" method="get" id="edit_event_form" class="inlineform inline" >
                    <input type="hidden" name="year" value="{$year}"/>
                    <input type="hidden" name="month" value="{$month}"/>
                    <input type="hidden" name="edit_event_id" value="{$event_info}"/>
                    <input type="submit" class="submit" value="{str tag="edit"}"/>
                </form>
                <button type="button" name="delete_event" value="{str tag="delete"}" onclick="delete_event()">
                    {str tag="delete"}
                </button>
                <form action="{$WWWROOT}{$cal}index.php" method="get" id="confirm_delete_form" class="js-hidden inline">
                    <div class="warning">
                        {str section="artefact.calendar" tag="deleteconfirm"}
                        <input type="submit" class="submit" value="{str tag="yes"}"/>
                        <button type="button" onclick="jQuery('#confirm_delete_form').addClass('js-hidden')">{str tag="no"}</button>
                    </div>
                    <input type="hidden" name="year" value="{$year}"/>
                    <input type="hidden" name="month" value="{$month}"/>
                    <input type="hidden" name="delete_event_final" value="{$event_info}"/>
                </form>
            </div>
        </div>
    </div>
</div>
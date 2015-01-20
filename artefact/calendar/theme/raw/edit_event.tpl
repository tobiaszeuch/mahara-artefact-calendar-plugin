<script type="text/javascript" src="{$WWWROOT}{$cal}js/editevent.js"></script>
<script>
    $(function() {
        $(".datepicker").datepicker({
        	showOn: "button",
            buttonImage: "{$WWWROOT}theme/raw/static/images/btn_calendar.png",
            buttonImageOnly: true,
            dateFormat: "{str section='artefact.calendar' tag='datepicker_format'}",
            altField: "#alternate",
            altFormat: "yy/mm/dd",
            numberOfMonths: 2,
            constrainInput: true,
            firstDay:  "{str section='artefact.calendar' tag='datepicker_firstday'}",
            dayNamesMin: 	[ "{str section='artefact.calendar' tag='sunday_short'}", 
            				"{str section='artefact.calendar' tag='monday_short'}", 
            				"{str section='artefact.calendar' tag='tuesday_short'}", 
            				"{str section='artefact.calendar' tag='wednesday_short'}", 
            				"{str section='artefact.calendar' tag='thursday_short'}", 
            				"{str section='artefact.calendar' tag='friday_short'}", 
            				"{str section='artefact.calendar' tag='saturday_short'}" ],
           monthNames: 		[ "{str section='artefact.calendar' tag='1'}", 
           					"{str section='artefact.calendar' tag='2'}", 
           					"{str section='artefact.calendar' tag='3'}", 
           					"{str section='artefact.calendar' tag='4'}", 
           					"{str section='artefact.calendar' tag='5'}", 
           					"{str section='artefact.calendar' tag='6'}", 
           					"{str section='artefact.calendar' tag='7'}", 
           					"{str section='artefact.calendar' tag='8'}", 
           					"{str section='artefact.calendar' tag='9'}", 
           					"{str section='artefact.calendar' tag='10'}", 
           					"{str section='artefact.calendar' tag='11'}", 
           					"{str section='artefact.calendar' tag='12'}" ]
        });
    });
</script>
<div id='eventoverlay'>
    <div id='overlay'></div>
    <div id='overlay_window' class="overlay">
        <div class="overlay_control" style='min-width:0;'>
            <img src="{$WWWROOT}theme/raw/static/images/btn_close.png" class="deletebutton" alt="X" onclick='hide_overlay("eventoverlay");'/>
        </div>
        <div id="overlay_content">
            <form name="editevent" method="get" action="" id="editevent"> 						
                {if $missing_title == 1}
                    <p class="errmsg">{str section="artefact.calendar" tag='missing_title'}</p>
                {/if}
                {if $missing_date == 1}
                    <p class="errmsg">{str section="artefact.calendar" tag='missing_date'}</p>
                {/if}
                {if $wrong_date == 1}
                    <p class="errmsg">{str section="artefact.calendar" tag='wrong_date'}</p>
                {/if}
                {if $specify_parent == 1}
                    <p>
                        <label for="edittask_title">{str section="artefact.plans" tag='plan'}</label>
                        <span class="requiredmarker">*</span><br/>
                        <select class="required" id="edittask_parent" name="parent_id">
                            {foreach from=$plans.data item=plan}
                            {assign var=id value=$plan->id}
                                <option value='{$id}'>{$plan->title}</option>
                            {/foreach}
                        </select>
                    </p>
                {/if}
                <p>
                    <label for="editevent_title">{str section="artefact.calendar" tag='title'}</label>
                    <span class="requiredmarker">*</span><br/>
                    <input type="text" class="required text autofocus" id="editevent_title" name="title" size="30" tabindex="1" value="{$form['title']}">
                </p>
                <hr>
                <p>
                    <label for="editevent_whole_day"> {str section="artefact.calendar" tag='whole_day'}</label><br/>
                    <input type="checkbox" value=1 name="whole_day" id="editevent_whole_day"{if $form['whole_day'] == 1}checked="checked"{/if}>
                </p>
                <p>
                    <label for="editevent_begin">{str section="artefact.calendar" tag='begin'}</label>
                    <input type="text" class="required text datepicker" id="editevent_begin" name="begin" value="{$form['begin_date']}"/>
                    &nbsp;
                    <span class="hideonwholeday">
                        <input type="text" class="text" id="editevent_begin_hour" name="begin_hour" value="{$form['begin_hour']}" size=2/>
                        :
                        <input type="text" class="text" id="editevent_begin_minute" name="begin_minute" value="{$form['begin_minute']}" size=2/>
                        {if get_string('am_pm', 'artefact.calendar') == '1'}
                          <td>
                            <select name="begin_am_pm">
                              <option {if $form['begin_am_pm'] == 'am'}selected="selected"{/if}>am</option>
                              <option {if $form['begin_am_pm'] == 'pm'}selected="selected"{/if}>pm</option>
                            </select>
                          </td>
                        {/if}
                    </span>
                </p>
                <p class="description">{str section="artefact.calendar" tag='format'}</p>
                <p class="hideonwholeday">
                    <label for="editevent_end_hour">{str section="artefact.calendar" tag='end'}</label>
                    <input type="text" class="text" id="editevent_end_hour" name="end_hour" value="{$form['end_hour']}" size=2/>
                    :
                    <input type="text" class="text" id="editevent_end_minute" name="end_minute" value="{$form['end_minute']}" size=2/>
                    {if get_string('am_pm', 'artefact.calendar') == '1'}
                      <td>
                        <select name="end_am_pm">
                          <option {if $form['end_am_pm'] == 'am'}selected="selected"{/if}>am</option>
                          <option {if $form['end_am_pm'] == 'pm'}selected="selected"{/if}>pm</option>
                        </select>
                      </td>
                    {/if}
                </p>
                <hr>
                <p>
                    <label for="repetition_type">{str section="artefact.calendar" tag="repetition"}</label><br/>
                    <select name="repeat_type" id="repetition_type">
                        <option value=0
                            {if $form['repeat_type'] == 0}selected="selected"{/if}
                        >
                            {str section="artefact.calendar" tag="repeat_never"}
                        </option>
                        <option value=1
                            {if $form['repeat_type'] == 1}selected="selected"{/if}
                        >
                            {str section="artefact.calendar" tag="repeat_daily"}
                        </option>
                        <option value=2
                            {if $form['repeat_type'] == 2}selected="selected"{/if}
                        >
                            {str section="artefact.calendar" tag="repeat_every"} x {str section="artefact.calendar" tag="repeat_x_days"}
                        </option>
                        <option value=3
                            {if $form['repeat_type'] == 3}selected="selected"{/if}
                        >
                            {str section="artefact.calendar" tag="repeat_every"} x {str section="artefact.calendar" tag="repeat_x_weeks"}
                        </option>
                    </select>
                    <span class="showonlyonrepeatdays {if $form['repeat_type'] !== '2'}js-hidden{/if}">
                        <input type="text" name="repeat_every_days" {if $form['repeats_every'] > 0}value="{$form['repeats_every']}"{/if}><br/>
                    </span>
                    <span class="showonlyonrepeatweeks {if $form['repeat_type'] < '3'}js-hidden{/if}">
                        <input type="text" name="repeat_every_weeks" {if $form['repeats_every'] > 0}value="{$form['repeats_every']}"{/if}><br/>
                    </span>
                </p>
                <span class="showonlyonrepeat {if $form['repeat_type'] == 0}js-hidden{/if}">
                    <p>
                        <table>
                            <tr>
                                <td>
                                    <label for="repetition_end">{str section="artefact.calendar" tag="end"}
                                    <select name="repetition_end" id="repetition_end">
                                        <option value="never"></option>
                                        <option value="after"
                                            {if $form['ends_after'] > 0}selected="selected"{/if}
                                        >
                                            {str section="artefact.calendar" tag="after"}
                                        </option>
                                        <option value="on"
                                            {if $form['end_date'] !== ''}selected="selected"{/if}
                                        >
                                            {str section="artefact.calendar" tag="on"}
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <div class="showonlyonrepeatendson {if $form['end_date'] === ''}js-hidden{/if}">
                                        <input type="text" name="end_date" class="text datepicker" value={$form['end_date']}/>
                                    </div>
                                    <span class="showonlyonrepeatendsafter {if $form['ends_after'] === ''}js-hidden{/if}">
                                        <input type="text" name="ends_after" value="{$form['ends_after']}"/>{str section="artefact.calendar" tag="times"}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </p>
                    <p class="description showonlyonrepeatendson js-hidden">{str section="artefact.calendar" tag='format'}</p>
                </span>
                <hr>
                <p>
                    <label for="editevent_description">{str section="artefact.calendar" tag='description'}</label><br/>
                    <textarea rows="5" cols="50" class="textarea" id="editevent_description" name="description" tabindex="1">{$form['description']}</textarea>
                </p>		
                <p>
                    <input type="hidden" name="event" value="{$edit_event_id}"/>
                    {if $specify_parent == 0}
                        <input type="hidden" name="parent_id" value="{$parent_id}"/>
                    {/if}
                    <input type="hidden" name="event_info" value="{$edit_event_id}"/>
                    <input type="hidden" name="type" value="event"/>
                    <input type="hidden" name="month" value="{$month}"/>
                    <input type="hidden" name="year" value="{$year}"/>
                </p>
                <p>
                    <input type="submit" class="submitcancel submit" id="editevent_submit" name="submit" tabindex="1" value="{str section="artefact.calendar" tag='saveevent'}">
                </p>
            </form> 
        </div>
    </div>	
</div>
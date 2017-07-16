<script type="text/javascript" src="{$WWWROOT}{$cal}js/edittask.js"></script>
<div id="task_info_overlay">
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
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close" onclick='hide_overlay("task_info_overlay");'>
                        <span aria-hidden="true">×</span>
                    </button>
                    <input type="hidden" name="task" value="{$task_info}" />
                    <input type="hidden" name="type" value="task"/>
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


            <div class="element_control">
                <form action="{$WWWROOT}{$cal}index.php" method="get" id="edit_task_form" class="inlineform inline" >
                    <input type="hidden" name="year" value="{$year}"/>
                    <input type="hidden" name="month" value="{$month}"/>
                    <input type="hidden" name="edit_task_id" value="{$task_info}"/>
                    <input type="submit" class="submit" value="{str tag="edit"}"/>
                </form>
                <button type="button" name="delete_task" value="{str tag="delete"}" onclick="delete_task()">
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
                    <input type="hidden" name="delete_task_final" value="{$task_info}"/>
                </form>
            </div>


        </div>
   </div>
</div>
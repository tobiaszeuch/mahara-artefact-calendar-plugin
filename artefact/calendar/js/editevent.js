jQuery().ready(function(){
    jQuery("#editevent_whole_day").change(function(){
        if (jQuery("#editevent_whole_day:checked").length > 0) {
            jQuery(".hideonwholeday").hide();
        }
        else {
            jQuery(".hideonwholeday").show();
        }
    });
    jQuery("#repetition_type").change(function(){
        if(jQuery(this).val() > 0) {
            jQuery(".showonlyonrepeat").removeClass("js-hidden");
        }
        else {
            jQuery(".showonlyonrepeat").addClass("js-hidden")
        }
        if(jQuery(this).val() == 2) {
            jQuery(".showonlyonrepeatdays").removeClass("js-hidden");
        }
        else {
            jQuery(".showonlyonrepeatdays").addClass("js-hidden")
        }
        if(jQuery(this).val() == 3) {
            jQuery(".showonlyonrepeatweeks").removeClass("js-hidden");
        }
        else {
            jQuery(".showonlyonrepeatweeks").addClass("js-hidden")
        }
    });
    jQuery("#repetition_end").change(function(){
        if (jQuery(this).val() === 'on') {
            jQuery(".showonlyonrepeatendson").removeClass("js-hidden");
            jQuery(".showonlyonrepeatendsafter").addClass("js-hidden");
        }
        else if (jQuery(this).val() === 'after') {
            jQuery(".showonlyonrepeatendson").addClass("js-hidden");
            jQuery(".showonlyonrepeatendsafter").removeClass("js-hidden");
        }
        else {
            jQuery(".showonlyonrepeatendson").addClass("js-hidden");
            jQuery(".showonlyonrepeatendsafter").addClass("js-hidden");
        }
    });
});

function delete_event() {
    $( "#confirm_delete_form" ).removeClass("js-hidden");
}
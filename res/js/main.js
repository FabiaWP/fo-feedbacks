/**
 * Created by fabia on 01/02/16.
 */
jQuery(document).ready(function () {
    var saveButton = jQuery('.wpcf-pr-save-ajax');
    saveButton.on('click', validateUser);
    var select = jQuery('.wpt-form-select');
    select.on('change', validateUser);
});
// Usa wp_localize_script() !!!
function validateUser(event) {
    var element = jQuery(this);

    event.preventDefault();
    var selects = element.closest('tr').find('.wpt-form-select');
    var giver = selects[0];
    var receiver = selects[1];

    if(giver.value === receiver.value) {
        alert(fo_feedbacks_data.message);
        return false;
    }
    return true;
    //jQuery.ajax({
    //
    //    url: ajaxurl,
    //    data: {
    //        'action': 'fo_get_feedbacks',
    //    //},
    //    success: function (resp) {
    //        if (nome1 == nome0) {
    //            alert('Error');
    //
    //        }
    //        else {
    //
    //            //do something
    //        }
    //    }
    //});
}




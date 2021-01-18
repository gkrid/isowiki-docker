jQuery(function() {

    jQuery('#plugin__telleveryone_cancel').click('on', function (event) {
        if (!window.confirm(LANG.plugins.telleveryone.cancel_confirm)) {
            event.preventDefault();
        }
    });
    jQuery('.plugin__telleveryone_delete').click('on', function (event) {
        if (!window.confirm(LANG.plugins.telleveryone.delete_confirm)) {
            event.preventDefault();
        }
    });
});

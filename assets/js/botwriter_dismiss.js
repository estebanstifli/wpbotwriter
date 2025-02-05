jQuery(document).on('click', '.botwriter-dismiss-announcement', function() {
    var announcement_id = jQuery(this).data('announcement-id');

    var data = {
        action 'botwriter_dismiss_announcement',  
        security botwriterData.nonce,
        announcement_id announcement_id
    };
    console.log(data);

    jQuery.post(botwriterData.ajaxurl, data, function(response) {
        if ( response.success ) {
            location.reload();  
        }
    });
});
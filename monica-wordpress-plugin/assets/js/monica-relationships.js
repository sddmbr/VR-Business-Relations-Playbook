jQuery(document).ready(function($) {
    var searchInput = $('#monica_related_contact_search');
    var hiddenIdInput = $('#monica_related_contact_id');

    searchInput.autocomplete({
        source: function(request, response) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'monica_search_contacts',
                    term: request.term,
                    nonce: monica_ajax.nonce,
                    exclude_post_id: $('#monica_post_id').val()
                },
                success: function(data) {
                    if (data.success) {
                        response($.map(data.data, function(item) {
                            return {
                                label: item.title,
                                value: item.title,
                                id: item.monica_id
                            };
                        }));
                    }
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            hiddenIdInput.val(ui.item.id);
        }
    });

    // Reset hidden ID if search field is cleared
    searchInput.on('input', function() {
        if ($(this).val() === '') {
            hiddenIdInput.val('');
        }
    });
});

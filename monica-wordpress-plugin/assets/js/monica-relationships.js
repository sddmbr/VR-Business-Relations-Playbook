jQuery(document).ready(function($) {
    if ($('#monica_contact_search').length) {
        $('#monica_contact_search').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: monicaRelationships.ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'monica_search_contacts',
                        nonce: monicaRelationships.nonce,
                        term: request.term,
                        exclude: monicaRelationships.post_id
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#monica_related_contact_id').val(ui.item.id);
            }
        });
    }
});

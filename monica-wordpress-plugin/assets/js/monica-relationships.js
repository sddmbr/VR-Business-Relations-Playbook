jQuery(document).ready(function($) {
    var $searchField = $('#monica_related_contact_search');
    var $idField = $('#monica_related_contact_id');

    if ($searchField.length === 0 || $idField.length === 0) {
        return;
    }

    $searchField.autocomplete({
        source: function(request, response) {
            $.ajax({
                url: monicaRelationshipsVars.ajaxUrl,
                dataType: 'json',
                data: {
                    action: 'monica_search_contacts',
                    nonce: monicaRelationshipsVars.nonce,
                    term: request.term,
                    post_id: monicaRelationshipsVars.postId
                },
                success: function(data) {
                    if (data.success === false) {
                        response([]);
                        return;
                    }
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $searchField.val(ui.item.label);
            $idField.val(ui.item.id);
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                // Clear the ID if the user clears the search field or types something not selected
                $idField.val('');
            }
        }
    });
});

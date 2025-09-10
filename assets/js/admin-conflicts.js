jQuery(document).ready(function($){
    $('.wfa-resolve-btn').on('click', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var choice = $('input[name="choice_' + id + '"]:checked').val() || '';
        var manual = $('#manual_' + id).val() || '';
        var note = $('#note_' + id).val() || '';

        if (!choice && !manual) {
            alert('Choose A/B or enter manual value.');
            return;
        }

        $.post(wfa_admin.ajax_url, {
            action: 'wfa_resolve_conflict',
            nonce: wfa_admin.nonce,
            conflict_id: id,
            choice: choice,
            manual_value: manual,
            note: note
        }, function(resp){
            if (resp.success) {
                alert('Conflict resolved.');
                location.reload();
            } else {
                alert('Error: ' + (resp.data || 'unknown'));
            }
        });
    });
});

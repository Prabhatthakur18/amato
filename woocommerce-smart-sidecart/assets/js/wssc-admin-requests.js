jQuery(document).ready(function($) {
    // Edit Status Button
    $(document).on('click', '.edit-status-btn', function() {
        var requestId = $(this).data('id');
        var currentStatus = $(this).closest('tr').find('.status-badge').text().toLowerCase().trim();
        
        $('#edit-request-id').val(requestId);
        $('#status-select').val(currentStatus);
        $('#status-edit-modal').show();
    });

    // Cancel Edit
    $(document).on('click', '.cancel-edit', function() {
        $('#status-edit-modal').hide();
    });

    // Submit Status Update
    $('#status-edit-form').on('submit', function(e) {
        e.preventDefault();
        
        var requestId = $('#edit-request-id').val();
        var newStatus = $('#status-select').val();
        
        $.ajax({
            url: wsscAdminRequests.ajax_url,
            type: 'POST',
            data: {
                action: 'wssc_update_request_status',
                id: requestId,
                status: newStatus,
                _ajax_nonce: wsscAdminRequests.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to see changes
                } else {
                    alert('Failed to update status');
                }
            },
            error: function() {
                alert('Error updating status');
            }
        });
    });

    // Delete Request
    $(document).on('click', '.delete-request-btn', function() {
        if (!confirm('Are you sure you want to delete this request?')) {
            return;
        }
        
        var requestId = $(this).data('id');
        var row = $(this).closest('tr');
        
        $.ajax({
            url: wsscAdminRequests.ajax_url,
            type: 'POST',
            data: {
                action: 'wssc_delete_request',
                id: requestId,
                _ajax_nonce: wsscAdminRequests.nonce
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut(300, function() {
                        row.remove();
                    });
                } else {
                    alert('Failed to delete request');
                }
            },
            error: function() {
                alert('Error deleting request');
            }
        });
    });
});
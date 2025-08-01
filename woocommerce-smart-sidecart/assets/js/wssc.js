jQuery(document).ready(function($) {
    // Auto-hide toast notifications
    $('.wssc-admin-toast').each(function() {
        const toast = $(this);
        
        // Auto-hide after 4 seconds
        setTimeout(function() {
            toast.fadeOut(400, function() {
                toast.remove();
            });
        }, 4000);
        
        // Allow manual close on click
        toast.on('click', function() {
            toast.fadeOut(300, function() {
                toast.remove();
            });
        });
    });

    // File input enhancement
    $('.file-input').on('change', function() {
        const fileName = $(this)[0].files[0]?.name;
        const fileText = $(this).siblings('.file-label').find('.file-text');
        
        if (fileName) {
            fileText.text(fileName);
            $(this).siblings('.file-label').css({
                'background': '#e7f3ff',
                'border-color': '#0073aa',
                'color': '#0073aa'
            });
        } else {
            fileText.text('Choose CSV File');
            $(this).siblings('.file-label').css({
                'background': '#f6f7f7',
                'border-color': '#c3c4c7',
                'color': 'inherit'
            });
        }
    });

    // Form submission loading state
    $('.wssc-upload-form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
               .html('<span class="upload-icon">⏳</span> Uploading...');
        
        // Re-enable if form doesn't submit for some reason
        setTimeout(function() {
            submitBtn.prop('disabled', false).html(originalText);
        }, 10000);
    });

    // Edit Status Button
    $('.edit-status-btn').on('click', function() {
        var requestId = $(this).data('id');
        var currentStatus = $(this).closest('tr').find('.status-badge').text().toLowerCase().trim();
        
        $('#edit-request-id').val(requestId);
        $('#status-select').val(currentStatus);
        $('#status-edit-modal').show();
    });

    // Cancel Edit
    $('.cancel-edit').on('click', function() {
        $('#status-edit-modal').hide();
    });

    // Submit Status Update
    $('#status-edit-form').on('submit', function(e) {
        e.preventDefault();
        
        var requestId = $('#edit-request-id').val();
        var newStatus = $('#status-select').val();
        
        $.ajax({
            url: wsscAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'wssc_update_request_status',
                id: requestId,
                status: newStatus,
                _ajax_nonce: wsscAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update the status badge in the table
                    var row = $('tr[data-id="' + requestId + '"]');
                    var statusBadge = row.find('.status-badge');
                    statusBadge.removeClass('status-pending status-done')
                              .addClass('status-' + newStatus)
                              .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                    
                    $('#status-edit-modal').hide();
                    showAdminToast('✅ Status updated successfully!', 'success');
                } else {
                    showAdminToast('❌ Failed to update status', 'error');
                }
            },
            error: function() {
                showAdminToast('❌ Error updating status', 'error');
            }
        });
    });

    // Delete Request
    $('.delete-request-btn').on('click', function() {
        if (!confirm('Are you sure you want to delete this request?')) {
            return;
        }
        
        var requestId = $(this).data('id');
        var row = $(this).closest('tr');
        
        $.ajax({
            url: wsscAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'wssc_delete_request',
                id: requestId,
                _ajax_nonce: wsscAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut(300, function() {
                        row.remove();
                    });
                    showAdminToast('✅ Request deleted successfully!', 'success');
                } else {
                   showAdminToast('❌ Failed to delete request', 'error');
                }
            },
            error: function() {
                showAdminToast('❌ Error deleting request', 'error');
            }
        });
    });

    // Buy Bulk Modal Form
// Buy Bulk Modal Form
$('.wssc-bulk-btn').on('click', function(e) {
    e.preventDefault();
    
    var modalHTML = `
    <div class="wssc-modal" id="wssc-bulk-modal">
        <div class="wssc-box">
            <h3>Buy in Bulk</h3>
            <form id="wssc-bulk-form">
                <input type="hidden" name="product_ids" value="${$(this).data('product')}">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="quantity" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="button button-primary">Submit</button>
                    <button type="button" class="button bulk-cancel-btn">Close</button>
                </div>
            </form>
        </div>
    </div>`;
    
    $('body').append(modalHTML);
    
    $('#wssc-bulk-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serializeArray();
        formData.push({name: 'action', value: 'wssc_buy_bulk'});
        
        $.ajax({
            url: wsscAjax.url,
            type: "POST",
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#wssc-bulk-modal').remove();
                    showToast('✅ Request submitted successfully!', 'success');
                } else {
                    showToast('❌ Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showToast('❌ Error submitting request', 'error');
            }
        });
    });
    
    $('.bulk-cancel-btn').on('click', function() {
        $('#wssc-bulk-modal').remove();
    });
});

    // Admin Toast Function
    function showAdminToast(message, type) {
        var toast = $('<div class="wssc-admin-toast ' + type + '">' + message + '</div>');
        $('body').append(toast);
        
        setTimeout(function() {
            toast.fadeOut(400, function() {
                toast.remove();
            });
        }, 4000);
        
        toast.on('click', function() {
            toast.fadeOut(300, function() {
                toast.remove();
            });
        });
    }

    // Frontend Toast Function
    function showToast(message, type) {
        var toast = $('<div class="wssc-toast ' + type + '">' + message + '</div>');
        $('body').append(toast);
        
        setTimeout(function() {
            toast.fadeOut(400, function() {
                toast.remove();
            });
        }, 4000);
        
        toast.on('click', function() {
            toast.fadeOut(300, function() {
                toast.remove();
            });
        });
    }

    // Close modal when clicking outside
    $(document).on('click', '.wssc-modal', function(e) {
        if (e.target === this) {
            $(this).remove();
        }
    });
});
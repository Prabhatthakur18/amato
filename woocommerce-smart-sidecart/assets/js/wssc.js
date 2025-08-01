jQuery(document).ready(function($) {
    // Add to cart functionality
    $(document).on('click', '.wssc-add-btn', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var productId = button.data('product-id');
        var originalText = button.html();
        
        // Disable button and show loading
        button.prop('disabled', true).html('Adding...');
        
        $.ajax({
            url: wsscAjax.url,
            type: 'POST',
            data: {
                action: 'wssc_add_to_cart',
                product_id: productId,
                quantity: 1,
                nonce: wsscAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update cart count
                    $('.cart-contents-count').text(response.data.cart_count);
                    
                    // Update quantity badge
                    var card = button.closest('.wssc-product-card');
                    var badge = card.find('.wssc-qty-badge');
                    var currentQty = badge.length ? parseInt(badge.text()) : 0;
                    var newQty = currentQty + 1;
                    
                    if (badge.length) {
                        badge.text(newQty);
                    } else {
                        card.append('<span class="wssc-qty-badge">' + newQty + '</span>');
                    }
                    
                    // Show success message
                    showToast('✅ Product added to cart!', 'success');
                    
                    // Trigger cart update
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    showToast('❌ Failed to add product', 'error');
                }
            },
            error: function() {
                showToast('❌ Error adding product', 'error');
            },
            complete: function() {
                // Re-enable button
                button.prop('disabled', false).html(originalText);
            }
        });
    });

    // Buy bulk button functionality
    $(document).on('click', '.wssc-bulk-btn', function(e) {
        e.preventDefault();
        
        var productIds = $(this).data('product');
        
        // Create and show bulk form modal
        var modal = createBulkModal(productIds);
        $('body').append(modal);
        $('#wssc-bulk-modal').show();
    });

    // Bulk form submission
    $(document).on('submit', '#wssc-bulk-form', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'wssc_buy_bulk',
            product_id: $('#bulk-product-id').val(),
            name: $('#bulk-name').val(),
            phone: $('#bulk-phone').val(),
            email: $('#bulk-email').val(),
            quantity: $('#bulk-quantity').val(),
            message: $('#bulk-message').val(),
            nonce: wsscAjax.nonce
        };
        
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('Submitting...');
        
        $.ajax({
            url: wsscAjax.url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showToast('✅ Bulk request submitted successfully!', 'success');
                    $('#wssc-bulk-modal').remove();
                } else {
                    showToast('❌ Failed to submit request', 'error');
                }
            },
            error: function() {
                showToast('❌ Error submitting request', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Close modal functionality
    $(document).on('click', '.wssc-modal', function(e) {
        if (e.target === this) {
            $(this).remove();
        }
    });
    
    $(document).on('click', '.wssc-close-modal', function() {
        $(this).closest('.wssc-modal').remove();
    });

    // Create bulk modal function
    function createBulkModal(productIds) {
        return `
            <div id="wssc-bulk-modal" class="wssc-modal" style="display: none;">
                <div class="wssc-box">
                    <h3>Bulk Purchase Request</h3>
                    <form id="wssc-bulk-form">
                        <input type="hidden" id="bulk-product-id" value="${productIds}">
                        
                        <label for="bulk-name">Name *</label>
                        <input type="text" id="bulk-name" name="name" required>
                        
                        <label for="bulk-phone">Phone *</label>
                        <input type="tel" id="bulk-phone" name="phone" required>
                        
                        <label for="bulk-email">Email</label>
                        <input type="email" id="bulk-email" name="email">
                        
                        <label for="bulk-quantity">Quantity *</label>
                        <input type="number" id="bulk-quantity" name="quantity" min="1" value="1" required>
                        
                        <label for="bulk-message">Message</label>
                        <textarea id="bulk-message" name="message" rows="3" placeholder="Any special requirements..."></textarea>
                        
                        <div style="margin-top: 15px;">
                            <button type="submit" class="button">Submit Request</button>
                            <button type="button" class="button wssc-close-modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    }

    // Toast notification function
    function showToast(message, type) {
        var toast = $('<div class="wssc-toast ' + (type || '') + '">' + message + '</div>');
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

    // Auto-hide existing toast notifications
    $('.wssc-admin-toast, .wssc-toast').each(function() {
        const toast = $(this);
        
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
    });
});
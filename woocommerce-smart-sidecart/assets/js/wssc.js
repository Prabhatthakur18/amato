jQuery(document).ready(function($) {
    
    // Handle add to cart from side cart recommendations
    $(document).on('click', '.wssc-add-btn', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var productId = button.data('product-id');
        var originalText = button.html();
        
        // Disable button and show loading
        button.prop('disabled', true).html('Adding...');
        
        // Get mobile brand and model from the page if available
        var mobileBrand = $('#mobile_brand').val() || '';
        var mobileModel = $('#mobile_model').val() || '';
        
        var ajaxData = {
            action: 'wssc_add_to_cart',
            product_id: productId,
            quantity: 1,
            nonce: wsscAjax.nonce,
            mobile_brand: mobileBrand,
            mobile_model: mobileModel
        };
        
        $.ajax({
            url: wsscAjax.url,
            type: 'POST',
            data: ajaxData,
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
                    
                    // Update cart fragments
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

    // Handle bulk button click
    $(document).on('click', '.wssc-bulk-btn', function(e) {
        e.preventDefault();
        var productId = $(this).data('product');
        $('#bulk-product-id').val(productId);
        $('#wssc-bulk-modal').addClass('wssc-modal-show');
    });
    
    // Handle cancel button click
    $(document).on('click', '#wssc-cancel-bulk', function(e) {
        e.preventDefault();
        closeModal();
    });
    
    // Close modal when clicking outside
    $(document).on('click', '#wssc-bulk-modal', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#wssc-bulk-modal').hasClass('wssc-modal-show')) {
            closeModal();
        }
    });
    
    // Handle bulk form submission
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
        
        // Disable submit button during request
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Submitting...');
        
        $.ajax({
            url: wsscAjax.url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showToast('✅ ' + response.data.message, 'success');
                    closeModal();
                } else {
                    showToast('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                showToast('❌ Error submitting request', 'error');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Function to close modal
    function closeModal() {
        $('#wssc-bulk-modal').removeClass('wssc-modal-show');
        $('#wssc-bulk-form')[0].reset();
    }
    
    // Toast function
    function showToast(message, type) {
        var toast = $('<div class="wssc-toast ' + type + '">' + message + '</div>');
        $('body').append(toast);
        
        setTimeout(function() {
            toast.fadeOut(400, function() {
                toast.remove();
            });
        }, 4000);
    }
});
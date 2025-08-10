jQuery(document).ready(function($) {
    console.log('WSSC JavaScript loaded');
    
    // Handle add to cart from side cart recommendations
    $(document).on('click', '.wssc-add-btn', function(e) {
        e.preventDefault();
        console.log('Add button clicked');
        
        var button = $(this);
        var productId = button.data('product-id');
        var originalText = button.html();
        
        console.log('Product ID:', productId);
        
        // Disable button and show loading
        button.prop('disabled', true).html('Adding...');
        
        // Get mobile brand and model from the page if available
        var mobileBrand = $('#mobile_brand').val() || '';
        var mobileModel = $('#mobile_model').val() || '';
        
        console.log('Mobile Brand:', mobileBrand, 'Mobile Model:', mobileModel);
        
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
                console.log('Add to cart response:', response);
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
            error: function(xhr, status, error) {
                console.log('Add to cart error:', error);
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
        console.log('Bulk button clicked');
        
        var productId = $(this).data('product');
        console.log('Setting product ID:', productId);
        
        $('#bulk-product-id').val(productId);
        
        // Show modal using multiple methods to ensure it works
        var modal = $('#wssc-bulk-modal');
        modal.show();
        modal.css('display', 'flex');
        modal.addClass('wssc-modal-show');
        
        console.log('Modal should be visible now');
    });
    
    // Handle cancel button click - Multiple selectors to ensure it works
    $(document).on('click', '#wssc-cancel-bulk, .wssc-cancel-bulk, .cancel-bulk', function(e) {
        e.preventDefault();
        console.log('Cancel button clicked');
        closeModal();
    });
    
    // Close modal when clicking outside
    $(document).on('click', '#wssc-bulk-modal', function(e) {
        if (e.target === this) {
            console.log('Clicked outside modal');
            closeModal();
        }
    });
    
    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#wssc-bulk-modal').is(':visible')) {
            console.log('Escape key pressed');
            closeModal();
        }
    });
    
    // Handle bulk form submission
    $(document).on('submit', '#wssc-bulk-form', function(e) {
        e.preventDefault();
        console.log('Bulk form submitted');
        
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
        
        console.log('Form data:', formData);
        
        // Disable submit button during request
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Submitting...');
        
        $.ajax({
            url: wsscAjax.url,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Bulk form response:', response);
                if (response.success) {
                    showToast('✅ ' + response.data.message, 'success');
                    closeModal();
                } else {
                    showToast('❌ ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('Bulk form error:', error);
                showToast('❌ Error submitting request', 'error');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Function to close modal - Multiple methods to ensure it works
    function closeModal() {
        console.log('Closing modal');
        
        var modal = $('#wssc-bulk-modal');
        
        // Try multiple methods to hide the modal
        modal.hide();
        modal.css('display', 'none');
        modal.removeClass('wssc-modal-show');
        
        // Reset form
        $('#wssc-bulk-form')[0].reset();
        
        console.log('Modal closed and form reset');
    }
    
    // Toast function
    function showToast(message, type) {
        console.log('Showing toast:', message, type);
        
        // Remove existing toasts
        $('.wssc-toast').remove();
        
        var toast = $('<div class="wssc-toast ' + type + '">' + message + '</div>');
        $('body').append(toast);
        
        setTimeout(function() {
            toast.fadeOut(400, function() {
                toast.remove();
            });
        }, 4000);
    }
    
    // Debug: Log when modal exists
    if ($('#wssc-bulk-modal').length) {
        console.log('Modal found in DOM');
    } else {
        console.log('Modal NOT found in DOM');
    }
    
    // Debug: Log mobile selectors
    if ($('#mobile_brand').length && $('#mobile_model').length) {
        console.log('Mobile selectors found');
    } else {
        console.log('Mobile selectors NOT found');
    }
});
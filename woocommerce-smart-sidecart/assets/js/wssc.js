// In wssc.js, update the add to cart functionality
$(document).on('click', '.wssc-add-btn', function(e) {
    e.preventDefault();
    
    var button = $(this);
    var productId = button.data('product-id');
    var originalText = button.html();
    
    // Check if mobile brand/model selector exists on the page
    var mobileBrand = '';
    var mobileModel = '';
    
    // Look for mobile selector with any instance ID
    var brandSelect = $('[id^="mobile_brand_"]').first();
    var modelSelect = $('[id^="mobile_model_"]').first();
    
    if (brandSelect.length && modelSelect.length) {
        mobileBrand = brandSelect.val();
        mobileModel = modelSelect.val();
        
        // Validate mobile selection if selectors are present
        if (!mobileBrand || !mobileModel) {
            showToast('❌ Please select mobile brand and model', 'error');
            return;
        }
    }
    
    // Disable button and show loading
    button.prop('disabled', true).html('Adding...');
    
    var ajaxData = {
        action: 'wssc_add_to_cart',
        product_id: productId,
        quantity: 1,
        nonce: wsscAjax.nonce
    };
    
    // Add mobile data if available
    if (mobileBrand && mobileModel) {
        ajaxData.mobile_brand = mobileBrand;
        ajaxData.mobile_model = mobileModel;
    }
    
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
                
                // Trigger cart update
                $(document.body).trigger('wc_fragment_refresh');
                
                // Refresh the page to show updated cart items with mobile data
                setTimeout(function() {
                    location.reload();
                }, 1000);
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
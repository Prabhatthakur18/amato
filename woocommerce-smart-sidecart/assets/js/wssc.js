jQuery(function ($) {
  // Open Bulk Buy Modal
  $(document).on('click', '.wssc-bulk-btn', function () {
    var pid = $(this).data('product');
    var modal = `
      <div class="wssc-modal">
        <div class="wssc-box">
          <h3>Buy in Bulk</h3>
          <form id="wssc-form">
            <input type="hidden" name="product_id" value="${pid}">
            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="number" name="quantity" placeholder="Quantity" required>
            <textarea name="message" placeholder="Message"></textarea>
            <button type="submit">Submit</button>
            <button type="button" class="wssc-close">Close</button>
          </form>
        </div>
      </div>`;
    $('body').append(modal);
  });

  // Close Modal
  $(document).on('click', '.wssc-close', function () {
    $('.wssc-modal').remove();
  });

  // Submit Bulk Form
  $(document).on('submit', '#wssc-form', function (e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=wssc_buy_bulk';
    $.post(wsscAjax.url, data, function (res) {
      showToast(res.data.message || 'Submitted!');
      $('.wssc-modal').remove();
    });
  });

  // Toast Notification
  function showToast(msg) {
    let toast = $('<div class="wssc-toast">' + msg + '</div>').appendTo('body');
    setTimeout(() => toast.fadeOut(400, () => toast.remove()), 2000);
  }

  // Add to Cart (+)
  $(document).on('click', '.wssc-add', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $.post(wc_add_to_cart_params.ajax_url, {
      action: 'woocommerce_add_to_cart',
      product_id: id
    }, function () {
      $(document.body).trigger('wc_fragment_refresh');
      showToast('✅ Added to Cart!');
    });
  });

  // Remove from Cart (-)
  $(document).on('click', '.wssc-remove', function (e) {
    e.preventDefault();
    var id = $(this).data('id');

    // Custom AJAX remove
    $.post(wsscAjax.url, {
      action: 'wssc_remove_from_cart',
      product_id: id,
      nonce: wsscAjax.nonce
    }, function () {
      $(document.body).trigger('wc_fragment_refresh');
      showToast('❌ Removed from Cart!');
    });
  });
});

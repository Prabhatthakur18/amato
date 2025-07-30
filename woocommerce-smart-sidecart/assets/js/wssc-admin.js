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
});
(function($) {
    $(document).ready(function() {
        if (typeof mangaUpdateSite === 'undefined') {
            console.error('mangaUpdateSite object is not defined. Ensure script is enqueued correctly.');
            return;
        }

        var $button = $('.update-site-btn');
        if ($button.length === 0) {
            console.error('Update Site button not found in DOM.');
            return;
        }

        console.log('Update Site script loaded. Nonce:', mangaUpdateSite.nonce);

        $button.on('click', function(e) {
            e.preventDefault();
            var nonce = mangaUpdateSite.nonce;

            if (!nonce) {
                console.error('Nonce is missing for cache clear request');
                alert('Error: Security token missing. Please refresh the page and try again.');
                return;
            }

            $button.prop('disabled', true).after('<span class="spinner is-active" style="float:none;margin:0 10px;"></span>');

            $.ajax({
                url: mangaUpdateSite.ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_reader_clear_cache',
                    nonce: nonce
                },
                timeout: 10000,
                success: function(response) {
                    if (response.success) {
                        console.log('Cache cleared successfully:', response.data.message);
                        alert('Cache cleared successfully!');
                        window.location.reload();
                    } else {
                        console.error('Cache clear failed:', response.data ? response.data.message : 'No error message provided');
                        alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Failed to clear cache. Check console for details.'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error clearing cache:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    alert('Error: Failed to clear cache. Status: ' + status + '. Check console for details.');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $button.siblings('.spinner').remove();
                }
            });
        });
    });
})(jQuery.noConflict());
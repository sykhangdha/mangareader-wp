jQuery(document).ready(function($) {
    // Verify jQuery and wp.media are loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded.');
        alert('Error: jQuery is not loaded. Please check for plugin conflicts or WordPress issues.');
        return;
    }
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('wp.media is not loaded.');
        alert('Error: WordPress media library is not loaded. Please ensure wp_enqueue_media() is called and check for plugin conflicts.');
        return;
    }

    // Toggle Sections
    $('.manga-reader-section .section-title').on('click', function() {
        console.log('Section title clicked:', $(this).text());
        const $title = $(this);
        const $content = $title.siblings('.section-content');
        $title.toggleClass('collapsed');
        $content.slideToggle(400, function() {
            $content.toggleClass('collapsed');
        });
    });

    // Scan Manga Folder
    $('#scan-manga-folder').on('click', function() {
        console.log('Scan manga folder clicked');
        const $button = $(this);
        const $spinner = $button.siblings('.spinner');
        const $results = $('#manga-scan-results');
        $spinner.addClass('is-active');
        $button.prop('disabled', true);

        $.ajax({
            url: mangaAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'manga_reader_scan_manga_folder',
                nonce: mangaAdminAjax.nonce
            },
            success: function(res) {
                let html = '<p style="color:' + (res.success ? 'green' : 'red') + ';">' + (res.data.message || 'Error scanning manga folder.') + '</p>';
                if (res.success && res.data.mangas && res.data.mangas.length > 0) {
                    html += '<ul class="manga-list">';
                    res.data.mangas.forEach(m => {
                        html += '<li>' + m.name;
                        if (m.cover) {
                            html += ' <img src="' + m.cover + '" style="max-width:50px; vertical-align:middle; border-radius:3px;" />';
                        }
                        html += '</li>';
                    });
                    html += '</ul>';
                }
                $results.html(html);
            },
            error: function(xhr, status, error) {
                console.error('Scan manga folder error:', error);
                $results.html('<p style="color:red;">Error scanning manga folder: ' + error + '</p>');
            },
            complete: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
            }
        });
    });

    // Add Manga Form
    $('#add-manga-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Add manga form submitted');
        const $form = $(this);
        const $spinner = $form.find('.spinner');
        const $button = $form.find('button[type="submit"]');
        const $results = $('#manga-add-results');
        $spinner.addClass('is-active');
        $button.prop('disabled', true);

        var formData = new FormData(this);
        formData.append('action', 'manga_reader_add_manga_folder');

        $.ajax({
            url: mangaAdminAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $results.html('<p style="color:' + (res.success ? 'green' : 'red') + ';">' + (res.data.message || 'Error adding manga folder.') + '</p>');
                if (res.success) {
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Add manga folder error:', error);
                $results.html('<p style="color:red;">Error adding manga folder: ' + error + '</p>');
            },
            complete: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
            }
        });
    });

    // Edit Manga Cover Form
    $('#edit_manga_name').on('change', function() {
        console.log('Edit manga name changed:', $(this).val());
        const mangaName = $(this).val();
        const $preview = $('#cover-preview');
        if (mangaName) {
            $.ajax({
                url: mangaAdminAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_reader_scan_manga_folder',
                    nonce: mangaAdminAjax.nonce
                },
                success: function(res) {
                    if (res.success && res.data.mangas) {
                        const manga = res.data.mangas.find(m => m.name === mangaName);
                        if (manga && manga.cover) {
                            $preview.html('<img src="' + manga.cover + '" />');
                        } else {
                            $preview.empty();
                        }
                    } else {
                        $preview.empty();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Edit manga cover preview error:', error);
                    $preview.empty();
                }
            });
        } else {
            $preview.empty();
        }
    });

    $('#edit-manga-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Edit manga form submitted');
        const $form = $(this);
        const $spinner = $form.find('.spinner');
        const $button = $form.find('button[type="submit"]');
        const $results = $('#manga-edit-results');
        $spinner.addClass('is-active');
        $button.prop('disabled', true);

        var formData = new FormData(this);
        formData.append('action', 'manga_reader_update_manga_cover');

        $.ajax({
            url: mangaAdminAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $results.html('<p style="color:' + (res.success ? 'green' : 'red') + ';">' + (res.data.message || 'Error updating cover.') + '</p>');
                if (res.success && res.data.cover_url) {
                    $('#cover-preview').html('<img src="' + res.data.cover_url + '" />');
                } else if (res.success) {
                    $('#cover-preview').empty();
                }
            },
            error: function(xhr, status, error) {
                console.error('Update manga cover error:', error);
                $results.html('<p style="color:red;">Error updating cover: ' + error + '</p>');
            },
            complete: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
            }
        });
    });

    // Add Chapter Form - Media Library
    var addFrame;
    $('#-chapter_images_upload').on('click', function(e) {
        e.preventDefault();
        console.log('Add chapter images upload clicked');
        if (!wp.media) {
            console.error('wp.media is not available');
            alert('Error: WordPress media library is not available. Please check for plugin conflicts or WordPress issues.');
            return;
        }
        if (addFrame) {
            addFrame.open();
            return;
        }
        addFrame = wp.media({
            title: 'Select Chapter Images',
            library: { type: 'image' },
            button: { text: 'Use Selected Images' },
            multiple: true
        });
        addFrame.on('select', function() {
            var attachments = addFrame.state().get('selection').toJSON();
            var ids = attachments.map(a => a.id);
            var previews = attachments.map(a => `<img src="${a.url}" style="max-width:100px; margin:5px;" />`).join('');
            $('#chapter_image_ids').val(ids.join(','));
            $('#image-preview').html(previews);
            console.log('Images selected:', ids);
        });
        addFrame.open();
    });

    // Dynamic Chapter Name Input Based on Manga Selection
    $('#chapter_manga').on('change', function() {
        console.log('Chapter manga changed:', $(this).val());
        const manga = $(this).val();
        if (!manga) {
            $('#chapter-naming-message').hide();
            $('#chapter-vol-ch-container').hide();
            $('#chapter-name-container').show();
            $('#chapter_vol').prop('required', false);
            $('#chapter_num').prop('required', false);
            $('#chapter_name').prop('required', true);
            return;
        }

        $.ajax({
            url: mangaAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'manga_reader_check_chapter_naming',
                nonce: mangaAdminAjax.nonce,
                manga: manga
            },
            success: function(res) {
                if (res.success && res.data.uses_vol_ch) {
                    $('#chapter-naming-message').show();
                    $('#chapter-vol-ch-container').show();
                    $('#chapter-name-container').hide();
                    $('#chapter_vol').prop('required', true);
                    $('#chapter_num').prop('required', true);
                    $('#chapter_name').prop('required', false);
                } else {
                    $('#chapter-naming-message').hide();
                    $('#chapter-vol-ch-container').hide();
                    $('#chapter-name-container').show();
                    $('#chapter_vol').prop('required', false);
                    $('#chapter_num').prop('required', false);
                    $('#chapter_name').prop('required', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Check chapter naming error:', error);
                $('#chapter-naming-message').hide();
                $('#chapter-vol-ch-container').hide();
                $('#chapter-name-container').show();
                $('#chapter_vol').prop('required', false);
                $('#chapter_num').prop('required', false);
                $('#chapter_name').prop('required', true);
            }
        });
    });

    $('#add-chapter-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Add chapter form submitted');
        const $form = $(this);
        const $spinner = $form.find('.spinner');
        const $button = $form.find('button[type="submit"]');
        const $results = $('#chapter-add-results');
        $spinner.addClass('is-active');
        $button.prop('disabled', true);

        var formData = new FormData(this);
        formData.append('action', 'manga_reader_add_chapter');

        // Handle chapter name formatting
        const chapterVol = $('#chapter_vol').val();
        const chapterNum = $('#chapter_num').val();
        if (chapterVol && chapterNum) {
            formData.delete('chapter_name'); // Remove if present
            formData.append('chapter_vol', chapterVol);
            formData.append('chapter_num', chapterNum);
        }

        $.ajax({
            url: mangaAdminAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $results.html('<p style="color:' + (res.success ? 'green' : 'red') + ';">' + (res.data.message || 'Error adding chapter.') + '</p>');
                if (res.success) {
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Add chapter error:', error);
                $results.html('<p style="color:red;">Error adding chapter: ' + error + '</p>');
            },
            complete: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
            }
        });
    });

    // Manga Filter
    $('#manga_filter').on('change', function() {
        console.log('Manga filter changed:', $(this).val());
        const manga = $(this).val();
        const url = new URL(window.location.href);
        if (manga) {
            url.searchParams.set('manga_filter', manga);
        } else {
            url.searchParams.delete('manga_filter');
        }
        url.searchParams.delete('paged');
        window.location.href = url.toString();
    });

    // Edit Chapter - Inline Form
    $('.edit-chapter').on('click', function() {
        console.log('Edit chapter button clicked, chapter ID:', $(this).data('id'));
        const chapterId = $(this).data('id');
        const $row = $(`tr[data-chapter-id="${chapterId}"]`);
        const $editRow = $row.next('.edit-chapter-form');
        const $results = $editRow.find('.chapter-update-results');

        // Hide all other edit forms
        $('.edit-chapter-form').hide();

        $.ajax({
            url: mangaAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'manga_reader_get_chapter_data',
                chapter_id: chapterId,
                nonce: mangaAdminAjax.nonce
            },
            success: function(res) {
                if (res.success) {
                    $(`#edit_chapter_manga_${chapterId}`).val(res.data.manga_name);
                    $(`#edit_chapter_name_${chapterId}`).val(res.data.chapter_name);
                    $(`#edit_chapter_date_${chapterId}`).val(res.data.chapter_date);
                    $(`.edit-chapter-image-ids[data-id="${chapterId}"]`).val(res.data.image_ids.join(','));
                    $(`.edit-image-preview[data-id="${chapterId}"]`).html(res.data.image_previews);
                    $(`#edit_chapter_image_urls_${chapterId}`).val(res.data.image_urls);
                    $editRow.show();
                } else {
                    $results.html('<p style="color:red;">' + (res.data.message || 'Error loading chapter data.') + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Get chapter data error:', error);
                $results.html('<p style="color:red;">Error loading chapter data: ' + error + '</p>');
            }
        });
    });

    // Close Edit Form
    $('.close-edit-form').on('click', function() {
        console.log('Close edit form clicked');
        const $editRow = $(this).closest('.edit-chapter-form');
        $editRow.hide();
        $editRow.find('.chapter-update-results').empty();
    });

    // Edit Chapter - Media Library
    var editFrames = {};
    $('.edit-chapter-images-upload').on('click', function(e) {
        e.preventDefault();
        const chapterId = $(this).data('id');
        console.log('Edit chapter images upload clicked for chapter ID:', chapterId);
        if (!wp.media) {
            console.error('wp.media is not available');
            alert('Error: WordPress media library is not available. Please check for plugin conflicts or WordPress issues.');
            return;
        }
        if (!editFrames[chapterId]) {
            editFrames[chapterId] = wp.media({
                title: 'Select Chapter Images',
                library: { type: 'image' },
                button: { text: 'Use Selected Images' },
                multiple: true
            });
            editFrames[chapterId].on('select', function() {
                var attachments = editFrames[chapterId].state().get('selection').toJSON();
                var ids = attachments.map(a => a.id);
                var previews = attachments.map(a => `<img src="${a.url}" style="max-width:100px; margin:5px;" />`).join('');
                $(`.edit-chapter-image-ids[data-id="${chapterId}"]`).val(ids.join(','));
                $(`.edit-image-preview[data-id="${chapterId}"]`).html(previews);
                console.log('Images selected for edit chapter ID:', chapterId, ids);
            });
        }
        editFrames[chapterId].open();
    });

    // Update Chapter Form
    $('.update-chapter-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Update chapter form submitted');
        const $form = $(this);
        const $spinner = $form.find('.spinner');
        const $button = $form.find('button[type="submit"]');
        const $results = $form.find('.chapter-update-results');
        $spinner.addClass('is-active');
        $button.prop('disabled', true);

        var formData = new FormData(this);
        formData.append('action', 'manga_reader_update_chapter');

        $.ajax({
            url: mangaAdminAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $results.html('<p style="color:' + (res.success ? 'green' : 'red') + ';">' + (res.data.message || 'Error updating chapter.') + '</p>');
                if (res.success) {
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Update chapter error:', error);
                $results.html('<p style="color:red;">Error updating chapter: ' + error + '</p>');
            },
            complete: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
            }
        });
    });

    // Delete Chapter
    $('.delete-chapter').on('click', function() {
        console.log('Delete chapter button clicked, chapter ID:', $(this).data('id'));
        const chapterId = $(this).data('id');
        const $row = $(`tr[data-chapter-id="${chapterId}"]`);
        const $editRow = $row.next('.edit-chapter-form');
        const $results = $editRow.find('.chapter-update-results');

        if (!confirm('Are you sure you want to delete this chapter? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: mangaAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'manga_reader_delete_chapter',
                chapter_id: chapterId,
                nonce: mangaAdminAjax.nonce
            },
            success: function(res) {
                if (res.success) {
                    $row.remove();
                    $editRow.remove();
                    alert(res.data.message);
                } else {
                    $results.html('<p style="color:red;">' + (res.data.message || 'Error deleting chapter.') + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete chapter error:', error);
                $results.html('<p style="color:red;">Error deleting chapter: ' + error + '</p>');
            }
        });
    });

    // Media uploader for announcement image
    $('#announcement_image_upload').on('click', function(e) {
        e.preventDefault();
        console.log('Announcement image upload clicked');
        if (!wp.media) {
            console.error('wp.media is not available');
            alert('Error: WordPress media library is not available. Please check for plugin conflicts or WordPress issues.');
            return;
        }
        var frame = wp.media({
            title: 'Select Announcement Image',
            button: { text: 'Use this image' },
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#announcement_image_url').val(attachment.url);
            $('#announcement_image_preview').html('<img src="' + attachment.url + '" style="max-width:100px; margin:5px;" />');
            console.log('Announcement image selected:', attachment.url);
        });

        frame.open();
    });
});

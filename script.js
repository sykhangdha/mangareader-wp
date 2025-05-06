jQuery(document).ready(function ($) {
    let currentImages = [];
    let currentIndex = 0;
    let viewType = 'list';
    let allChapters = [];
    let currentChapterIndex = -1;

    const $heading = $('#manga-heading');
    const $imageContainer = $('#manga-images');
    const $chapterListContainer = $('#mangaview-chapterlist');
    const $coverImage = $('#manga-cover');
    const $sidebar = $('#manga-sidebar');
    const $chapterListWrapper = $('#chapter-list-container');
    const mangaName = $('.manga-viewer').data('manga-name');

    function showLoadingHeading() {
        $heading.text(`${mangaName} - Loading...`);
    }

    function updateHeading(chapter) {
        const formatted = chapter === 'Chapters'
            ? 'Chapters'
            : chapter.replace(/[_\-]/g, ' ')
                     .replace(/^(ch(?:apter)?)(\s*\d+)/i, (_, p1, p2) =>
                         p1.charAt(0).toUpperCase() + p1.slice(1) + p2
                     );
        $heading.text(`${mangaName} - ${formatted}`);
    }

    function loadChapters() {
        updateHeading('Chapters');
        $imageContainer.hide().empty();
        $sidebar.find('.view-toggle, #back-to-chapters').hide();
        $chapterListContainer.empty().show();

        $.post(mangaAjax.ajaxurl, {
            action: 'get_chapters',
            manga: mangaName
        }, function (res) {
            if (res.success) {
                allChapters = res.data.chapters.sort((a, b) => {
                    const extractNum = str => {
                        const match = str.match(/Ch\.?\s*(\d+(?:\.\d+)?)/i);
                        return match ? parseFloat(match[1]) : 0;
                    };
                    return extractNum(b.name) - extractNum(a.name);
                });

                const html = allChapters.map(ch => {
                    const date = ch.date ? `<span class="chapter-date">(${ch.date})</span>` : '';
                    return `<li><a href="#" class="chapter-link" data-chapter="${ch.name}">${ch.name}</a> ${date}</li>`;
                }).join('');

                $chapterListContainer.html(html);
                $(window).trigger('resize');  // Recalculate layout after chapters are loaded
            } else {
                $heading.text(`${mangaName} - No Chapters Available`);
            }
        });
    }

    function loadImages(chapter) {
        showLoadingHeading();
        $chapterListWrapper.hide();  // Hide the chapter list wrapper when loading images
        $coverImage.fadeOut();  // Hide cover image when loading chapter
        $imageContainer.empty().hide();

        $.post(mangaAjax.ajaxurl, {
            action: 'get_images',
            manga: mangaName,
            chapter
        }, function (res) {
            if (res.success) {
                currentImages = res.data;
                currentIndex = 0;
                currentChapterIndex = allChapters.findIndex(c => c.name === chapter);

                renderImages(() => {
                    updateHeading(chapter);
                    $imageContainer.show();
                    $sidebar.find('.view-toggle, #back-to-chapters').show();
                    $sidebar.fadeIn();  // Fade in the sidebar when images are loaded
                });
            } else {
                $heading.text(`${mangaName} - Images Not Found`);
            }
        });
    }

    function renderImages(done) {
        $imageContainer.empty();

        if (viewType === 'list') {
            currentImages.forEach((src, i) => {
                $('<img>', {
                    src,
                    class: 'manga-image',
                    css: { display: 'none' }
                }).on('load', function () {
                    $(this).fadeIn(300 + i * 50);
                }).appendTo($imageContainer);
            });
        } else {
            const $img = $('<img>', {
                src: currentImages[currentIndex],
                class: 'manga-image',
                css: { display: 'none' }
            }).on('load', function () {
                $(this).fadeIn(300);
            });

            $imageContainer.append($img).append(`
                <div class="paged-controls">
                    <button id="prev-page">Prev</button>
                    <span>Page ${currentIndex + 1} of ${currentImages.length}</span>
                    <button id="next-page">Next</button>
                </div>
            `);

            $('#prev-page').off().on('click', function () {
                if (currentIndex > 0) {
                    currentIndex--;
                    renderImages(done);
                }
            });

            $('#next-page').off().on('click', function () {
                if (currentIndex < currentImages.length - 1) {
                    currentIndex++;
                    renderImages(done);
                }
            });

            $img.on('click', function () {
                if (currentIndex < currentImages.length - 1) {
                    currentIndex++;
                    renderImages(done);
                }
            });
        }

        showBottomNav();
        done();
    }

    function showBottomNav() {
        $('#bottom-chapter-navigation').remove();

        const $nav = $(` 
            <div id="bottom-chapter-navigation" style="text-align:center; margin:20px 0;">
				 <button id="next-chapter-bottom">Prev Chapter</button>
                <button id="prev-chapter-bottom">Next Chapter</button>
            </div>
        `).appendTo($imageContainer);

        $('#prev-chapter-bottom').off().on('click', function () {
            if (currentChapterIndex > 0) {
                loadImages(allChapters[currentChapterIndex - 1].name);
            }
        });

        $('#next-chapter-bottom').off().on('click', function () {
            if (currentChapterIndex < allChapters.length - 1) {
                loadImages(allChapters[currentChapterIndex + 1].name);
            }
        });
    }

    $(document).on('click', '.chapter-link', function (e) {
        e.preventDefault();
        const chapter = $(this).data('chapter');
        $('html, body').animate({ scrollTop: 0 }, 300);
        loadImages(chapter);
    });

    $('#back-to-chapters').on('click', function () {
        $imageContainer.hide().empty();
        $('#bottom-chapter-navigation').remove();
        $sidebar.find('.view-toggle, #back-to-chapters').hide();
        $chapterListWrapper.show();
        $coverImage.fadeIn();  // Show cover image again
        loadChapters();
    });

    $('.view-toggle button').on('click', function () {
        viewType = $(this).data('view');
        if (currentImages.length) {
            renderImages(() => updateHeading(allChapters[currentChapterIndex].name));
        }
    });

    $(document).on('click', '.manga-image', function () {
        const $next = $(this).next('.manga-image');
        if ($next.length) {
            $('html, body').animate({
                scrollTop: $next.offset().top
            }, 300);
        }
    });

    loadChapters();
});

jQuery(document).ready(function ($) {
    let currentImages = [];
    let currentIndex = 0;
    let viewType = 'list';
    let allChapters = [];
    let currentChapterIndex = -1;
    let isDragging = false;
    let startY = 0;
    let startScrollTop = 0;
    let targetScrollTop = 0;
    let currentScrollTop = 0;

    const $heading = $('#manga-heading');
    const $imageContainer = $('#manga-images');
    const $chapterListContainer = $('#mangaview-chapterlist');
    const $coverImage = $('#manga-cover');
    const $sidebar = $('#manga-sidebar');
    const $chapterListWrapper = $('#chapter-list-container');
    const mangaName = $('.manga-viewer').data('manga-name');

    // Create spinner element
    const $spinner = $('<div>', {
        class: 'manga-spinner',
        css: {
            display: 'none',
            textAlign: 'center',
            margin: '20px 0'
        },
        html: '<div class="spinner-circle"></div>'
    }).appendTo($imageContainer);

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
        $spinner.hide();
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
                $(window).trigger('resize');
            } else {
                $heading.text(`${mangaName} - No Chapters Available`);
            }
        });
    }

    function preloadImages(imageUrls) {
        imageUrls.forEach(url => {
            const img = new Image();
            img.src = url;
            img.onerror = () => {
                console.warn(`Failed to preload image: ${url}`);
            };
        });
    }

    function loadImages(chapter) {
        showLoadingHeading();
        $chapterListWrapper.hide();
        $coverImage.fadeOut();
        $imageContainer.empty().hide();
        $spinner.show();

        $.post(mangaAjax.ajaxurl, {
            action: 'get_images',
            manga: mangaName,
            chapter
        }, function (res) {
            $spinner.hide();
            if (res.success) {
                currentImages = Array.isArray(res.data) ? res.data.filter(url => url && typeof url === 'string') : [];
                currentIndex = 0;
                currentChapterIndex = allChapters.findIndex(c => c.name === chapter);

                if (currentImages.length === 0) {
                    $heading.text(`${mangaName} - No Images Available`);
                    $sidebar.find('.view-toggle, #back-to-chapters').hide();
                    return;
                }

                preloadImages(currentImages);

                if (currentChapterIndex < allChapters.length - 1) {
                    const nextChapter = allChapters[currentChapterIndex + 1].name;
                    $.post(mangaAjax.ajaxurl, {
                        action: 'get_images',
                        manga: mangaName,
                        chapter: nextChapter
                    }, function (nextRes) {
                        if (nextRes.success && Array.isArray(nextRes.data)) {
                            preloadImages(nextRes.data);
                        }
                    });
                }

                renderImages(() => {
                    updateHeading(chapter);
                    $imageContainer.show();
                    $sidebar.find('.view-toggle, #back-to-chapters').show();
                    $sidebar.fadeIn();
                });
            } else {
                $heading.text(`${mangaName} - Images Not Found`);
                $sidebar.find('.view-toggle, #back-to-chapters').hide();
            }
        }).fail(function () {
            $spinner.hide();
            $heading.text(`${mangaName} - Error Loading Images`);
            $sidebar.find('.view-toggle, #back-to-chapters').hide();
        });
    }

    function renderImages(done) {
        $imageContainer.empty();

        if (viewType === 'list') {
            currentImages.forEach((src, i) => {
                const $img = $('<img>', {
                    src,
                    class: 'manga-image',
                    css: { display: 'none' }
                }).on('load', function () {
                    $(this).fadeIn(300 + i * 50);
                }).on('error', function () {
                    console.warn(`Failed to load image: ${src}`);
                    $(this).remove();
                });
                $imageContainer.append($img);
            });
        } else {
            if (!currentImages[currentIndex]) {
                currentIndex = 0;
            }
            const $img = $('<img>', {
                src: currentImages[currentIndex],
                class: 'manga-image',
                css: { display: 'none' }
            }).on('load', function () {
                $(this).fadeIn(300);
            }).on('error', function () {
                console.warn(`Failed to load image: ${currentImages[currentIndex]}`);
                $(this).remove();
                currentIndex = currentIndex < currentImages.length - 1 ? currentIndex + 1 : 0;
                renderImages(done);
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

    // Smooth drag scrolling for List View
    function lerp(start, end, factor) {
        return start + (end - start) * factor;
    }

    function updateScroll() {
        if (isDragging && viewType === 'list') {
            currentScrollTop = lerp(currentScrollTop, targetScrollTop, 0.1);
            $(window).scrollTop(currentScrollTop);
            requestAnimationFrame(updateScroll);
        }
    }

    $(document).on('mousedown', '.manga-image', function (e) {
        if (viewType === 'list') {
            e.preventDefault();
            isDragging = true;
            startY = e.pageY;
            startScrollTop = $(window).scrollTop();
            currentScrollTop = startScrollTop;
            targetScrollTop = startScrollTop;
            $imageContainer.css('cursor', 'grabbing');
            document.body.style.userSelect = 'none';
            requestAnimationFrame(updateScroll);
        }
    });

    $(document).on('mousemove', function (e) {
        if (isDragging && viewType === 'list') {
            const deltaY = (startY - e.pageY) * 2.0; // Increased multiplier for faster scrolling
            targetScrollTop = startScrollTop + deltaY;
        }
    });

    $(document).on('mouseup mouseleave', function () {
        if (isDragging) {
            isDragging = false;
            $imageContainer.css('cursor', 'default');
            document.body.style.userSelect = '';
        }
    });

    // Keyboard navigation for smooth, manga-like scrolling in List View
    $(document).on('keydown', function (e) {
        if (viewType === 'list' && (e.key === 'ArrowDown' || e.key === ' ')) {
            e.preventDefault();
            const viewportHeight = $(window).height();
            const scrollDistance = viewportHeight * 0.8; // Scroll 80% of viewport height
            const currentScroll = $(window).scrollTop();
            const $images = $('.manga-image:visible');
            const lastImageBottom = $images.last().offset().top + $images.last().height();

            // If near the end of the chapter, load the next chapter
            if (currentScroll + viewportHeight >= lastImageBottom - 50 && currentChapterIndex < allChapters.length - 1) {
                loadImages(allChapters[currentChapterIndex + 1].name);
            } else {
                // Scroll down by partial viewport height
                $('html, body').animate({
                    scrollTop: currentScroll + scrollDistance
                }, 500); // Slower animation for readability
            }
        }
    });

    $(document).on('click', '.chapter-link', function (e) {
        e.preventDefault();
        const chapter = $(this).data('chapter');
        $('html, body').animate({ scrollTop: 0 }, 300);
        loadImages(chapter);
    });

    $('#back-to-chapters').on('click', function () {
        $imageContainer.hide().empty();
        $spinner.hide();
        $('#bottom-chapter-navigation').remove();
        $sidebar.find('.view-toggle, #back-to-chapters').hide();
        $chapterListWrapper.show();
        $coverImage.fadeIn();
        loadChapters();
    });

    $('.view-toggle button').on('click', function () {
        viewType = $(this).data('view');
        if (currentImages.length) {
            renderImages(() => updateHeading(allChapters[currentChapterIndex].name));
        }
    });

    loadChapters();
});

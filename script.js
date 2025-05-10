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
    const $sidebarList = $('#mangaview-chapterlist-sidebar');
    const $coverImage = $('#manga-cover');
    const $sidebar = $('#manga-sidebar');
    const $chapterListWrapper = $('#chapter-list-container');
    const $imageWrapper = $('#manga-images-container');
    const mangaName = $('.manga-viewer').data('manga-name');

    // Create global spinner element
    const $spinner = $('<div>', {
        class: 'manga-spinner',
        css: {
            display: 'none',
            textAlign: 'center',
            margin: '20px 0'
        },
        html: '<div class="spinner-circle"></div>'
    }).appendTo($imageContainer);

    // Create loading message element
    const $loadingMessage = $('<div>', {
        class: 'manga-loading-message',
        css: {
            display: 'none',
            textAlign: 'center',
            margin: '20px 0',
            fontSize: '18px',
            color: '#333'
        },
        text: 'Images are loading please wait 3 seconds'
    }).appendTo($imageContainer);

    // Create sidebar toggle button
    const $toggleButton = $('<button>', {
        class: 'sidebar-toggle',
        text: 'Chapters/Settings',
        css: {
            display: 'none'
        }
    }).appendTo($imageWrapper);

    // Ensure close button exists
    if (!$sidebar.find('.sidebar-close').length) {
        $('<button>', {
            class: 'sidebar-close',
            text: 'Close',
            css: { display: 'block' }
        }).prependTo($sidebar);
    }
    const $closeButton = $sidebar.find('.sidebar-close');

    // Detect mobile device
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 768;
    }

    // Preload images in background
    function preloadImages(imageUrls) {
        imageUrls.forEach(url => {
            const img = new Image();
            img.src = url;
            img.onerror = () => {
                console.warn(`Failed to preload image: ${url}`);
            };
        });
    }

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

    function toggleSidebar(show) {
        if (show) {
            $sidebar.removeClass('sidebar-hidden').css({ display: 'block', opacity: 1, transform: 'translateX(0)' });
            $imageContainer.removeClass('sidebar-expanded');
            $toggleButton.css('display', 'none');
            $closeButton.css('display', 'block');
        } else {
            $sidebar.addClass('sidebar-hidden').css({ opacity: 0, transform: 'translateX(100%)' });
            $imageContainer.addClass('sidebar-expanded');
            $toggleButton.css('display', 'block');
            $closeButton.css('display', 'none');
        }
    }

    function loadChapters() {
        updateHeading('Chapters');
        $imageWrapper.hide();
        $imageContainer.empty();
        $spinner.hide();
        $loadingMessage.hide();
        $chapterListContainer.empty().show();
        $sidebarList.empty();
        $chapterListWrapper.show();
        toggleSidebar(false);
        $('.view-toggle').hide();
        $coverImage.show();

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
                $sidebarList.html(html);
                $(window).trigger('resize');
            } else {
                $heading.text(`${mangaName} - No Chapters Available`);
            }
        });
    }

    function loadImages(chapter) {
        showLoadingHeading();
        $chapterListWrapper.hide();
        $imageWrapper.show();
        $imageContainer.empty().show();
        $spinner.show();
        $loadingMessage.hide();
        $('.view-toggle').show();

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
                    $('.view-toggle').hide();
                    $loadingMessage.hide();
                    toggleSidebar(false);
                    return;
                }

                if (isMobileDevice()) {
                    preloadImages(currentImages);
                }

                renderImages(() => {
                    updateHeading(chapter);
                    toggleSidebar(true);
                });
            } else {
                $heading.text(`${mangaName} - Images Not Found`);
                $('.view-toggle').hide();
                $loadingMessage.hide();
                toggleSidebar(false);
            }
        }).fail(function () {
            $spinner.hide();
            $loadingMessage.hide();
            $heading.text(`${mangaName} - Error Loading Images`);
            $('.view-toggle').hide();
            toggleSidebar(false);
        });
    }

    function renderImages(done) {
        $imageContainer.empty();
        const isMobile = isMobileDevice();

        if (viewType === 'list') {
            if (isMobile) {
                $imageContainer.show();
                $loadingMessage.show();
                setTimeout(() => {
                    $imageContainer.empty();
                    currentImages.forEach(src => {
                        const $imgWrapper = $('<div>', {
                            class: 'manga-image-wrapper',
                            css: {
                                position: 'relative',
                                backgroundColor: '#f0f0f0',
                                minHeight: '100px'
                            }
                        });
                        const $img = $('<img>', {
                            src: src,
                            class: 'manga-image',
                            css: { display: 'block', width: '100%', height: 'auto' }
                        }).on('error', function () {
                            console.warn(`Failed to load image: ${src}`);
                            $(this).parent().remove();
                        });
                        $imgWrapper.append($img);
                        $imageContainer.append($imgWrapper);
                    });
                    done();
                }, 3000);
            } else {
                currentImages.forEach((src, i) => {
                    const $img = $('<img>', {
                        src: src,
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
                done();
            }
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
            done();
        }

        showBottomNav();
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
            const deltaY = (startY - e.pageY) * 2.0;
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

    $(document).on('keydown', function (e) {
        if (viewType !== 'list') return;
        if (e.key === 'ArrowDown' || e.key === ' ') {
            e.preventDefault();
            const viewportHeight = $(window).height();
            const scrollDistance = viewportHeight * 0.8;
            const currentScroll = window.scrollY;
            const $lastImage = $('.manga-image:visible').last();
            const lastImageBottom = $lastImage.length ? $lastImage.offset().top + $lastImage.height() : 0;

            if (currentScroll + viewportHeight >= lastImageBottom - 50 && currentChapterIndex < allChapters.length - 1) {
                loadImages(allChapters[currentChapterIndex + 1].name);
            } else {
                window.scrollBy({ top: scrollDistance, behavior: 'smooth' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const viewportHeight = $(window).height();
            const scrollDistance = -viewportHeight * 0.8;
            window.scrollBy({ top: scrollDistance, behavior: 'smooth' });
        }
    });

    $(document).on('click', '.chapter-link', function (e) {
        e.preventDefault();
        const chapter = $(this).data('chapter');
        $('html, body').animate({ scrollTop: 0 }, 300);
        loadImages(chapter);
    });

    $('.view-toggle button').on('click', function () {
        viewType = $(this).data('view');
        if (currentImages.length) {
            renderImages(() => updateHeading(allChapters[currentChapterIndex].name));
        }
    });

    $toggleButton.on('click', function () {
        console.log('Toggle button clicked');
        toggleSidebar(true);
    });

    $closeButton.on('click', function () {
        console.log('Close button clicked');
        toggleSidebar(false);
    });

    $('#back-to-home, #back-to-home-sidebar').on('click', function () {
        window.location.href = '<?php echo esc_url(home_url()); ?>';
    });

    loadChapters();
});

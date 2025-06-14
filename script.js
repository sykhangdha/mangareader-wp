jQuery(document).ready(function ($) {
    // State variables
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

    // DOM elements
    const $heading = $('#manga-heading');
    const $imageContainer = $('#manga-images');
    const $chapterListContainer = $('#mangaview-chapterlist');
    const $sidebarList = $('#mangaview-chapterlist-sidebar');
    const $coverImage = $('#manga-cover');
    const $sidebar = $('#manga-sidebar');
    const $chapterListWrapper = $('#chapter-list-container');
    const $imageWrapper = $('#manga-images-container');
    const mangaName = $('.manga-viewer').data('manga-name');
    const $toggleButton = $('.sidebar-toggle');
    const $closeButton = $('.sidebar-close');

    // Create global spinner
    const $spinner = $('<div>', {
        class: 'manga-spinner',
        css: { display: 'none', textAlign: 'center', margin: '20px 0' },
        html: '<div class="spinner-circle"></div>'
    }).appendTo($imageContainer);

    // Create loading message
    const $loadingMessage = $('<div>', {
        class: 'manga-loading-message',
        css: { display: 'none', textAlign: 'center', margin: '20px 0', fontSize: '18px', color: '#333' },
        text: 'Images are loading please wait 3 seconds'
    }).appendTo($imageContainer);

    // Utility: Detect mobile device
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 768;
    }

    // Utility: Preload images
    function preloadImages(imageUrls) {
        imageUrls.forEach(url => {
            const img = new Image();
            img.src = url;
            img.onerror = () => console.warn(`Failed to preload image: ${url}`);
        });
    }

    // Update heading with loading state
    function showLoadingHeading() {
        $heading.text(`${mangaName} - Loading...`);
    }

    // Format and update heading
    function updateHeading(chapter) {
        const formatted = chapter === 'Chapters'
            ? 'Chapters'
            : chapter.replace(/[_\-]/g, ' ')
                     .replace(/^(ch(?:apter)?)(\s*\d+)/i, (_, p1, p2) =>
                         p1.charAt(0).toUpperCase() + p1.slice(1) + p2
                     );
        $heading.text(`${mangaName} - ${formatted}`);
    }

    // Toggle sidebar visibility
    function toggleSidebar(show) {
        if (show) {
            $sidebar.removeClass('sidebar-hidden').css({ opacity: 1 });
            $imageContainer.removeClass('sidebar-expanded');
            $toggleButton.css('display', 'none');
            $closeButton.css('display', 'block');
            $sidebar.find('.view-toggle').show();
        } else {
            $sidebar.addClass('sidebar-hidden').css({ opacity: 0 });
            $imageContainer.addClass('sidebar-expanded');
            $toggleButton.css('display', 'block');
            $closeButton.css('display', 'none');
            $sidebar.find('.view-toggle').hide();
        }
    }

    // Load chapters
    function loadChapters() {
        updateHeading('Chapters');
        $imageWrapper.hide();
        $imageContainer.empty();
        $spinner.hide();
        $loadingMessage.hide();
        $chapterListContainer.empty().show();
        $sidebarList.empty();
        $chapterListWrapper.show();
        $coverImage.show();

        if (!mangaAjax || !mangaAjax.ajaxurl) {
            console.error('mangaAjax.ajaxurl is undefined. Ensure AJAX is properly enqueued.');
            $heading.text(`${mangaName} - Error: AJAX Not Configured`);
            return;
        }

        $.post(mangaAjax.ajaxurl, {
            action: 'get_chapters',
            manga: mangaName
        }, function (res) {
            console.log('get_chapters response:', res); // Debug response
            if (res.success && Array.isArray(res.data.chapters)) {
                allChapters = res.data.chapters.sort((a, b) => {
                    const extractNum = str => {
                        const match = str.match(/Ch\.?\s*(\d+(?:\.\d+)?)/i);
                        return match ? parseFloat(match[1]) : 0;
                    };
                    return extractNum(b.name) - extractNum(a.name);
                });

                // Create chapter list HTML for both main and sidebar lists
                const fragment = document.createDocumentFragment();
                allChapters.forEach(ch => {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = '#';
                    a.className = 'chapter-link';
                    a.dataset.chapter = ch.name;
                    a.textContent = ch.name;
                    li.appendChild(a);
                    if (ch.date) {
                        const dateSpan = document.createElement('span');
                        dateSpan.className = 'chapter-date';
                        dateSpan.textContent = ` (${ch.date})`;
                        li.appendChild(dateSpan);
                    }
                    fragment.appendChild(li);
                });

                // Populate main and sidebar chapter lists
                $chapterListContainer.empty().append(fragment.cloneNode(true));
                $sidebarList.empty().append(fragment);

                // Highlight current chapter in sidebar
                if (currentChapterIndex >= 0) {
                    const $currentChapter = $sidebarList.find(`.chapter-link[data-chapter="${allChapters[currentChapterIndex].name}"]`);
                    if ($currentChapter.length) {
                        $currentChapter.parent().addClass('current-chapter');
                        $sidebarList[0].scrollTop = $currentChapter.parent()[0].offsetTop - $sidebarList[0].offsetTop;
                    }
                }

                // Show navigation buttons
                showBottomNav();
                toggleSidebar(false);
            } else {
                console.warn('Invalid chapters response:', res);
                $heading.text(`${mangaName} - No Chapters Available`);
                $sidebar.find('.view-toggle').hide();
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error('get_chapters AJAX failed:', textStatus, errorThrown);
            $heading.text(`${mangaName} - Error Loading Chapters`);
            $sidebar.find('.view-toggle').hide();
        });
    }

    // Load images for a chapter
    function loadImages(chapter) {
        showLoadingHeading();
        $chapterListWrapper.hide();
        $imageWrapper.show();
        $imageContainer.empty().show();
        $spinner.show();
        $loadingMessage.hide();
        $coverImage.hide();

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
                    // Highlight current chapter in sidebar
                    $sidebarList.find('.current-chapter').removeClass('current-chapter');
                    const $currentChapter = $sidebarList.find(`.chapter-link[data-chapter="${chapter}"]`);
                    if ($currentChapter.length) {
                        $currentChapter.parent().addClass('current-chapter');
                        $sidebarList[0].scrollTop = $currentChapter.parent()[0].offsetTop - $sidebarList[0].offsetTop;
                    }
                });
            } else {
                $heading.text(`${mangaName} - Images Not Found`);
                $loadingMessage.hide();
                toggleSidebar(false);
            }
        }).fail(function () {
            $spinner.hide();
            $loadingMessage.hide();
            $heading.text(`${mangaName} - Error Loading Images`);
            toggleSidebar(false);
        });
    }

    // Render images based on view type
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
                            css: { position: 'relative', backgroundColor: '#f0f0f0', minHeight: '100px' }
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
                }, 2000);
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

    // Show bottom navigation
    function showBottomNav() {
        $('#bottom-chapter-navigation').remove();

        const $nav = $(`
            <div id="bottom-chapter-navigation" class="bottom-nav">
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

    // Smooth scroll interpolation
    function lerp(start, end, factor) {
        return start + (end - start) * factor;
    }

    // Update scroll position
    function updateScroll() {
        if (isDragging && viewType === 'list') {
            currentScrollTop = lerp(currentScrollTop, targetScrollTop, 0.1);
            $(window).scrollTop(currentScrollTop);
            requestAnimationFrame(updateScroll);
        }
    }

    // Drag-to-scroll
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

    // Keyboard navigation
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

    // Chapter link click
    $(document).on('click', '.chapter-link', function (e) {
        e.preventDefault();
        const chapter = $(this).data('chapter');
        $('html, body').animate({ scrollTop: 0 }, 300);
        loadImages(chapter);
    });

    // View toggle
    $(document).on('click', '.view-toggle button', function () {
        viewType = $(this).data('view');
        if (currentImages.length) {
            renderImages(() => updateHeading(allChapters[currentChapterIndex].name));
        }
    });

    // Sidebar toggle buttons
    $toggleButton.on('click', function () {
        toggleSidebar(true);
    });

    $closeButton.on('click', function () {
        toggleSidebar(false);
    });

    // Back to home
    $('#back-to-home, #back-to-home-sidebar').on('click', function () {
        window.location.href = '<?php echo esc_url(home_url('/')); ?>';
    });

    // Header close button redirect to homepage
    $('.header-close').on('click', function () {
        window.location.href = '<?php echo esc_url(home_url('/')); ?>';
    });

    // Start menu toggle
    $(document).on('click', '[data-start-button]', function (e) {
        e.preventDefault();
        const $menu = $(this).siblings('.start-menu');
        const isVisible = $menu.is(':visible');
        $('.start-menu').hide(); // Close any open menus
        if (!isVisible) {
            $menu.stop().fadeIn(200); // Smooth fade-in
        }
    });

    // Close start menu when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.start-menu-wrapper').length) {
            $('.start-menu').stop().fadeOut(200);
        }
    });

    // Close start menu when clicking a manga link
    $(document).on('click', '.start-menu a', function () {
        $('.start-menu').stop().fadeOut(200);
    });

    // Initial load
    loadChapters();
});
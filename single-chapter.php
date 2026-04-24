<?php get_header(); ?>

<?php while (have_posts()) : the_post(); 
    $chapter_num = get_post_meta(get_the_ID(), 'chapter_number', true);
    $volume_num = get_post_meta(get_the_ID(), 'volume_number', true);
    $manga_id = get_post_meta(get_the_ID(), 'connected_manga_id', true);
    
    if (empty($chapter_num)) {
        preg_match('/(?:Ch\.?\s*)(\d+(?:\.\d+)?)/i', get_the_title(), $matches);
        $chapter_num = isset($matches[1]) ? $matches[1] : '?';
    }
    
    if (empty($volume_num)) {
        preg_match('/(?:Vol\.?\s*)(\d+)/i', get_the_title(), $matches);
        $volume_num = isset($matches[1]) ? $matches[1] : '';
    }
    
    $manga_title = $manga_id ? get_the_title($manga_id) : 'Unknown Manga';
    $images = get_post_meta(get_the_ID(), 'image_links', true);
    
    if (!empty($images)) {
        $images = explode("\n", $images);
        $images = array_map('trim', $images);
        $images = array_filter($images);
    } else {
        $images = array();
    }
    
    // Get all chapters for this manga
    $all_chapters = get_posts(array(
        'post_type' => 'chapter',
        'posts_per_page' => -1,
        'meta_key' => 'connected_manga_id',
        'meta_value' => $manga_id,
        'post_status' => 'publish',
        'orderby' => 'meta_value_num',
        'meta_key' => 'chapter_number',
        'order' => 'DESC'
    ));
    
    // If the above query fails, try alternative query
    if (empty($all_chapters) && $manga_id) {
        $all_chapters = get_posts(array(
            'post_type' => 'chapter',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'connected_manga_id',
                    'value' => $manga_id,
                    'compare' => '='
                )
            ),
            'post_status' => 'publish'
        ));
        
        // Sort by volume and chapter number manually
        usort($all_chapters, function($a, $b) {
            $vol_a = get_post_meta($a->ID, 'volume_number', true) ?: 0;
            $vol_b = get_post_meta($b->ID, 'volume_number', true) ?: 0;
            $chap_a = get_post_meta($a->ID, 'chapter_number', true) ?: 0;
            $chap_b = get_post_meta($b->ID, 'chapter_number', true) ?: 0;
            
            if ($vol_a != $vol_b) {
                return $vol_b - $vol_a;
            }
            return $chap_b - $chap_a;
        });
    }
    
    $current_index = -1;
    foreach ($all_chapters as $index => $chapter) {
        if ($chapter->ID == get_the_ID()) {
            $current_index = $index;
            break;
        }
    }
    
    $prev_chapter = ($current_index > 0 && isset($all_chapters[$current_index - 1])) ? $all_chapters[$current_index - 1] : null;
    $next_chapter = ($current_index < count($all_chapters) - 1 && isset($all_chapters[$current_index + 1])) ? $all_chapters[$current_index + 1] : null;
    $manga_url = $manga_id ? get_permalink($manga_id) : '#';
    $total_pages = count($images);
    
    // Get formatted display text for current chapter
    $display_chapter_text = $volume_num ? "Vol. {$volume_num} Ch. {$chapter_num}" : "Ch. {$chapter_num}";
?>

<div class="manga-reader-container" id="mangaReaderContainer" data-current-bg="dark">
    <!-- Top Navigation Bar -->
    <div class="reader-top-bar" id="readerTopBar">
        <div class="reader-top-bar-left">
            <a href="<?php echo esc_url($manga_url); ?>" class="reader-nav-btn" id="backToMangaBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                <span>Back</span>
            </a>
            <div class="chapter-info-compact">
                <span class="compact-title"><?php echo esc_html($manga_title); ?></span>
                <span class="compact-chapter"><?php echo esc_html($display_chapter_text); ?></span>
            </div>
        </div>
        
        <div class="reader-top-bar-center">
            <div class="chapter-nav">
                <?php if ($prev_chapter): ?>
                    <a href="<?php echo get_permalink($prev_chapter->ID); ?>" class="reader-nav-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6"/>
                        </svg>
                        Prev
                    </a>
                <?php else: ?>
                    <span class="reader-nav-btn disabled" style="opacity:0.5; cursor:not-allowed;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6"/>
                        </svg>
                        Prev
                    </span>
                <?php endif; ?>
                
                <div class="chapter-selector-wrapper">
                    <select id="chapterSelector" class="chapter-selector">
                        <option value="">-- Select Chapter --</option>
                        <?php if (!empty($all_chapters)): ?>
                            <?php 
                            $chapters_for_dropdown = array_reverse($all_chapters);
                            foreach ($chapters_for_dropdown as $chapter): 
                                $chap_num = get_post_meta($chapter->ID, 'chapter_number', true);
                                $vol_num = get_post_meta($chapter->ID, 'volume_number', true);
                                if (empty($chap_num)) {
                                    preg_match('/(?:Ch\.?\s*)(\d+(?:\.\d+)?)/i', $chapter->post_title, $matches);
                                    $chap_num = isset($matches[1]) ? $matches[1] : '?';
                                }
                                if (empty($vol_num)) {
                                    preg_match('/(?:Vol\.?\s*)(\d+)/i', $chapter->post_title, $matches);
                                    $vol_num = isset($matches[1]) ? $matches[1] : '';
                                }
                                $selected = ($chapter->ID == get_the_ID()) ? 'selected' : '';
                                $display_text = $vol_num ? "Vol. {$vol_num} Ch. {$chap_num}" : "Ch. {$chap_num}";
                            ?>
                                <option value="<?php echo get_permalink($chapter->ID); ?>" <?php echo $selected; ?>>
                                    <?php echo esc_html($display_text); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No chapters available</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <?php if ($next_chapter): ?>
                    <a href="<?php echo get_permalink($next_chapter->ID); ?>" class="reader-nav-btn">
                        Next
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="reader-nav-btn disabled" style="opacity:0.5; cursor:not-allowed;">
                        Next
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="reader-top-bar-right">
            <button class="reader-settings-btn" id="settingsToggleBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </button>
            <button class="reader-fullscreen-btn" id="fullscreenToggleBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Progress Bar -->
    <div class="reader-progress-bar">
        <div class="reader-progress-fill" id="readerProgressFill"></div>
    </div>
    
    <!-- Main Reader Area -->
    <div class="reader-main-content" id="readerMainContent">
        <div class="reader-viewer" id="readerViewer">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $index => $image): ?>
                    <div class="reader-page" data-page="<?php echo $index; ?>">
                        <img src="<?php echo esc_url($image); ?>" alt="Page <?php echo $index + 1; ?>" loading="lazy" data-page-index="<?php echo $index; ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-images-message">
                    <p>No images found for this chapter.</p>
                    <a href="<?php echo admin_url('post.php?post=' . get_the_ID() . '&action=edit'); ?>" class="btn-primary" style="display: inline-block; padding: 10px 20px; background: #0078d4; color: white; border-radius: 6px; text-decoration: none;">Edit Chapter</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Navigation Arrows -->
    <div class="nav-arrow nav-arrow-left" id="navArrowLeft">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6"/>
        </svg>
    </div>
    <div class="nav-arrow nav-arrow-right" id="navArrowRight">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 18l6-6-6-6"/>
        </svg>
    </div>
    
    <!-- Page Indicator -->
    <div class="page-indicator" id="pageIndicator">
        <span id="currentPageDisplay">1</span> / <span id="totalPagesDisplay"><?php echo $total_pages; ?></span>
    </div>
    
    <!-- Settings Panel -->
    <div class="reader-settings-panel" id="readerSettingsPanel">
        <div class="settings-panel-header">
            <h3>Reader Settings</h3>
            <button class="close-settings-btn" id="closeSettingsPanelBtn">×</button>
        </div>
        <div class="settings-panel-content">
            <div class="settings-section">
                <label>Reading Mode</label>
                <div class="settings-buttons-group">
                    <button class="settings-btn active" data-setting="mode" data-value="paged">
                        <span class="btn-icon">📄</span> Paged
                    </button>
                    <button class="settings-btn" data-setting="mode" data-value="longstrip">
                        <span class="btn-icon">📋</span> Long Strip
                    </button>
                </div>
            </div>
            
            <div class="settings-section">
                <label>Image Fit</label>
                <div class="settings-buttons-group">
                    <button class="settings-btn active" data-setting="fit" data-value="contain">
                        <span class="btn-icon">🖼️</span> Contain
                    </button>
                    <button class="settings-btn" data-setting="fit" data-value="cover">
                        <span class="btn-icon">📷</span> Cover
                    </button>
                    <button class="settings-btn" data-setting="fit" data-value="original">
                        <span class="btn-icon">🔍</span> Original
                    </button>
                </div>
            </div>
            
            <div class="settings-section">
    <label>Scale</label>
    <div class="settings-buttons-group">
        <button class="settings-btn" data-setting="scale" data-value="100">
            <span class="btn-icon">📏</span> 100%
        </button>
        <button class="settings-btn active" data-setting="scale" data-value="auto">
            <span class="btn-icon">🔄</span> Auto
        </button>
        <button class="settings-btn" data-setting="scale" data-value="width">
            <span class="btn-icon">📐</span> Fit to Width
        </button>
    </div>
</div>
            
            <div class="settings-section">
                <label>Background</label>
                <div class="settings-buttons-group">
                    <button class="settings-btn active" data-setting="bg" data-value="dark">
                        <span class="btn-icon">🌙</span> Dark
                    </button>
                    <button class="settings-btn" data-setting="bg" data-value="light">
                        <span class="btn-icon">☀️</span> Light
                    </button>
                    <button class="settings-btn" data-setting="bg" data-value="sepia">
                        <span class="btn-icon">📖</span> Sepia
                    </button>
                </div>
            </div>
            
            <div class="settings-section">
                <label>Navigation</label>
                <div class="settings-buttons-group">
                    <button class="settings-btn active" data-setting="nav" data-value="click">
                        <span class="btn-icon">🖱️</span> Click to Advance
                    </button>
                    <button class="settings-btn" data-setting="nav" data-value="buttons">
                        <span class="btn-icon">⬅️➡️</span> Buttons Only
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Zoom Controls -->
    <div class="reader-zoom-controls" id="readerZoomControls">
        <button class="zoom-btn" id="zoomOutBtn" title="Zoom Out">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
        </button>
        <span class="zoom-level" id="zoomLevelDisplay">100%</span>
        <button class="zoom-btn" id="zoomInBtn" title="Zoom In">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                <line x1="11" y1="8" x2="11" y2="14"/>
                <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
        </button>
        <button class="zoom-btn" id="resetZoomBtn" title="Reset Zoom">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        </button>
    </div>
</div>

<style>
/* ============================================
   READER STYLES - FULL BACKGROUND MODE SUPPORT
   ============================================ */

/* Base reader container styles */
.manga-reader-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: background-color 0.3s ease;
}

/* ============================================
   DARK MODE (DEFAULT)
   ============================================ */
.manga-reader-container[data-current-bg="dark"] {
    background: #0a0a0f;
}

.manga-reader-container[data-current-bg="dark"] .reader-top-bar {
    background: rgba(16, 16, 20, 0.95);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.manga-reader-container[data-current-bg="dark"] .reader-nav-btn,
.manga-reader-container[data-current-bg="dark"] .reader-settings-btn,
.manga-reader-container[data-current-bg="dark"] .reader-fullscreen-btn,
.manga-reader-container[data-current-bg="dark"] .chapter-selector {
    background: rgba(255, 255, 255, 0.08);
    color: white;
}

.manga-reader-container[data-current-bg="dark"] .reader-nav-btn:hover,
.manga-reader-container[data-current-bg="dark"] .reader-settings-btn:hover,
.manga-reader-container[data-current-bg="dark"] .reader-fullscreen-btn:hover {
    background: rgba(255, 255, 255, 0.15);
}

.manga-reader-container[data-current-bg="dark"] .compact-title,
.manga-reader-container[data-current-bg="dark"] .chapter-info-compact span {
    color: white;
}

.manga-reader-container[data-current-bg="dark"] .compact-chapter {
    color: rgba(255, 255, 255, 0.6);
}

.manga-reader-container[data-current-bg="dark"] .chapter-selector option {
    background: #1a1a2e;
    color: white;
}

.manga-reader-container[data-current-bg="dark"] .reader-settings-panel {
    background: rgba(20, 20, 30, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.manga-reader-container[data-current-bg="dark"] .settings-panel-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.manga-reader-container[data-current-bg="dark"] .settings-panel-header h3 {
    color: white;
}

.manga-reader-container[data-current-bg="dark"] .settings-section label {
    color: rgba(255, 255, 255, 0.7);
}

.manga-reader-container[data-current-bg="dark"] .settings-btn {
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.9);
}

.manga-reader-container[data-current-bg="dark"] .settings-btn:hover {
    background: rgba(255, 255, 255, 0.15);
}

.manga-reader-container[data-current-bg="dark"] .no-images-message {
    color: white;
}

.manga-reader-container[data-current-bg="dark"] .page-indicator {
    background: rgba(0, 0, 0, 0.7);
    color: white;
}

.manga-reader-container[data-current-bg="dark"] .reader-zoom-controls {
    background: rgba(0, 0, 0, 0.7);
}

.manga-reader-container[data-current-bg="dark"] .zoom-btn {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.manga-reader-container[data-current-bg="dark"] .zoom-level {
    color: white;
}

/* ============================================
   LIGHT MODE
   ============================================ */
.manga-reader-container[data-current-bg="light"] {
    background: #f5f5f5;
}

.manga-reader-container[data-current-bg="light"] .reader-top-bar {
    background: rgba(255, 255, 255, 0.95);
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="light"] .reader-nav-btn,
.manga-reader-container[data-current-bg="light"] .reader-settings-btn,
.manga-reader-container[data-current-bg="light"] .reader-fullscreen-btn,
.manga-reader-container[data-current-bg="light"] .chapter-selector {
    background: rgba(0, 0, 0, 0.05);
    color: #333;
}

.manga-reader-container[data-current-bg="light"] .reader-nav-btn:hover,
.manga-reader-container[data-current-bg="light"] .reader-settings-btn:hover,
.manga-reader-container[data-current-bg="light"] .reader-fullscreen-btn:hover {
    background: rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="light"] .compact-title,
.manga-reader-container[data-current-bg="light"] .chapter-info-compact span {
    color: #333;
}

.manga-reader-container[data-current-bg="light"] .compact-chapter {
    color: #666;
}

.manga-reader-container[data-current-bg="light"] .chapter-selector option {
    background: white;
    color: #333;
}

.manga-reader-container[data-current-bg="light"] .reader-settings-panel {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="light"] .settings-panel-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="light"] .settings-panel-header h3 {
    color: #333;
}

.manga-reader-container[data-current-bg="light"] .settings-section label {
    color: #666;
}

.manga-reader-container[data-current-bg="light"] .settings-btn {
    background: rgba(0, 0, 0, 0.05);
    color: #333;
}

.manga-reader-container[data-current-bg="light"] .settings-btn:hover {
    background: rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="light"] .no-images-message {
    color: #333;
}

.manga-reader-container[data-current-bg="light"] .no-images-message a {
    background: #0078d4;
    color: white;
}

.manga-reader-container[data-current-bg="light"] .reader-progress-bar {
    background: rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="light"] .reader-progress-fill {
    background: #0078d4;
}

.manga-reader-container[data-current-bg="light"] .nav-arrow {
    background: rgba(0, 0, 0, 0.5);
    color: white;
}

.manga-reader-container[data-current-bg="light"] .nav-arrow:hover {
    background: #0078d4;
}

.manga-reader-container[data-current-bg="light"] .page-indicator {
    background: rgba(0, 0, 0, 0.7);
    color: white;
}

.manga-reader-container[data-current-bg="light"] .reader-zoom-controls {
    background: rgba(0, 0, 0, 0.7);
}

.manga-reader-container[data-current-bg="light"] .zoom-btn {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.manga-reader-container[data-current-bg="light"] .zoom-btn:hover {
    background: #0078d4;
}

.manga-reader-container[data-current-bg="light"] .zoom-level {
    color: white;
}

/* ============================================
   SEPIA MODE
   ============================================ */
.manga-reader-container[data-current-bg="sepia"] {
    background: #f4ecd8;
}

.manga-reader-container[data-current-bg="sepia"] .reader-top-bar {
    background: rgba(244, 236, 216, 0.95);
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="sepia"] .reader-nav-btn,
.manga-reader-container[data-current-bg="sepia"] .reader-settings-btn,
.manga-reader-container[data-current-bg="sepia"] .reader-fullscreen-btn,
.manga-reader-container[data-current-bg="sepia"] .chapter-selector {
    background: rgba(0, 0, 0, 0.08);
    color: #5b4636;
}

.manga-reader-container[data-current-bg="sepia"] .reader-nav-btn:hover,
.manga-reader-container[data-current-bg="sepia"] .reader-settings-btn:hover,
.manga-reader-container[data-current-bg="sepia"] .reader-fullscreen-btn:hover {
    background: rgba(0, 0, 0, 0.12);
}

.manga-reader-container[data-current-bg="sepia"] .compact-title,
.manga-reader-container[data-current-bg="sepia"] .chapter-info-compact span {
    color: #5b4636;
}

.manga-reader-container[data-current-bg="sepia"] .compact-chapter {
    color: #8b7355;
}

.manga-reader-container[data-current-bg="sepia"] .chapter-selector option {
    background: #f4ecd8;
    color: #5b4636;
}

.manga-reader-container[data-current-bg="sepia"] .reader-settings-panel {
    background: rgba(244, 236, 216, 0.95);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="sepia"] .settings-panel-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="sepia"] .settings-panel-header h3 {
    color: #5b4636;
}

.manga-reader-container[data-current-bg="sepia"] .settings-section label {
    color: #8b7355;
}

.manga-reader-container[data-current-bg="sepia"] .settings-btn {
    background: rgba(0, 0, 0, 0.08);
    color: #5b4636;
}

.manga-reader-container[data-current-bg="sepia"] .settings-btn:hover {
    background: rgba(0, 0, 0, 0.12);
}

.manga-reader-container[data-current-bg="sepia"] .no-images-message {
    color: #5b4636;
}

.manga-reader-container[data-current-bg="sepia"] .reader-progress-bar {
    background: rgba(0, 0, 0, 0.1);
}

.manga-reader-container[data-current-bg="sepia"] .reader-progress-fill {
    background: #0078d4;
}

.manga-reader-container[data-current-bg="sepia"] .nav-arrow {
    background: rgba(0, 0, 0, 0.5);
    color: white;
}

.manga-reader-container[data-current-bg="sepia"] .nav-arrow:hover {
    background: #0078d4;
}

.manga-reader-container[data-current-bg="sepia"] .page-indicator {
    background: rgba(0, 0, 0, 0.7);
    color: white;
}

.manga-reader-container[data-current-bg="sepia"] .reader-zoom-controls {
    background: rgba(0, 0, 0, 0.7);
}

.manga-reader-container[data-current-bg="sepia"] .zoom-btn {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.manga-reader-container[data-current-bg="sepia"] .zoom-btn:hover {
    background: #0078d4;
}

.manga-reader-container[data-current-bg="sepia"] .zoom-level {
    color: white;
}

/* ============================================
   SETTINGS BUTTON STYLES (All Modes)
   ============================================ */
.settings-btn {
    position: relative;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.settings-btn .btn-icon {
    font-size: 14px;
}

.settings-btn.active {
    background: #0078d4 !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(0, 120, 212, 0.3);
    transform: scale(1.02);
}

.settings-btn.active::after {
    content: '✓';
    margin-left: 4px;
    font-size: 11px;
    font-weight: bold;
}

.settings-btn:hover {
    transform: translateY(-1px);
}

/* ============================================
   SHARED READER STYLES
   ============================================ */
.reader-top-bar {
    padding: 12px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 101;
    transition: all 0.3s ease;
}

.reader-nav-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
}

.reader-settings-btn,
.reader-fullscreen-btn {
    border: none;
    border-radius: 8px;
    padding: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.reader-progress-bar {
    position: absolute;
    top: 61px;
    left: 0;
    right: 0;
    height: 2px;
    z-index: 100;
}

.reader-progress-fill {
    width: 0%;
    height: 100%;
    transition: width 0.3s;
}

.reader-main-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 20px;
}

.reader-viewer {
    max-width: 1000px;
    margin: 0 auto;
}

.reader-page {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
    cursor: pointer;
}

.reader-page img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
    border-radius: 4px;
    transition: transform 0.2s;
}

.nav-arrow {
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    backdrop-filter: blur(10px);
    border-radius: 50%;
    padding: 12px;
    cursor: pointer;
    transition: all 0.2s;
    z-index: 100;
    opacity: 0;
}

.manga-reader-container:hover .nav-arrow {
    opacity: 1;
}

.nav-arrow-left {
    left: 20px;
}

.nav-arrow-right {
    right: 20px;
}

.page-indicator {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    backdrop-filter: blur(10px);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    z-index: 100;
    pointer-events: none;
}

.reader-settings-panel {
    position: fixed;
    top: 50%;
    right: -300px;
    transform: translateY(-50%);
    width: 300px;
    backdrop-filter: blur(20px);
    border-radius: 12px;
    z-index: 1000;
    transition: right 0.3s;
    overflow: hidden;
}

.reader-settings-panel.open {
    right: 20px;
}

.settings-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
}

.settings-panel-header h3 {
    margin: 0;
    font-size: 16px;
}

.close-settings-btn {
    background: none;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.2s;
}

.close-settings-btn:hover {
    background: rgba(0, 0, 0, 0.1);
}

.settings-panel-content {
    padding: 20px;
}

.settings-section {
    margin-bottom: 20px;
}

.settings-section label {
    display: block;
    font-size: 11px;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.settings-section label::before {
    content: '⚙️';
    margin-right: 6px;
    font-size: 11px;
}

.settings-buttons-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.reader-zoom-controls {
    position: fixed;
    bottom: 20px;
    right: 20px;
    backdrop-filter: blur(10px);
    padding: 8px;
    border-radius: 40px;
    display: flex;
    gap: 8px;
    z-index: 100;
}

.zoom-btn {
    border: none;
    padding: 8px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.zoom-btn:hover {
    transform: scale(1.05);
}

.zoom-level {
    font-size: 12px;
    padding: 0 8px;
    display: flex;
    align-items: center;
}

.no-images-message {
    text-align: center;
    padding: 60px;
}

/* Responsive */
@media (max-width: 768px) {
    .reader-top-bar {
        padding: 10px 16px;
    }
    
    .reader-nav-btn span {
        display: none;
    }
    
    .chapter-info-compact {
        display: none;
    }
    
    .chapter-selector {
        min-width: 100px;
        font-size: 11px;
    }
    
    .nav-arrow {
        opacity: 1;
        padding: 8px;
    }
    
    .reader-settings-panel.open {
        right: 10px;
        width: 280px;
    }
    
    .reader-zoom-controls {
        bottom: 70px;
    }
    
    .settings-btn {
        padding: 6px 10px;
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .settings-buttons-group {
        gap: 6px;
    }
    
    .settings-btn {
        padding: 5px 8px;
        font-size: 10px;
    }
    
    .settings-btn .btn-icon {
        font-size: 11px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // DOM Elements
    const container = document.getElementById('mangaReaderContainer');
    const pages = document.querySelectorAll('.reader-page');
    const totalPages = pages.length;
    const progressFill = document.getElementById('readerProgressFill');
    const currentPageDisplay = document.getElementById('currentPageDisplay');
    const totalPagesDisplay = document.getElementById('totalPagesDisplay');
    const prevArrow = document.getElementById('navArrowLeft');
    const nextArrow = document.getElementById('navArrowRight');
    const settingsPanel = document.getElementById('readerSettingsPanel');
    const settingsToggle = document.getElementById('settingsToggleBtn');
    const closeSettings = document.getElementById('closeSettingsPanelBtn');
    const fullscreenBtn = document.getElementById('fullscreenToggleBtn');
    const chapterSelector = document.getElementById('chapterSelector');
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    const resetZoomBtn = document.getElementById('resetZoomBtn');
    const zoomLevelDisplay = document.getElementById('zoomLevelDisplay');
    const scrollContainer = document.getElementById('readerMainContent');
    
    // State
    let currentPage = 0;
    let currentZoom = 1;
    let readingMode = 'paged';
    let imageFit = 'contain';
    let scaleMode = 'auto';
    let navMode = 'click';
    let currentBgMode = 'dark';
    let scrollTimeout = null;
    
    // Update total pages display
    if (totalPagesDisplay && totalPages > 0) {
        totalPagesDisplay.textContent = totalPages;
    }
    
    // Apply background mode - FULLY FIXED
    function applyBackgroundMode(mode) {
        currentBgMode = mode;
        if (container) {
            container.setAttribute('data-current-bg', mode);
        }
        
        // Also update body class for consistent theming if needed
        if (mode === 'light') {
            document.body.classList.remove('dark-mode', 'sepia-mode');
            document.body.classList.add('light-mode');
        } else if (mode === 'sepia') {
            document.body.classList.remove('dark-mode', 'light-mode');
            document.body.classList.add('sepia-mode');
        } else {
            document.body.classList.remove('light-mode', 'sepia-mode');
            document.body.classList.add('dark-mode');
        }
        
        localStorage.setItem('readerBgMode', mode);
    }
    
    // Apply image fit
    function applyImageFit() {
        pages.forEach(page => {
            const img = page.querySelector('img');
            if (img) {
                if (imageFit === 'contain') {
                    img.style.objectFit = 'contain';
                    img.style.width = '100%';
                    img.style.height = 'auto';
                    img.style.maxWidth = '100%';
                } else if (imageFit === 'cover') {
                    img.style.objectFit = 'cover';
                    img.style.width = '100%';
                    img.style.height = '100%';
                } else if (imageFit === 'original') {
                    img.style.objectFit = 'initial';
                    img.style.width = 'auto';
                    img.style.height = 'auto';
                }
            }
        });
    }
    
    // Apply scale/zoom
    function applyScale() {
        if (scaleMode === 'width') {
            pages.forEach(page => {
                const img = page.querySelector('img');
                if (img) {
                    img.style.width = '100%';
                    img.style.height = 'auto';
                    img.style.transform = 'none';
                }
            });
            if (zoomLevelDisplay) zoomLevelDisplay.textContent = 'Fit Width';
            return;
        }
        
        let scaleValue = (scaleMode === '100') ? 1 : currentZoom;
        
        pages.forEach(page => {
            const img = page.querySelector('img');
            if (img) {
                img.style.transform = `scale(${scaleValue})`;
                img.style.transformOrigin = 'center';
            }
        });
        
        if (zoomLevelDisplay && scaleMode !== 'width') {
            zoomLevelDisplay.textContent = Math.round(scaleValue * 100) + '%';
        }
    }
    
    // Update page display based on mode
    function updatePageDisplay() {
        if (readingMode === 'longstrip') {
            pages.forEach(page => {
                page.style.display = 'flex';
            });
            updateProgress();
        } else {
            pages.forEach((page, index) => {
                page.style.display = index === currentPage ? 'flex' : 'none';
            });
            updateProgress();
            updatePageIndicator();
        }
    }
    
    // Update progress bar
    function updateProgress() {
        if (readingMode === 'longstrip' && scrollContainer) {
            const scrollTop = scrollContainer.scrollTop;
            const scrollHeight = scrollContainer.scrollHeight - scrollContainer.clientHeight;
            if (scrollHeight > 0) {
                const progress = (scrollTop / scrollHeight) * 100;
                if (progressFill) progressFill.style.width = progress + '%';
            }
        } else if (totalPages > 0) {
            const progress = ((currentPage + 1) / totalPages) * 100;
            if (progressFill) progressFill.style.width = progress + '%';
        }
    }
    
    // Update page indicator
    function updatePageIndicator() {
        if (currentPageDisplay && totalPages > 0) {
            currentPageDisplay.textContent = currentPage + 1;
        }
    }
    
    // Navigate to next page (Paged mode only)
    function nextPage() {
        if (readingMode === 'longstrip') return;
        
        if (currentPage < totalPages - 1) {
            currentPage++;
            updatePageDisplay();
            scrollToPage(currentPage);
        }
    }
    
    // Navigate to previous page (Paged mode only)
    function prevPage() {
        if (readingMode === 'longstrip') return;
        
        if (currentPage > 0) {
            currentPage--;
            updatePageDisplay();
            scrollToPage(currentPage);
        }
    }
    
    // Scroll to specific page
    function scrollToPage(pageIndex) {
        const page = pages[pageIndex];
        if (page && scrollContainer) {
            const pageTop = page.offsetTop - 100;
            scrollContainer.scrollTo({ top: pageTop, behavior: 'smooth' });
        }
    }
    
    // Track current page on scroll (Paged mode only)
    function trackCurrentPage() {
        if (readingMode === 'longstrip') return;
        
        let closestPage = 0;
        let closestDistance = Infinity;
        
        pages.forEach((page, index) => {
            const rect = page.getBoundingClientRect();
            const containerRect = scrollContainer.getBoundingClientRect();
            const distance = Math.abs(rect.top - containerRect.top);
            if (distance < closestDistance) {
                closestDistance = distance;
                closestPage = index;
            }
        });
        
        if (closestPage !== currentPage) {
            currentPage = closestPage;
            updateProgress();
            updatePageIndicator();
        }
    }
    
    // LONG STRIP MODE: Navigate to next image when clicking
    function longStripNextImage(clickedPage) {
        if (readingMode !== 'longstrip') return;
        
        // Get all pages in order
        const allPages = Array.from(pages);
        const currentIndex = allPages.indexOf(clickedPage);
        
        // Check if there's a next page
        if (currentIndex < allPages.length - 1) {
            const nextPage = allPages[currentIndex + 1];
            // Smooth scroll to the next page
            nextPage.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    // Set reading mode
    function setReadingMode(mode) {
        readingMode = mode;
        
        if (mode === 'longstrip') {
            // Show all pages
            pages.forEach(page => {
                page.style.display = 'flex';
            });
            
            // Update scroll tracking for progress
            if (scrollContainer) {
                scrollContainer.removeEventListener('scroll', trackCurrentPage);
                scrollContainer.addEventListener('scroll', updateProgress);
            }
        } else {
            // Paged mode - show only current page
            updatePageDisplay();
            
            // Update scroll tracking
            if (scrollContainer) {
                scrollContainer.removeEventListener('scroll', updateProgress);
                scrollContainer.addEventListener('scroll', trackCurrentPage);
            }
        }
        
        localStorage.setItem('readerReadingMode', mode);
    }
    
    // Set image fit
    function setImageFitMode(fit) {
        imageFit = fit;
        applyImageFit();
        localStorage.setItem('readerImageFit', fit);
    }
    
    // Set scale mode
    function setScaleMode(mode) {
        scaleMode = mode;
        if (mode === 'auto') {
            currentZoom = 1;
        }
        if (mode === '100') {
            currentZoom = 1;
        }
        applyScale();
        localStorage.setItem('readerScaleMode', mode);
    }
    
    // Zoom functions (only affect when scale mode is auto)
    function zoomIn() {
        if (scaleMode !== 'auto') {
            // Switch to auto mode first
            setScaleMode('auto');
            document.querySelectorAll('[data-setting="scale"]').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.value === 'auto') {
                    btn.classList.add('active');
                }
            });
        }
        if (currentZoom < 2.5) {
            currentZoom += 0.25;
            applyScale();
        }
    }
    
    function zoomOut() {
        if (scaleMode !== 'auto') {
            setScaleMode('auto');
            document.querySelectorAll('[data-setting="scale"]').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.value === 'auto') {
                    btn.classList.add('active');
                }
            });
        }
        if (currentZoom > 0.5) {
            currentZoom -= 0.25;
            applyScale();
        }
    }
    
    function resetZoom() {
        currentZoom = 1;
        setScaleMode('auto');
        applyScale();
        document.querySelectorAll('[data-setting="scale"]').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.value === 'auto') {
                btn.classList.add('active');
            }
        });
    }
    
    // Event Listeners
    if (prevArrow) prevArrow.addEventListener('click', prevPage);
    if (nextArrow) nextArrow.addEventListener('click', nextPage);
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (readingMode !== 'longstrip') {
            if (e.key === 'ArrowLeft') {
                prevPage();
                e.preventDefault();
            } else if (e.key === 'ArrowRight') {
                nextPage();
                e.preventDefault();
            }
        } else {
            // For long strip mode, arrow keys scroll
            if (e.key === 'ArrowDown' && scrollContainer) {
                scrollContainer.scrollBy({ top: window.innerHeight * 0.8, behavior: 'smooth' });
                e.preventDefault();
            } else if (e.key === 'ArrowUp' && scrollContainer) {
                scrollContainer.scrollBy({ top: -window.innerHeight * 0.8, behavior: 'smooth' });
                e.preventDefault();
            }
        }
        
        if (e.key === 'f' || e.key === 'F') {
            if (fullscreenBtn) fullscreenBtn.click();
            e.preventDefault();
        } else if (e.key === 's' || e.key === 'S') {
            if (settingsToggle) settingsToggle.click();
            e.preventDefault();
        } else if (e.key === 'Escape') {
            if (settingsPanel && settingsPanel.classList.contains('open')) {
                settingsPanel.classList.remove('open');
            }
        }
    });
    
    // Settings panel
    if (settingsToggle) {
        settingsToggle.addEventListener('click', function() {
            settingsPanel.classList.toggle('open');
        });
    }
    
    if (closeSettings) {
        closeSettings.addEventListener('click', function() {
            settingsPanel.classList.remove('open');
        });
    }
    
    // Click outside to close settings
    document.addEventListener('click', function(e) {
        if (settingsPanel && settingsPanel.classList.contains('open')) {
            if (!settingsPanel.contains(e.target) && !settingsToggle.contains(e.target)) {
                settingsPanel.classList.remove('open');
            }
        }
    });
    
    // Fullscreen
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            const containerElem = document.querySelector('.manga-reader-container');
            if (!document.fullscreenElement) {
                containerElem.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        });
    }
    
    // Chapter selector
    if (chapterSelector && chapterSelector.options.length > 1) {
        chapterSelector.addEventListener('change', function() {
            if (this.value) {
                window.location.href = this.value;
            }
        });
    }
    
    // Zoom controls
    if (zoomInBtn) zoomInBtn.addEventListener('click', zoomIn);
    if (zoomOutBtn) zoomOutBtn.addEventListener('click', zoomOut);
    if (resetZoomBtn) resetZoomBtn.addEventListener('click', resetZoom);
    
    // Image click handling - WORKS FOR BOTH MODES
    pages.forEach(page => {
        page.addEventListener('click', function(e) {
            // Only advance in click navigation mode
            if (navMode === 'click') {
                if (readingMode === 'longstrip') {
                    // Long strip mode: scroll to next image
                    longStripNextImage(this);
                } else {
                    // Paged mode: go to next page
                    nextPage();
                }
            }
        });
    });
    
    // Scroll tracking
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', function() {
            if (readingMode === 'longstrip') {
                updateProgress();
            } else {
                // Debounce for better performance
                if (scrollTimeout) clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(trackCurrentPage, 100);
            }
        });
    }
    
    // Settings button handlers
    document.querySelectorAll('[data-setting]').forEach(btn => {
        btn.addEventListener('click', function() {
            const setting = this.dataset.setting;
            const value = this.dataset.value;
            
            // Update active state
            document.querySelectorAll(`[data-setting="${setting}"]`).forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            // Apply setting
            switch(setting) {
                case 'mode':
                    setReadingMode(value);
                    break;
                case 'fit':
                    setImageFitMode(value);
                    break;
                case 'scale':
                    setScaleMode(value);
                    if (value === 'auto') {
                        currentZoom = 1;
                        applyScale();
                    } else if (value === '100') {
                        currentZoom = 1;
                        applyScale();
                    } else if (value === 'width') {
                        applyScale();
                    }
                    break;
                case 'bg':
                    applyBackgroundMode(value);
                    break;
                case 'nav':
                    navMode = value;
                    localStorage.setItem('readerNavMode', value);
                    break;
            }
        });
    });
    
    // Load saved settings
    const savedReadingMode = localStorage.getItem('readerReadingMode');
    const savedImageFit = localStorage.getItem('readerImageFit');
    const savedScaleMode = localStorage.getItem('readerScaleMode');
    const savedBgMode = localStorage.getItem('readerBgMode');
    const savedNavMode = localStorage.getItem('readerNavMode');
    
    if (savedReadingMode && savedReadingMode !== 'paged') {
        setReadingMode(savedReadingMode);
        document.querySelector(`[data-setting="mode"][data-value="${savedReadingMode}"]`)?.classList.add('active');
        document.querySelector(`[data-setting="mode"][data-value="paged"]`)?.classList.remove('active');
    }
    
    if (savedImageFit && savedImageFit !== 'contain') {
        setImageFitMode(savedImageFit);
        document.querySelector(`[data-setting="fit"][data-value="${savedImageFit}"]`)?.classList.add('active');
        document.querySelector(`[data-setting="fit"][data-value="contain"]`)?.classList.remove('active');
    } else {
        applyImageFit();
    }
    
    if (savedScaleMode && savedScaleMode !== 'auto') {
        setScaleMode(savedScaleMode);
        document.querySelector(`[data-setting="scale"][data-value="${savedScaleMode}"]`)?.classList.add('active');
        document.querySelector(`[data-setting="scale"][data-value="auto"]`)?.classList.remove('active');
    } else {
        applyScale();
    }
    
    // Apply saved background mode or default to dark
    if (savedBgMode && savedBgMode !== 'dark') {
        applyBackgroundMode(savedBgMode);
        document.querySelector(`[data-setting="bg"][data-value="${savedBgMode}"]`)?.classList.add('active');
        document.querySelector(`[data-setting="bg"][data-value="dark"]`)?.classList.remove('active');
    } else {
        applyBackgroundMode('dark');
    }
    
    if (savedNavMode) {
        navMode = savedNavMode;
        document.querySelector(`[data-setting="nav"][data-value="${savedNavMode}"]`)?.classList.add('active');
        document.querySelector(`[data-setting="nav"][data-value="click"]`)?.classList.remove('active');
    }
    
    // Initial setup
    if (pages.length > 0) {
        updatePageDisplay();
    }
    
    // Hide body overflow
    document.body.style.overflow = 'hidden';
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    document.body.style.overflow = '';
});
</script>

<?php endwhile; ?>

<?php get_footer(); ?>
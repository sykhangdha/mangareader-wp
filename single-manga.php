<?php get_header(); ?>

<?php while (have_posts()) : the_post(); 
    $current_manga_id = get_the_ID();
    ?>
    <div class="single-manga">
        <div class="manga-header">
            <div class="manga-cover-wrapper">
                <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('large', array('class' => 'manga-cover-large')); ?>
                <?php else: ?>
                    <img src="https://via.placeholder.com/300x420?text=No+Cover" class="manga-cover-large">
                <?php endif; ?>
            </div>
            
            <div class="manga-details">
                <h1><?php the_title(); ?></h1>
                
                <?php
                $author = get_post_meta(get_the_ID(), '_manga_author', true);
                $artist = get_post_meta(get_the_ID(), '_manga_artist', true);
                $year = get_post_meta(get_the_ID(), '_manga_year', true);
                $genres = wp_get_post_terms(get_the_ID(), 'genre');
                $status = wp_get_post_terms(get_the_ID(), 'manga_status');
                
                // Helper function to extract chapter number from post
                function get_chapter_display_info($chapter_id) {
                    $chapter_num = get_post_meta($chapter_id, 'chapter_number', true);
                    $volume_num = get_post_meta($chapter_id, 'volume_number', true);
                    $title = get_the_title($chapter_id);
                    
                    if (empty($chapter_num)) {
                        preg_match('/(?:Ch\.?\s*)(\d+(?:\.\d+)?)/i', $title, $matches);
                        $chapter_num = isset($matches[1]) ? $matches[1] : '?';
                    }
                    
                    if (empty($volume_num)) {
                        preg_match('/(?:Vol\.?\s*)(\d+)/i', $title, $matches);
                        $volume_num = isset($matches[1]) ? intval($matches[1]) : 0;
                    }
                    
                    return array(
                        'chapter' => floatval($chapter_num),
                        'volume' => intval($volume_num),
                        'display' => $volume_num ? "Vol. {$volume_num} Ch. {$chapter_num}" : "Ch. {$chapter_num}",
                        'sort_key' => ($volume_num * 1000) + floatval($chapter_num)
                    );
                }
                
                // Get all chapters with their info
                $all_chapters_raw = get_posts(array(
                    'post_type' => 'chapter',
                    'posts_per_page' => -1,
                    'meta_key' => 'connected_manga_id',
                    'meta_value' => $current_manga_id,
                    'post_status' => 'publish'
                ));
                
                $chapters_with_info = array();
                foreach ($all_chapters_raw as $chapter) {
                    $info = get_chapter_display_info($chapter->ID);
                    $chapters_with_info[] = array(
                        'id' => $chapter->ID,
                        'post' => $chapter,
                        'info' => $info,
                        'date' => $chapter->post_date
                    );
                }
                
                // Sort by sort_key (volume * 1000 + chapter) descending for latest detection
                usort($chapters_with_info, function($a, $b) {
                    return $b['info']['sort_key'] - $a['info']['sort_key'];
                });
                
                $total_chapters = count($chapters_with_info);
                $latest_chapter = !empty($chapters_with_info) ? $chapters_with_info[0] : null;
                $first_chapter = !empty($chapters_with_info) ? $chapters_with_info[count($chapters_with_info) - 1] : null;
                ?>
                
                <div class="manga-stats">
                    <div class="stat-box">
                        <span class="stat-value"><?php echo esc_html($total_chapters); ?></span>
                        <span class="stat-label">Chapters</span>
                    </div>
                    <?php if (!empty($status)): ?>
                    <div class="stat-box">
                        <span class="stat-value status-<?php echo strtolower($status[0]->name); ?>"><?php echo esc_html($status[0]->name); ?></span>
                        <span class="stat-label">Status</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($year): ?>
                    <div class="stat-box">
                        <span class="stat-value"><?php echo esc_html($year); ?></span>
                        <span class="stat-label">Year</span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="manga-meta">
                    <?php if ($author): ?>
                        <div class="meta-item">
                            <span class="meta-label">Author:</span>
                            <span><?php echo esc_html($author); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($artist): ?>
                        <div class="meta-item">
                            <span class="meta-label">Artist:</span>
                            <span><?php echo esc_html($artist); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($genres)): ?>
                    <div class="genres">
                        <?php foreach ($genres as $genre): ?>
                            <a href="<?php echo get_term_link($genre); ?>" class="genre-tag"><?php echo esc_html($genre->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="manga-description">
                    <?php the_content(); ?>
                </div>
                
                <div class="manga-actions">
                    <?php if ($first_chapter): ?>
                        <a href="<?php echo get_permalink($first_chapter['id']); ?>" class="btn btn-primary btn-large">
                            📖 Read First
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($latest_chapter && (!$first_chapter || $latest_chapter['id'] != $first_chapter['id'])): ?>
                        <a href="<?php echo get_permalink($latest_chapter['id']); ?>" class="btn btn-secondary btn-large">
                            📚 Latest (<?php echo esc_html($latest_chapter['info']['display']); ?>)
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="chapters-section">
            <div class="chapters-header">
                <h2>All Chapters</h2>
                <div class="chapters-controls">
                    <button class="chapter-sort-btn active" data-sort="desc">Newest First</button>
                    <button class="chapter-sort-btn" data-sort="asc">Oldest First</button>
                </div>
            </div>
            
            <div id="chapters-container">
                <?php if (!empty($chapters_with_info)): ?>
                    <div class="chapters-grid">
                        <?php 
                        // Display chapters in descending order by default (newest first)
                        foreach ($chapters_with_info as $index => $chapter_data): 
                            $chapter = $chapter_data['post'];
                            $info = $chapter_data['info'];
                            $chapter_date = get_the_date('M d, Y', $chapter->ID);
                            $is_latest = ($index === 0);
                        ?>
                            <div class="chapter-card" 
                                 data-chapter="<?php echo esc_attr($info['chapter']); ?>" 
                                 data-volume="<?php echo esc_attr($info['volume']); ?>"
                                 data-sort-key="<?php echo esc_attr($info['sort_key']); ?>"
                                 data-date="<?php echo esc_attr($chapter_data['date']); ?>">
                                <a href="<?php echo get_permalink($chapter->ID); ?>" class="chapter-card-link">
                                    <div class="chapter-card-number">
                                        <span class="chapter-icon">📖</span>
                                        <span class="chapter-title"><?php echo esc_html($info['display']); ?></span>
                                    </div>
                                    <div class="chapter-card-meta">
                                        <span class="chapter-date"><?php echo esc_html($chapter_date); ?></span>
                                        <?php if ($is_latest): ?>
                                            <span class="new-badge">Latest</span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-chapters-message">
                        <p>No chapters available for this manga yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<style>
/* Additional styles for single manga page */
.single-manga .chapters-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.single-manga .chapter-card {
    transition: all 0.2s ease;
}

.single-manga .chapter-card-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: var(--surface, rgba(255, 255, 255, 0.7));
    border-radius: 8px;
    transition: all 0.2s;
    border: 1px solid var(--card-border, rgba(0, 0, 0, 0.08));
    text-decoration: none;
    color: var(--text, #202020);
}

.single-manga .chapter-card-link:hover {
    background: var(--card-bg, rgba(255, 255, 255, 0.85));
    border-color: var(--primary, #0078d4);
    transform: translateX(4px);
}

.single-manga .chapter-card-number {
    display: flex;
    align-items: center;
    gap: 10px;
}

.single-manga .chapter-icon {
    font-size: 18px;
}

.single-manga .chapter-title {
    font-weight: 500;
    font-size: 14px;
}

.single-manga .chapter-card-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}

.single-manga .chapter-date {
    font-size: 12px;
    color: var(--text-light, #605e5c);
}

.single-manga .new-badge {
    background: var(--primary, #0078d4);
    color: white;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 500;
}

.single-manga .chapters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--card-border, rgba(0, 0, 0, 0.08));
    flex-wrap: wrap;
    gap: 15px;
}

.single-manga .chapters-header h2 {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
}

.single-manga .chapters-controls {
    display: flex;
    gap: 8px;
}

.single-manga .chapter-sort-btn {
    padding: 5px 14px;
    background: var(--surface, rgba(255, 255, 255, 0.5));
    border: 1px solid var(--card-border, rgba(0, 0, 0, 0.08));
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
    color: var(--text, #202020);
}

.single-manga .chapter-sort-btn:hover {
    background: var(--primary, #0078d4);
    border-color: var(--primary, #0078d4);
    color: white;
}

.single-manga .chapter-sort-btn.active {
    background: var(--primary, #0078d4);
    border-color: var(--primary, #0078d4);
    color: white;
}

.single-manga .no-chapters-message {
    text-align: center;
    padding: 40px;
    background: var(--surface, rgba(255, 255, 255, 0.5));
    border-radius: 12px;
    color: var(--text-light, #605e5c);
}

/* Dark mode overrides */
body.dark-mode .single-manga .chapter-card-link {
    background: rgba(32, 32, 32, 0.7);
    border-color: rgba(255, 255, 255, 0.08);
    color: white;
}

body.dark-mode .single-manga .chapter-card-link:hover {
    background: rgba(32, 32, 32, 0.85);
}

body.dark-mode .single-manga .chapter-date {
    color: rgba(255, 255, 255, 0.6);
}

body.dark-mode .single-manga .chapter-sort-btn {
    background: rgba(32, 32, 32, 0.7);
    border-color: rgba(255, 255, 255, 0.08);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .single-manga .chapters-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .single-manga .chapter-card-link {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .single-manga .chapter-card-meta {
        width: 100%;
        justify-content: space-between;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    var chapters = [];
    var currentSort = 'desc';
    
    // Collect all chapters with their data
    $('.chapter-card').each(function() {
        var chapterNum = parseFloat($(this).data('chapter'));
        var volumeNum = parseInt($(this).data('volume')) || 0;
        var sortKey = parseFloat($(this).data('sort-key'));
        var date = $(this).data('date');
        
        chapters.push({
            element: $(this),
            number: chapterNum,
            volume: volumeNum,
            sortKey: sortKey,
            date: date
        });
    });
    
    // Sort chapters function
    function sortChapters(order) {
        var sortedChapters = [...chapters];
        
        if (order === 'desc') {
            // Sort by sortKey (volume*1000 + chapter) descending
            sortedChapters.sort((a, b) => b.sortKey - a.sortKey);
        } else {
            // Sort by sortKey ascending
            sortedChapters.sort((a, b) => a.sortKey - b.sortKey);
        }
        
        var container = $('.chapters-grid');
        container.empty();
        
        // Re-add sorted chapters to container
        $.each(sortedChapters, function(index, chapter) {
            // Remove existing badge
            chapter.element.find('.new-badge').remove();
            
            // Add "Latest" badge to the first item when sorted descending
            if (index === 0 && order === 'desc') {
                chapter.element.find('.chapter-card-meta').append('<span class="new-badge">Latest</span>');
            }
            
            container.append(chapter.element);
        });
    }
    
    // Sort button click handlers
    $('.chapter-sort-btn').click(function() {
        $('.chapter-sort-btn').removeClass('active');
        $(this).addClass('active');
        
        var sortOrder = $(this).data('sort');
        currentSort = sortOrder;
        sortChapters(sortOrder);
    });
});
</script>

<?php get_footer(); ?>
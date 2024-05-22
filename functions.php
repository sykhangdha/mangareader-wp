<?php
/**
 * Manga Starter Theme functions and definitions
 */
 
 /**
 * Manga Starter Theme only works in WordPress 4.7 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
    require get_template_directory() . '/inc/back-compat.php';
    return;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
 
function mangastarter_setup() {
    /*
     * Make theme available for translation.
    */
    load_theme_textdomain( 'mangastarter' );
    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );
    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support( 'title-tag' );
    /*
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support( 'post-thumbnails' );
    add_image_size( 'xlarge', 700, '', true ); // Extra Large Thumbnail
	add_image_size( 'large', 400, '', true ); // Large Thumbnail
    add_image_size( 'medium', 250, '', true ); // Medium Thumbnail
    add_image_size( 'small', 120, '', true ); // Small Thumbnail
	add_image_size( 'thumbnail', 225, 318, true ); // Thumbnail
    // This theme uses wp_nav_menu() in two locations.
    register_nav_menus( array(
        'top'    => __( 'Top', 'mangastarter' ),
    ) );
    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support( 'html5', array(
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );
    /*
	 * Add theme support for Custom Logo.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/custom-logo/
	 */
    add_theme_support( 'custom-logo', array(
        'width' => 200,
        'height' => 30,
    ) );
}
add_action( 'after_setup_theme', 'mangastarter_setup' );

/**
 * Display the custom logo
 */
function mangastarter_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		if( has_custom_logo() ) {
			the_custom_logo();
		} else {
			echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . get_bloginfo( 'name' ) . '">' . get_bloginfo( 'name' ) . '</a>';
		}
	}
}

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 */ 
function mangastarter_content_width() {
    $content_width = 1120;
    $GLOBALS['content_width'] = apply_filters( 'mangastarter_content_width', $content_width );
}
add_action( 'after_setup_theme', 'mangastarter_content_width', 0 );

/**
 * Register custom fonts.
 */
function mangastarter_fonts_url() {
	$fonts_url = '';
	/**
	 * Translators: If there are characters in your language that are not
	 * supported by Libre Frankin, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$font_families = array();
	$font_families[] = 'Lato:300,300i,400,400i,700,700i';
	$query_args = array(
		'family' => urlencode( implode( '|', $font_families ) ),
		'subset' => urlencode( 'latin,latin-ext' ),
	);
	$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	return esc_url_raw( $fonts_url );
}
/**
 * Add preconnect for Google Fonts.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param array  $urls           URLs to print for resource hints.
 * @param string $relation_type  The relation type the URLs are printed.
 * @return array $urls           URLs to print for resource hints.
 */
function mangastarter_resource_hints( $urls, $relation_type ) {
	if ( wp_style_is( 'mangastarter-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'mangastarter_resource_hints', 10, 2 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function mangastarter_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'mangastarter' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Add widgets here to appear in your sidebar.', 'mangastarter' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
    register_sidebar( array(
        'name'          => __( 'Footer 1', 'mangastarter' ),
        'id'            => 'sidebar-2',
        'description'   => __( 'Add widgets here to appear in your footer.', 'mangastarter' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
    register_sidebar( array(
        'name'          => __( 'Footer 2', 'mangastarter' ),
        'id'            => 'sidebar-3',
        'description'   => __( 'Add widgets here to appear in your footer.', 'mangastarter' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
	) );
    register_sidebar( array(
        'name'          => __( 'Footer 3', 'mangastarter' ),
        'id'            => 'sidebar-4',
        'description'   => __( 'Add widgets here to appear in your footer.', 'mangastarter' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'mangastarter_widgets_init' );

function mangastarter_descriptions_menu( $item_output, $item, $depth, $args ) {
    if ( !empty( $item->description ) ) {
        $item_output = str_replace( $args->link_after . '</a>', '<span class="menu-item-description">' . $item->description . '</span>' . $args->link_after . '</a>', $item_output );
    }
 
    return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'mangastarter_descriptions_menu', 10, 4 );

/**
 * Fallback menu.
 *
 * Adds a fallback menu in case "top" doesn't exists.
 *
 */
function mangastarter_fallback_menu() { ?>
	<ul id="top-menu" class="menu">
		<li class="menu-item menu-item-object-page <?php if( is_home() || is_front_page() ) echo 'current-menu-item current_page_item'; ?> menu-item-home">
			<a href="<?php echo esc_url( home_url() ); ?>">
				<?php _e( 'Home', 'mangastarter' ); ?>
				<span class="menu-item-description"><?php _e( 'Homepage', 'mangastarter' ); ?></span>
			</a>
		</li>
	</ul>
<?php
}

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 */
function mangastarter_javascript_detection() {
    echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'mangastarter_javascript_detection', 0 );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function mangastarter_pingback_header() {
    if ( is_singular() && pings_open() ) {
        printf( '<link rel="pingback" href="%s">' . "\n", get_bloginfo( 'pingback_url' ) );
    }
}
add_action( 'wp_head', 'mangastarter_pingback_header' );

/**
 * Enqueue scripts and styles.
 */
function mangastarter_scripts() {	
    // Add custom fonts, used in the main stylesheet.
    wp_enqueue_style( 'uikit', get_theme_file_uri( '/assets/css/uikit.min.css' ), array(), '2.27.4' );
	if ( is_home() || is_front_page() ) {
		wp_enqueue_style( 'owl-carousel', get_theme_file_uri( '/assets/css/owl.carousel.min.css' ), array(), '2.2.1' );
	}
    // Theme stylesheet.
    wp_enqueue_style( 'mangastarter-style', get_stylesheet_uri() );
    // Load form-select only in chapters.
    if ( is_singular( 'chapters' ) ) {	
        wp_register_style( 'uikit-form-select', get_theme_file_uri( '/assets/css/components/form-select.min.css' ), array( 'uikit' ), '2.27.4' );
        wp_enqueue_style( 'uikit-form-select' );
    }
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'mangastarter-fonts', mangastarter_fonts_url(), array(), null );
    // Load UIkit.
    wp_enqueue_script( 'uikit', get_theme_file_uri( '/assets/js/uikit.min.js' ), array( 'jquery' ), '2.27.4' );
	// Load Owl Carousel
	if ( is_home() || is_front_page() ) {
		wp_enqueue_script( 'owl-carousel', get_theme_file_uri( '/assets/js/owl.carousel.min.js' ), array( 'uikit' ), '2.2.1' );
	}
    // Load chapter conditional scripts
    if ( is_singular( 'chapters' ) ) {
        wp_enqueue_script( 'jquery-plugins', get_theme_file_uri( '/assets/js/jquery.plugins.js' ), array( 'uikit' ), '1.0.8' );
        wp_enqueue_script( 'uikit-form-select', get_theme_file_uri( '/assets/js/components/form-select.min.js' ), array( 'uikit' ), '2.27.4' );		
    }
	// Load Menu JS.
    wp_enqueue_script( 'main', get_theme_file_uri( '/assets/js/main.js' ), array( 'jquery' ), '1.0.0' );
    // Load the html5 shiv.
    wp_enqueue_script( 'html5', get_theme_file_uri( '/assets/js/html5.js' ), array(), '3.7.3' );
    wp_script_add_data( 'html5', 'conditional', 'lt IE 9' );
    // Load comment-reply.js
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'mangastarter_scripts' );

/**
 * Use front-page.php when Front page displays is set to a static page.
 *
 * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.
 */
function mangastarter_front_page_template( $template ) {
    return is_home() ? '' : $template;
}
add_filter( 'frontpage_template',  'mangastarter_front_page_template' );
 
/**
 * TGM Plugin Activation Class File
 */
require get_parent_theme_file_path( '/inc/tgm_plugin_activation.php' );

/**
 * The Required Plugins for the Theme.
 */
require get_parent_theme_file_path( '/inc/required_plugins.php' );

/**
 * Template functions.
 */
require get_parent_theme_file_path( '/inc/template-functions.php' );

/**
 * Customizer additions.
 */
require get_parent_theme_file_path( '/inc/customizer.php' );

/**
 * Chapter settings
 */
require get_parent_theme_file_path( '/inc/chapters.php' );

/**
 * Sort Manga Directory from A-Z
 */

add_action( 'pre_get_posts', 'my_change_sort_order'); 
    function my_change_sort_order($query){
        if(is_archive()):
         //If you wanted it for the archive of a custom post type use: is_post_type_archive( $post_type )
           //Set the order ASC or DESC
           $query->set( 'order', 'ASC' );
           //Set the orderby
           $query->set( 'orderby', 'title' );
        endif;    
    };

// Modify the query for mangas to include wp-pagenavi
function custom_manga_archive_query($query) {
    if (is_post_type_archive('mangas') && $query->is_main_query()) {
        // Set the number of posts per page (adjust as needed)
        $query->set('posts_per_page', 10); // You can change this value as needed
        $query->set('paged', get_query_var('paged'));
    }
}
add_action('pre_get_posts', 'custom_manga_archive_query');

/**
 * Modify the main query to include custom post types in search results.
 *
 * @param WP_Query $query The main query.
 */
function include_custom_post_types_in_search($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_search) {
        $post_types = array('post', 'page', 'manga'); // Add 'manga' to the list of post types.
        $query->set('post_type', $post_types);
    }
}
add_action('pre_get_posts', 'include_custom_post_types_in_search');

function load_more_chapters() {
    $args = $_POST['query'];
    $additional_query = new WP_Query($args);

    if ($additional_query->have_posts()) {
        while ($additional_query->have_posts()) {
            $additional_query->the_post();
            $chapter_title = get_the_title(); // Corrected to get the chapter title
            $chapter_date = get_the_date('F j, Y');
            $manga = get_field('manga');

            if ($manga) {
                foreach ($manga as $manga_item) {
                    $manga_title = get_the_title($manga_item); // Retrieve the manga title using the manga item ID
                    
                    // Display the chapter information within table row
                    ?>
                    <tr class="chapter-item">
                        <td class="chapter-title"><a href="<?php the_permalink(); ?>"><?php echo esc_html($chapter_title); ?></a></td>
                        <td class="manga-name"><a href="<?php echo esc_url(get_permalink($manga_item)); ?>"><?php echo esc_html($manga_title); ?></a></td>
                        <td class="chapter-date"><?php echo esc_html($chapter_date); ?></td>
                    </tr>
                    <?php
                }
            }
        }
    }

    wp_die();
}

add_action('wp_ajax_load_more_chapters', 'load_more_chapters');
add_action('wp_ajax_nopriv_load_more_chapters', 'load_more_chapters');


function add_chapters_to_homepage_query($query) {
    if (is_home() && $query->is_main_query()) {
        $query->set('post_type', array('post', 'chapters'));
    }
}
add_action('pre_get_posts', 'add_chapters_to_homepage_query');

// Add the settings page to the admin menu
function add_themes_styles_settings_page() {
    add_theme_page(
        'Themes/Styles', // Page title
        'Themes/Styles', // Menu title
        'manage_options', // Capability
        'themes-styles-settings', // Menu slug
        'render_themes_styles_settings_page' // Function to display the page
    );
}
add_action('admin_menu', 'add_themes_styles_settings_page');

// Render the settings page
function render_themes_styles_settings_page() {
    ?>
    <div class="wrap">
        <h1>Themes/Styles Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('themes_styles_settings_group');
            do_settings_sections('themes-styles-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings, sections, and fields
function themes_styles_settings_init() {
    register_setting('themes_styles_settings_group', 'selected_recent_chapters_template');
    register_setting('themes_styles_settings_group', 'selected_theme_css');

    add_settings_section(
        'themes_styles_settings_section',
        'Template Settings',
        'themes_styles_settings_section_callback',
        'themes-styles-settings'
    );

    add_settings_field(
        'selected_recent_chapters_template',
        'Select Recent Chapters Template',
        'selected_recent_chapters_template_callback',
        'themes-styles-settings',
        'themes_styles_settings_section'
    );

    add_settings_field(
        'selected_theme_css',
        'Select a Theme',
        'selected_theme_css_callback',
        'themes-styles-settings',
        'themes_styles_settings_section'
    );
}
add_action('admin_init', 'themes_styles_settings_init');

function themes_styles_settings_section_callback() {
    echo '<p>Select the template and theme for your site.</p>';
}

function selected_recent_chapters_template_callback() {
    // Check if a page with the recent chapters template exists
    $recent_chapters_page = get_pages(array(
        'meta_key' => '_wp_page_template',
        'meta_value' => 'recent-chapters-list.php', // Update this to match your template file
    ));

    if (empty($recent_chapters_page)) {
        echo '<p style="color: red;">You must first create a page with the Recent Chapters List template before selecting it here.</p>';
    } else {
        $selected_template = get_option('selected_recent_chapters_template', 'recent-chapters-list.php');
        ?>
        <select name="selected_recent_chapters_template">
            <option value="recent-chapters-list.php" <?php selected($selected_template, 'recent-chapters-list.php'); ?>>Style 1</option>
            <option value="recent-chapters-list-2.php" <?php selected($selected_template, 'recent-chapters-list-2.php'); ?>>Style 2</option>
        </select>
        <?php
    }
}

// Scan the themes folder and return available CSS files
function get_available_themes() {
    $theme_folder = get_template_directory() . '/themes/';
    $css_files = glob($theme_folder . '*.css');
    $themes = array();
    foreach ($css_files as $file) {
        $themes[] = basename($file);
    }
    return $themes;
}

function selected_theme_css_callback() {
    $selected_theme = get_option('selected_theme_css', 'default'); // Default to 'default'
    $themes = get_available_themes();
    ?>
    <select name="selected_theme_css">
        <option value="default" <?php selected($selected_theme, 'default'); ?>>Default</option>
        <?php foreach ($themes as $theme) : ?>
            <option value="<?php echo esc_attr($theme); ?>" <?php selected($selected_theme, $theme); ?>><?php echo esc_html($theme); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function apply_selected_theme_css() {
    $selected_theme = get_option('selected_theme_css', 'default');
    if ($selected_theme && $selected_theme !== 'default') {
        wp_enqueue_style('selected-theme-css', get_template_directory_uri() . '/themes/' . $selected_theme);
    }
}
add_action('wp_enqueue_scripts', 'apply_selected_theme_css');
?>



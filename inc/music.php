<?php
/**
 * Reader mini music player (lofi).
 * Playlist is managed in Appearance → Customize → Reader Music Player.
 * Supports YouTube links (videos and live streams) and direct audio files (mp3, ogg, wav…).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function manga_music_enabled() {
	return (bool) get_theme_mod( 'manga_music_enabled', true );
}

// One track per line: "Title | URL".
function manga_music_default_playlist() {
	return "Lofi Girl - lofi hip hop radio | https://www.youtube.com/watch?v=jfKfPfyJRdk\n"
		. 'Chillhop Radio - jazzy & lofi hip hop | https://www.youtube.com/watch?v=5yx6BWlEVcY';
}

function manga_youtube_id( $url ) {
	if ( preg_match( '~(?:youtu\.be/|youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|live/|shorts/))([A-Za-z0-9_-]{11})~', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

function manga_music_tracks() {
	$raw    = (string) get_theme_mod( 'manga_music_playlist', manga_music_default_playlist() );
	$tracks = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) ) {
			list( $title, $url ) = $parts;
		} else {
			$title = '';
			$url   = $parts[0];
		}

		$yt = manga_youtube_id( $url );
		if ( $yt ) {
			$tracks[] = array(
				'title' => '' !== $title ? $title : __( 'YouTube track', 'manga-theme' ),
				'type'  => 'youtube',
				'src'   => $yt,
			);
			continue;
		}

		$url = esc_url_raw( $url, array( 'http', 'https' ) );
		if ( $url ) {
			$tracks[] = array(
				'title' => '' !== $title ? $title : wp_basename( $url ),
				'type'  => 'audio',
				'src'   => $url,
			);
		}
	}

	return array_slice( $tracks, 0, 50 );
}

function manga_music_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'manga_music', array(
		'title'       => __( 'Reader Music Player', 'manga-theme' ),
		'priority'    => 161,
		'description' => __( 'A mini music player on the reader page. Add one track per line as: Title | Link. Links can be YouTube videos or live streams, or direct audio files (mp3, ogg, wav) such as files from your Media Library. Only add music you are allowed to use.', 'manga-theme' ),
	) );

	$wp_customize->add_setting( 'manga_music_enabled', array(
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );
	$wp_customize->add_control( 'manga_music_enabled', array(
		'section' => 'manga_music',
		'label'   => __( 'Show the music player on the reader page', 'manga-theme' ),
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'manga_music_playlist', array(
		'default'           => manga_music_default_playlist(),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'manga_music_playlist', array(
		'section'     => 'manga_music',
		'label'       => __( 'Playlist', 'manga-theme' ),
		'type'        => 'textarea',
		'input_attrs' => array( 'rows' => 10, 'placeholder' => 'Late Night Lofi | https://example.com/song.mp3' ),
	) );
}
add_action( 'customize_register', 'manga_music_customize_register' );

function manga_render_music_player() {
	if ( ! manga_music_enabled() ) {
		return;
	}
	$tracks = manga_music_tracks();
	if ( ! $tracks ) {
		return;
	}
	?>
	<div class="lofi-player" id="lofiPlayer"
		data-tracks="<?php echo esc_attr( wp_json_encode( $tracks ) ); ?>"
		data-play="<?php esc_attr_e( 'Play', 'manga-theme' ); ?>"
		data-pause="<?php esc_attr_e( 'Pause', 'manga-theme' ); ?>"
		data-error="<?php esc_attr_e( 'This track could not be played.', 'manga-theme' ); ?>">

		<button type="button" class="lofi-toggle" id="lofiToggle" aria-expanded="false" aria-controls="lofiPanel" aria-label="<?php esc_attr_e( 'Music player', 'manga-theme' ); ?>">
			<svg class="lofi-note" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
			<span class="lofi-bars" aria-hidden="true"><span></span><span></span><span></span></span>
		</button>

		<div class="lofi-panel" id="lofiPanel" role="group" aria-label="<?php esc_attr_e( 'Music player', 'manga-theme' ); ?>">
			<div class="lofi-video" id="lofiVideo" hidden><div id="lofiYt"></div></div>
			<div class="lofi-title" id="lofiTitle" aria-live="polite"></div>

			<div class="lofi-controls">
				<button type="button" class="lofi-btn" id="lofiPrev" aria-label="<?php esc_attr_e( 'Previous track', 'manga-theme' ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 6h2v12H6zM20 6v12L9.500 12z"/></svg>
				</button>
				<button type="button" class="lofi-btn lofi-btn-play" id="lofiPlay" aria-label="<?php esc_attr_e( 'Play', 'manga-theme' ); ?>">
					<svg class="i-play" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
					<svg class="i-pause" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
				</button>
				<button type="button" class="lofi-btn" id="lofiNext" aria-label="<?php esc_attr_e( 'Next track', 'manga-theme' ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 6h2v12h-2zM4 6l11.500 6L4 18z"/></svg>
				</button>
			</div>

			<label class="lofi-vol">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 5L6 9H2v6h4l5 4zM15.500 8.500a5 5 0 0 1 0 7"/></svg>
				<span class="screen-reader-text"><?php esc_html_e( 'Volume', 'manga-theme' ); ?></span>
				<input type="range" id="lofiVolume" min="0" max="100" step="1" value="40">
			</label>

			<label class="screen-reader-text" for="lofiSelect"><?php esc_html_e( 'Choose a track', 'manga-theme' ); ?></label>
			<select class="lofi-select" id="lofiSelect"></select>
			<div class="lofi-status" id="lofiStatus" role="status"></div>
		</div>
	</div>
	<?php
}

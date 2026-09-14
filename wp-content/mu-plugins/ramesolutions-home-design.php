<?php
/**
 * Plugin Name: Rame Solutions Home Design
 * Description: Homepage-only storefront styling.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! is_front_page() ) {
			return;
		}

		$file = WPMU_PLUGIN_DIR . '/ramesolutions-home.css';
		wp_enqueue_style( 'ramesolutions-home', WPMU_PLUGIN_URL . '/ramesolutions-home.css', array(), (string) filemtime( $file ) );
	},
	20
);

// Global styling for all pages
add_action( 'wp_head', static function () {
	echo '<style>
		@media(max-width:767px) {
			.custom-logo-link img, .site-logo img, .site-header .custom-logo {
				width: 150px !important;
				max-width: 100% !important;
				max-height: none !important;
			}
		}
	</style>';
} );

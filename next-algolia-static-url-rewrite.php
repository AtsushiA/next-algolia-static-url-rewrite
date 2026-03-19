<?php
/**
 * Plugin Name: NExT Algolia Static URL Rewrite
 * Plugin URI:  https://next-season.net
 * Description: wp-search-with-algolia でインデックスする際に WordPress の動作URLを静的公開URLに置き換えます。
 * Version:     1.0.0
 * Author:	  NExT-Season
 * Author URI:  https://next-season.net
 * License:     GPL-2.0-or-later
 * Text Domain: next-algolia-static-url-rewrite
 */

defined( 'ABSPATH' ) || exit;

define( 'ALGOLIA_STATIC_URL_REWRITE_VERSION', '1.0.0' );
define( 'ALGOLIA_STATIC_URL_REWRITE_DIR', plugin_dir_path( __FILE__ ) );

/**
 * wp-search-with-algolia が有効かチェックし、無効なら管理画面に通知を表示する。
 */
function algolia_static_url_rewrite_check_dependency(): void {
	if ( ! is_plugin_active( 'wp-search-with-algolia/algolia.php' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-warning"><p>';
			echo '<strong>NExT Algolia Static URL Rewrite:</strong> ';
			esc_html_e( 'このプラグインは wp-search-with-algolia が有効な場合のみ動作します。', 'next-algolia-static-url-rewrite' );
			echo '</p></div>';
		} );
	}
}
add_action( 'admin_init', 'algolia_static_url_rewrite_check_dependency' );

/**
 * プラグインの初期化。
 */
function algolia_static_url_rewrite_init(): void {
	require_once ALGOLIA_STATIC_URL_REWRITE_DIR . 'includes/class-settings.php';
	require_once ALGOLIA_STATIC_URL_REWRITE_DIR . 'includes/class-url-rewriter.php';

	( new Algolia_Static_URL_Settings() )->register();
	( new Algolia_Static_URL_Rewriter() )->register_hooks();
}
add_action( 'plugins_loaded', 'algolia_static_url_rewrite_init' );

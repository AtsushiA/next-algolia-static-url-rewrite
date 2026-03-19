<?php
defined( 'ABSPATH' ) || exit;

class Algolia_Static_URL_Settings {

	const OPTION_NAME = 'algolia_static_public_url';
	const MENU_SLUG   = 'next-algolia-static-url-rewrite';

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_menu(): void {
		add_options_page(
			__( 'NExT Algolia Static URL Rewrite', 'next-algolia-static-url-rewrite' ),
			__( 'NExT Algolia Static URL', 'next-algolia-static-url-rewrite' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this, 'render_page' ]
		);
	}

	public function register_settings(): void {
		register_setting(
			self::MENU_SLUG,
			self::OPTION_NAME,
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_url' ],
				'default'           => '',
			]
		);

		add_settings_section(
			'algolia_static_url_main',
			'',
			'__return_false',
			self::MENU_SLUG
		);

		add_settings_field(
			self::OPTION_NAME,
			__( '公開URL', 'next-algolia-static-url-rewrite' ),
			[ $this, 'render_url_field' ],
			self::MENU_SLUG,
			'algolia_static_url_main'
		);
	}

	public function sanitize_url( string $value ): string {
		$url = esc_url_raw( trim( $value ) );
		return untrailingslashit( $url );
	}

	public function render_url_field(): void {
		$value = get_option( self::OPTION_NAME, '' );
		printf(
			'<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="https://www.example.com" />',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $value )
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Algolia にインデックスする際に使用する静的サイトの公開URLを入力してください。（例: https://www.example.com）', 'next-algolia-static-url-rewrite' )
		);

		$wp_url = untrailingslashit( home_url() );
		printf(
			'<p class="description">%s <code>%s</code></p>',
			esc_html__( '現在の WordPress URL:', 'next-algolia-static-url-rewrite' ),
			esc_html( $wp_url )
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'NExT Algolia Static URL Rewrite', 'next-algolia-static-url-rewrite' ); ?></h1>
			<p><?php esc_html_e( 'wp-search-with-algolia でインデックスする際に WordPress の動作URLを静的公開URLに置き換えます。', 'next-algolia-static-url-rewrite' ); ?></p>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::MENU_SLUG );
				do_settings_sections( self::MENU_SLUG );
				submit_button( __( '保存', 'next-algolia-static-url-rewrite' ) );
				?>
			</form>
		</div>
		<?php
	}
}

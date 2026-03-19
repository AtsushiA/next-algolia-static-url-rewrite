<?php
defined( 'ABSPATH' ) || exit;

class Algolia_Static_URL_Rewriter {

	private string $wp_url;
	private string $public_url;

	public function __construct() {
		$this->wp_url     = untrailingslashit( home_url() );
		$this->public_url = untrailingslashit( (string) get_option( Algolia_Static_URL_Settings::OPTION_NAME, '' ) );
	}

	/**
	 * 公開URLが設定されている場合のみフックを登録する。
	 */
	public function register_hooks(): void {
		if ( empty( $this->wp_url ) || empty( $this->public_url ) ) {
			return;
		}

		// 投稿・固定ページ等（posts index）
		add_filter( 'algolia_post_shared_attributes', [ $this, 'rewrite_post_attrs' ], 10, 2 );

		// searchable posts index（全投稿タイプをまとめたインデックス）
		add_filter( 'algolia_searchable_post_shared_attributes', [ $this, 'rewrite_post_attrs' ], 10, 2 );

		// タクソノミー（カテゴリー・タグ等）
		add_filter( 'algolia_term_record', [ $this, 'rewrite_term_record' ], 10, 2 );

		// ユーザー（著者アーカイブ）
		add_filter( 'algolia_user_record', [ $this, 'rewrite_user_record' ], 10, 2 );
	}

	/**
	 * 投稿の shared attributes 内の permalink と images を置換する。
	 *
	 * @param array   $attrs
	 * @param WP_Post $post
	 * @return array
	 */
	public function rewrite_post_attrs( array $attrs, WP_Post $post ): array {
		if ( isset( $attrs['permalink'] ) ) {
			$attrs['permalink'] = $this->replace( $attrs['permalink'] );
		}
		if ( isset( $attrs['images'] ) && is_array( $attrs['images'] ) ) {
			$attrs['images'] = $this->replace_in_array( $attrs['images'] );
		}
		return $attrs;
	}

	/**
	 * タームレコード内の permalink を置換する。
	 *
	 * @param array   $record
	 * @param WP_Term $term
	 * @return array
	 */
	public function rewrite_term_record( array $record, WP_Term $term ): array {
		if ( isset( $record['permalink'] ) ) {
			$record['permalink'] = $this->replace( $record['permalink'] );
		}
		return $record;
	}

	/**
	 * ユーザーレコード内の posts_url / user_url を置換する。
	 *
	 * @param array   $record
	 * @param WP_User $user
	 * @return array
	 */
	public function rewrite_user_record( array $record, WP_User $user ): array {
		foreach ( [ 'posts_url', 'user_url' ] as $key ) {
			if ( isset( $record[ $key ] ) ) {
				$record[ $key ] = $this->replace( $record[ $key ] );
			}
		}
		return $record;
	}

	/**
	 * URL内の WordPress 動作URLを公開URLに置換する。
	 *
	 * @param string $url
	 * @return string
	 */
	private function replace( string $url ): string {
		return str_replace( $this->wp_url, $this->public_url, $url );
	}

	/**
	 * 配列内の文字列値を再帰的に置換する。
	 *
	 * @param array $data
	 * @return array
	 */
	private function replace_in_array( array $data ): array {
		foreach ( $data as $key => $value ) {
			if ( is_string( $value ) ) {
				$data[ $key ] = $this->replace( $value );
			} elseif ( is_array( $value ) ) {
				$data[ $key ] = $this->replace_in_array( $value );
			}
		}
		return $data;
	}
}

<?php
/**
 * プラグイン削除時のクリーンアップ。
 * WordPress のアンインストール手順から直接呼び出された場合のみ実行する。
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'algolia_static_public_url' );

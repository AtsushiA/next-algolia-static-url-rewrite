# NExT Algolia Static URL Rewrite

[![Try in WordPress Playground](https://img.shields.io/badge/Try%20in-WordPress%20Playground-3858e9?logo=wordpress)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/AtsushiA/next-algolia-static-url-rewrite/main/blueprint.json)

WordPress を静的化して別ドメインで公開する運用において、[wp-search-with-algolia](https://wordpress.org/plugins/wp-search-with-algolia/) によるインデックス時に WordPress の動作URLを静的公開URLに置き換えるプラグインです。

## 背景・課題

静的サイトジェネレーターや静的書き出しツールを使って WordPress のコンテンツを別ドメインで公開している場合、wp-search-with-algolia がそのまま Algolia にインデックスすると、レコード内の `permalink` は WordPress の動作URL（例: `https://wp.internal`）になります。

検索結果のリンクが内部の WordPress URLを指してしまうため、静的公開ドメインのURLに置き換えてインデックスする必要があります。

## 解決方法

wp-search-with-algolia が提供するフィルターフックに割り込み、Algolia へ送信される直前に URL を置換します。

```
WordPress 動作URL (home_url)  →  設定画面で入力した公開URL
```

## 必要環境

- WordPress 6.0 以上
- PHP 8.0 以上
- [wp-search-with-algolia](https://wordpress.org/plugins/wp-search-with-algolia/) が有効化されていること

## インストール

1. `next-algolia-static-url-rewrite` フォルダを `wp-content/plugins/` に配置
2. WordPress 管理画面 > プラグイン から「NExT Algolia Static URL Rewrite」を有効化

## 使い方

1. **設定 > NExT Algolia Static URL** を開く
2. 「公開URL」に静的サイトのドメインを入力（例: `https://www.example.com`）
3. 「保存」をクリック
4. wp-search-with-algolia の管理画面からインデックスを再構築

以降、Algolia に送信されるレコードの `permalink` が公開URLに置き換わります。

## ファイル構成

```
next-algolia-static-url-rewrite/
├── next-algolia-static-url-rewrite.php   # エントリーポイント・依存チェック
├── uninstall.php                         # プラグイン削除時のオプション削除
└── includes/
    ├── class-settings.php                # 設定ページ（Settings API）
    └── class-url-rewriter.php            # URL置換ロジック
```

## 技術仕様

### 使用フック

wp-search-with-algolia の `shared_attributes` フックを利用します。コンテンツが複数レコードに分割される**前**に呼ばれるため、1回の処理で全レコードに反映されます。

| フック名 | 対象 | 置換フィールド |
|---|---|---|
| `algolia_post_shared_attributes` | 投稿・固定ページ等（posts index） | `permalink`, `images`（再帰） |
| `algolia_searchable_post_shared_attributes` | searchable posts index | `permalink`, `images`（再帰） |
| `algolia_term_record` | カテゴリー・タグ等のターム | `permalink` |
| `algolia_user_record` | ユーザー（著者アーカイブ） | `posts_url`, `user_url` |

### 動作フロー

```
管理画面で「インデックスを再構築」
  → wp-search-with-algolia が algolia_post_shared_attributes フィルターを呼び出す
  → Algolia_Static_URL_Rewriter::rewrite_post_attrs() が実行される
  → permalink の WordPress 動作URL を 公開URL に str_replace で置換
  → 置換済みデータが Algolia に送信される
```

### 保存オプション

| オプション名 | 内容 |
|---|---|
| `algolia_static_public_url` | 公開URL（末尾スラッシュなし） |

### 注意事項

- 公開URLが未設定の場合、フックは登録されず置換は行われません
- URL置換は `home_url()` を基準に `str_replace` で行います。WordPress の動作URLと公開URLのプロトコル（http/https）も含めて正確に一致させてください
- wp-search-with-algolia が無効な場合、管理画面に警告を表示します

## ライセンス

GPL-2.0-or-later

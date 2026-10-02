=== mgn ブロックコピーボタン ===
Contributors: mgn
Tags: block, gutenberg, copy
Requires at least: 5.9
Tested up to: 7.0
Requires PHP: 5.6
Stable tag: 0.0.8
License: GPL2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

フロント表示の際に、そのページのブロック構造をコピーできるボタンを設置します。

== Description ==

投稿・固定ページなどの個別ページを表示した際に、そのページのブロック構造（ブロックエディターのコード）をクリップボードへコピーするボタンを画面下部に表示します。

ボタンは次の条件をすべて満たす場合にのみ表示されます。

* 投稿・固定ページなどの個別ページである
* ログイン中のユーザーがそのページの編集権限を持っている
* パスワード保護されたページの場合は、パスワードを入力済みである
* URL に `?mgn_wpblock_copy=on` が付いている

== Installation ==

1. GitHub のリリースページから `mgn_wpblock_copy.zip` をダウンロードします。
2. 管理画面の「プラグイン > 新規追加 > プラグインのアップロード」から zip をアップロードし、有効化します。
3. 編集権限のあるユーザーでログインし、対象ページの URL に `?mgn_wpblock_copy=on` を付けて表示します。

== Changelog ==

変更履歴は README.md を参照してください。

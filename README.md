# mgn ブロックコピーボタン
フロント表示の際にそのページのブロック構造をコピーできるボタンを設置します

## 使い方
編集権限のあるユーザーでログインし、投稿・固定ページの URL に `?mgn_wpblock_copy=on` を付けて表示すると、画面下部にコピーボタンが表示されます。

- 動作環境: WordPress 6.4 以上 / PHP 8.1 以上
- 未ログインのユーザーや編集権限のないユーザー、パスワード未入力の保護ページでは表示されません

# 開発

## セットアップ
```
npm ci
composer install
npm run build
```

`App/` 配下のクラスは composer の PSR-4 で読み込むため、`composer install`（または `composer dump-autoload`）が必要です。

## 開発環境
- `npm start`: 開発用の wp-env（http://localhost:1071）を起動します

## Lint・テスト
テストは開発環境とは別の wp-env 環境（`.wp-env.test.json` / http://localhost:8889 / PHP 8.1）で実行します。

```
npm run lint:php          # PHPCS（WordPress Coding Standards）
npm run env:test -- start # テスト用 wp-env を起動
npm run test:php          # PHPUnit
npm run test:e2e          # Playwright による E2E テスト
```

- PHPUnit はテーブル接頭辞 `wptests_` を使うため、テスト用サイトのデータ（`wp_`）は消えません
- 初回の `npm run test:e2e` では Playwright のブラウザがダウンロードされます
- PR では GitHub Actions（`.github/workflows/ci.yml`）で同じチェックが実行されます

## リリース
1. `mgn_wpblock_copy.php` の `Version` と `readme.txt` の `Stable tag` を新しいバージョンに更新し、この README の変更履歴を追記します（2 つの値が一致しない場合は CI がエラーになります）
2. dev にマージすると、dev → main のリリース PR が自動で作成されます（タイトルは `Version` の値）
3. リリース PR を main にマージすると、`Version` の値でタグとリリース（配布用 zip 付き）が作成されます
   - 前回と同じバージョンのまま main にマージした場合、リリースは作成されません

# 変更履歴
## 0.0.10
- 0.0.9 はリリース処理（配布用 zip の作成）が失敗し未公開のため、0.0.9 の変更内容を含めてリリース
- 配布用 zip 作成スクリプトに実行権限がなく、リリースが失敗する問題を修正し、PR ごとの CI で zip の作成を確認するよう変更
- リリース PR（dev → main）の「変更内容」に、dev にマージされた PR の一覧を自動で記載するよう変更
- リリース PR の確認内容から、CI で自動チェックしている項目を削除
- リリースノートに、リリース PR の確認内容が含まれないよう変更

## 0.0.9
- 動作環境の下限を WordPress 6.4 / PHP 8.1 に引き上げ
- footer 要素がないテーマで、コピーボタンがページ最下部のコンテンツに重ならないよう下余白を追加
- PHPCS（WordPress Coding Standards）・PHPUnit・E2E テスト（Playwright）を追加し、PR ごとに GitHub Actions で実行
- 配布用 zip に開発用ファイルを含めないよう修正（同梱ファイルを明示し、composer の開発用パッケージを除外）
- readme.txt の Stable tag と本体の Version の一致を CI でチェック

## 0.0.8
- **セキュリティ修正**: ページ本文をスクリプトへ埋め込む際のエスケープ漏れ（XSS）を修正
- **仕様変更**: コピーボタンは、ログイン中かつ投稿の編集権限を持つユーザーにのみ表示（パスワード保護ページは、パスワード入力後のみ）
- サブクエリのあるページでスクリプトがエラーになり、ボタンが動作しない問題を修正
- 更新通知メッセージの取得にタイムアウトとキャッシュを追加し、取得失敗時のエラーを修正
- コピーに Clipboard API を使用し、失敗時は「コピーに失敗しました」と表示
- footer 要素がないテーマでボタンが表示されない問題を修正
- リリースのバージョン番号を version.json ではなく本体ファイルの Version から取得するよう変更
- Nodeを14から24にアップグレード（バージョン管理をVoltaからmiseに変更）
- Nodeパッケージを最新版にアップグレードし、脆弱性を解消
- npm-run-allをnpm-run-all2に置き換え
- パッケージマネージャーをnpmに統一し、GitHub Actionsを更新

## 0.0.7
- github actionsのキャッシュをv2からv4にアップグレード

## 0.0.6
- 最新バージョンが取得できないバグの対応
- Yarnをv1からv3にアップグレード
- 更新アラートボックスに任意のメッセージを付与できる機能の追加
## 0.0.5
- Nodeパッケージのアップグレード
- マージせずにcloseした場合はGitHub Actionsをスキップするように変更
## 0.0.4
- wp-env環境の追加

## 0.0.3
- ???

## 0.0.2
- AutoUpdate機能の追加
- その他必要なファイルの設置など

<?php
/**
 * Plugin name: mgn ブロックコピーボタン
 * Description: フロント表示の際にそのページのブロック構造をコピーできるボタンを設置
 * Version: 0.0.8
 * Tested up to: 5.9
 * Requires at least: 5.9
 * Requires PHP: 5.6
 * Author: mgn Inc.,
 * Author URI: https://m-g-n.me/
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mgn_wpblock_copy
 *
 * @package mgn_wpblock_copy
 * @author mgn
 * @license GPL-2.0+
 */

namespace Mgn\Wpblock_copy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * declaration constant.
 */
define( 'MGN_WPBLOCK_COPY_KEY', 'MGN_WPBLOCK_COPY' );  //このプラグインのURL.
define( 'MGN_WPBLOCK_COPY_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/' );  //このプラグインのURL.
define( 'MGN_WPBLOCK_COPY_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/' ); //このプラグインのパス.
define( 'MGN_WPBLOCK_COPY_BASENAME', plugin_basename( __FILE__ ) ); //このプラグインのベースネーム.
define( 'MGN_WPBLOCK_COPY_TEXTDOMAIN', 'mgn_wpblock_copy' ); //テキストドメイン名.

/**
 * include files.
 */
require_once(MGN_WPBLOCK_COPY_PATH . 'vendor/autoload.php'); //アップデート用composer.

//各処理用のクラスを読み込む
foreach (glob(MGN_WPBLOCK_COPY_PATH.'App/**/*.php') as $filename) {
	require_once $filename;
}

/**
 * 初期設定.
 */
class Bootstrap {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'bootstrap' ] );
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'template_redirect', [ $this, 'check_allow_display_btn' ] );
	}

	/**
	 * Bootstrap.
	 */
	public function bootstrap() {
		new App\Setup\AutoUpdate(); //自動更新チェック.
		new App\Setup\InPluginUpdateMessage(); //更新アラートメッセージに追加でメッセージを表示
	}

	/**
	 * Load Textdomain.
	 */
	public function load_textdomain() {
		new App\Setup\TextDomain();
	}

	/**
	 * ボタンを表示するかチェック,
	 */
	public function check_allow_display_btn() {
		// 個別ページ（投稿・固定ページ等）以外では表示しない.
		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		// 編集権限のないユーザー、およびパスワード未入力の保護ページには本文を出力しない.
		if ( ! current_user_can( 'edit_post', $post->ID ) || post_password_required( $post ) ) {
			return;
		}

		//todo オプションページの値を取得
		$view_type = 'param';

		//オプションの設定内容によって表示するかの有無を判断.
		if ( 'param' === $view_type ) { //パラメータ値で表示.
			$param_name = 'mgn_wpblock_copy';
			$param_val  = 'on'; //TODO：将来オプションページの値から取得
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 表示切替のみで状態は変更しない.
			if ( isset( $_GET[ $param_name ] ) && $param_val === sanitize_text_field( wp_unslash( $_GET[ $param_name ] ) ) ) { //パラメータがある
				$this->dislay_btn( $post );
			}
		} elseif ( 'normal' === $view_type ) { //常時表示
			$this->dislay_btn( $post );
		}
	}

	/**
	 * ボタンを表示.
	 *
	 * @param \WP_Post $post コピー対象の投稿.
	 */
	public function dislay_btn( $post ) {
		new App\Setup\Assets(); //ボタン用のCSS・JSの読み込み.
		add_action(
			'wp_enqueue_scripts',
			function () use ( $post ) {
				// JSON としてエンコードし、スクリプト内に安全に埋め込む（< > & ' " もエスケープ）.
				$contents = wp_json_encode( $post->post_content, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
				wp_add_inline_script(
					App\Setup\Assets::SCRIPT_HANDLE,
					'const copyContents = ' . $contents . ';',
					'before'
				);
			},
			20 // Assets の enqueue（優先度10）より後に実行.
		);
	}
}

new Bootstrap();

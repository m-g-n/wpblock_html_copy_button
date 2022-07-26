<?php
/**
 * Plugin name: mgn ブロックコピーボタン
 * Description: フロント表示の際にそのページのブロック構造をコピーできるボタンを設置
 * Version: 0.0.4
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
		add_action( 'pre_get_posts', [ $this, 'check_allow_display_btn' ]); //TODO：もっと適切なhook名があったら修正する
	}

	/**
	 * Bootstrap.
	 */
	public function bootstrap() {
		new App\Setup\AutoUpdate(); //自動更新チェック.
		new App\Setup\OptionPage(); //オプションページ.
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
		//例外処理.
		if ( !is_page() && !is_single() && is_front_page() ){
			return;
		}

		//todo オプションページの値を取得
		$view_type = 'param';

		//オプションの設定内容によって表示するかの有無を判断.
		if ( 'param' === $view_type ) { //パラメータ値で表示.
			$param_name = 'mgn_wpblock_copy';
			$param_val  = 'on'; //TODO：将来オプションページの値から取得
			if ( isset($_GET[$param_name]) && $param_val === $_GET[$param_name] ) { //パラメータがある
				$this->dislay_btn();
			}
		} elseif ( 'normal' === $view_type ) { //常時表示
			$this->dislay_btn();
		} else {
			return;
		}
	}

	/**
	 * ボタンを表示.
	 */
	public function dislay_btn(){
		new App\Setup\Assets(); //ボタン用のCSS・JSの読み込み.
		add_action(
			'wp_footer',
			function(){
				global $post;
				$contents = $post->post_content;
				?>
				<script>
					const copyContents = `<?php echo $contents; ?>`;
				</script>
				<?php
			}
		);
	}
}

new Bootstrap();

<?php
/**
 * Plugin name: mgn ブロックコピーボタン
 * Description: フロント表示の際にそのページのブロック構造をコピーできるボタンを設置
 * Version: 0.0.2
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
	}

	/**
	 * Bootstrap.
	 */
	public function bootstrap() {
		new App\Setup\AutoUpdate(); //自動更新チェック
		new App\Setup\Assets();
		$this->view_button();
	}

	/**
	 * Load Textdomain.
	 */
	public function load_textdomain() {
		new App\Setup\TextDomain();
	}

	public function view_button(){
		add_action(
			'wp_footer',
			function(){
				if ( is_page() && ! is_home() ) {
					global $post;
					$contents = $post->post_content;
					?>
					<script>
						const copyContents = `<?php echo $contents; ?>`;
					</script>
					<?php
				}
			}
		);
	}
}

new Bootstrap();

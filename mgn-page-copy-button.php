<?php
/**
 * Plugin name: mgn ページコピーボタン
 * Description: デモサイト用にコンテンツコピーボタンを設置します。
 * Version: 0.0.1
 *
 * @package mgn-page-copy-button
 * @author mgn
 * @license GPL-2.0+
 */

/**
 * 定数を宣言
 */
define( 'MPCB_PLUGIN_URL', plugins_url( '', __FILE__ ) ); // このプラグインのURL
define( 'MPCB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // このプラグインのパス


add_action( 'wp_enqueue_scripts', 'mpcb_enqueue_style_script' );
/**
 * 外部JS・CSSの読み込み
 */
function mpcb_enqueue_style_script() {
	if ( is_page() && ! is_home() ) {
		wp_enqueue_style(
			'mpcb-style',
			MPCB_PLUGIN_URL . '/src/css/mpcb_style.css',
			array(),
			filemtime( MPCB_PLUGIN_PATH . '/src/css/mpcb_style.css' )
		);
		wp_enqueue_script(
			'mpcb-script',
			MPCB_PLUGIN_URL . '/src/js/mpcb_script.js',
			array(),
			filemtime( MPCB_PLUGIN_PATH . '/src/js/mpcb_script.js' ),
			true
		);
	}
}


add_action( 'wp_footer', 'add_btn_copy' );
/**
 * コピーボタンの設置 / コピー内容を取得
 */
function add_btn_copy() {
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
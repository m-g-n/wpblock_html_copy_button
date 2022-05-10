<?php
/**
 * @package ruijinen-skin-r002-lp
 * @author mgn
 * @license GPL-2.0+
 */

namespace Mgn\Wpblock_copy\App\Setup;

class Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
	}

	/**
	 * Enqueue front assets
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_style(
			MGN_WPBLOCK_COPY_BASENAME.'-style',
			MGN_WPBLOCK_COPY_URL . '/dist/css/style.css',
			[],
			filemtime( MGN_WPBLOCK_COPY_PATH . '/dist/css/style.css' )
		);
		wp_enqueue_script(
			MGN_WPBLOCK_COPY_BASENAME.'-script',
			MGN_WPBLOCK_COPY_URL . '/dist/js/script.js',
			array(),
			filemtime( MPCB_PLUGIN_PATH . '/dist/js/script.js' ),
			true
		);
	}
}

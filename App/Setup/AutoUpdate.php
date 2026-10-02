<?php
/**
 * GitHub リリースを使った自動更新.
 *
 * @package mgn_wpblock_copy
 * @author mgn
 * @license GPL-2.0+
 */

namespace Mgn\Wpblock_copy\App\Setup;

use Inc2734\WP_GitHub_Plugin_Updater\Bootstrap as Updater;

/**
 * GitHub のリリースからプラグインを自動更新する.
 */
class AutoUpdate {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'activate_autoupdate' ) );
	}

	/**
	 * Activate auto update using GitHub.
	 *
	 * @return void
	 */
	public function activate_autoupdate() {
		new Updater(
			MGN_WPBLOCK_COPY_BASENAME,
			'm-g-n',
			'wpblock_html_copy_button',
			array(
				'tested'       => '7.0', // Tested up WordPress version.
				'requires_php' => '8.1', // Requires PHP version.
				'requires'     => '6.4', // Requires WordPress version.
			)
		);
	}
}

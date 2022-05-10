<?php
/**
 * @package ruijinen-skin-r002-lp
 * @author mgn
 * @license GPL-2.0+
 */

namespace Mgn\Wpblock_copy\App\Setup;

use Inc2734\WP_GitHub_Plugin_Updater\Bootstrap as Updater;

class AutoUpdate{

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'activate_autoupdate' ) );
	}

	/**
	 * Activate auto update using GitHub,
	 *
	 * @return void
	 */
	public function activate_autoupdate() {
		new Updater(
			MGN_WPBLOCK_COPY_BASENAME,
			'm-g-n',
			'ruijinen-plugin_skin-r002-lp-a',
			[
				'description_url'  => 'https://rui-jin-en.com/block_patterns/r002-lp/',
				'faq_url'          => 'https://rui-jin-en.com/help/',
				'changelog_url'    => 'https://rui-jin-en.com/category/product-renew/',
				'icons' => [
					// 'svg' => '', // svg URL. Square recommended
					'1x'  => 'https://rui-jin-en.com/wp-content/uploads/2022/02/icon-64x64-1.png', // Image URL 64×64
					'2x'  => 'https://rui-jin-en.com/wp-content/uploads/2022/02/icon-128x128-1.png', // Image URL 128×128
				],
				// 'banners' => [
				// 	'low'  => '', // Image URL 772×250
				// 	'high' => '', // Image URL 1554×500
				// ],
				'tested'       => '5.9', // Tested up WordPress version
				'requires_php' => '5.6.0', // Requires PHP version
				'requires'     => '5.9', // Requires WordPress version
			]
		);
	}
}
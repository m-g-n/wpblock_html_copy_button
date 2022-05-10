<?php
/**
 * @package ruijinen-skin-r002-lp
 * @author mgn
 * @license GPL-2.0+
 */

namespace Mgn\Wpblock_copy\App\Setup;

class TextDomain{
	/**
	 * Constructor.
	 */
	public function __construct() {
		load_plugin_textdomain( MGN_WPBLOCK_COPY_TEXTDOMAIN, false, MGN_WPBLOCK_COPY_PATH . '/languages' );
		add_filter( 'load_textdomain_mofile', [ $this, 'load_textdomain_mofile' ], 10, 2 );
	}

	/**
	 * When local .mo file exists, load this.
	 *
	 * @param string $mofile Path to the MO file.
	 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
	 * @return string
	 */
	public function load_textdomain_mofile( $mofile, $domain ) {
		if ( MGN_WPBLOCK_COPY_TEXTDOMAIN === $domain ) {
			$mofilename   = basename( $mofile );
			$local_mofile = MGN_WPBLOCK_COPY_PATH . '/languages/' . $mofilename;
			if ( file_exists( $local_mofile ) ) {
				return $local_mofile;
			}
		}
		return $mofile;
	}
}

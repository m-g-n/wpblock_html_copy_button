<?php
/**
 * フロント用アセットの読み込み.
 *
 * @package mgn_wpblock_copy
 * @author mgn
 * @license GPL-2.0+
 */

namespace Mgn\Wpblock_copy\App\Setup;

/**
 * コピーボタン用の CSS・JS を読み込む.
 */
class Assets {

	/**
	 * フロント用スクリプトのハンドル名.
	 */
	const SCRIPT_HANDLE = MGN_WPBLOCK_COPY_BASENAME . '-script';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
	}

	/**
	 * Enqueue front assets
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_style(
			MGN_WPBLOCK_COPY_BASENAME . '-style',
			MGN_WPBLOCK_COPY_URL . 'dist/css/style.css',
			array(),
			$this->get_file_version( 'dist/css/style.css' )
		);
		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			MGN_WPBLOCK_COPY_URL . 'dist/js/script.js',
			array(),
			$this->get_file_version( 'dist/js/script.js' ),
			true
		);
	}

	/**
	 * キャッシュバスティング用のバージョン（ファイル更新日時）を返す.
	 * ファイルが存在しない場合は Warning を出さずに false（WordPress のバージョンを使用）を返す.
	 *
	 * @param string $relative_path プラグインディレクトリからの相対パス.
	 * @return int|false
	 */
	private function get_file_version( $relative_path ) {
		$path = MGN_WPBLOCK_COPY_PATH . $relative_path;
		return file_exists( $path ) ? filemtime( $path ) : false;
	}
}

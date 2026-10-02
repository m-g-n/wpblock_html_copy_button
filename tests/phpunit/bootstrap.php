<?php
/**
 * PHPUnit のブートストラップ.
 *
 * WordPress のテストスイートはテーブルを作り直すため、サイト本体のテーブル（wp_）とは
 * 別の接頭辞（wptests_）を使い、開発・E2E 用のデータを壊さないようにする.
 * wp-env の wp-tests-config.php は WORDPRESS_TABLE_PREFIX を参照する.
 *
 * @package mgn_wpblock_copy
 */

putenv( 'WORDPRESS_TABLE_PREFIX=wptests_' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- テストスイートのインストール処理（子プロセス）にも接頭辞を引き継ぐため.

$mgn_wpblock_copy_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $mgn_wpblock_copy_tests_dir ) {
	$mgn_wpblock_copy_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $mgn_wpblock_copy_tests_dir . '/includes/functions.php' ) ) {
	echo 'WordPress のテストライブラリが見つかりません: ' . $mgn_wpblock_copy_tests_dir . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI 出力.
	echo 'npm run test:php（wp-env のテスト環境）で実行してください。' . PHP_EOL;
	exit( 1 );
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress テストスイートが参照する定数名.
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills' );

require_once $mgn_wpblock_copy_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__, 2 ) . '/mgn_wpblock_copy.php';
	}
);

require $mgn_wpblock_copy_tests_dir . '/includes/bootstrap.php';

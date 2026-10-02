<?php
/**
 * コピーボタンの表示条件と本文の埋め込みのテスト.
 *
 * @package mgn_wpblock_copy
 */

use Mgn\Wpblock_copy\App\Setup\Assets;
use Mgn\Wpblock_copy\Bootstrap;

/**
 * Bootstrap::check_allow_display_btn() / display_btn() のテスト.
 */
class Test_Display_Btn extends WP_UnitTestCase {

	/**
	 * 管理者ユーザー ID.
	 *
	 * @var int
	 */
	private static $admin_id;

	/**
	 * 投稿者ユーザー ID（他人の投稿は編集できない）.
	 *
	 * @var int
	 */
	private static $author_id;

	/**
	 * 公開済み投稿 ID（管理者が作成）.
	 *
	 * @var int
	 */
	private static $post_id;

	/**
	 * テスト用のユーザーと投稿を作成する.
	 *
	 * @param WP_UnitTest_Factory $factory ファクトリー.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$admin_id  = $factory->user->create( array( 'role' => 'administrator' ) );
		self::$author_id = $factory->user->create( array( 'role' => 'author' ) );
		self::$post_id   = $factory->post->create(
			array(
				'post_author'  => self::$admin_id,
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:paragraph --><p>hello</p><!-- /wp:paragraph -->',
			)
		);
	}

	/**
	 * スクリプトの登録状態をテストごとに初期化する.
	 */
	public function set_up() {
		parent::set_up();
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;
		unset( $_GET['mgn_wpblock_copy'] );
	}

	/**
	 * 後片付け.
	 */
	public function tear_down() {
		unset( $_GET['mgn_wpblock_copy'] );
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;
		parent::tear_down();
	}

	/**
	 * 指定条件でフロント表示を再現し、出力される inline script（before）を返す.
	 *
	 * @param int    $user_id ログインユーザー ID（0 は未ログイン）.
	 * @param string $url     表示する URL.
	 * @param bool   $param   ?mgn_wpblock_copy=on を付けるか.
	 * @return string[] inline script の配列.
	 */
	private function render( $user_id, $url, $param = true ) {
		wp_set_current_user( $user_id );
		$this->go_to( $url );
		if ( $param ) {
			$_GET['mgn_wpblock_copy'] = 'on';
		}

		( new Bootstrap() )->check_allow_display_btn();
		do_action( 'wp_enqueue_scripts' );

		$before = wp_scripts()->get_data( Assets::SCRIPT_HANDLE, 'before' );
		return is_array( $before ) ? array_values( array_filter( $before ) ) : array();
	}

	/**
	 * 本文の取得は template_redirect で行い、サブクエリごとに発火する pre_get_posts は使わない.
	 */
	public function test_hooked_on_template_redirect_not_pre_get_posts() {
		$hooks = array();
		foreach ( array( 'template_redirect', 'pre_get_posts' ) as $hook_name ) {
			$hooks[ $hook_name ] = false;
			foreach ( $GLOBALS['wp_filter'][ $hook_name ]->callbacks ?? array() as $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( is_array( $callback['function'] ) && $callback['function'][0] instanceof Bootstrap ) {
						$hooks[ $hook_name ] = true;
					}
				}
			}
		}
		$this->assertTrue( $hooks['template_redirect'] );
		$this->assertFalse( $hooks['pre_get_posts'] );
	}

	/**
	 * 編集権限のあるユーザー + パラメータありの場合のみ本文を出力する.
	 */
	public function test_outputs_for_editor_with_param() {
		$before = $this->render( self::$admin_id, get_permalink( self::$post_id ) );

		$this->assertCount( 1, $before );
		$this->assertStringStartsWith( 'const copyContents = ', $before[0] );
		$this->assertTrue( wp_script_is( Assets::SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertTrue( wp_style_is( MGN_WPBLOCK_COPY_BASENAME . '-style', 'enqueued' ) );
	}

	/**
	 * ボタン文言を JS に渡す.
	 */
	public function test_localizes_labels() {
		$this->render( self::$admin_id, get_permalink( self::$post_id ) );

		$data = wp_scripts()->get_data( Assets::SCRIPT_HANDLE, 'data' );
		$this->assertStringContainsString( 'var mgnWpblockCopyL10n', $data );
		foreach ( array( 'copy', 'copied', 'failed' ) as $key ) {
			$this->assertStringContainsString( '"' . $key . '":', $data );
		}
	}

	/**
	 * パラメータがない、または値が異なる場合は出力しない.
	 */
	public function test_no_output_without_valid_param() {
		$this->assertSame( array(), $this->render( self::$admin_id, get_permalink( self::$post_id ), false ) );

		$GLOBALS['wp_scripts']    = null;
		$_GET['mgn_wpblock_copy'] = 'off';
		wp_set_current_user( self::$admin_id );
		$this->go_to( get_permalink( self::$post_id ) );
		( new Bootstrap() )->check_allow_display_btn();
		do_action( 'wp_enqueue_scripts' );
		$this->assertFalse( wp_script_is( Assets::SCRIPT_HANDLE, 'enqueued' ) );
	}

	/**
	 * 未ログインユーザーには出力しない.
	 */
	public function test_no_output_for_anonymous() {
		$this->assertSame( array(), $this->render( 0, get_permalink( self::$post_id ) ) );
		$this->assertFalse( wp_script_is( Assets::SCRIPT_HANDLE, 'enqueued' ) );
	}

	/**
	 * 編集権限のないユーザー（他人の投稿を見る投稿者）には出力しない.
	 */
	public function test_no_output_for_user_without_edit_permission() {
		$this->assertSame( array(), $this->render( self::$author_id, get_permalink( self::$post_id ) ) );
	}

	/**
	 * パスワード未入力の保護ページでは、管理者にも出力しない.
	 */
	public function test_no_output_for_password_protected_post() {
		$post_id = self::factory()->post->create(
			array(
				'post_author'   => self::$admin_id,
				'post_status'   => 'publish',
				'post_password' => 'secret',
				'post_content'  => 'SECRET_BODY',
			)
		);
		$this->assertSame( array(), $this->render( self::$admin_id, get_permalink( $post_id ) ) );
	}

	/**
	 * 個別ページ以外（アーカイブ・404）では出力しない.
	 */
	public function test_no_output_on_non_singular() {
		$this->assertSame( array(), $this->render( self::$admin_id, home_url( '/' ) ), 'home' );
		$GLOBALS['wp_scripts'] = null;
		$this->assertSame( array(), $this->render( self::$admin_id, home_url( '/?p=999999' ) ), '404' );
	}

	/**
	 * 固定ページでも出力する.
	 */
	public function test_outputs_on_page() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_author' => self::$admin_id,
				'post_status' => 'publish',
			)
		);
		$this->assertCount( 1, $this->render( self::$admin_id, get_permalink( $page_id ) ) );
	}

	/**
	 * 本文に JS・HTML として危険な文字列が含まれても、文字列リテラルとして安全に埋め込む.
	 */
	public function test_contents_are_safely_encoded() {
		$content = "<!-- wp:html -->a`b \${alert(1)} </script><script>alert(2)</script> & \"q\" 's<!-- /wp:html -->";
		$post_id = self::factory()->post->create(
			array(
				'post_author'  => self::$admin_id,
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
		// kses による本文の変更を避けるため、DB の値を直接比較対象にする.
		$expected = get_post( $post_id )->post_content;

		$before = $this->render( self::$admin_id, get_permalink( $post_id ) );
		$this->assertCount( 1, $before );

		$this->assertMatchesRegularExpression( '/^const copyContents = (".*");$/s', $before[0] );
		preg_match( '/^const copyContents = (".*");$/s', $before[0], $matches );
		$json = $matches[1];

		// script 要素を閉じたり HTML コメント状態に入ったりする文字を含まない.
		$this->assertStringNotContainsString( '<', $json );
		$this->assertStringNotContainsString( '>', $json );
		// JSON 文字列として元の本文に戻せる.
		$this->assertSame( $expected, json_decode( $json ) );
	}

	/**
	 * サブクエリが発生しても本文の出力は 1 回だけ.
	 */
	public function test_outputs_once_even_with_secondary_queries() {
		wp_set_current_user( self::$admin_id );
		$this->go_to( get_permalink( self::$post_id ) );
		$_GET['mgn_wpblock_copy'] = 'on';

		( new Bootstrap() )->check_allow_display_btn();
		new WP_Query( array( 'post_type' => 'post' ) );
		get_posts( array( 'post_type' => 'page' ) );
		do_action( 'wp_enqueue_scripts' );

		$before = array_filter( (array) wp_scripts()->get_data( Assets::SCRIPT_HANDLE, 'before' ) );
		$this->assertCount( 1, $before );
	}

	/**
	 * アセットの URL に二重スラッシュを含まない.
	 */
	public function test_asset_urls_have_no_double_slash() {
		$this->render( self::$admin_id, get_permalink( self::$post_id ) );

		$script = wp_scripts()->registered[ Assets::SCRIPT_HANDLE ]->src;
		$style  = wp_styles()->registered[ MGN_WPBLOCK_COPY_BASENAME . '-style' ]->src;
		foreach ( array( $script, $style ) as $src ) {
			$this->assertStringNotContainsString( '//dist/', $src );
		}
		$this->assertStringEndsWith( 'dist/js/script.js', $script );
		$this->assertStringEndsWith( 'dist/css/style.css', $style );
	}
}

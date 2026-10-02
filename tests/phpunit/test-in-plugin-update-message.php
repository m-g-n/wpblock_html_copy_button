<?php
/**
 * プラグイン更新画面への追加メッセージ表示のテスト.
 *
 * @package mgn_wpblock_copy
 */

use Mgn\Wpblock_copy\App\Setup\InPluginUpdateMessage;

/**
 * InPluginUpdateMessage のテスト.
 */
class Test_In_Plugin_Update_Message extends WP_UnitTestCase {

	/**
	 * HTTP リクエストの回数.
	 *
	 * @var int
	 */
	private $request_count = 0;

	/**
	 * 擬似レスポンス（WP_Error または配列）.
	 *
	 * @var array|WP_Error
	 */
	private $fake_response;

	/**
	 * 通知 JSON の取得を擬似レスポンスに差し替える.
	 */
	public function set_up() {
		parent::set_up();
		delete_transient( InPluginUpdateMessage::TRANSIENT_KEY );
		$this->request_count = 0;
		add_filter( 'pre_http_request', array( $this, 'fake_http' ), 10, 3 );
	}

	/**
	 * 後片付け.
	 */
	public function tear_down() {
		remove_filter( 'pre_http_request', array( $this, 'fake_http' ), 10 );
		delete_transient( InPluginUpdateMessage::TRANSIENT_KEY );
		parent::tear_down();
	}

	/**
	 * 通知 JSON の URL へのリクエストだけを擬似レスポンスで返す.
	 *
	 * @param false|array $preempt 既定値.
	 * @param array       $args    リクエスト引数.
	 * @param string      $url     URL.
	 * @return false|array|WP_Error
	 */
	public function fake_http( $preempt, $args, $url ) {
		if ( false === strpos( $url, '/update-notice/' . MGN_WPBLOCK_COPY_KEY . '.json' ) ) {
			return $preempt;
		}
		++$this->request_count;
		$this->assertSame( 5, $args['timeout'] );
		return $this->fake_response;
	}

	/**
	 * JSON 本文から擬似レスポンスを作る.
	 *
	 * @param string $body レスポンス本文.
	 * @param int    $code ステータスコード.
	 */
	private function respond_with( $body, $code = 200 ) {
		$this->fake_response = array(
			'headers'  => array(),
			'body'     => $body,
			'response' => array(
				'code'    => $code,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	/**
	 * 更新メッセージの出力を取得する.
	 *
	 * @param array $data in_plugin_update_message に渡すデータ.
	 * @return string
	 */
	private function output( $data ) {
		ob_start();
		( new InPluginUpdateMessage() )->in_plugin_update_message( $data, null );
		return ob_get_clean();
	}

	/**
	 * 該当バージョンのメッセージとリンクを表示する.
	 */
	public function test_outputs_message_for_matching_version() {
		$this->respond_with(
			wp_json_encode(
				array(
					array(
						'1.0.0' => array(
							'message' => '重要なお知らせ',
							'url'     => 'https://example.com/notice',
						),
					),
				)
			)
		);

		$html = $this->output( array( 'new_version' => '1.0.0' ) );

		$this->assertStringContainsString( '重要なお知らせ', $html );
		$this->assertStringContainsString( 'href="https://example.com/notice"', $html );
	}

	/**
	 * リモートの値はエスケープして出力する.
	 */
	public function test_escapes_remote_values() {
		$this->respond_with(
			wp_json_encode(
				array(
					array(
						'1.0.0' => array(
							'message' => '<script>alert(1)</script><strong>ok</strong>',
							'url'     => 'javascript:alert(1)',
						),
					),
				)
			)
		);

		$html = $this->output( array( 'new_version' => '1.0.0' ) );

		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '<strong>ok</strong>', $html );
		$this->assertStringNotContainsString( 'javascript:', $html );
	}

	/**
	 * 該当バージョンがない、または new_version がない場合は何も出力しない.
	 */
	public function test_no_output_without_matching_version() {
		$this->respond_with( wp_json_encode( array( array( '1.0.0' => array( 'message' => 'x' ) ) ) ) );

		$this->assertSame( '', $this->output( array( 'new_version' => '9.9.9' ) ) );
		$this->assertSame( '', $this->output( array() ) );
	}

	/**
	 * 不正な JSON・想定外の構造・HTTP エラーでも Fatal にならず何も出力しない.
	 *
	 * @dataProvider data_broken_responses
	 *
	 * @param string $body レスポンス本文.
	 * @param int    $code ステータスコード.
	 */
	public function test_broken_responses_are_ignored( $body, $code ) {
		$this->respond_with( $body, $code );

		$this->assertSame( '', $this->output( array( 'new_version' => '1.0.0' ) ) );
		$this->assertSame( array(), get_transient( InPluginUpdateMessage::TRANSIENT_KEY ) );
	}

	/**
	 * 壊れたレスポンスのパターン.
	 *
	 * @return array
	 */
	public function data_broken_responses() {
		return array(
			'不正な JSON'      => array( '{invalid', 200 ),
			'null'          => array( 'null', 200 ),
			'トップレベルがオブジェクト' => array( '{"1.0.0":"x"}', 200 ),
			'空配列'           => array( '[]', 200 ),
			'404'           => array( 'not found', 404 ),
		);
	}

	/**
	 * 通信エラー（WP_Error）でも何も出力しない.
	 */
	public function test_wp_error_is_ignored() {
		$this->fake_response = new WP_Error( 'http_request_failed', 'timeout' );

		$this->assertSame( '', $this->output( array( 'new_version' => '1.0.0' ) ) );
	}

	/**
	 * 取得結果をキャッシュし、2 回目以降は通信しない.
	 */
	public function test_caches_result() {
		$this->respond_with( wp_json_encode( array( array( '1.0.0' => array( 'message' => 'x' ) ) ) ) );

		$this->output( array( 'new_version' => '1.0.0' ) );
		$this->output( array( 'new_version' => '1.0.0' ) );

		$this->assertSame( 1, $this->request_count );
	}
}

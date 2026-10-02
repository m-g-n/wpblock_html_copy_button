<?php
/**
 * プラグイン更新画面への追加メッセージ表示.
 *
 * @package mgn_wpblock_copy
 * @author mgn
 * @license GPL-2.0+
 */

namespace Mgn\Wpblock_copy\App\Setup;

/**
 * 更新通知 JSON の内容をプラグイン更新画面のアラートに追記する.
 */
class InPluginUpdateMessage {

	/**
	 * 通知JSONのキャッシュ用 transient キー.
	 */
	const TRANSIENT_KEY = 'mgn_wpblock_copy_update_notice';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'in_plugin_update_message-' . MGN_WPBLOCK_COPY_BASENAME, array( $this, 'in_plugin_update_message' ), 10, 2 );
	}

	/**
	 * 更新画面のアラートボックスにメッセージを追加する.
	 *
	 * @param array  $data     プラグインのデータ（new_version を含む）.
	 * @param object $response 更新 API のレスポンス（未使用）.
	 */
	public function in_plugin_update_message( $data, $response ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- フックの引数シグネチャに合わせる.
		if ( empty( $data['new_version'] ) ) {
			return;
		}
		$messages = $this->get_the_notice_json( $data['new_version'] );
		if ( ! is_array( $messages ) || empty( $messages['message'] ) ) {
			return;
		}
		$message = '<br>' . wp_kses_post( $messages['message'] );
		if ( ! empty( $messages['url'] ) ) {
			$message .= '<a href="' . esc_url( $messages['url'] ) . '" target="_blank" rel="noopener"> &#62;&#62;詳細を見る</a>';
		}
		echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 各値はエスケープ済み.
	}

	/**
	 * 指定バージョンの通知メッセージを返す.
	 *
	 * @param string|null $version 対象バージョン.
	 * @return array|false メッセージ情報。該当なしの場合は false.
	 */
	private function get_the_notice_json( $version = null ) {
		$arr = $this->fetch_notices();
		return array_key_exists( $version, $arr ) ? $arr[ $version ] : false; // 指定のバージョンのキーがあったらメッセージ情報を取得.
	}

	/**
	 * 通知JSONを取得し、バージョンをキーとした配列で返す（取得失敗時は空配列）.
	 *
	 * @return array
	 */
	private function fetch_notices() {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$notices  = array();
		$url      = 'https://rui-jin-en.com/update-notice/' . MGN_WPBLOCK_COPY_KEY . '.json';
		$response = wp_remote_get( $url, array( 'timeout' => 5 ) );

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$json = wp_remote_retrieve_body( $response );
			$json = mb_convert_encoding( $json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN' ); // 文字コードをUTF-8に変換.
			$arr  = json_decode( $json, true ); // JSONを連想配列に変換.
			if ( is_array( $arr ) ) {
				$arr = array_values( array_filter( $arr, 'is_array' ) );
				if ( $arr ) {
					$notices = call_user_func_array( 'array_merge', $arr ); // 階層が１つ深いため配列の階層を1つ浅くする.
				}
			}
		}

		// 取得失敗時も短時間キャッシュし、更新画面表示のたびに通信しないようにする.
		set_transient( self::TRANSIENT_KEY, $notices, $notices ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );

		return $notices;
	}
}

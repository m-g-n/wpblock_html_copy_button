<?php
/**
 * @package wpblock_copy
 * @author mgn
 * @license GPL-2.0+
 */

namespace Mgn\Wpblock_copy\App\Setup\Admin;

class OptionPage{

	/**
	 * Properties.
	 */
	private $option_group = 'MGN_WPBLOCK_copy';
	private $option_name;
	private $option_page_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->option_name      = $this->option_group.'_sm_customize';
		$this->option_page_name = $this->option_group.'_sm_customize';
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_menu', [ $this, 'admin_init' ] );
	}

	/**
	 * Add Option Page.
	 */
	public function admin_menu() {
		add_options_page(
			__( '[RUI-JIN-EN Skin] Type LP(R002)', MGN_WPBLOCK_COPY_TEXTDOMAIN ),
			__( '[RUI-JIN-EN Skin] Type LP(R002)', MGN_WPBLOCK_COPY_TEXTDOMAIN ),
			'manage_options',
			$this->option_page_name, //メニューslug.
			function() {
				?>
				<div class="wrap">
					<h1><?php esc_html_e( '[RUI-JIN-EN Skin] Type LP(R002)', MGN_WPBLOCK_COPY_TEXTDOMAIN ); ?></h1>
					<form method="post" action="options.php">
						<?php
							settings_fields( $this->option_group );
							do_settings_sections( $this->option_page_name );
							submit_button();
						?>
					</form>
				</div>
				<?php
			}
		);
	}

	/**
	 * Register Setting.
	 */
	public function admin_init() {

		register_setting(
			$this->option_group, //オプショングループ
			$this->option_name, //オプション名
			function( $option ) {
				$default_option = [
					'set-recommend-customizer-items' => false,
				];
				$new_option = [];
				foreach ( $default_option as $key => $value ) {
					$new_option[ $key ] = ! empty( $option[ $key ] ) ? 1 : $value;
				}
				return $new_option;
			}
		);


		// カスタマイズ設定のセクション.
		add_settings_section(
			$this->option_name . '_recommend', //セクションID.
			__( 'Recommended settings for Snow Monkey customization items', MGN_WPBLOCK_COPY_TEXTDOMAIN ), //テキスト.
			function() {
				?>
				<p>Snow Monkeyのカスタマイズ設定をこのスキンに適した設定にしたい場合は「自動設定を有効にする」にチェックを入れて下さい<br>（自動設定が有効になると、カスタマイズ設定画面の設定項目は無視されるのでご注意ください）</p>
				<?php
			},
			$this->option_page_name //どのページにセクションを表示するか.
		);

		// カスタマイズ設定のフィールド（有効にするかのチェックボックス）.
		add_settings_field(
			'set-recommend-customizer-items', //フィールドID.
			__( '自動設定を有効にする', MGN_WPBLOCK_COPY_TEXTDOMAIN ),
			function() {
				$input_key = 'set-recommend-customizer-items';
				?>
				<input type="checkbox" name="<?php echo $this->option_name; ?>[<?php echo $input_key; ?>]" value="1" <?php checked( 1, $this->get_option( $input_key ) ); ?>>
				<?php
			},
			$this->option_page_name, //どのオプションに表示するか
			$this->option_name . '_recommend' //どのセクションに表示するか
		);
	}

	/**
	 * Return option.
	 *
	 * @param string $key The option key.
	 * @return mixed
	 */
	protected function get_option( $key ) {
		$option = get_option( $this->option_name );
		return isset( $option[ $key ] ) ? (int) $option[ $key ] : false;
	}
}
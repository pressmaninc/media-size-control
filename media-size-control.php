<?php
/**
 * Plugin Name: Media Size Control
 * Description: Set a limit size of the upload files for each extension.
 * Version: 1.0.0
 * Author: PRESSMAN
 * Author URI: https://www.pressman.ne.jp
 * Text Domain: media-size-control
 * Domain Path: /languages
 *
 * @author    PRESSMAN
 * @link      https://www.pressman.ne.jp
 * @copyright Copyright (c) 2020, PRESSMAN
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 or higher
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Media_Size_Control {
	const OPTION_NAME = 'limit_mime_type';

	/**
	 * The single instance of the class.
	 *
	 * @var Media_Size_Control
	 */
	protected static $instance = null;

	/**
	 * Ensures only one instance of this class.
	 *
	 * @return Media_Size_Control
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * construct
	 */
	public function __construct() {
		// add option page
		add_action( 'admin_menu', [ $this, 'add_option_to_menu' ] );
		add_action( 'pre_update_option_' . self::OPTION_NAME, [ $this, 'store_serialized_value' ], 10, 2 );

		// restrict file size
		add_filter( 'wp_handle_upload', [ $this, 'restrict_upload_size' ], 10, 1 );
	}

	/**
	 * Add options to the navigation menu
	 */
	public function add_option_to_menu() {
		$ext = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'media-size-control', plugin_dir_url( __FILE__ ) . "css/media-size-control{$ext}.css" );

		// create new top-level menu
		add_menu_page( 'Media Size Control', __( 'Media Size Control', 'media-size-control' ), 'administrator', __FILE__, [
			$this,
			'require_option_page'
		] );

		// call register settings function
		add_action( 'admin_init', [ $this, 'register_settings_group' ] );
	}

	/**
	 * Load option.php
	 */
	public function require_option_page() {
		require_once( dirname( __FILE__ ) . '/include/optionpage.php' );
	}

	/**
	 * Register setting group
	 */
	public function register_settings_group() {
		register_setting( 'media-size-control-settings-group', self::OPTION_NAME );
	}

	/**
	 * Save the serialized value
	 * @param $new_value
	 * @param $old_value
	 *
	 * @return array
	 */
	public function store_serialized_value( $new_value, $old_value ) {
		$sanitize_new_value = sanitize_textarea_field( $new_value );
		$row_array = preg_split( '/\n|\r\n?/', $sanitize_new_value );
		$row_array = array_map( 'trim', $row_array );
		$row_array = array_filter( $row_array, 'strlen' );
		$row_array = array_values( $row_array );
		$mime_types = array();
		foreach ( $row_array as $row ) {
			list( $mime_type, $file_size ) = explode( ',', $row );
			$mime_types[ $mime_type ] = $file_size;
		}

		return $mime_types;
	}

	/**
	 * Limit the size of the files that can be uploaded
	 * @param $file
	 *
	 * @return mixed
	 */
	public function restrict_upload_size( $file ) {
		$mime_types = get_option( self::OPTION_NAME );
		if ( in_array( $file['type'], array_keys( $mime_types ) ) ) {
			$kb_to_byte         = 1024;
			$max_file_size_byte = $mime_types[ $file['type'] ] * $kb_to_byte;
			if ( $_FILES['async-upload']['size'] >= $max_file_size_byte ) {
				$file["error"] = sprintf( __( "The file size should not exceed %s kb", 'media-size-control' ), $mime_types[ $file['type'] ] );
			}
		}

		return $file;
	}
}

if ( is_admin() ) {
	Media_Size_Control::instance();
}
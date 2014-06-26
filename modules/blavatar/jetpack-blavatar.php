<?php

/*
Plugin Name: Blavatar
Plugin URL: http://wordpress.com/
Description:  Add an avatar for your blog. 
Version: 0.1
Author: Automattic

Released under the GPL v.2 license.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

class Jetpack_Blavatar {

	public $option_name = 'blavatar';
	public $module 		= 'blavatar';
	public $version 	= 1;

	/**
	 * Singleton
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance )
			$instance = new Jetpack_Blavatar;

		return $instance;
	}

	function __construct() {

		add_action( 'jetpack_modules_loaded', array( $this, 'jetpack_modules_loaded' ) );
		add_action( 'jetpack_activate_module_blavatar', array( $this, 'jetpack_module_activated' ) );
		add_action( 'jetpack_deactivate_module_blavatar', array( $this, 'jetpack_module_deactivated' ) );
		add_action( 'admin_menu',            array( $this, 'admin_menu_upload_blavatar' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		
		add_action( 'admin_print_styles-options-general.php', array( $this, 'add_admin_styles' ) );

	}

	/**
	 * Add a hidden upload page for people that don't like modal windows
	 */
	function admin_menu_upload_blavatar() {
 		$page_hook = add_submenu_page( 
 			null, 
 			__( 'Blavatar Upload', 'jetpack' ), 
 			'', 
 			'manage_options', 
 			'jetpack-blavatar-upload', 
 			array( $this, 'upload_blavatar_page' ) 
 		);
 	
 		add_action( "admin_head-$page_hook", array( $this, 'upload_balavatar_head' ) );
	}

	/**
	 * After all modules have been loaded.
	 */
	function jetpack_modules_loaded() {

		Jetpack::enable_module_configurable( $this->module );
		Jetpack::module_configuration_load( $this->module, array( $this, 'jetpack_configuration_load' ) );

	}
	/**
	 * Add styles to the General Settings 
	 */
	function add_admin_styles(){
		wp_enqueue_style( 'blavatar-admin' );
	}
	/**
	 * Add Styles to the Upload UI Page
	 *
	 */
	function upload_balavatar_head(){
		wp_enqueue_style( 'blavatar-admin' );
	}
	

	/**
	 * Runs when the Blavatar module is activated.
	 */
	function jetpack_module_activated() {
		// not sure yet what is suppoed to go here!
	}

	/**
	 * Runs when the Blavatar module is deactivated.
	 */
	function jetpack_module_deactivated() {
		// there are no options that need to be deleted One option that I see we could have is the default blavatar that is defined on per network
		// Jetpack_Options::delete_option( $this->option_name );
	}

	/**
	 * Direct the user to the Settings -> General
	 */
	function jetpack_configuration_load() {
		wp_safe_redirect( admin_url( 'options-general.php#blavatar' ) );
		exit;
	}

	/**
	 * Load on when the admin is initialized
	 * 
	 */
	function admin_init(){
		/* regsiter the styles and scripts */
		wp_register_script( 'blavatar-admin', plugin_dir_url( __FILE__ ). "js/blavatar-admin.js" , array( 'jquery' ) , $this->version, true );
		wp_register_script( 'blavatar-crop',  plugin_dir_url( __FILE__ ). "js/blavatar-crop.js"  , array( 'jquery', 'jcrop' ) , $this->version, true );
		wp_register_style( 'blavatar-admin' , plugin_dir_url( __FILE__ ). "blavatar-admin.css", array(), $this->version );
		
		add_settings_section(
		  $this->module,
		  '',
		  array( $this, 'blavatar_settings'),
		  'general'
		);

	}
	/**
	 * Add HTML to the General Settings
	 * 
	 */
	function blavatar_settings() { 
		
		$upload_blovatar_url = admin_url( 'options-general.php?page=jetpack-blavatar-upload' );
		$remove_blovatar_url = ''; // this could be an ajax url 
		
		wp_enqueue_script( 'blavatar-admin' );
		
		?>
		<div id="blavatar" class="blavatar-shell">
			<h3><?php echo esc_html_e( 'Blog Picture / Icon', 'jetpack'  ); ?></h3>
			<div class="blavatar-content postbox">
				<div class="blavatar-image">
				<?php if( has_blavatar() ) { ?>
					<?php get_blavatar(); ?>
				<?php } ?>
				</div>
				<div class="blavatar-meta">

				<?php if( has_blavatar() ) { ?>
				
					<a href="<?php echo esc_url( $upload_blovatar_url ); ?>" id="blavatar-update" class="button"><?php echo esc_html_e( 'Change the Blavatar', 'jetpack'  ); ?></a>
					<a href="<?php echo esc_url( $remove_blovatar_url ); ?>" id="blavatar-remove" class="button"><?php echo esc_html_e( 'Remove the Blavatar', 'jetpack'  ); ?></a>
				
				<?php } else { ?>
				
					<a href="<?php echo esc_url( $upload_blovatar_url ); ?>" id="blavatar-update" class="button"><?php echo esc_html_e( 'Add a Blavatar', 'jetpack' ); ?></a>
				
				<?php } ?>
				
					<div class="blavatar-info">
					<p><?php echo esc_html_e( 'Blavatar is a term we came up with by combining Blog and Avatar. Blavatars are used in a number of ways. It will be displayed as the favicon for your blog, which shows up in a browser’s address bar and on browser tabs.', 'jetpack' ); ?>
					</p>
					</div>

				</div>
			</div>
		</div>
		<?php 	
	}

	/**
	 * Hidden Upload Blavatar page for people that don't like modals
	 */
	function upload_blavatar_page() { ?>
		<div class="wrap">
			<?php require_once( dirname( __FILE__ ) . '/upload-blavatar.php' ); ?>
		</div>
		<?php
	}


	static function select_page() {
		// show the current blavatar
		// display the blavatar form to upload the image
		 ?>
		<form action="" method="post" enctype="multipart/form-data">

			<h3>Blog Picture / Icon</h3>
			<p>Upload a picture (<strong>jpeg</strong> or <strong>png</strong>) to be used as your blog image across WordPress.com. We will let you crop it after you upload.</p>
			
			<p><input name="avatarfile" id="avatarfile" type="file" /></p>
			<p class="submit">
				<input name="submit" value="Upload Image" type="submit" class="button button-primary button-large" /> or <a href="#">Cancel</a> and go back to the settings.
				<input name="step" value="2" type="hidden" />
			
				<?php wp_nonce_field( 'update-blavatar-2', '_nonce' ); ?>
			</p>
			</div>
		</form>
		<?php
	}

	static function crop_page() { 

		// handle the uploaded image
		// display the image image croppping funcunality

		wp_enqueue_script( 'blavatar-crop' );
		
		?>
			<h3><?php esc_html_e( 'Crop the image', 'jetpack' ); ?></h3>
			<form action="" method="post" enctype="multipart/form-data">

			<p class="submit">

				<input name="submit" value="<?php esc_attr_e('Crop Image'); ?>" type="submit" class="button button-primary button-large" /> or <a href="#">Cancel</a> and go back to the settings.
				<input name="step" value="3" type="hidden" />
			
				<?php wp_nonce_field( 'update-blavatar-3', '_nonce' ); ?>
			</p>
			</div>
		</form>
		
		<?php
	}

	static function all_done_page() { 

		?>
		<h1><?php echo esc_html__( 'All Done', 'jetpack' ); ?></h1>

		<?php esc_html_e( 'You have successfully uploaded an Blavatar', 'jetpack' ); ?>
		<?php
	}

}

Jetpack_Blavatar::init();

if( ! function_exists( 'has_blavatar' ) ) :
function has_blavatar( $blog_id = null ) {

	if( ! is_int( $blog_id ) )
		$blog_id = get_current_blog_id();

	$upload_dir = wp_upload_dir();
	return true;

	return file_exists( $upload_dir['basedir'] . '/blavatar/blavatar-'. md5( $blog_id ) );
}
endif;

if( ! function_exists( 'get_blavatar' ) ) :
function get_blavatar( $blog_id = null, $size = '96', $default = '', $alt = false ) {

	if( ! is_int( $blog_id ) )
		$blog_id = get_current_blog_id();

	$size  = esc_attr( $size );
	$class = "avatar avatar-$size";
	$alt = esc_attr( $alt );
	$src = esc_url( blavatar_url( $blog_id, 'img', $size, $default ) );
	
	$avatar = "<img alt='{$alt}' src='{$src}' class='$class' height='{$size}' width='{$size}' />";

	return apply_filters( 'get_avatar', $avatar, $blog_id, $size, $default, $alt );
}
endif; 

if( ! function_exists( 'blavatar_url' ) ) :
function blavatar_url( $blog_id = null, $size = '96', $default = false ) {
	$url = '';
	if( ! is_int( $blog_id ) )
		$blog_id = get_current_blog_id();


	return $url;

}
endif;



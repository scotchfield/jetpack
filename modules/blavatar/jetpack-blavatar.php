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

	public static $min_size = 512; // same as wp.com

	public static $accepted_file_types = array( 
		'image/jpg', 
		'image/jpeg', 
		'image/gif', 
		'image/png' 
	);

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
		self::$min_size = ( defined( 'BLAVATAR_MIN_SIZE' ) && is_int( BLAVATAR_MIN_SIZE ) ? BLAVATAR_MIN_SIZE : self::$min_size );
		
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
		wp_register_script( 'blavatar-admin', plugin_dir_url( __FILE__ ). "js/blavatar-admin.js" , array( 'jquery' ) , $this->version, true );
		wp_enqueue_style( 'blavatar-admin' );
	}
	/**
	 * Add Styles to the Upload UI Page
	 *
	 */
	function upload_balavatar_head(){

		wp_register_script( 'blavatar-crop',  plugin_dir_url( __FILE__ ). "js/blavatar-crop.js"  , array( 'jquery', 'jcrop' ) , $this->version, false);
		if( isset( $_REQUEST['step'] )  && $_REQUEST['step'] == 2 ){
			wp_enqueue_script( 'blavatar-crop' );
			wp_enqueue_style( 'jcrop' );
		}
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

			<h2><?php esc_html_e( 'Blog Icon', 'jetpack'); ?></h2>
			<p><?php esc_html_e( 'Upload a picture to be used as your site image. We will let you crop it after you upload.', 'jetpack' ); ?></p>

			
			<p><input name="blavatarfile" id="blavatarfile" type="file" /></p>
			<p><?php esc_html_e( 'The image needs to be at least', 'jetpack' ); ?> <strong><?php echo self::$min_size; ?>px</strong> <?php esc_html_e( 'in both width and height.', 'jetpack' ); ?></p>
			<p class="submit">
				<input name="submit" value="<?php esc_attr_e( 'Upload Image' , 'jetpack' ); ?>" type="submit" class="button button-primary button-large" /> or <a href="#">Cancel</a> and go back to the settings.
				<input name="step" value="2" type="hidden" />
			
				<?php wp_nonce_field( 'update-blavatar-2', '_nonce' ); ?>
			</p>
			</div>
		</form>
		<?php
	}



	static function crop_page() { 

		// handle the uploaded image
		$image = self::handle_file_upload( $_FILES['blavatarfile'] );
		$crop_data = get_option( 'blavatar_temp_data' );
		// display the image image croppping funcunality
		if( is_wp_error( $image ) ) { ?>
			<div id="message" class="updated error below-h2"><p> <?php echo esc_html( $image->get_error_message() ); ?> </p></div> 
			<?php
			// back to step one
			$_POST = array();
			self::select_page();
			return;
		}
		// lets makre sure that the Javascript ia also loaded
		wp_localize_script( 'blavatar-crop', 'Blavatar_Crop_Data', array( 
			'init_x' => 0,
			'init_y' => 0,
			'init_size' => 128,
			'min_size' 	  => 128
		) );
		?>
		<h2><?php esc_html_e( 'Crop the image', 'jetpack' ); ?></h2>
		<div class="blavatar-crop-shell">
			<form action="" method="post" enctype="multipart/form-data">
			<p><input name="submit" value="<?php esc_attr_e( 'Crop Image', 'jetpack' ); ?>" type="submit" class="button button-primary button-large" /> or <a href="#">Cancel</a> and go back to the settings.</p>
			<div class="blavatar-crop-preview-shell">

			<h3><?php esc_html_e( 'Preview', 'jetpack' ); ?></h3>

				<strong><?php esc_html_e( 'As your favicon', 'jetpack' ); ?></strong>
				<div class="blavatar-crop-favicon-preview-shell">
					<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ). "browser.png" ); ?>" class="blavatar-browser-preview" width="172" height="79" alt="<?php esc_attr_e( 'Browser Chrome' , 'jetpack'); ?>" />
					<div class="blavatar-crop-preview-favicon">
						<img src="<?php echo esc_url( $image[0] ); ?>" id="preview-favicon" alt="<?php esc_attr_e( 'Preview Favicon' , 'jetpack'); ?>" />
					</div>
					<span class="blavatar-browser-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				</div>

				<strong><?php esc_html_e( 'As a mobile icon', 'jetpack' ); ?></strong>
				<div class="blavatar-crop-preview-homeicon">
					<img src="<?php echo esc_url( $image[0] ); ?>" id="preview-homeicon" alt="<?php esc_attr_e( 'Preview Home Icon' , 'jetpack'); ?>" />
				</div>
			</div>
			<img src="<?php echo esc_url( $image[0] ); ?>" id="crop-image" class="blavatar-crop-image"
				width="<?php echo esc_attr( $crop_data['resized_image_data'][0] ); ?>" 
				height="<?php echo esc_attr( $crop_data['resized_image_data'][1] ); ?>" 
				alt="<?php esc_attr_e( 'Image to be cropped', 'jetpack' ); ?>" />
		
			<input name="step" value="3" type="hidden" />
			<input type="hidden" id="crop-x" name="crop-x" />
			<input type="hidden" id="crop-y" name="crop-y" />
			<input type="hidden" id="crop-width" name="crop-w" />
			<input type="hidden" id="crop-height" name="crop-h" />
		
			<?php wp_nonce_field( 'update-blavatar-3', '_nonce' ); ?>

			</form>
		</div>
		<?php
	}

	static function all_done_page() { 

		$crop_data = get_option( 'blavatar_temp_data' );

		
		// 
		// Delete the 2 attacments
		// 
		
		?>
		<h1><?php echo esc_html__( 'All Done', 'jetpack' ); ?></h1>

		<?php esc_html_e( 'You have successfully uploaded an Blavatar', 'jetpack' ); ?>
		<?php
	}

	/*static function crop_blavatar( $image_id, $width, $height,   ) {

	}
	*/

	/**
	 * Handle the uploaded image
	 */
	static function handle_file_upload( $uploaded_file ) {
		
		// check that the image accuallt is a file with size
		if( !isset( $uploaded_file ) || ($uploaded_file['size'] <= 0 ) ) {
			return new WP_Error( 'broke', __( "Please upload a file.", 'jetpack' ) );
		} 

		$arr_file_type = wp_check_filetype( basename( $uploaded_file['name'] ) );
		$uploaded_file_type = $arr_file_type['type'];
		if( ! in_array( $uploaded_file_type, self::$accepted_file_types ) ) {
			
			return new WP_Error( 'broke', __( "The file that you uploaded is not an accepted file type. Please try again.", 'jetpack' ) );
		}

        $image = wp_handle_upload( $uploaded_file, array( 'test_form' => false ) );

        if(  is_wp_error( $image ) ) {
  			// this should contain the error message returned from wp_handle_upload
        	return $image;
        }
        
        // Lets try to crop the image into smaller files. 
        // We will be doing this later so it is better if it fails now.
        $image_edit = wp_get_image_editor( $image['file'] );
        if ( is_wp_error( $image_edit ) ) {
        	// this should contain the error message from WP_Image_Editor 
        	return $image_edit;
        }

        $image_size = getimagesize( $image['file'] );

        // height 
        if( $image_size[0] >= self::$min_size && $image_size[0] >= self::$min_size ) {
        	if( $image_size[0] > self::$min_size ){

        	}
        }
        // Save the uploaded image as an attachment for later use
        $attachment = array(
            'post_mime_type' => $uploaded_file_type,
            'post_title' 	 => __( 'Temporary Large Image for Blog Image', 'jetpack' ),
            'post_content' 	 => '',
            'post_status' 	 => 'inherit'
        );
        // Save the image as an attachment for later use. 
        $large_attachment_id = wp_insert_attachment( $attachment, $image['file'] );
        
		// Lets resize the image so that the user is trying to resize a image that in the view
        $image_edit->resize( 512, 512, false );
        $dir = wp_upload_dir();

        $filename = $image_edit->generate_filename( 'temp',  $dir['path'] , null );
        $image_edit->save( $filename );

        $attachment = array(
            'post_mime_type' => $uploaded_file_type,
            'post_title' 	 => __( 'Temporary Small Image for Blog Image', 'jetpack' ),
            'post_content' 	 => '',
            'post_status' 	 => 'inherit'
        );
        // Save the image as an attachment for later use. 
        $resized_attach_id = wp_insert_attachment( $attachment, $filename );
        $resized_image_size = getimagesize( $filename ); 
        // Save all of this into the the database for that we can work with it later.
        update_option( 'blavatar_temp_data', array( 
        		'large_image_attachment_id'  => $large_attachment_id,
        		'large_image_data'			 => $image_size,
        		'resized_image_attacment_id' => $resized_attach_id,
        		'resized_image_data'		 => $resized_image_size
        		) );
        
        return wp_get_attachment_image_src( $resized_attach_id );
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



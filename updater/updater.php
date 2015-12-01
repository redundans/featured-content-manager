<?php

// this is the URL our updater / license checker pings. This should be the URL of the site with fcm installed
define( 'FCM_STORE_URL', 'https://plugins.klandestino.se' );

// the name of your product. This should match the download name in fcm exactly
define( 'FCM_PRODUCT_NAME', 'Featured Content Manager' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function fcm_settings_menu() {
	if ( is_main_site() ) :
		add_options_page( 'Featured content manager', 'Featured content', 'manage_options', 'featured-content-manager-settings', 'fcm_settings_page' );
	endif;
}
add_action('admin_menu', 'fcm_settings_menu');

function fcm_admin_notice() {
	if ( is_main_site() ) :
		$data 	= get_option( 'fcm_license_data' );
		$status = $data->license;
		if ( $status != 'valid' ) :
			echo '<div class="error"><p>You need to enter a valid license for <a href="/wp-admin/options-general.php?page=featured-content-manager-settings">Featured Content Manager</a></p></div>';
		endif;
	endif;
}
add_action( 'admin_notices', 'fcm_admin_notice' );

function fcm_settings_page() {
	$license 	= get_option( 'fcm_license_key' );
	$data 	= get_option( 'fcm_license_data' );
	$status = $data->license;
	?>
	<div class="wrap">
		<h2><?php _e( 'Featured Content Manager License Options', 'featured-content-manager' ); ?></h2>

		<form method="post" action="options.php">

			<?php settings_fields('fcm_license'); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'License Key', 'featured-content-manager' ); ?>
						</th>
						<td>
							<input id="fcm_license_key" name="fcm_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="fcm_license_key"><?php _e( 'Enter your license key', 'featured-content-manager' ); ?></label>
						</td>
					</tr>
					<?php if( false !== $license && ! empty( $license ) && ! empty( $status ) && $status == 'valid' ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<span style="color: green;"><?php _e( 'License active', 'featured-content-manager' ); ?></span>
							</th>
							<td>
								<span>Expires: <?php echo $data->expires; ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'Deactivate License', 'featured-content-manager' ); ?>
							</th>
							<td>
								<?php wp_nonce_field( 'fcm_nonce', 'fcm_nonce' ); ?>
								<input type="submit" class="button-secondary" name="fcm_license_deactivate" value="<?php _e( 'Deactivate License', 'featured-content-manager' ); ?>"/>
							</td>
						</tr>
					<?php } else { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<span style="color: red;"><?php _e( 'License inactive', 'featured-content-manager' ); ?></span>
							</th>
							<?php if ( empty( $license ) ) { ?>
								<td>
									<span>Please enter your license key</span>
								</td>
							<?php } elseif ( $data->error == 'no_activations_left' ) { ?>
								<td>
									<span>You've reached your max number of active sites with this license. Upgrade your license at: <a href="https://plugins.klandestino.se" target="_blank">https://plugins.klandestino.se</a></span>
								</td>
							<?php } elseif ( $data->error == 'missing' ) { ?>
								<td>
									<span>Wrong license key, please check that you used the correct one.</span>
								</td>
							<?php } elseif ( strtotime( $data->expires ) < time() ) { ?>
								<td>
									<span>License has expired (at: <?php echo $data->expires; ?>)</span>
								</td>
							<?php } elseif ( $data->error ) { ?>
								<td>
									<span>Something went wrong, please try again or contact support at <a href="https://plugins.klandestino.se" target="_blank">https://plugins.klandestino.se</a> (error message: <?php echo $data->error; ?>)</span>
								</td>
							<?php } ?>
						</tr>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'Activate License', 'featured-content-manager' ); ?>
							</th>
							<td>
								<?php wp_nonce_field( 'fcm_nonce', 'fcm_nonce' ); ?>
								<input type="submit" class="button-secondary" name="fcm_license_activate" value="<?php _e( 'Activate License', 'featured-content-manager' ); ?>"/>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

		</form>
	</div>
	<?php
}

function fcm_register_option() {
	// creates our settings in the options table
	register_setting('fcm_license', 'fcm_license_key', 'fcm_sanitize_license' );
}
add_action('admin_init', 'fcm_register_option');

function fcm_sanitize_license( $new ) {
	$old = get_option( 'fcm_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'fcm_license_data' ); // new license has been entered, so must reactivate
	}
	return $new;
}



/************************************
* this illustrates how to activate
* a license key
*************************************/

function fcm_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['fcm_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'fcm_nonce', 'fcm_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'fcm_license_key' ) );

		if ( empty( $license ) ) {
			$license = trim( $_POST['fcm_license_key'] );
		}


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( FCM_PRODUCT_NAME ), // the name of our product in fcm
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( FCM_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'fcm_license_data', $license_data );

	}
}
add_action('admin_init', 'fcm_activate_license');


/***********************************************
* Illustrates how to deactivate a license key.
* This will descrease the site count
***********************************************/

function fcm_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['fcm_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'fcm_nonce', 'fcm_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'fcm_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( FCM_PRODUCT_NAME ), // the name of our product in fcm
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( FCM_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' )
			update_option( 'fcm_license_data', $license_data );

	}
}
add_action('admin_init', 'fcm_deactivate_license');


/************************************
* this illustrates how to check if
* a license key is still valid
* the updater does this for you,
* so this is only needed if you
* want to do something custom
*************************************/

function fcm_check_license() {

	global $wp_version;

	$license = trim( get_option( 'fcm_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( FCM_PRODUCT_NAME ),
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( FCM_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		echo 'valid'; exit;
		// this license is still valid
	} else {
		echo 'invalid'; exit;
		// this license is no longer valid
	}
}

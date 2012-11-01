<?php
/*
Plugin Name: Captain Analytics
Plugin URI: http://captaintheme.com/plugins/analytics/
Description: Easily add Google Analytics to your Wordpress Site.
Author: Captain Theme
Author URI: http://captaintheme.com
Version: 1.0
Text Domain: ctanalytics
License: GNU GPL V2
*/

// Credit to Jeff Star (perishablepress.com/) for implementation inspiration

// Google Analytics Tracking Code (ga.js) (http://code.google.com/apis/analytics/docs/tracking/asyncUsageGuide.html)
function ctanalytics_google_analytics_tracking_code() {
	$ctanalytics_options = get_option('ctanalytics_options'); 
	if ( $ctanalytics_options['ctanalytics_enable'] ) {
		ob_start();
	?>
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', '<?php echo $ctanalytics_options['ctanalytics_id']; ?>']);
		  _gaq.push(['_trackPageview']);
		
		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
	<?php
		echo ob_get_clean();
	}
}
// include GA tracking code before the closing </head> tag
add_action( 'wp_head', 'ctanalytics_google_analytics_tracking_code' );


// display settings link on plugin page
function ct_analytics_action_links($links, $file) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$ctanalytics_links = '<a href="'. get_admin_url() .'options-general.php?page=captain-analytics/captain-analytics.php">'. __( 'Settings', 'ctanalytics' ) .'</a>';
		array_unshift( $links, $ctanalytics_links );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'ct_analytics_action_links', 10, 2 );


// remove plugin settings after deletion
function ctanalytics_delete_plugin_options() {
	delete_option( 'ctanalytics_options' );
}
register_uninstall_hook( __FILE__, 'ctanalytics_delete_plugin_options' );


// define default settings
function ctanalytics_add_defaults() {
	$tmp = get_option( 'ctanalytics_options' );
	if ( ( $tmp['default_options'] == '1' ) || ( !is_array( $tmp ) ) ) {
		$arr = array( 'ctanalytics_id' => 'UA-XXXXX-X' );
		update_option( 'ctanalytics_options', $arr );
	}
}
register_activation_hook( __FILE__, 'ctanalytics_add_defaults' );


// whitelist settings
function ctanalytics_init() {
	register_setting( 'ctanalytics_options', 'ctanalytics_options', 'ctanalytics_validate_options' );
}
add_action( 'admin_init', 'ctanalytics_init' );


// sanitize and validate input
function ctanalytics_validate_options( $input ) {
	if ( !isset( $input['ctanalytics_enable'] ) )
		$input['ctanalytics_enable'] = null;

	$input['ctanalytics_enable'] = ( 1 == $input['ctanalytics_enable'] ? 1 : 0 );
	$input['ctanalytics_id'] = wp_filter_nohtml_kses( $input['ctanalytics_id'] );

	return $input;
}


// add the options page
function ctanalytics_add_options_page() {
	add_options_page( __( 'Captain Analytics', 'ctanalytics' ), __( 'Captain Analytics', 'ctanalytics' ), 'manage_options', __FILE__, 'ctanalytics_render_form' );
}
add_action( 'admin_menu', 'ctanalytics_add_options_page' );


// create the options page
function ctanalytics_render_form() {
	ob_start();
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Captain Analytics Settings', 'ctanalytics' ) ?></h2>
		<p><?php printf( __( 'Enter the UA Code for your Google Anayltics Property below. Need Help? View %sCaptain Analytics Documentation%s.', 'ctanalytics' ), '<a href="' . esc_url( 'http://captaintheme.com/docs/captain-analytics-documentation' ) . '">', '</a>' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'ctanalytics_options' ); ?>
			<?php $ctanalytics_options = get_option( 'ctanalytics_options' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row"><label class="description" for="ctanalytics_options[ctanalytics_id]"><?php _e( 'Property ID (UA Code)', 'ctanalytics' ) ?></label></th>
					<td><input type="text" size="20" maxlength="20" name="ctanalytics_options[ctanalytics_id]" value="<?php echo $ctanalytics_options['ctanalytics_id']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label class="description" for="ctanalytics_options[ctanalytics_enable]"><?php _e( 'Enable Google Analytics', 'ctanalytics' ) ?></label></th>
					<td>
						<input name="ctanalytics_options[ctanalytics_enable]" type="checkbox" value="1" <?php if (isset($ctanalytics_options['ctanalytics_enable'])) { checked('1', $ctanalytics_options['ctanalytics_enable']); } ?> /> 
						<?php _e('Include the GA Tracking Code on your site?') ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label class="description" for="ctanalytics_options[default_options]"><?php _e( 'Restore Default Settings', 'ctanalytics' ) ?></label></th>
					<td>
						<input name="ctanalytics_options[default_options]" type="checkbox" value="1" <?php if ( isset( $ctanalytics_options['default_options'] ) ) { checked( '1', $ctanalytics_options['default_options'] ); } ?> /> 
						<?php _e( 'Restore defaults upon plugin deactivation/reactivation', 'ctanalytics' ) ?><br /><em><?php _e( 'Leave option unchecked to remember your settings', 'ctanalytics' ) ?></em>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings') ?>" />
			</p>
		</form>
	</div>
<?php
	echo ob_get_clean();
}

?>
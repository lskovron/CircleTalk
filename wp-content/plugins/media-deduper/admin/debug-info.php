<?php
/**
 * Debug Info Section file.
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

global $media_deduper;
// set the value.
?>
<h2><?php esc_html_e( 'Debug Info', 'media-deduper' ); ?></h2>
<p style="margin:1rem 0;"><?php esc_html_e( 'Please copy and paste the info below if submitting a support request.', 'media-deduper' ); ?></p>
<code id="debug-code" style="margin:1rem 0;padding:1rem;display:block;white-space:pre-wrap;"><?php esc_html_e( 'WP Info', 'media-deduper' ); ?>

	<?php esc_html_e( 'Site URL', 'media-deduper' ); ?>: <?php echo esc_html( get_site_url() ); ?>

	<?php esc_html_e( 'Version', 'media-deduper' ); ?>: <?php echo esc_html( bloginfo( 'version' ) ); ?>

	<?php esc_html_e( 'IP Address', 'media-deduper' ); ?>: <?php echo esc_html( gethostbyname( gethostname() ) ); ?>

	<?php esc_html_e( 'Active Theme', 'media-deduper' ); ?>: <?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?>

	<?php esc_html_e( 'Active Theme Version', 'media-deduper' ); ?>: <?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?>
	

<?php esc_html_e( 'Active Plugins', 'media-deduper' ); ?>:
	<?php
	$plugin_details = get_plugins();
	$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

	if ( is_multisite() ) {
		// get active plugins for the network
		$network_plugins = get_site_option( 'active_sitewide_plugins' );
		if ( $network_plugins ) {
			$network_plugins = array_keys( $network_plugins );
			$active_plugins = array_merge( $active_plugins, $network_plugins );
		}
	}

	foreach ( $active_plugins as $key => $plugin ) {
		echo esc_html( $plugin_details[ $plugin ]['Name'] ) . ' | Version ' . esc_html( $plugin_details[ $plugin ]['Version'] ) . "\n";
	}
	?>

<?php esc_html_e( 'Media Deduper Info', 'media-deduper' ); ?>
	
	<?php esc_html_e( 'Version', 'media-deduper' ); ?>: <?php echo esc_html( $media_deduper::VERSION ); ?>
</code>

<?php
/**
 * Menu Settings Page file.
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

// The media deduper settings page.
$mdd_settings = new CSHP_Settings_Page( __( 'Media Deduper', 'media-deduper' ), __( 'Media Deduper', 'media-deduper' ), 'manage_options', 'mdd' );

// Adding the general sections.
$general_section = $mdd_settings->add_settings_section( 'general_section', __( 'General', 'media-deduper' ) );
	// Adding the general section settings Disable Duplicate Upload Blocking? field.
	$general_section->add_settings_field(
		array(
			'id'    => 'disable_block_duplicate_uploads',
			'label' => __( 'Disable Duplicate Upload Blocking?', 'media-deduper' ),
			'description' => __( 'Check this box if you do NOT wish Media Deduper to prevent duplicate uploads as they happen.', 'media-deduper' ),
			'field_type' => 'checkbox',
		)
	);

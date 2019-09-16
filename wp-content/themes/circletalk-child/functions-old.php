<?php
	
	function themeblvd_header_top_default() {

	if ( ! themeblvd_has_header_info() ) {
		return;
	}
	?>
	<div class="header-top">
		<div class="wrap clearfix">

			<?php themeblvd_header_text(); ?>

			<ul class="header-top-nav list-unstyled">

				<?php if ( themeblvd_get_option('searchform') == 'show' ) : ?>
					<li><?php themeblvd_search_popup(); ?></li>
				<?php endif; ?>

				<?php if ( themeblvd_installed('wpml') && themeblvd_supports('plugins', 'wpml') && get_option('tb_wpml_show_lang_switcher', '1') ) : ?>
					<li><?php do_action('icl_language_selector'); ?></li>
				<?php endif; ?>

			</ul>

		</div><!-- .wrap (end) -->
	</div><!-- .header-above (end) -->
	<?php
}
	
/*-------------------------------------------------------*/
/* Run Theme Blvd framework (required)
/*-------------------------------------------------------*/

require_once( get_template_directory() . '/framework/themeblvd.php' );

/*-------------------------------------------------------*/
/* Start Child Theme
/*-------------------------------------------------------*/

/**
 * Move header menu to the right of the logo
 */
remove_action('themeblvd_header_menu', 'themeblvd_header_menu_default');
/**
 * Move responsive main menu toggle
 */
remove_action( 'themeblvd_header_content', 'themeblvd_responsive_menu_toggle' );
add_action( 'themeblvd_header_addon', 'themeblvd_responsive_menu_toggle' );

/*-------------------------------------------------------*/
/* Define Widget Area/Sidebar Location
/* Docs: http://dev.themeblvd.com/tutorial/addremove-widget-area-location/
/*-------------------------------------------------------*/
 

themeblvd_add_sidebar_location( 'header_widget', 'Header Widget', 'collapsible' );


/*-------------------------------------------------------*/
/* Display Widget Area/Sidebar Location 
/* Docs: http://dev.themeblvd.com/tutorial/addremove-widget-area-location/
/*-------------------------------------------------------*/


function header_addon_widget() {
	?>
	
		<div class="header-right">
			<div class="header-widget">
				<?php themeblvd_display_sidebar( 'header_widget' );?>
			</div>
		</div>
		<div style="clear: right;"></div>
		<?php themeblvd_header_menu_default();?>
		
	
	<?php
}


add_action('themeblvd_header_addon','header_addon_widget');

/*-------------------------------------------------------*/
/* Shortcode for the themeblvd social icon
/*-------------------------------------------------------*/

/* use [socialwidget] anywhere on the site for the icons to appear*/

function orme_social() {
    return '<div class="social">'.themeblvd_contact_bar().'</div>';
}	
	
add_shortcode( 'socialwidget', 'orme_social' );

/*-------------------------------------------------------*/
/* Add custom field to right sidebar
/*-------------------------------------------------------*/

function right_sidebar_content( $position ){
	global $post;
	if( $position == 'right'  ) {
		?>
		<?php the_field('sidebar_image'); ?>
		<?php
		} 
		else {
			echo '';
	}
}
add_action( 'themeblvd_fixed_sidebar_after', 'right_sidebar_content' );
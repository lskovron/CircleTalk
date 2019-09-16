<?php
	
	function themeblvd_header_top_default() {

	if ( ! themeblvd_has_header_info() ) {
		return;
	}

	$icons = themeblvd_get_option('social_media');
	?>
	<div class="header-top">
		<div class="wrap clearfix">

			<?php themeblvd_header_text(); ?>

			<?php if ( themeblvd_get_option('searchform') == 'show' || themeblvd_do_cart() || $icons || themeblvd_do_lang_selector() ) : ?>

				<ul class="header-top-nav list-unstyled">

					<?php if ( themeblvd_get_option('searchform') == 'show' ) : ?>
						<li class="top-search"><?php themeblvd_floating_search_trigger(); ?></li>
					<?php endif; ?>

					<?php if ( themeblvd_do_cart() ) : ?>
						<li class="top-cart"><?php themeblvd_cart_popup_trigger(); ?></li>
					<?php endif; ?>

					
					<?php if ( themeblvd_do_lang_selector() ) : ?>
						<li class="top-wpml"><?php do_action('icl_language_selector'); ?></li>
					<?php endif; ?>

				</ul>

			<?php endif; ?>

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

function circle_social() {
    return '<div class="social">'.themeblvd_contact_bar().'</div>';
}	
	
add_shortcode( 'socialwidget', 'circle_social' );


/*-------------------------------------------------------*/
/* Add custom field to right sidebar
/*-------------------------------------------------------*/

function right_sidebar_content( $position ){
	global $post;
	if( $position == 'right'  ) {
		?>
		<div class="sidebar-image">
		<?php the_field('sidebar_image'); ?>
		</div>
		<?php
		} 
		else {
			echo '';
	}
}
add_action( 'themeblvd_fixed_sidebar_after', 'right_sidebar_content', 1 );


 
/*-------------------------------------------------------*/
/* Filter in custom logo URL for sticky menu
/*-------------------------------------------------------*/
function my_sticky_logo_uri() {
	return get_bloginfo('stylesheet_directory') . '/assets/images/circletalk-logo-sticky.png';
}
add_filter( 'themeblvd_sticky_logo_uri', 'my_sticky_logo_uri' );

add_filter('widget_text', 'do_shortcode');

// Remove SendGrid dashboard widget
function remove_dashboard_meta() {
	remove_meta_box( 'sendgrid_statistics_widget', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'remove_dashboard_meta', 99 );


/*-------------------------------------------------------*/
/* Get upcoming events shortcode
/*-------------------------------------------------------*/

add_shortcode('events','ct_events');

function ct_events($atts){

	extract( shortcode_atts( array( 'icon' => true ), $atts ) );

	$args = array(
		'post_type' => 'tribe_events',
		'post_status' => 'publish'	
	);
	$events_query = new WP_Query($args);
	$events_html = '';
	ob_start();
	if($events_query->have_posts()): while($events_query->have_posts()): $events_query->the_post(); ?>
	<div class='row' style='padding:10px 0;'>
		<?php if($icon): ?><div class='col-sm-6' style='text-align:right;'>
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/calendar.png">
		</div><?php endif; ?>
		<div class='<?php if($icon): echo "col-sm-6"; else: echo "col-xs-12"; endif; ?>' style='font-weight:bold;padding-top:10px;'>
			<?php
			echo get_the_title().'<br>';
			// echo tribe_get_start_date().' - '.tribe_get_end_date().'<br>';
			// if(tribe_get_venue( get_the_ID() )): echo tribe_get_venue( get_the_ID() ).', '.tribe_get_city( get_the_ID() ).' '.tribe_get_state( get_the_ID() ).'<br>'; endif;
			echo tribe_get_start_date( get_the_ID() ).'<br>';
			echo '<a href="'.get_the_permalink().'" style="font-weight:bold;text-decoration:underline;">Read More >>></a>';
			?>
		</div>
	</div>
	<?php endwhile; 
	else: ?>
	<p style='text-align:center;font-style:italic;'>No upcoming trainings</p>
	<?php  endif;
	return ob_get_clean();
}

/*-------------------------------------------------------*/
/* Add google analytics tag and facebook pixel
/*-------------------------------------------------------*/

add_action('wp_head','tracking_codes');

function tracking_codes(){ ?>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-81409001-4"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-81409001-4');
</script>

<!-- Facebook Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '1873493352706666');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=1873493352706666&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->


<?php }

add_action('wp_head','ct_favicon');

function ct_favicon() { ?>
<link rel="shortcut icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/ct-favicon.png"/>
<?php }



//allow shortcodes in widget
add_filter('widget_text', 'do_shortcode');


//custom thank you page redirect
// add_action( 'woocommerce_thankyou', 'bbloomer_redirectcustom');
function bbloomer_redirectcustom( $order_id ){
    $order = wc_get_order( $order_id );
  
    $url = 'https://circletalk.org/thank-you';
  
    if ( $order->status != 'failed' ) {
        wp_safe_redirect( $url );
        exit;
    }
}





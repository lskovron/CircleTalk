<?php
/**
 * Template Name: Custom Layout
 *
 * WARNING: This template file is a core part of the
 * Theme Blvd WordPress Framework. It is advised
 * that any edits to the way this file displays its
 * content be done with via actions, filters, and
 * template parts.
 *
 * @author     Jason Bobich <info@themeblvd.com>
 * @copyright  2009-2017 Theme Blvd
 * @package    Jump_Start
 * @subpackage Theme_Blvd
 * @since      Theme_Blvd 2.0.0
 */

get_header();
?>

<div id="custom-main" <?php themeblvd_main_class(); ?> role="main">

	<?php themeblvd_content_top(); ?>

	<?php if ( has_action( 'themeblvd_builder_content' ) ) : ?>

		<?php
		/**
		 * Fires for the content of a custom layout.
		 *
		 * @hooked themeblvd_builder_layout - 10
		 *
		 * @since Theme_Blvd 2.0.0
		 *
		 * @param string Context of custom layout.
		 */
		do_action( 'themeblvd_builder_content', 'main' );
		?>

	<?php else : ?>

		<div class="alert alert-warning">

			<p>
				<?php
				printf(
					themeblvd_get_local( 'no_builder_plugin' ),
					'<a href="https://wordpress.org/extend/plugins/theme-blvd-layout-builder" target="_blank">Theme Blvd Layout Builder</a>'
				);
				?>
			</p>

		</div>

	<?php endif; ?>

	<?php themeblvd_content_bottom(); ?>

	<section class='custom-footer'>
		<div class='container'>
			<div class='row'>
				<div class='col-sm-3'>
					<div class='headshot text-center'>
						<img src='https://www.circletalk.org/wp-content/uploads/2018/09/headshot.jpeg' style="width:auto;">
						<p style="font-size:24px;"><strong>Deborah Skovron</strong><br>Director / Creative Director</p>
					</div>
				</div>
				<div class='col-sm-9'>
					<div class='text-center'>
						<h2>Let's Talk!</h2>
						<p>Interested in learning more about CircleTalk or getting involved? We would love to hear from you. In building a better future for aging adults, we value all the ideas and feedback that you provide. Even if you just want to say hi, please reach out!</p>
						<?php echo do_shortcode('[gravityform id=8 title=false description=false]'); ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class='footer-mc-signup'>
		<div class='container'>
			<div class='row'>
				<div class='col-xs-12'>
					<div class='text-center'>
						<h3 style='color:white;'>Stay updated on how we’re transforming senior communities</h3>
						<!-- Begin MailChimp Signup Form -->
					<!-- 	<link href="//cdn-images.mailchimp.com/embedcode/horizontal-slim-10_7.css" rel="stylesheet" type="text/css"> -->
						<div id="mc_embed_signup">
						<form action="https://circletalk.us11.list-manage.com/subscribe/post?u=567052dbcd205e1eff83bd6e7&amp;id=c94c6f180b" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
						    <div id="mc_embed_signup_scroll">
							
							<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
						    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
						    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_567052dbcd205e1eff83bd6e7_c94c6f180b" tabindex="-1" value=""></div>
						    <div class="text-center"><input type="submit" value="I'm in!" name="subscribe" id="mc-embedded-subscribe" class="btn-default btn-blue"></div>
						    </div>
						</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

<!--End mc_embed_signup-->

</div><!-- #elements -->

<?php get_footer(); ?>

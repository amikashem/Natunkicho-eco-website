<?php
/**
 * Job listing in the loop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
?>

<li <?php job_listing_class(); ?>>

	<a href="<?php the_job_permalink(); ?>">

		<?php the_company_logo(); ?>

		<div class="position">

			<h3><?php wpjm_the_job_title(); ?></h3>

			<div class="company">

				<?php the_company_name( '<strong>', '</strong>' ); ?>

				<?php the_company_tagline( '<span class="tagline">', '</span>' ); ?>

			</div>

			<div class="location">
				<?php the_job_location( false ); ?>
			</div>

			<ul class="meta">

				<?php if ( get_option( 'job_manager_enable_types' ) ) { ?>

					<?php $types = wpjm_get_the_job_types(); ?>

					<?php if ( ! empty( $types ) ) : ?>

						<?php foreach ( $types as $type ) : ?>

							<li class="job-type <?php echo esc_attr( sanitize_title( $type->slug ) ); ?>">
								<?php echo esc_html( $type->name ); ?>
							</li>

						<?php endforeach; ?>

					<?php endif; ?>

				<?php } ?>

				<li class="date">
					<?php the_job_publish_date(); ?>
				</li>

			</ul>

		</div>

	</a>

	<button 
		class="nk-save-job-btn"
		data-job="<?php echo get_the_ID(); ?>"
	>
		♡ Save Job
	</button>
	<?php echo do_shortcode('[nk_apply_job id="' . get_the_ID() . '"]'); ?>
	<?php echo do_shortcode('[nk_easy_apply]'); ?>

</li>
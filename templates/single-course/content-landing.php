<?php
/**
 * Template for displaying content of landing course.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/single-course/content-landing.php.
 *
 * @author   ThimPress
 * @package  Learnpress/Templates
 * @version  3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();
?>

<?php
/**
 * @deprecated
 */
do_action( 'learn_press_before_content_landing' );

/**
 * @since 3.x.x
 */
do_action( 'learn-press/before-content-landing' );
?>

<div class="course-landing-summary">

	<?php
	/**
	 * @deprecated
	 */
	do_action( 'learn_press_content_landing_summary' );

	/**
	 * @since 3.0.0
	 */
	do_action( 'learn-press/content-landing-summary' );
	?>

</div>

<?php

/**
 * @since 3.x.x
 */
do_action( 'learn-press/after-content-landing' );

/**
 * @deprecated
 */
do_action( 'learn_press_after_content_landing' );
?>

<?php
/**
 * Template for displaying basic stats in user profile dashboard.
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 3.x.x
 */

defined( 'ABSPATH' ) or die;
$profile = LP_Profile::instance();

if ( ! $profile->get_user() ) {
	return;
}

?>

<ul class="instructor-stats">
    <li>
        <label><?php esc_html_e( 'Students', 'learnpress' ); ?></label>
        <p><?php echo learn_press_count_instructor_students( $profile->get_user_id() ); ?></p>
    </li>

    <li>
        <label><?php esc_html_e( 'Courses', 'learnpress' ); ?></label>
        <p><?php echo learn_press_count_instructor_courses( $profile->get_user_id() ); ?></p>
    </li>
</ul>

<h3><?php printf( __( 'Courses by %s', 'learnpress' ), learn_press_get_profile_display_name( $profile->get_user() ) ); ?></h3>
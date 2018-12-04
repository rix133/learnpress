<?php
/**
 * Template for displaying lesson item content in single course.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/single-course/content-item-lp_lesson.php.
 *
 * @author   ThimPress
 * @package  Learnpress/Templates
 * @version  3.0.0
 */

/**
 * Prevent loading this file directly
 *
 * @var LP_Course_Item $itemx
 */
defined( 'ABSPATH' ) || exit();

$item = LP_Global::course_item();
?>

<div <?php learn_press_content_item_summary_class(); ?>>

	<?php

	do_action( 'learn-press/vm/before-content-item-summary/' . $item->get_item_type() );

	do_action( 'learn-press/vm/content-item-summary/' . $item->get_item_type() );

	do_action( 'learn-press/vm/after-content-item-summary/' . $item->get_item_type() );

	?>

    <button v-if="canCompleteItem(item)" class="button-complete" type="button" @click="_completeItem($event)" :disabledx="currentItem.completed">
        <template v-if="currentItem.completed">{{'<?php esc_html_e( 'Completed', 'learnpress' ); ?>'}}</template>
        <template v-else>{{'<?php esc_html_e( 'Complete', 'learnpress' ); ?>'}}</template>
    </button>
</div>

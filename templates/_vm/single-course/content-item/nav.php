<?php
/**
 * Template for displaying next/prev item in course.
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 3.0.0
 */

defined( 'ABSPATH' ) or die();

?>
<div class="course-item-nav" v-if="prevItem || nextItem">
    <div class="prev" v-if="prevItem">
        <span><?php echo esc_html_x( 'Prev', 'course-item-navigation', 'learnpress' ); ?></span>
        <a :href="prevItem.permalink" @click="_moveToItem($event, prevItem.id)">
            {{prevItem.name}}
        </a>
    </div>

    <div class="next" v-if="nextItem">
        <span><?php echo esc_html_x( 'Next', 'course-item-navigation', 'learnpress' ); ?></span>
        <a :href="nextItem.permalink" @click="_moveToItem($event, nextItem.id)">
            {{nextItem.name}}
        </a>
    </div>
</div>



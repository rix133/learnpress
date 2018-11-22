<?php
/**
 * Template for displaying course curriculum using Vue framework
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 3.2.0
 */

defined( 'ABSPATH' ) or die;

$course      = LP_Global::course();
$course_item = LP_Global::course_item();
$context     = $course_item ? 'course-item' : 'course';
?>

<div id="learn-press-course-curriculum" class="curriculum-scrollable">

    <?php

    /**
     * @since 3.x.x
     *
     * @see learn_press_ajax_loading_svg
     */
    do_action('learn-press/vm/before-course-curriculum');
    ?>

    <ul class="course-curriculum curriculum-sections" :class="['course-curriculum', ready ? 'ready' : '']"
        data-context="<?php echo $context; ?>">
        <li v-for="(section, sectionIndex) in $courseStore().sections" :class="sectionClass(section)"
            :id="sectionHtmlId(section)"
            :data-id="section.slug" :data-section-d="section.id">

            <div class="section-header">
                <div class="section-left">
                    <h5 class="section-title">{{section.name}}</h5>
                    <p v-if="section.desc" class="section-desc">{{section.desc}}</p>
                </div>
                <div class="section-meta">
                    <div class="learn-press-progress section-progress" title="7%">
                        <div class="progress-bg">
                            <div class="progress-active primary-background-color"
                                 :style="getProgressStyles(section)"></div>
                        </div>
                    </div>
                    <span class="step">{{getSectionCountItemsHtml(section)}}</span>
                    <span class="collapse"></span>
                </div>
            </div>
            <ul class="section-content">
                <li v-for="(item, itemIndex) in section.items" :class="sectionItemClass(item, section)">
                    <a class="section-item-link" :href="item.permalink" @click="_openItem($event, item)">
                        <span class="item-name">{{item.name}}</span>
                        <div class="course-item-meta">
                            <i class="fa item-meta course-item-status trans" @click.prevent="_completeItem(item)"></i>
                        </div>
                    </a>
                    {{endTime(sectionIndex, itemIndex)}}
                </li>
            </ul>
        </li>
    </ul>
</div>

<?php


?>

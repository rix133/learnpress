<?php
/**
 * Preload all items for Vue framework
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 3.2.0
 */
defined( 'ABSPATH' ) or die;

/**
 * @var LP_Course         $course
 * @var LP_Course_Section $section
 * @var LP_Course_Item    $item
 */
global $lp_course_item;

$course             = LP_Global::course();
$global_course_item = $lp_course_item;
$sections           = array();
?>
<div id="learn-press-content-item">

	<?php
	/**
	 * @since 3.x.x
	 *
	 * @see   learn_press_ajax_loading_svg
	 */
	do_action( 'learn-press/vm/before-course-items' );
	?>

    <div class="content-item-scrollable">

        <div class="content-item-wrap">

            <div :class="mainClass()"
                 data-classes="<?php echo join( ' ', learn_press_content_item_summary_main_classes() ); ?>">

				<?php

				/**
				 * @since 3.0.0
				 */
				do_action( 'learn-press/vm/before-course-item-content' );

				?>
				<?php
				foreach ( $course->get_sections() as $section ) {

					$sec = array(
						'id'             => $section->get_id(),
						'name'           => $section->get_title(),
						'desc'           => $section->get_description(),
						'classes'        => $section->get_class(),
						'items'          => array(),
						'completedItems' => 0
					);

					foreach ( $section->get_items() as $item ) {
						$lp_course_item = $item;
						?>
                        <div id="content-item-<?php echo $item->get_id(); ?>"
                             v-show="isShowItem(<?php echo $item->get_id(); ?>)"

                             class="learn-press-content-item content-item-<?php echo $item->get_post_type(); ?>">
                            <component :is="getComponent('<?php echo $item->get_post_type(); ?>')"
                                       :item="getItem(<?php echo $item->get_id(); ?>)"
                                       :item-id="<?php echo $item->get_id(); ?>"
                                       :current-item="currentItem"
                                       :is-current="currentItem.id==<?php echo $item->get_id(); ?>"
                                       :can-next-item="canNextItem" inline-template>
                                <div class="content-item-content">
									<?php do_action( 'learn-press/vm/course-item-content', $item->get_id(), $course->get_id() ); ?>
                                </div>
                            </component>
                        </div>
						<?php
						$it_data = learn_press_get_user_item_data( $item->get_id(), '', $course->get_id() );
						$it      = array(
							'id'        => $item->get_id(),
							'name'      => $item->get_title(),
							'type'      => $item->get_post_type(),
							'slug'      => '',
							'completed' => $it_data ? $it_data->is_completed() : false,
							'status'    => $it_data ? $it_data->get_status() : '',
							'preview'   => $item->is_preview(),
							'permalink' => $item->get_permalink(),
							'classes'   => $item->get_class()
						);

						if ( $item->get_post_type() === LP_QUIZ_CPT ) {
							//$it['quizData'] = learn_press_get_quiz_data_json( $item->get_id(), $course->get_id() );

							$quizData = learn_press_get_quiz_data_json( $item->get_id(), $course->get_id() );
							foreach ( $quizData as $k => $v ) {
								$it[ $k ] = $v;
							}
						}

						$sec['items'][] = apply_filters( 'learn-press/course-item-data-js', $it, $item->get_id() );
					}

					$sections[] = apply_filters( 'learn-press/course-section-data-js', $sec, $course->get_id() );
				}
				?>

				<?php

				/**
				 * @since 3.0.0
				 */
				do_action( 'learn-press/vm/after-course-item-content' );

				?>
            </div>

        </div>

    </div>

</div>


<?php
LP_Object_Cache::set( 'course-curriculum', $sections );
// Reset global course item
$lp_course_item = $global_course_item;
?>




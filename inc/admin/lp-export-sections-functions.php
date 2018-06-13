<?php
/**
 * Export functions used for admin when export sections data to XML files
 *
 * @package   LearnPress
 * @author    ThimPress
 * @version   1.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

/**
 *
 * @param bool   $return_me
 * @param string $meta_key
 * @return bool
 */
function learn_press_fource_export_sections( $return_me, $meta_key, $meta ) {
	global $wpdb, $post;
	$post_id = ( $post->ID ) ? $post->ID : ( ( $meta->post_id ) ? $meta->post_id : 0 );
	if( !$post_id ){
	    return $return_me;
    }
	if( get_post_type($post_id) === LP_COURSE_CPT ){
		$current_id = wp_cache_get( 'current-course-export-id', 'lp-force-export-section-course' );
		if ( false === $current_id || ( $current_id && $current_id != $post_id ) ) {
			$course_sections = $wpdb->get_results(
				$wpdb->prepare( "
						SELECT s.* FROM {$wpdb->prefix}learnpress_sections s
						WHERE s.section_course_id = %d", $post_id )
			);
			if ( $course_sections ) {
				foreach ( $course_sections as $section ) {
					echo "\t\t<wp:section>\n";
					echo "\t\t\t<wp:section_id>{$section->section_id}</wp:section_id>\n";
					echo "\t\t\t<wp:section_name>{$section->section_name}</wp:section_name>\n";
					echo "\t\t\t<wp:section_course_id>{$section->section_course_id}</wp:section_course_id>\n";
					echo "\t\t\t<wp:section_order>{$section->section_order}</wp:section_order>\n";
					echo "\t\t\t<wp:section_description><![CDATA[{$section->section_description}]]></wp:section_description>\n";
					$course_items = $wpdb->get_results(
						$wpdb->prepare( "
						SELECT c.ID AS course_ID, si.item_order, p.* FROM {$wpdb->prefix}learnpress_sections s
						INNER JOIN {$wpdb->prefix}learnpress_section_items si ON si.section_id = s.section_id
						INNER JOIN {$wpdb->prefix}posts p ON si.item_id = p.ID
						INNER JOIN {$wpdb->prefix}posts c ON c.ID = s.section_course_id
						WHERE c.ID = %d AND si.section_id = %d ORDER BY si.item_order ASC", $post_id, $section->section_id )
					);
					if ( $course_items ) {
						foreach ( $course_items as $item ) {
							echo "\t\t\t<wp:section_item>\n";
							echo "\t\t\t\t<wp:section_id>{$section->section_id}</wp:section_id>\n";
							echo "\t\t\t\t<wp:item_id>{$item->ID}</wp:item_id>\n";
							echo "\t\t\t\t<wp:item_order>{$item->item_order}</wp:item_order>\n";
							echo "\t\t\t\t<wp:item_type>{$item->post_type}</wp:item_type>\n";
							echo "\t\t\t</wp:section_item>\n";
						}
					}
					echo "\t\t</wp:section>\n";
				}
			}
			wp_cache_set( 'current-course-export-id', $post_id, 'lp-force-export-section-course' );
		}
	}elseif( get_post_type($post_id) === LP_QUIZ_CPT ){
	    $current_id = wp_cache_get( 'current-quiz-export-id', 'lp-force-export-quiz-course' );
		if ( false === $current_id || ( $current_id && $current_id != $post_id ) ) {
			$query     = $wpdb->prepare( "
				SELECT * FROM {$wpdb->prefix}learnpress_quiz_questions
				WHERE quiz_id = %d ORDER BY question_order ASC
			", $post_id );
			$questions = $wpdb->get_results( $query );
			if ( $questions ) {
				foreach ( $questions as $question ) {
					echo "\t\t<wp:question>\n";
					echo "\t\t\t<wp:quiz_id>{$question->quiz_id}</wp:quiz_id>\n";
					echo "\t\t\t<wp:question_id>{$question->question_id}</wp:question_id>\n";
					echo "\t\t\t<wp:params>" . wxr_cdata( $question->params ) . "</wp:params>\n";
					echo "\t\t\t<wp:question_order>{$question->question_order}</wp:question_order>\n";
					$query = $wpdb->prepare( "
                        SELECT * FROM {$wpdb->prefix}learnpress_question_answers 
                        WHERE question_id = %d ORDER BY answer_order ASC
                        ", $question->question_id );
					$question_answers = $wpdb->get_results( $query );
					if ( $question_answers ) {
						foreach ( $question_answers as $answer ){
							echo "\t\t\t<wp:answer>\n";
							echo "\t\t\t\t<wp:question_id>{$answer->question_id}</wp:question_id>\n";
							echo "\t\t\t\t<wp:answer_data>" . wxr_cdata( 'base64:' . base64_encode( $answer->answer_data ) ) . "</wp:answer_data>\n";
							echo "\t\t\t\t<wp:answer_order>{$answer->answer_order}</wp:answer_order>\n";
							echo "\t\t\t</wp:answer>\n";
                        }
                    }
					echo "\t\t</wp:question>\n";
				}
			}
			wp_cache_set( 'current-quiz-export-id', $post_id, 'lp-force-export-quiz-course' );
		}
    }

	return $return_me;
}
add_filter( 'wxr_export_skip_postmeta', 'learn_press_fource_export_sections', 10, 3 );

function learn_press_prepare_import_sections_simple($post, $wp){
	if( isset($wp->section) && $post['post_type'] === LP_COURSE_CPT ){
		$post['sections'] = array();
		foreach ( $wp->section as $section ) {
			$section_element = array(
				'section_id' => (int) $section->section_id,
				'section_name' => (string) $section->section_name,
				'section_course_id' => (int) $section->section_course_id,
				'section_order' => (int) $section->section_order,
				'section_description' => (string) $section->section_description,
			);
			$post['sections'][] = $section_element;
		}
	}

	return $post;
}
//add_action( 'thim_import_import_post', 'learn_press_prepare_import_sections', 10, 1);
add_filter( 'thim_extra_fields_parser_simple', 'learn_press_prepare_import_sections_simple', 10, 2);
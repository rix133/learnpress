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
 * @param bool $return_me
 * @param string $meta_key
 *
 * @return bool
 */
function learn_press_fource_export_sections( $return_me, $meta_key, $meta ) {
	global $wpdb, $post;
	$post_id = ( $post->ID ) ? $post->ID : ( ( $meta->post_id ) ? $meta->post_id : 0 );
	if ( ! $post_id ) {
		return $return_me;
	}
	if ( get_post_type( $post_id ) === LP_COURSE_CPT ) {
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
	} elseif ( get_post_type( $post_id ) === LP_QUIZ_CPT ) {
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
					$query            = $wpdb->prepare( "
                        SELECT * FROM {$wpdb->prefix}learnpress_question_answers 
                        WHERE question_id = %d ORDER BY answer_order ASC
                        ", $question->question_id );
					$question_answers = $wpdb->get_results( $query );
					if ( $question_answers ) {
						foreach ( $question_answers as $answer ) {
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

function learn_press_prepare_import_sections_simple( $post, $wp ) {
	if ( isset( $wp->section ) && $post['post_type'] === LP_COURSE_CPT ) {
		$post['sections'] = array();
		foreach ( $wp->section as $section ) {
			$section_element = array(
				'section_id'          => (int) $section->section_id,
				'section_name'        => (string) $section->section_name,
				'section_course_id'   => (int) $section->section_course_id,
				'section_order'       => (int) $section->section_order,
				'section_description' => (string) $section->section_description,
			);
			$section_items   = array();
			if ( isset( $section->section_item ) ) {
				foreach ( $section->section_item as $section_item ) {
					$section_items[] = array(
						'section_id' => (int) $section_item->section_id,
						'item_id'    => (int) $section_item->item_id,
						'item_order' => (int) $section_item->item_order,
						'item_type'  => (string) $section_item->item_type
					);
				}
				$section_element['section_items'] = $section_items;
			}
			$post['sections'][] = $section_element;
		}
	} elseif ( isset( $wp->question ) && $post['post_type'] === LP_QUIZ_CPT ) {
		$post['question'] = array();
		foreach ( $wp->question as $question ) {
			$question_element = array(
				'quiz_id'        => (int) $question->quiz_id,
				'question_id'    => (int) $question->question_id,
				'params'         => (string) $question->params,
				'question_order' => (int) $question->question_order,
			);
			$answer_items     = array();
			if ( isset( $question->answer ) ) {
				foreach ( $question->answer as $answer ) {
					$answer_items[] = array(
						'question_id'  => (int) $answer->question_id,
						'answer_order' => (int) $answer->answer_order,
						'answer_data'  => (string) $answer->answer_data,
					);
				}
				$question_element['answers'] = $answer_items;
			}
			$post['question'][] = $question_element;
		}
	}


	return $post;
}

add_filter( 'thim_extra_fields_parser_simple', 'learn_press_prepare_import_sections_simple', 10, 2 );

if ( ! function_exists( 'learn_press_import_sections_process' ) ) {
	function learn_press_import_sections_process( $post_id, $post, $list_types ) {
		if ( ! isset( $post['sections'] ) ) {
			return false;
		}
		global $wpdb;
		foreach ( $post['sections'] as $section ) {
			$inserted = $wpdb->insert(
				$wpdb->prefix . 'learnpress_sections',
				array(
					'section_name'        => $section['section_name'],
					'section_description' => $section['section_description'],
					'section_course_id'   => $post_id,
					'section_order'       => $section['section_order']
				),
				array( '%s', '%s', '%d', '%d' )
			);
			if ( ! $inserted ) {
				continue;
			}
			$section_id = $wpdb->insert_id;
			if ( $section_id && ! empty( $section['section_items'] ) ) {
				foreach ( $section['section_items'] as $item ) {
					if ( ! in_array( $item['item_type'], $list_types ) ) {
						continue;
					}
					$exist_item = $wpdb->get_results( "select post_id from $wpdb->postmeta where meta_value = '{$item['item_id']}' AND meta_key LIKE 'learn_press_imported_item_old_id'", ARRAY_A );

					if ( isset( $exist_item[0] ) && $exist_item[0] > 0 ) {
						$item['item_id'] = $exist_item[0];
					}
					$wpdb->insert(
						$wpdb->prefix . 'learnpress_section_items',
						array(
							'section_id' => $section_id,
							'item_id'    => $item['item_id'],
							'item_order' => $item['item_order'],
							'item_type'  => $item['item_type'],
						),
						array( '%d', '%d', '%d', '%s' )
					);
				}
			}
		}
	}
}

if ( ! function_exists( 'learn_press_import_item_process' ) ) {
	function learn_press_import_item_process( $post_id, $original_post_ID, $post, $post_type ) {
		global $wpdb;
		if ( $post_id != $original_post_ID ) {
			$wpdb->delete( $wpdb->postmeta, array(
				'meta_key'   => 'learn_press_imported_item_old_id',
				'meta_value' => $original_post_ID
			) );
			update_post_meta( $post_id, 'learn_press_imported_item_old_id', $original_post_ID );
			$wpdb->update( $wpdb->prefix . 'learnpress_section_items',
				array(
					'item_id' => $post_id
				),
				array(
					'item_id'   => $original_post_ID,
					'item_type' => $post_type
				),
				array( '%s' ),
				array( '%s', '%d' )
			);
		}

		if ( $post_type === LP_QUIZ_CPT && isset( $post['question'] ) ) {
			foreach ( $post['question'] as $question ) {
				$exist_item = $wpdb->get_results( "select post_id from $wpdb->postmeta where meta_value = '{$question['question_id']}' AND meta_key LIKE 'learn_press_imported_question_old_id'", ARRAY_A );
				if ( isset( $exist_item[0] ) && $exist_item[0] > 0 ) {
					$question['question_id'] = $exist_item[0];
				}
				$inserted = $wpdb->insert(
					$wpdb->prefix . 'learnpress_quiz_questions',
					array(
						'quiz_id'        => $post_id,
						'question_id'    => $question['question_id'],
						'params'         => $question['params'],
						'question_order' => $question['question_order']
					),
					array( '%d', '%d', '%s', '%d' )
				);
				if ( ! $inserted ) {
					continue;
				}
				if ( $question['answers'] ) {
					foreach ( $question['answers'] as $answer ) {

						$key = 'base64:';
						if ( strpos( $answer['answer_data'], $key ) !== false ) {
							$answer_data = base64_decode( substr( $answer['answer_data'], strlen( $key ) ) );
						} else {
							$answer_data = $answer['answer_data'];
						}

						$inserted = $wpdb->insert(
							$wpdb->prefix . 'learnpress_question_answers',
							array(
								'question_id'  => $question['question_id'],
								'answer_data'  => $answer_data,
								'answer_order' => $answer['answer_order']
							),
							array( '%d', '%s', '%d' )
						);
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'learn_press_import_question_process' ) ) {
	function learn_press_import_question_process( $post_id, $original_post_ID ) {
		global $wpdb;
		if ( $post_id == $original_post_ID ) {

			return;
		}
		$wpdb->delete( $wpdb->postmeta, array(
			'meta_key'   => 'learn_press_imported_question_old_id',
			'meta_value' => $original_post_ID
		) );
		update_post_meta( $post_id, 'learn_press_imported_question_old_id', $original_post_ID );
		$wpdb->update( $wpdb->prefix . 'learnpress_quiz_questions',
			array(
				'question_id' => $post_id
			),
			array(
				'question_id' => $original_post_ID
			),
			array( '%s' ),
			array( '%s' )
		);
		$wpdb->update( $wpdb->prefix . 'learnpress_question_answers',
			array(
				'question_id' => $post_id
			),
			array(
				'question_id' => $original_post_ID
			),
			array( '%s' ),
			array( '%s' )
		);
	}
}

function learn_press_import_sections( $post_id, $original_post_ID, $postdata, $post ) {
	$item_types = learn_press_get_course_item_types();
	switch ( $post_type = get_post_type( $post_id ) ) {
		case LP_COURSE_CPT:
			learn_press_import_sections_process( $post_id, $post, $item_types );
			break;
		case in_array( $post_type, $item_types ):
			learn_press_import_item_process( $post_id, $original_post_ID, $post, $post_type );
			break;
		default:
			break;
	}
}

add_action( 'wp_import_insert_post', 'learn_press_import_sections', 10, 4 );

function learn_press_clean_extra_data() {
	global $wpdb;
	$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'learnpress_question_answers' );
	$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'learnpress_quiz_questions' );
	$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'learnpress_section_items' );
	$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'learnpress_sections' );
}

add_action( 'thim_extra_data_clean', 'learn_press_clean_extra_data', 10 );

function learn_press_check_existing_post_again( $post_exists, $post ) {
	global $wpdb;
	$post_if = $wpdb->get_var( "SELECT count(post_name) FROM $wpdb->posts WHERE post_name like '{$post['post_name']}'" );
	if ( $post_if < 1 ) {
		return 0;
	}

	return $post_exists;
}

add_filter( 'wp_import_existing_post', 'learn_press_check_existing_post_again', 10, 2 );
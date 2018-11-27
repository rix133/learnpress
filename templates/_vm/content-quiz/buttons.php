<?php
/**
 * Template for displaying buttons below quiz.
 * This template only used for VueJs Framework.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/content-question/content.php.
 *
 * @author   ThimPress
 * @package  Learnpress/Templates
 * @version  3.x.x
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

?>
<button v-show="canRetake() && item.status=='completed' && !isReviewing"
        @click="_retakeQuiz()"><?php esc_html_e( 'Retake', 'learnpress' ); ?></button>
<button v-show="item.status=='completed' && !isReviewing"
        @click="_reviewQuestions()"><?php esc_html_e( 'Review', 'learnpress' ); ?></button>
<button v-show="item.status=='completed' && isReviewing"
        @click="_reviewQuestions()"><?php esc_html_e( 'Results', 'learnpress' ); ?></button>
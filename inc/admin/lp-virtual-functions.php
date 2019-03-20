<?php
/**
 * Virtual functions used for admin
 *
 * @package   LearnPress
 * @author    ThimPress
 * @version   1.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

if ( ! function_exists( 'learn_press_premium_notice' ) ) {
	function learn_press_premium_notice( $addon_info ) {
		$notice_text = sprintf( __( 'This is a premium feature. Please upgrade to the <a href="https://thimpress.com/product/learnpress-add-ons-bundle/" target="_blank">LearnPress PRO bundle</a><br />
 						or install the <a href="%s" target="_blank">%s</a> to unlock this feature!', 'learnpress' ), $addon_info['premium_url'], $addon_info['name_desc'] );
		$html        = '<div class="lp-virtual-function"> <img src="' . $addon_info['background_url'] . '"/>';
		$html        .= '<p class="lp-virtual-functions-notice">' . $notice_text . '</p>';
		$html        .= '</div>';

		return $html;
	}
}

//Premium tab for Course settings
add_filter( 'learn-press/admin-course-tabs', 'learn_press_add_course_tab_advanced_settings', 1000, 1 );

if ( ! function_exists( 'learn_press_add_course_tab_advanced_settings' ) ) {
	function learn_press_add_course_tab_advanced_settings( $tabs ) {
		$premium_addon      = apply_filters( 'learn-press/premium-addons-list', array(
			'Announcements'        => array(
				'title'          => __( 'Announcements', 'learnpress' ),
				'post_types'     => LP_COURSE_CPT,
				'context'        => 'normal',
				'icon'           => 'dashicons-megaphone',
				'priority'       => 'high',
				'pages'          => array( LP_COURSE_CPT ),
				'fields'         => array(),
				'class'          => 'lp-virtual-functions-li',
				'name_desc'      => __( 'LearnPress Announcements Add-on', 'learnpress' ),
				'premium_url'    => 'https://thimpress.com/product/announcement-add-on-for-learnpress/',
				'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/announcements.png',
			),
			'Certificates'         => array(
				'title'          => __( 'Certificates', 'learnpress' ),
				'post_types'     => LP_COURSE_CPT,
				'context'        => 'normal',
				'icon'           => 'dashicons-welcome-learn-more',
				'priority'       => 'high',
				'pages'          => array( LP_COURSE_CPT ),
				'fields'         => array(),
				'class'          => 'lp-virtual-functions-li',
				'name_desc'      => __( 'LearnPress Certificates Add-on', 'learnpress' ),
				'premium_url'    => 'https://thimpress.com/product/certificates-add-on-for-learnpress/',
				'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/certificates.png',
			),
			'Co_Instructor'        => array(
				'title'          => __( 'Co-Instrutors', 'learnpress' ),
				'post_types'     => LP_COURSE_CPT,
				'context'        => 'normal',
				'icon'           => 'dashicons-businessman',
				'priority'       => 'high',
				'pages'          => array( LP_COURSE_CPT ),
				'fields'         => array(),
				'class'          => 'lp-virtual-functions-li',
				'name_desc'      => __( 'LearnPress Co-Instructors Add-on', 'learnpress' ),
				'premium_url'    => 'https://thimpress.com/product/co-instructors-add-on-for-learnpress/',
				'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/co_instructor.png',
			),
			'Collections'          => array(
				'title'          => __( 'Collections', 'learnpress' ),
				'post_types'     => LP_COURSE_CPT,
				'context'        => 'normal',
				'icon'           => 'dashicons-list-view',
				'priority'       => 'high',
				'pages'          => array( LP_COURSE_CPT ),
				'fields'         => array(),
				'class'          => 'lp-virtual-functions-li',
				'name_desc'      => __( 'LearnPress Collections Add-on', 'learnpress' ),
				'premium_url'    => 'https://thimpress.com/product/collections-add-on-for-learnpress/',
				'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/collections.png',
			),
			'Paid_Memberships_Pro' => array(
				'title'          => __( 'Course Memberships', 'learnpress' ),
				'post_types'     => LP_COURSE_CPT,
				'context'        => 'normal',
				'icon'           => 'dashicons-groups',
				'priority'       => 'high',
				'pages'          => array( LP_COURSE_CPT ),
				'fields'         => array(),
				'class'          => 'lp-virtual-functions-li',
				'name_desc'      => __( 'LearnPress Paid Memberships Pro Add-on', 'learnpress' ),
				'premium_url'    => 'https://thimpress.com/product/paid-memberships-pro-add-learnpress/',
				'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/memberships_pro.png',
			),
		) );
		$advanced_meta_box  = array(
			'title'      => __( 'Advanced Settings', 'learnpress' ),
			'subtitle'   => sprintf( __( '<i class="lp-premium-subtitle">(Premium)</i>', 'learnpress' ) ),
			'content'    => '<div class="lp-premium-advanced-settings"></div>',
			'post_types' => LP_COURSE_CPT,
			'context'    => 'normal',
			'id'         => 'advanced_settings',
			'icon'       => 'dashicons-hammer',
			'priority'   => 'high',
			'class'      => 'lp-virtual-functions-adv-tab',
			'pages'      => array( LP_COURSE_CPT ),
			'fields'     => array()
		);
		$display            = false;
		$premium_meta_boxes = array();
		$first_content      = '';
		foreach ( $premium_addon as $key => $addon ) {
			if ( ! class_exists( 'LP_Addon_' . $key ) ) {
				$display          = true;
				$addon['content'] = learn_press_premium_notice( $addon );
				if ( $first_content == '' ) {
					$first_content  = $addon['content'];
					$addon['class'] .= ' lp-first-in-list';
				}
				$premium_meta_boxes[ strtolower( $key ) ] = $addon;
			}
		}
		if ( $display ) {
			$advanced_meta_box['content'] = $first_content;
			$forum                        = array( 'course_advanced_settings' => $advanced_meta_box );
			$tabs                         = array_merge( $tabs, $forum, $premium_meta_boxes );
		}

		return $tabs;
	}
}

add_filter( 'learn-press/admin/tab-class', 'learn_press_check_virtual_classes', 10, 2 );
if ( ! function_exists( 'learn_press_check_virtual_classes' ) ) {
	function learn_press_check_virtual_classes( $class, $tab ) {
		if ( ! empty( $tab['class'] ) ) {
			$class[] = $tab['class'];
		}

		return $class;
	}
}

//Put Premium Info into global settings
add_filter( 'learn-press/submenu-learn-press-settings-heading-tabs', 'learn_press_global_settings_premium' );
if ( ! function_exists( 'learn_press_global_settings_premium' ) ) {
	function learn_press_global_settings_premium( $tabs ) {
		if ( ! class_exists( 'LP_Addon_Certificates' ) ) {
			$tab_premium_certificates       = new LP_Settings_Advanced();
			$tab_premium_certificates->id   = 'premium_certificates';
			$tab_premium_certificates->text = __( 'Certificate', 'learnpress' );
			$tabs['premium_certificates']   = $tab_premium_certificates;
		}
		if ( ! class_exists( 'LP_Addon_Commission' ) ) {
			$tab_premium_commission       = new LP_Settings_Advanced();
			$tab_premium_commission->id   = 'premium_commission';
			$tab_premium_commission->text = __( 'Commission', 'learnpress' );
			$tabs['premium_commission']   = $tab_premium_commission;
		}
		if ( ! class_exists( 'LP_Addon_Frontend_Editor' ) ) {
			$tab_premium_fe       = new LP_Settings_Advanced();
			$tab_premium_fe->id   = 'premium_frontend_editor';
			$tab_premium_fe->text = __( 'Frontend Editor', 'learnpress' );
			$tabs['premium_frontend_editor']   = $tab_premium_fe;
		}
		if ( ! class_exists( 'LP_Addon_Paid_Memberships_Pro' ) ) {
			$tab_premium_pmpro       = new LP_Settings_Advanced();
			$tab_premium_pmpro->id   = 'premium_pmpro';
			$tab_premium_pmpro->text = __( 'Memberships', 'learnpress' );
			$tabs['premium_pmpro']   = $tab_premium_pmpro;
		}

		return $tabs;
	}
}

add_action( 'learn-press/admin/page-content-settings-premium', 'learn_press_premium_global_content', 10, 1 );
if ( ! function_exists( 'learn_press_premium_global_content' ) ) {
	function learn_press_premium_global_content( $tab ) {
		$tab_info = array();
		switch ( $tab ) {
			case 'premium_certificates':
				$tab_info = array(
					'name_desc'      => __( 'LearnPress Certificates Add-on', 'learnpress' ),
					'premium_url'    => 'https://thimpress.com/product/certificates-add-on-for-learnpress/',
					'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/global-certificates.png',
				);
				break;
			case 'premium_commission':
				$tab_info = array(
					'name_desc'      => __( 'LearnPress Commission Add-on', 'learnpress' ),
					'premium_url'    => 'https://thimpress.com/product/commission-add-on-for-learnpress/',
					'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/global-commission.png',
				);
				break;
			case 'premium_frontend_editor':
				$tab_info = array(
					'name_desc'      => __( 'LearnPress Frontend Editor Add-on', 'learnpress' ),
					'premium_url'    => 'https://thimpress.com/product/frontend-editor-add-on-for-learnpress/',
					'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/global-frontend-editor.png',
				);
				break;
			case 'premium_pmpro':
				$tab_info = array(
					'name_desc'      => __( 'LearnPress Paid Memberships Pro Add-on', 'learnpress' ),
					'premium_url'    => 'https://thimpress.com/product/paid-memberships-pro-add-learnpress/',
					'background_url' => LP_PLUGIN_URL . 'assets/images/virtual_functions/global-membership.png',
				);
				break;
		}
		echo learn_press_premium_notice( $tab_info );
	}
}

//premium payment methods
add_filter( 'learn-press/submenu-sections', 'learn_press_add_premium_payment_methods', 10, 1 );
if(!function_exists('learn_press_add_premium_payment_methods')){
	function learn_press_add_premium_payment_methods($sections){
		echo'<pre>';print_r($sections);die;
	}
}
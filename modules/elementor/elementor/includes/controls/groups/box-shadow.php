<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor box shadow control.
 *
 * A base control for creating box shadow control. Displays input fields to define
 * the box shadow including the horizontal shadow, vertical shadow, shadow blur,
 * shadow spread, shadow color and the position.
 *
 * @since 1.2.2
 */
class Group_Control_Box_Shadow extends Group_Control_Base {

	/**
	 * Fields.
	 *
	 * Holds all the box shadow control fields.
	 *
	 * @since 1.2.2
	 * @access protected
	 * @static
	 *
	 * @var array Box shadow control fields.
	 */
	protected static $fields;

	/**
	 * Get box shadow control type.
	 *
	 * Retrieve the control type, in this case `box-shadow`.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return string Control type.
	 */
	public static function get_type() {
		return 'box-shadow';
	}

	/**
	 * Init fields.
	 *
	 * Initialize box shadow control fields.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @return array Control fields.
	 */
	protected function init_fields() {
		$controls = [];

		$controls['box_shadow'] = [
			'label' => _x_elementor_adapter( 'Box Shadow', 'Box Shadow Control', 'elementor' ),
			'type' => Controls_Manager::BOX_SHADOW,
			'condition' => [
				'box_shadow_type!' => '',
			],
			'selectors' => [
				'{{SELECTOR}}' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}} {{box_shadow_position.VALUE}};',
			],
		];

		$controls['box_shadow_position'] = [
			'label' => _x_elementor_adapter( 'Position', 'Box Shadow Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'options' => [
				' ' => _x_elementor_adapter( 'Outline', 'Box Shadow Control', 'elementor' ),
				'inset' => _x_elementor_adapter( 'Inset', 'Box Shadow Control', 'elementor' ),
			],
			'condition' => [
				'box_shadow_type!' => '',
			],
			'default' => ' ',
			'render_type' => 'ui',
		];

		return $controls;
	}

	/**
	 * Get default options.
	 *
	 * Retrieve the default options of the box shadow control. Used to return the
	 * default options while initializing the box shadow control.
	 *
	 * @since 1.9.0
	 * @access protected
	 *
	 * @return array Default box shadow control options.
	 */
	protected function get_default_options() {
		return [
			'popover' => [
				'starter_title' => _x_elementor_adapter( 'Box Shadow', 'Box Shadow Control', 'elementor' ),
				'starter_name' => 'box_shadow_type',
				'starter_value' => 'yes',
			],
		];
	}
}

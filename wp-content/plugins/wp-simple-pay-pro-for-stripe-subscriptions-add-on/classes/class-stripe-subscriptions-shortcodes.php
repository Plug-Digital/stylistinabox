<?php

/**
 * Shortcodes class - SP Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Subscriptions_Shortcodes' ) ) {

	class Stripe_Subscriptions_Shortcodes {

		protected static $instance = null;

		private function __construct() {
			add_shortcode( 'stripe_subscription', array( $this, 'main_shortcode' ) );

			add_shortcode( 'stripe_plan', array( $this, 'plan_items' ) );

			add_shortcode( 'stripe_recurring_total', array( $this, 'recurring_total' ) );
		}

		/**
		 * [stripe_subscription] shortcode
		 *
		 * @since 1.0.0
		 */
		public function main_shortcode( $attr, $content = null ) {
			global $sc_script_options;

			// Our unique identifier in case multiple forms exist
			STATIC $uid = 1;

			extract( shortcode_atts( array(
				'label'                 => '',
				'type'                  => 'radio',
				'default'               => '',
				'show_details'          => 'true',
				'id'                    => '',
				'interval'              => 'month',
				'interval_count'        => 1,
				'statement_description' => '',
				'use_amount'            => 'false',
				'setup_fee'             => 0,
				'max_occurrences'       => null,
			), $attr, 'stripe_subscription' ) );

			Shortcode_Tracker::add_new_shortcode( 'stripe_subscription_' . $uid, 'stripe_subscription', $attr, false );

			$sub_amount         = 0;
			$sub_interval       = 0;
			$sub_interval_count = 0;

			$error_count = 0;
			$error_html  = '';
			$html        = ( ! empty( $label ) ? '<p class="sc-radio-group-label">' . $label . '</p>' : '' );

			// Check for test_mode="true" in [stripe] parent shortcode.
			$base_attributes = Shortcode_Tracker::get_base_attributes();
			$test_mode_attr = $base_attributes['test_mode'];

			// Default test_mode to false unless true is explicitly set.
			$test_mode = ( isset( $test_mode_attr ) && ( 'true' == $test_mode_attr ) ? 'true' : 'false' );

			// If the type is set to single then we just want to make sure the button points to the right plan
			// and that the button will be assigned the correct price for the subscripti
			// We use sanitize_text_field here to account for empty values that may exist
			$content_check = sanitize_text_field( $content );

			// Limit to 15
			$statement_description = substr( $statement_description, 0, 22 );

			if ( $type != 'radio' ) {
				Shortcode_Tracker::update_error_count();

				if ( current_user_can( 'manage_options' ) ) {
					Shortcode_Tracker::add_error_message( '<h6>' . __( 'You have entered an invalid type.', 'simpay_sub' ) . '</h6>' );
				}

				return;
			}

			if ( empty( $content_check ) ) {

				if ( empty( $id ) ) {
					return;
				}

				$sub = Stripe_Subscriptions_Functions::get_subscription_by_id( $id, $test_mode );

				if ( ! is_object( $sub ) ) {

					if ( current_user_can( 'manage_options' ) ) {
						//$error_html .= _x( 'Invalid subscription ID entered - ', 'Shown when the admin has entered an invalid subscription ID', 'simpay_sub' ) . $id . '<br>';
						$error_html .= $sub;
						$error_count++;
					}
				} else {
					$default            = $sub->id;
					$sub_amount         = $sub->amount;
					$sub_interval       = $sub->interval;
					$sub_interval_count = $sub->interval_count;
					$sub_currency       = $sub->currency;
					$sub_trial_days     = ( null !== $sub->trial_period_days ) ? $sub->trial_period_days : 0;
				}

				$uid++;
			} else {
				$content = trim( substr( $this->parse_shortcode_content( $content ), 0, -1 ) );

				$ids = explode( ';', $content );

				STATIC $id_num = 1;

				$html .= '<div class="sc-radio-group">';
				foreach ( $ids as $id ) {
					$data = explode( '|', $id );

					$data[0] = trim( $data[0] );

					// Get the Subscription
					if ( empty( $data[0] ) ) {
						Shortcode_Tracker::update_error_count();

						if ( current_user_can( 'manage_options' ) ) {
							$error_html .= _x( 'You have not entered any subscription plan IDs', 'Shown when the admin has not entered any subscription IDs', 'simpay_sub' ) . $data[0] . '<br />';
						}

						$error_count++;
						continue;
					}

					$sub = Stripe_Subscriptions_Functions::get_subscription_by_id( $data[0], $test_mode );

					if ( ! is_object( $sub ) ) {

						if ( current_user_can( 'manage_options' ) ) {
							//$error_html .= _x( 'Invalid subscription ID entered - ', 'Shown when the admin has entered an invalid subscription ID', 'simpay_sub' ) . $id . '<br>';
							$error_html .= $sub;
						}

						$error_count++;
						continue;
					}

					if ( empty( $default ) ) {
						$default            = $sub->id;
						$sub_interval       = $sub->interval;
						$sub_interval_count = $sub->interval_count;
					}

					if ( $default == $sub->id ) {
						$sub_amount = $sub->amount;
					}

					$sub_trial_days = ( null !== $sub->trial_period_days ) ? $sub->trial_period_days : 0;

					$currency = $sub->currency;

					$formatted_amount = Stripe_Checkout_Misc::to_formatted_amount( $sub->amount, $sub->currency );

					$details_html = ' - ';
					$details_html .= ( $currency == 'usd' ? '$' : '' ) . $formatted_amount . ( $currency == 'usd' ? '' : ' ' . strtoupper( $currency ) );

					// For interval count of 1, use $1.00/month format.
					// For a count > 1, use $1.00 every 3 months format.
					if ( $sub->interval_count == 1 ) {
						$details_html .= '/' . $sub->interval;
					} else {
						$details_html .= ' ' . __( 'every', 'simpay_sub' ) . ' ' . $sub->interval_count . ' ' . $sub->interval . 's';
					}

					$details_html = ( ( $data[1] != 'null' ) ? $data[1] : $sub->name ) . ( $show_details == 'true' ? ' ' . $details_html : '' );

					$details_html = apply_filters( 'sc_subscription_details', $details_html, $sub );

					if ( $type == 'radio' ) {
						$html .= '<label title="' . esc_attr( $sub->name ) . '">';
						$html .= '<input type="radio" value="' . esc_attr( $sub->name ) . '" name="sc_radio_' . $uid . '" ' . 'id="sc_radio_' . $id_num . '" class="sp-sub-plan" ' . 'data-sub-amount="' . $sub->amount . '" ' . 'data-sub-id="' . $sub->id . '" ' . ( ! empty( $default ) && $sub->id == $default ? 'checked' : '' ) . ' ' . 'data-parsley-errors-container=".sc-radio-group" ' . 'data-sub-interval="' . $sub->interval . '" data-sub-interval-count="' . $sub->interval_count . '" ' . 'data-sub-currency="' . $currency . '" ' . 'data-sub-setup-fee="' . ( ! empty( $data[2] ) ? $data[2] : 0 ) . '" ' . 'data-sub-trial-days="' . $sub_trial_days . '">';
						$html .= '<span>' . $details_html . '</span>';
						$html .= '</label>';
					}

					if ( ! empty( $default ) && $sub->id == $default ) {
						$sub_interval = $sub->interval;
					}

					$id_num++;
				}
				$html .= '</div>';
			}

			if ( $setup_fee > 0 ) {
				$sc_script_options['script']['setupFee'] = $setup_fee;
			}

			// Set hidden fields for values that need to be submitted.
			if ( $error_count < 1 ) {
				$html .= '<input type="hidden" name="sc_sub_id" class="sc_sub_id" value="' . $default . '" />';
				$html .= '<input type="hidden" name="sc_sub_amount" class="sc_sub_amount" value="' . $sub_amount . '" />';
				$html .= '<input type="hidden" name="sc_sub_interval" class="sc_sub_interval" value="' . $sub_interval . '" />';
				$html .= '<input type="hidden" name="sc_sub_interval_count" class="sc_sub_interval_count" value="' . $sub_interval_count . '" />';
				$html .= '<input type="hidden" name="sc_sub_setup_fee" class="sc_sub_setup_fee" value="' . esc_attr( $setup_fee ) . '" />';
				$html .= '<input type="hidden" name="sc_sub_trial_days" class="sc_sub_trial_days" value="' . esc_attr( $sub_trial_days ) . '" />';

				// Max occurrences is raw value from extract() for now.
				$html .= '<input type="hidden" name="sc_sub_max_occurrences" class="sc_sub_max_occurrences" value="' . esc_attr( $max_occurrences ) . '" />';

				// Create hidden field for quantity, but we'll be changing it in JS only if a custom field with is_quantity is used.
				$html .= '<input type="hidden" name="sc_sub_quantity" class="sc_sub_quantity" value="1" />';

				$sc_script_options['script']['amount'] = $sub_amount;

				if ( isset( $sub_currency ) ) {
					$html .= '<input type="hidden" name="sc_sub_currency" class="sc_sub_currency" value="' . $sub_currency . '" />';
				}

				return '<div class="sc_sub_wrapper sc-form-group" id="sc_sub_wrapper_' . $uid . '">' . $html . '</div>';

			} else {

				Shortcode_Tracker::update_error_count();

				if ( current_user_can( 'manage_options' ) ) {
					Shortcode_Tracker::add_error_message( '<h6>' . __( 'Admin note: Shortcode error detected. Please check your shortcode syntax, subscription plan IDs and Stripe API keys.', 'simpay_sub' ) . '</h6>' );
				}

				return '<h6>' . __( 'An error has occurred. If the problem persists, please contact the site administrator.', 'simpay_sub' ) . '</h6>';
			}
		}

		/**
		 * [stripe_plan] shortcode
		 *
		 * This doesn't actually output anything to the screen so it should not be used by itself.
		 * It takes the data and transforms it to be used with the main shortcode.
		 *
		 * @since 1.0.0
		 */
		public function plan_items( $attr ) {

			STATIC $uid = 1;

			extract( shortcode_atts( array(
				'id'        => null,
				'label'     => ( ! isset( $attr['label'] ) ? 'null' : $attr['label'] ),
				'setup_fee' => 0,
			), $attr, 'stripe_subscriptions' ) );

			Shortcode_Tracker::add_new_shortcode( 'stripe_plan_' . $uid, 'stripe_plan', $attr, true );

			$uid++;

			return $id . '|' . $label . '|' . $setup_fee . ';';
		}

		/**
		 * [stripe_recurring_total] shortcode.
		 */
		public function recurring_total( $attr ) {

			global $sc_script_options;

			static $counter = 1;

			$attr = shortcode_atts( array(
				'label' => __( 'Recurring Payment:', 'simpay_sub' ) . ' ',
			), $attr, 'stripe_recurring_total' );

			$label     = $attr['label'];
			$currency  = strtoupper( $sc_script_options['script']['currency'] );
			$amount    = $sc_script_options['script']['amount'];
			$setup_fee = isset( $sc_script_options['script']['setupFee'] ) ? $sc_script_options['script']['setupFee'] : 0;

			Shortcode_Tracker::add_new_shortcode( 'stripe_recurring_total_' . $counter, 'stripe_total', $attr, false );

			$html = $label . ' ';
			$html .= '<span class="' . apply_filters( 'sc_recurring_total_amount_class', 'sc-recurring-total-amount' ) . '">';

			// USD only: Show dollar sign on left of amount.
			if ( $currency === 'USD' ) {
				$html .= '$';
			}

			$html .= Stripe_Checkout_Misc::to_formatted_amount( $amount + $setup_fee, $currency );

			// Non-USD: Show currency on right of amount.
			if ( $currency !== 'USD' ) {
				$html .= ' ' . $currency;
			}

			$html .= '</span>'; //sc-recurring-total-amount

			$counter++;

			// Set args to send with filter
			$args              = array();
			$args['label']     = $label;
			$args['currency']  = $currency;
			$args['amount']    = $amount;
			$args['setup_fee'] = $setup_fee;

			return '<div class="' . apply_filters( 'sc_form_group_class', 'sc-form-group' ) . '">' . apply_filters( 'sc_stripe_recurring_total', $html, $args ) . '</div>';
		}

		/**
		 * Function to remove the annoying <br> and <p> tags from wpautop inside the shortcode
		 *
		 * Found this function here: http://charlesforster.com/shortcodes-and-line-breaks-in-wordpress/
		 *
		 * @since 1.0.0
		 */
		public function parse_shortcode_content( $content ) {

			// Parse nested shortcodes and add formatting.
			$content = trim( do_shortcode( $content ) );

			// Remove '</p>' from the start of the string.
			if ( substr( $content, 0, 4 ) == '</p>' ) {
				$content = substr( $content, 4 );
			}

			// Remove '<p>' from the end of the string.
			if ( substr( $content, -3, 3 ) == '<p>' ) {
				$content = substr( $content, 0, -3 );
			}

			// Remove any instances of '<p></p>'.
			$content = str_replace( array( '<p>', '</p>' ), '', $content );
			$content = str_replace( array( '<br>', '<br />', '<br/>' ), '', $content );
			$content = str_replace( array( '/r', '/n', '/r/n' ), '', $content );

			return $content;
		}

		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * This is file define the fields of the meta boxes
 * @package wpl-logo-carousel
 */
class WPLLC_MetaBoxForm {

	/**
	 * text
	 *
	 * @param array $args
	 */
	public function text( array $args ) {
		if ( ! isset( $args['id'], $args['name'] ) ) {
			return;
		}

		list( $name, $value, $after ) = $this->field_common( $args );

		echo $this->field_before( $args );
		echo sprintf( '<input type="text" class="wpl-input-text" value="%1$s" id="%2$s" name="%3$s">%4$s', $value, $args['id'], $name, $after );
		echo $this->field_after();

	}
	/**
	 * text
	 *
	 * @param array $args
	 */
	public function url_disabled( array $args ) {
		if ( ! isset( $args['id'], $args['name'] ) ) {
			return;
		}

		list( $name, $value, $after ) = $this->field_common( $args );

		echo $this->field_before( $args );
		echo sprintf( '<input type="text" class="wpl-input-text" value="%1$s" id="%2$s" name="%3$s" disabled>%4$s', $value, $args['id'], $name, $after );
		echo $this->field_after();

	}

	/**
	 * color
	 *
	 * @param array $args
	 */
	public function color( array $args ) {
		if ( ! isset( $args['id'], $args['name'] ) ) {
			return;
		}

		list( $name, $value ) = $this->field_common( $args );
		$default_value = isset( $args['default'] ) ? $args['default'] : '';

		echo $this->field_before( $args );
		echo sprintf( '<input type="text" class="wpl-color-picker" value="%1$s" id="%2$s" name="%3$s" data-default-color="%4$s">', $value, $args['id'], $name, $default_value );
		echo $this->field_after();
	}

	/**
	 * number
	 *
	 * @param array $args
	 */
	public function number( array $args ) {
		if ( ! isset( $args['id'], $args['name'] ) ) {
			return;
		}

		list( $name, $value, $after ) = $this->field_common( $args );
		$min = isset( $args['min'] ) ? $args['min'] : null;
		$max = isset( $args['max'] ) ? $args['max'] : null;

		echo $this->field_before( $args );
		echo sprintf( '<input type="number" class="wpl-input-number" value="%1$s" id="%2$s" name="%3$s">%4$s', $value, $args['id'], $name, $after );
		echo $this->field_after();
	}

	/**
	 * checkbox
	 *
	 * @param array $args
	 */
	public function checkbox( array $args ) {
		if ( ! isset( $args['id'], $args['name'] ) ) {
			return;
		}

		list( $name, $value, $after ) = $this->field_common( $args );
		$checked = ( $value == 'on' ) ? ' checked' : '';

		echo $this->field_before( $args );
		echo sprintf( '<input type="hidden" name="%1$s" value="off">', $name );
		echo sprintf( '<label for="%2$s"><input type="checkbox" %4$s value="on" id="%2$s" name="%1$s">%3$s</label>', $name, $args['id'], $after, $checked );
		echo $this->field_after();
	}

	/**
	 * select
	 *
	 * @param array $args
	 */
	public function select( array $args ) {
		if ( ! isset( $args['id'], $args['name'] ) ) {
			return;
		}

		list( $name, $value ) = $this->field_common( $args );
		$multiple = isset( $args['multiple'] ) ? 'multiple' : '';

		echo $this->field_before( $args );
		echo sprintf( '<select name="%1$s" id="%2$s" class="wpl-input-text" %3$s>', $name, $args['id'], $multiple );
		foreach ( $args['options'] as $key => $option ) {
			$selected = ( $value == $key ) ? ' selected="selected"' : '';
			echo sprintf( '<option value="%1$s" %3$s>%2$s</option>', $key, $option, $selected );
		}
		echo '</select>';
		echo $this->field_after();
	}

	/**
	 * Select layout for pro ad.
	 * @param array $args
	 */
	public function select_layout( array $args ) {
		if ( ! isset( $args['id'], $args['name'] ) ) {
			return;
		}

		list( $name, $value ) = $this->field_common( $args );
		$multiple = isset( $args['multiple'] ) ? 'multiple' : '';

		echo $this->field_before( $args );
		echo sprintf( '<select name="%1$s" id="%2$s" class="wpl-input-text" %3$s>', $name, $args['id'], $multiple ); ?>
		<option value="carousel">Carousel</option>
		<option value="gird" disabled>Grid (Pro)</option>
		<option value="filter" disabled>Filter (Pro)</option>
		<option value="list" disabled>List (Pro)</option>
		<option value="inline" disabled>Inline (Pro)</option>
		<?php
		echo '</select>';
		echo $this->field_after();
	}

	/**
	 * field common
	 *
	 * @param $args
	 *
	 * @return array
	 */
	private function field_common( $args ) {
		global $post;

		// Meta Name
		$group    = isset( $args['group'] ) ? $args['group'] : 'wpl_lc_meta_box';
		$multiple = isset( $args['multiple'] ) ? '[]' : '';
		$name     = sprintf( '%s[%s]%s', $group, $args['id'], $multiple );
		$after    = isset( $args['after'] ) ? '<span class="wpl-mb-after">' . $args['after'] . '</span> ' : '';
		// Meta Value
		$default_value = isset( $args['default'] ) ? $args['default'] : '';
		$meta      = get_post_meta( $post->ID, $args['id'], true );
		$value     = ! empty( $meta ) ? $meta : $default_value;
		if ( $value == 'zero' ) {
			$value = 0;
		}

		return array( $name, $value, $after );
	}

	/**
	 * Before text of the field
	 * @since 2.0
	 * @param $args
	 *
	 * @return string
	 */
	private function field_before( $args ) {
		$table = '';
		$table .= sprintf( '<div class="wpl-element wpl-input-group" id="field-%s">', $args['id'] );
		$table .= sprintf( '<div class="wpl-input-label">' );
		$table .= sprintf( '<label for="%1$s"><h4>%2$s</h4></label>', $args['id'], $args['name'] );
		if ( ! empty( $args['desc'] ) ) {
			$table .= sprintf( '<p class="wpl-input-desc">%s</p>', $args['desc'] );
		}
		$table .= '</div>';
		$table .= sprintf( '<div class="wpl-input-field">' );

		return $table;
	}

	private function field_after() {
		return '</div></div>';
	}

}
<?php
$wp_customize->add_section( 'gf_stla_form_id_general_settings' , array(
		'title' => 'General Settings',
		'panel' => 'gf_stla_panel',
	) );

$wp_customize->add_setting( 'gf_stla_general_settings[force-styles]' , array(
		'default'     => '',
		'transport'   => 'postMessage',
		'type' => 'option'
	) );

$wp_customize->add_control( 'gf_stla_general_settings[force-styles]',   array(
		'type' => 'checkbox',
		'priority' => 10, // Within the section.
		'section' => 'gf_stla_form_id_general_settings', // Required, core or custom.
		'label' => __( 'Force CSS Styles (Enabe this setting if styles are not showing in frontend) ' ),
	)
);

$wp_customize->add_setting( 'gf_stla_general_settings[reset-styles]' , array(
		'default'     => -1,
		'transport'   => 'postMessage',
		'type' => 'option'
	) );

//get all gravity forms created by user
if ( class_exists( 'RGFormsModel' ) ) {
	$forms = RGFormsModel::get_forms( null, 'title' );

	$select_form = array( -1 => '---Select form --' );
	foreach ( $forms as $form ) {
		$style_current_form = get_option( 'gf_stla_form_id_'.$form->id );
		if ( !empty( $style_current_form ) ) {

			$select_form[$form->id] = $form->title;
		}
	}
}
else {
	$select_form['form not installed']= 'Gravity Forms not installed';
}

$wp_customize->add_control( 'gf_stla_general_settings[reset-styles]', array(
		'type' => 'select',
		'priority' => 10, // Within the section.
		'section' => 'gf_stla_form_id_general_settings', // Required, core or custom.
		'label' => __( 'Select Form to Reset Styles' ),
		'choices'        => $select_form,

	) );

//reset styles button
$wp_customize->add_setting( 'gf_stla_form_id_reset_style_button', array(
		'default' => 'Delete Styles',
		'transport' => 'postMessage',
		'type' => 'option'
	) );

$wp_customize->add_control( 'gf_stla_form_id_reset_style_button', array(
		'type' => 'button',
		'priority' => 10, // Within the section.
		'section' => 'gf_stla_form_id_general_settings', // Required, core or custom.
		'input_attrs' => array(
			'style' => 'float:left',
			'class' => 'gf-stla-reset-style-button'
		)
	) );

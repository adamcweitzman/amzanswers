<?php 

/**
 * Options for WordPress Theme Customizer.
 */
 
	function writee_theme_customize_register( $wp_customize ) {
		$writee_theme = wp_get_theme();
		$wp_customize->get_setting( 'blogname' )->transport          = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport   = 'postMessage';
		
	
		/* Main option Settings Panel */
		$wp_customize->add_panel('wrt_main_options', array(
			'capability' => 'edit_theme_options',
			'theme_supports' => '',
			'title' => __('Writee Options', 'writee'),
			'description' => __('Panel to update writee theme options', 'writee'), // Include html tags such as <p>.
			'priority' => 10 // Mixed with top-level-section hierarchy.
		));
	
		$wp_customize->add_section('wrt_layout_options', array(
			'title'    => __('Layout Options', 'writee'),
			'priority' => 30,
			'panel' => 'wrt_main_options'
		));
		
		$wp_customize->add_setting('wrt_sidebar_position', array(
			'default' => '2cr',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_sidebar_position'
		));
		
		$wp_customize->add_control('wrt_sidebar_position', array(
			 'label' => __('Website Layout Options', 'writee'),
			 'section' => 'wrt_layout_options',
			 'type'    => 'select',
			 'description' => __('Choose between different layout options to be used as default', 'writee'),
			 'choices'    => array(
				'1c' =>   __('No Sidebar', 'writee'),
				'2cl' =>  __('Left Sidebar', 'writee'),
				'2cr' =>  __('Right Sidebar', 'writee')
			)
		));
		
		$wp_customize->add_setting('wrt_blog_post_excerpt', array(
			'default' => 'enable',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_radio'
		));
		
		$wp_customize->add_control('wrt_blog_post_excerpt', array(
			 'label' => __( 'Blog Excerpt', 'writee' ),
			 'section' => 'wrt_layout_options',
			 'type'    => 'radio',
			 'description' => __( 'Turn ON to show auto Excerpt and OFF to show full posts.', 'writee' ),
			 'choices'    => array(
				'enable' =>   __('ON', 'writee'),
				'disable' =>  __('OFF', 'writee'),
				
			)
		));
		
		/*slider settings */		

		$wp_customize->add_section('wrt_slider_options', array(
			'title'    => __('Slider Options', 'writee'),
			'priority' => 31,
			'panel' => 'wrt_main_options'
		));
		
		$wp_customize->add_setting('wrt_slider_enable', array(
			'default' => 'enable',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_radio'
		));
		
		$wp_customize->add_control('wrt_slider_enable', array(
			 'label' => __( 'Display Slider', 'writee' ),
			 'section' => 'wrt_slider_options',
			 'type'    => 'radio',
			 'description' => '',
			 'choices'    => array(
				'enable' =>   __('ON', 'writee'),
				'disable' =>  __('OFF', 'writee'),
				
			)
		));
		
		$wp_customize->add_setting('wrt_slider_display_type', array(
			'default' => 'fluid',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_slider_style'
		));
		
		$wp_customize->add_control('wrt_slider_display_type', array(
			'label' => __( 'Slider Display Style', 'writee' ),
			'section' => 'wrt_slider_options',
			'type'    => 'select',
			'description' => '',
			'choices'    => array(
				'fluid'     =>   __('Fluid Layout', 'writee'),
				'non-fluid' =>  __('Non Fluid Layout', 'writee')
			)
		));
		
		
		$wp_customize->add_setting('wrt_slider_content', array(
			'default' => 'latest',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_slider_content'
		));
		
		$wp_customize->add_control('wrt_slider_content', array(
			'label' => __( 'Slider Display Style', 'writee' ),
			'section' => 'wrt_slider_options',
			'type'    => 'select',
			'description' => '',
			'choices'    => array(
				'latest'     =>  __('Latest added posts', 'writee'),
				'category' =>  __('Specific category', 'writee')				
			)
		));
		
		$wp_customize->add_setting('wrt_slider_post_cats', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_strip_slashes'
		));
		
		$wp_customize->add_control('wrt_slider_post_cats', array(
			'label' => __( 'Slider Post Categories', 'writee' ),
			'section' => 'wrt_slider_options',
			'type'    => 'text',
			'description' => __( 'Add post categories id comma separated (This option valid only for SPECIFIC CATEGORY view selection).', 'writee' ),
			 
		));
		
		$wp_customize->add_setting('wrt_slider_posts', array(
			'default' => '5',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_number'
		));
		
		$wp_customize->add_control('wrt_slider_posts', array(
			'label' => __( 'Number of slides', 'writee' ),
			'section' => 'wrt_slider_options',
			'type'    => 'number',
			'description' => __( 'Choose the number of slides you want to display', 'writee' ),
			'input_attrs' => array(
				'min' => 1,
				'max' => 12,
				'step' => 1,
			),
		));
		
		$wp_customize->add_setting('wrt_slider_content_meta', array(
			'default' => 'enable',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_radio'
		));
		
		$wp_customize->add_control('wrt_slider_content_meta', array(
			'label'   => __( 'Slider Content Meta', 'writee' ),
			'section' => 'wrt_slider_options',
			'type'    => 'radio',
			'description' => __( 'Choose slider content meta display or not.', 'writee' ),
			'choices'    => array(
				'enable' =>   __('ON', 'writee'),
				'disable' =>  __('OFF', 'writee'),
				
			) 
		));
		
		$wp_customize->add_setting('wrt_slider_duration', array(
			'default' => '5000',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_number'
		));
		
		$wp_customize->add_control('wrt_slider_duration', array(
			'label' => __( 'Slide Duration', 'writee' ),
			'section' => 'wrt_slider_options',
			'type'    => 'number',
			'description' => __( 'Choose the number of slides you want to display', 'writee' ),
			'input_attrs' => array(
				'min' => 3000,
				'max' => 20000,
				'step' => 500,
			),
		));
		
		/* color section setting */
		
		$wp_customize->add_section('wrt_color_settinges', array(
			'title'    => __('Theme Colors Section', 'writee'),
			'description' => __('Theme primary color','writee'),
			'panel' => 'wrt_main_options',
			'priority' => 32,
		));
		
		$wp_customize->add_setting('wrt_primary_color', array(
			'default' => '#bf9e3b',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_hexcolor'
		));
	
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wrt_primary_color', array(
			'label' => __('Primary Color', 'writee'), 
			'section' => 'wrt_color_settinges',
		)));
		
		/*Typography settings */
		
		$wp_customize->add_section('wrt_typography_options', array(
			'title'    => __( 'Typography', 'writee' ),
			'priority' => 33,
			'panel' => 'wrt_main_options'
		));
		
		$wp_customize->add_setting('wrt_font_url', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_font_url', array(
			 'label' => __('Google Font URL', 'writee'),
			 'section' => 'wrt_typography_options',
			 'type'    => 'text',
			 'description' => sprintf('%1$s <a href="%2$s" target="_blank">%3$s</a> %4$s',
				__( 'Insert', 'writee' ),
				esc_url('https://www.google.com/fonts'),
				__('Google Font URL' , 'writee'),
				__('for embed fonts.' ,'writee')
				),
			
		));
		
		$wp_customize->add_setting('wrt_font_family', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_strip_slashes'
		));
		
		$wp_customize->add_control('wrt_font_family', array(
			 'label' => __('Font Family', 'writee'),
			 'section' => 'wrt_typography_options',
			 'type'    => 'text',
			 'description' => __( 'Insert Font Family.', 'writee' ),
			
		));
		
		$wp_customize->add_setting('wrt_font_size', array(
			'default' => '14',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_number'
		));
		
		$wp_customize->add_control('wrt_font_size', array(
			'label' => __('Font Size', 'writee'),
			'section' => 'wrt_typography_options',
			'type'    => 'number',
			'description' => __( 'Select Font Size.', 'writee' ),
			'input_attrs' => array(
				'min' => 10,
				'max' => 40,
				'step' => 1,
			),
		));
		
		
		$wp_customize->add_setting('wrt_line_height', array(
			'default'     => '1.2',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_number'
		));
		
		$wp_customize->add_control('wrt_line_height', array(
			'label' => __('Line Height', 'writee'),
			'section' => 'wrt_typography_options',
			'type'    => 'number',
			'description' => __( 'Slect Line Height.', 'writee' ),
			'input_attrs' => array(
				'min' => '0',
				'max' => '4',
				'step' => '0.1',
			),
		));
		
		$wp_customize->add_setting('wrt_letter_space', array(
			'default' => '0',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_number'
		));
		
		$wp_customize->add_control('wrt_letter_space', array(
			'label'   => __('Letter Space', 'writee'),
			'section' => 'wrt_typography_options',
			'type'    => 'number',
			'description' => __( 'Select Letter Space.', 'writee' ),
			'input_attrs' => array(
				'min' => '-20',
				'max' => '4',
				'step' => '1',
			),
		));
		
		
		/* social media buttons */
		
		$wp_customize->add_section('wrt_social_options', array(
			'title'    =>  __( 'Social Media Icons', 'writee' ),
			'priority' => 34,
			'panel' => 'wrt_main_options'
		));
		
		$wp_customize->add_setting('wrt_social_button_style', array(
			'default' => 'default-colors',
			'transport'   => 'refresh',
			'sanitize_callback' => 'writee_sanitize_social_style'
		));
		
		$wp_customize->add_control('wrt_social_button_style', array(
			'label'   => __('Social Button Style', 'writee'), 
			'section' => 'wrt_social_options',
			'type'    => 'select',
			'choices'  => array(
				'default-colors'  => __('Default Color', 'writee'),
				'theme-colors'    => __('Theme Color', 'writee'),
				'original-colors' => __('Icon Original Color', 'writee')
			),
			
		));
		
		$wp_customize->add_setting('wrt_facebook', array(
			'default'  => '',
			'transport' => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_facebook', array(
			'label'   => __('Facebook', 'writee'),
			'section' => 'wrt_social_options',
			'type'    => 'text'
		));
		
		$wp_customize->add_setting('wrt_twitter', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_twitter', array(
			'label'   => __('Twitter', 'writee'),
			'section' => 'wrt_social_options',
			'type'    => 'text'
		));
		
		$wp_customize->add_setting('wrt_instagram', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_instagram', array(
			'label'   => __('Instagram', 'writee'),
			'section' => 'wrt_social_options',
			'type'    => 'text'
		));
		
		$wp_customize->add_setting('wrt_googleplus', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_googleplus', array(
			'label'   => __('Google+', 'writee'),
			'section' => 'wrt_social_options',
			'type'    => 'text'
		));
		
		$wp_customize->add_setting('wrt_linkedin', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_linkedin', array(
			'label'   => __('LinkedIn', 'writee'),
			'section' => 'wrt_social_options',
			'type'    => 'text',
		));
		
		$wp_customize->add_setting('wrt_youtube', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_youtube', array(
			'label'   => __('YouTube', 'writee'),
			'section' => 'wrt_social_options',
			'type'    => 'text',
		));
		
		$wp_customize->add_setting('wrt_pinterest', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_pinterest', array(
			'label'   => __('Pinterest', 'writee'),
			'section' => 'wrt_social_options',
			'type'    => 'text',
		));
		
		$wp_customize->add_setting('wrt_dribble', array(
			'default' => '',
			'transport'   => 'refresh',
			'sanitize_callback' => 'esc_url_raw'
		));
		
		$wp_customize->add_control('wrt_dribble', array(
			'label'   => __('Dribbble', 'writee'),
			'section' => 'wrt_social_options',
			'type'    => 'text',
		));
		
		/* footer section */

		$wp_customize->add_section('wrt_footer_content', array(
			'title'    => __('Footer Section', 'writee'),
			'description' => __('Footer section content settings','writee'),
			'priority' => 121,
		));
		
		$wp_customize->add_setting( 'wrt_copyright', array(
			'default' => '',
			'transport' => 'refresh', // or refresh
			'sanitize_callback' => 'writee_sanitize_strip_slashes'
		) );

		$wp_customize->add_control( 'wrt_copyright', array(
		  'label'   => __( 'Copyright Text', 'writee'),
		  'type'    => 'textarea',
		  'section' => 'wrt_footer_content',
		) );
		
		
		$wp_customize->add_section( 'theme_detail', array(
			'title'    => __( 'About Theme', 'writee' ),
			'priority' => 160
		) );
	
		
		$wp_customize->add_setting( 'upgrade_text', array(
			'default' => '',
			'sanitize_callback' => '__return_false'
		) );
		
		$wp_customize->add_control( new Writee_Customize_Static_Text_Control( $wp_customize, 'upgrade_text', array(
			'section'     => 'theme_detail',
			'label'       => __( 'About Theme', 'writee' ),
			'description' => array(
				sprintf('Theme Name: %1$s<br/>
				Version: %2$s<br/>
				Author: %3$s<br/>
				Author URL: 
				<a href="'.esc_url('%4$s').'" target="_blank">%5$s</a><br/>
				Theme URL: <a href="'.esc_url('%6$s').'" target="_blank">%7$s</a><br/>',
				$writee_theme->get( 'Name' ),
				$writee_theme->get( 'Version' ),
				$writee_theme->get( 'Author' ),
				$writee_theme->get( 'AuthorURI' ),
				$writee_theme->get( 'AuthorURI' ),
				$writee_theme->get( 'ThemeURI' ),
				$writee_theme->get( 'ThemeURI' )
				),
				
			),
		) ) );
	
	}
	add_action( 'customize_register', 'writee_theme_customize_register' );

/**
 * Adds sanitization callback function: Strip Slashes
 * @package writee
 */
function writee_sanitize_strip_slashes($input) {
    return wp_kses_stripslashes($input);
}

/**
 * Adds sanitization callback function: colors
 * @package writee
 */
function writee_sanitize_hexcolor($color) {
    if ($unhashed = sanitize_hex_color_no_hash($color))
        return '#' . $unhashed;
    return $color;
}
	

/**
 * Adds sanitization callback function: Number
 * @package writee
 */
function writee_sanitize_number($input) {
    if ( isset( $input ) && is_numeric( $input ) ) {
        return $input;
    }
}

/**
 * Adds sanitization callback function: Number float
 * @package writee
 */
 

/**
 * Adds sanitization callback function: Sidebar Layout
 * @package writee
 */
function writee_sanitize_sidebar_position( $input ) {
   $site_layout = array(
		'1c' =>   __('No Sidebar', 'writee'),
		'2cl' =>  __('Left Sidebar', 'writee'),
		'2cr' =>  __('Right Sidebar', 'writee')
	);
    if ( array_key_exists( $input, $site_layout ) ) {
        return $input;
    } else {
        return '';
    }
}

function writee_sanitize_social_style( $input ) {
   $social_btn_style = array(
		'default-colors'  => __('Default Color', 'writee'),
		'theme-colors'    => __('Theme Color', 'writee'),
		'original-colors' => __('Icon Original Color', 'writee')
	);
    if ( array_key_exists( $input, $social_btn_style ) ) {
        return $input;
    } else {
        return '';
    }
}

function writee_sanitize_slider_style( $input ) {
   $slider_style = array(
		'fluid'     =>   __('Fluid Layout', 'writee'),
		'non-fluid' =>  __('Non Fluid Layout', 'writee')
	);
    if ( array_key_exists( $input, $slider_style ) ) {
        return $input;
    } else {
        return '';
    }
}

function writee_sanitize_slider_content( $input ) {
   $slider_style = array(
		'latest'     =>  __('Latest added posts', 'writee'),
		'category'   =>  __('Specific category', 'writee')
	);
    if ( array_key_exists( $input, $slider_style ) ) {
        return $input;
    } else {
        return '';
    }
}

function writee_sanitize_radio( $input ) {
   $radio_options = array(
		'enable'     => __('ON', 'writee'),
		'disable'    => __('OFF', 'writee')
	);
    if ( array_key_exists( $input, $radio_options ) ) {
        return $input;
    } else {
        return '';
    }
}
/**
 * Add CSS for custom controls
 */
function writee_customizer_custom_control_css() {
	?>
    <style>
       div.button.icon-picker {
		font-size: 24px;
		height: 45px;
		width: 45px;
		margin: 0;
		padding: 0;
		line-height: 45px;
		text-align: center;
	}
#customize-control-blogname span.customize-control-title, #customize-control-blogdescription span.customize-control-title, #customize-control-site_icon span.customize-control-title, 
#customize-control-background_color span.customize-control-title{
	padding: 13px 0px 0px 0px;
	font-weight: bold;
    display: block;
}

 #customize-control-blogname label, #customize-control-blogdescription label, #customize-control-site_icon label, #customize-control-background_color label{
    padding: 0px 0px 10px;
    display: block;
}
#social-share .fb-like{ margin-bottom:20px;}
    </style>
<?php
}
add_action( 'customize_controls_print_styles', 'writee_customizer_custom_control_css' );



if ( ! class_exists( 'WP_Customize_Control' ) )
    return NULL;
class Writee_Customize_Static_Text_Control extends WP_Customize_Control {
	/**
	 * Control type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'static-text';

	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );
	}

	protected function render_content() {
		if ( ! empty( $this->label ) ) :
			?><span class="WRT-customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
		endif;

		if ( ! empty( $this->description ) ) :
			?><div class="WRT-description WRT-customize-control-description"><?php

			if( is_array( $this->description ) ) {
				echo '<p>' . implode( '</p><p>', $this->description ) . '</p>';
			} else {
				echo $this->description;
			}

			?>
			
			<p style="text-align:center;">
				<a href="<?php echo esc_url('http://www.scissorthemes.com/theme/writee-pro/'); ?>" traget="_blank" class="button button-primary button-hero WRT-customizer-link WRT-customizer-upgrade"><?php esc_html_e( 'Upgrade To Pro', 'writee' ); ?></a>
			</p>		
			<p style="text-align:center;">
				<a href="<?php echo esc_url('http://www.scissorthemes.com/doc/writee/'); ?>" traget="_blank" class="button button-secondary button-hero WRT-customizer-link WRT-customizer-documentation"><?php esc_html_e( 'Documentation', 'writee' ); ?></a>
			</p>
			</div>
			
			
			<div class="inside">
				
				<p><?php _e('The best way to contact us with <b>support questions</b> and <b>bug reports</b> is via','writee') ?> 
				<a href="<?php echo esc_url('http://www.scissorthemes.com/forums/') ?>" target="_blank">
				<?php _e('Writee support forum','writee') ?></a>.
				</p>
				<p><?php _e('If you like this theme, I\'d appreciate any of the following:','writee') ?></p>
				<ul>
					<li>
					<a class="button button-secondary" href="<?php echo esc_url('https://wordpress.org/support/theme/writee/reviews/#new-post') ?>" title="<?php esc_attr_e('Rate this Theme', 'writee'); ?>" target="_blank"><?php printf(__('Rate this Theme','writee')); ?></a>
					</li>
					
				</ul>
			</div>
		
		<?php
		endif;

	}

}

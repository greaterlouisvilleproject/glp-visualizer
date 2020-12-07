<?php

class GLPVisualizer {
	
	private $glp_visualizer_options;
	protected $shinyserver;
	protected $downloadable;
	
	public function __construct() {
		$this->glp_visualizer_options = get_option('glp_visualizer_option_name');
		$this->initOptions();
		
		$this->shinyserver = $this->glp_visualizer_options['shiny_server'];
		$this->downloadable = get_home_url().$this->glp_visualizer_options['form_location'];
		
		$this->initAdmin();
		$this->loadScripts();
		$this->bindShortcodes();
	}
	
	private function checkInvalid($str) {
		return $this->glp_visualizer_options === false || $this->glp_visualizer_options[$str] === false;
	}
	
	private function initOptions() {
		if($this->checkInvalid($this->glp_visualizer_options['shiny_server'])) {
			$this->glp_visualizer_options['shiny_server'] = 'https://greaterlou.shinyapps.io/';
			update_option('glp_visualizer_option_name', $this->glp_visualizer_options);
		}
		if($this->checkInvalid($this->glp_visualizer_options['form_location'])) {
			$this->glp_visualizer_options['form_location'] = '/path-to-form/';
			update_option('glp_visualizer_option_name', $this->glp_visualizer_options);
		}
	}
	
	private function initAdmin() {
		add_action('admin_menu', array($this, 'glp_visualizer_add_plugin_page'));
		add_action('admin_init', array($this, 'glp_visualizer_page_init'));
	}
	
	public function glp_visualizer_add_plugin_page() {
		add_menu_page(
			'GLP Visualizer', // page_title
			'GLP Visualizer', // menu_title
			'manage_options', // capability
			'glp-visualizer', // menu_slug
			array($this, 'glp_visualizer_create_admin_page'), // function
			'dashicons-media-spreadsheet', // icon_url
			81 // position
		);
	}
	
	public function glp_visualizer_create_admin_page() {
		$this->glp_visualizer_options = get_option('glp_visualizer_option_name');
		?>
		<div class="wrap">
			<h2>GLP Visualizer</h2>
			<p>Manage settings for the GLP Visualizer plugin</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields('glp_visualizer_option_group');
					do_settings_sections('glp-visualizer-admin');
					submit_button();
				?>
			</form>
		</div>
		<?php
	}
	
	public function glp_visualizer_page_init() {
		register_setting(
			'glp_visualizer_option_group', // option_group
			'glp_visualizer_option_name', // option_name
			array($this, 'glp_visualizer_sanitize') // sanitize_callback
		);

		add_settings_section(
			'glp_visualizer_setting_section', // id
			'Settings', // title
			array($this, 'glp_visualizer_section_info'), // callback
			'glp-visualizer-admin' // page
		);

		add_settings_field(
			'shiny_server', // id
			'Shiny Server', // title
			array($this, 'shiny_server_callback'), // callback
			'glp-visualizer-admin', // page
			'glp_visualizer_setting_section' // section
		);
		
		add_settings_field(
			'form_location', // id
			'Questionnaire Location', // title
			array( $this, 'form_location_callback' ), // callback
			'glp-visualizer-admin', // page
			'glp_visualizer_setting_section' // section
		);
	}
	
	public function glp_visualizer_sanitize($input) {
		
		$sanitary_values = array();
		
		if (isset( $input['shiny_server'])) {
			$sanitary_values['shiny_server'] = sanitize_text_field($input['shiny_server']);
		}
		
		if (isset( $input['form_location'])) {
			$sanitary_values['form_location'] = sanitize_text_field($input['form_location']);
		}

		return $sanitary_values;
	}
	
	public function glp_visualizer_section_info() {}
	
	public function shiny_server_callback() {
		printf('<input class="regular-text" type="text" name="glp_visualizer_option_name[shiny_server]" id="shiny_server" value="%s">', isset($this->glp_visualizer_options['shiny_server']) ? esc_attr($this->glp_visualizer_options['shiny_server']) : '');
	}
	
	public function form_location_callback() {
		printf('<input class="regular-text" type="text" name="glp_visualizer_option_name[form_location]" id="form_location" value="%s">', isset($this->glp_visualizer_options['form_location']) ? esc_attr($this->glp_visualizer_options['form_location']) : '');
	}
	
	private function loadScripts() {
		add_action('wp_enqueue_scripts', function() {
			wp_register_style('glp-visualizer', plugins_url('css/style.css', __FILE__));
			wp_enqueue_style('glp-visualizer');
			wp_register_script('glp-visualizer', plugins_url('js/helpers.js', __FILE__), [], false, true);
			wp_enqueue_script('glp-visualizer');
		});
	}
	
	private function bindShortcodes() {
		add_shortcode('glpdata', function($atts) {
			$defaults = [
				'width' => '100%',
				'height' => '500',
				'scrolling' => 'no',
				'data' => '',
				'class' => 'glp-data',
				'frameborder' => '0',
			];
			
			foreach($defaults as $default => $value) {
				if(!@array_key_exists($default, $atts)) {
					$atts[$default] = $value;
				}
			}
			
			$has_scr = false;
			$html = '<iframe ';
			foreach($atts as $attr => $value) {
				if(strtolower($attr) == 'src') {
					$has_scr = true;
					$html .= ' src="'.$this->shinyserver.$value.'"';
				} elseif(!in_array(strtolower($attr), ['onload', 'onpageshow', 'onclick'])) {
					$html .= ' '.esc_attr($attr).($value ? '="'.esc_attr($value).'"' : '');
				}
			}
			$html .= '></iframe>'."\n";
			
			if($atts['data'] != '') {
				$this->downloadable .= '?glp-downloadable=https://github.com/greaterlouisvilleproject/glp-downloadable/raw/main/'.$atts['data'];
				$html .= '<div class="button__container"><a class="button" href="'.$this->downloadable.'" target="_blank" rel="noopener">Download Data</a></div>';
			}
			
			if(!$has_scr) {
				$html = '<p class="glpdata-error">Visualizer Error:<br>The <code>src</code> attribute must be set for GLP elements.</p>';
			}
			
			return $html;
		});
	}
}

<?php

class GLPVisualizer {
	
	protected static $shinyserver;
	
	public function __construct() {
		$this->shinyserver = 'https://greaterlou.shinyapps.io/'; //TODO
		$this->loadScripts();
		$this->bindShortcodes();
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
			
			if(!$has_scr) {
				$html = '<p class="glpdata-error">Visualizer Error:<br>The <code>src</code> attribute must be set for GLP elements.</p>';
			}
			
			return $html;
		});
	}
}

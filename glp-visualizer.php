<?php
/**
 * @wordpress-plugin
 * Plugin Name:		GLP Visualizer
 * Plugin URI:		https://github.com/greaterlouisvilleproject/glp-visualizer
 * Description:		Bar charts and line graphs and geography, oh my!
 * Version:			0.1
 * Author:			UofL Intern Team
 * License:			GPL-3.0+
 * License URI:		http://www.gnu.org/licenses/gpl-3.0.txt
 */

// Abort if this file is called directly.
if(!defined('WPINC')) {
	die();
}

// Includes the visualizer library
require plugin_dir_path( __FILE__ ).'includes/glp-visualizer.class.php';

// Runs a new instance of the plugin
$plugin = new GLPVisualizer();

?>
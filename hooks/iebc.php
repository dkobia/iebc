<?php defined('SYSPATH') or die('No direct script access.');
/**
 * IEBC Hook - Load All Events
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class iebc {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{	
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		$county_tree_view = county::get_county_tree_view();

		// Add Sidebar Box
		Event::add('ushahidi_action.main_sidebar', array($this, 'county_sidebar'));

		// Only add the events if we are on that controller
		if (Router::$controller == 'main')
		{
			// Add County/Constituency/Polling Station Layers
			Event::add('ushahidi_action.header_scripts', array($this, 'main_js'));

			plugin::add_stylesheet('iebc/views/css/iebc');
		}
	}

	public function county_sidebar()
	{
		$box = View::factory('iebc/sidebar');
		$box->counties = self::_counties();
		$box->render(TRUE);
	}

	public function main_js()
	{
		$js = View::factory('iebc/js');
		$js->render(TRUE);
	}

	/**
	 * Gets the county data for the specified county
	 */
	private function _county_data()
	{
		if ($_GET)
		{
			// Get the county id
			$county_id = $_GET['county_id'];

			// Get the layer file
			$county = new County_Model($county_id);
			$layer_file = $county->county_layer_file;
			if ( ! empty($layer_file))
			{
				// Set the URL for the layer file
				$layer_file = url::base().Kohana::config('upload.relative_directory').'/'.$layer_file;
			}

			// Build output JSON
			echo file_get_contents($layer_file);
		}
		else
		{
			$json_output = json_encode(array(
				'success' => FALSE
			));
		}

		// Flush the
		header("Content-type: application/json; charset=utf-8");
		print $json_output;
	}

	//Get all Counties
	private function _counties()
	{
		$counties = ORM::factory('county')
			->orderby('county_name')
			->find_all();

		return $counties;
	}
}

new iebc;
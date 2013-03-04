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
		// Add Sidebar Box
		Event::add('ushahidi_action.main_sidebar', array($this, 'county_sidebar'));

		// Only add the events if we are on that controller
		if (Router::$controller == 'main')
		{
			// Add County/Constituency/Polling Station Layers
			Event::add('ushahidi_action.header_scripts', array($this, 'main_js'));

			plugin::add_stylesheet('iebc/views/css/iebc');
		}
		
		if (stripos(Router::$current_uri, "admin/reports/download") !== false)
		{
			Event::add('ushahidi_filter.report_download_csv_header', array($this, 'csv_header'));
			Event::add('ushahidi_filter.report_download_csv_incident', array($this, 'csv_incident'));
			Event::add('ushahidi_action.reports_download_form_data', array($this, 'reports_download_form_data'));
		}

		// Add Constituency Select Parameters
		Event::add('ushahidi_filter.fetch_incidents_set_params', array($this, 'filter'));	

		// Add Sidebar Box
		Event::add('ushahidi_action.main_sidebar', array($this, 'county_sidebar'));
	}

	/**
	 * Main page right side bar
	 */
	public function county_sidebar()
	{
		$box = View::factory('iebc/sidebar');
		$box->counties = self::_counties();
		$box->render(TRUE);
	}

	/**
	 * Main inline JS
	 */
	public function main_js()
	{
		$js = View::factory('iebc/js');
		$js->render(TRUE);
	}

	/**
	 * Hook into the report map filter
	 */
	public function filter()
	{
		if (isset($_GET['cty']) AND (int) $_GET['cty'])
		{
			$db = new Database();

			$county_id = (int) $_GET['cty'];
			$sql = "SELECT AsText(geometry) as geometry
					FROM ".Kohana::config('database.default.table_prefix')."county 
					WHERE id = ?";
			$query = $db->query($sql, $county_id);
			$geometry = FALSE;
			foreach ( $query as $item )
			{
				$geometry = $item->geometry;
			}

			if ($geometry)
			{
				$filter = " MBRContains(GeomFromText('".$geometry."'), GeomFromText(CONCAT_WS(' ','Point(',l.longitude, l.latitude,')')))";

				// Finally add to filters params
				array_push(Event::$data, $filter);
			}
		}

		if (isset($_GET['cst']) AND (int) $_GET['cst'])
		{
			$db = new Database();

			$constituency_id = (int) $_GET['cst'];
			$sql = "SELECT AsText(geometry) as geometry
					FROM ".Kohana::config('database.default.table_prefix')."constituency 
					WHERE id = ?";
			$query = $db->query($sql, $constituency_id);
			$geometry = FALSE;
			foreach ( $query as $item )
			{
				$geometry = $item->geometry;
			}

			if ($geometry)
			{
				$filter = " MBRContains(GeomFromText('".$geometry."'), GeomFromText(CONCAT_WS(' ','Point(',l.longitude, l.latitude,')')))";

				// Finally add to filters params
				array_push(Event::$data, $filter);
			}
		}
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

	/**
	 * Get All the Counties
	 */
	private function _counties()
	{
		$counties = ORM::factory('county')
			->orderby('county_name')
			->find_all();

		return $counties;
	}
	
	public function csv_header()
	{
		$header = Event::$data;
		if (!empty($_POST['data_include_county']))
		{
			$header .= ",CONSTITUENCY_NAME,CONSTITUENCY_CODE,COUNTY_NAME,COUNTY_CODE";
		}
		Event::$data = $header;
	}
	
	public function csv_incident()
	{
		$data = Event::$data;
		$incident = $data['incident'];
		$csv_text = $data['report_csv'];
		if (!empty($_POST['data_include_county']))
		{
			
			$db = Database::instance();
			
			$constituency = $db->query("
				SELECT c.constituency_name, c.const_code, county_name, county.county_code FROM constituency c
				LEFT JOIN county ON (county.id = c.county_id)
				WHERE
				MBRContains(c.geometry, GeomFromText('Point(? ?)'))
				LIMIT 0,1 
			", $incident->location->longitude, $incident->location->latitude);
			
			if ($constituency->count() > 0) {
				$result = $constituency->result_array(FALSE);
				$csv_text .= ','.implode(',', $result[0]);
			}
			else
			{
				$csv_text .= ',,,,';
			}
			
		}
		Event::$data = array("report_csv" => $csv_text, "incident" => $incident);
	}
	
	public function reports_download_form_data()
	{
		
		echo "
		<tr>
			<td>".form::checkbox('data_include_county','1',FALSE)." Include County/Constituency data</td>
			<td></td>
		</tr>
		";
		
	}
	
}

new iebc;
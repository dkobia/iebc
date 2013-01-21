<?php defined('SYSPATH') or die('No direct script access.');

class Iebc_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * GET County GeoJSON
	 */
	public function county($id = 0)
	{
		if ($id)
		{
			$db = new Database();

			// Get Geometries via raw SQL query as ORM can't handle Spatial Data
			// Retrieve WKT data for OpenLayers
			$sql = "SELECT county_name, AsText(geometry) as geometry, longitude, latitude 
				FROM ".Kohana::config('database.default.table_prefix')."county 
				WHERE id = ?";
			$query = $db->query($sql, $id);
			foreach ( $query as $item )
			{
				echo $item->geometry;
			}
		}
		else
		{
			
		}
	}
}
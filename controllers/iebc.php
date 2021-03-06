<?php defined('SYSPATH') or die('No direct script access.');

class Iebc_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * GET County GeoJSON
	 * @param $int id county_id
	 */
	public function county($id = 0)
	{
		$json = array(
			'name' => '',
			'features' => '',
			'children' => array()
		);

		if ($id)
		{
			$db = new Database();

			// Get Geometries via raw SQL query as ORM can't handle Spatial Data
			// Retrieve WKT data for OpenLayers
			$sql = "SELECT id, county_name, AsText(geometry) as geometry 
				FROM ".Kohana::config('database.default.table_prefix')."county 
				WHERE id = ?";
			$query = $db->query($sql, $id);
			foreach ( $query as $item )
			{
				$json = array(
					'name' => $item->county_name,
					'features' => $item->geometry,
					'children' => array()
				);

				$sql2 = "SELECT id, constituency_name, AsText(geometry) as geometry
					FROM ".Kohana::config('database.default.table_prefix')."constituency 
					WHERE county_id = ?";
				$query2 = $db->query($sql2, $item->id);
				foreach ( $query2 as $item2 )
				{
					$json['children'][] = array(
						'name' => $item2->constituency_name,
						'features' => $item2->geometry,
						'children' => array()
					);
				}
			}
		}

		header('Content-type: application/json; charset=utf-8');
		echo json_encode($json);
	}

	/**
	 * GET Constituency GeoJSON
	 * @param $int id constituency_id
	 */
	public function constituency($id = 0)
	{
		$json = array(
			'name' => '',
			'features' => '',
			'children' => array()
		);
		
		if ($id)
		{
			$db = new Database();

			// Get Geometries via raw SQL query as ORM can't handle Spatial Data
			// Retrieve WKT data for OpenLayers
			$sql = "SELECT id, constituency_name, AsText(geometry) as geometry 
				FROM ".Kohana::config('database.default.table_prefix')."constituency 
				WHERE id = ?";
			$query = $db->query($sql, $id);
			foreach ( $query as $item )
			{
				$json = array(
					'name' => $item->constituency_name,
					'features' => $item->geometry,
					'children' => array()
				);
			}
		}

		header('Content-type: application/json; charset=utf-8');
		echo json_encode($json);
	}

	/**
	 * GET Polling Stations GEOJSON
	 * @param $int id constituency_id
	 */
	public function polling($id = 0)
	{
		$json_features = array();
		$stations = ORM::factory('polling')
			->where('constituency_id', $id)
			->find_all();

		foreach ($stations as $station)
		{
			$json_item = array();
			$json_item['type'] = 'Feature';
			$json_item['properties'] = array(
				'id' => $station->id,
				'name' => $station->polling_name,
				'link' => '',
				'category' => '',
				'color' => '#25B200',
				'icon' => '',
				'thumb' => '',
				'timestamp' => strtotime("now")
			);
			$json_item['geometry'] = array(
				'type' => 'Point',
				'coordinates' => array($station->longitude, $station->latitude)
			);

			array_push($json_features, $json_item);
		}

		$json = json_encode(array(
			"type" => "FeatureCollection",
			"features" => $json_features
		));

		header('Content-type: application/json; charset=utf-8');
		echo $json;
	}
}
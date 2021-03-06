<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Constituencies
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Constituency_Model extends ORM {	

	
	/**
	 * Database table name
	 * @var string
	 */
	protected $table_name = 'constituency';

	/**
	 * Many-to-one relationship definition
	 * @var array
	 */
	protected $belongs_to = array('county');

	/**
	 * One-to-many relationship definition
	 * @var array
	 */
	protected $has_many = array('ward', 'polling');
	
}

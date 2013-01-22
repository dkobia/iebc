<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Wards
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

class Ward_Model extends ORM {	

	
	/**
	 * Database table name
	 * @var string
	 */
	protected $table_name = 'ward';

	/**
	 * Many-to-one relationship definition
	 * @var array
	 */
	protected $belongs_to = array('constituency');
}

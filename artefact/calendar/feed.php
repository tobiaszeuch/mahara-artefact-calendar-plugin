<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-calendar
 * @author     Angela Karl, Uwe Boettcher
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Technische Universitaet Darmstadt, Germany
 *
 */

define('INTERNAL', 0);
define('MENUITEM', 'calendar');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'calendar');
define('SECTION_PAGE', 'index');

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(dirname(dirname(dirname(__FILE__))).'/artefact/calendar/lib.php')      ;
require_once(dirname(dirname(dirname(__FILE__))).'/artefact/plans/lib.php')  ;
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'artefact/lib.php');

define('TITLE', get_string('calendar', 'artefact.calendar'));

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 100);

$plans = ArtefactTypePlan::get_plans($offset, $limit);

ArtefactTypeCalendar::build_feed($plans);

?>

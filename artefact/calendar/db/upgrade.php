<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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

defined('INTERNAL') || die();

function xmldb_artefact_calendar_upgrade($oldversion=0) {

    if ($oldversion < 2013062404) {
        $table = new XMLDBTable('artefact_calendar_reminder');
        drop_table($table);
        
        $table->addFieldInfo('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('reminder_type', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL);
        $table->addFieldInfo('reminder_date', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '-1', null);

        $table->addKeyInfo('reminder_pk', XMLDB_KEY_PRIMARY, array('user'));

        if (!create_table($table)) {
            throw new SQLException($table . " could not be created, check log for errors.");
        }
        execute_sql('ALTER TABLE {artefact_calendar_calendar} DROP COLUMN {reminder_status}');
    }
    return true;
    
}

?>

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Observer class for format_collapsibletopics plugin.
 *
 * @package    format_collapsibletopics
 * @author     Jean-Roch Meurisse
 * @copyright  2018 - Cellule TICE - Unversite de Namur
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/format/lib.php');

/**
 * Event observed by collapsibletopics format.
 */
class format_collapsibletopics_observer {
    /**
     * Observe user_loggedout event in order to delete topics collapse state for logging out user if necessary.
     *
     * @param \core\event\base $event
     * @throws coding_exception
     */
    public static function user_loggedout(core\event\base $event) {
        global $DB;
        if (!get_config('format_collapsibletopics', 'keepstateoversession')) {
            $eventdata = $event->get_data();
            $courses = enrol_get_all_users_courses($eventdata['userid']);
            foreach ($courses as $course) {
                if (course_get_format($course->id)->get_format() == 'collapsibletopics') {
                    $DB->delete_records_select('user_preferences', $DB->sql_like('name', ':name') . ' AND userid=:userid ',
                        array( 'name' => 'sections-toggle-' . $course->id, 'userid' => $eventdata['userid']));
                }
            }
        }
    }
}

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
 * Privacy Subsystem implementation for format_collapsibletopics.
 * Cloned from format_topics, only namespace changed.
 *
 * @package    format_collapsibletopics
 * @copyright 2018 - Cellule TICE - Unversite de Namur
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_collapsibletopics\privacy;

use \core_privacy\local\request\writer;
use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the user tours feature.
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin has some sitewide user preferences to export.
    \core_privacy\local\request\user_preference_provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $itemcollection The initialised item collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $items) : collection {
        // There are several user preferences.
        $items->add_user_preference('sections-toggle', 'privacy:metadata:preference:sectionstoggle');

        return $items;
    }

    /**
     * Store all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {

        $preferences = get_user_preferences();
        foreach ($preferences as $prefname => $prefvalue) {
            $courseid = null;
            if (strpos($prefname, 'sections-toggle-') === 0) {
                $courseid = substr($prefname, 16);
                $decodedprefvalue = (array)json_decode($prefvalue);
                $sectionsarray = array_keys($decodedprefvalue);
                $sections = implode(', ', $sectionsarray);
                writer::export_user_preference(
                    'format_collapsibletopics',
                    $prefname,
                    $sections,
                    get_string('privacy:request:preference:sectionstoggle', 'format_collapsibletopics', (object) [
                        'name' => $courseid,
                        'value' => $sections
                    ])
                );
            }
        }

    }
}
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
 * Format collapsibletopics settings file.
 *
 * @package    format_collapsibletopics
 * @author     Jean-Roch Meurisse
 * @copyright  2018 - Cellule TICE - Unversite de Namur
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot. '/course/format/singleactivity/settingslib.php');

if ($ADMIN->fulltree) {
    $name = 'format_collapsibletopics/keepstateoversession';
    $title = get_string('keepstateoversession', 'format_collapsibletopics');
    $description = get_string('keepstateoversession_desc', 'format_collapsibletopics');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);
}

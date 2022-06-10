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
 * Renderer for outputting the collapisbletopics course format.
 *
 * @package format_collapsibletopics
 * @copyright 2018 - Cellule TICE - Unversite de Namur
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

/**
 * Basic renderer for collapsingtopics format.
 *
 * @package    format_collapsibletopics
 * @copyright  2018 - Cellule TICE - Unversite de Namur
 * @copyright  2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_collapsibletopics_renderer extends format_section_renderer_base {

    /**
     * Overrides format_section_renderer_base implementation.
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {

        if (!isset($course->coursedisplay)) {
            $course->coursedisplay = COURSE_DISPLAY_SINGLEPAGE;
        }

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();
        $numsections = course_get_format($course)->get_last_section_number();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                // 0-section is displayed a little different then the others.
                if ($thissection->summary or !empty($modinfo->sections[0]) or $this->page->user_is_editing()) {
                    $this->page->requires->strings_for_js(array('collapseall', 'expandall'), 'moodle');
                    $modules = $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->section_header($thissection, $course, false, 0);
                    echo $modules;
                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo '<div class="collapsible-actions" >
    <a href="#" class="expandall" role="button">' . get_string('expandall') . '
    </a>
</div>';
                    echo $this->section_footer();
                }
                continue;
            }
            if ($section > $numsections) {
                // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo))
                || (!$thissection->visible && !$course->hiddensections);
            if (!$showsection) {
                continue;
            }

            $modules = $this->courserenderer->course_section_cm_list($course, $thissection, 0);
            $control = $this->courserenderer->course_section_add_cm_control($course, $section, 0);
            echo $this->section_header($thissection, $course, false, 0);

            if ($thissection->uservisible) {
                echo $modules;
                echo $control;
            }

            echo $this->section_footer();
        }

        if ($this->page->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $numsections or empty($modinfo->sections[$section])) {
                    // This is not stealth section or it is empty.
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo $this->change_number_sections($course, 0);
        } else {
            echo $this->end_section_list();
        }
    }

    /**
     * Generate the starting container html for a list of sections overrides format_section_renderer_base
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'accordion collapsibletopics', 'aria-multiselectable' => true));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Overrides format_section_renderer_base
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {

        $o = '';
        $sectionstyle = '';

        if ($course->sectionprogress) {
            $total = 0;
            $complete = 0;
            $cancomplete = isloggedin() && !isguestuser();
            $modinfo = get_fast_modinfo($course);
            $completioninfo = new completion_info($course);
            if (!empty($modinfo->sections[$section->section])) {
                foreach ($modinfo->sections[$section->section] as $cmid) {

                    $thismod = $modinfo->cms[$cmid];

                    if ($thismod->modname == 'label') {
                        // Labels are special (not interesting for students)!
                        continue;
                    }

                    if ($thismod->uservisible) {
                        if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                            $total++;
                            $completiondata = $completioninfo->get_data($thismod, true);
                            if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                                    $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                                $complete++;
                            }
                        }
                    }
                }
            }
        }

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o .= html_writer::start_tag('li', array('id' => 'section-' . $section->section,
            'class' => 'section main clearfix' . $sectionstyle, 'role' => 'region',
            'aria-labelledby' => "sectionid-{$section->id}-title"));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o .= html_writer::start_tag('div', array('class' => 'content'));

        // When not on a section page, we display the section titles except the general section if null.
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one.
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        if (!$this->page->user_is_editing()) {
            $sectionname = html_writer::tag('span', $this->section_title_without_link($section, $course),
                array('class' => 'sectionname'));
            // Add collapse toggle.
            if (course_get_format($course)->is_section_current($section)) {
                if ($course->sectionprogress && $total > 0) {
                    $o .= $this->section_progressbar($total, $complete);
                }
                $o .= '<a class="sectiontoggle' .
                    '" data-toggle="collapse" data-parent=".accordion" href="#collapse-' .
                    $section->section .
                    '" aria-expanded="true" aria-controls="collapse-' .
                    $section->section .
                    '">&nbsp;' . $sectionname . '</a> ';
            } else if ($section->section != 0) {
                if ($course->sectionprogress && $total > 0) {
                    $o .= $this->section_progressbar($total, $complete);
                }
                $o .= '<a class="sectiontoggle collapsed' .
                    '" data-toggle="collapse" data-parent=".accordion" href="#collapse-' .
                    $section->section .
                    '" aria-expanded="false" aria-controls="collapse-' .
                    $section->section .
                    '">&nbsp;' . $sectionname .
                    '</a> ';
            } else if ($section->section == 0 && !is_null($section->name)) {
                $o .= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
            }
            // End collapse toggle.

            $o .= '<div class="clearfix">';
            $o .= $this->section_availability($section) . '</div>';
            if ($section->uservisible || $section->visible) {
                // Show summary if section is available or has availability restriction information.
                // Do not show summary if section is hidden but we still display it because of course setting
                // "Hidden sections are shown in collapsed form".
                $o .= $this->section_summary($section, $course, null);
            }

        } else {
            $sectionname = html_writer::tag('span', $this->section_title_without_link($section, $course));

            // Add collapse toggle.
            if (course_get_format($course)->is_section_current($section)) {
                $o .= '<a class="sectiontoggle' .
                    '" data-toggle="collapse" data-parent=".accordion" href="#collapse-' .
                    $section->section .
                    '" aria-expanded="true" aria-controls="collapse-' .
                    $section->section .
                    '">&nbsp;</a> ';
            } else if ($section->section != 0) {
                $o .= '<a class="sectiontoggle collapsed' .
                    '" data-toggle="collapse" data-parent=".accordion" href="#collapse-' .
                    $section->section .
                    '" aria-expanded="false" aria-controls="collapse-' .
                    $section->section .
                    '">&nbsp;</a> ';
            }
            // End collapse toggle.

            $o .= '<div class="clearfix">' . $this->output->heading($sectionname, 3, 'sectionname' . $classes);
            $o .= $this->section_availability($section) . '</div>';
            if ($section->uservisible || $section->visible) {
                // Show summary if section is available or has availability restriction information.
                // Do not show summary if section is hidden but we still display it because of course setting
                // "Hidden sections are shown in collapsed form".
                $o .= $this->section_summary($section, $course, null);
            }
        }
        if (course_get_format($course)->is_section_current($section)) {
            $classes = 'collapse show';
        } else if ($section->section != 0) {
            $classes = 'collapse';
        } else {
            $classes = '';
        }
        $o .= '<div id="collapse-' .
            $section->section .
            '" class="' .
            $classes .
            '" role="tabpanel" aria-labelledby="heading' .
            $section->section .
            '">' .
            '<span class="hidden">' . $sectionname . '</span>';

        return $o;
    }

    /**
     * Generate the display of the footer part of a section
     *
     * @return string HTML to output.
     */
    protected function section_footer() {
        // Collapsing format needs has an extra div surrounding content to allow section collapsing.
        $o = html_writer::end_tag('div');
        $o .= parent::section_footer();

        return $o;
    }

    /**
     * Override to add spacer into current section too.
     * Generate the content to displayed on the left part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_left_content($section, $course, $onsectionpage) {
        $o = $this->output->spacer();

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (course_get_format($course)->is_section_current($section)) {
                $o .= get_accesshide(get_string('currentsection', 'format_'.$course->format));
            }
        }

        return $o;
    }

    /**
     * Override to separate section summary from section name.
     * @param stdClass $section
     * @param stdClass $course
     * @param array $mods
     * @return string
     */
    protected function section_summary($section, $course, $mods) {
        $o = '';
        $o .= html_writer::start_tag('div', array('class' => 'summarytext'));
        $o .= $this->format_summary_text($section);
        $o .= html_writer::end_tag('div');
        $o .= $this->section_activity_summary($section, $course, null);

        return $o;
    }

    /**
     * Override to display progression count only when section progress bar is disabled.
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_activity_summary($section, $course, $mods) {
        $modinfo = get_fast_modinfo($course);
        if (empty($modinfo->sections[$section->section])) {
            return '';
        }

        // Generate array with count of activities in this section.
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new completion_info($course);
        foreach ($modinfo->sections[$section->section] as $cmid) {
            $thismod = $modinfo->cms[$cmid];

            if ($thismod->uservisible) {
                if (isset($sectionmods[$thismod->modname])) {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                    $sectionmods[$thismod->modname]['count']++;
                } else {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
                    $sectionmods[$thismod->modname]['count'] = 1;
                }
                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                        $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                        $complete++;
                    }
                }
            }
        }

        if (empty($sectionmods)) {
            // No sections.
            return '';
        }

        // Output section activities summary.
        $o = '';
        $o .= html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
        foreach ($sectionmods as $mod) {
            $o .= html_writer::start_tag('span', array('class' => 'activity-count'));
            $o .= $mod['name'].': '.$mod['count'];
            $o .= html_writer::end_tag('span');
        }
        $o .= html_writer::end_tag('div');

        // Output section completion data.
        if (!$course->sectionprogress && $total > 0) {
            $a = new stdClass;
            $a->complete = $complete;
            $a->total = $total;

            $o .= html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
            $o .= html_writer::tag('span', get_string('progresstotal', 'completion', $a), array('class' => 'activity-count'));
            $o .= html_writer::end_tag('div');
        }

        return $o;
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {

        if (!$this->page->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic,
                                                   'data-action' => 'removemarker'));
            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic,
                                                   'data-action' => 'setmarker'));
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }

    /**
     * Generate the section progress bar
     *
     * @param int $total the number of activities in the section
     * @param int $complete the number of completed activities in the section
     * @return string
     * @throws coding_exception
     */
    protected function section_progressbar($total, $complete) {
        $o = '';
        $completion = new stdClass;
        $completion->complete = $complete;
        $completion->total = $total;
        $percenttext = get_string('sectionprogresstext', 'format_collapsibletopics');
        $percent = 0;
        $current = 0;

        if ($complete > 0) {
            $current = (int)$complete;
            $percent = (int)(($complete / $total) * 100);
        }

        $o .= '<div class="progress">' .
            '<div class="progress-bar progress-bar-info" role="progressbar" ' .
            'aria-valuenow="' . $current .'" aria-valuemin="0" aria-valuemax="' . $total .'" ' .
            'style="width: ' . $percent . '%;" ' .
            'data-tooltip="tooltip" data-placement="bottom" ' .
            'title="' . get_string('progresstotal', 'completion', $completion) . '">';
        $o .= '<div class="progresstest">';
        $o .= '<span class="sr-only">' . $percenttext . '</span>';
        $o .= '</div>';
        $o .= '</div>';
        $o .= '</div>';

        return $o;
    }
}

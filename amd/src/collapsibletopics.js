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
 * AMD module for the collapsible sections feature.
 *
 * @module    format_collapsibletopics
 * @author     Jean-Roch Meurisse
 * @copyright  2018 University of Namur - Cellule TICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/log', 'core/str'], function($, log, str) {

    "use strict";

    /**
     * Update toggles state of current course in browser storage.
     * @param {number} course The current course
     * @param {array} toggles The list of open sections
     * @param {String} storage The configured storage type (local/session)
     */
    var setState = function(course, toggles, storage) {
        if (storage == 'local') {
            window.localStorage.setItem('sections-toggle-' + course, JSON.stringify(toggles));
        } else if (storage == 'session') {
            window.sessionStorage.setItem('sections-toggle-' + course, JSON.stringify(toggles));
        }
    };

    /**
     * Update toggles state of current course in browser storage.
     * @param {number} course The current course
     * @param {String} storage The configured storage type (local/session)
     * @returns the list of open sections for the current course
     */
    var getState = function(course, storage) {
        var toggles;
        if (storage == 'local') {
            toggles = window.localStorage.getItem('sections-toggle-' + course);
        } else if (storage == 'session') {
            toggles = window.sessionStorage.getItem('sections-toggle-' + course);
        }
        if (toggles === null) {
            return {};
        } else {
            return JSON.parse(toggles);
        }
    };

    return {
        init: function(args) {
            log.debug('Format collapsibletopics AMD module initialized');
            $(document).ready(function($) {
                var sectiontoggles;
                var keepstateoversession = args.keepstateoversession;
                var storage;
                if (keepstateoversession == 1) {
                    // Use browser local storage.
                    storage = 'local';
                } else {
                    // Use browser session storage.
                    storage = 'session';
                }

                sectiontoggles = getState(args.course, storage);

                setTimeout(function() {
                    for (var section in sectiontoggles) {
                        section = '#collapse-' + parseInt(section);
                        $(section).collapse('show');
                    }
                    var currentcontent = $('.section.current a.sectiontoggle').attr('href');
                    $(currentcontent).collapse('show');
                }, 50);

                // Handle toggle all sections.
                $('body').on('click', '.expandall', function(event) {
                    event.preventDefault();
                    var target = event.target;
                    $(target).removeClass('expandall').addClass('collapseall').html(M.util.get_string('collapseall', 'moodle'));

                    $('.sectiontoggle').each(function(index) {
                        var section = '#collapse-' + (index + 1);
                        $(section).collapse('show');
                        if (!sectiontoggles.hasOwnProperty(index + 1)) {
                            sectiontoggles[index + 1] = "true";
                            setState(args.course, sectiontoggles, storage);
                        }
                    });
                });

                $('body').on('click', '.collapseall', function(event) {
                    event.preventDefault();
                    var target = event.target;
                    $(target).removeClass('collapseall').addClass('expandall').html(M.util.get_string('expandall', 'moodle'));
                    $('.sectiontoggle').each(function(index) {
                        var section = '#collapse-' + (index + 1);
                        $(section).collapse('hide');
                        if (sectiontoggles.hasOwnProperty(index + 1)) {
                            delete sectiontoggles[index + 1];
                            setState(args.course, sectiontoggles, storage);
                        }
                    });
                });
                $('#nav-drawer div.media').on('click', function(event) {

                    var href = $(event.target).parent().parent().parent().attr('href');

                    if (href.lastIndexOf('#section-') != -1) {
                        var index = href.substring(href.lastIndexOf('-') + 1);
                        var attr = '#collapse-' + index;
                        $(attr).collapse('show');
                    }
                });

                $('.collapse').on('show.bs.collapse', function(event) {
                    var sectionstringid = $(event.target).attr('id');
                    var sectionid = sectionstringid.substring(sectionstringid.lastIndexOf('-') + 1);

                    if (!sectiontoggles.hasOwnProperty(sectionid)) {
                        sectiontoggles[sectionid] = "true";
                        setState(args.course, sectiontoggles, storage);
                    }
                });

                $('.collapse').on('hide.bs.collapse', function(event) {
                    var sectionstringid = $(event.target).attr('id');
                    var sectionid = sectionstringid.substring(sectionstringid.lastIndexOf('-') + 1);

                    if (sectiontoggles.hasOwnProperty(sectionid)) {
                        delete sectiontoggles[sectionid];
                        setState(args.course, sectiontoggles, storage);
                    }
                });
                $('body').on('click', '.togglecompletion button', function(event) {
                    var target = event.target;
                    var state = $(target).parent().parent().children('input[name="completionstate"]').val();
                    var section = ($(target).closest('li.section'));
                    var progressbar = $(section).find('.progress-bar');
                    var oldvalue = parseInt($(progressbar).attr('aria-valuenow'));
                    var newvalue = state == 1 ? oldvalue + 1 : oldvalue - 1;
                    var total = parseInt($(progressbar).attr('aria-valuemax'));
                    var percent = Math.round((newvalue / total * 100));
                    $(progressbar).attr('aria-valuenow', newvalue);
                    $(progressbar).attr('style', 'width: ' + percent + '%');
                    var strings = [
                        {
                            key: 'progresstotal',
                            component: 'completion',
                            param: {
                                complete: newvalue,
                                total: total
                            }
                        }
                    ];
                    str.get_strings(strings).then(function(progress) {
                        $(progressbar).attr('data-original-title', progress);
                        return true;
                    });

                });
            });
        }
    };
});

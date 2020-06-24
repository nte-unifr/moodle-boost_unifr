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
 * Theme Boost Unifr Login - Layout file.
 *
 * @package   theme_boost_unifr
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @copyright based on code from theme_boost by Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$bodyattributes = $OUTPUT->body_attributes();

$wayf = '';
include_once($CFG->dirroot . '/theme/boost_unifr/wayf.php');

$templatecontext = [
    'rememberusername' => $CFG->rememberusername,
    'ext' => optional_param('ext', 0, PARAM_INT),
    'wayf' => $wayf,
    'logintoken' => \core\session\manager::get_login_token(),
    'error' => (isset($SESSION->loginerrormsg)) ? ($SESSION->loginerrormsg) : (''),
    'sitename' => $CFG->unifr_sitename,
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'forgotpasswordurl' => new moodle_url('/login/forgot_password.php')
];

// MODIFICATION START: Handle additional layout elements.
// The output buffer is needed to render the additional layout elements now without outputting them to the page directly.
ob_start();


echo $OUTPUT->render_from_template('theme_boost_unifr/login', $templatecontext);

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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package   theme_boost_unifr
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *            copyright based on code from theme_boost by Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_unifr\output;

use coding_exception;
use core\plugininfo\enrol;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use single_select;
use paging_bar;
use url_select;
use context_course;
use pix_icon;

defined('MOODLE_INTERNAL') || die;


/**
 * Extending the core_renderer interface.
 *
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package theme_boost_unifr
 * @category output
 */
class core_renderer extends \core_renderer {

    /**
     * Override to add additional class for the random login image to the body.
     *
     * Returns HTML attributes to use within the body tag. This includes an ID and classes.
     *
     * KIZ MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @since Moodle 2.5.1 2.6
     * @param string|array $additionalclasses Any additional classes to give the body tag,
     * @return string
     */
    public function body_attributes($additionalclasses = array()) {
        global $CFG;
        require_once($CFG->dirroot . '/theme/boost_unifr/locallib.php');

        if (!is_array($additionalclasses)) {
            $additionalclasses = explode(' ', $additionalclasses);
        }

        // MODIFICATION START.
        // Only add classes for the login page.
        if ($this->page->bodyid == 'page-login-index') {
            $additionalclasses[] = 'loginbackgroundimage';
            // Generating a random class for displaying a random image for the login page.
            $additionalclasses[] = theme_boost_unifr_get_random_loginbackgroundimage_class();
        }
        // MODIFICATION END.

        return ' id="'. $this->body_id().'" class="'.$this->body_css_classes($additionalclasses).'"';
    }

    /**
     * Override to be able to use uploaded images from admin_setting as well.
     *
     * Returns the moodle_url for the favicon.
     *
     * KIZ MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @since Moodle 2.5.1 2.6
     * @return moodle_url The moodle_url for the favicon
     */
    public function favicon() {
        // MODIFICATION START.
        if (!empty($this->page->theme->settings->favicon)) {
            return $this->page->theme->setting_file_url('favicon', 'favicon');
        } else {
            return $this->image_url('favicon', 'theme');
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        return $this->image_url('favicon', 'theme');
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
    }


    /**
     * Override to display switched role information beneath the course header instead of the user menu.
     * We change this because the switch role function is course related and therefore it should be placed in the course context.
     *
     * MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        // MODIFICATION START.
        global $USER, $COURSE, $CFG;
        // MODIFICATION END.

        if ($this->page->include_region_main_settings_in_header_actions() && !$this->page->blocks->is_block_present('settings')) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
            ));
        }

        $header = new stdClass();
        // MODIFICATION START.
        // Show the context header settings menu on all pages except for the profile page as we replace
        // it with an edit button there and if we are not on the content bank view page (contentbank/view.php)
        // as this page only adds header actions.
        if ($this->page->pagelayout != 'mypublic' && $this->page->bodyid != 'page-contentbank') {
            $header->settingsmenu = $this->context_header_settings_menu();
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START
        $header->settingsmenu = $this->context_header_settings_menu();
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        // MODIFICATION START.
        // Show the page heading button on all pages except for the profile page.
        // There we replace it with an edit profile button.
        if ($this->page->pagelayout != 'mypublic') {
            $header->pageheadingbutton = $this->page_heading_button();
        } else {
            // Get the id of the user for whom the profile page is shown.
            $userid = optional_param('id', $USER->id, PARAM_INT);
            // Check if the shown and the operating user are identical.
            $currentuser = $USER->id == $userid;
            if (($currentuser || is_siteadmin($USER)) &&
                has_capability('moodle/user:update', \context_system::instance())) {
                $url = new moodle_url('/user/editadvanced.php', array('id'       => $userid, 'course' => $COURSE->id,
                                                                      'returnto' => 'profile'));
                $header->pageheadingbutton = $this->single_button($url, get_string('editmyprofile', 'core'));
            } else if ((has_capability('moodle/user:editprofile', \context_user::instance($userid)) &&
                    !is_siteadmin($USER)) || ($currentuser &&
                    has_capability('moodle/user:editownprofile', \context_system::instance()))) {
                $url = new moodle_url('/user/edit.php', array('id'       => $userid, 'course' => $COURSE->id,
                                                              'returnto' => 'profile'));
                $header->pageheadingbutton = $this->single_button($url, get_string('editmyprofile', 'core'));
            }
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START
        $header->pageheadingbutton = $this->page_heading_button();
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();
        // MODIFICATION START:
        // Change this to add the result in the html variable to be able to add further features below the header.
        // Render from the own header template if we are not on the content bank view page (contentbank/view.php).
        if ($this->page->bodyid == 'page-contentbank') {
            $html = $this->render_from_template('core/full_header', $header);
        } else {
            $html = $this->render_from_template('theme_boost_unifr/full_header', $header);
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START
        return $this->render_from_template('core/full_header', $header);
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd

        // MODIFICATION START:
        // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
        // a hint for the visibility will be shown.
        if (get_config('theme_boost_unifr', 'showhintcoursehidden') == 'yes'
                && has_capability('theme/boost_unifr:viewhintinhiddencourse', \context_course::instance($COURSE->id))
                && $this->page->has_set_url()
                && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
                && $COURSE->visible == false) {
            $html .= html_writer::start_tag('div', array('class' => 'course-hidden-infobox alert alert-warning'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3 icon-size-5'));
            $html .= html_writer::tag('i', null, array('class' => 'fa fa-exclamation-circle fa-3x'));
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string('showhintcoursehiddengeneral', 'theme_boost_unifr', $COURSE->id);
            // If the user has the capability to change the course settings, an additional link to the course settings is shown.
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $html .= html_writer::tag('div', get_string('showhintcoursehiddensettingslink',
                    'theme_boost_unifr', array('url' => $CFG->wwwroot.'/course/edit.php?id='. $COURSE->id)));
            }
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }
        // MODIFICATION END.

        // MODIFICATION START:
        // If the setting showhintcourseguestaccess is set, a hint for users that view the course with guest access is shown.
        // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
        // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
        // intended.
        if (get_config('theme_boost_unifr', 'showhintcourseguestaccess') == 'yes'
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $this->page->has_set_url()
            && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)) {
            $html .= html_writer::start_tag('div', array('class' => 'course-guestaccess-infobox alert alert-warning'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3 icon-size-5'));
            $html .= html_writer::tag('i', null, array('class' => 'fa fa-exclamation-circle fa-3x'));
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string('showhintcourseguestaccessgeneral', 'theme_boost_unifr',
                array('role' => role_get_name(get_guest_role())));
            $html .= theme_boost_unifr_get_course_guest_access_hint($COURSE->id);
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }
        // MODIFICATION END.

        // MODIFICATION START:
        // If the setting showhintcourseselfenrol is set, a hint for users is shown that the course allows unrestricted self
        // enrolment. This hint is only shown if the course is visible, the self enrolment is visible and if the user has the
        // capability "theme/boost_unifr:viewhintcourseselfenrol".
        if (get_config('theme_boost_unifr', 'showhintcourseselfenrol') == 'yes'
                && has_capability('theme/boost_unifr:viewhintcourseselfenrol', \context_course::instance($COURSE->id))
                && $this->page->has_set_url()
                && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
                && $COURSE->visible == true) {
            // Get the active enrol instances for this course.
            $enrolinstances = enrol_get_instances($COURSE->id, true);
            // Prepare to remember when self enrolment is / will be possible.
            $selfenrolmentpossiblecurrently = false;
            $selfenrolmentpossiblefuture = false;
            foreach ($enrolinstances as $instance) {
                // Check if unrestricted self enrolment is possible currently or in the future.
                $now = (new \DateTime("now", \core_date::get_server_timezone_object()))->getTimestamp();
                if ($instance->enrol == 'self' && empty($instance->password) && $instance->customint6 == 1 &&
                        (empty($instance->enrolenddate) || $instance->enrolenddate > $now)) {

                    // Build enrol instance object with all necessary information for rendering the note later.
                    $instanceobject = new stdClass();

                    // Remember instance name.
                    if (empty($instance->name)) {
                        $instanceobject->name = get_string('pluginname', 'enrol_self') .
                                " (" . get_string('defaultcoursestudent', 'core') . ")";
                    } else {
                        $instanceobject->name = $instance->name;
                    }

                    // Remember type of unrestrictedness.
                    if (empty($instance->enrolenddate) && empty($instance->enrolstartdate)) {
                        $instanceobject->unrestrictedness = 'unlimited';
                        $selfenrolmentpossiblecurrently = true;
                    } else if (empty($instance->enrolstartdate) &&
                            !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                        $instanceobject->unrestrictedness = 'until';
                        $selfenrolmentpossiblecurrently = true;
                    } else if (empty($instance->enrolenddate) &&
                            !empty($instance->enrolstartdate) && $instance->enrolstartdate > $now) {
                        $instanceobject->unrestrictedness = 'from';
                        $selfenrolmentpossiblefuture = true;
                    } else if (empty($instance->enrolenddate) &&
                            !empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now) {
                        $instanceobject->unrestrictedness = 'since';
                        $selfenrolmentpossiblecurrently = true;
                    } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate > $now &&
                            !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                        $instanceobject->unrestrictedness = 'fromuntil';
                        $selfenrolmentpossiblefuture = true;
                    } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now &&
                            !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                        $instanceobject->unrestrictedness = 'sinceuntil';
                        $selfenrolmentpossiblecurrently = true;
                    } else {
                        // This should not happen, thus continue to next instance.
                        continue;
                    }

                    // Remember enrol start date.
                    if (!empty($instance->enrolstartdate)) {
                        $instanceobject->startdate = $instance->enrolstartdate;
                    } else {
                        $instanceobject->startdate = null;
                    }

                    // Remember enrol end date.
                    if (!empty($instance->enrolenddate)) {
                        $instanceobject->enddate = $instance->enrolenddate;
                    } else {
                        $instanceobject->enddate = null;
                    }

                    // Remember this instance.
                    $selfenrolinstances[$instance->id] = $instanceobject;
                }
            }

            // If there is at least one unrestricted enrolment instance,
            // show the hint with information about each unrestricted active self enrolment in the course.
            if (!empty($selfenrolinstances) &&
                    ($selfenrolmentpossiblecurrently == true || $selfenrolmentpossiblefuture == true)) {
                // Start hint box.
                $html .= html_writer::start_tag('div', array('class' => 'course-selfenrol-infobox alert alert-info'));
                $html .= html_writer::start_tag('div', array('class' => 'media'));
                $html .= html_writer::start_tag('div', array('class' => 'mr-3 icon-size-5'));
                $html .= html_writer::tag('i', null, array('class' => 'fa fa-sign-in fa-3x'));
                $html .= html_writer::end_tag('div');
                $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));

                // Show the start of the hint depending on the fact if enrolment is already possible currently or
                // will be in the future.
                if ($selfenrolmentpossiblecurrently == true) {
                    $html .= get_string('showhintcourseselfenrolstartcurrently', 'theme_boost_unifr');
                } else if ($selfenrolmentpossiblefuture == true) {
                    $html .= get_string('showhintcourseselfenrolstartfuture', 'theme_boost_unifr');
                }
                $html .= html_writer::empty_tag('br');

                // Iterate over all enrolment instances to output the details.
                foreach ($selfenrolinstances as $selfenrolinstanceid => $selfenrolinstanceobject) {
                    // If the user has the capability to config self enrolments, enrich the instance name with the settings link.
                    if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
                        $url = new moodle_url('/enrol/editinstance.php', array('courseid' => $COURSE->id,
                                'id' => $selfenrolinstanceid, 'type' => 'self'));
                        $selfenrolinstanceobject->name = html_writer::link($url, $selfenrolinstanceobject->name);
                    }

                    // Show the enrolment instance information depending on the instance configuration.
                    if ($selfenrolinstanceobject->unrestrictedness == 'unlimited') {
                        $html .= get_string('showhintcourseselfenrolunlimited', 'theme_boost_unifr',
                                array('name' => $selfenrolinstanceobject->name));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'until') {
                        $html .= get_string('showhintcourseselfenroluntil', 'theme_boost_unifr',
                                array('name' => $selfenrolinstanceobject->name,
                                        'until' => userdate($selfenrolinstanceobject->enddate)));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'from') {
                        $html .= get_string('showhintcourseselfenrolfrom', 'theme_boost_unifr',
                                array('name' => $selfenrolinstanceobject->name,
                                        'from' => userdate($selfenrolinstanceobject->startdate)));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'since') {
                        $html .= get_string('showhintcourseselfenrolsince', 'theme_boost_unifr',
                                array('name' => $selfenrolinstanceobject->name,
                                        'since' => userdate($selfenrolinstanceobject->startdate)));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'fromuntil') {
                        $html .= get_string('showhintcourseselfenrolfromuntil', 'theme_boost_unifr',
                                array('name' => $selfenrolinstanceobject->name,
                                        'until' => userdate($selfenrolinstanceobject->enddate),
                                        'from' => userdate($selfenrolinstanceobject->startdate)));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'sinceuntil') {
                        $html .= get_string('showhintcourseselfenrolsinceuntil', 'theme_boost_unifr',
                                array('name' => $selfenrolinstanceobject->name,
                                        'until' => userdate($selfenrolinstanceobject->enddate),
                                        'since' => userdate($selfenrolinstanceobject->startdate)));
                    }

                    // Add a trailing space to separate this instance from the next one.
                    $html .= ' ';
                }

                // If the user has the capability to config self enrolments, add the call for action.
                if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
                    $html .= html_writer::empty_tag('br');
                    $html .= get_string('showhintcourseselfenrolinstancecallforaction', 'theme_boost_unifr');
                }

                // End hint box.
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
        }
        // MODIFICATION END.

        // MODIFICATION START.
        // Only use this if setting 'showswitchedroleincourse' is active.
        if (get_config('theme_boost_unifr', 'showswitchedroleincourse') === 'yes') {
            // Check if the user did a role switch.
            // If not, adding this section would make no sense and, even worse,
            // user_get_user_navigation_info() will throw an exception due to the missing user object.
            if (is_role_switched($COURSE->id)) {
                // Get the role name switched to.
                $opts = \user_get_user_navigation_info($USER, $this->page);
                $role = $opts->metadata['rolename'];
                // Get the URL to switch back (normal role).
                $url = new moodle_url('/course/switchrole.php',
                    array('id'        => $COURSE->id, 'sesskey' => sesskey(), 'switchrole' => 0,
                          'returnurl' => $this->page->url->out_as_local_url(false)));
                $html .= html_writer::start_tag('div', array('class' => 'switched-role-infobox alert alert-info'));
                $html .= html_writer::start_tag('div', array('class' => 'media'));
                $html .= html_writer::start_tag('div', array('class' => 'mr-3 icon-size-5'));
                $html .= html_writer::tag('i', null, array('class' => 'fa fa-user-circle fa-3x'));
                $html .= html_writer::end_tag('div');
                $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
                $html .= html_writer::start_tag('div');
                $html .= get_string('switchedroleto', 'theme_boost_unifr');
                // Give this a span to be able to address via CSS.
                $html .= html_writer::tag('span', $role, array('class' => 'switched-role'));
                $html .= html_writer::end_tag('div');
                // Return to normal role link.
                $html .= html_writer::start_tag('div');
                $html .= html_writer::tag('a', get_string('switchrolereturn', 'core'),
                    array('class' => 'switched-role-backlink', 'href' => $url));
                $html .= html_writer::end_tag('div'); // Return to normal role link: end div.
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
        }
        // MODIFICATION END.
        return $html;
    }


    /**
     * Override to display course settings on every course site for permanent access
     *
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page.
     *
     * MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php.
     *
     * @return string
     */
    public function context_header_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        // MODIFICATION START.
        // REASON: With the original code, the course settings icon will only appear on the course main page.
        // Therefore the access to the course settings and related functions is not possible on other
        // course pages as there is no omnipresent block anymore. We want these to be accessible
        // on each course page.
        if (($context->contextlevel == CONTEXT_COURSE || $context->contextlevel == CONTEXT_MODULE) && !empty($currentnode)) {
            $showcoursemenu = true;
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        if (($context->contextlevel == CONTEXT_COURSE) &&
                !empty($currentnode) &&
                ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcoursemenu = true;
        }
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
                !$courseformat->has_view_page()) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                        $activenode->type == navigation_node::TYPE_RESOURCE)) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
                !empty($currentnode) &&
                $currentnode->key === 'home') {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
                !empty($currentnode) &&
                ($currentnode->key === 'myprofile')) {
            $showusermenu = true;
        }

        if ($showfrontpagemenu) {
            $settingsnode = $this->page->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $this->page->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $this->render($menu);
    }

    /**
     * Override to use theme_boost_unifr login template
     * Renders the login form.
     *
     * MODIFICATION: This renderer function is copied and modified from lib/outputrenderers.php
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE;

        /*
        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string($SITE->fullname, true,
                ['context' => context_course::instance(SITEID), "escape" => false]);
        */
        // MODIFICATION START.
        // Only if setting "loginform" is checked, then call own login.mustache.
        /*
        if (get_config('theme_boost_unifr', 'loginform') == 'yes') {
            return $this->render_from_template('theme_boost_unifr/loginform', $context);
        } else {
            return $this->render_from_template('core/loginform', $context);
        }
        */
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        return $this->render_from_template('core/loginform', $context);
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
        $error = $form->error;

        if ($error) {
            return \html_writer::div($error, 'alert alert-danger');
        }

        return '';
    }

    /**
     * Implementation of user image rendering.
     *
     * MODIFICATION: This renderer function is copied and modified from lib/outputrenderers.php
     *
     * @param help_icon $helpicon A help icon instance
     * @return string HTML fragment
     */
    protected function render_help_icon(help_icon $helpicon) {
        $context = $helpicon->export_for_template($this);
        // MODIFICATION START.
        // ID needed for modal dialog.
        $context->linkid = $helpicon->component.'-'.$helpicon->identifier;
        // Fill body variable needed for modal mustache with text value.
        $context->body = $context->text;
        if (get_config('theme_boost_unifr', 'helptextmodal') == 'yes') {
            return $this->render_from_template('theme_boost_unifr/help_icon', $context);
        } else {
            return $this->render_from_template('core/help_icon', $context);
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        $context = $helpicon->export_for_template($this);
        return $this->render_from_template('core/help_icon', $context);
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
    }
}

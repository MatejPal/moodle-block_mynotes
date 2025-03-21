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
 * Class note_dynamic_form
 *
 * @package    block_mynotes
 * @copyright  2025 Matej Pal <matej.pal@agiledrop.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mynotes\form;

use context;
use moodle_url;

/**
 * Class note_dynamic_form
 */
class note_dynamic_form extends \core_form\dynamic_form {

    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('block/mynotes:postnotes', \context_system::instance());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return mixed
     */
    public function process_dynamic_submission() {
        return $this->get_data();
    }

    /**
     * Load in existing data as form defaults
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        $this->get_data();
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new \moodle_url('/block/mynotes/block_mynotes.php');
    }

    /**
     * Definition function for the dynamic form
     *
     * @return void
     */
    protected function definition() {
        global $USER;

        $noteform = $this->_form;

        $noteform->addElement('textarea', 'note', get_string('yournote', 'block_mynotes'));
        $noteform->setType('note', PARAM_TEXT);

        $noteform->addElement('hidden', 'userid', $USER->id);
        $noteform->setType('userid', PARAM_INT);

        $submitlabel = get_string('submit');

        $noteform->addElement('submit', 'submit', $submitlabel);
    }
}

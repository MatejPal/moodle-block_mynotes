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
 * @package    block_mynotes
 * @copyright  2025 Matej Pal <matej.pal@agiledrop.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mynotes\form;

use context;
use moodle_url;

class note_dynamic_form extends \core_form\dynamic_form {

    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    protected function check_access_for_dynamic_submission(): void {
        require_capability('block/mynotes:postnotes', \context_system::instance());
    }

    public function process_dynamic_submission() {
        return $this->get_data();
    }

    public function set_data_for_dynamic_submission(): void {
        $this->get_data([
        ]);
    }

    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new \moodle_url('/block/mynotes/note.php');
    }

    protected function definition() {
        global $USER;

        $noteform = $this->_form;

        $noteform->addElement('textarea', 'note', get_string('yournote', 'block_mynotes'));
        $noteform->setType('note', PARAM_TEXT);

        $noteform->addElement('hidden', 'userid', $USER->id);
        $noteform->setType('userid', PARAM_INT);

        // Add editing form functionality.

        $submitlabel = get_string('submit');

        $noteform->addElement('submit', 'submit', $submitlabel);
    }
}

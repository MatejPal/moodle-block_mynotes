<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

use block_mynotes\form\note_dynamic_form;

/**
 * Block mynotes is defined here.
 *
 * @package     block_mynotes
 * @copyright   2025 Matej Pal <matej.pal@agiledrop.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_mynotes extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_mynotes');
    }

    /**
     * Self test.
     *
     * @return true
     */
    public function _self_test(): bool {
        return true;
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();

        $noteform = new note_dynamic_form();
        $noteform->set_data_for_dynamic_submission();

        $instanceid = $this->instance->id;

        $this->content->text = html_writer::div(
            $noteform->render(),
            '',
            [
                'data-region' => 'form',
                'class' => '',
                'data-block-instance-id' => $instanceid,
            ]
        );

        $this->content->text .= html_writer::div(
            '',
            '',
            [
                'data-region' => 'notes-container',
                'class' => 'notes-list',
            ]
        );

        $this->page->requires->js_call_amd(
            'block_mynotes/mynotes',
            'addNote',
            [
                '[data-region=form]',
                note_dynamic_form::class,
                $instanceid,
            ],
        );

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_mynotes');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Allow multiple instances in a single course?
     *
     * @return bool True if multiple instances are allowed, false otherwise.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return [
            'my' => true,
        ];
    }
}

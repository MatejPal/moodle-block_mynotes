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
 * Plugin external functions and services are defined here.
 *
 * @package     block_mynotes
 * @category    external
 * @copyright   2025 Matej Pal <matej.pal@agiledrop.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_mynotes_add_note' => [
        'classname' => 'block_mynotes\external\add_note',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => "Add a new note.",
        'type' => 'write',
        'ajax' => true,
        'capabilities'  => 'block/mynotes:postnotes',
    ],
    'block_mynotes_get_notes' => [
        'classname' => 'block_mynotes\external\get_notes',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Retrieve notes for the logged-in user.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'block/mynotes:viewnotes',
    ],
    'block_mynotes_delete_note' => [
        'classname'   => 'block_mynotes\external\delete_note',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Delete a note',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'block/mynotes:postnotes',
    ],
    'block_mynotes_edit_note' => [
        'classname'   => 'block_mynotes\external\edit_note',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Edit a note.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'block/mynotes:postnotes',
    ],
];

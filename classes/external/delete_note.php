<?php
// This file is part of the Allocation form plugin
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
//

/**
 * Webservices for the plugin.
 *
 * @package     block_mynotes
 * @copyright   2025 Matej Pal <matej.pal@agiledrop.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mynotes\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use context_system;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

/**
 * External API class for deleting a note in the My Notes plugin.
 */
class delete_note extends external_api {

    /**
     * Define input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
                'noteid' => new external_value(PARAM_INT, 'ID of the note to delete'),
        ]);
    }

    /**
     * Delete the note from the database.
     *
     * @param int $noteid The note ID.
     * @return array Result status.
     */
    public static function execute($noteid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['noteid' => $noteid]);
        $context = context_system::instance();
        self::validate_context($context);

        require_capability('block/mynotes:postnotes', $context);

        $note = $DB->get_record('block_mynotes', ['id' => $params['noteid']]);
        if (!$note) {
            throw new \moodle_exception('notenotfound', 'block_mynotes');
        }

        if ($note->userid != $USER->id) {
            throw new \moodle_exception('cannotdelete', 'block_mynotes');
        }

        $DB->delete_records('block_mynotes', ['id' => $params['noteid']]);

        return ['status' => 'success'];
    }

    /**
     * Define return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
                'status' => new external_value(PARAM_TEXT, 'Result status')
        ]);
    }

}

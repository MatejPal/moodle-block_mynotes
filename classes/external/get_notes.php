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

use context_system;
use core_external\restricted_context_exception;
use dml_exception;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use required_capability_exception;

require_once($CFG->libdir . '/externallib.php');

/**
 * External API class for getting notes in the My Notes plugin.
 */
class get_notes extends external_api {

    /**
     * Define input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'limit'  => new external_value(PARAM_INT, 'Number of notes per page', VALUE_DEFAULT, 5),
            'offset' => new external_value(PARAM_INT, 'Starting offset for pagination', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Fetch user notes.
     *
     * @param int $limit
     * @param int $offset
     * @return array Array of notes.
     */
    public static function execute($limit = 5, $offset = 0): array {
        global $DB, $USER;
        $context = context_system::instance();
        self::validate_context($context);

        require_capability('block/mynotes:viewnotes', $context);

        // Validate and get parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
                'limit'  => $limit,
                'offset' => $offset,
        ]);
        $limit = (int)$params['limit'];
        $offset = (int)$params['offset'];

        // Use get_records_sql with offset and limit.
        $sql = 'SELECT * FROM {block_mynotes} WHERE userid = ? ORDER BY timecreated DESC';
        $notes = $DB->get_records_sql($sql, [$USER->id], $offset, $limit);

        $result = [];
        foreach ($notes as $note) {
            $result[] = [
                    'id'          => $note->id,
                    'note'        => $note->note,
                    'timecreated' => userdate($note->timecreated),
            ];
        }

        return ['notes' => $result];
    }

    /**
     * Define return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
                'notes' => new external_multiple_structure(
                        new external_single_structure([
                                'id'          => new external_value(PARAM_INT, 'Note ID'),
                                'note'        => new external_value(PARAM_TEXT, 'Note content'),
                                'timecreated' => new external_value(PARAM_TEXT, 'Creation time'),
                        ])
                ),
        ]);
    }
}

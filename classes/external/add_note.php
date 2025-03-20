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

use context_system;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class add_note extends external_api {

    /**
     * Define input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'ID of the user posting the note'),
            'note'   => new external_value(PARAM_TEXT, 'The content of the note'),
        ]);
    }

    /**
     * Process the request and add the note to the database.
     *
     * @param int $userid The user ID.
     * @param string $note The note content.
     * @return array Array containing warnings and record ID (if successful).
     */
    public static function execute ($userid, $note) {
        global $DB, $USER;

        $warnings = [];

        $params = self::validate_parameters(self::execute_parameters(), [
                'userid' => $userid,
                'note' => $note
        ]);

        $context = context_system::instance();
        self::validate_context($context);

        require_capability('block/mynotes:postnotes', $context);

        $notecontent = trim($params['note']);
        if (empty($notecontent)) {
            $warnings[] = [
                    'item' => 'note',
                    'itemid' => $userid,
                    'warningcode' => 'emptynote',
                    'message' => get_string('emptynote', 'block_mynotes')
            ];
            return [
                    'recordid' => 0,
                    'warnings' => $warnings
            ];
        }

        $record = new stdClass();
        $record->userid = $userid;
        $record->note = $notecontent;
        $record->status = 0;
        $record->timecreated = time();
        $record->timeupdated = time();

        $recordid = $DB->insert_record('block_mynotes', $record);

        return [
                'recordid' => $recordid,
                'warnings' => [],
        ];
    }

    /**
     * Define return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
                'recordid' => new external_value(PARAM_INT, 'The ID of the inserted note (0 if not inserted)'),
                'warnings' => new external_warnings(),
        ]);
    }
}

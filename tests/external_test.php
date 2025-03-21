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

namespace block_mynotes;

use block_mynotes;
use external_api;
use invalid_response_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the block_mynotes plugin
 *
 * @runTestsInSeparateProcesses
 * @package     block_mynotes
 * @category    test
 * @copyright   2025 Matej Pal <matej.pal@agiledrop.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class external_test extends \advanced_testcase {

    /** @var stdClass The primary test user. */
    protected $user;

    /** @var stdClass Another test user. */
    protected $anotheruser;

    /**
     * Setup before each test.
     *
     * This method resets the test environment, creates two users, and sets the primary user as current.
     */
    public function setUp(): void {
        $this->resetAfterTest();

        $this->user = self::getDataGenerator()->create_user();
        self::setUser($this->user);

        $this->anotheruser = self::getDataGenerator()->create_user();
    }

    /**
     * Helper function to fetch the current user's notes via the external API.
     *
     * @return array The array of notes.
     * @throws invalid_response_exception
     */
    protected function getUserNotes($limit = 5, $offset = 0): array {
        $raw = block_mynotes\external\get_notes::execute($limit, $offset);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\get_notes::execute_returns(),
                $raw
        );
        return $result['notes'];
    }

    /**
     * Test adding a new note.
     *
     * Verifies that when a note is added:
     * - Initially no notes exist.
     * - The add_note API returns a valid record ID.
     * - The warnings array is empty.
     * - A subsequent call to get_notes returns one note.
     * - The note record in the database contains the expected user id and content.
     */
    public function test_add_note() {
        global $DB;

        $notes = $this->getUserNotes();
        $this->assertEmpty($notes);

        $notecontent = 'This is a test note';
        $raw = block_mynotes\external\add_note::execute($this->user->id, $notecontent);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\add_note::execute_returns(),
                $raw
        );
        $this->assertGreaterThan(0, $result['recordid']);
        $this->assertEmpty($result['warnings'], 'Warnings array should be empty on successful add.');

        $notes = $this->getUserNotes();

        $this->assertCount(1, $notes, 'There should be one note after adding.');

        $note = $DB->get_record('block_mynotes', ['id' => $result['recordid']]);
        $this->assertEquals($this->user->id, $note->userid, 'The user ids should be the same.');
        $this->assertEquals($notecontent, $note->note, 'The notes should be the same.');
    }

    /**
     * Test editing an existing note.
     */
    public function test_edit_note() {
        global $DB;

        $notecontent = 'Original note';
        $raw = block_mynotes\external\add_note::execute($this->user->id, $notecontent);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\add_note::execute_returns(),
                $raw
        );

        $recordid = $result['recordid'];

        $newcontent = 'Updated note';
        $raw = block_mynotes\external\edit_note::execute($recordid, $newcontent);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\edit_note::execute_returns(),
                $raw
        );
        $this->assertEquals('success', $result['status'], 'The edit should return a success status.');

        $notes = $this->getUserNotes();
        $this->assertCount(1, $notes, 'There should be one note after editing.');

        $note = $DB->get_record('block_mynotes', ['id' => $recordid]);
        $this->assertEquals($this->user->id, $note->userid, 'The user ids should be the same.');
        $this->assertEquals($newcontent, $note->note, 'The notes should be the same.');
    }

    /**
     * Test deleting a note.
     */
    public function test_delete_note() {

        $notecontent = 'Note to delete';
        $raw = block_mynotes\external\add_note::execute($this->user->id, $notecontent);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\add_note::execute_returns(),
                $raw
        );
        $recordid = $result['recordid'];

        $notes = $this->getUserNotes();
        $this->assertCount(1, $notes, 'There should be one note before deletion.');

        $raw = block_mynotes\external\delete_note::execute($recordid);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\delete_note::execute_returns(),
                $raw
        );

        $this->assertEquals('success', $result['status'], 'The note should be deleted and status success returned.');

        $notes = $this->getUserNotes();
        $this->assertCount(0, $notes, 'There should be no notes after deleting.');
    }

    /**
     * Test that editing a note by another user is not allowed.
     */
    public function test_edit_note_by_another_user() {
        $notecontent = 'User note';
        $raw = block_mynotes\external\add_note::execute($this->user->id, $notecontent);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\add_note::execute_returns(),
                $raw
        );
        $recordid = $result['recordid'];

        self::setUser($this->anotheruser);
        $this->expectException(\moodle_exception::class);
        block_mynotes\external\edit_note::execute($recordid, 'Attempted edit by another user');
    }

    /**
     * Test that deleting a note by another user is not allowed.
     */
    public function test_delete_note_by_another_user() {
        $notecontent = 'This is not yours!';
        $raw = block_mynotes\external\add_note::execute($this->user->id, $notecontent);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\add_note::execute_returns(),
                $raw
        );
        $recordid = $result['recordid'];

        self::setUser($this->anotheruser);
        $this->expectException(\moodle_exception::class);
        block_mynotes\external\delete_note::execute($recordid);
    }

    /**
     * Test that adding an empty note is not allowed.
     */
    public function test_add_empty_note() {
        $notecontent = ' ';
        $raw = block_mynotes\external\add_note::execute($this->user->id, $notecontent);
        $result = external_api::clean_returnvalue(
                block_mynotes\external\add_note::execute_returns(),
                $raw
        );
        $this->assertEquals(0, $result['recordid'], 'Recordid should be 0 for empty note.');
        $this->assertNotEmpty($result['warnings'], 'Warnings array should not be empty for empty note.');
    }

    /**
     * Test pagination.
     */
    public function test_get_notes_pagination() {
        // Create 7 notes.
        for ($i = 1; $i <= 7; $i++) {
            block_mynotes\external\add_note::execute($this->user->id, 'Note ' . $i);
        }
        // Get first page (limit 5, offset 0).
        $notes = $this->getUserNotes();
        $this->assertCount(5, $notes, 'There should be 5 notes on the first page.');

        // Get second page (limit 5, offset 5).
        $notes = $this->getUserNotes(5, 5);
        $this->assertCount(2, $notes, 'There should be 2 notes on the second page.');
    }
}

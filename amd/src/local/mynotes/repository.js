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
 * Repository module for block_mynotes.
 *
 * This module encapsulates all AJAX calls related to the My Notes plugin using Moodle's core/ajax.
 * It provides functions to:
 * - Retrieve user data,
 * - Add new notes,
 * - Fetch paginated notes,
 * - Delete a note, and
 * - Edit an existing note.
 *
 * Each function returns a Promise that resolves to the data returned by the corresponding external web service.
 *
 * @module     block_mynotes/local/mynotes/repository
 * @copyright  2025 Matej Pal <matej.pal@agiledrop.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';

/**
 *
 * @param {Number} userid
 * @returns
 */
export const getUser = (userid = 0) => {
    return fetchMany([{
        methodname: 'core_user_get_users_by_field',
        args: {field: 'id', values: [userid]}
    }])[0];
};

/**
 *
 * @param {number} userid
 * @param {string} note
 * @returns
 */
export const addNote = (userid, note) => {
    return fetchMany([{
        methodname: 'block_mynotes_add_note',
        args: {userid, note}
    }])[0];
};

/**
 * Fetch user notes from the server with pagination.
 * @param {number} limit - Number of notes per page.
 * @param {number} offset - Starting offset.
 * @returns {Promise}
 */
export const getNotes = (limit = 5, offset = 0) => {
    return fetchMany([{
        methodname: 'block_mynotes_get_notes',
        args: { limit, offset }
    }])[0];
};

/**
 * Delete a note by note ID.
 * @param {number} noteId - The ID of the note to delete.
 * @returns {Promise}
 */
export const deleteNote = (noteId) => {
    return fetchMany([{
        methodname: 'block_mynotes_delete_note',
        args: { noteid: noteId }
    }])[0];
};

/**
 * Edit a note by note ID.
 * @param {number} noteId - The ID of the note to delete.
 * @param {string} newNote
 * @returns {Promise}
 */
export const editNote = (noteId, newNote) => {
    return fetchMany([{
        methodname: 'block_mynotes_edit_note',
        args: { noteid: noteId, note: newNote }
    }])[0];
};

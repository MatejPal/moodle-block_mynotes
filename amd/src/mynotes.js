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
 * My Notes Main Module.
 *
 * This module initializes the dynamic form for adding notes, handles AJAX-based
 * submission of new notes, and fetches & displays existing notes with pagination.
 * It also manages note editing and deletion by integrating callbacks for these actions.
 *
 * The module leverages Moodle's core dynamic form API, Repository functions for
 * AJAX calls to external web services, and helper functions from the noteRenderer
 * submodule to render note items and pagination controls.
 *
 * @module     block_mynotes/mynotes
 * @copyright  2025 Matej Pal <matej.pal@agiledrop.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import DynamicForm from 'core_form/dynamicform';
import * as Repository from 'block_mynotes/local/mynotes/repository';
import { renderNote, renderPagination } from 'block_mynotes/local/mynotes/noteRenderer';

let currentOffset = 0;
const limit = 5; // You want to show 5 notes per page.

/**
 * Initialize the dynamic form for adding a note and fetch existing notes.
 *
 * @param {string} selector - The CSS selector for the form container.
 * @param {Function} formClass - The class used to instantiate the dynamic form.
 * @param {number} instanceId - The unique instance ID of this block.
 */
export const addNote = (selector, formClass, instanceId) => {
    const instanceSelector = `${selector}[data-block-instance-id="${instanceId}"]`;
    const formElement = document.querySelector(instanceSelector);
    if (!formElement) {
        window.console.warn('Form element not found for instance ' + instanceId);
        return;
    }
    if (formElement.dataset.initialized === "1") {
        return;
    }
    formElement.dataset.initialized = "1";

    const form = new DynamicForm(formElement, formClass);
    form.addEventListener(form.events.FORM_SUBMITTED, (e) => {
        e.preventDefault();
        const response = e.detail;
        const submitButton = formElement.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }
        window.console.log('Form submitted: ' + JSON.stringify(response));
        Repository.addNote(response.userid, response.note)
            .then(function(res) {
                window.console.log(JSON.stringify(res));
                // After a successful submission, reset pagination to page 1.
                currentOffset = 0;
                fetchAndDisplayNotes();
                if (submitButton) {
                    submitButton.disabled = false;
                }
                const inputField = formElement.querySelector('textarea[name="note"]');
                if (inputField) {
                    inputField.value = '';
                }
            })
            .catch(error => {
                window.console.error('Error saving note:', error);
                if (submitButton) {
                    submitButton.disabled = false;
                }
            });
    });

    fetchAndDisplayNotes();
};

/**
 * Fetches and displays notes in all elements with data-region="notes-container".
 * Applies pagination and renders each note using the renderNote function.
 */
const fetchAndDisplayNotes = () => {
    const notesContainers = document.querySelectorAll('[data-region="notes-container"]');
    if (!notesContainers.length) {
        return;
    }
    Repository.getNotes(limit + 1, currentOffset)
        .then(response => {
            // Unwrap the notes array.
            const notesAll = response.data ? response.data.notes : response.notes;
            // Use only the first "limit" items for display.
            const notes = notesAll.slice(0, limit);
            notesContainers.forEach(notesContainer => {
                notesContainer.innerHTML = '';
                if (!notes || notes.length === 0) {
                    notesContainer.innerHTML = '<p>No notes yet.</p>';
                } else {
                    notes.forEach(note => {
                        const noteElement = renderNote(note, onEditCallback, onDeleteCallback);
                        notesContainer.appendChild(noteElement);
                    });
                    // Render pagination using the total count (notesAll.length).
                    const paginationControls = renderPagination(currentOffset, limit, notesAll.length, (newOffset) => {
                        currentOffset = newOffset;
                        fetchAndDisplayNotes();
                    });
                    notesContainer.appendChild(paginationControls);
                }
            });
        })
        .catch(error => window.console.error('Error fetching notes:', error));
};

/**
 * Callback for editing a note.
 * Hides the current note content and displays an editing interface with a textarea, Save, and Cancel buttons.
 *
 * @param {Object} note - The note object.
 * @param {HTMLElement} noteElement - The container element for the note.
 * @param {HTMLElement} contentContainer - The element that displays the note text.
 * @param {HTMLElement} noteFooter - The footer element containing the note's date and action buttons.
 */
const onEditCallback = (note, noteElement, contentContainer, noteFooter) => {
    // Hide the note content to show the edit interface.
    contentContainer.style.display = 'none';
    // Check if an edit container already exists.
    let editContainer = noteElement.querySelector('.edit-container');
    if (!editContainer) {
        // Create a new edit container.
        editContainer = document.createElement('div');
        editContainer.classList.add('edit-container');

        // Create a textarea pre-filled with the current note text.
        const textarea = document.createElement('textarea');
        textarea.classList.add('edit-textarea');
        textarea.value = note.note;

        // Create the Save button.
        const saveButton = document.createElement('button');
        saveButton.textContent = 'Save';
        saveButton.addEventListener('click', () => {
            const newNote = textarea.value.trim();
            if (newNote === '') {
                alert('Note cannot be empty.');
                return;
            }
            Repository.editNote(note.id, newNote)
                .then((res) => {
                    window.console.log('Note updated:', res);
                    fetchAndDisplayNotes();
                })
                .catch((error) => {
                    window.console.error('Error updating note:', error);
                });
        });

        // Create the Cancel button.
        const cancelButton = document.createElement('button');
        cancelButton.textContent = 'Cancel';
        cancelButton.addEventListener('click', () => {
            editContainer.style.display = 'none';
            contentContainer.style.display = '';
        });

        // Assemble the edit container.
        editContainer.appendChild(textarea);
        editContainer.appendChild(saveButton);
        editContainer.appendChild(cancelButton);
        // Insert the edit container before the note footer.
        noteElement.insertBefore(editContainer, noteFooter);
    } else {
        // If an edit container already exists, show it and reset the textarea.
        editContainer.style.display = '';
        const textarea = editContainer.querySelector('textarea');
        textarea.value = note.note;
    }
};

/**
 * Callback for deleting a note.
 * Asks for confirmation and then calls the repository to delete the note.
 *
 * @param {Object} note - The note object.
 */
const onDeleteCallback = (note) => {
    if (confirm('Are you sure you want to delete this note?')) {
        Repository.deleteNote(note.id)
            .then((res) => {
                window.console.log('Note deleted:', res);
                fetchAndDisplayNotes();
            })
            .catch((error) => {
                window.console.error('Error deleting note:', error);
            });
    }
};

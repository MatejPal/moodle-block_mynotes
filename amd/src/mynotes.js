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
 * TODO describe module greetings
 *
 * @module     block_mynotes/mynotes
 * @copyright  2025 Matej Pal <matej.pal@agiledrop.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'block_mynotes/local/mynotes/selectors';
import DynamicForm from 'core_form/dynamicform';
import * as Repository from 'block_mynotes/local/mynotes/repository';

let currentOffset = 0;
const limit = 5; // You want to show 5 notes per page.

/**
 *
 * @param {Number} userid
 */
export const init = (userid) => {
    registerEventListeners(userid);
};

const registerEventListeners = (userid) => {
    document.addEventListener('click', e =>  {

        if (e.target.closest(Selectors.actions.showAlertButton)) {
            window.alert('Hello!');
        }

        window.console.log(e.target);
        window.console.log(userid);
    });
};

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
 * Fetch and display notes in all notes containers on the page.
 */
const fetchAndDisplayNotes = () => {
    const notesContainers = document.querySelectorAll('[data-region="notes-container"]');
    if (!notesContainers.length) {
        return;
    }
    Repository.getNotes(limit, currentOffset)
        .then(response => {
            // If the response is wrapped, use response.data.notes.
            const notes = response.data ? response.data.notes : response.notes;
            notesContainers.forEach(notesContainer => {
                notesContainer.innerHTML = '';
                if (!notes || notes.length === 0) {
                    notesContainer.innerHTML = '<p>No notes yet.</p>';
                } else {
                    notes.forEach(note => {
                        // Create the note element container.
                        const noteElement = document.createElement('div');
                        noteElement.classList.add('note-item');

                        // Create a container for note content.
                        const contentContainer = document.createElement('div');
                        contentContainer.classList.add('note-content');
                        contentContainer.textContent = note.note;

                        // Create a footer for date and action buttons.
                        const noteFooter = document.createElement('div');
                        noteFooter.classList.add('note-footer');

                        // Create element for the creation date.
                        const dateElem = document.createElement('small');
                        dateElem.textContent = note.timecreated;

                        // Create a container for action links.
                        const actionsContainer = document.createElement('div');
                        actionsContainer.classList.add('note-actions');

                        // Create the Edit link as an anchor.
                        const editLink = document.createElement('a');
                        editLink.setAttribute('role', 'button');
                        editLink.setAttribute('href', '#');
                        editLink.classList.add('edit-note');

                        const editIconUrl = M.util.image_url('t/edit', 'core').toString();
                        const editAltText = M.util.get_string('edit', 'core');

                        editLink.innerHTML = `<img src="${editIconUrl}" alt="${editAltText}" style="width:16px; height:16px;">`;

                        editLink.addEventListener('click', (e) => {
                            e.preventDefault();
                            // Hide the content container.
                            contentContainer.style.display = 'none';
                            // Create or show the edit container.
                            let editContainer = noteElement.querySelector('.edit-container');
                            if (!editContainer) {
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
                                        .then(function(res) {
                                            window.console.log('Note updated:', res);
                                            fetchAndDisplayNotes();
                                        })
                                        .catch(error => window.console.error('Error updating note:', error));
                                });

                                // Create the Cancel button.
                                const cancelButton = document.createElement('button');
                                cancelButton.textContent = 'Cancel';
                                cancelButton.addEventListener('click', () => {
                                    editContainer.style.display = 'none';
                                    contentContainer.style.display = '';
                                });

                                editContainer.appendChild(textarea);
                                editContainer.appendChild(saveButton);
                                editContainer.appendChild(cancelButton);
                                noteElement.insertBefore(editContainer, noteFooter);
                            } else {
                                editContainer.style.display = '';
                                // Reset textarea value.
                                const textarea = editContainer.querySelector('textarea');
                                textarea.value = note.note;
                            }
                        });

                        // Create the Delete link.
                        const deleteLink = document.createElement('a');
                        deleteLink.setAttribute('role', 'button');
                        deleteLink.setAttribute('href', '#');
                        deleteLink.dataset.noteId = note.id;
                        deleteLink.classList.add('delete-note');
                        const iconUrl = M.util.image_url('t/delete', 'core').toString();
                        const altText = M.util.get_string('delete', 'core');
                        deleteLink.innerHTML = `<img src="${iconUrl}" alt="${altText}" style="width:16px; height:16px;">`;
                        deleteLink.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (confirm('Are you sure you want to delete this note?')) {
                                Repository.deleteNote(note.id)
                                    .then(function(res) {
                                        window.console.log('Note deleted:', res);
                                        fetchAndDisplayNotes();
                                    })
                                    .catch(error => window.console.error('Error deleting note:', error));
                            }
                        });

                        // Append edit and delete links.
                        actionsContainer.appendChild(editLink);
                        actionsContainer.appendChild(deleteLink);

                        // Assemble the footer.
                        noteFooter.appendChild(dateElem);
                        noteFooter.appendChild(actionsContainer);

                        // Assemble the note element.
                        noteElement.appendChild(contentContainer);
                        noteElement.appendChild(noteFooter);
                        notesContainer.appendChild(noteElement);
                    });

                    // Add pagination controls (same as before).
                    const paginationContainer = document.createElement('div');
                    paginationContainer.classList.add('pagination-controls');

                    const prevButton = document.createElement('button');
                    prevButton.textContent = 'Previous';
                    prevButton.disabled = (currentOffset === 0);
                    prevButton.addEventListener('click', () => {
                        if (currentOffset >= limit) {
                            currentOffset -= limit;
                            fetchAndDisplayNotes();
                        }
                    });

                    const nextButton = document.createElement('button');
                    nextButton.textContent = 'Next';
                    nextButton.disabled = (notes.length < limit);
                    nextButton.addEventListener('click', () => {
                        currentOffset += limit;
                        fetchAndDisplayNotes();
                    });

                    paginationContainer.appendChild(prevButton);
                    paginationContainer.appendChild(nextButton);
                    notesContainer.appendChild(paginationContainer);
                }
            });
        })
        .catch(error => window.console.error('Error fetching notes:', error));
};

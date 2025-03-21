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
 * Renders a single note element with its content, footer, and action buttons.
 *
 * @param {Object} note - The note object (with id, note, and timecreated properties).
 * @param {Function} onEdit - Callback to call when the edit action is triggered.
 *                              It receives (note, noteElement, contentContainer, noteFooter).
 * @param {Function} onDelete - Callback to call when the delete action is triggered.
 *                              It receives (note).
 * @returns {HTMLElement} - The rendered note element.
 */
export const renderNote = (note, onEdit, onDelete) => {
    // Create the main container for the note.
    const noteElement = document.createElement('div');
    noteElement.classList.add('note-item');

    // Create a container to display the note content.
    const contentContainer = document.createElement('div');
    contentContainer.classList.add('note-content');
    contentContainer.textContent = note.note;

    // Create a footer container to hold the creation date and action buttons.
    const noteFooter = document.createElement('div');
    noteFooter.classList.add('note-footer');

    // Create an element to display the creation date.
    const dateElem = document.createElement('small');
    dateElem.textContent = note.timecreated;

    // Create a container for the action links (Edit and Delete).
    const actionsContainer = document.createElement('div');
    actionsContainer.classList.add('note-actions');

    // --- Create the Edit action ---
    const editLink = document.createElement('a');
    editLink.setAttribute('role', 'button');
    editLink.setAttribute('href', '#');
    editLink.classList.add('edit-note');

    // Use Moodle's utility to get the URL for the edit icon.
    const editIconUrl = M.util.image_url('t/edit', 'core').toString();
    // Get the translated string for the alt attribute.
    const editAltText = M.util.get_string('edit', 'core');
    // Set the inner HTML to display the icon.
    editLink.innerHTML = `<img src="${editIconUrl}" alt="${editAltText}" style="width:16px; height:16px;">`;

    // When the edit link is clicked, call the onEdit callback.
    editLink.addEventListener('click', (e) => {
        e.preventDefault();
        // Hide the current content so the edit interface can show.
        contentContainer.style.display = 'none';
        // Call the onEdit callback with necessary parameters.
        onEdit(note, noteElement, contentContainer, noteFooter);
    });

    // --- Create the Delete action ---
    const deleteLink = document.createElement('a');
    deleteLink.setAttribute('role', 'button');
    deleteLink.setAttribute('href', '#');
    deleteLink.dataset.noteId = note.id;
    deleteLink.classList.add('delete-note');

    const deleteIconUrl = M.util.image_url('t/delete', 'core').toString();
    const deleteAltText = M.util.get_string('delete', 'core');
    deleteLink.innerHTML = `<img src="${deleteIconUrl}" alt="${deleteAltText}" style="width:16px; height:16px;">`;

    // When the delete link is clicked, call the onDelete callback.
    deleteLink.addEventListener('click', (e) => {
        e.preventDefault();
        onDelete(note);
    });

    // Append the edit and delete actions to the actions container.
    actionsContainer.appendChild(editLink);
    actionsContainer.appendChild(deleteLink);

    // Assemble the footer: put the date and the actions together.
    noteFooter.appendChild(dateElem);
    noteFooter.appendChild(actionsContainer);

    // Assemble the main note element: content on top, footer below.
    noteElement.appendChild(contentContainer);
    noteElement.appendChild(noteFooter);

    return noteElement;
};

/**
 * Renders pagination controls.
 *
 * @param {number} currentOffset - The current offset (starting record).
 * @param {number} limit - Number of records per page.
 * @param {number} totalCount - Total number of records returned (including the extra one).
 * @param {Function} onPageChange - Callback function called with the new offset when a page button is clicked.
 * @returns {HTMLElement} - The pagination container element.
 */
export const renderPagination = (currentOffset, limit, totalCount, onPageChange) => {
    const paginationContainer = document.createElement('div');
    paginationContainer.classList.add('pagination-controls');

    // Create the Previous button.
    const prevButton = document.createElement('button');
    prevButton.textContent = 'Previous';
    prevButton.disabled = (currentOffset === 0);
    prevButton.addEventListener('click', () => {
        const newOffset = currentOffset - limit;
        if (newOffset >= 0) {
            onPageChange(newOffset);
        }
    });

    // Create the Next button.
    const nextButton = document.createElement('button');
    nextButton.textContent = 'Next';
    // If we fetched limit+1 records, then totalCount > limit means there is a next page.
    // If totalCount <= limit, disable Next.
    nextButton.disabled = (totalCount <= limit);
    nextButton.addEventListener('click', () => {
        const newOffset = currentOffset + limit;
        onPageChange(newOffset);
    });

    paginationContainer.appendChild(prevButton);
    paginationContainer.appendChild(nextButton);

    return paginationContainer;
};


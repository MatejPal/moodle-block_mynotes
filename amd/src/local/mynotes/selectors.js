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
 * TODO describe module selectors
 *
 * @module     block_mynotes/local/mynotes/selectors
 * @copyright  2025 Matej Pal <matej.pal@agiledrop.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export default {
    actions: {
    //     showGreetingButton: '[data-action="block_mynotes/helloworld-greet_button"]',
    //     resetButton: '[data-action="block_mynotes/helloworld-reset_button"]',
        showAlertButton: '[data-action="mynotes/helloworld-update_button"]',
    },
    regions: {
        // greetingBlock: '[data-region="block_mynotes/helloworld-usergreeting"]',
        inputField: '[data-region="block_mynotes/addnote-input"]',
    },
};

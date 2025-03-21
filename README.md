# My Notes #

My Notes is a Moodle block plugin that allows users to quickly add, edit,
and delete personal notes directly from their dashboard.
With a user-friendly interface and seamless AJAX-powered interactions,
My Notes is a simple yet effective tool to manage reminders, tasks,
or any personal information.

My Notes integrates tightly with Moodle’s external API and dynamic form framework.
When a user submits a new note, it is stored securely in the Moodle database,
and the user interface updates dynamically to reflect the change.
Similarly, editing or deleting a note is handled via AJAX,
ensuring a smooth and responsive user experience.

Key aspects include:

- AJAX-Driven Operations: Uses Moodle’s core AJAX system for adding, editing, and deleting notes,
providing immediate feedback and eliminating full page reloads.
- Dynamic Forms: Built on Moodle's dynamic form API,
the plugin maintains a consistent look and feel with the rest of Moodle.
- Pagination: Automatically paginates notes so users can easily navigate through their list, even if it grows large.
- Security & Permissions: Enforces capability checks
(such as block/mynotes:postnotes and block/mynotes:viewnotes)
to ensure that only authorized users can make changes.
- Unit Testing: Includes robust PHPUnit tests that cover key functionality,
ensuring the plugin remains reliable as it evolves.


## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/blocks/mynotes

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2025 Matej Pal <matej.pal@agiledrop.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.

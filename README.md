_An open source very lightweight wiki engine_

## Features

*   **Does not use database**; save the contents of the editor to the local filesystem
*   **Editor without WYSIWYG**; pages can also be edited directly in code editors
*   Page modification **history:** modification timeline with restore function (only when saved from wiki)
*   **Index** of all pages, with support for subdirectories (neasted folders)
*   Optional file delivery in **raw**; without framing it on the wiki
*   Can be installed in a **folder** or **root directory** of your website
*   HTML5, CSS and PHP; no javascript. Tested in **Apache** & **IIS**

## Reference

<table>

<tbody>

<tr>

<th>Home</th>

<td>Goes to the home page configured in config.php</td>

</tr>

<tr>

<th>Index</th>

<td>Show the pages within the pages folders (or its subfolders) with links to view, edit or version history</td>

</tr>

<tr>

<th>History</th>

<td>Show the list of previous version of a given page, with links to preview and restore</td>

</tr>

<tr>

<th>Restore</th>

<td>Take a snapshot of the current page and restore the selected version</td>

</tr>

<tr>

<th>Delete</th>

<td>Erase a givem page, leaving backups for restauration</td>

</tr>

<tr>

<th>Raw</th>

<td>Display the page without the wiki frame</td>

</tr>

<tr>

<th>Edit</th>

<td>Open a simple source code editor, with links to save, cancel, raw, history, index and home</td>

</tr>

<tr>

<th>Cancel</th>

<td>Discard changes on the source editor, going back to page view</td>

</tr>

<tr>

<th>Save</th>

<td>Create a backup of the current page (if it exists) and write the changes</td>

</tr>

<tr>

<th>Preview</th>

<td>Preview a history version the be restored, with links to restauration, history, index and home</td>

</tr>

</tbody>

</table>

## Examples

You can see a fully functional copy of Quiki at [http://quiki.blag.us/](http://quiki.blag.us/).

Examples of internal pages: [HTML elements](examples/Elements) and [font families](examples/Fonts) (you can also view the [index](./?index))

## Structure

<table>

<tbody>

<tr>

<th>`.htaccess & web.config`</th>

<td>Front controllers (forward all requests to index.php)</td>

</tr>

<tr>

<th>`index.php`</th>

<td>Quiki engine</td>

</tr>

<tr>

<th>`config.php`</th>

<td>Configuration file (title, template, folders etc.)</td>

</tr>

<tr>

<th>`lib/template.php`</th>

<td>Template HTML</td>

</tr>

<tr>

<th>`lib/layout.css`</th>

<td>Template CSS</td>

</tr>

<tr>

<th>`pages/`</th>

<td>Folder that contains all the wiki pages</td>

</tr>

<tr>

<th>`history/`</th>

<td>Backup folder, with page name as folder and timestamp as filenames</td>

</tr>

</tbody>

</table>

## Tested on browsers

<table>

<thead>

<tr>

<th>OS</th>

<th>Browser</th>

<th>Version</th>

</tr>

</thead>

<tbody>

<tr>

<td rowspan="4">Mac OS El Capitan</td>

<td>Google Chrome</td>

<td>47.0, 49.0</td>

</tr>

<tr>

<td>Mozilla Firefox</td>

<td>42.0</td>

</tr>

<tr>

<td>Apple Safari</td>

<td>9.0.2</td>

</tr>

<tr>

<td>Opera</td>

<td>12.16</td>

</tr>

<tr>

<td rowspan="5">Windows Server 2016  
(Windows 10)</td>

<td>Internet Explorer</td>

<td>11</td>

</tr>

<tr>

<td>Edge</td>

<td>?</td>

</tr>

<tr>

<td>Google Chrome</td>

<td>48, 50</td>

</tr>

<tr>

<td>Firefox</td>

<td>42</td>

</tr>

<tr>

<td>Opera</td>

<td>34</td>

</tr>

<tr>

<td rowspan="6">Windows 7</td>

<td>Internet Explorer</td>

<td>?</td>

</tr>

</tbody>

</table>

## Tested on webservers

*   Apache 2.4.16 (Mac OS El Capitan) + PHP 5.5.30
*   IIS 10 (Windows Server 2016) + PHP 5.6.0
*   Apache 2.3.9 (Linux) + PHP 5.3.29

## To do / known bugs

*   Header link: **"Rename"** [page] (and backups, with conflict check)
*   Header link: **"Move"** [page] (and backups, with conflict check) using the index table with a link "here" (at the right cell, in the same context of "edit"/"raw"/"history" links at rightest cell)
*   Header links: **"Previous"**, **"next"** and **"edit"** when in history preview
*   Header link: **"Create"** [page] (with window.prompt or any better solution)
*   **"Purge"** orphan history data. For a single deleted item, on red feedback message. Or for all history.
*   Old versions of IE do not support HSLA color mode; this is not intended to be fixed
*   Maybe, someday, a <span title="what you see is what you get">WYSIWYG</span> editor
*   Done: <del>Header link: **"Delete"** [page] (keep backups)</del>
*   Done: <del>Rename "newest" to **"latest"** and "oldest" to **"first"** in history table (at template)</del>

## Source code

Oh, yes, Quiki code is open source available on [GitHub](https://github.com/lexblagus/Quiki)

## About

I needed a wiki for the home page of all browsers in my computers; lightweight and fast and without a visual editor because I think is easier to edit HTML/JS/CSS by hand.

Inspired by [TigerWiki](http://c2.com/cgi/wiki?TigerWiki), created by [Lex Blagus](http://blag.us/).
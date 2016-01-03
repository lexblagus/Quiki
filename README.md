*An open source very lightweight wiki engine*

Features
--------

-   **Does not use database**; save the contents of the editor to the local filesystem
-   **Editor without WYSIWYG**; pages can also be edited directly in code editors
-   Page modification **history:** modification timeline with restore function (only when saved from wiki)
-   **Index** of all pages, with support for subdirectories (neasted folders)
-   Optional file delivery in **raw**; without framing it on the wiki
-   Can be installed in a **folder** or **root directory** of your website
-   HTML5, CSS and PHP; no javascript. Tested in **Apache** & **IIS**

Reference
---------

Home

Goes to the home page configured in config.php

Index

Show the pages within the pages folders (or its subfolders) with links to view, edit or version history

Edit

Open a simple source code editor, with links to save, cancel, raw, history, index and home

Save

Create a backup of the current page (if it exists) and write the changes

Cancel

Discard changes on the source editor, going back to page view

History

Show the list of previous version of a given page, with links to preview and restore

Preview

Preview a history version the be restored, with links to restauration, history, index and home

Restore

Take a snapshot of the current page and restore the selected version

Raw

Display the page without the wiki frame

Structure
---------

`/.htaccess & /web.config             `

Front controllers (forward all requests to index.php)

`/index.php             `

Quiki engine

`/config.php             `

Configuration file (title, template, folders etc.)

`/lib/template.php             `

Template HTML

`/lib/layout.css             `

Template CSS

`/pages             `

Folder that contains all the wiki pages

`/history             `

Backup folder

About
-----

I needed a wiki for the home page of all browsers in my computers; lightweight and fast and without a visual editor because I think is easier to edit HTML/JS/CSS by hand. Inspired by [TigerWiki](http://c2.com/cgi/wiki?TigerWiki), created by [Lex Blagus](http://blag.us/).

<p><i>An open source very lightweight wiki engine</i></p>

<h2>Features</h2>

<p>
	<ul>
		<li><b>Does not use database</b>; save the contents of the editor to the local filesystem</li>
		<li><b>Editor without WYSIWYG</b>; pages can also be edited directly in code editors</li>
		<li>Page modification <b>history:</b> modification timeline with restore function (only when saved from wiki)</li>
		<li><b>Index</b> of all pages, with support for subdirectories (neasted folders)</li>
		<li>Optional file delivery in <b>raw</b>; without framing it on the wiki</li>
		<li>Can be installed in a <b>folder</b> or <b>root directory</b> of your website</li>
		<li>HTML5, CSS and PHP; no javascript. Tested in <b>Apache</b> &amp; <b>IIS</b></li>
	</ul>
</p>

<h2>Reference</h2>

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
			<th>Edit</th>
			<td>Open a simple source code editor, with links to save, cancel, raw, history, index and home</td>
		</tr>
		<tr>
			<th>Save</th>
			<td>Create a backup of the current page (if it exists) and write the changes</td>
		</tr>
		<tr>
			<th>Cancel</th>
			<td>Discard changes on the source editor, going back to page view</td>
		</tr>
		<tr>
			<th>History</th>
			<td>Show the list of previous version of a given page, with links to preview and restore</td>
		</tr>
		<tr>
			<th>Preview</th>
			<td>Preview a history version the be restored, with links to restauration, history, index and home</td>
		</tr>
		<tr>
			<th>Restore</th>
			<td>Take a snapshot of the current page and restore the selected version</td>
		</tr>
		<tr>
			<th>Raw</th>
			<td>Display the page without the wiki frame</td>
		</tr>
	</tbody>
</table>

<h2>Structure</h2>

<table>
	<tbody>
		<tr>
			<th style="text-align:left;"><code>/.htaccess &amp; /web.config</code></th>
			<td>Front controllers (forward all requests to index.php)</td>
		</tr>
		<tr>
			<th style="text-align:left;"><code>/index.php</code></th>
			<td>Quiki engine</td>
		</tr>
		<tr>
			<th style="text-align:left;"><code>/config.php</code></th>
			<td>Configuration file (title, template, folders etc.)</td>
		</tr>
		<tr>
			<th style="text-align:left;"><code>/lib/template.php</code></th>
			<td>Template HTML</td>
		</tr>
		<tr>
			<th style="text-align:left;"><code>/lib/layout.css</code></th>
			<td>Template CSS</td>
		</tr>
		<tr>
			<th style="text-align:left;"><code>/pages</code></th>
			<td>Folder that contains all the wiki pages</td>
		</tr>
		<tr>
			<th style="text-align:left;"><code>/history</code></th>
			<td>Backup folder</td>
		</tr>
	</tbody>
</table>


<h2>About</h2>
I needed a wiki for the home page of all browsers in my computers; lightweight and fast and without a visual editor because I think is easier to edit HTML/JS/CSS by hand. Inspired by <a href="http://c2.com/cgi/wiki?TigerWiki">TigerWiki</a>, created by <a href="http://blag.us/">Lex Blagus</a>. 


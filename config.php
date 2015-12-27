<?php
$arrUserOptions = array(
	'title'           => 'Quiki',             // Title of the page to be shown in header and tab
	'template'        => 'template.php',      // Rendering file
	'pagesDir'        => 'pages',             // Directory where the wiki page lives
	'pagesSuffix'     => '.html',             // File extension
	'historyDir'      => 'history',           // Backup folder
	'home'            => 'Home',              // Homepage file (without extension if pagesSuffix is not empty)
	'history'         => 1,                   // Enable history feature (backups on save)
	'debug'           => 0                    // Application debug
);
if(0){
	// Options for using file extensions (allow opening another extensions than the suffix)
	$arrOptions['pagesSuffix'] = '';
	$arrOptions['home']        = 'home.html';
}

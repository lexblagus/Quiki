<?php
$arrUserOptions = array(
	'title'             => 'Quiki',             // Title of the page to be shown in header and tab
	'template'          => 'lib/template.php',  // Rendering file
	'pagesDir'          => 'pages',             // Directory where the wiki page lives
	'pagesSuffix'       => '.html',             // File extension
	'historyDir'        => 'history',           // Backup folder
	'home'              => 'Home',              // Homepage file (without extension if pagesSuffix is not empty)
	'delete'            => 1,                   // Enable deleting files (keep backups)
	'history'           => 1,                   // Enable history feature (backups on save)
	'debug'             => 0,                   // Application debug
	'enableUserDebug'   => 0,                   // Enable debug by querystring, e.g.: "http://domain/?debug=1"
	'additionalCSShref' => array(               // Add custom CSS files to the template head
		// 'http://domain.tld/custom.css',      // example of a remote CSS file
		// '/lib/custom-layout.css'             // example of a local CSS file (pay attention to the absolute path)
	),
	'additionalJSsrc'   => array(               // Add custom Javascript files to the template head
		// 'http://domain.tld/library.js',      // example of a remote Javascript file
		// '/lib/library.js'                    // example of a local Javascript file (pay attention to the absolute path)
	)
);
if(0){
	// Options for using file extensions (allow opening another extensions than the suffix)
	$arrOptions['pagesSuffix'] = '';
	$arrOptions['home']        = 'home.html';
}

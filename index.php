<?php
/*
================================================================================
 ////////        ///////                                                        
    /////          /////                                                        
    ////           ////                                                         
   /////           ////                                                         
   /////           ////                                                         
   /////          /////                                                         
   ////           /////                                                         
  /////           ////                                                          
  /////           ////                             ///                          
  ///// ////     /////     ///// ////     //////   //////////    ////     ///// 
  //// /   ///   /////   ////  / ////    ///   //// //  ////    /////   //   ///
 //////    ///   ////   ////    ////    ////   ////     ////    ////   ///   ///
 /////     ////  ////   ////    ////   /////   ////    /////    ////   ////  ///
 ////      //// /////  ////     ////   ////    ////    /////    ////   //////   
 ////     ///// ///// /////     ////   ////   /////    ////    /////   ///////  
/////     ///// ////  /////     ///    ////   ////     ////    ////     /////// 
/////     ////  ////  /////    ////    ////   ///      ////    ////       ///// 
////      //// /////  ////     ////  //   /////       /////   /////  ////  //// 
////     ////  /////  ////    /////  //               ////    /////  ////   /// 
////     ///   /////  ////    ////  /////////////     ////   ////// /////   /// 
 ///    ///    ////  / ///   / /// / /////////////    ////  /  /// /  //   ///  
  ///////       /////   /////   ///   ////////////     /////   ////    /////    
                                      ////////////                              
                                     //         //                              
                                    //          //                              
                                    //          /                               
                                    //         /                                
                                     //      //                                 
                                      ///////                                   
================================================================================
*/
//error_reporting(0); // no errors at all
error_reporting(E_ALL|E_STRICT); // all kinds of error


// =============================================================================
// Define options
$arrOptions = array(
	'title'       => 'Quiki',         // Title of the page to be shown in header and tab
	'template'    => 'template.php',  // Rendering file
	'pagesDir'    => 'pages',         // Directory where the wiki page lives
	'pagesSuffix' => '.html',         // File extension. May be empty if you want to use different page extensions, e.g.: http://quiki.local/myfile.txt
	'historyDir'  => 'history',       // Backup folder
	'home'        => 'home',          // Homepage file (without extension if pagesSuffix is not empty)
	'debugDomains'=> array(           // Virtual domains to dump debug data
		'debug.tars',
		'debug.quiki.tars'
	)
);


// =============================================================================
// Working variables
$loadTemplate = false;
// ...


// =============================================================================
/* Front controller logic

	Virtual HTTP address
	http://domain/appFolder1/appFolder2/wikiFolder1/wikiFolder2/page?action1=value1&action2=value2&action3#hash

	Local Windows filesystem
	C:\webserver\website\appFolder1\appFolder2\wikiFolder1\wikiFolder2\page.extension
	Local Unix-like filesystem
	/directory/webserver/website/appFolder1/appFolder2/wikiFolder1/wikiFolder2/page.extension
*/


// Application folder
$strAppBaseFolder = 
	str_replace(
		str_replace( // remove dir from file
			str_replace('\\','/',__DIR__), // normalize WIndows backslash
			'', 
			str_replace('\\','/',__FILE__) // normalize WIndows backslash
		),
		'', 
		$_SERVER["SCRIPT_NAME"]
	)
;
$strAppFolder = 
	preg_replace( 
		"/^\/|\/$/", // remove / from begin and end of uri
		"", 
		$strAppBaseFolder
	)
;
if($strAppFolder==''){
	$arrAppFolders = array();
}else{
	$arrAppFolders = 
		preg_split(
			"/\//", // split by /
			$strAppFolder
		)
	;
}


// Virtual folders
$arrRequest = 
	preg_split(
		"/\?/", // split by ? (querystring)
		$_SERVER['REQUEST_URI']
	)
;
$strTrimmedPath = 
	preg_replace(
		"/^\/|\/$/", // remove / from begin and end of uri
		"", 
		$arrRequest[0]
	)
;
if($strTrimmedPath==''){
	$arrVirtualFolders = array();
}else{
	$arrVirtualFolders = 
		preg_split(
			"/\//", // strip by /
			$strTrimmedPath
		)
	;
}
for ($i=0; $i < count($arrAppFolders); $i++) { 
	if( count($arrVirtualFolders) > 0 && $arrAppFolders[$i] == $arrVirtualFolders[0] ){
		$arrDevNull = array_shift($arrVirtualFolders); // remove an app folder from virtual path
	}
}


// Virtual page
if( $strAppFolder . '/' == $arrRequest[0] ){ // is application root
	$isFolder = true;
	$strVirtualPage = $arrOptions['home'];
}elseif( preg_match('/\/$/', $arrRequest[0]) ){ // is a folder, trim page from last index
	$isFolder = true;
	$strVirtualPage = $arrOptions['home'];
}else{
	$isFolder = false;
	$strVirtualPage = array_pop($arrVirtualFolders);
}


// Virtual references
$virtualTitle = implode(' / ', array_merge($arrVirtualFolders,array($strVirtualPage)));
$virtualHome = $strAppFolder=='' ? '/' : '/' . $strAppFolder . '/';
$virtualPath = '/' . implode('/', array_merge($arrAppFolders,$arrVirtualFolders,array($strVirtualPage)));
$virtualAbsIndex = '/' . implode('/', array_merge($arrAppFolders,$arrVirtualFolders)) . '/';
$isHome = count($arrVirtualFolders)==0 && $strVirtualPage==$arrOptions['home'];


// Actions (querystrings)
$arrActions = array();
if( count( $arrRequest ) >= 2 ){
	$arrActionsRaw = 
		preg_split(
			"/\&/", // strip by &
			$arrRequest[1]
		)
	;
	foreach ($arrActionsRaw as $idx => $value) {
		if(  !( strpos($value,'=') === false )  ){
			// Is a key pair
			$arrValue = explode('=', $value);
			$arrActions[ $arrValue[0] ] = $arrValue[1];
		}else{
			// Is a simple value, like an action
			array_push( $arrActions , $value);
		}
	}
}


// Local file
$localFile = 
	$arrOptions['pagesDir'] . 
	'/' .
	implode('/', array_merge($arrVirtualFolders,array($strVirtualPage))) .
	$arrOptions['pagesSuffix']
;
$localFileExists = file_exists( $localFile );
$localHistoryDir = 
	$arrOptions['historyDir'] . 
	'/' .
	implode('/', array_merge($arrVirtualFolders,array($strVirtualPage)))
;


// Front controller declaration, to be used at template
$frontController = array(
	'appBaseFolder'            => $strAppBaseFolder,
	'appFolder'                => $strAppFolder,
	'appFolders'               => $arrAppFolders,
	'virtualFolder'            => implode('/', $arrVirtualFolders), 
	'virtualFolders'           => $arrVirtualFolders, 
	'virtualPage'              => $strVirtualPage, 
	'virtualHome'              => $virtualHome,
	'virtualPath'              => $virtualPath,
	'virtualAbsIndex'          => $virtualAbsIndex, 
	'virtualTitle'             => $virtualTitle,
	'isHome'                   => $isHome,
	'actions'                  => $arrActions,
	'localFile'                => $localFile,
	'localFileExists'          => $localFileExists,
	'localHistoryDir'          => $localHistoryDir,
	'localIndexDir'            => false,
	'localIndexDirExists'      => false,
	'localIndexDirContents'    => array(),
	//...
	'messages'                 => array()
);


// =============================================================================
// Execute actions
if(  in_array("index" , $frontController['actions'])  ){
	// Retrieve directory listing
	$frontController['localIndexDir'] = __DIR__ . '\\' . $arrOptions['pagesDir'] . '\\' . implode('\\', $frontController['virtualFolders']);
	$frontController['localIndexDirExists'] = file_exists( $frontController['localIndexDir'] );
	if( $frontController['localIndexDirExists'] ){
		$arrDirList = scandir( $frontController['localIndexDir'] );
		$arrDirList = array_slice($arrDirList,2); // remove . and ..
		foreach($arrDirList as $item){
			array_push(
				$frontController['localIndexDirContents'] , 
				array(
					'name'        => 	preg_replace(
						'/' . $arrOptions['pagesSuffix'] . '$/',
						'', 
						$item
					),
					'kind'      => is_file( $frontController['localIndexDir'] . $item ) ? 'file' : 'folder',
					'virtualPage' => 'javascript:;',
					'lastChange'  => '31/12/9999',
					'sizeInBytes' => '99999'
				)
			);
		}
	}else{
		array_push($frontController['messages'], "Folder does not exist");
	}
	$loadTemplate = true;

}elseif(  in_array("history" , $frontController['actions'])  ){
	// Retrieve file history list
	//...
	$loadTemplate = true;

}elseif(  in_array("save" , $frontController['actions'])  ){
	// Save contents
	//...
	header('Location:' . $frontController['virtualAbsolute']) ;

}elseif(  in_array("restore" , $frontController['actions'])  ){
	// Restore contents from history
	//...
	$loadTemplate = true;

}elseif(  in_array("edit" , $frontController['actions'])  ){
	// Show file editor
	if( !$frontController['localFileExists'] ){ 
		array_push($frontController['messages'], "File not found; will create new one on save.");
	}
	//...
	$loadTemplate = true;

}elseif(  in_array("raw" , $frontController['actions'])  ){
	// Show raw file
	//...

}elseif(  in_array("preview" , $frontController['actions'])  ){
	// View history file
	//...
	$loadTemplate = true;

}else{
	// Read file (default action)
	if( $frontController['localFileExists'] ){ 
		array_push($frontController['actions'], "view");
		//...
		$loadTemplate = true;
	}else{
		// Redirect to editor
		if( count($frontController['actions'])>0 ){
			header('Location:' . $frontController['_SERVER_REQUEST_URI'] . '&edit') ;
		}else{
			header('Location:' . $frontController['virtualAbsolute'] . '?edit') ;
		}
	}
}


// =============================================================================
// debug is on the table
if( in_array($_SERVER['SERVER_NAME'], $arrOptions['debugDomains']) ){
	echo('<html><body><pre><code>');

	/*
	echo('$strAppFolder = ');
	var_export($strAppFolder);
	echo('<br>');
	echo('$arrAppFolders = ');
	var_export($arrAppFolders);
	echo('<br>');
	echo('$arrRequest = ');
	var_export($arrRequest);
	echo('<br>');
	echo('$strTrimmedPath = ');
	var_export($strTrimmedPath);
	echo('<br>');
	echo('$arrVirtualFolders = ');
	var_export($arrVirtualFolders);
	echo('<br>');
	echo('$strVirtualPage = ');
	var_export($strVirtualPage);
	echo('<br>');
	echo('$isFolder = ');
	var_export($isFolder);
	echo('<hr>');
	*/

	echo('$arrOptions = ');
	var_export($arrOptions);
	echo('<hr>');

	echo('$frontController = ');
	var_export($frontController);
	echo('<hr>');
	
	echo('__DIR__ = ');
	var_export(__DIR__);
	echo('<hr>');
	
	echo('__FILE__ = ');
	var_export(__FILE__);
	echo('<hr>');
	
	echo('$_SERVER["REQUEST_URI"] = ');
	var_export($_SERVER['REQUEST_URI']);
	echo('<br>');
	echo('$_SERVER["SCRIPT_NAME"] = ');
	var_export($_SERVER['SCRIPT_NAME']);
	echo('<br>');
	echo('$_SERVER = ');
	var_export($_SERVER);

	echo('</pre></code><hr></body></html>');
	die;
}


// =============================================================================
// Load template
if( $loadTemplate ){
	include_once( $arrOptions['template'] );
}

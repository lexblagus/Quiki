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
	'title'           => 'Quiki',             // Title of the page to be shown in header and tab
	'template'        => 'template.php',      // Rendering file
	'pagesDir'        => 'pages',             // Directory where the wiki page lives
	'pagesSuffix'     => '.html',             // File extension. May be empty if you want to use different page extensions, e.g.: http://quiki.local/myfile.txt
	'historyDir'      => 'history',           // Backup folder
	'home'            => 'Home',              // Homepage file (without extension if pagesSuffix is not empty)
	'history'         => 1,                   // Enable history (backups on save)
	'defaultTimezone' => 'America/Sao_Paulo', // Timezone (needed for file timestamps)
	'debug'           => 0                    // Application debug
);


// =============================================================================
// Working variables
$loadTemplate = false;
date_default_timezone_set( $arrOptions['defaultTimezone'] );
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
		urldecode($_SERVER['REQUEST_URI'])
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


// Virtual page
if( in_array("index" , $arrActions) ){ // is index
	$isFolder = true;
	$virtualPage = '';
}elseif( $strAppFolder . '/' == $arrRequest[0] ){ // is application root
	$isFolder = true;
	$virtualPage = $arrOptions['home'];
}elseif( preg_match('/\/$/', $arrRequest[0]) ){ // is a folder, trim page from last index
	$isFolder = true;
	$virtualPage = $arrOptions['home'];
}else{
	$isFolder = false;
	$virtualPage = array_pop($arrVirtualFolders);
}


// Virtual references
if( in_array("index" , $arrActions) ){ // is index
	$virtualTitle = implode(' / ', $arrVirtualFolders);
}else{
	$virtualTitle = implode(' / ', array_merge($arrVirtualFolders,array($virtualPage)));
}
$virtualHome = $strAppFolder=='' ? '/' : '/' . $strAppFolder . '/';
$virtualPath = '/' . implode('/', array_merge($arrAppFolders,$arrVirtualFolders,array($virtualPage)));
if( count($arrAppFolders)==0 && count($arrVirtualFolders)==0 ){
	$virtualAbsIndex = '/';
}else{
	$virtualAbsIndex = '/' . implode('/', array_merge($arrAppFolders,$arrVirtualFolders)) . '/';
}
$isHome = count($arrVirtualFolders)==0 && ($virtualPage==$arrOptions['home'] || $virtualPage=='');


// Local file
$localFile = 
	implode(
		'/',
		array_merge(
			array($arrOptions['pagesDir']),
			$arrVirtualFolders,
			array( $virtualPage != '' ? $virtualPage : $arrOptions['home'])
		)
	) .
	$arrOptions['pagesSuffix']
;
$localFileExists = file_exists( $localFile );
$localHistoryDir = 
	$arrOptions['historyDir'] . 
	'/' .
	implode('/', array_merge($arrVirtualFolders,array($virtualPage)))
;
$localHistoryFile = $localHistoryDir . '/' . date("YmdHis") . $arrOptions['pagesSuffix'];


// Front controller declaration, to be used at template
$frontController = array(
	'appBaseFolder'            => $strAppBaseFolder,
	'appFolder'                => $strAppFolder,
	'appFolders'               => $arrAppFolders,
	'virtualFolder'            => implode('/', $arrVirtualFolders), 
	'virtualFolders'           => $arrVirtualFolders, 
	'virtualPage'              => $virtualPage, 
	'virtualHome'              => $virtualHome,
	'virtualPath'              => $virtualPath,
	'virtualAbsIndex'          => $virtualAbsIndex, 
	'virtualTitle'             => $virtualTitle,
	'isHome'                   => $isHome,
	'localFile'                => $localFile,
	'localFileExists'          => $localFileExists,
	'localHistoryDir'          => $localHistoryDir,
	'localHistoryDirExists'    => false,
	'localHistoryFile'         => $localHistoryFile,
	'localIndexDir'            => false,
	'localIndexDirExists'      => false,
	'localIndexDirContents'    => array(),
	'actions'                  => $arrActions,
	'showActionHome'           => false,
	'showActionIndex'          => false,
	'showActionHistory'        => false,
	'showActionRaw'            => false,
	'showActionEdit'           => false,
	'showActionCancel'         => false,
	'showActionSave'           => false,
	'showSectionMain'          => false,
	'showSectionEdit'          => false,
	'showSectionHistory'       => false,
	'showSectionIndex'         => false,
	//...
	'contents'                 => isset($_POST["sourcecode"]) ? $_POST["sourcecode"] : '',
	'messages'                 => array()
);


// =============================================================================
// Execute actions
if(  in_array("index" , $frontController['actions'])  ){
	// Retrieve directory listing
	$frontController['localIndexDir'] = 
		preg_replace(
			'/\/\/$/', 
			'/', 
			str_replace(
				'\\',
				'/',
				__DIR__ . '\\' . $arrOptions['pagesDir'] . '\\' . implode('\\', $frontController['virtualFolders'])
			) . '/'
		)
	;
	$frontController['localIndexDirExists'] = file_exists( $frontController['localIndexDir'] );
	if( $frontController['localIndexDirExists'] ){
		$arrDirList = scandir( $frontController['localIndexDir'] , SCANDIR_SORT_ASCENDING);
		if( $isHome ){
			$arrDirList = array_diff( $arrDirList , array('.','..') ); // remove . and ..
		}else{
			$arrDirList = array_diff( $arrDirList , array('.') ); // remove .
		}
		$arrFiles = array();
		$arrFolders = array();
		foreach($arrDirList as $item){
			$itemName = preg_replace(
				'/' . $arrOptions['pagesSuffix'] . '$/',
				'', 
				$item
			);
			
			$itemLocal = $frontController['localIndexDir'] . $item;
			
			if(is_dir($itemLocal)){
				$itemKind =  'folder';
			}elseif(is_file($itemLocal)){
				$itemKind =  'file';
			}else{
				$itemKind =  'unknown';
			}
			
			if($itemKind == 'folder'){
				$itemVirtualPage = $frontController['virtualAbsIndex'] . $itemName . '/?index';
				$itemSize = -1;
			}elseif($itemKind == 'file'){
				$itemVirtualPage = $frontController['virtualAbsIndex'] . $itemName;
				$itemSize = filesize($itemLocal);
			}else{
				$itemVirtualPage = 'javascript:;';
				$itemSize = -1;
			}
			
			$arrItem = array(
				'name'        => $itemName,
				'kind'        => $itemKind,
				'virtualPage' => $itemVirtualPage,
				'lastChange'  => filemtime($itemLocal),
				'sizeInBytes' => $itemSize,
			);

			if($itemKind == 'folder'){
				array_push(
					$arrFolders , 
					$arrItem
				);
			}elseif($itemKind == 'file'){
				array_push(
					$arrFiles , 
					$arrItem
				);
			}
		}
		$frontController['localIndexDirContents'] = array_merge($arrFolders,$arrFiles);
	}else{
		array_push($frontController['messages'], "Folder does not exist");
	}
	$loadTemplate = true;

// -----------------------------------------------------------------------------
}elseif(  in_array("save" , $frontController['actions'])  ){
	// Save contents
	if( $arrOptions['history'] && $frontController['localFileExists'] ){
		$frontController['localHistoryDirExists'] = file_exists( $frontController['localHistoryDir'] );
		if( !$frontController['localHistoryDirExists'] ){
			$arrParts = explode('/', $frontController['localHistoryDir']);
			$countParts = count($arrParts);
			$currPart = '';
			for($i=0; $i<$countParts; $i++) {
				$currPart .= ($i > 0 ? '/' : '') . $arrParts[$i];
				if( !file_exists($currPart) ){
					mkdir($currPart);
				}
			}
			
		}
		copy($frontController['localFile'], $frontController['localHistoryFile']);
	}

	if( !$frontController['localFileExists'] ){
		$arrParts = explode('/', $frontController['localFile']);
		$countParts = count($arrParts);
		$currPart = '';
		for($i=0; $i<$countParts-1; $i++) {
			$currPart .= ($i > 0 ? '/' : '') . $arrParts[$i];
			if( !file_exists($currPart) ){
				mkdir($currPart);
			}
		}
	}
	
	file_put_contents( $frontController['localFile'] , $frontController['contents']);
	
	$loadTemplate = false;
	header('Location:' . $frontController['virtualPath']) ;

// -----------------------------------------------------------------------------
}elseif(  in_array("history" , $frontController['actions'])  ){
	// Retrieve file history list
	//...
	$loadTemplate = true;

// -----------------------------------------------------------------------------
}elseif(  in_array("restore" , $frontController['actions'])  ){
	// Restore contents from history
	//...
	$loadTemplate = true;

}elseif(  in_array("edit" , $frontController['actions'])  ){
	// Show file editor
	if( !$frontController['localFileExists'] ){ 
		array_push($frontController['messages'], "File not found; will create new one on save.");
	}
	if($frontController['localFileExists']){
		$frontController['contents'] = file_get_contents(
			$frontController['localFile']
		);
	}
	//...
	$loadTemplate = true;

// -----------------------------------------------------------------------------
}elseif(  in_array("raw" , $frontController['actions'])  ){
	// Show raw file
	//...

// -----------------------------------------------------------------------------
}elseif(  in_array("preview" , $frontController['actions'])  ){
	// View history file
	//...
	if($frontController['localFileExists']){
		$frontController['contents'] = file_get_contents(
			$frontController['localFile']
		);
	}
	$loadTemplate = true;

// -----------------------------------------------------------------------------
}else{
	// Read file (default action)
	if( $frontController['localFileExists'] ){ 
		array_push($frontController['actions'], "view");
		//...
		$frontController['contents'] = file_get_contents(
			$frontController['localFile']
		);
		$loadTemplate = true;
	}else{
		// Redirect to editor
		if( count($frontController['actions'])>0 ){
			header('Location:' . $frontController['_SERVER_REQUEST_URI'] . '&edit') ;
		}else{
			header('Location:' . $frontController['virtualPath'] . '?edit') ;
		}
	}
}


// History not enabled
if( !$arrOptions['history'] && in_array('history' , $frontController['actions']) ){
	array_push($frontController['messages'], "History not enabled.");
}


// Template control
// =============================================================================
$showActionHome = 
		in_array('index'   , $frontController['actions']) || 
		in_array('history' , $frontController['actions']) || 
		in_array('save'    , $frontController['actions']) || 
		in_array('restore' , $frontController['actions']) || 
		in_array('edit'    , $frontController['actions']) || 
		in_array('preview' , $frontController['actions']) || 
		(!$isHome && in_array('view' , $frontController['actions'])) 
;
$showActionIndex = 
		in_array('save'    ,  $frontController['actions']) ||
		in_array('restore' ,  $frontController['actions']) ||
		in_array('edit'    ,  $frontController['actions']) ||
		in_array('preview' ,  $frontController['actions']) ||
		in_array('view'    ,  $frontController['actions']) ||
		in_array("history" ,  $frontController['actions'])
;
$showActionHistory= 
		$arrOptions['history'] && $localFileExists &&
		(
			in_array('save'    , $frontController['actions']) ||
			in_array('restore' , $frontController['actions']) ||
			in_array('edit'    , $frontController['actions']) ||
			in_array('preview' , $frontController['actions']) ||
			in_array('view'    , $frontController['actions'])
		)
;
$showActionRaw = 
		$localFileExists && 
		(
			in_array('save'    , $frontController['actions']) ||
			in_array('restore' , $frontController['actions']) ||
			in_array('edit'    , $frontController['actions']) ||
			in_array('preview' , $frontController['actions']) ||
			in_array('view'    , $frontController['actions'])
		)
;
$showActionEdit = 
	in_array('save'    , $frontController['actions']) ||
	in_array('restore' , $frontController['actions']) ||
	in_array('view'    , $frontController['actions'])
;
$showActionCancel = in_array('edit' , $frontController['actions']);
$showActionSave   = in_array('edit' , $frontController['actions']);


$showSectionMain    = 
	in_array('save'    , $frontController['actions']) ||
	in_array('restore' , $frontController['actions']) ||
	in_array('preview' , $frontController['actions']) ||
	in_array('view'    , $frontController['actions'])
;
$showSectionEdit    = in_array('edit'    , $frontController['actions']);
$showSectionHistory = $arrOptions['history'] && in_array('history' , $frontController['actions']);
$showSectionIndex   = in_array('index'   , $frontController['actions']);


$frontController['showActionHome']           = $showActionHome;
$frontController['showActionIndex']          = $showActionIndex;
$frontController['showActionHistory']        = $showActionHistory;
$frontController['showActionRaw']            = $showActionRaw;
$frontController['showActionEdit']           = $showActionEdit;
$frontController['showActionCancel']         = $showActionCancel;
$frontController['showActionSave']           = $showActionSave;
$frontController['showSectionMain']          = $showSectionMain;
$frontController['showSectionEdit']          = $showSectionEdit;
$frontController['showSectionHistory']       = $showSectionHistory;
$frontController['showSectionIndex']         = $showSectionIndex;


// =============================================================================
// debug is on the table
if( $arrOptions['debug'] ){
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
	echo('$virtualPage = ');
	var_export($virtualPage);
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

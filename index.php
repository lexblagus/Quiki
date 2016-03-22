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
================================================================================ */
//error_reporting(0); // no errors at all
error_reporting(E_ALL|E_STRICT); // all kinds of error
// =============================================================================


class Quiki{


	// -----------------------------------------------------------------------------
	// Configuration
	// -----------------------------------------------------------------------------
	private $config = array(                      // default configuration. This may be overwritten by config.php
		'title'           => 'Quiki',             // Title of the page to be shown in header and tab
		'template'        => 'lib/template.php',  // Rendering file
		'pagesDir'        => 'pages',             // Directory where the wiki page lives
		'pagesSuffix'     => '.html',             // File extension
		'historyDir'      => 'history',           // Backup folder
		'home'            => 'Home',              // Homepage file (without extension if pagesSuffix is not empty)
		'delete'          => 1,                   // Enable deleting files (keep backups)
		'history'         => 1,                   // Enable history feature (backups on save)
		'debug'           => 1,                   // Debug mode
		'enableUserDebug' => 0                    // Enable debug by querystring "http://domain/?debug=1"
	);


	// -----------------------------------------------------------------------------
	// Working variables
	// -----------------------------------------------------------------------------
	private $loadTemplate = false;
	private $fileTimeFormat = 'YmdHis';


	// -----------------------------------------------------------------------------
	// Constructor
	// -----------------------------------------------------------------------------
	public function __construct($userConfig = array()){
		$this->log('log', $this->logIndent, __LINE__, '__construct');
		$this->logIndent++;
		
		if(1){ // log samples
			$this->logIndent++;
			$this->logHR(0);
			$this->log(0          ,  1 , __LINE__ , 'This is a log'     );
			$this->log('log'      ,  2 , __LINE__ , 'This is a log'     );
			$this->log(1          ,  3 , __LINE__ , 'This is a detail'  );
			$this->log('detail'   ,  4 , __LINE__ , 'This is a detail'  );
			$this->log(2          ,  5 , __LINE__ , 'This is a debug'   );
			$this->log('debug'    ,  6 , __LINE__ , 'This is a debug'   );
			$this->log(3          ,  7 , __LINE__ , 'This is a info'    );
			$this->log('info'     ,  8 , __LINE__ , 'This is a info'    );
			$this->log(4          ,  9 , __LINE__ , 'This is a warn'    );
			$this->log('warn'     , 10 , __LINE__ , 'This is a warn'    );
			$this->log(5          , 11 , __LINE__ , 'This is a error'   );
			$this->log('error'    , 12 , __LINE__ , 'This is a error'   );
			$this->log(6          , 13 , __LINE__ , 'This is a fatal'   );
			$this->log('fatal'    , 14 , __LINE__ , 'This is a fatal'   );
			$this->log(9          , 15 , __LINE__ , 'This is a unknown' );
			$this->log('unknown'  , 16 , __LINE__ , 'This is a unknown' );
			$this->log( null , null , null , 'This is a default message withou any parameter' );
			$this->logHR(0);
			$this->logIndent--;
		}
		
		$this->log('info',  $this->logIndent, __LINE__,'Setup configuration');
		$this->config = array_merge(
			$this->config,
			$userConfig
		);
		$this->log('debug', $this->logIndent, __LINE__,'$this->config = '."\n".var_export($this->config,true));
		
		date_default_timezone_set(@date_default_timezone_get());
		
		//...
		
		$this->logIndent--;
		$this->render();
	}


	// -----------------------------------------------------------------------------
	// Front controller logic
	// -----------------------------------------------------------------------------
	//	Virtual HTTP addresses
	//	http://domain/appFolder1/appFolder2/index.php/wikiFolder1/wikiFolder2/page?action1=value1&action2=value2&action3#hash
	//	http://domain/appFolder1/appFolder2/wikiFolder1/wikiFolder2/page?action1=value1&action2=value2&action3#hash
	//
	//	Local Windows filesystem
	//	C:\webserver\website\appFolder1\appFolder2\wikiFolder1\wikiFolder2\page.extension
	//	Local Unix-like filesystem
	//	/directory/webserver/website/appFolder1/appFolder2/wikiFolder1/wikiFolder2/page.extension
	// -----------------------------------------------------------------------------
	private function getFrontController(){
		$this->log('log', $this->logIndent, __LINE__,'render');

	}


	// -----------------------------------------------------------------------------
	// Render
	// -----------------------------------------------------------------------------
	private function render(){
		$this->log('log', $this->logIndent, __LINE__,'render');
		$this->logFlush();
		//...
	}


	// -----------------------------------------------------------------------------
	// Debug/log
	// -----------------------------------------------------------------------------
	private $logData = array();
	private $logIndent = 0;
	private $logLevels = array(
		'log',
		'detail',
		'debug' ,
		'info'  ,
		'warn'  ,
		'error' ,
		'fatal' 
	);
	private function log($level, $indent, $line, $message){
		$levelSearch = array_search($level, $this->logLevels);
		array_push(
			$this->logData, 
			array( 
				'level'   => $this->getLogLevel($level), 
				'indent'  => $indent===null ? $this->logIndent : $indent,
				'line'    => $line, 
				'message' => $message 
			)
		);
	}
	private function getLogLevel($level){
		if( is_string($level) ){
			$serchLevel = array_search($level, $this->logLevels);
			if($serchLevel===false){
				$intLevel = -1;
			}else{
				$intLevel = $serchLevel;
			}
		}elseif( is_int($level) ){
			$intLevel = $level;
		}else{
			$intLevel = -1;
		}
		return $intLevel;
	}
	private function logHR($level , $char='-'){
		array_push(
			$this->logData, 
			array( 
				'level' => $this->getLogLevel($level), 
				'indent' => 0,
				'line' => -1, 
				'message' => str_repeat($char, 80)
			)
		);
	}
	private function logFlush(){
		if(
			$this->config['debug']==1 || 
			(
				$this->config['enableUserDebug']==1 && 
				isset($_GET['debug']) && 
				$_GET['debug']==1
			)
		){
			echo('<html><body><pre><code>');
			echo('<b>Quiki debug mode</b>'."\n"."\n");
			// detect longest line number length
			$largestLineNumberStrLen = 0;
			foreach ($this->logData as $idx => $value) {
				$largestLineNumberStrLen = 
					$this->logData[$idx]['line'] > 0
					? (
						strlen($this->logData[$idx]['line']) > $largestLineNumberStrLen 
						? strlen($this->logData[$idx]['line']) 
						: $largestLineNumberStrLen
					)
					: $largestLineNumberStrLen
				;
			}
			// render contents
			foreach ($this->logData as $idx => $value) {
				// colorize
				if(      $this->logData[$idx]['level']==0  ){ echo('<span style="color:hsl(  0,    0%,  0%)">'); } // log
				elseif(  $this->logData[$idx]['level']==1  ){ echo('<span style="color:hsl(  0,    0%, 50%)">'); } // detail
				elseif(  $this->logData[$idx]['level']==2  ){ echo('<span style="color:hsl(135,   75%, 33%)">'); } // debug
				elseif(  $this->logData[$idx]['level']==3  ){ echo('<span style="color:hsl(240,   50%, 50%)">'); } // info
				elseif(  $this->logData[$idx]['level']==4  ){ echo('<span style="color:hsl( 45,  100%, 50%)">'); } // warn
				elseif(  $this->logData[$idx]['level']==5  ){ echo('<span style="color:hsl(  0,  100%, 50%)">'); } // error
				elseif(  $this->logData[$idx]['level']==6  ){ echo('<span style="color:hsl(315,  100%, 50%)">'); } // fatal
				else{                                         echo('<span style="color:hsl(  0,    0%, 90%)">'); } // unknown
				// indentation
				$indentChar = str_repeat("&nbsp;", $largestLineNumberStrLen); // maybe "\t"
				$indent = str_repeat(
					$indentChar,
					$this->logData[$idx]['indent']
				);
				// line number empty fill
				$lineNumberIndent = str_repeat(
					' ',
					(
						$largestLineNumberStrLen >= 0
						? (
							$largestLineNumberStrLen - strlen(
								$this->logData[$idx]['line']
							)
						)
						: 0
					)
				);
				// line number itself
				$lineNumber = $this->logData[$idx]['line'] > 0 ? $this->logData[$idx]['line'] . " " : "";
				$message = str_replace(
					"\n",
					"\n" . $indent . $indentChar . str_repeat(" ", $largestLineNumberStrLen),
					htmlentities(
						$this->logData[$idx]['message']
					)
				);
				echo($indent . $lineNumberIndent . $lineNumber . $message);
				echo('</span>'."\n");
			}
			echo('</pre></code></body></html>');
			die;
		}
	}
	// -----------------------------------------------------------------------------
}
// =============================================================================
new Quiki();
die; // ...





// ████████████████████████████████████████████████████████████████████████████████








//error_reporting(0); // no errors at all
error_reporting(E_ALL|E_STRICT); // all kinds of error


// =============================================================================
// Define options
include_once('config.php');
$arrOptions = array_merge(
	array( // default configuration. This is overwritten by config.php
		'title'           => 'Quiki',             // Title of the page to be shown in header and tab
		'template'        => 'lib/template.php',  // Rendering file
		'pagesDir'        => 'pages',             // Directory where the wiki page lives
		'pagesSuffix'     => '.html',             // File extension
		'historyDir'      => 'history',           // Backup folder
		'home'            => 'Home',              // Homepage file (without extension if pagesSuffix is not empty)
		'delete'          => 1,                   // Enable deleting files (keep backups)
		'history'         => 1,                   // Enable history feature (backups on save)
		'debug'           => 0                    // Application debug
	),
	$arrUserOptions
);


// =============================================================================
// Working variables
$loadTemplate = false;
date_default_timezone_set(@date_default_timezone_get());
$fileTimeFormat = 'YmdHis';
// ...


// =============================================================================
// Front controller logic
//
//	Virtual HTTP addresses
//	http://domain/appFolder1/appFolder2/index.php/wikiFolder1/wikiFolder2/page?action1=value1&action2=value2&action3#hash
//	http://domain/appFolder1/appFolder2/wikiFolder1/wikiFolder2/page?action1=value1&action2=value2&action3#hash
//
//	Local Windows filesystem
//	C:\webserver\website\appFolder1\appFolder2\wikiFolder1\wikiFolder2\page.extension
//	Local Unix-like filesystem
//	/directory/webserver/website/appFolder1/appFolder2/wikiFolder1/wikiFolder2/page.extension
//


// Application folder
$strAppBase = 
	str_replace(
		str_replace( // remove dir from file
			str_replace('\\','/',dirname(__FILE__)), // normalize Windows backslash
			'', 
			str_replace('\\','/',__FILE__) // normalize Windows backslash
		),
		'', 
		$_SERVER["SCRIPT_NAME"]
	)
;
if(strpos($_SERVER["REQUEST_URI"] ,$_SERVER["SCRIPT_NAME"])===0){ // url rewrite disabled("index.php/" is in the address)
	$strAppBaseFolder = $_SERVER["SCRIPT_NAME"];
	$strAppBaseRoot = $strAppBase;
}else{
	$strAppBaseFolder = $strAppBase;
	$strAppBaseRoot = $strAppBase;
}
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


// Virtual folders href
$arrVirtualFoldersHref = array();
$strAcumulateFolders = '';
for ($i=0; $i < count($arrVirtualFolders); $i++) { 
	$strAcumulateFolders .= $arrVirtualFolders[$i] . '/'; 
	array_push(
		$arrVirtualFoldersHref,
		$virtualHome . $strAcumulateFolders . '?index'
	);
}


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
$localHistoryFile = $localHistoryDir . '/' . date($fileTimeFormat) . $arrOptions['pagesSuffix'];


// Front controller declaration, to be used at template.
$frontController = array(
	'appBaseRoot'              => $strAppBaseRoot,
	'appBaseFolder'            => $strAppBaseFolder,
	'appFolder'                => $strAppFolder,
	'appFolders'               => $arrAppFolders,
	'virtualFolder'            => implode('/', $arrVirtualFolders), 
	'virtualFolders'           => $arrVirtualFolders, 
	'virtualFoldersHref'       => $arrVirtualFoldersHref,
	'virtualPage'              => $virtualPage, 
	'virtualHome'              => $virtualHome,
	'virtualPath'              => $virtualPath,
	'virtualAbsIndex'          => $virtualAbsIndex,
	'virtualTitle'             => $virtualTitle,
	'isHome'                   => $isHome,
	'localFile'                => $localFile,
	'localFileExists'          => $localFileExists,
	'localHistoryDir'          => $localHistoryDir,
	'localHistoryDirExists'    => false,                 // to be defined bellow
	'localHistoryDirContents'  => array(),               // to be defined bellow
	'localHistoryFile'         => $localHistoryFile,
	'localHistoryFileExists'   => false,                 // to be defined bellow
	'localIndexDir'            => false,                 // to be defined bellow
	'localIndexDirExists'      => false,                 // to be defined bellow
	'localIndexDirContents'    => array(),               // to be defined bellow
	'actions'                  => $arrActions,
	'showActionHome'           => false,                 // to be defined bellow
	'showActionIndex'          => false,                 // to be defined bellow
	'showActionHistory'        => false,                 // to be defined bellow
	'showActionRestore'        => false,                 // to be defined bellow
	'showActionRaw'            => false,                 // to be defined bellow
	'showActionDelete'         => false,                 // to be defined bellow
	'showActionEdit'           => false,                 // to be defined bellow
	'showActionCancel'         => false,                 // to be defined bellow
	'showActionSave'           => false,                 // to be defined bellow
	'showSectionMain'          => false,                 // to be defined bellow
	'showSectionEdit'          => false,                 // to be defined bellow
	'showSectionHistory'       => false,                 // to be defined bellow
	'showSectionIndex'         => false,                 // to be defined bellow
	'contents'                 => isset($_POST["sourcecode"]) ? $_POST["sourcecode"] : '',
	'messages'                 => array()                // to be defined bellow
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
				dirname(__FILE__) . '\\' . $arrOptions['pagesDir'] . '\\' . implode('\\', $frontController['virtualFolders'])
			) . '/'
		)
	;
	$frontController['localIndexDirExists'] = file_exists( $frontController['localIndexDir'] );
	if( $frontController['localIndexDirExists'] ){
		$arrDirList = scandir( $frontController['localIndexDir'] , 0);
		if( $isHome ){
			$arrDirList = array_diff( $arrDirList , array('.','..') ); // exclude . and ..
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
				if(
					$arrOptions['pagesSuffix'] == '' ||
					preg_match('/' . preg_quote($arrOptions['pagesSuffix']) . '$/', $item)
				){
					$itemVirtualPage = $frontController['virtualAbsIndex'] . $itemName;
					$itemSize = filesize($itemLocal);
				}else{
					$itemKind = 'unknown';
					$itemVirtualPage = 'javascript:;';
					$itemSize = -1;
				}
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
		if( count($frontController['localIndexDirContents']) == 0 ){
			array_push($frontController['messages'], "Folder is empty");
		}
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
	if( $arrOptions['history'] ){
		$frontController['localHistoryDirExists'] = file_exists( $frontController['localHistoryDir'] );
		if( $frontController['localHistoryDirExists'] ){
			$arrDirList = scandir( $frontController['localHistoryDir'] , 1);
			$arrDirList = array_diff( $arrDirList , array('.','..') ); // exclude . and ..
			foreach($arrDirList as $item){
				$itemLocal = $frontController['localHistoryDir'] . '/' . $item;
				$strFilePattern = '/^(\d{14})(' . preg_quote($arrOptions['pagesSuffix']) . ')$/';
				if( 
					is_file($itemLocal) &&
					preg_match($strFilePattern , $item)
				){
					$itemTimestamp = preg_replace($strFilePattern , '$1', $item);
					$itemDateTime = date_create_from_format($fileTimeFormat , $itemTimestamp);
					$itemSize = filesize($itemLocal);
					array_push(
						$frontController['localHistoryDirContents'],
						array(
							'timestamp'        => $itemTimestamp,
							'whenBackedUp'     => $itemDateTime,
							'sizeInBytes'      => $itemSize,
							'internalNote'     => '&nbsp;'
						)
					);
				}
			}
			// Discover newest item and put into notes
			$arrTimestamps = array();
			foreach($frontController['localHistoryDirContents'] as $idx => $arrProps){
				array_push($arrTimestamps, $arrProps['timestamp']);
			}
			$arrNewest = array_keys( $arrTimestamps , max($arrTimestamps) , true );
			foreach($arrNewest as $idxVal){
				$frontController['localHistoryDirContents'][$idxVal]['internalNote'] = '(latest)';
			}
			// Discover oldest item and put into notes
			$arrTimestamps = array();
			foreach($frontController['localHistoryDirContents'] as $idx => $arrProps){
				array_push($arrTimestamps, $arrProps['timestamp']);
			}
			$arrOldest = array_keys( $arrTimestamps , min($arrTimestamps) , true );
			foreach($arrOldest as $idxVal){
				$frontController['localHistoryDirContents'][$idxVal]['internalNote'] = '(first)';
			}
		}else{
			array_push($frontController['messages'], 'No history.');
		}
	} else {
		array_push($frontController['messages'], "This feature is not enabled.");
	}
	$loadTemplate = true;

// -----------------------------------------------------------------------------
}elseif(  in_array("preview" , $frontController['actions'])  ){
	// View history file
	if( $arrOptions['history'] ){
		if( isset( $frontController['actions']["timestamp"] ) ){
			$frontController['localHistoryFile']  = $localHistoryDir . '/' . $frontController['actions']['timestamp'] . $arrOptions['pagesSuffix'];
			$frontController['localHistoryFileExists'] = file_exists( $frontController['localHistoryFile'] );
			if( $frontController['localHistoryFileExists'] ){
				array_push($frontController['actions'], "view");
				$frontController['contents'] = file_get_contents(
					$frontController['localHistoryFile']
				);
			}else{
				array_push($frontController['messages'], 'Restore file not found');
			}
		}else{
			array_push($frontController['messages'], 'Missing action timestamp');
		}
	} else {
		array_push($frontController['messages'], "This feature is not enabled.");
	}
	$loadTemplate = true;

// -----------------------------------------------------------------------------
}elseif(  in_array("restore" , $frontController['actions'])  ){
	// Restore contents from history
	if( $arrOptions['history'] ){
		if( isset( $frontController['actions']["timestamp"] ) ){
			$localHistoryFileNow = $frontController['localHistoryFile'];
			$frontController['localHistoryFile']  = $localHistoryDir . '/' . $frontController['actions']['timestamp'] . $arrOptions['pagesSuffix'];
			$frontController['localHistoryFileExists'] = file_exists( $frontController['localHistoryFile'] );
			if( $frontController['localHistoryFileExists'] ){
				if( $frontController['localFileExists'] ){
					copy($frontController['localFile'], $localHistoryFileNow); // make backup
				}
				// Create directory structure (if not exists already)
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
				copy($frontController['localHistoryFile'] , $frontController['localFile']); // restore
				$loadTemplate = false;
				header('Location:' . $frontController['virtualPath']) ;
			}else{
				array_push($frontController['messages'], 'Restore file not found');
				$loadTemplate = true;
			}
		}else{
			array_push($frontController['messages'], 'Missing action timestamp');
			$loadTemplate = true;
		}
	} else {
		array_push($frontController['messages'], "This feature is not enabled.");
		$loadTemplate = true;
	}

// -----------------------------------------------------------------------------
}elseif(  in_array("delete" , $frontController['actions'])  ){
	// erase file
	if( $arrOptions['delete'] ){
		if( $frontController['localFileExists'] ){
			// make last backup
			if( $arrOptions['history'] ){
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

			// delete target file and all empty parent folders
			$arrParts = explode('/', $frontController['localFile']);
			$arrParts = array_reverse($arrParts);
			$countParts = count($arrParts);
			$currPart = '';
			$arrLocals = array();
			for($i=$countParts-1; $i>=0; $i--) {
				$currPart .= ($i < $countParts-1 ? '/' : '') . $arrParts[$i];
				array_push($arrLocals, $currPart);
			}
			$arrLocals = array_reverse($arrLocals);
			$isDeleted = false;
			foreach($arrLocals as $itemLocal){
				if(is_file($itemLocal)){
					try{
						if( @unlink($itemLocal) ){
							$isDeleted = true;
							array_push($frontController['messages'], "File deleted.");
						}else{
							$isDeleted = false;
						}
					}catch(exception $err){
						$isDeleted = false;
					}
					if(!$isDeleted){
						array_push($frontController['messages'], 'Unable to delete file: filesystem permitions or it is in use.');
						array_push($frontController['actions'], "view");
						$frontController['contents'] = file_get_contents(
							$frontController['localFile']
						);
						$loadTemplate = true;
					}
				}elseif($isDeleted && is_dir($itemLocal)){
					$arrDirList = scandir( $itemLocal );
					$arrDirList = array_diff( $arrDirList , array('.','..') ); // exclude . and ..
					if( count($arrDirList) == 0 ){
						try{
							if( @rmdir($itemLocal) ){
								$isDeleted = true;
							}else{
								$isDeleted = false;
							}
						}catch(exception $err){
							$isDeleted = false;
						}
						if(!$isDeleted){
							$loadTemplate = false;
							array_push($frontController['messages'], 'Error erasing folder: ' . $itemLocal);
						}
					}
				}
			}
			$loadTemplate = true;
		}else{
			array_push($frontController['messages'], "File not found. No worries.");
			$loadTemplate = true;
		}
	} else {
		array_push($frontController['messages'], "This feature is not enabled.");
		$loadTemplate = true;
	}


// -----------------------------------------------------------------------------
}elseif(  in_array("raw" , $frontController['actions'])  ){
	// Deliver raw file
	if( $frontController['localFileExists'] ){ 
		array_push($frontController['actions'], "view");
		header('Location:' . $frontController['appBaseRoot'] . '/' . $frontController['localFile']);
		$loadTemplate = false;
	}else{
		// Redirect to editor
		if( count($frontController['actions'])>0 ){
			header('Location:' . $frontController['_SERVER_REQUEST_URI'] . '&edit') ;
		}else{
			header('Location:' . $frontController['virtualPath'] . '?edit') ;
		}
	}

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
	$loadTemplate = true;

// -----------------------------------------------------------------------------
}else{
	// Read file (default action)
	if( $frontController['localFileExists'] ){ 
		array_push($frontController['actions'], "view");
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


// Template control
// =============================================================================
$showActionHome = 
		in_array('index'   , $frontController['actions']) || 
		in_array('history' , $frontController['actions']) || 
		in_array('save'    , $frontController['actions']) || 
		in_array('restore' , $frontController['actions']) || 
		in_array('delete'  , $frontController['actions']) || 
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
		in_array('edit'    , $frontController['actions']) || (
			$arrOptions['history'] && $localFileExists &&
			(
				in_array('save'    , $frontController['actions']) ||
				in_array('restore' , $frontController['actions']) ||
				in_array('delete'  , $frontController['actions']) ||
				in_array('preview' , $frontController['actions']) ||
				in_array('view'    , $frontController['actions'])
			)
		)
;
$showActionRestore = in_array('preview' , $frontController['actions']);
$showActionRaw = 
		$localFileExists && 
		!in_array('preview' , $frontController['actions']) &&
		(
			in_array('save'    , $frontController['actions']) ||
			in_array('restore' , $frontController['actions']) ||
			in_array('edit'    , $frontController['actions']) ||
			in_array('view'    , $frontController['actions'])
		)
;
$showActionDelete = 
		$arrOptions['delete'] && 
		$localFileExists && (
			$localFileExists && 
			(
				in_array('history' , $frontController['actions']) ||
				in_array('edit'    , $frontController['actions']) ||
				in_array('view'    , $frontController['actions'])
			)
		)
;
$showActionEdit = 
	!in_array('preview' , $frontController['actions']) &&
	(
		in_array('save'    , $frontController['actions']) ||
		in_array('restore' , $frontController['actions']) ||
		in_array('view'    , $frontController['actions'])
	)
;
$showActionCancel = 
	(
		in_array('edit'    , $frontController['actions']) ||
		in_array('preview' , $frontController['actions'])
	) && 
	$frontController['localFileExists']
;
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
$frontController['showActionRestore']        = $showActionRestore;
$frontController['showActionRaw']            = $showActionRaw;
$frontController['showActionDelete']         = $showActionDelete;
$frontController['showActionEdit']           = $showActionEdit;
$frontController['showActionCancel']         = $showActionCancel;
$frontController['showActionSave']           = $showActionSave;
$frontController['showSectionMain']          = $showSectionMain;
$frontController['showSectionEdit']          = $showSectionEdit;
$frontController['showSectionHistory']       = $showSectionHistory;
$frontController['showSectionIndex']         = $showSectionIndex;


// =============================================================================
// debug is on the table
if( $arrOptions['debug']==1 ){
	echo('<html><body><pre><code>');

	//echo('$strAppFolder = ');
	//echo(htmlentities(var_export($strAppFolder,true)));
	//echo('<br>');
	//echo('$arrAppFolders = ');
	//echo(htmlentities(var_export($arrAppFolders,true)));
	//echo('<br>');
	//echo('$arrRequest = ');
	//echo(htmlentities(var_export($arrRequest,true)));
	//echo('<br>');
	//echo('$strTrimmedPath = ');
	//echo(htmlentities(var_export($strTrimmedPath,true)));
	//echo('<br>');
	//echo('$arrVirtualFolders = ');
	//echo(htmlentities(var_export($arrVirtualFolders,true)));
	//echo('<br>');
	//echo('$virtualPage = ');
	//echo(htmlentities(var_export($virtualPage,true)));
	//echo('<br>');
	//echo('$isFolder = ');
	//echo(htmlentities(var_export($isFolder,true)));
	//echo('<hr>');

	echo('$arrOptions = ');
	echo(htmlentities(var_export($arrOptions,true)));
	echo('<hr>');

	echo('$frontController = ');
	echo(htmlentities(var_export($frontController,true)));
	echo('<hr>');
	
	echo('dirname(__FILE__) = ');
	echo(htmlentities(var_export(dirname(__FILE__),true)));
	echo('<hr>');
	
	echo('__FILE__ = ');
	echo(htmlentities(var_export(__FILE__,true)));
	echo('<hr>');
	
	echo('$_SERVER["REQUEST_URI"] = ');
	echo(htmlentities(var_export($_SERVER['REQUEST_URI'],true)));
	echo('<br>');
	echo('$_SERVER["SCRIPT_NAME"] = ');
	echo(htmlentities(var_export($_SERVER['SCRIPT_NAME'],true)));
	echo('<br>');
	echo('$_SERVER = ');
	echo(htmlentities(var_export($_SERVER,true)));

	echo('</pre></code><hr></body></html>');
	die;
}


// =============================================================================
// Load template
if( $loadTemplate ){
	include_once( $arrOptions['template'] );
}

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
		'debug'           => 0,                   // Debug mode
		'enableUserDebug' => 0                    // Enable debug by querystring, e.g.: "http://domain/?debug=1"
	);


	// -----------------------------------------------------------------------------
	// Working variables
	// -----------------------------------------------------------------------------
	public $frontController = array();
	private $loadTemplate = false;
	private $fileTimeFormat = 'YmdHis';


	// -----------------------------------------------------------------------------
	// Constructor
	// -----------------------------------------------------------------------------
	public function __construct($userConfig = array()){
		$this->log('log', $this->logIndent, __LINE__, '__construct', 1);
		$this->logIndent++;
		
		$this->init($userConfig);
		
		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Init
	// -----------------------------------------------------------------------------
	private function init($userConfig = array()){
		$this->log('log', $this->logIndent, __LINE__, 'init', 1);
		$this->logIndent++;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$this->log('info',  $this->logIndent, __LINE__,'Setup configuration', 1);
		$this->config = array_merge(
			$this->config,
			$userConfig
		);

		if(
			$this->config['enableUserDebug']==1 && 
			isset($_GET['debug']) && 
			$_GET['debug']==1
		){
			$this->log('info',  $this->logIndent, __LINE__,'Enable debug', 1);
			$this->config['debug']=1;
		}

		$this->log('debug', $this->logIndent, __LINE__,'$this->config = '.var_export($this->config,true), 1);
		
		$this->logSamples();
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		date_default_timezone_set(@date_default_timezone_get());
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$this->run();
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$this->logHR();
		$this->log('info', $this->logIndent, __LINE__,'Final results');
		$this->log('debug', $this->logIndent, __LINE__,'$this->config = '.var_export($this->config,true), 1);
		$this->log('debug', $this->logIndent, __LINE__,'$this->frontController = ' . var_export($this->frontController,true));
		$this->log('detail', $this->logIndent, __LINE__,'dirname(__FILE__) = ' . var_export(dirname(__FILE__),true));
		$this->log('detail', $this->logIndent, __LINE__,'__FILE__ = ' . var_export(__FILE__,true));
		$this->log('detail', $this->logIndent, __LINE__,'$_SERVER["REQUEST_URI"] = ' . var_export($_SERVER["REQUEST_URI"],true));
		$this->log('detail', $this->logIndent, __LINE__,'$_SERVER["SCRIPT_NAME"] = ' . var_export($_SERVER["SCRIPT_NAME"],true));
		$this->log('detail', $this->logIndent, __LINE__,'$_GET = ' . var_export($_GET,true));
		$this->log('detail', $this->logIndent, __LINE__,'$_POST = ' . var_export($_POST,true));
		$this->log('detail', $this->logIndent, __LINE__,'$_SERVER = ' . var_export($_SERVER,true));
		$this->log('info', $this->logIndent, __LINE__,'Render log');
		$this->logFlush();
		
		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Runner
	// -----------------------------------------------------------------------------
	private function run(){
		$this->log('log', $this->logIndent, __LINE__, 'run');
		$this->logIndent++;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$this->getFrontController();

		$this->log('info',  $this->logIndent, __LINE__,'Run actions');
		if     ( in_array("index"   , $this->frontController['actions']) ){
			$this->actionIndex();
		}elseif( in_array('save'    , $this->frontController['actions']) ){
			$this->actionSave();
		}elseif( in_array('history' , $this->frontController['actions']) ){
			$this->actionHistory();
		}elseif( in_array('preview' , $this->frontController['actions']) ){
			$this->actionPreview();
		}elseif( in_array('restore' , $this->frontController['actions']) ){
			$this->actionRestore();
		}elseif( in_array('delete'  , $this->frontController['actions']) ){
			$this->actionDelete();
		}elseif( in_array('raw'     , $this->frontController['actions']) ){
			$this->actionRaw();
		}elseif( in_array('edit'    , $this->frontController['actions']) ){
			$this->actionEdit();
		}else{
			$this->actionRead();
		}

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		$this->render();

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Front controller
	// -----------------------------------------------------------------------------
	private function getFrontController(){
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		//	Virtual HTTP addresses
		//	http://domain/appFolder1/appFolder2/index.php/wikiFolder1/wikiFolder2/page?action1=value1&action2=value2&action3#hash
		//	http://domain/appFolder1/appFolder2/wikiFolder1/wikiFolder2/page?action1=value1&action2=value2&action3#hash
		//
		//	Local Windows filesystem
		//	C:\webserver\website\appFolder1\appFolder2\wikiFolder1\wikiFolder2\page.extension
		//	Local Unix-like filesystem
		//	/directory/webserver/website/appFolder1/appFolder2/wikiFolder1/wikiFolder2/page.extension
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->log('log', $this->logIndent, __LINE__,'getFrontController');
		$this->logIndent++;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->log('info', $this->logIndent, __LINE__,'Get application folder');
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
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->log('info', $this->logIndent, __LINE__,'Get virtual folders');
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
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->log('info', $this->logIndent, __LINE__,'Get actions (querystrings)');
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
		$this->log('detail', $this->logIndent, __LINE__,'count($arrActions) = ' . count($arrActions));
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->log('info', $this->logIndent, __LINE__,'Get virtual page');
		if( in_array("index" , $arrActions) ){ // is index
			$isFolder = true;
			$virtualPage = '';
		}elseif( $strAppFolder . '/' == $arrRequest[0] ){ // is application root
			$isFolder = true;
			$virtualPage = $this->config['home'];
		}elseif( preg_match('/\/$/', $arrRequest[0]) ){ // is a folder, trim page from last index
			$isFolder = true;
			$virtualPage = $this->config['home'];
		}else{
			$isFolder = false;
			$virtualPage = array_pop($arrVirtualFolders);
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->log('info', $this->logIndent, __LINE__,'Get virtual references');
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
		$isHome = count($arrVirtualFolders)==0 && ($virtualPage==$this->config['home'] || $virtualPage=='');

		$this->log('info', $this->logIndent, __LINE__,'Get virtual folders href');
		$arrVirtualFoldersHref = array();
		$strAcumulateFolders = '';
		for ($i=0; $i < count($arrVirtualFolders); $i++) { 
			$strAcumulateFolders .= $arrVirtualFolders[$i] . '/'; 
			array_push(
				$arrVirtualFoldersHref,
				$virtualHome . $strAcumulateFolders . '?index'
			);
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->log('info', $this->logIndent, __LINE__,'Get local file');
		$localFile = 
			implode(
				'/',
				array_merge(
					array($this->config['pagesDir']),
					$arrVirtualFolders,
					array( $virtualPage != '' ? $virtualPage : $this->config['home'])
				)
			) .
			$this->config['pagesSuffix']
		;
		$localFileExists = file_exists( $localFile );
		$localHistoryDir = 
			$this->config['historyDir'] . 
			'/' .
			implode('/', array_merge($arrVirtualFolders,array($virtualPage)))
		;
		$localHistoryFile = $localHistoryDir . '/' . date($this->fileTimeFormat) . $this->config['pagesSuffix'];
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->log('info', $this->logIndent, __LINE__,'Set front controller array');
		$this->frontController = array(
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
		$this->log('debug', $this->logIndent, __LINE__,'$this->frontController = ' . var_export($this->frontController,true));
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: index
	// -----------------------------------------------------------------------------
	private function actionIndex(){
		$this->log('log', $this->logIndent, __LINE__,'actionIndex');
		$this->logIndent++;

		//...

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: read
	// -----------------------------------------------------------------------------
	private function actionRead(){
		$this->log('log', $this->logIndent, __LINE__,'actionRead');
		$this->logIndent++;

		if( $this->frontController['localFileExists'] ){ 
			$this->log('info', null, __LINE__, 'local file exists');
			array_push($this->frontController['actions'], "view");
			$this->frontController['contents'] = file_get_contents(
				$this->frontController['localFile']
			);
			$this->loadTemplate = true;
		}else{
			$this->log('info', null, __LINE__, 'Local file does not exists; redirect to editor');
			
			if( count($this->frontController['actions'])>0 ){
				$redirectTo = $this->frontController['_SERVER_REQUEST_URI'] . '&edit';
			}else{
				$redirectTo = $this->frontController['virtualPath'] . '?edit';
			}
			$this->log('debug', $this->logIndent, __LINE__,'$redirectTo = ' . var_export($redirectTo,true));
			
			if($this->config['debug'] != 1){
				$this->log('warn', null, __LINE__, 'Redirecting…');
				header('Location:' . $redirectTo) ;
			}else{
				$this->log('warn', null, __LINE__, 'Local file does not exists; would redirect to editor if not in debug mode');
			}
		}

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: save
	// -----------------------------------------------------------------------------
	private function actionSave(){
		$this->log('log', $this->logIndent, __LINE__,'actionSave');
		$this->logIndent++;

		//...

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: history
	// -----------------------------------------------------------------------------
	private function actionHistory(){
		$this->log('log', $this->logIndent, __LINE__,'actionHistory');
		$this->logIndent++;

		//...

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: preview
	// -----------------------------------------------------------------------------
	private function actionPreview(){
		$this->log('log', $this->logIndent, __LINE__,'actionPreview');
		$this->logIndent++;

		//...

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: restore
	// -----------------------------------------------------------------------------
	private function actionRestore(){
		$this->log('log', $this->logIndent, __LINE__,'actionIndex');
		$this->logIndent++;

		//...

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: delete
	// -----------------------------------------------------------------------------
	private function actionDelete(){
		$this->log('log', $this->logIndent, __LINE__,'actionDelete');
		$this->logIndent++;

		//...

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: raw
	// -----------------------------------------------------------------------------
	private function actionRaw(){
		$this->log('log', $this->logIndent, __LINE__,'actionRaw');
		$this->logIndent++;

		//...

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: edit
	// -----------------------------------------------------------------------------
	private function actionEdit(){
		$this->log('log', $this->logIndent, __LINE__,'actionEdit');
		$this->logIndent++;

		//...

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Render
	// -----------------------------------------------------------------------------
	private function render(){
		$this->log('log', $this->logIndent, __LINE__,'render');
		$this->logIndent++;
		$this->log('debug', $this->logIndent, __LINE__,'$this->loadTemplate = ' . var_export($this->loadTemplate,true));
		if( $this->loadTemplate==true ){
			$this->log('info', null, __LINE__, 'Render template file');
			$this->log('debug', $this->logIndent, __LINE__,'$this->config[\'template\'] = ' . var_export($this->config['template'],true));
			if( $this->config['debug']==false ){
				include_once( $this->config['template'] );
			}else{
				$this->log('warn', null, __LINE__, 'Do not render template file because we are in debug mode');
			}
		}else{
			$this->log('warn', null, __LINE__, 'Do not render template file');
		}

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Debug/log
	// -----------------------------------------------------------------------------
	private $logData = array();
	private $logIndent = 0;
	private $logLevels = array(
		array( 'levelNumber' => 0 , 'levelName' => 'fatal'   , 'color' => 'hsl(315,  100%, 50%)' ),
		array( 'levelNumber' => 1 , 'levelName' => 'error'   , 'color' => 'hsl(  0,  100%, 50%)' ),
		array( 'levelNumber' => 2 , 'levelName' => 'warn'    , 'color' => 'hsl( 45,  100%, 50%)' ),
		array( 'levelNumber' => 3 , 'levelName' => 'info'    , 'color' => 'hsl(240,   50%, 50%)' ),
		array( 'levelNumber' => 4 , 'levelName' => 'log'     , 'color' => 'hsl(  0,    0%,  0%)' ),
		array( 'levelNumber' => 5 , 'levelName' => 'debug'   , 'color' => 'hsl(135,   75%, 33%)' ),
		array( 'levelNumber' => 6 , 'levelName' => 'detail'  , 'color' => 'hsl(  0,    0%, 50%)' ),
		array( 'levelNumber' => 7 , 'levelName' => 'DEFAULT' , 'color' => 'hsl(  0,    0%, 75%)' )  // default must always be the last item
	);
	private function log($level, $indent, $line, $message, $force=false){
		if($this->config['debug']==1 || $force){ // do not accumulate log data if not in debug
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
	}
	private function getLogLevel($level){
		if( is_int($level) && isset($this->logLevels[$level]) ){ // by index
			return $this->logLevels[$level];
		}elseif( is_string($level) ){ // by level name
			for($i=0; $i<count($this->logLevels); $i++) { // find this level name
				if( $this->logLevels[$i]['levelName']==$level ){ // match found
					return $this->logLevels[$i];
				}
			}
			return $this->logLevels[count($this->logLevels)-1]; // no matches found
		}else{// no level found
			return $this->logLevels[count($this->logLevels)-1]; 
		}
	}
	private function logHR($level=7, $char='—'){ // -, _, —, ¯, ═, =, ▄, ▀, ▌, ▐, ▒, ▓, █
		array_push(
			$this->logData, 
			array( 
				'level' => $this->getLogLevel($level), 
				'indent' => 0,
				'line' => null, 
				'message' => str_repeat($char, 80)
			)
		);
	}
	private function logFlush(){
		if($this->config['debug']==1){
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
			echo('<html><body><pre><code>');
			echo('<b>Quiki debug mode</b>'."\n"."\n");
			foreach ($this->logData as $idx => $value) {
				// colorize
				echo('<span style="color:' . $this->logData[$idx]['level']['color'] . ';">');
				// indentation
				$indentChar = str_repeat(" ", $largestLineNumberStrLen) . " ";
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
				$lineNumber = $this->logData[$idx]['line'] > 0 ? $this->logData[$idx]['line'] . " " : " ";
				// message with lines indented
				$message = str_replace(
					"\n",
					"\n" . $indent . str_repeat(" ", $largestLineNumberStrLen) . " ",
					htmlentities(
						$this->logData[$idx]['message']
					)
				);
				// render
				echo($indent . $lineNumberIndent . $lineNumber . $message);
				echo('</span>'."\n");
			}
			echo('</pre></code></body></html>');
		}
	}
	private function logSamples(){
		$this->log('log',  $this->logIndent, __LINE__,'logSamples', 1);
		$this->logIndent++;
		
		$this->log(null , null , null , 'This is a default message without any parameter' );
		foreach ($this->logLevels as $arrLevel) {
			$this->log(
				$arrLevel['levelName'],
				null,
				__LINE__ ,
				'$this->log(\'' . $arrLevel['levelName'] . '\', null, __LINE__, \'' . $arrLevel['levelName'] . ' message\');'
			);
		}
		foreach ($this->logLevels as $arrLevel) {
			$this->log(
				$arrLevel['levelNumber'],
				null,
				__LINE__ ,
				'$this->log(\'' . $arrLevel['levelNumber'] . '\', null, __LINE__, \'' . $arrLevel['levelNumber'] . ' message\');'
			);
		}

		$this->logIndent--;
	}
	// -----------------------------------------------------------------------------
}
// =============================================================================
include_once('config.php');
$quiki = new Quiki(
	$arrUserOptions
);

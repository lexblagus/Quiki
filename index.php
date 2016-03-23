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
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$this->logHR();
		$this->logSamples();
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$this->logHR();
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
		
		$this->logHR();
		$this->getFrontController();

		$this->log('info',  $this->logIndent, __LINE__,'Run actions');
		$this->logHR();
		if     ( in_array("index"   , $this->frontController['actions']) ){
			$this->actionIndex();
		}elseif( in_array('history' , $this->frontController['actions']) ){
			$this->actionHistory();
		}elseif( in_array('restore' , $this->frontController['actions']) ){
			$this->actionRestore();
		}elseif( in_array('preview' , $this->frontController['actions']) ){
			$this->actionPreview();
		
		}elseif( in_array('delete'  , $this->frontController['actions']) ){
			$this->actionDelete();
		}elseif( in_array('save'    , $this->frontController['actions']) ){
			$this->actionSave();
		}elseif( in_array('edit'    , $this->frontController['actions']) ){
			$this->actionEdit();
		}elseif( in_array('raw'     , $this->frontController['actions']) ){
			$this->actionRaw();
		}else{
			$this->actionRead();
		}

		$this->logHR();
		$this->getActionsAndSections();

		$this->logHR();
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
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
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
			$this->log('warn', null, __LINE__, 'Local file does not exists; redirect to editor');
			$this->auxRedirect($this->frontController['virtualPath'] . '?edit');
		}

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: raw
	// -----------------------------------------------------------------------------
	private function actionRaw(){
		$this->log('log', $this->logIndent, __LINE__,'actionRaw');
		$this->logIndent++;
		
		if( $this->frontController['localFileExists'] ){ 
			array_push($this->frontController['actions'], "view");
			$this->auxRedirect($this->frontController['appBaseRoot'] . '/' . $this->frontController['localFile']);
			$this->loadTemplate = false;
		}else{
			$this->log('error', null, __LINE__, 'Local file does not exists; redirect to editor');
			$this->auxRedirect($this->frontController['virtualPath'] . '?edit');
		}
		
		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: edit
	// -----------------------------------------------------------------------------
	private function actionEdit(){
		$this->log('log', $this->logIndent, __LINE__,'actionEdit');
		$this->logIndent++;

		if( !$this->frontController['localFileExists'] ){ 
			array_push($this->frontController['messages'], "File does not exist yet; will create new one on save.");
		}
		if($this->frontController['localFileExists']){
			$this->frontController['contents'] = file_get_contents(
				$this->frontController['localFile']
			);
		}
		$this->loadTemplate = true;

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: save
	// -----------------------------------------------------------------------------
	private function actionSave(){
		$this->log('log', $this->logIndent, __LINE__,'actionSave');
		$this->logIndent++;

		$this->auxMakeLocalFileDirStruct();
		file_put_contents( $this->frontController['localFile'] , $this->frontController['contents']);
		$this->getFrontController();
		$this->auxMakeBackup();
		$this->loadTemplate = false;
		$this->auxRedirect( $this->frontController['virtualPath'] );

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: delete
	// -----------------------------------------------------------------------------
	private function actionDelete(){
		$this->log('log', $this->logIndent, __LINE__,'actionDelete');
		$this->logIndent++;

		if( $this->config['delete'] ){
			$this->log('info', null, __LINE__, 'Delete is enabled in config');

			if( $this->frontController['localFileExists'] ){
				$this->log('info', null, __LINE__, 'Make backup');
				$this->auxMakeBackup();

				$this->log('info', null, __LINE__, 'Delete target file and all empty parent folders');
				$arrParts = explode('/', $this->frontController['localFile']);
				$arrParts = array_reverse($arrParts);
				$countParts = count($arrParts);
				$currPart = '';
				$arrLocals = array();
				$this->logIndent++;
				for($i=$countParts-1; $i>=0; $i--) {
					$this->log('debug', $this->logIndent, __LINE__,'$i = '.var_export($i, true));
					$this->logIndent++;
					$currPart .= ($i < $countParts-1 ? '/' : '') . $arrParts[$i];
					$this->log('debug', $this->logIndent, __LINE__,'$currPart = '.var_export($currPart, true));
					array_push($arrLocals, $currPart);
					$this->logIndent--;
				}
				$this->logIndent--;
				$arrLocals = array_reverse($arrLocals);
				$this->log('debug', $this->logIndent, __LINE__,'$arrLocals = '.var_export($arrLocals, true));
				$isDeleted = false;
				$this->logIndent++;
				foreach($arrLocals as $itemLocal){
					$this->log('debug', $this->logIndent, __LINE__,'$itemLocal = '.var_export($itemLocal, true));
					$this->logIndent++;
					if(is_file($itemLocal)){
						$this->log('info', null, __LINE__, 'Is a file');
						try{
							$this->log('info', null, __LINE__, 'Trying to delete…');
							if( @unlink($itemLocal) ){
								$this->log('info', null, __LINE__, 'File deleted');
								$isDeleted = true;
								array_push($this->frontController['messages'], "File deleted.");
							}else{
								$this->log('error', null, __LINE__, 'File not deleted');
								$isDeleted = false;
							}
						}catch(exception $err){
							$this->log('error', null, __LINE__, 'Error erasing file');
							$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
							$isDeleted = false;
						}
						if(!$isDeleted){
							$this->log('info', null, __LINE__, 'Push error message');
							array_push($this->frontController['messages'], 'Unable to delete file: filesystem permitions or it is in use.');
							array_push($this->frontController['actions'], "view");
							$this->log('info', null, __LINE__, 'Get contents to render');
							$this->frontController['contents'] = file_get_contents(
								$this->frontController['localFile']
							);
							$this->loadTemplate = true;
						}
					}elseif($isDeleted && is_dir($itemLocal)){
						$this->log('info', null, __LINE__, 'Parent has been deleted and this is a directory');
						$arrDirList = scandir( $itemLocal );
						$arrDirList = array_diff( $arrDirList , array('.','..') ); // exclude . and ..
						$this->logIndent++;
						if( count($arrDirList) == 0 ){
							$this->log('info', null, __LINE__, 'Directory is empty');
							try{
								$this->log('info', null, __LINE__, 'Trying to delete…');
								if( @rmdir($itemLocal) ){
									$this->log('info', null, __LINE__, 'Directory deleted');
									$isDeleted = true;
								}else{
									$this->log('error', null, __LINE__, 'Directory not deleted');
									$isDeleted = false;
								}
							}catch(exception $err){
								$this->log('error', null, __LINE__, 'Error erasing directory');
								$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
								$isDeleted = false;
							}
							if(!$isDeleted){
								$this->log('info', null, __LINE__, 'Push erasing file error message');
								$this->loadTemplate = false;
								array_push($this->frontController['messages'], 'Error erasing folder: ' . $itemLocal);
							}
						}else{
							$this->log('warn', null, __LINE__, 'Responsability assurance: cannot delete a non-empty directory');
						}
						$this->logIndent--;
					}
					$this->logIndent--;
				}
				$this->logIndent--;
				$this->loadTemplate = true;
			}else{
				$this->log('warn', null, __LINE__, 'File to be deleted not found');
				array_push($this->frontController['messages'], "File not found. No worries.");
				$this->loadTemplate = true;
			}
		} else {
			$this->log('error', null, __LINE__, 'Delete is enabled in config');
			array_push($this->frontController['messages'], "This feature is not enabled.");
			$this->loadTemplate = true;
		}

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
	// Auxiliar: make backup
	// -----------------------------------------------------------------------------
	private function auxMakeBackup(){
		$this->log('log', $this->logIndent, __LINE__,'auxMakeBackup');
		$this->logIndent++;

		if( $this->config['history'] && $this->frontController['localFileExists'] ){
			$this->log('info', null, __LINE__, 'History is enabled and local file exists');
			$this->frontController['localHistoryDirExists'] = file_exists( $this->frontController['localHistoryDir'] );
			if( !$this->frontController['localHistoryDirExists'] ){
				$this->log('info', null, __LINE__, 'Create backup directory structure');
				$arrParts = explode('/', $this->frontController['localHistoryDir']);
				$countParts = count($arrParts);
				$currPart = '';
				for($i=0; $i<$countParts; $i++) {
					$this->log('debug', $this->logIndent, __LINE__,'$i = '.var_export($i,true));
					$this->logIndent++;
					$currPart .= ($i > 0 ? '/' : '') . $arrParts[$i];
					$this->log('debug', $this->logIndent, __LINE__,'$currPart = '.var_export($currPart,true));
					$this->logIndent++;
					if( !file_exists($currPart) ){
						$this->log('info', null, __LINE__, 'Create directory');
						mkdir($currPart);
					}else{
						$this->log('info', null, __LINE__, 'Directory exists');
					}
					$this->logIndent--;
					$this->logIndent--;
				}
				
			}
			copy($this->frontController['localFile'], $this->frontController['localHistoryFile']);
		}elseif(!$this->config['history']){
			$this->log('warn', null, __LINE__, 'History is not enabled.');
		}elseif(!$this->frontController['localFileExists']){
			$this->log('error', null, __LINE__, 'Local file to backup does not exists');
		}else{
			$this->log('error', null, __LINE__, 'Unpredicted error');
		}

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Auxiliar: make directory structure
	// -----------------------------------------------------------------------------
	private function auxMakeLocalFileDirStruct(){
		$this->log('log', $this->logIndent, __LINE__,'auxMakeLocalFileDirStruct');
		$this->logIndent++;

		if( !$this->frontController['localFileExists'] ){
			$this->log('info', null, __LINE__, 'Local file exists');
			$arrParts = explode('/', $this->frontController['localFile']);
			$countParts = count($arrParts);
			$currPart = '';
			for($i=0; $i<$countParts-1; $i++) {
				$this->log('debug', $this->logIndent, __LINE__,'$i = '.var_export($i,true));
				$this->logIndent++;
				$currPart .= ($i > 0 ? '/' : '') . $arrParts[$i];
				$this->log('debug', $this->logIndent, __LINE__,'$currPart = '.var_export($currPart,true));
				$this->logIndent++;
				if( !file_exists($currPart) ){
					$this->log('info', null, __LINE__, 'Create directory');
					mkdir($currPart);
				}else{
					$this->log('info', null, __LINE__, 'Directory exists');
				}
				$this->logIndent--;
				$this->logIndent--;
			}
		}else{
			$this->log('warn', null, __LINE__, 'Local file to make directory structure already exists');
		}

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Auxiliar: redirect
	// -----------------------------------------------------------------------------
	private function auxRedirect($redirectTo){
		$this->log('log', $this->logIndent, __LINE__,'auxRedirect');
		$this->logIndent++;

		$this->log('debug', $this->logIndent, __LINE__,'$redirectTo = ' . var_export($redirectTo,true));
		if(!$this->config['debug']){
			$this->log('warn', null, __LINE__, 'Redirecting…');
			header('Location:' . $redirectTo) ;
		}else{
			$this->log('warn', null, __LINE__, 'Would redirect to editor if not in debug mode');
		}

		$this->logIndent--;
	}



	// -----------------------------------------------------------------------------
	// Template controls
	// -----------------------------------------------------------------------------
	private function getActionsAndSections(){
		$this->log('log', $this->logIndent, __LINE__,'getActionsAndSections');
		$this->logIndent++;

		$showActionHome = 
				in_array('index'   , $this->frontController['actions']) || 
				in_array('history' , $this->frontController['actions']) || 
				in_array('save'    , $this->frontController['actions']) || 
				in_array('restore' , $this->frontController['actions']) || 
				in_array('delete'  , $this->frontController['actions']) || 
				in_array('edit'    , $this->frontController['actions']) || 
				in_array('preview' , $this->frontController['actions']) || 
				(!$this->frontController['isHome'] && in_array('view' , $this->frontController['actions'])) 
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showActionHome = '.var_export($showActionHome, true));

		$showActionIndex = 
				in_array('save'    ,  $this->frontController['actions']) ||
				in_array('restore' ,  $this->frontController['actions']) ||
				in_array('edit'    ,  $this->frontController['actions']) ||
				in_array('preview' ,  $this->frontController['actions']) ||
				in_array('view'    ,  $this->frontController['actions']) ||
				in_array("history" ,  $this->frontController['actions'])
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showActionIndex = '.var_export($showActionIndex, true));

		$showActionHistory= 
				in_array('edit'    , $this->frontController['actions']) || (
					$this->config['history'] && $this->frontController['localFileExists'] &&
					(
						in_array('save'    , $this->frontController['actions']) ||
						in_array('restore' , $this->frontController['actions']) ||
						in_array('delete'  , $this->frontController['actions']) ||
						in_array('preview' , $this->frontController['actions']) ||
						in_array('view'    , $this->frontController['actions'])
					)
				)
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showActionHistory = '.var_export($showActionHistory, true));

		$showActionRestore = in_array('preview' , $this->frontController['actions']);
		$this->log('debug', $this->logIndent, __LINE__,'$showActionRestore = '.var_export($showActionRestore, true));

		$showActionRaw = 
				$this->frontController['localFileExists'] && 
				!in_array('preview' , $this->frontController['actions']) &&
				(
					in_array('save'    , $this->frontController['actions']) ||
					in_array('restore' , $this->frontController['actions']) ||
					in_array('edit'    , $this->frontController['actions']) ||
					in_array('view'    , $this->frontController['actions'])
				)
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showActionRaw = '.var_export($showActionRaw, true));

		$showActionDelete = 
				$this->config['delete'] && 
				$this->frontController['localFileExists'] && (
					$this->frontController['localFileExists'] && 
					(
						in_array('history' , $this->frontController['actions']) ||
						in_array('edit'    , $this->frontController['actions']) ||
						in_array('view'    , $this->frontController['actions'])
					)
				)
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showActionDelete = '.var_export($showActionDelete, true));

		$showActionEdit = 
			!in_array('preview' , $this->frontController['actions']) &&
			(
				in_array('save'    , $this->frontController['actions']) ||
				in_array('restore' , $this->frontController['actions']) ||
				in_array('view'    , $this->frontController['actions'])
			)
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showActionEdit = '.var_export($showActionEdit, true));

		$showActionCancel = 
			(
				in_array('edit'    , $this->frontController['actions']) ||
				in_array('preview' , $this->frontController['actions'])
			) && 
			$this->frontController['localFileExists']
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showActionCancel = '.var_export($showActionCancel, true));

		$showActionSave   = in_array('edit' , $this->frontController['actions']);
		$this->log('debug', $this->logIndent, __LINE__,'$showActionSave = '.var_export($showActionSave, true));

		$showSectionMain    = 
			in_array('save'    , $this->frontController['actions']) ||
			in_array('restore' , $this->frontController['actions']) ||
			in_array('preview' , $this->frontController['actions']) ||
			in_array('view'    , $this->frontController['actions'])
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showSectionMain = '.var_export($showSectionMain, true));

		$showSectionEdit    = in_array('edit'    , $this->frontController['actions']);
		$this->log('debug', $this->logIndent, __LINE__,'$showSectionEdit = '.var_export($showSectionEdit, true));

		$showSectionHistory = $this->config['history'] && in_array('history' , $this->frontController['actions']);
		$this->log('debug', $this->logIndent, __LINE__,'$showSectionHistory = '.var_export($showSectionHistory, true));

		$showSectionIndex   = in_array('index'   , $this->frontController['actions']);
		$this->log('debug', $this->logIndent, __LINE__,'$showSectionIndex = '.var_export($showSectionIndex, true));

		$this->frontController['showActionHome']           = $showActionHome;
		$this->frontController['showActionIndex']          = $showActionIndex;
		$this->frontController['showActionHistory']        = $showActionHistory;
		$this->frontController['showActionRestore']        = $showActionRestore;
		$this->frontController['showActionRaw']            = $showActionRaw;
		$this->frontController['showActionDelete']         = $showActionDelete;
		$this->frontController['showActionEdit']           = $showActionEdit;
		$this->frontController['showActionCancel']         = $showActionCancel;
		$this->frontController['showActionSave']           = $showActionSave;
		$this->frontController['showSectionMain']          = $showSectionMain;
		$this->frontController['showSectionEdit']          = $showSectionEdit;
		$this->frontController['showSectionHistory']       = $showSectionHistory;
		$this->frontController['showSectionIndex']         = $showSectionIndex;

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
			include_once( $this->config['template'] );
			$this->log('warn', null, __LINE__, 'Do not render template file because we are in debug mode');
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
		array( 'levelNumber' => 2 , 'levelName' => 'warn'    , 'color' => 'hsl( 45,  100%, 45%)' ),
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
			echo('<html><body><hr><h1>Debug</h1><pre><code>'."\n"."\n");
			foreach ($this->logData as $idx => $value) {
				// colorize
				echo('<span class="debug" style="color:' . $this->logData[$idx]['level']['color'] . ';">');
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
		
		$this->log('info' , null , __LINE__ , 'All possible log variations:' );
		$this->logIndent++;
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
		$this->log('info' , null , __LINE__ , 'Some examples of log usage:' );
		$this->logIndent++;
		$this->log(null , null , null , '$this->log(null , null , null , \'No parameters\' );' );
		$this->log(null , null , null , '$this->log(\'debug\', $this->logIndent, __LINE__,\'$something = \'.var_export($something, true));' );
		$this->logIndent--;
		

		$this->logIndent--;
	}
	// -----------------------------------------------------------------------------
}
// =============================================================================
include_once('config.php');
$quiki = new Quiki(
	$arrUserOptions
);

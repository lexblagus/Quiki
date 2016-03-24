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
		'title'             => 'Quiki',             // Title of the page to be shown in header and tab
		'template'          => 'lib/template.php',  // Rendering file
		'pagesDir'          => 'pages',             // Directory where the wiki page lives
		'pagesSuffix'       => '.html',             // File extension
		'historyDir'        => 'history',           // Backup folder
		'home'              => 'Home',              // Homepage file (without extension if pagesSuffix is not empty)
		'delete'            => 1,                   // Enable deleting files (keep backups)
		'history'           => 1,                   // Enable history feature (backups on save)
		'debug'             => 0,                   // Debug mode
		'enableUserDebug'   => 0,                   // Enable debug by querystring, e.g.: "http://domain/?debug=1"
		'additionalCSShref' => array(),             // Add custom CSS files to the template head
		'additionalJSsrc'   => array()              // Add custom Javascript files to the template head
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

		$this->logHR();
		$this->log('info',  $this->logIndent, __LINE__,'Done class instantiation.', 1);
		
		$this->logIndent--;

		if(1){ // Diagnostics detailed informtion: get, post, server, php info and log samples
			$this->logHR(null, '═');
			$this->log('info', $this->logIndent, __LINE__,'Diagnostics');
			$this->log('detail', $this->logIndent, __LINE__,'$_GET = ' . var_export($_GET,true));
			$this->log('detail', $this->logIndent, __LINE__,'$_POST = ' . var_export($_POST,true));
			$this->logHR();
			$this->log('detail', $this->logIndent, __LINE__,'$_SERVER = ' . var_export($_SERVER,true));
			$this->logHR();
			$this->log('detail', $this->logIndent, __LINE__,'$this->phpinfo2array() = ' . var_export($this->phpinfo2array(),true));
			$this->logHR();
			$this->logSamples();
			$this->logHR();
		}

		$this->log('info', $this->logIndent, __LINE__,'Render log');
		$this->logFlush();
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
		if     ( false ){ // dummy
		}elseif( in_array("index"   , $this->frontController['actions']) ){ $this->actionIndex();
		}elseif( in_array('restore' , $this->frontController['actions']) ){ $this->actionRestore();
		}elseif( in_array('preview' , $this->frontController['actions']) ){ $this->actionPreview();
		}elseif( in_array('history' , $this->frontController['actions']) ){ $this->actionHistory();
		}elseif( in_array('delete'  , $this->frontController['actions']) ){ $this->actionDelete();
		}elseif( in_array('save'    , $this->frontController['actions']) ){ $this->actionSave();
		}elseif( in_array('edit'    , $this->frontController['actions']) ){ $this->actionEdit();
		}elseif( in_array('raw'     , $this->frontController['actions']) ){ $this->actionRaw();
		}else  {                                                            $this->actionRead(); }

		$this->logHR();
		$this->getActionsAndSections();

		$this->logHR();

		if(0){
			$this->log('warn', null, __LINE__, 'Show all menu itens for layout development');
			$this->frontController['showActionHome'] = true;
			$this->frontController['showActionNew'] = true;
			$this->frontController['showActionIndex'] = true;
			$this->frontController['showActionHistory'] = true;
			$this->frontController['showActionRestore'] = true;
			$this->frontController['showActionRaw'] = true;
			$this->frontController['showActionDelete'] = true;
			$this->frontController['showActionEdit'] = true;
			$this->frontController['showActionCancel'] = true;
			$this->frontController['showActionSave'] = true;
		}

		$this->render();
		$this->log('info',  $this->logIndent, __LINE__,'Done engine logic');

		$this->logHR();
		$this->log('debug', $this->logIndent, __LINE__,'$this->config = '.var_export($this->config,true), 1);
		$this->log('debug', $this->logIndent, __LINE__,'$this->frontController = ' . var_export($this->frontController,true));

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
		$this->log('debug', $this->logIndent, __LINE__,'$_SERVER["SCRIPT_NAME"] = ' . var_export($_SERVER["SCRIPT_NAME"],true));
		$this->log('debug', $this->logIndent, __LINE__,'__FILE__ = ' . var_export(__FILE__,true));
		$this->log('debug', $this->logIndent, __LINE__,'dirname(__FILE__) = ' . var_export(dirname(__FILE__),true));
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
		$this->log('debug', $this->logIndent, __LINE__,'$_SERVER["REQUEST_URI"] = ' . var_export($_SERVER["REQUEST_URI"],true));
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
		$this->log('debug', $this->logIndent, __LINE__,'count($arrActions) = ' . count($arrActions));
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
		try{
			$this->log('info', null, __LINE__, 'Try to access filesystem');
			$localFileExists = file_exists( $localFile );
		}catch(exception $err){
			$this->log('error', null, __LINE__, 'Error accessing filesystem');
			$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
		}
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
			'localHistoryDirExists'    => false,
			'localHistoryDirContents'  => array(),
			'localHistoryFile'         => $localHistoryFile,
			'localHistoryFileExists'   => false,
			'localIndexDir'            => false,
			'localIndexDirExists'      => false,
			'localIndexDirContents'    => array(),
			'actions'                  => $arrActions,
			'showActionHome'           => false,
			'showActionNew'            => false,
			'showActionIndex'          => false,
			'showActionHistory'        => false,
			'showActionRestore'        => false,
			'showActionRaw'            => false,
			'showActionDelete'         => false,
			'showActionEdit'           => false,
			'showActionCancel'         => false,
			'showActionSave'           => false,
			'showSectionMain'          => false,
			'showSectionEdit'          => false,
			'showSectionHistory'       => false,
			'showSectionIndex'         => false,
			'contents'                 => isset($_POST["sourcecode"]) ? $_POST["sourcecode"] : '',
			'messages'                 => array()
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
			$this->log('info', null, __LINE__, 'Local file exists');
			array_push($this->frontController['actions'], "view");
			try{
				$this->log('info', null, __LINE__, 'Try to access filesystem');
				$this->frontController['contents'] = file_get_contents(
					$this->frontController['localFile']
				);
			}catch(exception $err){
				$this->log('error', null, __LINE__, 'Error accessing filesystem');
				$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
			}
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
			$this->log('info', null, __LINE__, 'local file exists');
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
			try{
				$this->log('info', null, __LINE__, 'Try to access filesystem');
				file_put_contents( $this->frontController['localFile'] , $this->frontController['contents']);
			}catch(exception $err){
				$this->log('error', null, __LINE__, 'Error accessing filesystem');
				$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
			}
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
					try{
						$this->log('info', null, __LINE__, 'Try to access filesystem');
						$this->logIndent++;
						if(is_file($itemLocal)){
							$this->log('info', null, __LINE__, 'Is a file');
							try{
								$this->log('info', null, __LINE__, 'Try to access filesystem');
								if( @unlink($itemLocal) ){
									$this->log('info', null, __LINE__, 'File deleted');
									$isDeleted = true;
									array_push($this->frontController['messages'], "File deleted.");
								}else{
									$this->log('error', null, __LINE__, 'File not deleted');
									$isDeleted = false;
								}
							}catch(exception $err){
								$this->log('error', null, __LINE__, 'Error accessing filesystem');
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
									$this->log('info', null, __LINE__, 'Try to access filesystem');
									if( @rmdir($itemLocal) ){
										$this->log('info', null, __LINE__, 'Directory deleted');
										$isDeleted = true;
									}else{
										$this->log('error', null, __LINE__, 'Directory not deleted');
										$isDeleted = false;
									}
								}catch(exception $err){
									$this->log('error', null, __LINE__, 'Error accessing filesystem');
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
					}catch(exception $err){
						$this->log('error', null, __LINE__, 'Error accessing filesystem');
						$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
					}
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
	// Action: history
	// -----------------------------------------------------------------------------
	private function actionHistory(){
		$this->log('log', $this->logIndent, __LINE__,'actionHistory');
		$this->logIndent++;

		if( $this->config['history'] ){
			$this->log('info', null, __LINE__, 'History is enabled');
			$this->frontController['localHistoryDirExists'] = file_exists( $this->frontController['localHistoryDir'] );
			$this->logIndent++;
			if( $this->frontController['localHistoryDirExists'] ){
				$this->log('info', null, __LINE__, 'Local history dir exists');
				try{
					$this->log('info', null, __LINE__, 'Try to access filesystem');
					$arrDirList = scandir( $this->frontController['localHistoryDir'] , 1);
				}catch(exception $err){
					$this->log('error', null, __LINE__, 'Error accessing filesystem');
					$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
				}
				$arrDirList = array_diff( $arrDirList , array('.','..') ); // exclude . and ..
				$this->log('debug', $this->logIndent, __LINE__,'$arrDirList = '.var_export($arrDirList, true));
				foreach($arrDirList as $item){
					$this->log('debug', $this->logIndent, __LINE__,'$item = '.var_export($item, true));
					$this->logIndent++;
					$itemLocal = $this->frontController['localHistoryDir'] . '/' . $item;
					$this->log('debug', $this->logIndent, __LINE__,'$itemLocal = '.var_export($itemLocal, true));
					$strFilePattern = '/^(\d{14})(' . preg_quote($this->config['pagesSuffix']) . ')$/';
					$this->log('debug', $this->logIndent, __LINE__,'$strFilePattern = '.var_export($strFilePattern, true));
					try{
						$this->log('info', null, __LINE__, 'Try to access filesystem');
						$this->logIndent++;
						if( 
							is_file($itemLocal) &&
							preg_match($strFilePattern , $item)
						){
							$this->log('info', null, __LINE__, 'Item is file and match pattern');
							$itemTimestamp = preg_replace($strFilePattern , '$1', $item);
							$this->log('debug', $this->logIndent, __LINE__,'$itemTimestamp = '.var_export($itemTimestamp, true));
							$itemDateTime = date_create_from_format($this->fileTimeFormat , $itemTimestamp);
							$this->log('debug', $this->logIndent, __LINE__,'$itemDateTime = '.var_export($itemDateTime, true));
							$itemSize = filesize($itemLocal);
							$this->log('debug', $this->logIndent, __LINE__,'$itemSize = '.var_export($itemSize, true));
							array_push(
								$this->frontController['localHistoryDirContents'],
								array(
									'timestamp'        => $itemTimestamp,
									'whenBackedUp'     => $itemDateTime,
									'sizeInBytes'      => $itemSize,
									'internalNote'     => '&nbsp;'
								)
							);
						}else{
							$this->log('warn', null, __LINE__, 'Item is not file and/or match pattern');
						}
						$this->logIndent--;
					}catch(exception $err){
						$this->log('error', null, __LINE__, 'Error accessing filesystem');
						$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
					}
					$this->logIndent--;
				}
				$this->log('info', null, __LINE__, 'Discover newest item and put into notes');
				$arrTimestamps = array();
				$this->logIndent++;
				foreach($this->frontController['localHistoryDirContents'] as $idx => $arrProps){
					$this->log('debug', $this->logIndent, __LINE__,'$arrProps = '.var_export($arrProps, true));
					array_push($arrTimestamps, $arrProps['timestamp']);
				}
				$this->logIndent--;
				$arrNewest = array_keys( $arrTimestamps , max($arrTimestamps) , true );
				$this->log('debug', $this->logIndent, __LINE__,'$arrNewest = '.var_export($arrNewest, true));
				$this->logIndent++;
				foreach($arrNewest as $idxVal){
					$this->log('debug', $this->logIndent, __LINE__,'$idxVal = '.var_export($idxVal, true));
					$this->frontController['localHistoryDirContents'][$idxVal]['internalNote'] = '(latest)';
				}
				$this->logIndent--;
				$this->log('info', null, __LINE__, 'Discover oldest item and put into notes');
				$arrTimestamps = array();
				$this->logIndent++;
				foreach($this->frontController['localHistoryDirContents'] as $idx => $arrProps){
					$this->log('debug', $this->logIndent, __LINE__,'$arrProps = '.var_export($arrProps, true));
					array_push($arrTimestamps, $arrProps['timestamp']);
				}
				$this->logIndent--;
				$arrOldest = array_keys( $arrTimestamps , min($arrTimestamps) , true );
				$this->log('debug', $this->logIndent, __LINE__,'$arrOldest = '.var_export($arrOldest, true));
				$this->logIndent++;
				foreach($arrOldest as $idxVal){
					$this->log('debug', $this->logIndent, __LINE__,'$idxVal = '.var_export($idxVal, true));
					$this->frontController['localHistoryDirContents'][$idxVal]['internalNote'] = '(first)';
				}
				$this->logIndent--;
			}else{
				$this->log('warn', null, __LINE__, 'There is no history folder');
				array_push($this->frontController['messages'], 'No history.');
			}
			$this->logIndent--;
		} else {
			$this->log('error', null, __LINE__, 'History is not enabled');
			array_push($this->frontController['messages'], "This feature is not enabled.");
		}
		$this->loadTemplate = true;

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: preview
	// -----------------------------------------------------------------------------
	private function actionPreview(){
		$this->log('log', $this->logIndent, __LINE__,'actionPreview');
		$this->logIndent++;

		if( $this->config['history'] ){
			$this->log('info', null, __LINE__, 'History is enabled');
			$this->logIndent++;
			if( isset( $this->frontController['actions']["timestamp"] ) ){
				$this->log('info', null, __LINE__, 'Timestamp action has been declared');
				$this->frontController['localHistoryFile'] = $this->frontController['localHistoryDir'] . '/' . $this->frontController['actions']['timestamp'] . $this->config['pagesSuffix'];
				$this->frontController['localHistoryFileExists'] = file_exists( $this->frontController['localHistoryFile'] );
				$this->logIndent++;
				if( $this->frontController['localHistoryFileExists'] ){
					$this->log('info', null, __LINE__, 'Local history file exists');
					array_push($this->frontController['actions'], "view");
					try{
						$this->log('info', null, __LINE__, 'Try to access filesystem');
						$this->frontController['contents'] = file_get_contents(
							$this->frontController['localHistoryFile']
						);
					}catch(exception $err){
						$this->log('error', null, __LINE__, 'Error accessing filesystem');
						$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
					}
				}else{
					$this->log('error', null, __LINE__, 'Restore file not found');
					array_push($this->frontController['messages'], 'Restore file not found');
				}
				$this->logIndent--;
			}else{
				$this->log('error', null, __LINE__, 'Missing timestamp action');
				array_push($this->frontController['messages'], 'Missing timestamp action');
			}
			$this->logIndent--;
		} else {
			$this->log('error', null, __LINE__, 'History is not enabled');
			array_push($this->frontController['messages'], "This feature is not enabled.");
		}
		$this->loadTemplate = true;

		$this->logIndent--;
	}


	// -----------------------------------------------------------------------------
	// Action: restore
	// -----------------------------------------------------------------------------
	private function actionRestore(){
		$this->log('log', $this->logIndent, __LINE__,'actionRestore');
		$this->logIndent++;

		if( $this->config['history'] ){
			$this->log('info', null, __LINE__, 'History is enabled');
			if( isset( $this->frontController['actions']["timestamp"] ) ){
				$this->log('info', null, __LINE__, 'Timestamp action has been declared');
				$this->frontController['localHistoryFile']  = $this->frontController['localHistoryDir'] . '/' . $this->frontController['actions']['timestamp'] . $this->config['pagesSuffix'];
				$this->frontController['localHistoryFileExists'] = file_exists( $this->frontController['localHistoryFile'] );
				if( $this->frontController['localHistoryFileExists'] ){
					$this->auxMakeBackup();
					$this->auxMakeLocalFileDirStruct();
					$this->log('info', null, __LINE__, 'Restore file');
					try{
						$this->log('info', null, __LINE__, 'Try to access filesystem');
						copy($this->frontController['localHistoryFile'] , $this->frontController['localFile']);
					}catch(exception $err){
						$this->log('error', null, __LINE__, 'Error accessing filesystem');
						$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
					}
					$this->loadTemplate = false;
					$this->auxRedirect($this->frontController['virtualPath']);
				}else{
					array_push($this->frontController['messages'], 'Restore file not found');
					$this->loadTemplate = true;
				}
			}else{
				$this->log('error', null, __LINE__, 'Missing timestamp action');
				array_push($this->frontController['messages'], 'Missing timestamp action');
				$this->loadTemplate = true;
			}
		} else {
			$this->log('error', null, __LINE__, 'History is not enabled');
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

		$this->log('debug', $this->logIndent, __LINE__,'__FILE__ = ' . var_export(__FILE__,true));
		$this->log('debug', $this->logIndent, __LINE__,'dirname(__FILE__) = ' . var_export(dirname(__FILE__),true));
		$this->frontController['localIndexDir'] = 
			preg_replace(
				'/\/\/$/', 
				'/', 
				str_replace(
					'\\',
					'/',
					dirname(__FILE__) . '\\' . $this->config['pagesDir'] . '\\' . implode('\\', $this->frontController['virtualFolders'])
				) . '/'
			)
		;
		$this->log('debug', $this->logIndent, __LINE__,'$this->frontController[\'localIndexDir\'] = '.var_export($this->frontController['localIndexDir'], true));

		try{
			$this->log('info', null, __LINE__, 'Try to access filesystem');
			$this->frontController['localIndexDirExists'] = file_exists( $this->frontController['localIndexDir'] );
		}catch(exception $err){
			$this->log('error', null, __LINE__, 'Error accessing filesystem');
			$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
		}
		$this->log('debug', $this->logIndent, __LINE__,'$this->frontController[\'localIndexDirExists\'] = '.var_export($this->frontController['localIndexDirExists'], true));

		$this->logIndent++;
		if( $this->frontController['localIndexDirExists'] ){
			$this->log('info', null, __LINE__, 'Local index dir exists');
			try{
				$this->log('info', null, __LINE__, 'Try to access filesystem');
				$arrDirList = scandir( $this->frontController['localIndexDir'] , 0);
			}catch(exception $err){
				$this->log('error', null, __LINE__, 'Error accessing filesystem');
				$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
			}
			$this->logIndent++;
			if( $this->frontController['isHome'] ){
				$this->log('info', null, __LINE__, 'Home: exclude . and ..');
				$arrDirList = array_diff( $arrDirList , array('.','..') );
			}else{
				$this->log('info', null, __LINE__, 'Not home: exclude . only');
				$arrDirList = array_diff( $arrDirList , array('.') ); // remove .
			}
			$this->logIndent--;
			$this->log('debug', $this->logIndent, __LINE__,'$arrDirList = '.var_export($arrDirList, true));
			$arrFiles = array();
			$arrFolders = array();

			$this->log('info', null, __LINE__, 'Loop into items');

			foreach($arrDirList as $item){
				$this->log('debug', $this->logIndent, __LINE__,'$item = '.var_export($item, true));
				$this->logIndent++;
				$itemName = preg_replace(
					'/' . $this->config['pagesSuffix'] . '$/',
					'', 
					$item
				);
				$this->log('debug', $this->logIndent, __LINE__,'$itemName = '.var_export($itemName, true));
				
				$itemLocal = $this->frontController['localIndexDir'] . $item;
				$this->log('debug', $this->logIndent, __LINE__,'$itemLocal = '.var_export($itemLocal, true));
				
				try{
					$this->log('info', null, __LINE__, 'Try to access filesystem');
					$this->logIndent++;
					if(is_dir($itemLocal)){
						$this->log('info', null, __LINE__, 'Local item is folder');
						$itemKind =  'folder';
						$itemVirtualPage = $this->frontController['virtualAbsIndex'] . $itemName . '/?index';
						$itemSize = -1;
					}elseif(is_file($itemLocal)){
						$this->log('info', null, __LINE__, 'Local item is file');
						$itemKind =  'file';
						if(
							$this->config['pagesSuffix'] == '' ||
							preg_match('/' . preg_quote($this->config['pagesSuffix']) . '$/', $item)
						){
							$itemVirtualPage = $this->frontController['virtualAbsIndex'] . $itemName;
							try{
								$this->log('info', null, __LINE__, 'Try to access filesystem');
								$itemSize = filesize($itemLocal);
							}catch(exception $err){
								$this->log('error', null, __LINE__, 'Error accessing filesystem');
								$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
							}
						}else{
							$itemKind = 'unknown';
							$itemVirtualPage = 'javascript:;';
							$itemSize = -1;
						}
					}else{
						$this->log('info', null, __LINE__, 'Local item is unknown');
						$itemKind =  'unknown';
						$itemVirtualPage = 'javascript:;';
						$itemSize = -1;
					}
				}catch(exception $err){
					$this->log('error', null, __LINE__, 'Error accessing filesystem');
					$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
				}
				$this->logIndent--;
				$this->log('debug', $this->logIndent, __LINE__,'$itemKind = '.var_export($itemKind, true));
				$this->log('debug', $this->logIndent, __LINE__,'$itemVirtualPage = '.var_export($itemVirtualPage, true));
				$this->log('debug', $this->logIndent, __LINE__,'$itemSize = '.var_export($itemSize, true));
				
				$this->log('info', null, __LINE__, 'Push item');
				try{
					$this->log('info', null, __LINE__, 'Try to access filesystem');
					$arrItem = array(
						'name'        => $itemName,
						'kind'        => $itemKind,
						'virtualPage' => $itemVirtualPage,
						'lastChange'  => filemtime($itemLocal),
						'sizeInBytes' => $itemSize,
					);
				}catch(exception $err){
					$this->log('error', null, __LINE__, 'Error accessing filesystem');
					$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
				}
				$this->log('debug', $this->logIndent, __LINE__,'$arrItem = '.var_export($arrItem, true));

				$this->logIndent++;
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
				$this->logIndent--;

				$this->logIndent--;
			}
			$this->log('info', null, __LINE__, 'Push files and folders arrays to front controller');
			$this->log('debug', $this->logIndent, __LINE__,'$arrFolders = '.var_export($arrFolders, true));
			$this->log('debug', $this->logIndent, __LINE__,'$arrFiles = '.var_export($arrFiles, true));
			$this->frontController['localIndexDirContents'] = array_merge($arrFolders,$arrFiles);
			$this->log('debug', $this->logIndent, __LINE__,'$this->frontController[\'localIndexDirContents\'] = '.var_export($this->frontController['localIndexDirContents'], true));
			if( count($this->frontController['localIndexDirContents']) == 0 ){
				$this->log('info', null, __LINE__, 'Folder is empty');
				array_push($this->frontController['messages'], "Folder is empty");
			}else{
				$this->log('info', null, __LINE__, 'Folder is not empty');
			}
		}else{
			$this->log('error', null, __LINE__, 'Index folder does not exists');
			array_push($this->frontController['messages'], "Folder does not exist");
		}
		$this->logIndent--;
		$this->loadTemplate = true;

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
			if( !file_exists($this->frontController['localHistoryDir']) ){
				$this->log('info', null, __LINE__, 'Create backup directory structure');
				$arrParts = explode('/', $this->frontController['localHistoryDir']);
				$countParts = count($arrParts);
				$currPart = '';
				for($i=0; $i<$countParts; $i++) {
					$this->log('debug', $this->logIndent, __LINE__,'$i = '.var_export($i,true));
					$this->logIndent++;
					$currPart .= ($i > 0 ? '/' : '') . $arrParts[$i];
					$this->log('debug', $this->logIndent, __LINE__,'$currPart = '.var_export($currPart,true));
					try{
						$this->log('info', null, __LINE__, 'Try to access filesystem');
						$this->logIndent++;
						if( !file_exists($currPart) ){
							$this->log('info', null, __LINE__, 'Create directory');
							try{
								$this->log('info', null, __LINE__, 'Try to access filesystem');
								mkdir($currPart);
							}catch(exception $err){
								$this->log('error', null, __LINE__, 'Error accessing filesystem');
								$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
							}
						}else{
							$this->log('info', null, __LINE__, 'Directory exists');
						}
						$this->logIndent--;
					}catch(exception $err){
						$this->log('error', null, __LINE__, 'Error accessing filesystem');
						$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
					}
					$this->logIndent--;
				}
				
			}
			$this->log('info', null, __LINE__, 'Copy (with new name) local file to backup directory');
			$localHistoryFile = $this->frontController['localHistoryDir'] . '/' . date($this->fileTimeFormat) . $this->config['pagesSuffix'];
			$this->log('debug', $this->logIndent, __LINE__,'$localHistoryFile = '.var_export($localHistoryFile,true));
			try{
				$this->log('info', null, __LINE__, 'Try to access filesystem');
				copy(
					$this->frontController['localFile'],
					$localHistoryFile
				);
			}catch(exception $err){
				$this->log('error', null, __LINE__, 'Error accessing filesystem');
				$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
			}
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
					try{
						$this->log('info', null, __LINE__, 'Try to access filesystem');
						mkdir($currPart);
					}catch(exception $err){
						$this->log('error', null, __LINE__, 'Error accessing filesystem');
						$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
					}
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

		$showActionNew = 
			!in_array('preview'   , $this->frontController['actions']) &&
			(
				in_array('index'   , $this->frontController['actions']) || 
				in_array('view'    , $this->frontController['actions'])
			)
		;
		$this->log('debug', $this->logIndent, __LINE__,'$showActionNew = '.var_export($showActionNew, true));

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
			!in_array('preview'   , $this->frontController['actions']) &&
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
		$this->frontController['showActionNew']            = $showActionNew;
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
			try{
				$this->log('info', null, __LINE__, 'Try to access filesystem');
				include_once( $this->config['template'] );
			}catch(exception $err){
				$this->log('error', null, __LINE__, 'Error accessing filesystem');
				$this->log('debug', $this->logIndent, __LINE__,'$err = '.var_export($err, true));
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
			echo('<html><body>');
			echo('<h1 style="padding:0 0.5ex 0.25ex 0.5ex; color:hsl(45,100%,50%); background-color:hsl(0,0%,25%);">Debug</h1>');
			echo('<pre><code>');
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
				'$this->log(' . $arrLevel['levelNumber'] . ', null, __LINE__, \'' . $arrLevel['levelNumber'] . ' message\');'
			);
		}
		$this->logIndent--;

		$this->log('info' , null , __LINE__ , 'Indentation:' );

			for($i=0; $i <= $this->logIndent; $i++){
				$this->log('log' , $i , null , '$this->log(null , ' . $i . ' , null , \'Indentation ' . $i . '\' );' );
			}

			$this->logIndent++;
			$this->log('log' , null , null , '$this->logIndent++;' );
			$this->logIndent++;
			$this->log('log' , null , null , '$this->log(null , null , null , \'More indentation; must be used with null in second argument\' );' );
			$this->log('log' , null , null , '$this->logIndent--;' );
			$this->logIndent--;
			$this->log('log' , null , null , '$this->log(null , null , null , \'Less indentation; must be used with null in second argument\' );' );

		$this->logIndent--;

		$this->log('info' , null , __LINE__ , 'Some examples of log usage:' );
		$this->logIndent++;
		$this->log('log' , null , null , '$this->log(null , null , null , \'No parameters\' );' );
		$this->log('log' , null , null , '$this->log(\'debug\', $this->logIndent, __LINE__,\'$something = \'.var_export($something, true));' );
		$this->logIndent--;

		$this->logIndent--;
	}

	private function phpinfo2array() {
		$entitiesToUtf8 = function($input) {
			// http://php.net/manual/en/function.html-entity-decode.php#104617
			return preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input);
		};
		$plainText = function($input) use ($entitiesToUtf8) {
			return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
		};
		$titlePlainText = function($input) use ($plainText) {
			return '# '.$plainText($input);
		};
		
		ob_start();
		phpinfo(-1);
		
		$phpinfo = array('phpinfo' => array());

		// Strip everything after the <h1>Configuration</h1> tag (other h1's)
		if (!preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
			return array();
		}
		
		$input = $matches[1];
		$matches = array();

		if(preg_match_all(
			'#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|'.
			'(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
			$input, 
			$matches, 
			PREG_SET_ORDER
		)) {
			foreach ($matches as $match) {
				$fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
				if (strlen($match[1])) {
					$phpinfo[$match[1]] = array();
				} elseif (isset($match[3])) {
					$keys1 = array_keys($phpinfo);
					$phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array($fn($match[3]), $fn($match[4])) : $fn($match[3]);
				} else {
					$keys1 = array_keys($phpinfo);
					$phpinfo[end($keys1)][] = $fn($match[2]);
				}

			}
		}
		
		return $phpinfo;
	}


	// -----------------------------------------------------------------------------
}
// =============================================================================
include_once('config.php');

try{
	$quiki = new Quiki(
		$arrUserOptions
	);
}catch(exception $err){
	var_export($err);
}


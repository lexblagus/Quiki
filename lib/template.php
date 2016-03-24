<?php if( !isset($this->config) ){ header('Location:..') ; } /* do not render this page if called outside index.php */ ?><!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<!--
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
	-->
	<title>
		<?php echo($this->config['title'] . ' / ' . $this->frontController['virtualTitle'] ); ?>
		<?php if(  in_array("edit" , $this->frontController['actions'])  ){ ?> / edit<?php } ?>
		<?php if(  in_array("history" , $this->frontController['actions'])  ){ ?> / history<?php } ?>
		<?php if(  in_array("preview" , $this->frontController['actions'])  ){ ?> / preview<?php } ?>
		<?php if(  in_array("index" , $this->frontController['actions'])  ){ ?> / index<?php } ?>
	</title>
	<link rel="stylesheet" type="text/css" href="<?php echo( $this->frontController['appBaseRoot'] ); ?>/lib/reset.css" media="all">
	<link rel="stylesheet" type="text/css" href="<?php echo( $this->frontController['appBaseRoot'] ); ?>/lib/layout.css" media="all">
	<?php foreach($this->config['additionalCSShref'] as $val ){ ?>
	<link rel="stylesheet" type="text/css" href="<?php echo($val); ?>" media="all">
	<?php } ?>
	<script> 
// For discussion and comments, see: http://remysharp.com/2009/01/07/html5-enabling-script/
(function(){if(!/*@cc_on!@*/0)return;var e = "abbr,article,aside,audio,canvas,datalist,details,eventsource,figure,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,time,video".split(','),i=e.length;while(i--){document.createElement(e[i])}})()
	</script>
	<?php if(  in_array("edit" , $this->frontController['actions'])  ){ ?>
	<script type="text/javascript" src="<?php echo( $this->frontController['appBaseRoot'] ); ?>/lib/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="<?php echo( $this->frontController['appBaseRoot'] ); ?>/lib/jquery.autogrowtextarea.min.js"></script>
	<script type="text/javascript">
function addEvent(obj, evType, fn){
	if (obj.addEventListener){
		obj.addEventListener(evType, fn, false);
		return true;
	} else if (obj.attachEvent){
		var r = obj.attachEvent("on"+evType, fn);
		return r;
	} else {
		return false;
	}
}

addEvent(
	window,
	'load',
	function(){
		var el = document.getElementById('sourcecode');
		el.onkeydown = function(e){
			try{
				if (e.keyCode === 9) { // tab was pressed
					// get caret position/selection
					var val = 
						this.value,
						start = this.selectionStart,
						end = this.selectionEnd
					;
					// set textarea value to: text before caret + tab + text after caret
					this.value = val.substring(0, start) + '\t' + val.substring(end);
					// put caret at right position again
					this.selectionStart = this.selectionEnd = start + 1;
					// prevent the focus lose
					return false;
				}
			} catch(err){}
		};
	}
);

try{
	$(document).ready(
		function(){
			$("section form textarea").autoGrow(
				{ /* extraLine: true */ }
			);
		}
	);
} catch(err){}
	</script>
	<?php } ?>
	<?php foreach($this->config['additionalJSsrc'] as $val ){ ?>
	<script type="text/javascript" src="<?php echo($val); ?>"></script>
	<?php } ?>
</head>
<body class="_debug">
	<header>
		<div>
			<h1>
				<span class="title"><?php echo($this->config['title'] . ' / '); ?></span>
				<span class="page">
					<?php for($i=0; $i<count($this->frontController['virtualFolders']); $i++){ ?>
						<a href="<?php echo( $this->frontController['virtualFoldersHref'][$i] ); ?>"><?php echo( $this->frontController['virtualFolders'][$i] ); ?></a> / 
					<?php } ?>
					<a href="<?php echo( $this->frontController['virtualPath'] ); ?>"><?php echo( $this->frontController['virtualPage'] ); ?></a>
				</span>
				<?php if(  in_array("edit" , $this->frontController['actions'])  ){ ?><span class="action"> / edit</span><?php } ?>
				<?php if(  in_array("history" , $this->frontController['actions'])  ){ ?><span class="action"> / history</span><?php } ?>
				<?php if(  in_array("preview" , $this->frontController['actions'])  ){ ?><span class="action"> / preview</span><?php } ?>
				<?php if(  in_array("index" , $this->frontController['actions'])  ){ ?><span class="action"> index</span><?php } ?>
			</h1>
		</div>
		<nav>
			<ul>
				<?php if($this->frontController['showActionHome']){    ?><li><a href="<?php echo( $this->frontController['virtualHome'] ); ?>">home</a></li><?php } ?>
				<?php if($this->frontController['showActionNew']){     ?><li><a href="javascript:javascript:void(document.getElementById('deleteSure').style.display='none');void(document.getElementById('createPage').style.display='block');void(document.getElementById('createPageName').focus());">new</a></li><?php } ?>
				<?php if($this->frontController['showActionIndex']){   ?><li><a href="<?php echo($this->frontController['virtualAbsIndex']); ?>?index">index</a></li><?php } ?>
				<?php if($this->frontController['showActionHistory']){ ?><li><a href="?history">history</a></li><?php } ?>
				<?php if($this->frontController['showActionRestore']){ ?><li><a href="?restore&amp;timestamp=<?php echo(isset($this->frontController['actions']['timestamp']) ? $this->frontController['actions']['timestamp'] : ''); ?>">restore</a></li><?php } ?>
				<?php if($this->frontController['showActionDelete']){  ?><li><a href="javascript:void(document.getElementById('createPage').style.display='none');void(document.getElementById('deleteSure').style.display='block');">delete</a></li><?php } ?>
				<?php if($this->frontController['showActionRaw']){     ?><li><a href="?raw">raw</a></li><?php } ?>
				<?php if($this->frontController['showActionEdit']){    ?><li><a href="?edit">edit</a></li><?php } ?>
				<?php if($this->frontController['showActionCancel']){  ?><li><a href="<?php echo($this->frontController['virtualPath']); ?>">cancel</a></li><?php } ?>
				<?php if($this->frontController['showActionSave']){    ?><li><a href="javascript:(function(){document.formEdit.submit();})();">save</a></li><?php } ?>
			</ul>
		</nav>
		<?php if( count($this->frontController['messages']) > 0 ){ ?>
		<div id="messages" class="messages">
			<?php echo(implode('<br>' , $this->frontController['messages'])); ?>
		</div>
		<?php } ?>
		<div id="deleteSure" class="messages right" style="display:none;">
			Are you pretty sure about this?
			<a href="?delete">yes</a>
			<a href="javascript:void(document.getElementById('deleteSure').style.display='none');">no</a>
		</div>
		<div id="createPage" class="messages right" style="display:none;">
			Page name
			<span class="tip">(you can use </span>/<span class="tip"> to create folders)</span>:
			<span id="createPageName" contenteditable="true" onkeydown="javascript:if(event.keyCode==13){ document.location.href=document.getElementById('createPageName').innerHTML; return false; }else if( event.keyCode==27 ){ document.getElementById('createPage').style.display='none'; return false; }" ></span>
			<a href="javascript:void(document.location.href=document.getElementById('createPageName').innerHTML);">create</a>
			<a href="javascript:void(document.getElementById('createPage').style.display='none');">cancel</a>
		</div>
	</header>
	<?php if($this->frontController['showSectionMain']){ ?>
	<main>
<?php echo($this->frontController['contents']); ?>
	</main>
	<?php } ?>
	<?php if($this->frontController['showSectionEdit']){ ?>
	<section class="edit">
		<form name="formEdit" action="<?php echo($this->frontController['virtualPath']); ?>?save" method="post" enctype="application/x-www-form-urlencoded">
			<textarea id="sourcecode" name="sourcecode"><?php echo(htmlentities($this->frontController['contents'])); ?></textarea>
		</form>
	</section>
	<?php } ?>
	<?php if($this->frontController['showSectionHistory']){ ?>
	<section class="history">
		<table class="custom">
			<?php if(count($this->frontController['localHistoryDirContents'])>0){ ?>
			<thead>
				<tr>
					<th class="minWidth center">size</th>
					<th class="minWidth center" colspan="2">when</th>
					<th class="minWidth center" colspan="2">actions</th>
					<th class="maxWidth">&nbsp;</th>
				</tr>
			</thead>
			<?php } ?>
			<tbody>
				<?php foreach($this->frontController['localHistoryDirContents'] as $item ){ ?>
				<tr>
					<td class="minWidth right"><?php echo($item['sizeInBytes']); ?> bytes</td>
					<td class="minWidth"><?php echo( $item['whenBackedUp']->format("Y-m-d H:i:s") ); ?></td>
					<td class="minWidth notes"><?php echo($item['internalNote']); ?></td>
					<td class="minWidth tag"><a href="?restore&amp;timestamp=<?php echo($item['timestamp']); ?>">restore</a></td>
					<td class="minWidth tag"><a href="?preview&amp;timestamp=<?php echo($item['timestamp']); ?>">preview</a></td>
					<td class="maxWidth">&nbsp;</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</section>
	<?php } ?>
	<?php if($this->frontController['showSectionIndex']){ ?>
	<section class="index">
		<?php if( $this->frontController['localIndexDirExists'] ){ ?>
		<table class="custom">
			<thead>
					<th class="minWidth">name</th>
					<th class="minWidth center">size / kind</th>
					<th class="minWidth center">modified</th>
					<th class="minWidth center" colspan="4">actions</th>
					<th class="maxWidth">&nbsp;</th>
			</thead>
			<tbody>
				<?php foreach($this->frontController['localIndexDirContents'] as $item ){ ?>
				<tr class="<?php echo($item['kind']); ?>">
					<td class="minWidth"><a href="<?php echo($item['virtualPage']); ?>"><?php echo($item['name']); ?></a></td>
					<?php if( $item['kind']=='file' ){ ?>
					<td class="minWidth right"><?php echo($item['sizeInBytes']); ?> bytes</td>
					<?php } elseif( $item['kind']=='folder' ){ ?>
					<td class="minWidth right">folder</td>
					<?php } ?>
					<td class="minWidth"><?php echo( date("Y-m-d H:i:s",$item['lastChange']) ); ?></td>
					<?php if( $item['kind']=='file' ){ ?>
					<td class="minWidth tag"><a href="<?php echo($item['virtualPage']); ?>?edit"   >edit</a></td>
					<td class="minWidth tag"><a href="<?php echo($item['virtualPage']); ?>?raw"   >raw</a></td>
					<td class="minWidth tag"><a href="javascript:if(window.confirm('Pretty sure?')){location.href='<?php echo($item['virtualPage']); ?>?delete'};" >delete</a></td>
					<td class="minWidth tag"><a href="<?php echo($item['virtualPage']); ?>?history">history</a></td>
					<?php } elseif( $item['kind']=='folder' ){ ?>
					<td class="minWidth">&nbsp;</td>
					<td class="minWidth">&nbsp;</td>
					<td class="minWidth">&nbsp;</td>
					<td class="minWidth">&nbsp;</td>
					<?php } ?>
					<td class="maxWidth">&nbsp;</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php } ?>
	</section>
	<?php } ?>
</body>
</html>
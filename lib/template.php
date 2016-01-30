<?php if( !isset($arrOptions) ){ header('Location:..') ; } /* do not render this page if called outside index.php */ ?><!DOCTYPE html>
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
		<?php echo($arrOptions['title'] . ' / ' . $frontController['virtualTitle'] ); ?>
		<?php if(  in_array("edit" , $frontController['actions'])  ){ ?> / edit<?php } ?>
		<?php if(  in_array("history" , $frontController['actions'])  ){ ?> / history<?php } ?>
		<?php if(  in_array("preview" , $frontController['actions'])  ){ ?> / preview<?php } ?>
		<?php if(  in_array("index" , $frontController['actions'])  ){ ?> / index<?php } ?>
	</title>
	<link rel="stylesheet" type="text/css" href="<?php echo( $frontController['appBaseRoot'] ); ?>/lib/reset.css" media="all">
	<link rel="stylesheet" type="text/css" href="<?php echo( $frontController['appBaseRoot'] ); ?>/lib/layout.css" media="all">
	<script> 
// For discussion and comments, see: http://remysharp.com/2009/01/07/html5-enabling-script/
(function(){if(!/*@cc_on!@*/0)return;var e = "abbr,article,aside,audio,canvas,datalist,details,eventsource,figure,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,time,video".split(','),i=e.length;while(i--){document.createElement(e[i])}})()
	</script>
	<?php if(  in_array("edit" , $frontController['actions'])  ){ ?>
	<script type="text/javascript" src="<?php echo( $frontController['appBaseRoot'] ); ?>/lib/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="<?php echo( $frontController['appBaseRoot'] ); ?>/lib/jquery.autogrowtextarea.min.js"></script>
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
</head>
<body class="_debug">
	<header>
		<h1>
			<span class="title"><?php echo($arrOptions['title'] . ' / '); ?></span>
			<span class="page">
				<?php for($i=0; $i<count($frontController['virtualFolders']); $i++){ ?>
					<a href="<?php echo( $frontController['virtualFoldersHref'][$i] ); ?>"><?php echo( $frontController['virtualFolders'][$i] ); ?></a> / 
				<?php } ?>
				<a href="<?php echo( $frontController['virtualPath'] ); ?>"><?php echo( $frontController['virtualPage'] ); ?></a>
			</span>
			<?php if(  in_array("edit" , $frontController['actions'])  ){ ?><span class="action"> / edit</span><?php } ?>
			<?php if(  in_array("history" , $frontController['actions'])  ){ ?><span class="action"> / history</span><?php } ?>
			<?php if(  in_array("preview" , $frontController['actions'])  ){ ?><span class="action"> / preview</span><?php } ?>
			<?php if(  in_array("index" , $frontController['actions'])  ){ ?><span class="action"> index</span><?php } ?>
		</h1>
		<nav>
			<ul>
				<?php if($frontController['showActionHome']){    ?><li><a href="<?php echo( $frontController['virtualHome'] ); ?>">home</a></li><?php } ?>
				<?php if($frontController['showActionIndex']){   ?><li><a href="<?php echo($frontController['virtualAbsIndex']); ?>?index">index</a></li><?php } ?>
				<?php if($frontController['showActionHistory']){ ?><li><a href="?history">history</a></li><?php } ?>
				<?php if($frontController['showActionRestore']){ ?><li><a href="?restore&amp;timestamp=<?php echo(isset($_GET['timestamp']) ? $_GET['timestamp'] : ''); ?>">restore</a></li><?php } ?>
				<?php if($frontController['showActionDelete']){  ?><li><a href="javascript:if(window.confirm('Pretty sure?')){location.href='?delete'};">delete</a></li><?php } ?>
				<?php if($frontController['showActionRaw']){     ?><li><a href="?raw">raw</a></li><?php } ?>
				<?php if($frontController['showActionEdit']){    ?><li><a href="?edit">edit</a></li><?php } ?>
				<?php if($frontController['showActionCancel']){  ?><li><a href="<?php echo($frontController['virtualPath']); ?>">cancel</a></li><?php } ?>
				<?php if($frontController['showActionSave']){    ?><li><a href="javascript:(function(){document.formEdit.submit();})();">save</a></li><?php } ?>
			</ul>
		</nav>
		<div class="clear"></div>
		<?php if( count($frontController['messages']) > 0 ){ ?>
		<div id="messages" class="messages">
			<?php echo(implode('<br>' , $frontController['messages'])); ?>
			<a href="javascript:(function(){document.getElementById('messages').style.display='none';})();" class="close">dismiss</a>
		</div>
		<?php } ?>
	</header>
	<?php if($frontController['showSectionMain']){ ?>
	<main>
<?php echo($frontController['contents']); ?>
	</main>
	<?php } ?>
	<?php if($frontController['showSectionEdit']){ ?>
	<section class="edit">
		<form name="formEdit" action="<?php echo($frontController['virtualPath']); ?>?save" method="post" enctype="application/x-www-form-urlencoded">
			<textarea id="sourcecode" name="sourcecode"><?php echo(htmlentities($frontController['contents'])); ?></textarea>
		</form>
	</section>
	<?php } ?>
	<?php if($frontController['showSectionHistory']){ ?>
	<section class="history">
		<table class="custom">
			<!-- thead>
				<tr>
					<th class="minWidth">size</th>
					<th class="minWidth">when</th>
					<th class="minWidth">&nbsp;</th>
					<th class="minWidth">&nbsp;</th>
					<th class="minWidth">&nbsp;</th>
					<th class="maxWidth">&nbsp;</th>
				</tr>
			</thead -->
			<tbody>
				<?php foreach($frontController['localHistoryDirContents'] as $item ){ ?>
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
	<?php if($frontController['showSectionIndex']){ ?>
	<section class="index">
		<?php if( $frontController['localIndexDirExists'] ){ ?>
		<table class="custom">
			<tbody>
				<?php foreach($frontController['localIndexDirContents'] as $item ){ ?>
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
					<td class="minWidth tag"><a href="<?php echo($item['virtualPage']); ?>?edit"   >edit</a></td>
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
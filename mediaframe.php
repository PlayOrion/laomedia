<?php
include "dbconnect.php";

include "playerConfig.php";

//IMPORT VARIABLE MEDIA ID#
$mediaID = $_GET['id'];

if(!is_numeric($mediaID)){
	echo "<div style='width:300px;margin:50 auto;font-size:1.5em;'>Invalid media ID.</div>";
	exit();
	}


//IS VIDEO IN ALBUM WITH RESTRICTED ACCESS?
$albumPermission = 1;//set flag to public viewing
//GET ALBUM INFO
$stmt = $db->prepare("SELECT albummedia.albumID, albums.album, albums.permission FROM albummedia LEFT JOIN albums ON albummedia.albumID = albums.albumID WHERE albummedia.mediaID = :mediaID");
			$stmt->execute(array(':mediaID'=> $mediaID));
			$row = $stmt->fetch(PDO::FETCH_ASSOC); 
			//IF ALBUM PERMISSION RESTRICTED REDIRECT TO HTTPS
			if(isset($row['permission'])){
				if($row['permission'] != 'public'){
					$albumPermission = 0;//change flag to restricted viewing
				}
			}


//GET VIDEO INFO
$stmt = $db->prepare("SELECT * FROM media WHERE mediaID=:mediaID");
$stmt->execute(array(':mediaID'=> $mediaID));
$row = $stmt->fetch();
$title = $row['title'];
$type = $row['type'];//is multi bitrate available?
$permission = $row['permission'];//get permissions - public or limited?
$caption = $row['caption'];//are captions available?
$format = $row['format'];
$size = $row['size'];
$posterimage = $row['posterimage'];//poster image uploaded or use default?
$viewcount = $row['viewcount'] + 1;

if($stmt->rowCount() > 0){//video ID exists
//CHECK VIDEO PERMISSION
	if($permission == 'public'){
		//show video
	}elseif($permission == 'hidden'){
		echo"<div style='text-align:center;margin-top:150px;'>This video has restricted viewing access.</div>";
		exit();
	}elseif(($permission == 'album') && ($albumPermission == 1)){	
		//show video
	}else{	
	//redirect to secure page
	header("Location:mediaframesecure.php?id=" . $mediaID);
	}
}
	
//set player size
include "functions/playersize.php";

//set player image
include "functions/playerimage.php";
$db=null;

?>

<html>
<head>
<title>LAO Media</title>

<!-- START OF THE PLAYER EMBEDDING-->
<script type='text/javascript' src='assets/jwplayer/jwplayer.js'></script>
<script type="text/javascript">jwplayer.key="<?php echo $playerKey; ?>";</script>
<style type="text/css">
	.offscreen {
		position: absolute;
		right: 101%;
		overflow: hidden;
	}
	</style>
                                     
</head>

<body style='margin:0;padding:0;'>
<div id='mediaspace' style="margin:0px;padding:0px;border:none;"></div>
<script type="text/javascript">
	jwplayer("mediaspace").setup({
	playlist: [{
		image:"<?php echo $playerimage;?>",
		title:"<?php echo $title;?>",
    	sources: [
    		<?php
    		if($type == 'multivid'){
    			echo '{file: "http://' . $server . $wowzaport . $videocontent . 'smil:' . $mediaID . '.smil/jwplayer.smil"},';
    			echo '{file: "http://' . $server . $wowzaport . $videocontent . 'mp4:'  . $mediaID . '_hi.mp4/playlist.m3u8"}';
    		}elseif($type == 'singlevid'){
    			echo '{file: "rtmp://' . $server . $wowzaport . $videocontent . 'mp4:' . $mediaID . '.mp4"},';
    			echo '{file: "http://' . $server . $wowzaport . $videocontent . 'mp4:' .$mediaID . '.mp4/playlist.m3u8"}';
    		}elseif($type == 'audio'){
    			echo '{file: "rtmp://' . $server . $wowzaport . $videocontent . 'mp3:' . $mediaID . '.mp3"},';
    			echo '{file: "http://' . $server . $wowzaport . $videocontent . 'mp3:'  . $mediaID . '.mp3/playlist.m3u8"}';
    		}
    		?>    		    
		]
	<?php if($caption != 'none'){ ?>
    	,
    	tracks: [
            {file:"captions/<?php echo $mediaID; ?>.srt",
            	label: "English",
            	kind: "captions",
            	default: true }           
        	],
        	
    <?php } ?>
		}],
    	height: "100%",
    	width: "100%",
    	captions: {
        		back: true,
        		color:"ffffff",
        		fontsize: 16
    		}
	});

			//when viewer hits play store count to DB
			jwplayer("mediaspace").onPlay(function() {       
       			//alert("playing");
       			var mediaID = <?php echo $mediaID; ?>;
       			var viewcount = <?php echo $viewcount; ?>;
				var dataString = 'mediaID='+mediaID + '&viewcount=' + viewcount;
				$.ajax({
					type: "POST",  
 					 url: "updateViewcount.php",  
  					data: dataString   				
   		 		});      

     		});
    
			
</script>
<!--offscreen play button for screen readers -->
	<div class="offscreen">
		<a href="#"  onclick='jwplayer().play()'>Start or Pause Video Playback</a>
	</div>

<script src="assets/js/jquery.js"></script>    

</body>
</html>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Welcome to Twip</title>
<?php if (empty($info)) { ?>
	<script type="text/javascript"
	    src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {
	    var url= 'statuses/friends_timeline.json';
	    var params = {
		count: 5
	    };

	    $('#msg').ajaxStart(function() {
		$(this).empty();
		$(this).append("Loading...");
		$('#msg').css({color:"blue", fontSize:"24px"});
	    });

	    $.getJSON(url, params, function (json) {
		$('#content').empty();
		$.each(json, function(index, status) {
		    var row=$('<tr />');

		    var userTd=$('<td />');
		    userTd.attr({align : 'center'});
		    
		    var imgDiv=$('<div />');
		    var img=$('<img />');
		    img.attr({src: status.user.profile_image_url, width: '48', height: '48'});
		    imgDiv.append(img);

		    var nameDiv=$('<div />');
		    nameDiv.append(status.user.screen_name);

		    userTd.append(imgDiv).append(nameDiv);
		    
		    var textTd=$('<td />');
		    textTd.append(status.text);

		    var timeTd=$('<td />');
		    timeTd.attr({width: '15%'});
		    var date=new Date(status.created_at);
		    var dateStr=date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate()+' '+date.getHours()+':'+date.getMinutes();
		    timeTd.append(dateStr);

		    row.append(userTd).append(textTd).append(timeTd);

		    $('#content').append(row);
		});
		
		$('#content').attr("border","1");
		
		$('#msg').empty();
		$('#msg').append("Ok!");
		$('#msg').css({color:"green", fontWeight:"bold", fontSize:"24px"});
	    });

	    $('#msg').ajaxError(function() {
		$(this).empty();
		$(this).append("Failed!"); 
		$('#msg').css({color:"red", fontWeight:"bold", fontSize:"24px"});
	    });
	});
	</script>
<?php } ?>
</head>
<body>
	<div>
	<h2>Welcome to Twip.</h2>

	<p>This is a Twitter API proxy,and is not intend to be viewed in a browser. If you are having issues try <a href='./clearsessions.php'>clearing your session</a>.</p>
	<?php if ($enable_oauth) { ?>
		<p>To start get the access token, <a href="./redirect.php"><img src="./images/lighter.png" alt="Sign in Twitter" border="0" /></a>. Forget your password? Just simply re-get the access token.</p>
		<p>If you have saved the access token, you could <a href="./index.php?login=true">show your timeline</a> for a test.</p>
		<p>If you want to delete the token saved in, please <a href="./index.php?login=true">login</a> and then <a href='./clearsessions.php?removetoken=true'>remove the token</a>.</p>
	<?php } else { ?>
		<p>You could <a href="./login.php">show your friends' timeline</a> for a test.</p>
	<?php } ?>
	<hr />
	<?php if (!empty($info)) { ?>
		<?php echo $info; ?>
	<?php }else{ ?>
		<div id="msg"></div>
		<table id="content" border="0" width="100%"></table>
	<?php } ?>
    </div>
</body>
</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Twip Test Page</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	var url= 'statuses/friends_timeline.json';
	var params = {
		count: 5
	};

	$('#msg').ajaxStart(function() {
		$(this).empty();
		$(this).append("loading...");
		$(this).css("color","blue");
	});

	$.getJSON(url, params, function (json) {
		var content_inner_html='';
		$.each(json, function(index, status) {
			content_inner_html+='<tr><td>'+(index+1)+'</td><td>'+status.text+'</td><td>'+status.user.screen_name+'</td></tr>';
		});
		
		$('#content').empty();
		$('#content').append(content_inner_html);
		
		$('#msg').empty();
		$('#msg').append("ok!");
		$('#msg').css("color","green");
	});

	$('#msg').ajaxError(function() {
		$(this).empty();
		$(this).append("some error occurs"); 
		$(this).css("color","red");
	});
});

</script>
</head>
<body>
<h1>Twip Test Page</h1>
<p>Get the latest 5 statuses in your friend time line</p>
<p>Input your twitter username and password in the popup dialog</p>
<div id="msg" style="font: bold;"></div>
<table id="content" border="1"></table>
</body>
</html>

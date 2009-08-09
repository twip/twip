<?php
	require('config.php');
	$requesturl = substr( $_SERVER['REQUEST_URI'] , strlen($webroot) );
?>
<?php
	if(!isset($debug) || $debug != true){
?>
<html>
<head>
<title>twip debug page</title>
</head>
<body>
<h1>twip debug page</h1>
<p>
Since this page will leak some server infomation, if you want to see debug info , you should set $debug = true in your config.php
</p>
</body>
</html>
<?php
	}
	else {
?>
<html>
<head>
<title>twip debug page</title>
</head>
<body>
<h1>twip debug page</h1>
<table>
<tr>
<td>DOCUMENT_ROOT</td>
<td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
</tr>
<tr>
<td>webroot</td>
<td><?php echo $webroot; ?></td>
</tr>
<tr>
<td>__FILE__</td>
<td><?php echo __FILE__; ?></td>
</tr>
<tr>
<td>REQUEST_URI</td>
<td><?php echo $_SERVER['REQUEST_URI']; ?></td>
</tr>
<tr>
<td>requesturl</td>
<td><?php echo  $requesturl ?></td>
</tr>
</table>
</body>
</html>
<?
	}
?>


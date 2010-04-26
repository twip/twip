<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Save OAuth token</title>
		<script type="text/javascript">
		function check(){
			var pass = document.getElementById("password");
			var conf = document.getElementById("cofrpass");
			if(pass.value == "" || conf.value == "")
			{
				alert("Please enter the password.");
				pass.focus();
				return false;
			}

			if(pass.value != conf.value)
			{
				alert("Passwords do not match.");
				pass.focus();
				return false;
			}

			return true;
		}
		</script>
	</head>
	<body>
		<h2>Save your OAuth token.</h2>
		<p>This password is used to access your access token,which should be used in your application for authorization.
		<form name="form" id="form" method="POST" action="./savetoken.php" onsubmit="return check()">
			Password:<br />
			<input type="password" name="password" id="password" size="30" value="" /><br />
			Confirm password:<br />
			<input type="password" name="cofrpass" id="cofrpass" size="30" value="" />
			<input type="submit" name="save" value="Save" />
		</form>
	</body>
</html>
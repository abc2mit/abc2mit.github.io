<?php
if ($_POST['uname'] != "") {
	#$password = getPassword($_POST['uname']);
	$userdata = getUserData($_POST['uname']);
	$password = $userdata['password'];
	if ($password == $_POST['pass']) {
		$expire=time()+60*60;
		setcookie("name", $_POST['uname'], $expire);
		setcookie("gid", $userdata['gid'], $expire);
		#echo "cookie set for: [" . $_COOKIE['name'] . "]<br/>";
	}
	else {
		$login_failed = true;
	}
}

if ($_GET['logout']) {
	setcookie("name","", time()-3600);
	setcookie("gid","", time()-3600);
}

/*if ($_COOKIE['name'] != "") {
		echo "cookie set for: [" . $_COOKIE['name'] . "]<br/>";
}*/

function getUserData ($username) {
	try {
		$userarray = array();
		#echo "username: $username<br/>";
		$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_mj', 'mj060708');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $db->prepare("SELECT group_id FROM users WHERE name=?");
		$stmt->bindParam(1, $username);
		$completed = $stmt->execute();
		$array = $stmt->fetch(PDO::FETCH_BOTH);
		#echo "group id: " . $array['group_id'] . "<br/>";
		$userarray['gid'] = $array['group_id'];
		$stmt = $db->prepare("SELECT password FROM groups WHERE id=?");
		$stmt->bindParam(1, $array['group_id']);
		$completed = $stmt->execute();
		$array = $stmt->fetch(PDO::FETCH_BOTH);
		#echo "password: " . $array['password'] . "<br/>";
		$userarray['password'] = $array['password'];
		#return $array['password'];
		return $userarray;
	}
	catch (PDOException $e) {
		echo $e->getMessage();
	}
	return false;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>mike and jess : RSVP</title>
<link rel='stylesheet' type='text/css' href='css/main.css' />
<script type="text/javascript" src="js/browsercheck.js"></script>
<script type="text/javascript" src="js/applycss.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
if ($login_failed) {
	echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1; URL=/rsvp.php?error=true\"/>";
}
else if ($_GET['logout'] || $_SERVER['REQUEST_METHOD'] === 'POST') {
	echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1; URL=/rsvp.php\"/>";
}
?>
</head>
<body>
<div id="main">
<?php
    include('verse.php');
?>
<div id="background">
<div id="banner">
    <b class="b1t"></b><b class="b2t"></b><b class="b3t"></b><b class="b4t"></b>
    <div class="contentt">
<?php
include('menu/menu.php');
?>
    </div>
</div>
<div id="text">
    <div class="contentf">
<?php
if ($_COOKIE['name'] != '') {
	echo "<div id=\"logout\"><a href=\"/rsvp.php?logout=true\">logout</a></div>";
	echo "<div id=\"welcome\">Welcome, " . $_COOKIE['name'] . "!</div>";
	
	if ($_GET['update'] == 'success') {
		echo "<div id=\"success\">Your information has been updated.</div>\n";
	}
	
	// retrieve the contents of the registration
	$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_mj', 'mj060708');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->prepare("SELECT id, name, rsvp FROM users WHERE group_id=?");
	$stmt->bindParam(1, $_COOKIE['gid']);
	$completed = $stmt->execute();
	$i = 0;
	echo "<form action=\"modifyrsvp.php\" method=\"POST\" id=\"datainput\">\n";
	echo "<fieldset>\n<legend>Guests in your party who are attending.</legend>\n";
	echo "<div class=\"instructions\">Please check off any guests who will be attending the wedding. If any guest cannot come, please leave the box unchecked.</div>\n";
	echo "<table>\n";
	while ($row = $stmt->fetch()) {
		echo "<tr>";
		/*echo "<td><input type=\"checkbox\" name=\"rsvp_" . $row['id'] . "\"";
		if ($row['rsvp']) {
			echo "checked";
		}
		echo "></td>";*/
		echo "<td class=\"name\">" . $row['name'] . "</td><td><input type=\"radio\" id=\"yes\" name=\"rsvp_" . $row['id'] . "\" value=\"1\" ";
		if ($row['rsvp'] == "1") {
			echo "checked";
		}
		echo "/><label for=\"yes\">yes</label></td>";
		echo "<td><input type=\"radio\" id=\"no\" name=\"rsvp_" . $row['id'] . "\" value=\"-1\" ";
		if ($row['rsvp'] == "-1") {
			echo "checked";
		}
		echo "/><label for=\"no\">no</label></td>";
		echo "<td><input type=\"radio\" id=\"maybe\" name=\"rsvp_" . $row['id'] . "\" value=\"0\" ";
		if ($row['rsvp'] == "0") {
			echo "checked";
		}
		echo "/><label for=\"maybe\">maybe</label></td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</fieldset>\n";
	
	// address in database
	$stmt = $db->prepare("SELECT street, city, state, zip, accommodations FROM groups WHERE id=?");
	$stmt->bindParam(1, $_COOKIE['gid']);
	$completed = $stmt->execute();
	echo "<fieldset>\n<legend>Invitation Mailing Address</legend>\n";
	echo "<table><tr><td colspan=\"3\">\n";
	echo "<label for=\"street\">street</label><br/><input name=\"street\" type=\"text\" size=\"30\"";
	$data = $stmt->fetch();
	if ($data['street'] != "") {
		echo "value=\"" . $data['street'] . "\"";
	}
	echo "/></td></tr></table>\n";
	echo "<table><tr><td>";
	echo "<label for=\"city\">city</label><br/><input name=\"city\" type=\"text\" size=\"15\"";
	if ($data['city'] != "") {
		echo "value=\"" . $data['city'] . "\"";
	}
	echo "/>";
	echo "</td><td>";
	echo "<label for=\"state\">state</label><br/><input name=\"state\" type=\"text\" size=\"2\" ";
	if ($data['state'] != "") {
		echo "value=\"" . $data['state'] . "\"";
	}
	echo "/>";
	echo "</td><td>\n";
	echo "<label for=\"zip\">zip</label><br/><input name=\"zip\" type=\"text\" size=\"5\" ";
	if ($data['zip'] != "0") {
		echo "value=\"" . $data['zip'] . "\"";
	}
	echo "/></td></tr>\n";
	echo "</table>\n";
	echo "</fieldset>\n";
	
	// accommodations preferences
	echo "<fieldset>\n<legend>Accommodation Preference</legend>\n";
	echo "<div class=\"instructions\">Please select your preference for accommodations. This will help us to get group discounts.</div>\n";
	echo "<input type=\"radio\" name=\"a\" value=\"hotel\" ";
	if ($data['accommodations'] == "hotel") {
		echo "checked";
	}
	echo ">Hotel<br/>\n";
	echo "<input type=\"radio\" name=\"a\" value=\"beachhouse\" ";
	if ($data['accommodations'] == "beachhouse") {
		echo "checked";
	}
	echo ">Beach House<br/>\n";
	echo "<input type=\"radio\" name=\"a\" value=\"other\" ";
	if ($data['accommodations'] != "hotel" && $data['accommodations'] != "beachhouse" && $data['accommodations'] != "" ) {
		echo "checked";
		$value = $data['accommodations'];
	}
	echo ">Other ";
	echo "<input type=\"text\" name=\"other_accom\" value=\"$value\" />\n";
	echo "</fieldset>\n";
	
	echo "<div id=\"submit\"><input type=\"submit\" value=\"Submit\"/></div>\n";
	echo "</form>\n";
}
else if ($_GET['logout']) {
	echo "logging out";
}
else {
	if ($_GET['error']) {
		echo "Login and/or password incorrect. Please try again!";
	}
?>
	<form name="login" action="/rsvp.php" method="post">
    	<fieldset>
    		<legend>Please login.</legend>
    		<table>
    			<tr>
    			<td><label for="uname">Name:</label></td>
    			<td><input name="uname" type="text"/></td>
    			</tr>
    			<tr>
    			<td><label for="pass">Password:</label></td>
	    		<td><input name="pass" type="password"/></td>
	    		</tr>
	    	</table>
	    	<input type="submit" value="Submit">
    	</fieldset>
    </form>
<?php
}
?>
    	
		<div id="copyright">
		&copy; 2008 Michael Ho and Jessica Fung
		</div>
	</div> <!-- contentf -->
<b class="b4f"></b><b class="b3f"></b><b class="b2f"></b><b class="b1f"></b>
</div> <!-- text -->
</div> <!-- background -->
</div>

</body>
</html>

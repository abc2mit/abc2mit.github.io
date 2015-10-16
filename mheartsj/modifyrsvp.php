<?php
$success = false;
if ($_POST['street'] != "" || $_POST['city'] != "" || $_POST['state'] != "" || $_POST['zip'] != "") {
	$street = $_POST['street'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	// save the data
	$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_updater', 'mj051109');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->prepare("UPDATE groups SET street=?, city=?, state=?, zip=? WHERE id=?");
	$stmt->bindParam(1, $street);
	$stmt->bindParam(2, $city);
	$stmt->bindParam(3, $state);
	$stmt->bindParam(4, $zip);
	$stmt->bindParam(5, $_COOKIE['gid']);
	$completed = $stmt->execute();
	$success = true;
}

$success = false;
if ($_POST['a']) {
	#echo "accommodations = " . $_POST['a'] . "<br/>\n";
	$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_updater', 'mj051109');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->prepare("UPDATE groups SET accommodations=? WHERE id=?");
	$a = $_POST['a'];
	if ($a == "other") {
		$a = $_POST['other_accom'];
	}
	$stmt->bindParam(1, $a);
	$stmt->bindParam(2, $_COOKIE['gid']);
	$completed = $stmt->execute();
	$success = true;
}

$success = false;
// cookie should be set, so lookup the user
if (isset($_COOKIE['gid'])) {
	$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_updater', 'mj051109');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->prepare("SELECT id, rsvp FROM users WHERE group_id=?");
	$stmt->bindParam(1, $_COOKIE['gid']);
	$completed = $stmt->execute();
	while ($row = $stmt->fetch()) {
		$datafield = 'rsvp_' . $row['id'];
		$param = $_POST[$datafield];
		#$param = ($_POST[$datafield] == "on") ? true : false;
		#echo "datafield ["  . $datafield . "] = {" . $_POST[$datafield] . "/" . $param . "}<br/>\n";
		#echo "current = {" . $row['rsvp'] . "}<br/>\n";
		$param = $_POST[$datafield];
		if ($param != $row['rsvp']) {
			#echo "datafield changed.<br/>\n";
			$stmt2 = $db->prepare("UPDATE users SET rsvp=? WHERE id=?");
			$stmt2->bindParam(1, $param);
			$stmt2->bindParam(2, $row['id']);
			$completed = $stmt2->execute();
		}
	}
	$success = true;
}

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($success) {
		echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1; URL=/rsvp.php?update=success\"/>";
	}
	else {
		echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1; URL=/rsvp.php\"/>";
	}
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
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	echo "You need to go to the <a href=\"/rsvp.php\">RSVP page</a> to RSVP.";
}
else if ($_COOKIE['name'] != '') {
	#echo "<div id=\"logout\"><a href=\"/rsvp.php?logout=true\">logout</a></div>";
	#echo "Welcome, " . $_COOKIE['name'] . "!<br/>";
	
	#echo "[" . $street . "]<br/>\n";
	#echo "[" . $city . "]<br/>\n";
	#echo "[" . $state . "]<br/>\n";
	#echo "[" . $zip . "]<br/>\n";
	echo "Updating RSVP... The changes will be shown in just a second!<br/>\n";
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

<html>
<head>
<title>Adding person to the database...</title>
</head>
<body>
<?php
	include('db.php');
	$uname = $_POST['uname'];
	$pass = $_POST['pass'];
	$gid = $_POST['gid'];
	$apass = $_POST['apass'];
	$email = $_POST['email'];
	$id = $gid;
	$success = false;
	if ($apass == 'mh3artsj') {
		echo "inserting $uname with $pass and group id: ";
		if ($gid != -1) {
			echo $gid;
		}
		else {
			try {
				$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_updater', 'mj051109');
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$stmt = $db->prepare("INSERT INTO groups SET email=?, password=?, name=?");
				$stmt->bindParam(1, $email);
				$stmt->bindParam(2, $pass);
				$stmt->bindParam(3, $uname);
				$completed = $stmt->execute();
			}
			catch (PDOException $e) {
				echo $e->getMessage();
			}
			if ($completed) {
				connectDB('michaeq6_mheartsj');
				$query = "SELECT id FROM groups WHERE email='" . $email . "'";
				$result = mysql_query($query);
				$array = mysql_fetch_array($result, MYSQL_BOTH);
				$id = $array['id'];
				echo $id;
				echo "<br/>new group created.<br/>";
				$success = true;
			}
			else {
				echo "<br/>error creating group.<br/>";
			}
		}
		try {
			// TODO: check to see if user already exists in the database.
			$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_updater', 'mj051109');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			#$stmt = $db->prepare("INSERT INTO users (name, password, group_id) VALUES (':username', ':password', ':group')");
			$stmt = $db->prepare("INSERT INTO users SET name=?, password=?, group_id=?");
			$stmt->bindParam(1, $uname);
			$stmt->bindParam(2, md5($pass));
			$stmt->bindParam(3, $id);
			$completed = $stmt->execute();
		}
		catch (PDOException $e) {
			echo $e->getMessage();
		}
		if ($completed) {
			echo "<br/>data successfully added.<br/>";
			$success = true;
		}
		else {
			echo "<br/>error executing statement.<br/>";
			$success = false;
		}
		/*
		$mysqli = new mysqli("localhost", "michaeq6_mj", "mj060708", "michaeq6_mheartsj");
		$stmt = $mysqli -> prepare("INSERT INTO users (name, password, group_id) VALUES (?, ?, ?)");
		$stmt -> bind_param("ssi", $uname, $pass, $id);
		$stmt -> execute();
		*/
	}
	else {
		echo "invalid administrator's password. will not do anything.";
	}

?>
<br/>
<?php
	if ($success) {
		echo "<a href=\"admin.php\">back</a>";
	}
	else {
		echo "<a href=\"javascript:history.go(-1);\">back</a>";
	}
?>
</body>
</html>

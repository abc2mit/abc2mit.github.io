<html>
<head>
<title>Emailing Group</title>
<link rel='stylesheet' type='text/css' href='css/main.css' />
<link rel='stylesheet' type='text/css' href='css/email.css' />
</head>
<body>
<!--<a href="javascript:history.go(-1);">back</a>-->
<a href="admin.php">back</a>
<br/><br/><br/>
<?php
	$apass = $_POST['apass'];
	$gidlist = $_POST['gid'];
	$admin_password = 'mh3artsj';
	echo "There are " . count($gidlist) . " emails to send out<br/>\n";
	include('db.php');
	connectDB('michaeq6_mheartsj');
	$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_updater', 'mj051109');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	foreach ( $gidlist as $gid )
	{
		if ($apass == $admin_password && $gid != -1) {
			$subject = "RSVP for the wedding of Mike and Jess!";
			$message = "Dear ";
			// retrieve the email
			$query = "SELECT name, email, password FROM groups WHERE id='" . $gid . "'";
			$result = mysql_query($query);
			$array = mysql_fetch_array($result, MYSQL_BOTH);
			$email = $array['email'];
			$password = $array['password'];
			$gname = $array['name'];
			
			// retrieve members (users) of that group
			$query = "SELECT name FROM users WHERE group_id='" . $gid . "'";
			$result = mysql_query($query);
			$starting = true;
			while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
				if ($starting) {
					$message .= $row['name'] . ",\n\n";
					# 2009/03/20 email updated to remind people to RSVP
					#$message .= "You are cordially invited to our wedding! We hope you enjoyed our printed invitation as much as we loved creating it. If you haven't received a printed invitation, please let us know and we will make sure you get one!\n\n";
					$message .= "This email is for the following guests:\n";
					$message .= " - " . $row['name'];
					$starting = false;
				}
				else {
					$message .= "\n - " . $row['name'];
				}
			}
			# 2009/03/20 email updated to remind people to RSVP
			$message .= "\n\nAccording to our records, you haven't RSVP'ed with us! We're only inviting 75 people to our wedding, so you are one of our cherished guests! We would appreciate your response by March 30, 2009. Please let us know if you can make it to our special day! We would love to have you join us in Hawaii.\n\nUpdates are available on our website. If you need assistance with transportation or housing, please let us know and we'd be happy to help out! Currently, we still have a few spots left in a beach house with several other guests and the rate is as low as $25 for certain nights.";
			# end: 2009/03/20 email updated to remind people to RSVP
			$message .= "\n\nYour RSVP password is: $password\n";
			$message .= "Please use it to log in at http://www.mheartsj.com/rsvp.php and RSVP!\n\n";
			$message .= "Login instructions:\n";
			$message .= "NAME: Any name that is shown above.\n";
			$message .= "PASSWORD: The RSVP password in this email.\n";
			$message .= "If you have any questions, please do not hesitate to email us back.\n\n";
			$message .= "Many thanks!\nMike and Jess";
			$address = $email;
			if ($gname != null) {
				$address = $gname . "<" . $email . ">";
			}
			mail($address, $subject, $message, 'From: "Mike and Jess" <mheartsj09@gmail.com>');
			echo "email sent to <b class=\"emailaddress\">$email</b>.<br/>\n";
			echo "<div class=\"email\">\n";
			echo "<div class=\"subject\">Subject: $subject</div>\n";
			echo "<div class=\"message\">" . str_replace("\n", "<br/>", $message) . "</div>";
			echo "</div>\n";
			// log the fact that the person was emailed
			$stmt = $db->prepare("UPDATE groups SET emailed='1' WHERE id=?");
			$stmt->bindParam(1, $gid);
			$stmt->execute();
		}
		else if ($apass != $admin_password) {
			echo "incorrect administrator's password.<br/>";
		}
		else {
			echo "No group selected.<br/>";
		}
	}
?>
<br/><br/><br/>
<!--<a href="javascript:history.go(-1);">back</a>-->
<a href="admin.php">back</a>
</body>
</html>

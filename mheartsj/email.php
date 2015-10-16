<?php
	# functions
	
	# 2009/03/20: I tried to create this function to simplify the main PHP code, but this is not very efficiently used. The main code retrieves each user
	function generateAndSendEmail ($gid, $gname, $email, $password, $db, $version) {
		// version 0: initial email
		// version 1 (2009/03/20): reminder to RSVP by March 30, 2009
		// version 2 (2009/04/11): itinerary
		// version 3 (2009/04/11): dress code
		// version 4 (2009/04/24): dress code clarification
		$email_version = intval($version);
		$subject = "RSVP for the wedding of Mike and Jess!";
		$lf = "\n";
		if ($email_version >= 2) {
			$subject = "MJ Wedding Announcement";
			$lf = "<br/>";
		}
		switch ($email_version) {
			case 2:
				$body = "<html><head><title>MJ Wedding Itinerary</title></head><body>";
				break;
			case 3:
			case 4:
				$body = "<html><head><title>MJ Wedding Dress Code</title></head><body>";
				break;
		}
		
		$message = "Dear ";
		// retrieve members (users) of that group
		$query = "SELECT name FROM users WHERE group_id='" . $gid . "'";
		$result = mysql_query($query);
		$starting = true;
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
			if ($starting) {
				$message .= $row['name'] . "," . $lf . $lf;
				if ($email_version == 0) {
					$message .= "You are cordially invited to our wedding! We hope you enjoyed our printed invitation as much as we loved creating it. If you haven't received a printed invitation, please let us know and we will make sure you get one!" . $lf . $lf;
				}
				$message .= "This email is for the following guests:" . $lf;
				$message .= " - " . $row['name'];
				$starting = false;
			}
			else {
				$message .= $lf . " - " . $row['name'];
			}
		}
		if ($email_version > 0) {
			$message .= $lf . $lf;
		}
		switch ($email_version) {
			case 1:
				$message .= "According to our records, you haven't RSVP'ed with us! We're only inviting 75 people to our wedding, so you are one of our cherished guests! We would appreciate your response by March 30, 2009. Please let us know if you can make it to our special day! We would love to have you join us in Hawaii." . $lf . $lf . "Updates are available on our website. If you need assistance with transportation or housing, please let us know and we'd be happy to help out! Currently, we still have a few spots left in a beach house with several other guests and the rate is as low as $25 for certain nights.";
				break;
			case 2:
				$message .= "Hawaii is only a month away!!!!!! Yes, time for you to get away for a nice, sunny vacation... and time for us to get MARRIED!!!!!!!" . $lf . $lf . "Some things to look forward to as you are working/studying!" . $lf . "During our time on the Island, there are certain wedding matters that we need to take care of, but here are some of our free times that we would invite you to join us if you choose to..." . $lf . $lf . "-Jess" . $lf . $lf . $lf . "<b>5/7 Thurs</b>" . $lf . "<i>Night</i>" . $lf . "-Dinner and relax, feel free to join us." . $lf . $lf . "<b>5/8 Fri</b>" . $lf . "<i>Night</i>" . $lf . "-Bachelorette Party at night! Open to all guests, but ladies only!" . $lf . " **interested? contact Matron of Honor Tiff @ zhu.tiff@gmail.com" . $lf . "-The fellows should be doing something as well, open to all guests, but men only!" . $lf . " **interested? contact Best Man Jason @ jasongu@andrew.cmu.edu" . $lf . $lf . "<b>5/9 Sat</b>" . $lf . "<i>Free day except for Rehearsal</i>" . $lf . "-Free time for everyone! Some activities will be thought of if you are interested in playing with the group." . $lf . " **interested? Contact Jen jenpwu@gmail.com and Jason jasongu@andrew.cmu.edu together." . $lf . "<i>Evening</i>" . $lf . "-Mandatory Rehearsal for Bride and Groom immediate families, Uncle Larry, and Bridal Party" . $lf . "-A Luau for all is in the plan, Jason is looking into it, hopefully all guests can come!" . $lf . $lf . "<b>5/10 Sun</b>" . $lf . "church and groomsmen/bridesmaids only events" . $lf . $lf . "<b>5/11 Mon</b> :)" . $lf . "<i>3:30pm</i>" . $lf . " -pre-ceremony refreshments" . $lf . "<i>4:00pm</i>" . $lf . " -DUN dun dun dun dun dun dun dun (the wedding ceremony instrumental)" . $lf . "---Jess will be a very happy and blessed woman!!! :D" . $lf . "<i>until 9:30pm</i>" . $lf . " -Dining and dancing under the starry sky..." . $lf . $lf . "";
				break;
			case 3:
				$message .= "Wanted to give you an update on the dress code! Our dress code is categorized as \"smart casual\". This means:" . $lf . $lf ."<b>Men</b> - Dress Shirt, Dress Pants or Khakis (no shorts). Flip flops or shoes." . $lf . "<b>Women</b> - \"Sunday dress\"" . $lf . $lf . "Contact us if you need clarification! ";
				break;
			case 4:
				$message .= "There's a clarification on the dress code for the men! Our dress code is categorized as \"smart casual\". This means:" . $lf . $lf ."<b>Men</b> - <i>Long-Sleeved</i> Dress Shirt, Dress Pants or Khakis (no shorts). Flip flops or shoes." . $lf . "<b>Women</b> - \"Sunday dress\"" . $lf . $lf . "Contact us if you need clarification! ";
				break;
		}
		if ($email_version < 2) {
			$message .= $lf . $lf . "Your RSVP password is: $password" . $lf;
			$message .= "Please use it to log in at http://www.mheartsj.com/rsvp.php and RSVP!" . $lf . $lf;
			$message .= "Login instructions:" . $lf;
			$message .= "NAME: Any name that is shown above." . $lf;
			$message .= "PASSWORD: The RSVP password in this email." . $lf;
		}
		$message .= "If you have any questions, please do not hesitate to email us back." . $lf . $lf;
		$message .= "Many thanks!" . $lf . "Mike and Jess";
		$headers = 'From: "Mike and Jess" <mheartsj09@gmail.com>';
		// set the body to include the message
		$body .= $message;
		if ($email_version >= 2) {
			$body .= "</body></html>";
			$headers .= "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n";
		}
		$address = $email;
		if ($gname != null) {
			$address = $gname . "<" . $email . ">";
		}
		mail($address, $subject, $body, $headers);
		echo "email sent to <b class=\"emailaddress\">$email</b>." . $lf . "\n";
		echo "<div class=\"email\">\n";
		echo "<div class=\"subject\">Subject: $subject</div>\n";
		echo "<div class=\"message\">" . str_replace("\n", "<br/>", $message) . "</div>";
		echo "</div>\n";
		// log the fact that the person was emailed
		$stmt = $db->prepare("UPDATE groups SET emailed='1' WHERE id=?");
		$stmt->bindParam(1, $gid);
		$stmt->execute();
	}
?>

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
	$version = $_POST['version'];
	if ($gidlist[0] != "-1") {
		echo "There are " . count($gidlist) . " emails to send out.<br/>\n";
	}
	include('db.php');
	connectDB('michaeq6_mheartsj');
	$db = new PDO('mysql:dbname=michaeq6_mheartsj', 'michaeq6_updater', 'mj051109');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	foreach ( $gidlist as $gid )
	{
		if ($apass == $admin_password) {
			// send to all groups that have not yet RSVP'ed
			// modified 2009/04/11
			if ($gid == -1) {
				$sent_list = array();
				//$group_query = "SELECT group_id FROM users WHERE rsvp='0'";
				$group_query = "SELECT group_id FROM users WHERE rsvp='1'";
				$group_result = mysql_query($group_query);
				$count = 0;
				while ($row = mysql_fetch_array($group_result, MYSQL_BOTH)) {
					$gid = $row['group_id'];
					if (!in_array($gid, $sent_list)) {
						// retrieve the email
						$query = "SELECT name, email, password FROM groups WHERE id='" . $gid . "'";
						$result = mysql_query($query);
						$array = mysql_fetch_array($result, MYSQL_BOTH);
						generateAndSendEmail($gid, $array['name'], $array['email'], $array['password'], $db, $version);
						$count++;
						$sent_list[] = $gid;
					}
				}
				echo "<br/>Sent out $count emails.<br/>";
				// exit loop because $gid == -1 should be the only selection
				break;
			}
			// retrieve the email
			$query = "SELECT name, email, password FROM groups WHERE id='" . $gid . "'";
			$result = mysql_query($query);
			$array = mysql_fetch_array($result, MYSQL_BOTH);
			$email = $array['email'];
			$password = $array['password'];
			$gname = $array['name'];
			
			// send to specific group selected
			generateAndSendEmail($gid, $gname, $email, $password, $db, $version);
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

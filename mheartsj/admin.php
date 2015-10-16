<?php
$realm = 'Restricted area';

//user => password
$users = array('admin' => 'm05h3artsj11');

if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

    die('Sorry! You need to authenticate to view this page.');
}

// analyze the PHP_AUTH_DIGEST variable
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($users[$data['username']])) {
	$_SERVER['PHP_AUTH_DIGEST'] = "";
    die('Not a valid user!');
}

// generate the valid response
$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

if ($data['response'] != $valid_response)
    die('Wrong Credentials!');

// ok, valid username & password
echo 'Your are logged in as: ' . $data['username'];

// function to parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();

    preg_match_all('@(\w+)=(?:([\'"])([^$2]+)$2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
   
    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? trim($m[3],"\",'") : trim($m[4],"\",'");
        unset($needed_parts[$m[1]]);
    }
   
    return $needed_parts ? false : $data;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>mike and jess : administrator page</title>
<link rel='stylesheet' type='text/css' href='css/main.css' />
<script type="text/javascript" src="js/browsercheck.js"></script>
<script type="text/javascript" src="js/applycss.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
    	<fieldset>
    		<legend> Guest Count </legend>
    		<table>
    		<?php
    			$query = "SELECT count(*) FROM users WHERE rsvp='1'";
    			$result = mysql_query($query);
    			$answer = mysql_fetch_array($result, MYSQL_NUM);
    			$coming = $answer[0];
    			echo "<tr><td><b>Coming:</b></td><td class=\"number\">" . $coming . "</td></tr>";
    			
    			$query = "SELECT count(*) FROM users WHERE rsvp='-1'";
    			$result = mysql_query($query);
    			$answer = mysql_fetch_array($result, MYSQL_NUM);
    			$notcoming = $answer[0];
    			echo "<tr><td><b>Not Coming:</b></td><td class=\"number\">" . $notcoming . "</td></tr>";
    			
    			$query = "SELECT count(*) FROM users WHERE rsvp='0'";
    			$result = mysql_query($query);
    			$answer = mysql_fetch_array($result, MYSQL_NUM);
    			$undecided = $answer[0];
    			echo "<tr><td><b>Undecided:</b></td><td class=\"number\">" . $undecided . "</td></tr>";
    			
    			$total = $coming + $notcoming + $undecided;
    			echo "<tr id=\"total\"><td><b>Total:</b></td><td class=\"number\">" . $total . "</td></tr>";
    		?>
    		</table>
    		<a href="/admindetails.php">details</a>
    	</fieldset>
    
    	<fieldset>
    		<legend>Add user.</legend>
    		<form method="post" action="addperson.php">
    		<table>
    			<tr>
	    		<td><label for="uname">Name</label></td>
	    		<td><input type="text" name="uname" value="" /></td>
	    		</tr>
    			<tr>
    			<td><label for="gid">Group ID:</label></td>
	    		<td>
	    			<select name="gid">
	    			<option value="-1" selected="true">New Group</option>
	    			<?php
	    				#connectDB('digressi_mheartsj');
	    				$query = "SELECT id FROM groups ORDER BY id DESC";
	    				$result = mysql_query($query);
	    				while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
	    					$id = $row['id'];
	    					echo "<option value=\"$id\">$id";
	    					$userquery = "SELECT name FROM users WHERE group_id='" . $id . "'";
	    					$userresult = mysql_query($userquery);
	    					$starting = true;
	    					while ($urow = mysql_fetch_array($userresult, MYSQL_BOTH)) {
	    						if ($starting) {
	    							echo ": ";
	    							$starting = false;
	    						}
	    						else {
	    							echo ", ";
	    						}
	    						echo $urow['name'];
	    					}
	    					echo "</option>\n";
	    				}
	    			?>
	    			</select>
	    		</td>
	    		</tr>
    			<tr>
    			<td><label for="email">New Group Email:</label></td>
	    		<td><input name="email" type="text"/></td>
	    		</tr>
				<tr>
				<td><label for="pass">Group Password:</label></td>
	    		<td><input name="pass" type="text"/></td>
	    		</tr>
    			<tr>
    			<td><label for="apass">Admin Password:</label></td>
	    		<td><input name="apass" type="password"/></td>
	    		</tr>
    		</table>
    		<input type="submit" value="Submit"/>
    		</form>
    	</fieldset>
    	<fieldset>
    		<legend>Email group.</legend>
    		<form method="post" action="email.php"/>
    		<table>
    		<tr><td>Group ID:</td><td>
    		<select name="gid[]" multiple size=20>
			<?php
				#connectDB('digressi_mheartsj');
				$query = "SELECT id, emailed FROM groups ORDER BY id DESC";
				$result = mysql_query($query);
				$selected = false;
				while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
					$id = $row['id'];
					echo "<option value=\"$id\"";
					if ($row['emailed'] == "0") {
						echo "selected=\"true\"";
						$selected = true;
					}
					echo ">$id";
					$userquery = "SELECT name FROM users WHERE group_id='" . $id . "'";
					$userresult = mysql_query($userquery);
					$starting = true;
					while ($urow = mysql_fetch_array($userresult, MYSQL_BOTH)) {
						if ($starting) {
							echo ": ";
							$starting = false;
						}
						else {
							echo ", ";
						}
						echo $urow['name'];
					}
					echo "</option>\n";
				}
				if (!$selected) {
					echo "<option value=\"-1\" selected=\"true\">No Group Selected.</option>";
				}
			?>
			</select>
			</td></tr>
			<tr><td>Email Version:</td><td>
			<select name="version">
				<option value="0">Version 0</option>
				<option value="1" selected="true">Version 1 (2009/03/20)</option>
				<option value="2" selected="true">Version 2 (2009/04/11)</option>
				<option value="3" selected="true">Version 3 (2009/04/11)</option>
				<option value="4" selected="true">Version 4 (2009/04/24)</option>
			</select>
			</td></tr>
			<tr>
			<td><label for="apass">Admin Password:</label></td>
			<td><input name="apass" type="password"/></td>
			</tr>
			</table>
    		<input type="submit" value="Submit"/>
    		</form>
    	</fieldset>
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

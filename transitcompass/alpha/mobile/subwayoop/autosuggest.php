<?php

/*
note:
this is just a static test version using a hard-coded countries array.
normally you would be populating the array out of a database

the returned xml has the following structure
<results>
	<rs>foo</rs>
	<rs>bar</rs>
</results>
*/

	include('../subwaydb/db.php');
	connectDB($_GET['db']);
	
	
	$input = strtolower( $_GET['input'] );
	$len = strlen($input);
	$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 0;
	
	
	$aResults = array();
	$count = 0;
	
	if ($len)
	{
	   // retrieve from the DB
	   $query = "SELECT id, name, suggest FROM markers WHERE name LIKE '" . $input . "%'";
	   $results = mysql_query($query) or die("Attempt to retrieve station short-list failed. " . mysql_error());
	   while ($marker = mysql_fetch_array($results, MYSQL_BOTH)) {
	       $count++;
	       $conn_query = "SELECT DISTINCT line, type FROM connections WHERE marker_id='" . $marker['id'] . "'";
	       $conn_results = mysql_query($conn_query) or die("Attempt to retrieve connection list failed. " . mysql_error());
	       $num_rows = mysql_num_rows($conn_results);
	       $i = 1;
	       $loc_suggestion = "(";
	       while ($connection = mysql_fetch_array($conn_results, MYSQL_BOTH)) {
	           if ($connection['type'] != "transfer") {
                   $loc_suggestion .= $connection['line'];
                   // allow commas to be inserted.
                   if ($i < $num_rows) {
                       $loc_suggestion .= ",";
                   }
	           }
               $i++;
	       }
	       $length = strlen($loc_suggestion);
	       if (strrpos($loc_suggestion, ",") == ($length - 1)) {
	           $loc_suggestion = substr($loc_suggestion, 0, ($length - 1));
	       }
		   $loc_suggestion .= ") " . $marker['suggest'];
	       $aResults[] = array("id"=>$marker['id'], "value"=>$marker['name'], "info"=>$loc_suggestion);
	       if ($limit && $count==$limit) {
	           break;
           }
	   }
	   
		/*for ($i=0;$i<count($aUsers);$i++)
		{
			// had to use utf_decode, here
			// not necessary if the results are coming from mysql
			//
			if (strtolower(substr(utf8_decode($aUsers[$i]),0,$len)) == $input)
			{
				$count++;
				$aResults[] = array( "id"=>($i+1) ,"value"=>htmlspecialchars($aUsers[$i]), "info"=>htmlspecialchars($aInfo[$i]) );
			}
			
			if ($limit && $count==$limit)
				break;
		}*/
	}
	
	header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header ("Pragma: no-cache"); // HTTP/1.0
		
	if (isset($_REQUEST['json']))
	{
		header("Content-Type: application/json");
	
		echo "{\"results\": [";
		$arr = array();
		for ($i=0;$i<count($aResults);$i++)
		{
			$arr[] = "{\"id\": \"".$aResults[$i]['id']."\", \"value\": \"".$aResults[$i]['value']."\", \"info\": \"\"}";
		}
		echo implode(", ", $arr);
		echo "]}";
	}
	else
	{
		header("Content-Type: text/xml");

		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?><results>";
		for ($i=0;$i<count($aResults);$i++)
		{
			echo "<rs id=\"".$aResults[$i]['id']."\" info=\"".$aResults[$i]['info']."\">".$aResults[$i]['value']."</rs>";
		}
		echo "</results>";
	}
?>
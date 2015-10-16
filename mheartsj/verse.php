<div id="verse">
<?php
    include('db.php');
    connectDB('digressi_mheartsj');
    $query = "SELECT count(*) FROM scripture";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $count = mysql_fetch_array($result, MYSQL_BOTH);
    $id = rand(1,$count[0]);
    #echo $id . "<br/>\n";
    $query = "SELECT * fROM scripture WHERE id='" . $id . "'";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $verse = mysql_fetch_array($result, MYSQL_BOTH);
    echo $verse['text'];
    echo "<div id=\"versetitle\"><i>" . $verse['book'] . " " . $verse['chapter'] . ":" . $verse['verses'] . "</i> (" . $verse['version'] . ")</div>";
?>
</div>

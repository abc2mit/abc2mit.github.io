<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorith are prohibited by law.
    #
    #   db.php
    #   This file contains all functions related to retrieving data from the DB.
    #
    
    /**
        Opens a connection to a database.
        
        @param $database<String> the name of the DB to connect to.
    */
    function connectDB ($database) {
        $link = mysql_connect("localhost", "digressi_mj", "mj060708") or die('Could not connect: ' . mysql_error());
        #echo "Connected successfully<br/>";
        mysql_select_db("$database") or die('Could not select database');
    }
?>

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
        $link = mysql_connect("localhost", "michaeq6_subway", "matthew1323") or die('Could not connect: ' . mysql_error());
        #echo "Connected successfully<br/>";
        mysql_select_db("$database") or die('Could not select database');
    }
    
    /**
        Looks in the incoming string for a borough. If it doesn't exist or the city
        does not exist, it will insert or append the city name.
        
        @param $address<String> the string used for search
        @param $city<String> the name of the city to add to the string if none is found.
    */
    function findAndAddBorough ($address, $city_name) {
        $debug = false;
        $query = 'SELECT * FROM boroughs';
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        //$found = false;
        while ($borough = mysql_fetch_array($result, MYSQL_BOTH)) {
            $borough_name = $borough['name'];
            if ($debug) {
                echo "borough: $borough_name<br/>";
            }
            if (stripos($address, $borough_name) !== false) {
                // address contains one of the boroughs, so we simply return
                // the string without modifying it
                return $address;
            }
        }
        if (stripos($address, $city_name) !== false) {
            // address contains the city, so we simply return
            // the string without modifying it
            return $address;
        }
        // none of the boroughs were found.
        if ($debug) {
            echo "old address = $address<br/>";
        }
        // see if there's a comma in the string
        // assumption: <number> <street>, <city/borough>, <state> <zipcode>
        $comma_count = substr_count($address, ",");
        if ($comma_count > 0) {
            // borough isn't found, so that means the comma defines the state
            $city_string = ", " . $city_name . ", ";
            $new_address = str_replace(",", $city_string, $address);
            if ($debug) {
                echo "new address = $new_address<br/>";
            }
            return $new_address;
        }
        $new_address = $address . ", " . $city_name;
        if ($debug) {
            echo "new address = $new_address<br/>";
        }
        return $new_address;
    }
    
    /**
        This function assumes that there's no zip code, so the state is appended to
        the end of the address string if none is found.
    */
    function findAndAddState ($address, $state_string) {
        $debug = false;
        $query = 'SELECT * FROM states';
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        //$found = false;
        while ($state = mysql_fetch_array($result, MYSQL_BOTH)) {
            $state_name = $state['name'];
            if ($debug) {
                echo "state: $state_name<br/>";
            }
            if (stripos($address, $state_name) !== false) {
                // address contains one of the boroughs, so we simply return
                // the string without modifying it
                return $address;
            }
        }
        if (stripos($address, $state_string) !== false) {
            // address contains the state, so we simply return
            // the string without modifying it
            return $address;
        }
        if ($debug) {
            echo "old address = $address<br/>";
        }
        // assumption: <number> <street>, <city/borough>, <state> <zipcode>
        // if we're here, there's no zipcode
        $new_address = $address . ", " . $state_string;        
        if ($debug) {
            echo "new address = $new_address<br/>";
        }
        return $new_address;
    }
    
    function getSlogan () {
        $query = 'SELECT count(*) FROM slogans';
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        $result_array = mysql_fetch_array($result, MYSQL_BOTH);
        $count = $result_array[0] - 1;
        $id = rand()%$count + 1;
        $query = "SELECT slogan FROM slogans WHERE id='" . $id . "'";
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        $slogan_array = mysql_fetch_array($result, MYSQL_BOTH);
        return $slogan_array['slogan'];        
    }
    
    function logDirectionsQuery ($start_address, $end_address) {
        $debug = false;
        if ($debug) {
            echo "logDirectionsQuery: [$start_address] to [$end_address]<br/>";
        }
        $query = "INSERT INTO directions_query (start_address,end_address,query_time) VALUES ('" . $start_address . "','" . $end_address . "',NOW())";
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    }
    
    function logLocationQuery ($location) {
        $debug = false;
        if ($debug) {
            echo "logLocationQuery: $location<br/>";
        }
        $query = "INSERT INTO location_query (location,query_time) VALUES ('" . $location . "',NOW())";
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    }
    
    function logS2SQuery ($start_station, $end_station) {
        $debug = false;
        if ($debug) {
            echo "logS2SQuery: [$start_station] to [$end_station]<br/>";
        }
        $query = "INSERT INTO s2s_query (start_station,end_station,query_time) VALUES ('" . $start_station . "','" . $end_station . "',NOW())";
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());    }
    
    /**
        Retrieves the markers from the DB and loads them into an array. It creates full
        Marker objects with Line and Connection objects.
        
        @return an array of Markers
    */
    function retrieveMarkers () {
        $debug = false;
        $markers = array();
        
        $query = 'SELECT * FROM markers';
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        
        while ($line = mysql_fetch_array($result, MYSQL_BOTH)) {
            $id = $line['id'];
            $lat = $line['lat'];
            $lng = $line['lng'];
            $name = $line['name'];
            $url = $line['url'];
            if ($debug) {
                echo "building marker: $name ($id)<br/>";
            }
            $marker = new Marker($lat, $lng, $id, $name);
            $marker->setURL($url);
            // grab all connections
            $conn_query = "SELECT * FROM connections WHERE id='" . $id . "'";
            $conn_result = mysql_query($conn_query) or die('Query failed: ' . mysql_error());
            $lines = array();
            $line_names = array();
            while ($conn = mysql_fetch_array($conn_result, MYSQL_BOTH)) {
                // grab all variables from DB
                $marker_id = $conn['marker_id'];
                $day = $conn['day'];
                $duration = $conn['duration'];
                $line = $conn['line'];
                $type = $conn['type'];
                $start = $conn['start'];
                $end = $conn['end'];
                if ($debug) {
                    echo "-&gt;found connection: $marker_id<br/>";
                }
                // create a new connection
                $connection = new Connection($marker_id, $type, $start, $end, $day, $duration);
                $marker_line = $lines["$line"];
                if (is_null($marker_line)) {
                    $marker_line = array();
                }
                $marker_line[] = $connection;
                // add it to an array. we need it later.
                if ($debug) {
                    echo "-&gt;found line: $line<br/>";
                }
                $lines["$line"] = $marker_line;
                if (!in_array($line, $line_names)) {
                    if ($debug) {
                        echo "adding $line to line_names<br/>";
                    }
                    $line_names[] = $line;
                }
            } // connection loop            
            if ($debug) {
                echo "line_names count: " . count($line_names) . "<br/>";
            }
            // retrieved all connections and set them in their appropriate lines.
            // now we need to create the lines
            foreach ($line_names as $line_name) {
                if ($debug) {
                    echo "-&gt;looking at line: $line_name<br/>";
                }
                // for each line, grab the data from the database
                $line_query = "SELECT * FROM `lines` WHERE name='" . $line_name . "'";
                $line_result = mysql_query($line_query) or die('Query failed: ' . mysql_error());
                // the line_name is the unique key for that table, so we only need to get the first one
                $line_array = mysql_fetch_array($line_result, MYSQL_BOTH);
                $lname = $line_array['name'];
                $lurl = $line_array['url'];
                $limg = $line_array['img'];
                $line = new Line($limg, $lurl, $lname);
                if ($debug) {
                    echo "--&gt;found line: " . $lname . "<br/>";
                }
                // now add all the connections
                $connections = $lines["$line_name"];
                foreach ($connections as $connection) {
                    // each one is a connection object from above. add it
                    $line->addConnection($connection);
                    if ($debug) {
                        echo "---&gt;adding " . $connection->id . " to $line_name<br/>";
                    }
                }
                // now add the line to the marker
                $marker->addLine($line);
                if ($debug) {
                    echo "--&gt;adding $line_name to $name<br/>";
                }
            } // line loop
            // the marker object is created and populated. add to the markers array.
            $markers["$id"] = $marker;
            if ($debug) {
                echo "adding $id to markers<br/>";
            }
        } // marker loop
        return $markers;
    }
?>
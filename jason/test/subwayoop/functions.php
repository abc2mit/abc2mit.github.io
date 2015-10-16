<?php
    // returns true if it is a valid zip code
    function isValidZipCode($value) {
        //$re = "/^\d{5}([-]\d{4})?$/";
        return preg_match("/^\\d{5}$/", $value);
    }
    
    // checks the string and adds the substring if it does not exist
    function checkString($loc, $substring) {
        if (stristr($loc, $substring) === false) {
            $loc .= ", $substring";
        }
        return $loc;
    }
    
    /*function correctOutput($location_string, $location, $num) {
        // geocoder leaves out intersections! we have to fill in with the query here
        if (substr($location->desc,0,1) == ",") {
            $search = $location_string . ", new york ny " . substr($location->desc, strlen($location->desc) - 5, 5);
            echo "[<a href=\"javascript:focusOn(new GLatLng($location->getLat(),$location->getLng()), map.getZoom());\">$num</a>] $search&nbsp;";
            echo "<input type=\"hidden\" name=\"l\" value=\"$search\" />\n";
        }
        else {
            // normal address lookup
            echo "[<a href=\"javascript:focusOn(new GLatLng($location->getLat(),$location->getLng()), map.getZoom());\">$num</a>] $location->desc&nbsp;";
            echo "<input type=\"hidden\" name=\"l\" value=\"$location->desc\" />\n";
        }
    }*/
    
    function listStations($selected_id, $markers) {
        foreach ($markers as $marker) {
            $line_string = "|";
            foreach ($marker->getLines() as $line) {
                foreach($line->getConnections() as $connection) {
                    if ($connection->type != "transfer") {
                        $line_string .= $line->name . "|";
                        break;
                    }
                }
            }
            if (isset($selected_id) && ($marker->id == $selected_id)) {
                echo "<OPTION VALUE=\"$marker->id\" SELECTED>$marker->name&nbsp;&nbsp;$line_string</OPTION>\n";
            }
            else {
                echo "<OPTION VALUE=\"$marker->id\">$marker->name&nbsp;&nbsp;$line_string</OPTION>\n";
            }
        }
    }
        
    /**
        This method displays the path instructions on the right side of the screen.
        
        @param $path<Path2> the path found by the algorithm
    */
    function printPath($path) {
        global $website;
        $debug = false;
        $count = count($path);
        if ($debug) {
            echo "path count = $count<br/>\n";
            print_r($path);
        }
        $old_lines = null;
        if ($count > 0) {
            // print consolidated path
            echo "<table border=\"1\">\n<thead>\n";
            echo "<tr><td style=\"text-align: center;padding: 0 2px 0 2px\"><b>Step</b></td>";
            echo "<td class=\"instruction\"><b>Instructions</b></td><td style=\"text-align: center;padding: 0 2px 0 2px\"><b>Time</b></td></tr>\n";
            echo "</thead>\n<tbody>\n";
            $old_lines = null;
            for ($x = 0; $x < $count; $x++) {
                $y = $x + 1;
                $segment = $path[$x];
                if ($debug) {
                    echo $segment . "<br/>";
                    echo "segment = " . $segment->toString() . "<br/>";
                }
                $marker = $segment->m1;
                $m1_name = $marker->name;
                $m1lat = $marker->getLat();
                $m1lng = $marker->getLng();
                $m2_name = $segment->m2->name;
                $connection = $segment->connection;
                if ($connection == "walking") {
                    echo "<tr><td style=\"text-align: center\"><a href=\"javascript:focusOn(new GLatLng($m1lat,$m1lng), map.getZoom());\">[ $y ]</a></td><td class=\"instruction\">Walk from <b>$m1_name</b> to <b>$m2_name</b>. ";
                    $rawTime = $segment->t;
                    $time_string = getTimeString($rawTime);
                    echo "</td><td style=\"text-align: center;padding:1px 2px 1px 2px\">$time_string</td></tr>\n";
                }
                else if ($connection == "transfer") {
                    $lines = $segment->lines;
                    $from_string = "";
                    $to_string = "";
                    for ($i = 0; $i < count($old_lines); $i++) {
                        //if (!in_array($old_lines[$i], $lines)) {
                            $from_string .= "<img width=\"20\" src=\"images/" . $old_lines[$i]->img . "\"/>";
                        //}
                    }
                    for ($i = 0; $i < count($lines); $i++) {
                        //if (!in_array($lines[$i], $old_lines)) {
                            $to_string .= "<img width=\"20\" src=\"images/" . $lines[$i]->img . "\"/>";
                        //}
                    }
                    echo "<tr><td style=\"text-align: center\"><a href=\"javascript:focusOn(new GLatLng($m1lat,$m1lng), map.getZoom());\">[ $y ]</a></td><td class=\"instruction\">Transfer from <b>$m1_name</b> $from_string to <b>$m2_name</b> $to_string";
                    $rawTime = $segment->t;
                    $time_string = getTimeString($rawTime);
                    echo "</td><td style=\"text-align: center;padding:1px 2px 1px 2px\">$time_string</td></tr>\n";
                }
                else {
                    echo "<tr><td style=\"text-align: center\"><a href=\"javascript:focusOn(new GLatLng($m1lat,$m1lng), map.getZoom());\">[ $y ]</a></td><td class=\"instruction\">Take ";
                    $lines = $segment->lines;
                    for ($i = 0; $i < count($lines); $i++) {
                        echo "<a href=\"$website" . $lines[$i]->url . "\"><img width=\"20\" src=\"images/" . $lines[$i]->img . "\"/></a>";
                    }
                    echo " from <b>$m1_name</b> to <b>$m2_name</b>. ";
                    $rawTime = $segment->t;
                    $time_string = getTimeString($rawTime);
                    echo "</td><td style=\"text-align: center;padding:1px 2px 1px 2px\">$time_string</td></tr>\n";
                    $old_lines = $lines;
                }
            }
            echo "</tbody>\n</table>\n";
        }
        else {
            echo "Sorry. No path found.<br/>\n";
        }
    }
    
    function getTimeString($rawTime) {
        $minutes = floor($rawTime/60);
        $seconds = round(($rawTime/60-$minutes)*60);
        if ($seconds == 0) {
            $seconds = "00";
        }
        else if ($seconds < 10) {
            $seconds = "0" . $seconds;
        }
        return "$minutes:$seconds";
    }

    function printAddress($loc, $addr, $marker_img) {
        // geocoder leaves out intersections! we have to fill in with the query here
            $lng = $loc->getLng();
            $lat = $loc->getLat();
        if (substr($loc->desc,0,1) == ",") {
            $search = $addr;
            $zip = substr($loc->desc, strlen($loc->desc) - 6, 6);
            if (!isValidZipCode($zip)) {
                $search .= " " . $zip;
            }
            echo "<a href=\"javascript:focusOn(new GLatLng($lat,$lng), map.getZoom());\"><img src=\"images/$marker_img\"/></a> $search<br/>";
        }
        else {
            // normal address lookup
            echo "<a href=\"javascript:focusOn(new GLatLng($lat,$lng), map.getZoom());\"><img src=\"images/$marker_img\"/></a> $loc->desc<br/>";
        }
    }
    
    function printMarker($marker, $marker_img) {
        $lng = $marker->getLng();
        $lat = $marker->getLat();
        echo "<a href=\"javascript:focusOn(new GLatLng($lat,$lng), map.getZoom());\"><img src=\"images/$marker_img\"/></a><b>$marker->name</b><br/>";
    }
    
    function printStation($station) {
        global $website, $station_marker_img;
        $station_name = $station->name;
        $station_lng = $station->getLng();
        $station_lat = $station->getLat();
        echo "<td style=\"text-align: center\">";
        echo "<a href=\"javascript:focusOn(new GLatLng($station_lat,$station_lng), map.getZoom());\"><img src=\"images/$station_marker_img\" border=\"0\"/></a>";
        echo "</td>\n";
        echo "<td>&nbsp;$station_name&nbsp;&nbsp;</td>\n";
        echo "<td>&nbsp;";
        $lines = $station->getLines();
        foreach($lines as $line) {
            $line_img = $line->img;
            $line_url = $website . $line->url;
            echo "<a href=\"javascript:void(0);\" onclick=\"window.open('$line_url');\"><img width=\"20\" src=\"images/$line_img\" /></a>";
        }
        echo "</td>\n";
    }
    
    // ensure that zip and intersection are part of the query value
    function correctHiddenValue($loc, $addr, $addr_name) {
        if (substr($loc,0,1) == ",") {
            $search = $addr;
            $zip = substr($loc, strlen($loc) - 6, 6);
            if (!isValidZipCode($zip)) {
                $search .= $zip;
            }
            echo "<input type=\"hidden\" name=\"$addr_name\" value=\"$search\" />\n";
        }
        else {
            // normal address lookup
            echo "<input type=\"hidden\" name=\"$addr_name\" value=\"$loc\" />\n";
        }
    }
    
    function correctRadioValue($loc, $addr, $addr_name, $num) {
        if (substr($loc->desc,0,1) == ",") {
            $search = $addr;
            $zip = substr($loc->desc, strlen($loc->desc) - 5, 5);
            if (!isValidZipCode($zip)) {
                $search .= " " . $zip;
            }
            echo "<input type=\"radio\" name=\"$addr_name\" value=\"$search\" />\n";
            echo "[<a href=\"javascript:focusOn(new GLatLng($loc->getLat(),$loc->getLng()), map.getZoom());\">$num</a>] $search";
        }
        else {
            // normal address lookup
            echo "<input type=\"radio\" name=\"$addr_name\" value=\"$loc->desc\" />\n";
            echo "[<a href=\"javascript:focusOn(new GLatLng($loc->getLat(),$loc->getLng()), map.getZoom());\">$num</a>] $loc->desc";
        }
    }
    
    /**
        @deprecated?
    */
    function loadFromArray($array) {
        $markers = array();
        $id = "";
        foreach ($array as $marker_array) {
            $marker = new Marker($marker_array[0]['lat'], $marker_array[0]['lng'], $marker_array[0]['id'], $marker_array[0]['name']);
            $id = $marker_array[0]['id'];
            
            echo "functions.load: processing $id<br/>\n";
            
            $count = count($marker_array);
            for ($i = 1; $i < $count; $i++) {
                $line_array = $marker_array[$i];
                $line = new Line($line_array[0]['img'], $marker_array[0]['url'], $marker_array[0]['name']);
                
                $count1 = count($line_array);
                for ($j = 1; $j < $count1; $j++) {
                    $connection_array = $line_array[$j];
                    $connection = new Connection($connection_array[0]['id'], $connection_array[0]['type'], $connection_array[0]['start'], $connection_array[0]['end'], $connection_array[0]['duration']);
                    
                    $line->addConnection($connection);
                }
                $marker->addLine($line);
            }
            $markers[$id] = $marker;
        }
        return $markers;
    }
    
    /**
        @deprecated?
    */
    function saveToArray($markers) {
        $array = array();
        $id = "";
        foreach ($markers as $marker) {
            $marker_array = array();
            $id = $marker->id;
            // add attributes
            $attribute_array = array('id' => $marker->id, 'lat' => $marker->getLat(), 'lng' => $marker->getLng(), 'name' => $marker->name);
            $marker_array[] = $attribute_array;
            $id2 = $attribute_array['id'];
            echo "functions.save: id = $id<br/>\n";
            echo "functions.save: id2 = $id2<br/>\n";
            
            $lines = $marker->getLines();
            foreach ($lines as $line) {
                $line_array = array();
                $attribute_array = array('img' => $line->img, 'url' => $line->url, 'name' => $line->name);
                $line_array[] = $attribute_array;
                
                $connections = $line->getConnections();
                foreach ($connections as $connection) {
                    $connection_array = array();
                    $attribute_array = array('id' => $connection->id, 'type' => $connection->type, 'start' => $connection->start, 'end' => $connection->end, 'duration' => $connection->duration, 'day' => $connection->day);
                    
                    $line_array[] = $connection_array;
                }
                $marker_array[] = $line_array;
            }
            $array[$id] = $marker_array;
        }
        return $array;
    }
?>
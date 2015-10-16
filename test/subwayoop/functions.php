<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #
    #   functions.php
    #   This file contains functions that are used for direction instructions output.
    #
    
    /**
        Checks to see if the string contains a valid zip code. If so, returns true.
        If not, then returns false.
    */
    function containsValidZipCode($string) {
        //$re = "/^\d{5}([-]\d{4})?$/";
        return preg_match("/^\\d{5}$/", $string);
    }
    
    /**
        Checks to see if the string contains a valid zip code. If so, returns true.
        If not, then returns false.
    */
    function isValidZipCode($value) {
        //$re = "/^\d{5}([-]\d{4})?$/";
        return preg_match("/^\\d{5}$/", $value);
    }
    
    /**
        Checks to see if the string contains a given substring. if it does not, then
        append the substring (with a comma) and return the string.
        
        @param $loc<String> the location string from the user
        @param $substring<String> the string to search
        
        @return the original string if substring was found or the modified string if not.
        @deprecated
    */
    function checkString($loc, $substring) {
        if (stristr($loc, $substring) === false) {
            $loc .= ", $substring";
        }
        return $loc;
    }
    
    /**
        Displays the station choices for choosing alternate stations when using direction finding.
        
        @param $url<String> the URL for the form
        @param $start_location<Location> the starting point for the search
        @param $end_location<Location> the ending point for the search
        @param $path_array[]<Segment> the path found in the original search
    */
    function displayStationChoices ($url, $start_location, $end_location, $path_array) {
        $debug = false;
        if ($debug) {
            echo $start_location->desc . " to " . $end_location->desc . "<br/>";
        }
        echo "<form id=\"form_pick\" action=\"$url\" method=\"get\">\n";
        // list alternate stations
        echo "<table border=\"1\">\n<tbody>\n<tr>\n<td colspan=\"4\"><b>Start Station Choices</b></td>\n</tr>\n<tr>\n<td>&nbsp;<b>Show</b>&nbsp;</td>\n<td><b>Station Name</b></td>\n<td><b>Subway Lines</b></td>\n</tr>\n";
        $num = 1;
        // grab the stations
        $start_stations = findNearestStations2($start_location);
        $start_station_marker = $path_array[0]->m2;
        if ($debug) {
            echo "start_station_marker->id = " . $start_station_marker->id . "<br/>";
        }
        foreach($start_stations as $station) {
            $station_marker = $station->marker;
            echo "<tr>\n";
            printStation($station_marker);
            echo "<td>\n";
            if ($start_station_marker->id == $station_marker->id) {
                echo "<input type=\"radio\" name=\"start\" value=\"$station_marker->id\" checked/>\n";
            }
            else {
                echo "<input type=\"radio\" name=\"start\" value=\"$station_marker->id\" />\n";
            }
            echo "</td>\n";
            echo "</tr>\n";
            $num++;
        }
        // list alternate end stations
        $end_stations = findNearestStations2($end_location);
        $end_station_marker = $path_array[count($path_array) - 1]->m1;
        if ($debug) {
            echo "end_station_marker->id = " . $end_station_marker->id . "<br/>";
        }
        echo "<tr>\n<td colspan=\"4\"><b>End Station Choices</b></td>\n</tr>\n";
        echo "<tr>\n<td>&nbsp;<b>Show</b>&nbsp;</td>\n";
        echo "<td><b>Station Name</b></td>\n";
        echo "<td><b>Subway Lines</b></td>\n</tr>\n";
        $num = 1;
        foreach($end_stations as $station) {
            $station_marker = $station->marker;
            echo "<tr>\n";
            printStation($station_marker);
            echo "<td>\n";
            if ($end_station_marker->id == $station_marker->id) {
                echo "<input type=\"radio\" name=\"end\" value=\"$station_marker->id\" checked/>\n";
            }
            else {
                echo "<input type=\"radio\" name=\"end\" value=\"$station_marker->id\" />\n";
            }
            echo "</td>\n</tr>\n";
            $num++;
        }
        echo "</tbody>\n</table>\n";
        
        // geocoder leaves out intersections! we have to fill in with the query here
        //correctHiddenValue($start_loc, $saddr, "saddr");
        // geocoder leaves out intersections! we have to fill in with the query here
        //correctHiddenValue($end_loc, $daddr, "daddr");
        echo "<input type=\"hidden\" name=\"type\" value=\"dir_alt\" />\n";
        echo "<input type=\"hidden\" name=\"saddr\" value=\"" . $start_location->getAddressString() . "\" />\n";
        echo "<input type=\"hidden\" name=\"daddr\" value=\"" . $end_location->getAddressString() . "\" />\n";
        echo "<br/>\n";
        echo "<input type=\"image\" name=\"submit\" src=\"images/usebutton.png\">\n";
        echo "</form><br/>\n";
    }
    
    /**
        Displays the list of stations as options.
        
        @param $selected_id the selected station, if any
        @param $markers the system markers
    */
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
            if ($count > 1) {
                print_r($path);
            }
            else {
                echo "path = $path<br/>";
            }
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
                        echo "<a href=\"javascript:void(0);\" onclick=\"window.open('$website" . $lines[$i]->url . "');\"><img width=\"20\" src=\"images/" . $lines[$i]->img . "\"/></a>";
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

    /**
        @deprecated?
    */
    function printAddress ($loc, $addr, $marker_img) {
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
    
    /**
        Converts location into text data and provies a clickable image that will allow it to zoom to the correct
        location on the map.
        
        @param $loc the location object
        @param $marker_img the marker image to use for display
    */
    function printAddress2 ($loc, $marker_img) {
        $lng = $loc->getLng();
        $lat = $loc->getLat();
        echo "<a href=\"javascript:focusOn(new GLatLng($lat,$lng), map.getZoom());\"><img src=\"images/$marker_img\"/></a>";
        if ($loc->name != null || $loc->name != "") {
            echo "<b>" . $loc->name . "</b> ";
        }
        echo $loc->getAddressString() . "<br/>";
    }
    
    function printMarker($marker, $marker_img) {
        $lng = $marker->getLng();
        $lat = $marker->getLat();
        echo "<a href=\"javascript:focusOn(new GLatLng($lat,$lng), map.getZoom());\"><img src=\"images/$marker_img\"/></a><b>$marker->name</b><br/>";
    }
    
    /**
        Displays station information on the sidebar.
    */
    function printStation ($station_marker) {
        global $website, $station_marker_img;
        $station_name = $station_marker->name;
        $station_lng = $station_marker->getLng();
        $station_lat = $station_marker->getLat();
        echo "<td style=\"text-align: center\">";
        echo "<a href=\"javascript:focusOn(new GLatLng($station_lat,$station_lng), map.getZoom());\"><img src=\"images/$station_marker_img\" border=\"0\"/></a>";
        echo "</td>\n";
        echo "<td>&nbsp;$station_name&nbsp;&nbsp;</td>\n";
        echo "<td>&nbsp;";
        $lines = $station_marker->getLines();
        foreach($lines as $line) {
            $line_img = $line->img;
            $line_url = $website . $line->url;
            echo "<a href=\"javascript:void(0);\" onclick=\"window.open('$line_url');\"><img width=\"20\" src=\"images/$line_img\" /></a>";
        }
        echo "</td>\n";
    }
    
    // ensure that zip and intersection are part of the query value
    // @deprecated
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
    
    function correctRadioValue ($loc, $addr, $addr_name, $num) {
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
        
    function validateSearchString ($address, $city, $state) {
        $debug = false;
        if (containsValidZipCode($address)) {
            // address has a zip code. Google will find the address without
            // a city or state
            if ($debug) {
                echo "debug[validateSearchString]: found a valid zip code in $address<br/>";
            }
            return $address;
        }
        // this function is in subwaydb/db.php
        $new_address = findAndAddBorough($address, $city);
        $new_address = findAndAddState($new_address, $state);
        return $new_address;
    }    
?>
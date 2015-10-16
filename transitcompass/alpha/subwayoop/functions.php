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
        global $website, $station_marker_img;
        $debug = false;
        if ($debug) {
            echo $start_location->desc . " to " . $end_location->desc . "<br/>";
        }
        // list alternate start stations
        $start_table = new Table(null, "choicetable");
        $div = new Div("alternate_choices", "<b>Start Station Choices</b><br/>\n");
        $div->addDataElement($start_table);
        $div->addDataElement("<br/>\n");
        
        $header_row = new Row(null, "header_row");
        $header_row->addCell(new Cell("header_cell", "Show"));
        $header_row->addCell(new Cell("header_cell", "Station Name"));
        $header_row->addCell(new Cell("header_cell", "Subway Lines"));
        $header_row->addCell(new Cell("header_cell"));
        $start_table->addRow($header_row);
        
        $start_stations = findNearestStations2($start_location);
        $start_station_marker = $path_array[0]->m2;
        if ($debug) {
            echo "start_station_marker->id = " . $start_station_marker->id . "<br/>";
        }
        $i = 0;
        foreach ($start_stations as $station) {
            $station_marker = $station->marker;
            $station_lng = $station_marker->getLng();
            $station_lat = $station_marker->getLat();
            if (isEven($i)) {
                $class_name = "rowHighlight";
            }
            else {
                $class_name = "row";
            }
            $station_row = new Row($station_marker->id, $class_name);
            // station marker link
            $station_row->addCell(new Cell("step_cell", new Link("javascript:focusOn(new GLatLng($station_lat,$station_lng), map.getZoom());", new Image("images/$station_marker_img", "0"))));
            $station_row->addCell(new Cell("instruction", $station_marker->name));
            // build lines cell
            $lines = $station_marker->getLines();
            $lines_cell = new Cell("instruction");
            foreach ($lines as $line) {
                $line_img = $line->img;
                $line_url = $website . $line->url;
                $lines_cell->addData(new Link("javascript:void(0);", new Image(null, "images/$line_img", $line->name, null, "20"), "window.open('$line_url');"));
            }
            $station_row->addCell($lines_cell);
            
            if ($start_station_marker->id == $station_marker->id) {
                $cell_data = "<input type=\"radio\" name=\"start\" value=\"$station_marker->id\" checked=\"true\"/>";
            }
            else {
                $cell_data = "<input type=\"radio\" name=\"start\" value=\"$station_marker->id\" />";
            }
            $station_row->addCell(new Cell("step_cell", $cell_data));
            $start_table->addRow($station_row);
            $i++;
        }
        
        // list alternate end stations
        $div->addDataElement("<b>End Station Choices</b><br/>\n");
        $end_table = new Table(null, "choicetable");
        $div->addDataElement($end_table);
        $end_table->addRow($header_row);
        
        $end_stations = findNearestStations2($end_location);
        $end_station_marker = $path_array[count($path_array) - 1]->m1;
        if ($debug) {
            echo "end_station_marker->id = " . $end_station_marker->id . "<br/>";
        }
        
        $i = 0;
        foreach ($end_stations as $station) {
            $station_marker = $station->marker;
            $station_lng = $station_marker->getLng();
            $station_lat = $station_marker->getLat();
            if (isEven($i)) {
                $class_name = "rowHighlight";
            }
            else {
                $class_name = "row";
            }
            $station_row = new Row($station_marker->id, $class_name);
            // station marker link
            $station_row->addCell(new Cell("step_cell", new Link("javascript:focusOn(new GLatLng($station_lat,$station_lng), map.getZoom());", new Image("images/$station_marker_img", "0"))));
            $station_row->addCell(new Cell("instruction", $station_marker->name));
            // build lines cell
            $lines = $station_marker->getLines();
            $lines_cell = new Cell("instruction");
            foreach ($lines as $line) {
                $line_img = $line->img;
                $line_url = $website . $line->url;
                $lines_cell->addData(new Link("javascript:void(0);", new Image(null, "images/$line_img", $line->name, null, "20"), "window.open('$line_url');"));
            }
            $station_row->addCell($lines_cell);
            
            if ($end_station_marker->id == $station_marker->id) {
                $cell_data = "<input type=\"radio\" name=\"end\" value=\"$station_marker->id\" checked=\"true\"/>";
            }
            else {
                $cell_data = "<input type=\"radio\" name=\"end\" value=\"$station_marker->id\" />";
            }
            $station_row->addCell(new Cell("step_cell", $cell_data));
            $end_table->addRow($station_row);
            $i++;
        }
        
        
        echo "<form id=\"form_pick\" action=\"$url\" method=\"get\">\n";
        echo $div->toString();
        
        echo "<input type=\"hidden\" name=\"type\" value=\"dir_alt\" />\n";
        echo "<input type=\"hidden\" name=\"sa\" value=\"" . $start_location->getAddressString() . "\" />\n";
        echo "<input type=\"hidden\" name=\"da\" value=\"" . $end_location->getAddressString() . "\" />\n";
        echo "<br/>\n";
        #echo "<input type=\"image\" name=\"submit\" src=\"images/usebutton.png\">\n";
        echo "<div id=\"usebutton\"><input class=\"btn\" type=\"submit\" id=\"submit\" value=\"Use\"></div>\n";
        echo "</form>";
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
    
    function isEven($num){
      return ($num%2) ? true : false;
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
            echo "<b>" . $loc->name . "</b><br/>";
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
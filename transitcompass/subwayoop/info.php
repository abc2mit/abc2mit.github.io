<?php
    $debug = false;
    if($type == 's2s') {
        echo "Start Station:<br/>\n";
        printMarker($start_marker, $start_marker_img);
        echo "<br/>\n";
        echo "End Station:<br/>\n";
        printMarker($end_marker, $end_marker_img);
        echo "<br/><br/>\n";
        $cpath = $path;
        if (is_string($path)) {
            if ($path == "walk") {
                echo "You don't need to take the subway. Why don't you walk instead?<br/>";
            }
            else if ($path == "same") {
                echo "You're just testing the system, aren't you? Go do something more interesting, like telling your friends about this great site.<br/>";
            }
        }
        else if (is_object($path)) {
            $cpath = $path->path;
            if (count($path->path) > 1) {
                #echo "s2s consolidating<br/>";
                $cpath = consolidatePath2($path, $type);
                // remove the last path because we're not walking
                //array_pop($cpath);
            }
            printPath($cpath, $path->time);
        }
    }
    else if ($type == 'location') {
        if (count($locations) > 1) {
            echo "Multiple Locations Found:<br/><br/>\n";
            for ($i = 0; $i < count($locations); $i++) {
                $loc = $locations[$i];
                $num = $i + 1;
                echo "<form id=\"form_pick\" action=\"$link\" method=\"get\">\n";
                $lat = $loc->getLat();
                $lng = $loc->getLng();
                echo "<a href=\"javascript:focusOn(new GLatLng($lat,$lng), map.getZoom());\"><img src=\"images/$location_marker\"/></a>";
                echo "<b>" . $loc->name . "</b> (" . $loc->phone . "): " . $loc->getAddressString() . "\n";
                // TODO find an address that tests this
                echo "<input type=\"hidden\" name=\"n\" value=\"". $loc->name . "\" />\n";
                echo "<input type=\"hidden\" name=\"sa\" value=\"". $loc->getAddressString() . "\" />\n";
                echo "<input type=\"hidden\" name=\"type\" value=\"location\" />\n";
                echo "<input type=\"image\" name=\"submit\" src=\"images/usebutton.png\">\n";
                echo "</form>\n";
            }
        }
        else if (count($locations) == 1) {
            echo "<b>Location</b><br/>\n";
            $loc = $locations[0];
            $marker_link = new MarkerLink($loc->getLat(), $loc->getLng(), new Image("images/$location_marker", 0));
            echo $marker_link->toString();
            if ($loc->name != null || $loc->name != "") {
                echo "<b>" . $loc->name . "</b><br/>";            }
            if ($loc->phone != null || $loc->phone != "") {
                echo " (" . $loc->phone . ")<br/>";
            }
            $address_string = $loc->getAddressString();
            if ($address_string != "") {
                echo $address_string . "\n<br/>";
            }
            echo "<br/>";
            
            $stations_div = new Div("closest_stations", "<b>The Three Closest Stations</b>");
            // build the table
            $stations_table = new Table(null, "info_table");
            $stations_div->addDataElement($stations_table);
            // create the header
            $header_row = new Row(null, "header_row");
            $stations_table->addRow($header_row);
            $header_row->addCell(new Cell("header_cell", "<b>Show</b>"));
            $header_row->addCell(new Cell("header_cell", "<b>Station Name</b>"));
            $header_row->addCell(new Cell("header_cell", "<b>Subway Lines</b>"));
            $highlight = false;
            foreach ($stations as $station) {
                if ($highlight) {
                    $class_name = "rowHighlight";
                    $highlight = false;
                }
                else {
                    $class_name = "row";
                    $highlight = true;
                }
                $stations_table->addRow(createStationTableRow($station, $station_marker_img, $class_name));
            }
            
            echo $stations_div->toString() . "<br/>\n";
            
            // directions table
            if (isset($_GET['sa'])) {
                $l = $_GET['sa'];
            }
            else if (isset($_GET['da'])) {
                $l = $_GET['da'];
            }
            else {
                $l = $_GET['l'];
            }
            $directions_table = new Table(null, "info_table");
            $header_row = new Row(null, "header_row");
            $directions_table->addRow($header_row);
            $header_row->addCell(new Cell("header_cell", "<b>Directions</b>"));
            $header_row->addCell(new Cell("header_cell", ""));
            $choices_row = new Row(null, "row");
            $directions_table->addRow($choices_row);
            $choices_row->addCell(new Cell("choice", "<a href=\"javascript:void(0)\" onclick=\"return _gd('to', '$l')\"><img src=\"images/$start_marker_img\"/> To $l</a>"));
            $choices_row->addCell(new Cell("choice", "<a href=\"javascript:void(0)\" onclick=\"return _gd('from', '$l')\"><img src=\"images/$end_marker_img\"/> From $l</a>"));
            echo $directions_table->toString();
        }
        else {
            echo "Sorry. Address could not be found. Make sure that it is a valid postal address, not a vanity address.<br/>\n"; 
        }
    }
    // directions
    else if ($type == 'dir' || $type == 'dir_alt') {
        if ((count($starts) == 1) && (count($ends) == 1)) {
            // TODO convert to class
            echo "<div id=\"address_info\">\n";
            echo "<b>Start Address:</b><br/>\n";
            $loc = $starts[0];
            printAddress2($loc, $start_marker_img);
            echo "<br/>\n";
            echo "<b>End Address:</b><br/>\n";
            $loc = $ends[0];
            printAddress2($loc, $end_marker_img);
            echo "</div>\n";
            
            if (!is_null($path)) {
                if (is_string($path)) {
                    if ($path == "walk") {
                        echo "You don't need to take the subway. Why don't you walk instead?<br/>";
                    }
                    else if ($path == "same") {
                        echo "You're just testing the system, aren't you? Go do something more interesting, like telling your friends about this great site.<br/>";
                    }
                }
                else if (is_object($path)) {
                    if ($debug) {
                        echo "setting cpath<br/>";
                    }
                    $cpath = $path->path;
                    if (count($cpath) > 1) {
                        if ($debug) {
                            echo "consolidating path<br/>";
                        }   
                        $cpath = consolidatePath2($path, $type);
                    }
                    if (is_string($cpath)) {
                        if ($cpath == "walk") {
                            echo "You don't need to take the subway. Why don't you walk instead?<br/>";
                        }
                        else if ($cpath == "same") {
                            echo "You're just testing the system, aren't you? Go do something more interesting, like telling your friends about this great site.<br/>";
                        }
                    }
                    else {
                        printPath($cpath, $path->time);
                    }
                    echo "<br/>\n";
                    displayStationChoices($link, $start_location, $end_location, $path->path);
                }
            }
            else {
                echo "No Path Found.<br/>\n";
            }
        }
        // more than one location found
        else {
            echo "<form id=\"form_pick\" action=\"$current_url\" method=\"get\">\n";
            if (count($starts) > 1) {
                echo "Multiple Locations Found for Start Address:<br/><br/>\n";
                for ($i = 0; $i < count($starts); $i++) {
                    $loc = $starts[$i];
                    $num = $i + 1;
                    //correctRadioValue($starts[$i], $sa, "sa", $num);
                    echo "<input type=\"radio\" name=\"sa\" value=\"" . $loc->getAddressString() . "\" />\n";
                    echo "[<a href=\"javascript:focusOn(new GLatLng(" . $loc->getLat() . "," . $loc->getLng() . "), map.getZoom());\">$num</a>] ";
                    if ($loc->name != "") {
                        echo "<b>" . $loc->name . "</b> ";
                    }
                    echo $loc->getAddressString();
                    echo "<br/>\n";
                }
            }
            else if (count($starts) == 1) {
                $loc = $starts[0];
                echo "Start Address:<br/>\n";
                $num = 1;
                //correctRadioValue($starts[0], $sa, "sa", $num);
                echo "<a href=\"javascript:focusOn(new GLatLng(" . $loc->getLat() . "," . $loc->getLng() . "), map.getZoom());\"><img src=\"images/$location_marker\"/></a>";
                if ($loc->name != null || $loc->name != "") {
                    echo "<b>" . $loc->name . "</b> ";
                }
                echo $loc->getAddressString();
                echo "<br/>\n";
                echo "<input type=\"hidden\" name=\"sa\" value=\"$sa\" />\n";
            }
            else {
                echo "No starting address found for: $sa<br/>Make sure that it is a valid postal address, not a vanity address.<br/>\n";
                echo "<input type=\"hidden\" name=\"sa\" value=\"$sa\" />\n";
            }
            echo "<br/>\n";
            if (count($ends) > 1) {
                echo "Multiple Locations Found for End Address:<br/><br/>\n";
                for ($i = 0; $i < count($ends); $i++) {
                    $loc = $ends[$i];
                    $num = $i + 1;
                    //correctRadioValue($ends[$i], $da, "da", $num);
                    echo "<input type=\"radio\" name=\"da\" value=\"" . $loc->getAddressString() . "\" />\n";
                    echo "[<a href=\"javascript:focusOn(new GLatLng(" . $loc->getLat() . "," . $loc->getLng() . "), map.getZoom());\">$num</a>] ";
                    if ($loc->name != null || $loc->name != "") {
                        echo "<b>" . $loc->name . "</b> ";
                    }
                    echo $loc->getAddressString();
                    echo "<br/>\n";
                }
            }
            else if (count($ends) == 1) {
                $loc = $ends[0];
                echo "End Address:<br/>\n";
                $num = $num + 1;
                //correctRadioValue($ends[0], $da, "da", $num);
                echo "<a href=\"javascript:focusOn(new GLatLng(" . $loc->getLat() . "," . $loc->getLng() . "), map.getZoom());\"><img src=\"images/$location_marker\"/></a>";
                if ($loc->name != "") {
                    echo "<b>" . $loc->name . "</b> ";
                }
                echo $loc->getAddressString();
                echo "<br/>\n";
                echo "<input type=\"hidden\" name=\"da\" value=\"$da\" />\n";
                echo "<br/>\n";
            }
            else {
                echo "No destination address found for:$da<br/>Make sure that it is a valid postal address, not a vanity address.<br/>\n";
                echo "<input type=\"hidden\" name=\"da\" value=\"$da\" />\n";
            }
            echo "<br/>\n";
            echo "<input type=\"image\" name=\"submit\" src=\"images/usebutton.png\" />\n";
            echo "<input type=\"hidden\" name=\"type\" value=\"dir\" />\n";
            echo "</form><br/>\n";
        }
    }
    else if ($type == "s2a") {
        echo "Start Station:<br/>\n";
        printMarker($start_marker, $start_marker_img);
        echo "<br/>\n";
        echo "End Address:<br/>\n";
        $loc = $ends[0];
        $end_loc = $loc->name;
        //printAddress($loc, $da, $end_marker_img);
        printAddress2($loc, $end_marker_img);
        echo "<br/><br/>\n";
        if (!is_null($path)) {
            if (is_string($path)) {
                if ($path == "walk") {
                    echo "You don't need to take the subway. Why don't you walk instead?<br/>";
                }
                else if ($path == "same") {
                    echo "You're just testing the system, aren't you? Go do something more interesting, like telling your friends about this great site.<br/>";
                }
            }
            else if (is_object($path)) {
                if ($debug) {
                    echo "setting cpath<br/>";
                }
                $cpath = $path->path;
                if (count($cpath) > 1) {
                    if ($debug) {
                        echo "consolidating path<br/>";
                    }   
                    $cpath = consolidatePath2($path, $type);
                }
                if (is_string($cpath)) {
                    if ($cpath == "walk") {
                        echo "You don't need to take the subway. Why don't you walk instead?<br/>";
                    }
                    else if ($cpath == "same") {
                        echo "You're just testing the system, aren't you? Go do something more interesting, like telling your friends about this great site.<br/>";
                    }
                }
                else {
                    printPath($cpath, $path->time);
                }
                echo "<br/>\n";
                #displayStationChoices($current_url, $start_location, $end_location, $path->path);
            }
        }
        else {
            echo "No Path Found.<br/>\n";
        }
    }
    else if ($type == "a2s") {
        echo "Start Address:<br/>\n";
        $loc = $starts[0];
        printAddress2($loc, $start_marker_img);
        echo "<br/>\n";        
        echo "End Station:<br/>\n";
        printMarker($end_marker, $end_marker_img);
        echo "<br/><br/>\n";
        if (!is_null($path)) {
            if (is_string($path)) {
                if ($path == "walk") {
                    echo "You don't need to take the subway. Why don't you walk instead?<br/>";
                }
                else if ($path == "same") {
                    echo "You're just testing the system, aren't you? Go do something more interesting, like telling your friends about this great site.<br/>";
                }
            }
            else if (is_object($path)) {
                if ($debug) {
                    echo "setting cpath<br/>";
                }
                $cpath = $path->path;
                if (count($cpath) > 1) {
                    if ($debug) {
                        echo "consolidating path<br/>";
                    }   
                    $cpath = consolidatePath2($path, $type);
                }
                if (is_string($cpath)) {
                    if ($cpath == "walk") {
                        echo "You don't need to take the subway. Why don't you walk instead?<br/>";
                    }
                    else if ($cpath == "same") {
                        echo "You're just testing the system, aren't you? Go do something more interesting, like telling your friends about this great site.<br/>";
                    }
                }
                else {
                    printPath($cpath, $path->time);
                }
                echo "<br/>\n";
                #displayStationChoices($current_url, $start_location, $end_location, $path->path);
            }
        }
        else {
            echo "No Path Found.<br/>\n";
        }
    }
    // default page
    else {
        echo "<b>How To Use This Site</b><br/>\n";
        echo "Find the <i>nearest station</i> by entering an address in one of the two fields.<br/>";
        if ($state == "NY") {
            echo "(i.e. <a href=\"http://transitcompass.com/nyc/?sa=50+Wall+St\">50 Wall St</a>)<br/>";
        }
        echo "<br/>\n";
        echo "Find <i>directions</i> by entering a start address and an end address.<br/>";
        if ($state == "NY") {
            echo "(i.e. <a href=\"http://transitcompass.com/nyc/?sa=1032+Greene+Ave%2C+Brooklyn%2C+NY&da=1585+Broadway\">1032 Greene Ave, Brooklyn to 1585 Broadway</a>)<br/>";
        }
        echo "<br/>\n";
        echo "Find <i>directions</i> by entering a starting station name and an ending station name.<br/>";
        if ($state == "NY") {
            echo "(i.e. <a href=\"http://transitcompass.com/nyc/?sa=Forest+Hills-71st+Ave&da=Brooklyn+Bridge-City+Hall&sid=SFH71AEFGRV&did=SBBCH456\">Forest Hills-71st Ave to Brooklyn Bridge-City Hall</a>)<br/>";
        }
        echo "<br/>\n";
        echo "Or you can enter any combination of station name or address!<br/>";
        if ($state == "NY") {
            echo "(i.e. <a href=\"http://transitcompass.com/nyc/?sa=Forest+Hills-71st+Ave&sid=SFH71AEFGRV&da=1585+Broadway\">Forest Hills-71st Ave to 1585 Broadway</a>)\n";
        }
        echo "</div>\n";
    }
?>
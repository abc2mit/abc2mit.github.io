<?php
    $debug = false;
    if($type == 's2s') {
        echo "Start Marker:<br/>\n";
        printMarker($start_marker, $start_marker_img);
        echo "<br/>\n";
        echo "End Marker:<br/>\n";
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
                $cpath = consolidatePath($path, $type);
                // remove the last path because we're not walking
                //array_pop($cpath);
            }
            printPath($cpath);
        }
    }
    else if ($type == 'location') {
        if (count($locations) > 1) {
            echo "Multiple Locations Found:<br/><br/>\n";
            for ($i = 0; $i < count($locations); $i++) {
                $loc = $locations[$i];
                $num = $i + 1;
                echo "<form id=\"form_pick\" action=\"$current_url\" method=\"get\">\n";
                $lat = $loc->getLat();
                $lng = $loc->getLng();
                echo "<a href=\"javascript:focusOn(new GLatLng($lat,$lng), map.getZoom());\"><img src=\"images/$location_marker\"/></a>";
                echo "<b>" . $loc->name . "</b> (" . $loc->phone . "): " . $loc->getAddressString() . "\n";
                echo "<input type=\"hidden\" name=\"n\" value=\"". $loc->name . "\" />\n";
                echo "<input type=\"hidden\" name=\"l\" value=\"". $loc->getAddressString() . "\" />\n";
                //correctOutput($_GET['l'], $loc, $num);
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
                    $class_name = "stationRowHighlight";
                    $highlight = false;
                }
                else {
                    $class_name = "stationRow";
                    $highlight = true;
                }
                $stations_table->addRow(createStationTableRow($station, $station_marker_img, $class_name));
            }
            
            echo $stations_div->toString() . "<br/>\n";
            if (isset($_GET['saddr'])) {
                $l = $_GET['saddr'];
            }
            else if (isset($_GET['daddr'])) {
                $l = $_GET['daddr'];
            }
            else {
                $l = $_GET['l'];
            }
            $directions_table = new Table(null, "info_table");
            $header_row = new Row(null, "header_row");
            $directions_table->addRow($header_row);
            $header_row->addCell(new Cell("header_cell", "<b>Directions</b>"));
            $header_row->addCell(new Cell("header_cell", ""));
            $choices_row = new Row(null, "stationRow");
            $directions_table->addRow($choices_row);
            $choices_row->addCell(new Cell("choice", "<a href=\"javascript:void(0)\" onclick=\"return _gd('to', '$l')\"><img src=\"images/$start_marker_img\"/> To $l</a>"));
            //$choices_row = new Row(null, "stationRowHighlight");
            //$directions_table->addRow($choices_row);
            $choices_row->addCell(new Cell("choice", "<a href=\"javascript:void(0)\" onclick=\"return _gd('from', '$l')\"><img src=\"images/$end_marker_img\"/> From $l</a>"));
            echo $directions_table->toString();
            #echo "Directions: [<a href=\"javascript:void(0)\" onclick=\"return _gd('to', '$l')\">To Here</a>] [<a href=\"javascript:void(0)\" onclick=\"return _gd('from', '$l')\">From Here</a>]\n";
        }
        else {
            echo "Sorry. Address could not be found. Make sure that it is a valid postal address, not a vanity address.<br/>\n"; 
        }
    }
    // directions
    else if ($type == 'dir' || $type == 'dir_alt') {
        if ((count($starts) == 1) && (count($ends) == 1)) {
            echo "Start Address:<br/>\n";
            $loc = $starts[0];
            $start_loc = $loc->name;
            //printAddress($loc, $saddr, $start_marker_img);
            printAddress2($loc, $start_marker_img);
            echo "<br/>\n";
            echo "End Address:<br/>\n";
            $loc = $ends[0];
            $end_loc = $loc->name;
            //printAddress($loc, $daddr, $end_marker_img);
            printAddress2($loc, $end_marker_img);
            echo "<br/>\n";
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
                        $cpath = consolidatePath($path, $type);
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
                        printPath($cpath);
                    }
                    echo "<br/>\n";
                    displayStationChoices($current_url, $start_location, $end_location, $path->path);
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
                    //correctRadioValue($starts[$i], $saddr, "saddr", $num);
                    echo "<input type=\"radio\" name=\"saddr\" value=\"" . $loc->getAddressString() . "\" />\n";
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
                //correctRadioValue($starts[0], $saddr, "saddr", $num);
                echo "<a href=\"javascript:focusOn(new GLatLng(" . $loc->getLat() . "," . $loc->getLng() . "), map.getZoom());\"><img src=\"images/$location_marker\"/></a>";
                if ($loc->name != null || $loc->name != "") {
                    echo "<b>" . $loc->name . "</b> ";
                }
                echo $loc->getAddressString();
                echo "<br/>\n";
                echo "<input type=\"hidden\" name=\"saddr\" value=\"$saddr\" />\n";
            }
            else {
                echo "No starting address found for: $saddr<br/>Make sure that it is a valid postal address, not a vanity address.<br/>\n";
                echo "<input type=\"hidden\" name=\"saddr\" value=\"$saddr\" />\n";
            }
            echo "<br/>\n";
            if (count($ends) > 1) {
                echo "Multiple Locations Found for End Address:<br/><br/>\n";
                for ($i = 0; $i < count($ends); $i++) {
                    $loc = $ends[$i];
                    $num = $i + 1;
                    //correctRadioValue($ends[$i], $daddr, "daddr", $num);
                    echo "<input type=\"radio\" name=\"daddr\" value=\"" . $loc->getAddressString() . "\" />\n";
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
                //correctRadioValue($ends[0], $daddr, "daddr", $num);
                echo "<a href=\"javascript:focusOn(new GLatLng(" . $loc->getLat() . "," . $loc->getLng() . "), map.getZoom());\"><img src=\"images/$location_marker\"/></a>";
                if ($loc->name != "") {
                    echo "<b>" . $loc->name . "</b> ";
                }
                echo $loc->getAddressString();
                echo "<br/>\n";
                echo "<input type=\"hidden\" name=\"daddr\" value=\"$daddr\" />\n";
                echo "<br/>\n";
            }
            else {
                echo "No destination address found for:$daddr<br/>Make sure that it is a valid postal address, not a vanity address.<br/>\n";
                echo "<input type=\"hidden\" name=\"daddr\" value=\"$daddr\" />\n";
            }
            echo "<br/>\n";
            echo "<input type=\"image\" name=\"submit\" src=\"images/usebutton.png\" />\n";
            echo "<input type=\"hidden\" name=\"type\" value=\"dir\" />\n";
            echo "</form><br/>\n";
        }
    }
    // default page
    else {
        echo "<table>\n<thead>\n<tr>\n<td colspan=\"2\">\n";
        echo "<b>How To Use This Site</b>";
        echo "</td>\n</tr>\n</thead>\n";
        echo "<tbody>\n<tr style=\"background-color:#DDDDDD\">\n<td style=\"vertical-align:top\">\n";
        echo "<b>Station</b></td><td>Find the nearest subway station to your location. Simply enter the address and click <b>search</b>.\n";
        echo "</td>\n</tr>\n";
        echo "<tr>\n<td style=\"vertical-align:top\">\n";
        echo "<b>Station To Station</b></td><td>Find the shortest distance directions between two stations.\n";
        echo "</td>\n</tr>\n";
        echo "<tr style=\"background-color:#DDDDDD\">\n<td style=\"vertical-align:top\">\n";
        echo "<b>Directions</b></td><td>Need to get from A to B? Put the addresses into the fields and see the fastest way by subway!\n";
        echo "</td>\n</tr>\n</tbody>\n</table>\n";
?>
<!--<div style="background-color:#ffeac0;padding:2px 5px 2px 5px;margin-top:10px">
<b>If you like this site, please tell your friends!</b> The site is written entirely by hand and funded by personal wallets, just to make travel in the Big Apple greater and better for you!<br/><br/><i>NOTE: Anything Google can find, we can find.</i>
</div>
<p>We are committed to constantly improving this site. Most of what we do is on our free time. We keep a log on our <a href="faq_maps.html">FAQ page</a>, so please refer to that if you want to know what we've done!</p>
<div style="background-color:#ffeac0;padding:2px 5px 2px 5px">
We need your help! If you run into an error, can you please email us the address(es) or stations you were using to search? This would help us immensely in debugging any problems the code might have. Thanks so much! Email: <a href="mailto:subwaymaps@gmail.com">subwaymaps@gmail.com</a>-->
</div>
<?php
    }
?>
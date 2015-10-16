<?php
    function createCityTableRow ($city, $marker_img, $class_name, $link) {
        $city_name = $city->name;
        #echo "createCityTableRow: " . $city_name . "<br/>";
        $city_row = new Row($city_name, $class_name);
        $marker_link = new MarkerLink($city->getLat(), $city->getLng(), new Image("images/$marker_img", 0));
        $city_row->addCell(new Cell(null, "stationMarkerLink", "text-align: center", $marker_link));
        $city_row->addCell(new Cell("cityName", $city_name));
        $city_row->addCell(new Cell("cityLink", new Link("http://" . $link, $link)));
        return $city_row;
    }

    /**
        @param $station<Station>
    */
    function createStationTableRow ($station, $marker_img, $class_name) {
        global $website;
        $station_marker = $station->marker;
        $station_name = $station_marker->name;
        $marker_link = new MarkerLink($station_marker->getLat(), $station_marker->getLng(), new Image("images/$marker_img", 0));
        #echo $marker_link->toString() . "<br/>";
        $station_row = new Row($station_name, $class_name);
        //$station_cell = new Cell(null, null, null, $marker_link);
        #echo $station_cell->toString();
        #$station_row->addCell($station_cell);
        $station_row->addCell(new Cell(null, "stationMarkerLink", "text-align: center", $marker_link));
        $station_row->addCell(new Cell("stationName", $station_name));
        $lines_cell = new Cell("stationLines");
        $lines = $station_marker->getLines();
        foreach ($lines as $line) {
            foreach($line->getConnections() as $connection) {
                if ($connection->type != "transfer") {
                    $line_img = $line->img;
                    $line_url = $website . $line->url;
                    $link = new Link("javascript:void(0);", new Image(null, null, "images/$line_img", $line->name, null, 20), "window.open('$line_url');");
                    //echo $link->toString();
                    $lines_cell->addData($link);
                    break;
                }
            }
        }
        $station_row->addCell($lines_cell);
        #echo "<!--" . $station_row->toString() . "-->";
        return $station_row;
    }
    
    /**
        This method displays the path instructions on the left side of the screen.
        
        @param $path<array[Segment]> the path found by the algorithm
    */
    function printPath($path, $path_time) {
        global $website;
        $debug = false;
        $count = count($path);
        if ($debug) {
            echo "[display_functions.php:printPath()]<br/>\n";
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
            $directions_table = new Table(null, "info_table");
            $header_row = new Row(null, "header_row");
            $directions_table->addRow($header_row);
            $header_row->addCell(new Cell("header_cell", "Step"));
            $header_row->addCell(new Cell("header_cell", "Instructions"));
            $header_row->addCell(new Cell("header_cell", "Time"));
            
            for ($x = 0; $x < $count; $x++) {
                // create the row object
                if (isEven($x)) {
                    $row_class = "rowHighlight";
                }
                else {
                    $row_class = "row";
                }
                $row = new Row(null, $row_class);
                
                $y = $x + 1;
                // grab the segment and all its variables
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
                
                $step_cell = new Cell("step_cell", new Link("javascript:focusOn(new GLatLng($m1lat,$m1lng), map.getZoom());", "[ $y ]"));
                $instruction_cell = new Cell("instruction");
                $rawTime = $segment->t;                
                $time_cell = new Cell("step_cell", getTimeString($rawTime));
                
                if ($connection == "walking") {
                    $instruction_cell->addData("Walk from <b>$m1_name</b> to <b>$m2_name</b>.");
                }
                else if ($connection == "transfer") {
                    $lines = $segment->lines;
                    $from_string = "";
                    $to_string = "";
                    for ($i = 0; $i < count($old_lines); $i++) {
                            $from_string .= "<img width=\"20\" src=\"images/" . $old_lines[$i]->img . "\"/>";
                    }
                    for ($i = 0; $i < count($lines); $i++) {
                            $to_string .= "<img width=\"20\" src=\"images/" . $lines[$i]->img . "\"/>";
                    }
                    
                    $instruction_cell->addData("Transfer from <b>$m1_name</b> $from_string to <b>$m2_name</b> $to_string");
                }
                else {
                    // this is a regular segment
                    $output_string = "Take ";
                    $lines = $segment->lines;
                    for ($i = 0; $i < count($lines); $i++) {
                        $output_string .= "<a href=\"javascript:void(0);\" onclick=\"window.open('$website" . $lines[$i]->url . "');\"><img width=\"20\" src=\"images/" . $lines[$i]->img . "\"/></a>";
                    }
                    $output_string .= " from <b>$m1_name</b> to <b>$m2_name</b>.";
                    $instruction_cell->addData($output_string);
                    $old_lines = $lines;
                }
                $row->addCell($step_cell);
                $row->addCell($instruction_cell);
                $row->addCell($time_cell);
                $directions_table->addRow($row);
            }
            $row = new Row(null, "total_row");
            $row->addCell(new Cell(null, ""));
            $row->addCell(new Cell("total", "Total Time:"));
            $row->addCell(new Cell("total_time", getTimeString($path_time)));
            
            $directions_table->addRow($row);
            
            echo $directions_table->toString();
        }
        else {
            echo "Sorry. No path found.<br/>\n";
        }
    }
?>
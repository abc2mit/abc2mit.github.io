<?php
    if ($type == 'dir') {
        $saddr = $_GET['saddr'];
        $daddr = $_GET['daddr'];
        if ($saddr == null || $saddr == "") {
            $type = "location";
            $location_string = $daddr;
            $daddr = fixSearchString($daddr);
        }
        else if ($daddr == null || $daddr == "") {
            $type = "location";
            $location_string = $saddr;
            $saddr = fixSearchString($saddr);
        }
        else {
            $saddr = fixSearchString($saddr);
            $daddr = fixSearchString($daddr);
            if ($debug) {
                echo "saddr: $saddr<br/>";
                echo "daddr: $daddr<br/>"; 
            }
            // check for new york and ny
            //$saddr = checkString($saddr, $city);
            //$saddr = checkString($saddr, $state);
            $saddr = validateSearchString($saddr, $city, $state);
            $starts = getLocations2($saddr);
            // check for new york and ny
            //$daddr = checkString($daddr, $city);
            //$daddr = checkString($daddr, $state);
            $daddr = validateSearchString($daddr, $city, $state);
            $ends = getLocations2($daddr);
            // log
            if ($logging) {
                logDirectionsQuery($_GET['saddr'], $_GET['daddr']);
            }
            
            // only one of each, so we can calculate the path
            if ((count($starts) == 1) && (count($ends) == 1)) {
                $start_location = $starts[0];
                $end_location = $ends[0];
                
                #echo "start_location = " . $start_location . "<br/>";
                #echo "end_location = " . $end_location . "<br/>";
                if ($start_location != false && $end_location != false) {
                    // find three closest starting stations
                    $start_stations = findNearestStations2($start_location);
                    // find three closest ending stations
                    $end_stations = findNearestStations2($end_location);
                    $path = findDirPath($start_location, $end_location, $start_stations, $end_stations);
                }
            }
        }
    }
    else if ($type == 'dir_alt') {
        /*
            this is new. we generated directions, but the user wants to leave from a different station
            and/or arrive at a different station.
        */
        // get the start address
        $s = $_GET['saddr'];
        // get the end address
        $e = $_GET['daddr'];
        if ($debug) {
            echo "saddr: $s<br/>";
            echo "daddr: $e<br/>"; 
        }
        $starts = getLocations2($s);
        if ($debug) {
            echo "start locations:<br/>";
            print_r($starts);
            echo "<br/>";
        }
        $ends = getLocations2($e);
        if ($debug) {
            echo "end locations:<br/>";
            print_r($ends);
            echo "<br/>";
        }
        $start_station_marker = $markers[$_GET['start']];
        $end_station_marker = $markers[$_GET['end']];
        // find the path
        $path = findS2SPath($start_station_marker, $end_station_marker);
        // add two segments to the marker
        $start_location = $starts[0];
        $end_location = $ends[0];
        // even though we shouldn't, we'll add do it directly.
        $start_marker = new Marker($start_location->point->lat,$start_location->point->lng,"start",$start_location->desc);
        $segment = new Segment($start_marker,$start_station_marker,$start_station_marker->lines,distance($start_marker,$start_station_marker)/$WALKING_SPEED,"walking");
        array_unshift($path->path,$segment);
        $path->time += $segment->t;
        // now add the last marker
        $end_marker = new Marker($end_location->point->lat,$end_location->point->lng,"end",$end_location->desc);
        $segment = new Segment($end_station_marker,$end_marker,null,distance($end_station_marker,$end_marker)/$WALKING_SPEED,"walking");
        $path->addSegment($segment,null);
        // find three closest starting stations
        $start_stations = findNearestStations2($start_location);
        // find three closest ending stations
        $end_stations = findNearestStations2($end_location);
    }
    else if ($type == 's2s') {
        // get starting point
        $start_marker = $markers[$_GET['start']];
        // get ending point
        $end_marker = $markers[$_GET['end']];
        // log
        if ($logging) {
            logS2SQuery($_GET['start'],$_GET['end']);
        }
        // find the path
        $path = findS2SPath($start_marker, $end_marker);
    }
    if ($type == 'location') {
        $loc = $_GET['l'];
        if ($location_string != null || $location_string != "") {
            $loc = $location_string;
        }
        // log
        if ($logging) {
            logLocationQuery($loc);
        }
        if ($debug) {
            echo "location=$loc<br/>";
        }
        $loc = fixSearchString($loc);
        // check for new york and ny
        $fix_loc = checkString($loc, $city);
        $fix_loc = checkString($fix_loc, $state);
        $locations = getLocations2($fix_loc);
        if (count($locations) == 1) {
            // grab stations
            $stations = findNearestStations2($locations[0]);
        }
    }
    echo "<!-- type is /" . $type . "/ -->\n";
?>
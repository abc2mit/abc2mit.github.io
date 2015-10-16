<?php
    #
    #   transitcompass.com
    #   search2.php
    #   v0.1
    #
    #   Integrated into main.php, this file is used to prepare the incoming parameters for the search required.
    #
    
    /*
        We no longer have types of searches, rather we determine based on the incoming. That way, we are more flexible with the inputs. However, we will still determine a "type" based on what we get. There should be four parameters as listed:
        sid: start station id
        did: destination station id
        
        sa: start address
        da: destination address
        
        These are used accordingly in the following cases:
        1. Address to Address. Standard searching, we go from point A to point B using the shortest method possible.
            sid: null
            did: null
            sa: not null
            da: not null
        
        2. Address to Station. This query goes from a point A to a station B.
            sid: not null
            did: null
            sa: not null, should match name of sid
            da: not null
            
            Alternate case: did is not null and sid is null. sa is address, da is name of did.
        
        3. Station to Station. This query goes from a station A to a station B.
            sid: not null
            did: not null
            sa: not null, should match name of sid
            da: not null, should match name of did
        
        4. Location. This query is to find the nearest stations to point A.
            sid: null
            did: null
            sa: not null
            da: null
            
            Alternate case: da is not null and sa is null.
    */
    $debug = false;
    $sid = $_GET['sid'];
    $did = $_GET['did'];
    $sa = $_GET['sa'];
    $da = $_GET['da'];
    $type = "default";
    if ($debug) {
        echo "*** incoming ***<br/>";
        echo "sid: $sid<br/>";
        echo "did: $did<br/>";
        echo "sa: $sa<br/>";
        echo "da: $da<br/>";
        echo "*** end incoming ***<br/>";
    }
    
    if (($sid == "") && ($sa != "")) {
        $start_query = "SELECT * FROM markers WHERE name='" . $sa . "'";
        if ($debug) {
            echo "start query: $start_query<br/>";
        }
        $start_result = mysql_query($start_query) or die("unable to run search query " . mysql_error());
        if ($marker = mysql_fetch_array($start_result, MYSQL_BOTH)) {
            // if we're here, then that means that there is a result
            // and there should only be one result!
            $sid = $marker['id'];
        }
    }
    
    if (($did == "") && ($da != "")) {
        $end_query = "SELECT * FROM markers WHERE name='" . $da . "'";
        if ($debug) {
            echo "end query: $end_query<br/>";
        }
        $end_result = mysql_query($end_query) or die("unable to run search query " . mysql_error());
        if ($marker = mysql_fetch_array($end_result, MYSQL_BOTH)) {
            // if we're here, then that means that there is a result
            // and there should only be one result!
            $did = $marker['id'];
        }
    }
    
    // determine type
    if ($_GET['type'] == "dir_alt") {
        $type = $_GET['type'];
    }
    else if (($sa != "") && ($da != "") && ($sid == "") && ($did == "")) {
        // A2A
        $type = "dir";
    }
    else if (($sid != "") && ($did != "")) {
        $type = "s2s";
    }
    else if (($sid != "") && ($da != "")) {
        $type = "s2a";
    }
    else if (($sa != "") && ($did != "")) {
        $type = "a2s";
    }
    else if (($sid == "") && ($did == "") && ($sa != "" || $da != "")) {
        $type = "location";
    }
    else if (($sid == "") && ($did == "") && ($sa == "") && ($da == "")) {
        // this is a starting page. There's no query here, so don't do anything.
    }
    else {
        // this is currently unsupported
        echo "unsupported search!<br/>";
    }
        
    if ($debug) {
        echo "*** outgoing ***<br/>";
        echo "type: $type<br/>";
        echo "sid: $sid<br/>";
        echo "did: $did<br/>";
        echo "sa: $sa<br/>";
        echo "da: $da<br/>";
        echo "*** end outgoing ***<br/>";
    }
    
    // search based on type
    if ($type == "dir") {
        // log
        if ($logging) {
            logDirectionsQuery($_GET['sa'], $_GET['da']);
        }
        // correct the strings
        $sa = fixSearchString($sa);
        $da = fixSearchString($da);
        if ($debug) {
            echo "sa: $sa<br/>";
            echo "da: $da<br/>";
        }
        $sa = validateSearchString($sa, $city, $state);
        $starts = getLocations2($sa);
        
        $da = validateSearchString($da, $city, $state);
        $ends = getLocations2($da);
        
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
    else if ($type == "s2s") {
        // S2S
        // get starting point
        $start_marker = getMarker($sid);
        // get ending point
        $end_marker = getMarker($did);
        // log
        if ($logging) {
            logS2SQuery($sid, $did);
        }
        // find the path
        $path = findS2SPath($start_marker, $end_marker);    
    }
    else if ($type == "location") {
        // location
        if ($sa != "") {
            $loc = $sa;
        }
        else {
            $loc = $da;
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
        //$fix_loc = checkString($loc, $city);
        //$fix_loc = checkString($fix_loc, $state);
        $fix_loc = validateSearchString($loc, $city, $state);
        $locations = getLocations2($fix_loc);
        if (count($locations) == 1) {
            // grab stations
            $stations = findNearestStations2($locations[0]);
        }
    }
    else if ($type == "s2a") {
        $da = fixSearchString($da);
        $da = validateSearchString($da, $city, $state);
        if ($debug) {
            echo "da: $da<br/>";
        }
        $ends = getLocations2($da);
        if ($debug) {
            echo $ends[0]->printInfo();
        }
        $end_location = $ends[0];
        $end_stations = findNearestStations2($end_location);
        
        $start_marker = getMarker($sid);
        if ($debug) {
            echo $start_marker->printInfo();
        }
        $path = findS2APath($start_marker, $end_stations, $end_location);
    }
    else if ($type == "a2s") {
        $sa = fixSearchString($sa);
        $sa = validateSearchString($sa, $city, $state);
        $starts = getLocations2($sa);
        $start_location = $starts[0];
        $start_stations = findNearestStations2($start_location);
        
        if ($debug) {
            echo $end_location->printInfo();
        }
        $end_marker = getMarker($did);
        if ($debug) {
            echo $end_marker->printInfo();
        }
        
        $path = findA2SPath($start_location, $start_stations, $end_marker);
    }
    else if ($type == "dir_alt") {
         /*
            this is new. we generated directions, but the user wants to leave from a different station
            and/or arrive at a different station.
        */
        // get the start address
        $s = $_GET['sa'];
        // get the end address
        $e = $_GET['da'];
        if ($debug) {
            echo "sa: $s<br/>";
            echo "da: $e<br/>"; 
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
        $start_station_marker = getMarker($_GET['start']);
        $end_station_marker = getMarker($_GET['end']);
        // find the path
        $path = findS2SPath($start_station_marker, $end_station_marker);
        // add two segments to the marker
        $start_location = $starts[0];
        $end_location = $ends[0];
        // add the segment directly to the Path.
        // shouldn't because it's a violation of abstraction, but I haven't developed the API yet.
        $start_marker = new Marker($start_location->point->lat,$start_location->point->lng,"start",$start_location->getAddressString());
        $segment = new Segment($start_marker,$start_station_marker,$start_station_marker->lines,distance($start_marker,$start_station_marker)/$WALKING_SPEED,"walking");
        // add segment directly to the beginning of the path
        array_unshift($path->path,$segment);
        $path->time += $segment->t;
        // now add the last marker
        $end_marker = new Marker($end_location->point->lat,$end_location->point->lng,"end",$end_location->getAddressString());
        $segment = new Segment($end_station_marker,$end_marker,null,distance($end_station_marker,$end_marker)/$WALKING_SPEED,"walking");
        $path->addSegment($segment,null);
        // find three closest starting stations
        $start_stations = findNearestStations2($start_location);
        // find three closest ending stations
        $end_stations = findNearestStations2($end_location);
    }
?>
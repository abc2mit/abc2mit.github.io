<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorith are prohibited by law.
    #

/**
    For each segment, the first marker and the second marker of the same segment will have the same lines. We check the line used. If it does not exist in second segment's second marker, then we know that the first marker is a junction point and needs to have a transfer.
    
    @param $path[]<Segment> the list of path segments
    @param $type<String> the type of connection (s2s,dir)
*/
function consolidatePath ($path, $type) {
    $cpath = array();
    $debug = false;
    $path_array = $path->path;
    $count = count($path_array);
    // this is a direction path and there is only one or two paths.
    // that means it either goes from loc->station->loc or just loc->loc
    if ($type == "dir" && $count <= 2) {
        return "walk";
    }
    //set up the first segment
    $segment = $path_array[0];
    if ($type == "s2s") {
        $i = 1;   
    }
    else {
        $cpath[] = $segment;
        $segment = $path_array[1];
        $i = 2;
    }
    $marker1 = $segment->m1;
    $current_lines = $segment->m1->getOverlapLines($segment->m2);
    $connection = $segment->connection;
    $total_time = $segment->t;
    $segment2 = $path_array[$i+1];
    // loop through the path, starting at the next segment.
    for (; $i < $count; $i++) {
        // grab the segment
        $segment2 = $path_array[$i];
        
        if ($debug) {
            echo "@" . $segment2->m1->name . "<br/>";
            echo "lines = ";
            foreach($current_lines as $line) {
                echo $line->name . " ";
            }
            echo "<br/>";
        }
        
        if ($connection == "transfer") {
            // we know this segment is a transfer, so they will have to get off the train
            if ($debug) {
                echo "transfer<br/>";
            }
            
            // end the segment
            $new_segment = new Segment($marker1, $segment2->m1, $current_lines, $total_time, $connection);
            if ($debug) {
                echo "segment = " . $new_segment->toString() . "<br/>";
            }
            $cpath[] = $new_segment;
            
            // add the transfer segment
            // we need to get the transfer lines
            $lines = $segment2->m1->getOverlapLines($segment2->m2);
            $lines = getCorrectLines($lines, $segment2->connection);
            // create new segment
            $new_segment = new Segment($segment2->m1, $segment2->m2, $lines, $segment2->t, $segment2->connection);
            $cpath[] = $new_segment;
            
            // increment if we need to
            if ($i < $count) {
                // increment
                $i++;
                $segment = $path_array[$i];
                $marker1 = $segment->m1;
                echo "marker = " . $marker1 . "<br/>";
                echo "segment marker = " . $segment->m1->name . "<br/>";
                $current_lines = $segment->m1->getOverlapLines($segment->m2);
                $connection = $segment->connection;
                $total_time = $segment->t;
                if ($i < $count) {
                    // more segments available
                    continue;
                }
                // we end here with this segment
                $new_segment = new Segment($segment->m1, $segment->m2, $current_lines, $total_time, $connection);
                $cpath[] = $new_segment;
                return $cpath;
            }
        }
        // not a transfer
        
        $segment_lines = array();
        // find out which lines fit the segment
        foreach ($segment2->m1->lines as $line) {
            if ($debug) {
                echo "looking @ line: " . $line->name . "<br/>";
            }
            // check the connections and find which one to go to
            foreach ($line->connections as $line_connection) {
                if ($debug) {
                    echo "looking @ connection: " . $line_connection->id . "<br/>";
                }
                if ($line_connection->id == $segment2->m2->id) {
                    $line_name = $line->name;
                    if ($debug) {
                        echo "adding line $line_name<br/>";
                    }
                    $segment_lines["$line_name"] = $line;
                    break;
                }
            }
        }
        // we found all the lines that go to the marker that is next
        // now compare them to the current lines we have
        $new_lines = linesIntersect($current_lines, $segment_lines);
        if (count($new_lines) == 0) {
            // no overlapping lines
            // end the old segment and add to the path
            $new_segment = new Segment($marker1, $segment2->m1, $current_lines, $total_time, $connection);
            if ($debug) {
                echo "new_segment = " . $new_segment->toString() . "<br/>";
            }
            $cpath[] = $new_segment;
            
            // reset our variables for the next segment
            $segment = $segment2;
            $marker1 = $segment2->m1;
            $current_lines = $segment2->m1->getOverlapLines($segment2->m2);
            $connection = $segment2->connection;
            $total_time = $segment2->t;
            continue;
        }
        // we move forward
        if ($debug) {
            echo "# current_lines: " . count($new_lines) . "<br/>";                
            foreach($new_lines as $line) {
                echo $line->name . " ";
            }
            echo "<br/>";
        }
        $current_lines = $new_lines;
        $connection = $segment2->connection;
        $total_time += $segment2->t;
    } // for
    // add the last segment
    if ($marker1->id != $segment2->m2->id) {
        $new_segment = new Segment($marker1, $segment2->m2, $current_lines, $total_time, $connection);
        $cpath[] = $new_segment;
    }
    return $cpath;
}

/**
    For each segment, the first marker and the second marker of the same segment will have the same lines. We check the line used. If it does not exist in second segment's second marker, then we know that the first marker is a junction point and needs to have a transfer.
    
    @param $path[]<Segment> the list of path segments
    @param $type<String> the type of connection (s2s,dir)
    
    @deprecated
*/
function consolidatePath2($path, $type) {
    $cpath = array();
    /*
        What are the conditions that determine a transfer?
        1. If the connection type is different (express -> local)
        2. If the train in the next segment is different and the original one is not available
        3. if the connection type is transfer
    */
    // debugging
    $debug = true;
    $path_array = $path->path;
    $count = count($path_array);
    // this is a direction path and there is only one or two paths.
    // that means it either goes from loc->station->loc or just loc->loc
    if ($type == "dir" && $count <= 2) {
        return "walk";
    }
    //set up the first segment
    $segment = $path_array[0];
    if ($type == "s2s") {
        $i = 1;   
    }
    else {
        $cpath[] = $segment;
        $segment = $path_array[1];
        $i = 2;
    }
    $marker1 = $segment->m1;
    $current_lines = $segment->m1->getOverlapLines($segment->m2);
    $connection = $segment->connection;
    $total_time = $segment->t;
    $segment2 = $path_array[$i+1];
    // loop through the path, starting at the next segment.
    for (; $i < $count; $i++) {
        // grab the segment
        $segment2 = $path_array[$i];
        
        if ($debug) {
            echo "@" . $segment2->m1->name . "<br/>";
            echo "lines = ";
            foreach($current_lines as $line) {
                echo $line->name . " ";
            }
            echo "<br/>";
        }
        
        if ($connection == "transfer") {
            // we know this segment is a transfer, so they will have to get off the train
            echo "transfer<br/>";
            
            // end the segment
            $new_segment = new Segment($marker1, $segment2->m1, $current_lines, $total_time, $connection);
            if ($debug) {
                echo "segment = " . $new_segment->toString() . "<br/>";
            }
            $cpath[] = $new_segment;
            
            // add the transfer segment
            // we need to get the transfer lines
            $lines = $segment2->m1->getOverlapLines($segment2->m2);
            $lines = getCorrectLines($lines, $segment2->connection);
            // create new segment
            $new_segment = new Segment($segment2->m1, $segment2->m2, $lines, $segment2->t, $segment2->connection);
            $cpath[] = $new_segment;
            
            // increment if we need to
            if ($i < $count) {
                // increment
                $i++;
                $segment = $path_array[$i];
                $marker1 = $segment->m1;
                $current_lines = $segment->m1->getOverlapLines($segment->m2);
                $connection = $segment->connection;
                $total_time = $segment->t;
                if ($i < $count) {
                    // more segments available
                    continue;
                }
                // we end here with this segment
                $new_segment = new Segment($segment->m1, $segment->m2, $current_lines, $total_time, $connection);
                $cpath[] = $new_segment;
                return $cpath;
            }
        }
        else if ($connection != $segment2->connection) {
            // the connection types are different, but trains can shift from express to local and vice versa.
            if ($debug) {
                echo "old conn: " . $connection . " | new conn: " . $segment2->connection . "<br/>";
            }
            // do we have the same train available?
            if (sameTrain($current_lines, $segment2)) {
                // it's the same train, so we just update our lines and continue
                $current_lines = updateLines($segment, $current_lines);
                $total_time += $segment2->t;
                $connection = $segment2->connection;
                continue;
            }
            // different trains, so we need to transfer
            
            // end the old segment and add to the path
            $new_segment = new Segment($marker1, $segment2->m1, $current_lines, $total_time, $connection);
            if ($debug) {
                echo "new_segment = " . $new_segment->toString() . "<br/>";
            }
            $cpath[] = $new_segment;
            
            // reset our variables for the next segment
            $segment = $segment2;
            $marker1 = $segment2->m1;
            $current_lines = $segment2->m1->getOverlapLines($segment2->m2);
            $connection = $segment2->connection;
            $total_time = $segment2->t;
            continue;
        }
        else if (!sameTrain($current_lines, $segment2)) {
            // different trains, so we need to transfer
            if ($debug) {
                echo "old lines: ";
                foreach($current_lines as $line) {
                    echo $line->name . " ";
                }
                echo "<br/>";
                echo "new line: " . $segment2->lines[0]->name . "<br/>";
            }            
            // end the old segment and add to the path
            $new_segment = new Segment($marker1, $segment2->m1, $current_lines, $total_time, $connection);
            if ($debug) {
                echo "new_segment = " . $new_segment->toString() . "<br/>";
            }
            $cpath[] = $new_segment;
            
            // reset our variables for the next segment
            $segment = $segment2;
            $marker1 = $segment2->m1;
            $current_lines = $segment2->m1->getOverlapLines($segment2->m2);
            $connection = $segment2->connection;
            $total_time = $segment2->t;
            continue;
        }
        // otherwise, we keep going and merge the segments
        $current_lines = linesIntersect($current_lines, $segment2->m1->getOverlapLines($segment2->m2));
        if ($debug) {
            echo "# current_lines after intersect: " . count($current_lines) . "<br/>";                
            foreach($current_lines as $line) {
                echo $line->name . " ";
            }
            echo "<br/>";
        }
        $current_lines = getCorrectLines($current_lines, $connection);
        if ($debug) {
            echo "# current_lines after correction: " . count($current_lines) . "<br/>";                
            foreach($current_lines as $line) {
                echo $line->name . " ";
            }
            echo "<br/>";
        }
        $connection = $segment2->connection;
        $total_time = $segment2->t;
    } // for
    // add the last segment
    $new_segment = new Segment($marker1, $segment2->m2, $current_lines, $total_time, $connection);
    $cpath[] = $new_segment;    
    return $cpath;
}

function linesIntersect($current_lines, $segment_lines) {
    $intersect = array();
    foreach ($current_lines as $cline) {
        foreach ($segment_lines as $sline) {
            if ($cline->name == $sline->name) {
                $intersect[] = $sline;
                break;
            }
        }
    }
    return $intersect;
}

function sameTrain($current_lines, $segment) {
    foreach ($current_lines as $line) {
        if ($line->name == $segment->lines[0]->name) {
            // we do have the same train, not a transfer
            return true;
        }
    }
    return false;
}

function updateLines($segment, $current_lines) {
    $segment_lines = $segment->m1->getOverlapLines($segment->m2);
    $current_lines = linesIntersect($current_lines, $segment_lines);
    return $current_lines;

}

function getCorrectLines($lines, $connection_type) {
    $new_lines = array();
    foreach ($lines as $line) {
        $add_line = false;
        $connections = $line->connections;
        foreach ($connections as $connection) {
            if ($connection->type == $connection_type) {
                $add_line = true;
                break;
            }
        }
        if ($add_line) {
            $new_lines[] = $line;
        }
    }
    return $new_lines;
}

function getLineNames($lines) {
    $names = array();
    foreach ($lines as $line) {
        $names[] = $line->name;
    }
    return $names;
}

/**
    Finds the distance between two locations.
*/
function distance2Loc($loc1, $loc2) {
    $miles_per_degree_latitude = 69.023;
    $miles_per_degree_longitude = 51.075;
    //$lat = $marker->point->lat;
    //echo "marker lat = $lat<br/>\n";
    $m1_lat = $miles_per_degree_latitude * $loc1->getLat();
    $m1_lng = $miles_per_degree_longitude * $loc1->getLng();
    $m2_lat = $miles_per_degree_latitude * $loc2->getLat();
    $m2_lng = $miles_per_degree_longitude * $loc2->getLng();
    
    return sqrt(pow(($m1_lat - $m2_lat),2) + pow(($m1_lng - $m2_lng),2));
}

function distance($m1, $m2) {
    $miles_per_degree_latitude = 69.023;
    $miles_per_degree_longitude = 51.075;
    $m1_lat = $miles_per_degree_latitude * $m1->getLat();// / 1000000;
    $m1_lng = $miles_per_degree_longitude * $m1->getLng();// / 1000000;
    $m2_lat = $miles_per_degree_latitude * $m2->getLat();// / 1000000;
    $m2_lng = $miles_per_degree_longitude * $m2->getLng();// / 1000000;
    //echo "algorithm: (x1, y1) = ($m1_lat, $m1_lng)<br/>\n";
    //echo "algorithm: (x2, y2) = ($m2_lat, $m2_lng)<br/>\n";
    
    return sqrt(pow(($m1_lat - $m2_lat),2) + pow(($m1_lng - $m2_lng),2));
}
    
/*
	Description of the algorithm
	TODO	
*/

/**
    Find a path from the start marker to the end marker using the shortest distance.
*/
function findAPath($open, $end_stations, $end_marker)
{
    $debug = false;
    if ($debug) {
        echo "findAPath<br/>";
    }
    $WALKING_SPEED = 2 / 3600;
    $STATION_STOP_TIME = 30;
    $TRANSFER_TIME = 45;
    global $markers;
    $new_open = array();
    //print_r($open);
    //echo "<br/>";
    $time = -1;
    $open_path = null;
    foreach ($open as $open_array) {
        if ($time == -1) {
            $time = $open_array[0]->time;
            $open_path = $open_array[0];
        }
        ##echo "open_path " . $open_path->toString() . ":$time<br/>";
        for($i = 0; $i < count($open_array); $i++) {
            $opath = $open_array[$i];
            if ($opath->time < $time) {
                #echo "overwritting...<br/>";
                $time = $opath->time;
                $open_path = $opath;
            }        
        }
    }
    // determine the distance to the end
    $last_marker = $open_path->getLastMarker();
    $distance = distance($last_marker, $end_marker);
    $open_array2 = $new_open["$distance"];
    if (is_null($open_array2)) {
        $open_array2 = array();
    }
    $open_array2[] = $open_path;
    ##echo "adding " . $open_path->toString() . ":" . $distance . "<br/>";
    $new_open["$distance"] = $open_array2;
    
    while (count($new_open) > 0) {
        // sort the array
        ksort($new_open);
        // grab the first path since it is closest to the destination
        $open_array = array_shift($new_open);
        if (!is_array($open_array)) {
            return null;
        }
        $path = array_shift($open_array);
        ##echo "looking at path: " . $path->toString() . "<br/>";
        
        $last_marker  = $path->getLastMarker();
        foreach ($end_stations as $station) {
            $station_marker = $station->marker;
            if ($last_marker == $station_marker) {
                $path->addSegment(new Segment($last_marker, $end_marker, null, $station->getDistance()/$WALKING_SPEED, "walking"), null);
                #echo "first path found...<br/>";
                return $path;
            }
        }
        // get the next set of markers
        foreach ($last_marker->lines as $line) {
            foreach ($line->connections as $connection) {
                $next_marker = $markers[$connection->id];
                if (!in_array($next_marker, $path->visited)) {
                    $old_distance = distance($last_marker, $end_marker);
                    $distance = distance($next_marker, $end_marker);
                    #echo "old_marker = " . $last_marker->toString() . ",$old_distance<br/>";
                    #echo "marker = " . $next_marker->toString() . ",$distance<br/>";
                    /*
                        The 1.25 is a random factor that allows the distance to work as planned. It's a bit strange because of how the subway system is designed.
                        From 1st Ave -> Astor Pl, it moves to 3rd Ave. However, the distance to Astor Pl from 3rd Ave is less than the distance from Union Square
                        to Astor Pl. Only this factor will allow that path to be added.
                    */
                    if ($distance < $old_distance*1.25) {
                        #echo "adding marker<br/>";
                        // add to the path purely by distance. we don't care whether it is the fastest connection, but that it is one.
                        $new_path = new Path2($path, $next_marker, $line, $connection->duration + $STATION_STOP_TIME + $TRANSFER_TIME, $connection->type, null, $path->visited, 0);
                        foreach ($path->getLastSegment()->lines as $last_line) {
                            if ($line->name == $last_line->name) {                                
                                $new_path = new Path2($path, $next_marker, $line, $connection->duration + $STATION_STOP_TIME, $connection->type, null, $path->visited, 0);
                                #echo "no transfer..." . $line->name . " | " . $new_path->toString() . "<br/>";
                                break;
                            }
                        }
                        $open_array = $new_open["$distance"];
                        if (is_null($open_array)) {
                            $open_array = array();
                        }
                        $open_array[] = $new_path;
                        $new_open["$distance"] = $open_array;
                    }
                }
            }
        }
    }
    return null;
}

/**
	
	@param $start<Location> the starting location (address)
	@param $end<Location> the ending location (address)
*/
function findDirPath ($start, $end, $start_stations, $end_stations)
{
    if ($start == $end) {
        return "same";
    }
    if (distance($start,$end) < 0.25) {
        return "walk";
    }
    $WALKING_SPEED = 2 / 3600;
    #echo "findDirPath<br/>";
	// determine original bounding box
	$original_box = new Box($start->point, $end->point);
	// create end marker
	$end_marker = new Marker($end->point->lat,$end->point->lng,"end",$end->desc);
	// create array of paths
	$open = array();
	foreach($start_stations as $station) {
        $start_marker = new Marker($start->point->lat,$start->point->lng,"start",$start->desc);
        $segment = new Segment($start_marker,$station->marker,$station->marker->lines,$station->getDistance()/$WALKING_SPEED, "walking");
        $visited = array();
        $visited[] = $station->marker;
        $new_box = new Box($station->marker->point,$end_marker->point);
        $path = new Path2($segment, $new_box, $visited);
        $area = $new_box->getArea();
        $array = $open["$area"];
        if ($array == null) {
            $array = array();
        }
        $array[] = $path;
        #echo "adding $array<br/>";
        $open["$area"] = $array;
	}
	return findPath($open, $end_stations, $end_marker);
}

/**
    @param $start_station<Marker>
    @param $end_station<Marker>
*/
function findS2SPath ($start_marker, $end_marker)
{
    $debug = false;
    if ($debug) {
        echo "findS2SPath<br/>";
    }
    if ($start_marker == $end_marker) {
        return "walk";
    }
    global $markers;
    // determine original bounding box
    $original_box = new Box($start_marker->point, $end_marker->point);
    // create array of paths
    $open = array();
    foreach($start_marker->getLines() as $line) {
        foreach($line->getConnections() as $connection) {
            // get associated marker
            $marker = $markers[$connection->id];
            if ($debug) {
                echo "retrieved marker from connection (" . $connection->id . "): ";
                $marker->printInfo();
            }
            $lines = $start_marker->getOverlapLines($marker);
            $segment = new Segment($start_marker, $marker, $lines, $connection->duration,$connection->type);
            $visited = array();
            $visited[] = $start_marker;
            $visited[] = $marker;
            $new_box = new Box($marker->point,$end_marker->point);
            $path = new Path2($segment, $new_box, $visited);
            if ($marker == $end_marker) {
                return $path;
            }
            $area = $new_box->getArea();
            $array = $open["$area"];
            if ($array == null) {
                $array = array();
            }
            $array[] = $path;
            $open["$area"] = $array;
        }
    }
    $end_stations = array();
    $end_stations[] = new Station2($end_marker, 0);
    return findPath($open, $end_stations, $end_marker);
}

/**
    @param $end_marker<Marker> can be null if S2S search
    @return<Path2> a path from the start to the end or null if none is found
*/
function findPath ($open, $end_stations, $end_marker)
{
    $debug = false;
    $debug_all = false;
    $WALKING_SPEED = 2 / 3600;
    $STATION_STOP_TIME = 30;
    $TRANSFER_TIME = 45;
	$completed_time = -1;
	$completed_stops = -1;
	if ($debug) {
        echo "findPath<br/>";
    }
	global $markers;
	// create array of completed paths
	$completed = array();
	$one_path = findAPath($open, $end_stations, $end_marker);
	if ($one_path != null) {
        if ($debug) {
            echo "path by shortest distance: " . $one_path->toString() . "<br/>";
        }
        $completed[] = $one_path;
        // set time
        $completed_time = $one_path->time;
        $completed_stops = count($one_path->path);
	}
	// create closed paths
	$closed = array();
	$restart = false;
	#$counter = 0;
	$multiple_paths = array();
	while (count($open) > 0) {
        #echo "loop: $counter<br/>";
        #$counter++;
        if (!is_array($open)) {
            return null;
        }
        if ($debug_all) {
            echo "open size = " . count($open) . "<br/>";
        }
        ksort($open);
	    // look at the path at the front of OPEN, remove from list
        $keys = array_keys($open);
        $key = $keys[0];
        $multiple_paths = array_shift($open);
        if (is_array($multiple_paths)) {
            $path = array_shift($multiple_paths);
        }
        else {
            return null;
        }
        if (count($multiple_paths) > 0) {
            $open["$key"] = $multiple_paths;
        }
        
        if ($completed_time > 0 && $completed_time < $path->time) {
            if ($debug_all) {
                echo "path is too long ($completed_time < " . $new_path2->time . "). discard.<br/>";
            }
            continue;
        }
        $par = $completed_time * pow($completed_stops,2);
        if ($completed_stops > 0 && $completed_time > 0) {
            $metric = pow(count($path->path),2)*$path->time;
            #echo "metric = $metric | par = $par<br/>";
            if ($par/$metric < 0.8) {
                if ($debug_all) {
                    echo "path metric is too big (" . $par/$metric . "). discard.<br/>";
                }
                continue;
            }
        }
        
        $boundary_box = $path->bounding_box;
        #echo "box area = " . $boundary_box->getArea() . "<br/>";
        // print path
        if ($debug_all) {
            echo $path->toString();
        }
        // find degree of freedom for each station at the end of each path
        // DoF is defined as the number of outbound connections from the marker
        $last_marker = $path->getLastMarker();
        
        //$box = new Box($last_marker->point, $end_marker->point);
        #echo "box area = " . $box->getArea() . "<br/>";
        
        #$stl_marker = $path->getSTLMarker();
        #echo $stl_marker->toString() . " looking at marker = [" . $last_marker->toString() . "]<br/>";
        $lines_array = $last_marker->getLines();
        $DoF = getDegreeOfFreedom($lines_array);
        $connection_added = array();
        if ($DoF - 1 <= 1) {
            // station only has two connections
            // grab the next station
            $old_marker = $path->getSTLMarker();
            // loop through each connection and find the new marker
            #echo "connections = <br/>";
            foreach ($lines_array[0]->getConnections() as $connection) {
                #echo $connection->id . ": ";
                if ($connection->id != $old_marker->id) {
                    // new marker found, add to path
                    $next_marker = $markers[$connection->id];
                    $original_dist = $last_marker->point->getDistanceMiles($end_marker->point);
                    $dist = $next_marker->point->getDistanceMiles($end_marker->point);
                    #echo "p2p distance = $dist ";
                    #echo "from " . $next_marker->toString() . "<br/>";
                    // calculate the box
                    if ($single_path_length < 3) {
                        $new_box = new Box($next_marker->point, $end_marker->point);
                    }
                    else {
                        $new_box = null;
                    }
                    $single_path_length = $path->single_path_length;
                    if ($original_dist < $dist && $single_path_length > 2) {
                        if ($new_box->getArea() > $boundary_box->getArea()) {
                            // outside our scope, move to closed list
                            ##echo "spl: $single_path_length :: bounding box (". $next_marker->id .") is too big: " . $new_box->getArea() . ">" . $boundary_box->getArea() . ". will not add path...<br/>";
                            $closed[] = $path;
                            continue;
                        }
                    }
                    $single_path_length++;
                    if (in_array($next_marker, $path->visited)) {
                        if ($debug_all) {
                            echo "connection (" . $connection->id . ") is backtracking to " . $next_marker->toString() .". ignoring.<br/>";
                        }
                        continue;
                    }
                    
                    if (in_array($next_marker->id, $connection_added)) {
                        if ($debug_all) {
                            echo "[$next_marker->id] marker was already added. skipping.<br/>\n";
                        }
                        continue;
                    }
                    $new_path = new Path2($path, $next_marker, $lines_array[0], $connection->duration + $STATION_STOP_TIME, $connection->type, $new_box, $path->visited, $single_path_length);
                    $connection_added[] = $next_marker->id;
                    
                    // check to see if the new station is one of the end stations. if so, then mark the path as completed and remove from OPEN
                    foreach ($end_stations as $station) {
                        if ($station->marker == $next_marker) {
                            if ($end_marker != $next_marker) {
                                // add end location marker
                                $new_path2 = new Path2($new_path, $end_marker, null, $station->getDistance()/$WALKING_SPEED, "walking", $box, $new_path->visited, $single_path_length);
                            }
                            else {
                                $new_path2 = $new_path;
                            }
                            if ($completed_time > 0 && $completed_time < $new_path2->time) {
                                if ($debug_all) {
                                    echo "path is too long ($completed_time < " . $new_path2->time . "). discard.<br/>";
                                }
                                continue;
                            }
                            $par = $completed_time * pow($completed_stops,2);
                            if ($completed_stops > 0 && $completed_time > 0) {
                                $metric = pow(count($path->path),2)*$path->time;
                                if ($par/$metric < 0.75) {
                                    if ($debug_all) {
                                        echo "path metric is too big (" . $par/$metric . "). discard.<br/>";
                                    }
                                    continue;
                                }
                            }
                            // add path to COMPLETED and remove from OPEN
                            if ($debug) {
                                echo "adding (" . $new_path2->toString() . ") to COMPLETED<br/>";
                            }
                            $completed[] = $new_path2;
                            if ($completed_time < 0 || $completed_time > $new_path2->time) {
                                $completed_time = $new_path2->time;
                                $completed_stops = count($new_path2->path);
                            }
                            if ($debug_all) {
                                echo "completed time = $completed_time<br/>";
                                echo "completed stops = $completed_stops<br/>";
                            }
                            #$restart = true;
                            break;
                        }
                    }
                    
                    // add path back to open
                    if (!$restart) {                    
                        #echo "adding path to OPEN<br/>";
                        $box = $new_path->bounding_box;
                        if (!is_null($box)) {
                            $area = $new_path->bounding_box->getArea();
                        }
                        else {
                            $area = 0;
                        }
                        $multi_paths = $open["$area"];
                        if ($multi_paths == null) {
                            $multi_paths = array();
                        }
                        $add_path = true;
                        foreach ($multi_paths as $check_path) {
                            $check_box = $check_path->bounding_box;
                            if (!is_null($check_box)) {
                                if ($area > $check_box->getArea()) {
                                    echo "1 PATH bounding box ($area) is too big: " . $new_path->toString() . "<br/>";
                                    $add_path = false;
                                    break;
                                }
                            }
                        }
                        if ($add_path) {
                            $multi_paths[] = $new_path;
                            $open["$area"] = $multi_paths;
                        }
                    }
                    else {
                        break;
                    }
                }
            } // foreach ($lines_array[0]
        } // if ($DoF
        else {
            // more than one line so we need to determine whether the connection is outside the box
            // find connecting stations
            foreach ($lines_array as $line) {
                $connections = $line->getConnections();
                #echo "[" . $line->name . "] number of connections = " . count($connections) . "<br/>";
                // for each station
                foreach ($connections as $connection) {
                    $next_marker = $markers[$connection->id];
                    #echo "connection = " . $connection->id . "<br/>";
                    #echo "next_marker = " . $next_marker->toString() . "<br/>";
                    // if station is in the path
                    if (in_array($next_marker, $path->visited)) {
                        if ($debug_all) {
                            echo "connection (" . $connection->id . ") is backtracking to " . $next_marker->toString() .". ignoring.<br/>";
                        }
                        continue;
                    }
                    $original_dist = $last_marker->point->getDistanceMiles($end_marker->point);
                    $dist = $next_marker->point->getDistanceMiles($end_marker->point);
                    #echo "p2p distance = $dist ";
                    #echo "from " . $next_marker->toString() . "<br/>";
                    // calculate the box
                    $new_box = new Box($next_marker->point, $end_marker->point);
                    if (!is_null($boundary_box)) {
                        if ($original_dist < $dist) {
                            if ($new_box->getArea() > $boundary_box->getArea()) {
                                // outside our scope, move to closed list
                                if ($debug_all) {
                                    echo "new bounding box (". $next_marker->id .") is too big: " . $new_box->getArea() . ">" . $boundary_box->getArea() . ". will not add path<br/>";
                                }
                               $closed[] = $path;
                               continue;
                            }
                        }
                    }
                    // if station is in new box area
                    if ((is_null($boundary_box) || ($boundary_box->withinBox($next_marker))) && !in_array($next_marker->id, $connection_added)) {
                        // add marker to path array
                        $new_path = new Path2($path, $next_marker, $line, $connection->duration + $STATION_STOP_TIME + $TRANSFER_TIME, $connection->type, $new_box, $path->visited, 0);
                        foreach ($path->getLastSegment()->lines as $last_line) {
                            if ($line->name == $last_line->name) {                                
                                $new_path = new Path2($path, $next_marker, $line, $connection->duration + $STATION_STOP_TIME, $connection->type, $new_box, $path->visited, 0);
                                #echo "no transfer..." . $line->name . " | " . $new_path->toString() . "<br/>";
                                break;
                            }
                        }
                        if ($debug_all) {
                            echo "new_path: " . $new_path->toString() . "<br/>";
                        }
                        $connection_added[] = $next_marker->id;
                        
                        // check to see if the new station is one of the end stations. if so, then mark the path as completed and remove from OPEN
                        foreach ($end_stations as $station) {
                            // if path has ending station
                            if ($station->marker == $next_marker) {
                                if ($end_marker != $next_marker) {
                                    // add end location marker
                                    $new_path2 = new Path2($new_path, $end_marker, null, $station->getDistance()/$WALKING_SPEED, "walking", null, $new_path->visited, 0);
                                }
                                else {
                                    $new_path2 = $new_path;
                                }
                                if ($completed_time > 0 && $completed_time < $new_path2->time) {
                                    if ($debug_all) {
                                        echo "path is too long ($completed_time < " . $new_path2->time . "). discard.<br/>";
                                    }
                                    continue;
                                }
                                $par = $completed_time * pow($completed_stops,2);
                                if ($completed_stops > 0 && $completed_time > 0) {
                                    $metric = pow(count($path->path),2)*$path->time;
                                    #echo "par/metric = " . $par/$metric . "<br/>";
                                    if ($par/$metric < 0.75) {
                                        #echo "path metric is too big (" . $par/$metric . "). discard.<br/>";
                                        continue;
                                    }
                                }
                                // add path to COMPLETED and remove from OPEN
                                if ($debug) {
                                    echo "adding (" . $new_path2->toString() . ") to COMPLETED<br/>";
                                }
                                $completed[] = $new_path2;
                                
                                if ($completed_time < 0 || $completed_time > $new_path2->time) {
                                    $completed_time = $new_path2->time;
                                    $completed_stops = count($new_path2->path);
                                }
                                if ($debug_all) {
                                    echo "completed time = $completed_time<br/>";
                                    echo "completed stops = $completed_stops<br/>";
                                }
                                #$restart = true;
                                break;
                            }
                        } // foreach ($end_station
                        
                        // add path back to open
                        if (!$restart) {
                            #echo "adding path to OPEN<br/>";
                            $new_box = $new_path->bounding_box;
                            $area = 0;
                            if (!is_null($new_box)) {
                                $area = $new_box->getArea();
                            }
                            $multi_paths = $open["$area"];
                            if ($multi_paths == null) {
                                $multi_paths = array();
                            }
                            $add_path = true;
                            foreach ($multi_paths as $check_path) {
                                $check_box = $check_path->bounding_box;
                                $check_area = 0;
                                if (!is_null($check_box)) {
                                    $check_area = $check_box->getArea();
                                }
                                if ($area >= $check_area && $area != 0) {
                                    if ($debug_all) {
                                        echo "2 PATH bounding box ($area) is too big: " . $new_path->toString() . "<br/>";
                                    }
                                    $add_path = false;
                                    break;
                                }
                            }
                            if ($add_path) {
                                $multi_paths[] = $new_path;
                                $open["$area"] = $multi_paths;
                            }
                        }
                        else {
                            break;
                        }
                    } // if ((is_null($boundary_box) || ($boundary_box->withinBox($next_marker)) &&
                    else {
                        if ($debug_all) {
                            echo "PATH to ($next_marker->id) is outside of bounding box or connection has already been added.<br/>";
                        }
                    }
                } // foreach ($connections
            
            }
	    } // else
	    if (count($open) == 0 && count($completed) == 0) {
	       // we didn't find a path! expand our search
	       $open = $closed;
	    }
	    $restart = false;
	} // while (count($open) > 0)
	// return path with the shortest time
	if ($debug) {
        echo "number of COMPLETED = " . count($completed) . "<br/>";
	}
	$x = 0;
	$path = array_shift($completed);
	if (count($completed) > 0) {
	   foreach ($completed as $apath) {
	       if ($path->time > $apath->time) {
	           $path = $apath;
	       }
	       else if ($path->time == $apath->time) {
	           if (count($path->path) > count($apath->path)) {
	               $path = $apath;
	           }
	       }
	   }   
	}
    if ($debug) {
        echo $path->toString() . "<br/>";
    }
	return $path;
}

function findNearestStations2($location)
{
	global $markers;
	// the list of stations to return
	$stations = array();
	$maxDist = 0;
	foreach ($markers as $marker) {
		$currentStation = new Station2($marker, distance2Loc($location, $marker));
        if (count($stations) == 3) {	
            $maxDist = max($stations[0]->getDistance(),$stations[1]->getDistance(),$stations[2]->getDistance());
        }
		if (count($stations) < 3) {
			$stations[] = $currentStation;
            if (count($stations) < 3) {
                continue;
            }
		}
		if ($maxDist > $currentStation->getDistance()) {
			//Find station with maxDist
			$i = 0;
			for (; $i < count($stations); $i++) {
                if ($stations[$i]->getDistance() == $maxDist) {
                    break;
                }
			}
			//Replace station with maxDist with currentStation
			$stations[$i] = $currentStation;
		}
	}
	// this array should be the three closest stations	
	return $stations;
}

/**
    @param $lines_array[]<Line> cannot be null
*/
function getDegreeOfFreedom ($lines_array)
{
    // DoF = # of unique connections
    $num_lines = count($lines_array);
    $num_connections = 0;
    $connections = array();
    foreach ($lines_array as $line) {
        $connections = array_merge($connections, $line->getConnections());
    }
    $connections = array_unique($connections);
    return count($connections);
}
?>
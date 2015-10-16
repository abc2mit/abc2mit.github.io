<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
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
    if ($debug) {
        echo "// algorithm2.php:consolidatePath()<br/>";
    }
    // this is a direction path and there is only one or two paths.
    // that means it either goes from loc->station->loc or just loc->loc
    if (($type == "dir" || $type == "dir_alt") && $count <= 2) {    
        if ($debug) {
            echo "// end algorithm2.php:consolidatePath()<br/>";
        }
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
    $segment2 = $path_array[$i];
    // loop through the path, starting at the next segment.
    for (; $i < $count; $i++) {
        // grab the segment
        $segment2 = $path_array[$i];
        
        if ($debug) {
            echo "[$i/$count]@" . $segment2->m1->name . "<br/>";
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
            $lines = $marker1->getOverlapLines($segment2->m1);
            $lines = getCorrectLines($lines, "transfer");
            if ($debug) {
                echo "lines: ";
                foreach ($lines as $line) {
                    echo $line->name . " ";
                }
                echo "<br/>";
            }
            
            // create the transfer segment
            $new_segment = new Segment($marker1, $segment2->m1, $lines, $total_time, $connection);
            if ($debug) {
                echo "adding segment = " . $new_segment->toString() . "<br/>";
            }
            $cpath[] = $new_segment;
            
            $segment = $path_array[$i];
            if ($debug) {
                echo "new segment = " . $segment->toString() . "<br/>";
            }
            $marker1 = $segment->m1;
            $current_lines = $marker1->getOverlapLines($segment->m2);
            $connection = $segment->connection;
            $total_time = $segment->t;
            if ($i < $count) {
                // more segments available
                if ($debug) {
                    echo "more segments $i/$count<br/>";
                }
                continue;
            }
            // we end here with this segment
            if ($debug) {
                echo "ending after transfer<br/>";
            }
            //$current_lines = getCorrectLines($current_lines, $connection);
            //$new_segment = new Segment($segment->m1, $segment->m2, $current_lines, $total_time, $connection);
            //$cpath[] = $new_segment;
            return $cpath;
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
        if ($debug) {
            echo "lines that fit the segment: ";
            foreach ($segment_lines as $line) {
                echo $line->name . " ";
            }
            echo "<br/>";
        }
        // we found all the lines that go to the marker that is next
        // now compare them to the current lines we have
        $new_lines = linesIntersect($current_lines, $segment_lines);
        if ($debug) {
            echo "lines that intersect the old: ";
            foreach ($new_lines as $line) {
                echo $line->name . " ";
            }
            echo "<br/>";
        }
        if (count($new_lines) == 0) {
            // no overlapping lines
            // end the old segment and add to the path
            $new_segment = new Segment($marker1, $segment2->m1, $current_lines, $total_time, $connection);
            if ($debug) {
                echo "adding segment = " . $new_segment->toString() . "<br/>";
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
        $current_lines = getCorrectLines($current_lines, $connection);
        $new_segment = new Segment($marker1, $segment2->m2, $current_lines, $total_time, $connection);
        $cpath[] = $new_segment;
    }
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
    $debug_all = false;
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
        if ($debug) {
            echo "open_path " . $open_path->toString() . ":$time<br/>";
        }
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
    if ($debug) {
        echo "adding " . $open_path->toString() . ":" . $distance . "<br/>";
    }
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
        if ($debug) {
            echo "looking at path: " . $path->toString() . "<br/>";
        }
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
                    if ($debug_all) {
                        echo "old_marker = " . $last_marker->toString() . ",$old_distance<br/>";
                        echo "marker = " . $next_marker->toString() . ",$distance<br/>";
                    }
                    /*
                        The 1.25 is a random factor that allows the distance to work as planned. It's a bit strange because of how the subway system is designed.
                        From 1st Ave -> Astor Pl, it moves to 3rd Ave. However, the distance to Astor Pl from 3rd Ave is less than the distance from Union Square
                        to Astor Pl. Only this factor will allow that path to be added.
                    */
                    if ($distance <= $old_distance*1.25) {
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
function findDirPath ($start, $end, $start_stations, $end_stations) {
    $debug = false;
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
	$end_name = $end->name;
	if ($end_name == "") {
	   $end_name = $end->getAddressString();
	}
	$end_marker = new Marker($end->point->lat,$end->point->lng,"end",$end_name);
	if ($debug) {
	   echo "end marker = ";
	   $end_marker->printInfo();
	}
	// create array of paths
	$open = array();
	$start_name = $start->name;
	if ($start_name == "") {
	   $start_name = $start->getAddressString();
	}
    $start_marker = new Marker($start->point->lat,$start->point->lng,"start",$start_name);
	if ($debug) {
	   echo "start marker = ";
	   $start_marker->printInfo();
	}
	foreach($start_stations as $station) {
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
    $end_stations[] = new Station($end_marker, 0);
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
    $RATIO = 0.75;
	$completed_time = -1;
	$completed_stops = -1;
	if ($debug) {
        echo "findPath<br/>";
    }
	global $markers;
	// create array of completed paths
	$completed = array();
    // there's a bug in PHP where passing an array by value that contains objects passes the objects by reference
	$new_open = array();
	foreach ($open as $path_array) {
	   $new_array = array();
	   $time = 0;
	   foreach ($path_array as $path) {
	       $new_array[] = $path->createCopy();
	       $time = $path->time;
	   }
	   $new_open["$time"] = $new_array;
	}
	$one_path = findAPath($new_open, $end_stations, $end_marker);
	if ($one_path != null) {
        if ($debug) {
            echo "path by shortest distance: " . $one_path->toString() . "<br/>";
        }
        /*if (count($one_path->path) <= 2) {
            // this path does not even take the subway. no point searching for anything else.
            return $one_path;
        }*/
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
        if ($debug_all) {
            $last_marker->printInfo();
        }
        // for some reason the last marker and the end marker are the same.
        // 4 E 28th St, New York, NY 10016 to 350 5th Ave, New York, NY 10118
        // add to path and continue
        if ($last_marker->id == $end_marker->id) {
            $completed[] = $path;
            continue;
        }
        //$box = new Box($last_marker->point, $end_marker->point);
        #echo "box area = " . $box->getArea() . "<br/>";
        
        #$stl_marker = $path->getSTLMarker();
        #echo $stl_marker->toString() . " looking at marker = [" . $last_marker->toString() . "]<br/>";
        $lines_array = $last_marker->getLines();
        /*if ($debug_all) {
            echo "lines_array<br/>\n";
            print_r($lines_array);
            echo "<br/>\n";
        }*/
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
                    //if ($debug) {
                    //    echo "next_marker: $next_marker->id<br/>";
                    //}
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
                    
                        /*
                            If the station marker is the next marker, then we know we're at one of the end points. We will definitely add this path, but we should also make one more check to see if the next possible marker is also an end station.
                        */
                        if ($station->marker == $next_marker) {
                        
                            // grab the next set of markers from this marker and see if any of them are also end stations
                            $next_lines = $next_marker->lines;
                            foreach ($next_lines as $next_line) {
                                $next_connections = $next_line->connections;
                                foreach ($next_connections as $next_connection) {
                                    // check if each connection matches
                                    foreach ($end_stations as $station) {
                                        $connection_id = $next_connection->id;
                                        if ($connection_id == $station->marker->id) {
                                            // we found a marker that's valid
                                            // create a path
                                            $alternate_path = new Path2($new_path, $markers[$connection_id], $next_line, $next_connection->duration, $next_connection->type, $box, $new_path->visited, $single_path_length);
                                            // add the walking segment
                                            $alternate_path->addSegment(new Segment($alternate_path->getLastMarker(), $end_marker, null, $station->getDistance()/$WALKING_SPEED, "walking"), null);
                                            // verify path is valid
                                            if ($completed_time > 0 && $completed_time < $alternate_path->time) {
                                                // discarding because the time for this path is slower than one we already have
                                                if ($debug_all) {
                                                    echo "path ($next_marker->id) is too long ($completed_time < " . $alternate_path->time . "). discard.<br/>";
                                                }
                                                break;
                                            }
                                            // calculate par
                                            $par = $completed_time * pow($completed_stops,2);
                                            if ($completed_stops > 0 && $completed_time > 0) {
                                                // if we have something to compare it to, then compare
                                                $metric = pow(count($path->path),2)*$path->time;
                                                if ($par/$metric < $RATIO) {
                                                    if ($debug_all) {
                                                        echo "path metric ($next_marker->id) is too big (" . $par/$metric . "). discard.<br/>";
                                                    }
                                                    break;
                                                }
                                            }
                                                                                            
                                            if ($debug) {
                                                echo "adding (" . $alternate_path->toString() . ") to COMPLETED<br/>";
                                            }
                                            // add the path to completed
                                            $completed[] = $alternate_path;
                                            if ($completed_time < 0 || $completed_time > $alternate_path->time) {
                                                $completed_time = $alternate_path->time;
                                                $completed_stops = count($alternate_path->path);
                                            }
                                            if ($debug_all) {
                                                echo "completed time = $completed_time<br/>";
                                                echo "completed stops = $completed_stops<br/>";
                                            }
                                            break;
                                        } // if ($connection_id
                                    } // foreach ($end_stations
                                } // foreach ($next_connections
                            } // foreach ($next_lines
                        
                            // check to see if it is the end marker
                            if ($end_marker != $next_marker) {
                                // add end location marker
                                $new_path2 = new Path2($new_path, $end_marker, null, $station->getDistance()/$WALKING_SPEED, "walking", $box, $new_path->visited, $single_path_length);
                            }
                            else {
                                // otherwise use the existing path
                                $new_path2 = $new_path;
                            }
                            
                            if ($completed_time > 0 && $completed_time < $new_path2->time) {
                                // discarding because the time for this path is slower than one we already have
                                if ($debug_all) {
                                    echo "path ($next_marker->id) is too long ($completed_time < " . $new_path2->time . "). discard.<br/>";
                                }
                                continue;
                            }
                            
                            // calculate par
                            $par = $completed_time * pow($completed_stops,2);
                            if ($completed_stops > 0 && $completed_time > 0) {
                                // if we have something to compare it to, then compare
                                $metric = pow(count($path->path),2)*$path->time;
                                if ($par/$metric < $RATIO) {
                                    if ($debug_all) {
                                        echo "path metric ($next_marker->id) is too big (" . $par/$metric . "). discard.<br/>";
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
                            
                                // grab the next set of markers from this marker and see if any of them are also end stations
                                $next_lines = $next_marker->lines;
                                foreach ($next_lines as $next_line) {
                                    $next_connections = $next_line->connections;
                                    foreach ($next_connections as $next_connection) {
                                        // check if each connection matches
                                        foreach ($end_stations as $station) {
                                            $connection_id = $next_connection->id;
                                            if ($connection_id == $station->marker->id) {
                                                // we found a marker that's valid
                                                // create a path
                                                $alternate_path = new Path2($new_path, $markers[$connection_id], $next_line, $next_connection->duration, $next_connection->type, $box, $new_path->visited, $single_path_length);
                                                // add the walking segment
                                                $alternate_path->addSegment(new Segment($alternate_path->getLastMarker(), $end_marker, null, $station->getDistance()/$WALKING_SPEED, "walking"), null);
                                                // verify path is valid
                                                if ($completed_time > 0 && $completed_time < $alternate_path->time) {
                                                    // discarding because the time for this path is slower than one we already have
                                                    if ($debug_all) {
                                                        echo "path ($next_marker->id) is too long ($completed_time < " . $alternate_path->time . "). discard.<br/>";
                                                    }
                                                    break;
                                                }
                                                // calculate par
                                                $par = $completed_time * pow($completed_stops,2);
                                                if ($completed_stops > 0 && $completed_time > 0) {
                                                    // if we have something to compare it to, then compare
                                                    $metric = pow(count($path->path),2)*$path->time;
                                                    if ($par/$metric < $RATIO) {
                                                        if ($debug_all) {
                                                            echo "path metric ($next_marker->id) is too big (" . $par/$metric . "). discard.<br/>";
                                                        }
                                                        break;
                                                    }
                                                }
                                                
                                                if ($debug) {
                                                    echo "adding (" . $alternate_path->toString() . ") to COMPLETED<br/>";
                                                }
                                                // add the path to completed
                                                $completed[] = $alternate_path;
                                                if ($completed_time < 0 || $completed_time > $alternate_path->time) {
                                                    $completed_time = $alternate_path->time;
                                                    $completed_stops = count($alternate_path->path);
                                                }
                                                if ($debug_all) {
                                                    echo "completed time = $completed_time<br/>";
                                                    echo "completed stops = $completed_stops<br/>";
                                                }
                                                break;
                                            } // if ($connection_id
                                        } // foreach ($end_stations
                                    } // foreach ($next_connections
                                } // foreach ($next_lines
                            
                                if ($end_marker != $next_marker) {
                                    // add end location marker
                                    $new_path2 = new Path2($new_path, $end_marker, null, $station->getDistance()/$WALKING_SPEED, "walking", null, $new_path->visited, 0);
                                    if ($debug_all) {
                                        echo "created new path:: " . $new_path2->toString() . "<br/>";
                                    }
                                }
                                else {
                                    $new_path2 = $new_path;
                                }
                                if ($completed_time > 0 && $completed_time < $new_path2->time) {
                                    if ($debug_all) {
                                        echo "path ($next_marker->id) is too long ($completed_time < " . $new_path2->time . "). discard.<br/>";
                                    }
                                    continue;
                                }
                                $par = $completed_time * pow($completed_stops,2);
                                if ($completed_stops > 0 && $completed_time > 0) {
                                    $metric = pow(count($path->path),2)*$path->time;
                                    #echo "par/metric = " . $par/$metric . "<br/>";
                                    if ($par/$metric < $RATIO) {
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

/**
    @param $lines_array[]<Line> cannot be null
*/
function getDegreeOfFreedom ($lines_array)
{
    $debug = false;
    // DoF = # of unique connections
    //$num_lines = count($lines_array);
    //$num_connections = 0;
    $connections = array();
    foreach ($lines_array as $line) {
        $connections = array_merge($connections, $line->getConnections());
        if ($debug) {
            echo "connections<br/>\n";
            print_r($connections);
            echo "<br/>\n";
        }
    }
    //$connections = array_unique($connections);
    $connections = uniqueConnections($connections);
    return count($connections);
}

function uniqueConnections ($array) {
    $connections = array();
    $exist = array();
    foreach ($array as $connection) {
        if (!in_array($connection->id, $exist)) {
            $exist[] = $connection->id;
            $connections[] = $connection;
        }
    }
    return $connections;
}
?>
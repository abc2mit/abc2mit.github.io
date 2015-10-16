<?php

/**
    For each segment, the first marker and the second marker of the same segment will have the same lines. We check the line used. If it does not exist in second segment's second marker, then we know that the first marker is a junction point and needs to have a transfer.
*/
function consolidatePath($path) {
    #echo "path is " . $path->toString() . "<br/>";
    $path_array = $path->path;
    $count = count($path_array);
    if ($count <= 2) {
        return "walk";
    }
    // transfer time from one train to another is about 5 min
    $TRANSFER_TIME = 600;
    $new_path = array();
    $new_path[] = $path_array[0];
    
    $segment = $path_array[1];
    $current_line = $segment->lines[0];
    $marker1 = $segment->m1;
    $total_time = $segment->t;
    $connection = $segment->connection;
    for ($i = 2; $i < $count; $i++) {
        $segment2 = $path_array[$i];
        $segment2_line = $segment2->lines[0];
        echo "current_line = " . $current_line->name . "<br/>";
        echo "segment2_line = " . $segment2_line->name . "<br/>";
        echo "connection = $connection<br/>";
        if ($current_line->name != $segment2_line->name) {
            // potential transfer
            $marker2 = $segment2->m2;
            #$marker2_lines = $marker2->lines;
            $transfer = true;
            foreach ($marker1->lines as $mline) {
                if ($segment2_line->name == $mline->name) {
                    echo "MLINE = " . $mline->name . "<br/><br/>";
                    if ($connection == $segment2->connection) {
                        $transfer = false;
                        break;
                    }
                }
            }
            if ($transfer) {
                #$connection = $segment->connection;
                $overlap = $marker1->getOverlapLines($segment2->m1);
                $overlap = getCorrectLines($overlap, $connection);
                #if (count($overlap) == 0) {
                #    $overlap = array($current_line);
                #}
                $new_path[] = new Segment($marker1, $segment2->m1, $overlap, $total_time, $connection);
                $marker1 = $segment2->m1;
                $segment = $segment2;
                $total_time = $segment2->t;
                $current_line = $segment2->lines[0];
                $connection = $segment2->connection;
                continue;
            }
        }
        $total_time += $segment2->t;
        $connection = $segment2->connection;
    }
    $new_path[] = $path_array[$count - 1];
    return $new_path;
}

 // removes markers from path where the travel is the same
function oldconsolidatePath($path) {
    #echo "consolidate path<br/>\n";
    $new_path = array();
    // count is guaranteed to be at least 3 (start->station, station->station, station->end)
    $count = count($path);
    $new_path[] = $path[0];
    #echo "path is $count long<br/>\n";
    // find the lines for the first segment
    $segment = $path[1];
    $segment_lines = $segment->m1->getOverlap($segment->m2);
    $marker1 = $segment->m1;
    $s1_time = $segment->t;
    if ($count > 3) {
        for ($i = 2; $i < $count; $i++) {
            $segment2 = $path[$i];
            $segment_lines2 = $segment2->m1->getOverlap($segment2->m2);
            $lines = array_intersect($segment_lines, $segment_lines2);
            if (count($lines) == 0) {
                $x = $i-1;
                $overlap = $marker1->getOverlapLines($segment->m2);
                $connection = $segment->connection;
                $overlap = getCorrectLines($overlap, $connection);
                $new_path[] = new Segment($marker1, $segment->m2, $overlap, $s1_time + $segment->t, $connection);
                $segment_lines = $segment_lines2;
                $marker1 = $segment2->m1;
                $s1_time = $segment2->t;
            }
            else {
                $lines = array_intersect(getLineNames($segment2),$lines);
                if (count($lines) == 1) {
                    $x = $i-1;
                    #$overlap = $marker1->getOverlapLines($segment->m2);
                    #$connection = $segment->connection;
                    #$overlap = getCorrectLines($overlap, $connection);
                    $m_lines = $marker1->getLines();
                    $new_lines = array();
                    foreach ($m_lines as $line) {
                        if ($line->name == $lines[0]) {
                            $new_lines[] = $line;
                            break;
                        }
                    }
                    $new_path[] = new Segment($marker1, $segment->m2, $new_lines, $s1_time + $segment->t, $connection);
                    $segment_lines = $segment_lines2;
                    $marker1 = $segment2->m1;
                    $s1_time = $segment2->t;
                }
                
            }
            $segment = $segment2;
        }
        $new_path[] = $path[count($path) - 1];
    }
    else if (count($path) == 2) {
        return $path;
    }
    return $new_path;
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
/*
	This is a new algorithm that determines the route by a combination of the A* algorithm and also
	by looking at the degree of freedom for each point. If the degree of freedom is less than or
	equal to 2, then there is no reason to analyse that node.
	
	f(n) = sum_n[d_n + t_stop * v_n] + d_p2p->goal = sum_n[d_n + t_stop * (d/dt_n * d_n)] + d_p2p->goal = g(n) + h(n)
		
	1. g(n) = cost of getting from the initial node to n (distance along path traveled).
	2. h(n) = the estimate of the cost of getting from n to the goal node (straight line distance).
	3. f(n) = g(n) + h(n)
	4. a pointer to the parent node
	5. the type of connection between the two nodes
	6. the velocity of the leg traveled (distance / duration)
	
	 we want to find: min ( d_traveled + t_stops * (v_1 + v_2 + ...) + d_p2p->goal ) = min ( g(n) + h(n) + c_stop * v_sum )
	    where
	       v = the velocity of the train between the previous node and the current node
	       c_stop = 2 min/stop
	 f(n)
	
	1 Create a node with the goal location (node_goal)
	2 Create a node with the start location (node_start)
	3 Put node_start on the OPEN list
	4 while the OPEN list is not empty
	5 {
	6   Get the node off the open list with the lowest f(n) and call it node_current
	7   if node_current is the same state as node_goal, we have found the solution; break from the while loop
	8   determine the "degree of freedom" for this node
	9   while DoF - 1 <= 1
	10  {
	11    create node_successor
	12    calculate f(n) to node_successor and store
	13    add node_current to CLOSED list
	14    set node_current = node_successor
	15  }
	16  generate each state node-successor that can come after node_current
    17  for each node_successor of node_current
    18  {
    19    Set the cost of node_successor to be the cost of node_current plus the cost to get to node_successor from node_current
    20    find node_successor on the OPEN list
    21    if node_successor is on the OPEN list but the existing one is as good or better then discard this successor and continue
    22    if node_successor is on the CLOSED list but the existing one is as good or better then discard this successor and continue
    23    Remove occurences of node_successor from OPEN and CLOSED
    24    Set the parent of node_successor to node_current
    25    Set h to be the estimated distance to node_goal (Using the heuristic function)
    26    Add node_successor to the OPEN list
    27  }
    28  Add node_current to the CLOSED list
    29 }
	
*/

/**
	
	@param $start<Location> the starting location (address)
	@param $end<Location> the ending location (address)
*/
function findDirPath ($start, $end, $start_stations, $end_stations)
{
    $walking_speed = 2 / 3600;
    #echo "findDirPath<br/>";
	// determine original bounding box
	$original_box = new Box($start->point, $end->point);
	// create end marker
	$end_marker = new Marker($end->point->lat,$end->point->lng,"end",$end->desc);
	// create array of paths
	$open = array();
	foreach($start_stations as $station) {
        $start_marker = new Marker($start->point->lat,$start->point->lng,"start",$start->desc);
        $segment = new Segment($start_marker,$station->marker,$station->marker->lines,$station->getDistance()/$walking_speed, "walking");
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
    global $markers;
    // determine original bounding box
    $original_box = new Box($start_marker->point, $end_marker->point);
    // create array of paths
    $open = array();
    foreach($start_marker->getLines() as $line) {
        foreach($line->getConnections() as $connection) {
            // get associated marker
            $marker = $markers[$connection->id];
            $segment = new Segment($start_marker, $marker, $marker->lines,$connection->duration,$connection->type);
            $visited = array();
            $visited[] = $start_marker;
            $visited[] = $marker;
            $new_box = new Box($marker->point,$end_marker->point);
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
    }
    $end_stations = array();
    $end_stations[] = new Station2($end_marker, 0);
    findPath($open,$end_stations, null);
}

/**
    @param $end_marker<Marker> can be null if S2S search
    @return<Path2> a path from the start to the end or null if none is found
*/
function findPath ($open, $end_stations, $end_marker)
{
    $walking_speed = 2 / 3600;
    #echo "findPath<br/>";
	global $markers;
	// create array of completed paths
	$completed = array();
	// create closed paths
	$closed = array();
	$restart = false;
	//$boundary_box = $original_box;
	#$counter = 0;
	$completed_time = -1;
	$completed_stops = -1;
	$multiple_paths = array();
	while (count($open) > 0) {
        #echo "loop: $counter<br/>";
        #$counter++;
        ##echo "open size = " . count($open) . "<br/>";
        //if (count($completed) > 4) {
        //    break;
        //}
        ksort($open);
	    // look at the path at the front of OPEN, remove from list
        $keys = array_keys($open);
        #echo "KEYS = ";
        #print_r($keys);
        #echo "<br/>";
        $multiple_paths = $open[$keys[0]];
        $path = array_shift($multiple_paths);
	    if (count($multiple_paths) == 0) {
            unset($open[$keys[0]]);
            if (count($open) > 0) {
                reset($open);
            }
            else {
                $open = array();
            }
        }
        else {
            $open[$keys[0]] = $multiple_paths;
        }
        
        if ($completed_time > 0 && $completed_time < $path->time) {
            #echo "path is too long ($completed_time < " . $new_path2->time . "). discard.<br/>";
            continue;
        }
        $par = $completed_time * pow($completed_stops,2);
        if ($completed_stops > 0 && $completed_time > 0) {
            $metric = pow(count($path->path),2)*$path->time;
            #echo "metric = $metric | par = $par<br/>";
            if ($par/$metric < 0.8) {
                #echo "path metric is too big (" . $par/$metric . "). discard.<br/>";
                continue;
            }
        }
        
        $boundary_box = $path->bounding_box;
        #echo "box area = " . $boundary_box->getArea() . "<br/>";
        #echo "open size (after shift) = " . count($open) . "<br/>";
        // print path
        ##echo $path->toString();
        // find degree of freedom for each station at the end of each path
        // DoF is defined as the number of outbound connections from the marker
        $last_marker = $path->getLastMarker();
        
        $box = new Box($last_marker->point, $end_marker->point);
        #$dist = $last_marker->point->getDistanceMiles($end_marker->point);
        #echo "p2p distance = $dist ";
        #echo "from".$last_marker->toString()."<br/>";
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
                    $new_box = new Box($next_marker->point, $end_marker->point);
                    if ($original_dist < $dist) {
                        if ($new_box->getArea() > $box->getArea()) {
                            // outside our scope, move to closed list
                            #echo "bounding box (". $next_marker->id .") is too big: " . $new_box->getArea() . ">" . $box->getArea() . ". will not add path<br/>";
                            $closed[] = $path;
                            continue;
                        }
                    }
                    if (in_array($next_marker, $path->visited)) {
                        #echo "connection (" . $connection->id . ") is backtracking to " . $next_marker->toString() .". ignoring.<br/>";
                        continue;
                    }
                    
                    if (in_array($next_marker->id, $connection_added)) {
                        #echo "[$next_marker->id] marker was already added. skipping.<br/>\n";
                        continue;
                    }
                    $new_path = new Path2($path, $next_marker, $lines_array[0], $connection->duration, $connection->type, $new_box, $path->visited);
                    $connection_added[] = $next_marker->id;
                    
                    // check to see if the new station is one of the end stations. if so, then mark the path as completed and remove from OPEN
                    foreach ($end_stations as $station) {
                        if ($station->marker == $next_marker) {
                            if ($end_marker != null) {
                                // add end location marker
                                $new_path2 = new Path2($new_path, $end_marker, null, $station->getDistance()/$walking_speed, "walking", $box, $new_path->visited);
                                #$new_path->addSegment(new Segment($next_marker, $end_marker, null, $station->getDistance()/$walking_speed, "walking"), $box);
                            }
                            if ($completed_time > 0 && $completed_time < $new_path2->time) {
                                #echo "path is too long ($completed_time < " . $new_path2->time . "). discard.<br/>";
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
                            #echo "adding (" . $new_path2->toString() . ") to COMPLETED<br/>";
                            $completed[] = $new_path2;
                            if ($completed_time < 0 || $completed_time > $new_path2->time) {
                                $completed_time = $new_path2->time;
                                $completed_stops = count($new_path2->path);
                            }
                            #echo "completed time = $completed_time<br/>";
                            #echo "completed stops = $completed_stops<br/>";
                            #$restart = true;
                            break;
                        }
                    }
                    
                    // add path back to open
                    if (!$restart) {                    
                        #echo "adding path to OPEN<br/>";
                        $area = $new_path->bounding_box->getArea();
                        $multi_paths = $open["$area"];
                        if ($multi_paths == null) {
                            $multi_paths = array();
                        }
                        $add_path = true;
                        foreach ($multi_paths as $check_path) {
                            if ($new_path->bounding_box->getArea() > $check_path->bounding_box->getArea()) {
                                #echo "PATH bounding box is too big.<br/>";
                                $add_path = false;
                                break;
                            }
                        }
                        if ($add_path) {
                            //$key = $new_path->bounding_box->getArea();
                            $multi_paths[] = $new_path;
                            //ksort($multi_paths);
                            $area = $new_path->bounding_box->getArea();
                            #echo "adding $multi_paths<br/>";
                            $open["$area"] = $multi_paths;
                        }
                    }
                    else {
                        break;
                    }
                }
            } // foreach ($lines_array[0]
            #echo "OOPEN size = " . count($open) . "<br/>";
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
                    // if station is in the path
                    if (in_array($next_marker, $path->visited)) {
                        #echo "connection (" . $connection->id . ") is backtracking to " . $next_marker->toString() .". ignoring.<br/>";
                        continue;
                    }
                    $original_dist = $last_marker->point->getDistanceMiles($end_marker->point);
                    $dist = $next_marker->point->getDistanceMiles($end_marker->point);
                    #echo "p2p distance = $dist ";
                    #echo "from " . $next_marker->toString() . "<br/>";
                    // calculate the box
                    $new_box = new Box($next_marker->point, $end_marker->point);
                    if ($original_dist < $dist) {
                        if ($new_box->getArea() > $box->getArea()) {
                           // outside our scope, move to closed list
                           #echo "new bounding box (". $next_marker->id .") is too big: " . $new_box->getArea() . ">" . $box->getArea() . ". will not add path<br/>";
                           $closed[] = $path;
                           continue;
                        }
                    }
                    // if station is in new box area
                    if ($box->withinBox($next_marker) && !in_array($next_marker->id, $connection_added)) {
                        // add marker to path array
                        $new_path = new Path2($path, $next_marker, $line, $connection->duration, $connection->type, $new_box, $path->visited);                        
                        $connection_added[] = $next_marker->id;
                        
                        // check to see if the new station is one of the end stations. if so, then mark the path as completed and remove from OPEN
                        foreach ($end_stations as $station) {
                            // if path has ending station
                            if ($station->marker == $next_marker) {
                                if ($end_marker != null) {
                                    // add end location marker
                                    $new_path2 = new Path2($new_path, $end_marker, null, $station->getDistance()/$walking_speed, "walking", $box, $new_path->visited);
                                    //$new_path->addSegment(new Segment($next_marker, $end_marker, null, $station->getDistance()/$walking_speed, "walking"), $box);
                                }
                                
                                if ($completed_time > 0 && $completed_time < $new_path2->time) {
                                    #echo "path is too long ($completed_time < " . $new_path2->time . "). discard.<br/>";
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
                                ##echo "adding (" . $new_path2->toString() . ") to COMPLETED<br/>";
                                $completed[] = $new_path2;
                                
                                if ($completed_time < 0 || $completed_time > $new_path2->time) {
                                    $completed_time = $new_path2->time;
                                    $completed_stops = count($new_path2->path);
                                }
                                #echo "completed time = $completed_time<br/>";
                                #echo "completed stops = $completed_stops<br/>";
                                #$restart = true;
                                break;
                            }
                        } // foreach ($end_station
                        
                        
                        // add path back to open
                        if (!$restart) {
                            #echo "adding path to OPEN<br/>";
                            $area = $new_path->bounding_box->getArea();
                            $multi_paths = $open["$area"];
                            if ($multi_paths == null) {
                                $multi_paths = array();
                            }
                            $add_path = true;
                            foreach ($multi_paths as $check_path) {
                                if ($new_path->bounding_box->getArea() > $check_path->bounding_box->getArea()) {
                                    #echo "PATH bounding box is too big.<br/>";
                                    $add_path = false;
                                    break;
                                }
                            }
                            if ($add_path) {
                                //$key = $new_path->bounding_box->getArea();
                                $multi_paths[] = $new_path;
                                //ksort($multi_paths);
                                $area = $new_path->bounding_box->getArea();
                                #echo "adding $multi_paths<br/>";
                                $open["$area"] = $multi_paths;
                            }
                        }
                        else {
                            break;
                        }
                    } // if ($box->withinBox
                    else {
                        #echo "PATH is outside of bounding box or connection has already been added.<br/>";
                    }
                } // foreach ($connections
            
            }
            
            $boundary_box = $box;
	    } // else
	    if (count($open) == 0 && count($completed) == 0) {
	       // we didn't find a path! expand our search
	       $open = $closed;
	    }
	    $restart = false;
	} // while (count($open) > 0)
	// return path with the shortest time
	##echo "number of COMPLETED = " . count($completed) . "<br/>";
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
    echo $path->toString() . "<br/>";
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

/**
	This class defines a station that is closest to a given marker.
*/
class Station2
{
	// the marker for the station
	var $marker;
	// the distance to the input address
	var $dist2loc;
	
	/**
		The constructor for this station.
		
		@param $m the marker for this station
		@param $d the distance from the input address to this station
	*/
	function Station2($m, $d) {
		$this->marker = $m;
		$this->dist2loc = $d;
	}
	
	/**
		Returns the marker for this station.
	*/
	function getMarker() {
		return $this->marker;
	}
	
	/**
		Returns the distance from the original location to this station.
	*/
	function getDistance() {
		return $this->dist2loc;
	}
	
	function toString()
	{
	   return $this->marker->name . ":" . $this->dist2loc;
	}
}

/**
    lat = y, lng = x
*/
class Box {
    var $top;
    var $bottom;
    var $left;
    var $right;
    
    /**
        Constructor. Builds the box based on the start point and end point.
        The box is built upon the outer bounds found by analysing the two 
        points plus two points found at the equidistant edges of a line
        intersecting the midpoint of the line between the start and end
        point with the same distance.
    */
    function Box($start_point, $end_point) {
        #echo "Box Constructor<br/>";
        #echo "P1={" . $start_point->lat . "," . $start_point->lng . "}<br/>";        
        #echo "P2={" . $end_point->lat . "," . $end_point->lng . "}<br/>";
        $midpoint = $this->findMidpoint($start_point, $end_point);
        #echo "P3={" . $midpoint->lat . "," . $midpoint->lng . "}<br/>";
        $slope = $this->findSlope($start_point, $end_point);
        $distance = $midpoint->getDistanceMiles($start_point);
        #echo "new distance is $distance<br/>";
        //$p1 = $this->findP1($slope, $midpoint, $distance/4);
        $p1 = $this->gP1($slope, $start_point, $end_point);
        #echo "P4={" . $p1->lat . "," . $p1->lng . "}<br/>";
        $p2 = $this->gP2($slope, $start_point, $end_point);
        //$p2 = $this->findP2($slope, $midpoint, $distance/4);
        #echo "P5={" . $p2->lat . "," . $p2->lng . "}<br/>";
        $y = array($start_point->lat, $end_point->lat, $p1->lat, $p2->lat);
        $x = array($start_point->lng, $end_point->lng, $p1->lng, $p2->lng);
        $this->top = max($y);
        $this->bottom = min($y);
        $this->left = min($x);
        $this->right = max($x);
    }
    
    /**
        Finds the midpoint between two points.
        
        @param p1 the first point
        @param p2 the second point
        @return a new point that is equidistant and on the direct path between p1
        and p2
    */
    function findMidpoint ($p1, $p2) {
        return new Point(($p1->lat + $p2->lat)/2, ($p1->lng + $p2->lng)/2);
    }
    
    /**
        Finds the slope of the line intersecting two points.
        
        @param the first point
        @param the second point
        @return the slope of the line intersecting the two points
    */
    function findSlope ($p1, $p2) {
        return ($p1->lat - $p2->lat)/($p1->lng - $p2->lng);
    }
    
    /**
        Finds the endpoint of a line that represents the intersection of the equidistant
        plane between two points and the z-plane.
        @param
    */
    function gP1 ($slope, $p1, $p2) {
        $x1 = $p1->lng;
        $y1 = $p1->lat;
        $x2 = $p2->lng;
        $y2 = $p2->lat;
        $d = (pow($x2-$x1,2)+pow($y2-$y1,2));
        $c0 = 1/4*(((pow($slope,2) + 1)/pow($slope,2))*(pow($x2-$x1,2))-$d);
        $c = pow($slope,2)/(pow($slope,2)+1)*$c0;
        $x3 = (($x2 - $x1) + sqrt(pow($x2-$x1,2)-4*$c))/2 + $x1;
        $xm=($x1+$x2)/2;
        $ym=($y1+$y2)/2;
        $y3 = $ym + sqrt(($d/4)-pow($x3-$xm,2));
        return new Point($y3,$x3);
    }
    
    /**
        Finds the other endpoint of a line that represents the intersection of the
        equidistant plane between two points and the z-plane.
    */
    function gP2 ($slope, $p1, $p2) {
        $x1 = $p1->lng;
        $y1 = $p1->lat;
        $x2 = $p2->lng;
        $y2 = $p2->lat;
        $d = (pow($x2-$x1,2)+pow($y2-$y1,2));
        $c0 = 1/4*(((pow($slope,2) + 1)/pow($slope,2))*(pow($x2-$x1,2))-$d);
        $c = pow($slope,2)/(pow($slope,2)+1)*$c0;
        $x4 = (($x2 - $x1) - sqrt(pow($x2-$x1,2)-4*$c))/2 + $x1;
        $xm=($x1+$x2)/2;
        $ym=($y1+$y2)/2;
        $y4 = $ym - sqrt(($d/4)-pow($x4-$xm,2));
        return new Point($y4,$x4);
    }
    
    function getArea() {
        $miles_per_degree_latitude = 69.023;
        $miles_per_degree_longitude = 51.075;
        $x = $miles_per_degree_latitude*abs($this->top-$this->bottom);
        $y = $miles_per_degree_longitude*abs($this->right-$this->left);
        #echo "{x,y} = {" . $x . "," . $y . "}<br/>";
        $area = ($x*$y);
        #echo "[$this->top,$this->bottom,$this->left,$this->right] = $area<br/>";
        return $area;
    }
    
    function withinBox($marker) {
        if ($marker->lat < $this->top || $marker->lat > $this->bottom || $marker->lng > $this->left || $marker->lng < $this->right) {
            return true;
        }
        return false;
    }
}

class Path2 {
    var $path;
    var $time;
    var $bounding_box;
    var $visited = array();
    
    function Path2 ($param1 = null, $param2 = null, $param3 = null, $param4 = null, $param5 = null, $param6 = null, $param7 = null) {
        $num_args = func_num_args();
        $arg_list = func_get_args();
        $args = "";
        for ($i = 0; $i < $num_args; $i++) {
            if ($i != 0) {
                $args .= ", ";
            }
            $args .= "\$param" . ($i + 1);
        }
        eval("\$this->constructor" . $i . "(" . $args . ");");        
    }
    
    function constructor7 ($path, $marker, $line, $time, $connection, $box, $v) {
        $this->path = $path->path;
        $this->time = $path->time;
        $last_marker = $this->getLastMarker();
        $lines = array();
        $lines[] = $line;
        if ($v != null) {
            $this->visited = array_merge($this->visited, $v);
        }
        $this->addSegment(new Segment($last_marker, $marker, $lines, $time, $connection), $box);
    }
    
    function constructor3 ($segment, $box, $v) {
        $this->path = array();
        $this->path[] = $segment;
        $this->time = $segment->t;
        $this->bounding_box = $box;
        if ($v != null) {
            $this->visited = array_merge($this->visited, $v);
        }
    }
    
    function addSegment($segment, $box) {
        $this->path[] = $segment;
        $this->time = $this->time + $segment->t;
        $this->bounding_box = $box;
        $this->visited[] = $segment->m1;
        $this->visited[] = $segment->m2;
    }
    
    function getSTLMarker() {
        return $this->path[count($this->path) - 1]->m1;
    }
    
    function getLastMarker() {
        return $this->path[count($this->path) - 1]->m2;
    }
    
    function toString()
    {
        $s = "Path2(" . $this->time . "," . count($this->path) . ") = ";
        foreach ($this->path as $segment) {
            $s .= "{";
            $s .= $segment->toString();
            $s .= "}";
        }
        $s .= "<br/>";
        return $s;
    }
}

class Segment {
    var $m1;
    var $m2;
    var $lines;
    var $t;
    var $connection;
    
    function Segment($m1, $m2, $lines, $time, $connection) {
        $this->m1 = $m1;
        $this->m2 = $m2;
        $this->lines = $lines;
        $this->t = $time;
        $this->connection = $connection;
    }
    
    function toString() {
        $s = $this->m1->toString() . "-&gt;" . $this->m2->toString();
        $size = count($this->lines);
        if ($size > 0) {
            $s .= "[";
            for ($i = 0; $i < $size; $i++) {
                $line = $this->lines[$i];
                $s .= $line->name;
                if ($i != $size - 1) {
                    $s .= ",";
                }
            }
            $s .= "]";
            $s .= "*" . $this->t . "*";
            $s .= "#" . $this->connection . "#";
        }
        return $s;
    }
}
?>
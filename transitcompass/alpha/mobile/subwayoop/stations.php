<?php
    function distanceLoc($point, $marker) {
        $miles_per_degree_latitude = 69.023;
        $miles_per_degree_longitude = 51.075;
        //$lat = $marker->point->lat;
        //echo "marker lat = $lat<br/>\n";
        $m1_lat = $miles_per_degree_latitude * $point->getLat() / 1000000;
        $m1_lng = $miles_per_degree_longitude * $point->getLng() / 1000000;
        $m2_lat = $miles_per_degree_latitude * $marker->getLat() / 1000000;
        $m2_lng = $miles_per_degree_longitude * $marker->getLng() / 1000000;
        
        return sqrt(pow(($m1_lat - $m2_lat),2) + pow(($m1_lng - $m2_lng),2));
    }
    
    // for location function only. using this twice for directions is too slow.
    function findNearestStation($loc) {
        global $markers;
        $closest_marker = "";
        $shortest_distance = "";
        foreach($markers as $marker) {
            if ($shortest_distance == "") {
                $closest_marker = $marker;
                $shortest_distance = distanceLoc($loc, $marker);
            }
            else {
                $distance = distanceLoc($loc, $marker);
                if ($distance < $shortest_distance) {
                    $closest_marker = $marker;
                    $shortest_distance = $distance;
                }
            }
        }
        return $closest_marker;
    }
    
    // alternate function to find the three closest stations.
    function findStations($loc) {
        global $markers;
        $stations = array();
        $cmarker = "";
        foreach ($markers as $marker) {
            //$count = count($markers);
            //echo "count = $count<br/>\n";
            $name = $marker->name;
            //echo "name = $name<br/>\n";
            if (count($stations) == 0) {
                //echo "new start<br/>\n";
                $cmarker = array();
                $cmarker['m'] = $marker;
                $cmarker['d'] = distanceLoc($loc, $marker);
                $stations[] = $cmarker;
            }
            // otherwise
            else {
                $distance = distanceLoc($loc, $marker);
                // check top three
                // check first
                if ($distance < $stations[0]['d']) {
                    //echo "adding marker to beginning\n";
                    $cmarker = array();
                    $cmarker['m'] = $marker;
                    $cmarker['d'] = $distance;
                    array_unshift($stations, $cmarker);
                }
                // if there's more than one element
                else if (count($stations) > 1) {
                    // check second
                    if ($distance < $stations[1]['d']) {
                        $cmarker = array();
                        $cmarker['m'] = $marker;
                        $cmarker['d'] = $distance;
                        // pull the first element off
                        $tmp_marker = array_shift($stations);
                        // put new element on
                        array_unshift($stations, $cmarker);
                        // put first element back on
                        array_unshift($stations, $tmp_marker);
                    }
                    // if there are more than two elements
                    else if (count($stations) > 2) {
                        if ($distance < $stations[2]['d']) {
                            $cmarker = array();
                            $cmarker['m'] = $marker;
                            $cmarker['d'] = $distance;
                            // remove last element, size of array should always be 3
                            array_pop($stations);
                            // put this marker on
                            array_push($stations, $cmarker);
                        }
                    }
                    // otherwise, add to the end (only two elements so far)
                    else {
                        $cmarker = array();
                        $cmarker['m'] = $marker;
                        $cmarker['d'] = $distance;
                        array_push($stations, $cmarker);
                    }
                }
                // otherwise, add to the end (only one element so far)
                else {
                    $cmarker = array();
                    $cmarker['m'] = $marker;
                    $cmarker['d'] = $distance;
                    array_push($stations, $cmarker);
                }
                // trim size to 3
                while (count($stations) > 3) {
                    array_pop($stations);
                }
            }
        }
        return $stations;
    }
    
    // for directions finder.
    function findNearestStations($loc1, $loc2) {
        global $markers;
        $start_stations = array();
        $end_stations = array();
        $cmarker = "";
        // have to look at each marker
        foreach ($markers as $marker) {
            // starting
            if (count($start_stations) == 0) {
                $cmarker = array();
                $cmarker['m'] = $marker;
                $cmarker['d'] = distanceLoc($loc1, $marker);
                $start_stations[] = $cmarker;
            }
            // otherwise
            else {
                $distance = distanceLoc($loc1, $marker);
                // check top three
                // check first
                if ($distance < $start_stations[0]['d']) {
                    $cmarker = array();
                    $cmarker['m'] = $marker;
                    $cmarker['d'] = $distance;
                    array_unshift($start_stations, $cmarker);
                }
                // if there's more than one element
                else if (count($start_stations) > 1) {
                    // check second
                    if ($distance < $start_stations[1]['d']) {
                        $cmarker = array();
                        $cmarker['m'] = $marker;
                        $cmarker['d'] = $distance;
                        // pull the first element off
                        $tmp_marker = array_shift($start_stations);
                        // put new element on
                        array_unshift($start_stations, $cmarker);
                        // put first element back on
                        array_unshift($start_stations, $tmp_marker);
                    }
                    // if there are more than two elements
                    else if (count($start_stations) > 2) {
                        if ($distance < $start_stations[2]['d']) {
                            $cmarker = array();
                            $cmarker['m'] = $marker;
                            $cmarker['d'] = $distance;
                            // remove last element, size of array should always be 3
                            array_pop($start_stations);
                            // put this marker on
                            array_push($start_stations, $cmarker);
                        }
                    }
                    // otherwise, add to the end (only two elements so far)
                    else {
                        $cmarker = array();
                        $cmarker['m'] = $marker;
                        $cmarker['d'] = $distance;
                        array_push($start_stations, $cmarker);
                    }
                }
                // otherwise, add to the end (only one element so far)
                else {
                    $cmarker = array();
                    $cmarker['m'] = $marker;
                    $cmarker['d'] = $distance;
                    array_push($start_stations, $cmarker);
                }
                // trim size to 3
                while (count($start_stations) > 3) {
                    array_pop($start_stations);
                }
            }
            // starting
            if (count($end_stations) == 0) {
                $cmarker = array();
                $cmarker['m'] = $marker;
                $cmarker['d'] = distanceLoc($loc2, $marker);
                $end_stations[] = $cmarker;
            }
            // otherwise
            else {
                $distance = distanceLoc($loc2, $marker);
                // check top three
                // check first
                if ($distance < $end_stations[0]['d']) {
                    $cmarker = array();
                    $cmarker['m'] = $marker;
                    $cmarker['d'] = $distance;
                    array_unshift($end_stations, $cmarker);
                }
                // if there's more than one element
                else if (count($end_stations) > 1) {
                    // check second
                    if ($distance < $end_stations[1]['d']) {
                        $cmarker = array();
                        $cmarker['m'] = $marker;
                        $cmarker['d'] = $distance;
                        // pull the first element off
                        $tmp_marker = array_shift($end_stations);
                        // put new element on
                        array_unshift($end_stations, $cmarker);
                        // put first element back on
                        array_unshift($end_stations, $tmp_marker);
                    }
                    // if there are more than two elements
                    else if (count($end_stations) > 2) {
                        if ($distance < $end_stations[2]['d']) {
                            $cmarker = array();
                            $cmarker['m'] = $marker;
                            $cmarker['d'] = $distance;
                            // remove last element, size of array should always be 3
                            array_pop($end_stations);
                            // put this marker on
                            array_push($end_stations, $cmarker);
                        }
                    }
                    // otherwise, add to the end (only two elements so far)
                    else {
                        $cmarker = array();
                        $cmarker['m'] = $marker;
                        $cmarker['d'] = $distance;
                        array_push($end_stations, $cmarker);
                    }
                }
                // otherwise, add to the end (only one element so far)
                else {
                    $cmarker = array();
                    $cmarker['m'] = $marker;
                    $cmarker['d'] = $distance;
                    array_push($end_stations, $cmarker);
                }
                // trim size to 3
                while (count($end_stations) > 3) {
                    array_pop($end_stations);
                }
            }
        }
        return array($start_stations, $end_stations);
    }
?>
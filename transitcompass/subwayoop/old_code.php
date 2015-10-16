<?php
    #   old_code.php
    #   Placeholder for old code. Ensures that current files are clean.
    
    

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

function getLineNames($lines) {
    $names = array();
    foreach ($lines as $line) {
        $names[] = $line->name;
    }
    return $names;
}
?>
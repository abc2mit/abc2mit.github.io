<?php
/**
    This class describes a physical location.
*/
class Location {
    public $point;
    public $desc;
    
    function Location ($param1 = null, $param2 = null, $param3 = null) {
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
    
    function constructor1 ($param1) {
        $this->desc = $param1;
        $this->point = new Point();
    }
    
    function constructor3 ($lat, $long, $description) {
        $this->point = new Point($lat, $long);
        $string = $this->cleanupDescription($description);
        $this->desc = $string;
    }
    
    function getLat () {
        return $this->point->getLat();
    }
    
    function getLng () {
        return $this->point->getLng();
    }
    
    function getPoint () {
        return $this->point;
    }
    
    function printInfo () {
        echo "$this->desc {<br/>";
            echo $this->point->toString();
        echo "}<br/>";
    }
    
    function cleanupDescription($desc) {
        $desc = str_replace("+", " ", $desc);
        $desc = str_replace("%2C", ",", $desc);
        return $desc;
    }
}

/**
    This class describes a station. A station contains a location and also
    some variables such as the name and the lines that are used by this station.
    Lines are Line objects.
*/
class Station2 extends Location
{
    private /*String*/ $id;
    private /*String*/ $name;
    private $lines;
    public $m_url;
    private $last_line;
    
    function Station2 ($lat, $lng, $id, $name) {
        $this->point = new Point($lat, $lng);
        $this->id = $id;
        $this->name = $name;
        $this->lines = array();
    }
    
    function addLine ($line) {
        $this->lines[$line->name] = $line;
        $this->last_line = $line->name;
    }
    
    function getID () {
        return $this->id;
    }
    
    function getLat () {
        return $this->point->lat;
    }
    
    function getLng () {
        return $this->point->lng;
    }
    
    function getLine ($i) {
        return $this->lines[$i];
    }
    
    function getLines () {
        return $this->lines;
    }
    
    function getLineCount () {
        return count($this->lines);
    }
    
    function getLineNames () {
        $names = array();
        foreach ($this->lines as $line) {
            $names[] = $line->name;
        }
        return $names;
    }
    
    function getLastLine () {
        return $this->lines[$this->last_line];
    }
    
    function getName () {
        return $this->name;
    }
    
    function getOverlapLines ($marker) {
        $lines = array();
        $keys = array_keys($this->lines);
        $mkeys = array_keys($marker->lines);
        for ($i = 0; $i < count($keys); $i++) {
            $name = $this->lines[$keys[$i]]->name;
            for ($j = 0; $j < count($mkeys); $j++) {
                $name2 = $marker->lines[$mkeys[$j]]->name;
                if ($name == $name2) {
                    $line = $this->lines[$keys[$i]];
                    $lines[] = $line;
                }
            }
        }
        return $lines;
    }
    
    function getURL () {
        return $this->m_url;
    }
    
    function setLastLine ($line) {
        $this->last_line = $line->name;
    }
    
    function setURL ($url) {
        $this->m_url = $url;
    }
    
    function toString() {
        return $this->name;
    }
    
    function printInfo() {
        echo "(" . $this->point->lat . "," . $this->point->lng . ") " . $this->name . " [" . $this->id . "] ";
        foreach ($this->lines as $line) {
            echo $line->name;
        }
        echo "<br/>\n";
    }
}

/**
    This class denotes a location that has an address. Beyond extending
    Location, it includes all the information for standard US addresses.
    This class may need to be extended in the future for other countries.
*/
class Address extends Location
{
    public $id;
    public $name;
    public $number;
    public $street;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $phone;

    function Address ($i, $la, $ln, $na, $nu, $st, $ci, $sta, $zi, $co, $ph) {
        $this->id = $i;
        $this->point = new Point($la, $ln);
        $this->name = $na;
        $this->number = $nu;
        $this->street = $st;
        $this->city = $ci;
        $this->state = $sta;
        $this->zip = $zi;
        $this->country = $co;
        $this->phone = $ph;
    }
    
    function getAddressString () {
        $address = "";
        if ($this->number != null || $this->number != "") {
            $address .= $this->number . " " . $this->street;
        }
        else if (strpos($this->street, "&") != -1) {
            $address .= $this->street;
        }
        if ($this->city != null || $this->city != "") {
            if ($address != "") {
                $address .= ", " . $this->city;
            }
            else {
                $address = $this->city;
            }
        }
        if ($this->state != null || $this->state != "") {
            $address .= ", " . $this->state;
        }
        if ($this->zip != null || $this->zip != "") {
            $address .= " " . $this->zip;
        }
        return $address;
    }
    
    function getLat () {
        return $this->point->lat;
    }
    
    function getLng () {
        return $this->point->lng;
    }
    
    function printInfo() {
        echo $this->getAddressString() . " {<br/>";
            echo $this->point->toString();
        echo "}<br/>";
    }
    
    function toString () {
        return $this->id . "(" . $this->point->lat . "," . $this->point->lng . ") " . $this->number . " " . $this->street . ", " . $this->city . ", " . $this->state . " " . $this->zip . " " . $this->country . " (" . $this->name . ") " . $this->phone; 
    }
}

/**
    To create a new path, we give the path a segment. That segment is extended based on the line of that segment until we reach a junction in which there is more than one option. This will reduce the possibilities for calculations that are not necessary. An array of destinations is also passed in, in case one of these is reached during the traversal.

*/
class Path
{
    private $pathArray;
    // numSegments has to be guaranteed to be greater than zero
    private $numSegments;
    private $currentLines;
    //private $avoidLines = array();
    public $duration;
    public $bounding_box;
    public $visited = array();
    public $single_path_length = 0;
    // either Station2 or Address
    private $destination;
    public $stationBox;
    
    function Path ($param1 = null, $param2 = null, $param3 = null, $param4 = null, $param5 = null, $param6 = null, $param7 = null, $param8 = null, $param9 = null) {
        $debug = false;
        $num_args = func_num_args();
        $arg_list = func_get_args();
        outputDebug("creating new Path ($num_args)", $debug);
        $args = "";
        for ($i = 0; $i < $num_args; $i++) {
            if ($i != 0) {
                $args .= ", ";
            }
            $args .= "\$param" . ($i + 1);
        }
        eval("\$this->constructor" . $i . "(" . $args . ");");        
    }
    
    function constructor8 ($path, $marker, $line, $time, $connection, $box, $v, $length) {
        $this->pathArray = $path->pathArray;
        $this->duration = $path->duration;
        $last_marker = $this->getLastMarker();
        $lines = array();
        $lines[] = $line;
        if ($v != null) {
            $this->visited = array_merge($this->visited, $v);
        }
        $this->single_path_length = $length;
        $this->destination = $path->destination;
        $this->addSegment(new Segment($last_marker, $marker, $lines, $time, $connection), $box);
    }
    
    function constructor4 ($path, $station, $line, $connection) {
        $this->constructor1($path);
        $currentStation = $this->getCurrentStation();
        $lines = $currentStation->getOverlapLines($station);
        $currentLines = $this->getCurrentLines();
        $intersect = linesIntersect($currentLines, $lines);
        if (count($intersect) == 0) {
            $this->addSegment(new Segment($currentStation, $currentStation, $intersect, 60, "transfer"), $this->bounding_box);
        }
        $box = new Box($station->getPoint(), $this->destination->getPoint());
        $this->addSegment(new Segment($currentStation, $station, $lines, $connection->duration, $connection->type), $box);
    }
    
    /*function constructor5 ($path, $time, $bounding_box, $visited, $single_path_length) {
        $this->pathArray = $path->pathArray;
        $this->duration = $time;
        $this->bounding_box = $bounding_box;
        $this->visited = $visited;
        $this->single_path_length = $single_path_length;
        $this->destination = $path->destination;
        $this->currentLines = $path->currentLines;
        $this->numSegments = $path->numSegments;
    }*/
    
    function constructor3 ($segment, $v, $destination) {
        $this->pathArray = array();
        $this->pathArray[] = $segment;
        $this->duration = $segment->t;
        $this->numSegments = 1;
        $currentStation = $this->getCurrentStation();
        $box = new Box($currentStation->getPoint(), $destination->getPoint());
        $this->bounding_box = $box;
        if ($v != null) {
            $this->visited = array_merge($this->visited, $v);
        }
        $this->destination = $destination;
        $this->currentLines = $segment->lines;
    }
    
    function constructor1 ($path) {
        $this->pathArray = $path->pathArray;
        $this->duration = $path->duration;
        $this->bounding_box = $path->bounding_box;
        $this->visited = $path->visited;
        $this->single_path_length = $path->single_path_length;
        $this->destination = $path->destination;
        $this->currentLines = $path->currentLines;
        $this->numSegments = $path->numSegments;
    }
    
    function createCopy () {
        return new Path($this); 
    }
    
    function addSegment ($segment, $box) {
        $this->pathArray[] = $segment;
        $this->duration = $this->duration + $segment->t;
        $this->bounding_box = $box;
        $this->visited[] = $segment->m2;
        $this->numSegments = $this->numSegments + 1;
        $this->currentLines = $segment->lines;
    }
    
    function toString()
    {
        $s = "Path(" . $this->duration . "," . count($this->pathArray) . "," . $this->bounding_box->getArea() . ") = ";
        foreach ($this->pathArray as $segment) {
            if ($segment->connection != "transfer") {
                $s .= "{";
                $s .= $segment->toString();
                $s .= "}";
            }
        }
        return $s;
    }
    
    /*function addAvoidLine ($line) {
        $this->avoidLines[] = $line;
    }*/
    
    /**
        Returns the current line that the path is using. This function helps determine whether a path needs to make a transfer.
    */
    function getCurrentLines () {
        return $this->currentLines;
    }

    /**
        Returns the station that this current path is on.
    */
    function getCurrentStation () {
        $debug = false;
        if ($debug) {
            echo "numSegments = " . $this->numSegments . "<br/>\n";
        }
        return $this->pathArray[$this->numSegments - 1]->m2;
    }
    
    function getDestination () {
        return $this->destination;
    }
    
    /**
        Returns the most recent station from this current path. This function is used to determine the direction of the path.
    */
    function getLastStation () {
        return $this->pathArray[$this->numSegments - 1]->m1;
    }
    
    /**
        Returns an array of the lines currently being checked, not including lines that are on the avoidLine list.
    */
    function getLines () {
        $lines = $this->pathArray[$this->numSegments - 1]->m2->getLines();
        return $lines;
    }
    
    function getCurrentSegment () {
        return $this->pathArray[$this->numSegments - 1];
    }
    
    function getSegment ($i) {
        return $this->pathArray[$i];
    }
    
    function getBox () {
        return $this->bounding_box;
    }
    
    function getCount () {
        return $this->numSegments;
    }
}

class MetaPath
{
    public $status;
    public $path;
    
    function MetaPath ($status, $path) {
        $this->status = $status;
        $this->path = $path;
    }
}

/**
    We don't care about closed paths. These are the conditions for a failed path:
    1. If the path dead-ends.
    2. If the path attempts to loop back on itself.
    3. If the path exceeds the time of an already completed path.
*/
function algorithm ($start, $end) {
    $debug = true;
    outputDebug("&rarr; algorithm.algorithm()", $debug);
    outputDebug("start = " . $start->toString(), $debug);
    outputDebug("end = " . $end->toString(), $debug);
    // this variable is an array, but at the same time, it's only for paths of the same time.
    $completedPath = array();
    // determine start type and, if necessary, create the first segment
    if ($start instanceof Station2) {
        outputDebug("*** creating startPaths ***", $debug);
        $startPaths = createPaths($start, $end);
        outputDebug("*** creating startPaths : DONE ***", $debug);
    }
    
    // determine end type and, if necessary, create the end segment
    if ($end instanceof Station2) {
        outputDebug("*** creating endPaths ***", $debug);
        $endPaths = createPaths($end, $start);
        outputDebug("*** creating endPaths : DONE ***", $debug);
    }
    
    /*
        This loop will continue to run until we have exhausted all possibilities.
        For each start path that exists, compare the lines with those of the end paths.
    */
    while (count($startPaths) > 0) {
        ksort($startPaths);
        ksort($endPaths);
        //$keys = array_keys($startPaths);
        //$startArray = array_shift($startPaths);
        outputDebug("startPaths count = " . count($startPaths), $debug);
        $newEndPaths = array();
        $newStartPaths = array();
        $buildOnce = true;
        foreach ($startPaths as $startKey => $startArray) {
            outputDebug("startKey = " . $startKey, $debug);
            outputDebug("startArray count = " . count($startArray), $debug);
            /*
                $startArray denotes a list of paths that share the same area, where area
                is denoted by the last station in the path and the destination.
            */
            foreach ($startArray as $startPath) {
                outputDebug("##foreach startArray", $debug);
                $startAdded = false;
                $limit = 0;
                if (count($completedPath) > 0) {
                    $limit = $completedPath[0]->duration;
                }
                if ($limit > 0 && $startPath->duration > $limit) {
                    outputDebug("ignoring startPath because it is too long.", $debug);
                    continue;
                }
                // check to see if this path is complete. If so, we don't need to do anything.
                if ($end instanceof Station2) {
                    if ($debug) {
                        echo "looking for: ";
                        $end->printInfo();
                        echo "currently @: ";
                        $startPath->getCurrentStation()->printInfo();
                    }
                    if ($startPath->getCurrentStation() == $end) {
                        outputDebug("path is complete.", $debug);
                        $completedPath = addPath($completedPath, $startPath);
                        $startAdded = true;
                        continue;
                    }
                }
                $endACount = 0;
                foreach ($endPaths as $endKey => $endArray) {
                    $endcount = 0;
                    if ($debug) {
                        echo "endKey = " . $endKey . "<br/>\n";
                        echo "endPaths count = " . count($endPaths) . "<br/>\n";
                        echo "endArray count = " . count($endArray) . "<br/>\n";
                    }
                    foreach ($endArray as $endPath) {
                        outputDebug($endcount++ . " of " . $endACount, $debug);
                        outputDebug("startpath = " . $startPath->toString(), $debug);
                        outputDebug("endpath = " . $endPath->toString(), $debug);
                        $box = new Box($startPath->getCurrentStation()->point, $endPath->getCurrentStation()->point);
                        if ($startPath->stationBox == null) {
                            if ($box->getArea() > 0) {
                                $startPath->stationBox = $box;
                            }
                        }
                        else if ($box->getArea() < $startPath->stationBox->getArea()) {
                            outputDebug("replacing box with new box.", $debug);
                            $startPath->stationBox = $box;
                        }
                        if (count($completedPath) > 0) {
                            $limit = $completedPath[0]->duration;
                            if ($startPath->stationBox != null) {
                                $cArea = $completedPath[0]->getBox()->getArea();
                                $sArea = $startPath->stationBox->getArea();
                                if ($cArea < $sArea) {
                                    outputDebug($cArea . " < " . $sArea . ". Skipping path check.", $debug);
                                    continue;
                                }
                            }
                        }
                        if ($limit > 0 && $endPath->duration > $limit) {
                            outputDebug("ignoring endPath because it is too long.", $debug);
                            continue;                        
                        }
                        // check to see if both paths have the same current station
                        if ($startPath->getCurrentStation() == $endPath->getCurrentStation()) {
                            outputDebug("startPath and endPath match", $debug);
                            // we have a completed path.
                            $path = mergePaths($startPath, $endPath);
                            $completedPath = addPath($completedPath, $path);
                            $limit = $completedPath[0]->duration;
                            $startAdded = true;
                        }
                        // see if the paths have an overlapping line
                        $line = compareLines($startPath, $endPath);
                        if ($line != null) {
                            outputDebug("found line match of " . $line->name, $debug);
                            // we have an overlapping line
                            if (count($completedPath) > 0) {
                                $limit = $completedPath[0]->duration;
                            }
                            $path = completePath($startPath, $endPath, $line, $limit);
                            if ($path != null) {
                                $completedPath = addPath($completedPath, $path);
                                $limit = $completedPath[0]->duration;
                                $startAdded = true;
                            }
                        } // if ($line != null)
                        else {
                            outputDebug("No line match found between startPath and endPath.", $debug);
                        }
                        /*
                            Even if a path is completed, it isn't guaranteed to be the faster than any new ones.
                            But how do we save cycles from doing repeat information?
                        */
                        /*
                            We need to extend each path that we look at and store it for the next loop.
                        */
                        if ($buildOnce) {
                            $newEndPaths = extendPath($endPath, $newEndPaths, $limit);
                        }
                    } // foreach ($endArray)
                    $endACount++;
                } // foreach ($endPaths)
                if ($buildOnce) {
                    // we only want to build the new paths once, so set it to false after the first loop through
                    // all the paths.
                    $buildOnce = false;
                }
                if (!$startAdded) {
                    $newStartPaths = extendPath($startPath, $newStartPaths, $limit);
                }
            } // foreach ($startArray)
        } // foreach ($startPaths)
        // next loop, we want to overwrite with the new possibilities.
        /*outputDebug("newStartPaths count = " . count($newStartPaths), $debug);
        outputDebug("startPaths count = " . count($startPaths), $debug);*/
        $startPaths = $newStartPaths;
        outputDebug("startPaths count = " . count($startPaths), $debug);
        /*outputDebug("newEndPaths count = " . count($newEndPaths), $debug);
        outputDebug("endPaths count = " . count($endPaths), $debug);*/
        $endPaths = $newEndPaths;
        outputDebug("endPaths count = " . count($endPaths), $debug);
    } // while
    if ($debug) {
        echo "completedPath count = " . count($completedPath) . "<br/>\n";
        foreach ($completedPath as $cpath) {
            if ($cpath instanceof Path) {
                echo $cpath->toString() . "<br/>\n";
            }
        }
        echo "<br/>\n";
    }
    return $completedPath;
}

function extendPath ($path, $pathTable, $limit) {
    $debug = true;
    outputDebug("algorithm.extendPath()", $debug);
    outputDebug("current path = " . $path->toString(), $debug);
    /*
        $pathTable = {
            [$area1] = {
                $path1,
                $path2
            }
            [$area2] = {
                $path3,
                $path4
            }
        }
    */
    if ($limit > 0 && $path->duration > $limit) {
        outputDebug("path to extend (" . $Path->duration . ") > ($limit). discarding.", $debug);
        return $pathTable;
    }
    // get the last station of the path
    $currentStation = $path->getCurrentStation();
    $nextLines = $currentStation->getLines();
    $visited = array();
    foreach ($nextLines as $nextLine) {
        $connections = $nextLine->getConnections();
        foreach ($connections as $connection) {
            $id = $connection->id;
            $nextStation = retrieveStation($id);
            // check if the connection has been visited
            if (isVisited($nextStation, $path->visited) || in_array($id, $visited)) {
                // ignore this station because path has already been there
                outputDebug("path has already been created or station has been visited.", $debug);
                continue;
            }
            $visited[] = $id;
            // create the new extended path
            $newPath = new Path($path, $nextStation, $nextLine, $connection);
            outputDebug("new path = " . $newPath->toString(), $debug);
            // add to the path table
            // get the current table
            $area = $newPath->getBox()->getArea();
            /*$oldArea = $path->getBox()->getArea();
            if ($area > ($oldArea + 0.5)) {
                outputDebug("area ($area) > oldArea ($oldArea). discarding.", $debug);
                continue;
            }*/
            if ($limit > 0 && $newPath->duration > $limit) {
                outputDebug("newPath (" . $newPath->duration . ") > ($limit). discarding.", $debug);
                continue;
            }
            $pathArray = $pathTable["$area"];
            if ($pathArray == null) {
                // create a new array
                $pathArray = array();
            }
            $pathArray[] = $newPath;
            //outputDebug("adding to $area array", $debug);
            $pathTable["$area"] = $pathArray;
        }
    }
    return $pathTable;
}

function addPath ($completedPaths, $path) {
    $debug = true;
    outputDebug("algorithm.addPath(): adding " . $path->toString(), $debug);
    if (count($completedPaths) == 0) {
        $completedPaths[] = $path;
    }
    else if ($path->duration < $completedPaths[0]->duration) {
        // erase all the old path values
        $completedPaths = array();
        $completedPaths[] = $path;
    }
    else {
        outputDebug("path is longer than an existing one. ignoring.", $debug);
        //$completedPaths[] = $path;
    }
    return $completedPaths;
}

function compareLines ($startPath, $endPath) {
    $debug = false;
    outputDebug("algorithm.compareLines()", $debug);
    $startLines = $startPath->getLines();
    $endLines = $endPath->getLines();
    foreach ($startLines as $startLine) {
        foreach ($endLines as $endLine) {
            if ($startLine->getName() == $endLine->getName()) {
                return $startLine;
            }
        }
    }
}

function createPaths ($station, $destination) {
    $debug = false;
    if ($debug) {
        echo "algorithm.createPaths()<br/>\n";
        echo "looking at station: " . $station->toString() . "<br/>\n";
    }
    $open = array();
    $connectionCreated = array();
    foreach($station->getLines() as $line) {
        foreach($line->getConnections() as $connection) {
            $id = $connection->id;
            if (in_array($id, $connectionCreated)) {
                continue;
            }
            // get associated marker
            $station2 = retrieveStation($id);
            $connectionCreated[] = $id;
            if ($debug) {
                echo "retrieved marker from connection (" . $id . "): ";
                $station2->printInfo();
            }
            $lines = $station->getOverlapLines($station2);
            $segment = new Segment($station, $station2, $lines, $connection->duration,$connection->type);
            $visited = array();
            $visited[] = $station;
            $visited[] = $station2;
            $new_box = new Box($station2->point, $destination->point);
            $path = new Path($segment, $visited, $destination);
            if ($debug) {
                echo "creating path: ";
                echo $path->toString() . "<br/>\n";
            }
            /*if ($station == $station2) {
                return $path;
            }*/
            $area = $new_box->getArea();
            $array = $open["$area"];
            if ($array == null) {
                $array = array();
            }
            $array[] = $path;
            $open["$area"] = $array;
        }
    }
    return $open;
}

/**
    @return an array of MergePaths
*/
function completePath ($startPath, $endPath, $line, $duration) {
    $debug = true;
    outputDebug("algorithm.completePath()", $debug);
    outputDebug("duration = " . $duration, $debug);
    /*
        The startPath runs from start to finish. The endPath runs from finish to start. We need to combine based
        on the line that we found. We know that eventually we'll go down the line until we hit the other path.
        We don't know if we're on the line or not. If we already are, then we're only going in one direction.
    */
    
    $finished = 0;
    $currentStation = $startPath->getCurrentStation();
    $currentLine = $currentStation->getLine($line->name);
    $connections = $currentLine->getConnectionList($startPath->getLastStation()->getID());
    foreach ($connections as $connection) {
       // $path = $startPath->createCopy();
        // create a new segment from the connection
        $nextStation = retrieveStation($connection->id);
        // check to see if it's the destination or if it's visited
        $visited = isVisited($nextStation, $startPath->visited);
        if ($visited) {
            // we only care to go in the forward direction
            if ($debug) {
                echo "continuePath() :: already visited.<br/>\n";
            }
            continue;
        }
        /*$lines = $currentStation->getOverlapLines($nextStation);
        $segment = new Segment($currentStation, $nextStation, $lines, $connection->duration, $connection->type);
        if ($debug) {
            echo "created segment: " . $segment->toString() . "<br/>\n";
        }
        $path->addSegment($segment, new Box($nextStation->point, $path->getDestination()->point));
        if ($debug) {
            echo "<br/>\n" . $path->toString() . "<br/>\n";
        }*/
        $path = new Path($startPath, $nextStation, $currentLine, $connection);
        if ($duration > 0 && $path->duration > $duration) {
            if ($debug) {
                echo "duration is longer than an already completed path. discard.<br/>\n";
            }
            return null;
        }
        if ($nextStation == $endPath->getCurrentStation()) {
            if ($debug) {
                echo "algorithm.completePath() : found match, creating completed path<br/>\n";
            }
            return mergePaths($path, $endPath);
        }
        while ($finished == 0 && $path->getCurrentStation() != $endPath->getCurrentStation()) {
            if ($debug) {
                echo "loop:<br/>\n" . $path->toString() . "<br/>\n";
            }
            $array = continuePath($path, $endPath->getCurrentStation(), $currentLine->name);
            foreach ($array as $metaPath) {
                /*
                    status: 0 = continued path, 1 = match with end path found, 2 = end of line, 3 = found destination
                */
                if ($metaPath->status == 3) {
                    if ($duration > 0 && $path->duration < $duration) {
                        $area = $metaPath->path->getBox()->getArea();
                        $oldArea = $startPath->getBox()->getArea();
                        if ($area < $oldArea) {
                            return $metaPath->path;
                        }
                    }
                }
                if ($metaPath->status == 1) {
                    if ($duration > 0 && $path->duration < $duration) {
                        $area = $metaPath->path->getBox()->getArea();
                        $oldArea = $startPath->getBox()->getArea();
                        if ($area < $oldArea) {
                            return mergePaths($metaPath->path, $endPath);
                        }
                    }
                }
                if ($finished != 0 && $metaPath->status == 0) {
                    $path = $metaPath->path;
                    $finished == 0;
                }
                else {
                    $finished = $metaPath->status;
                }
            }
        }
    }
    if ($debug) {
        echo "could not find a path because both directions either have been visited or are at the end of the line.<br/>\n";
    }
    return null;
}

function continuePath ($path, $destination, $lineName) {
    $debug = true;
    outputDebug("algorithm.continuePath()", $debug);
    $currentStation = $path->getCurrentStation();
    $currentLine = $currentStation->getLine($lineName);
    $connections = $currentLine->getConnectionList($path->getLastStation()->getID());
    if ($debug) {
        echo "currentStation = " . $currentStation->getName() . " (" . $currentStation->getID() . ")<br/>\n";
        echo "currentLine = " . $currentLine->name . "<br/>\n";
        echo "continuePath() :: found " . count($connections) . " connections.<br/>\n";
    }
    $newPaths = array();
    if (count($connections) == 0) {
        // end of the line. no match.
        outputDebug("0 connections. end of line.", $debug);
        $newPaths[] = new MetaPath(2, $path);
        return $newPaths;
    }
    foreach ($connections as $connection) {
        // create a new segment from the connection
        outputDebug("connection->id = " . $connection->id, $debug);
        $nextStation = retrieveStation($connection->id);
        $visited = isVisited($nextStation, $path->visited);
        if ($visited) {
            if (count($connections) == 1) {
                // end of the line. no match.
                outputDebug("end of line.", $debug);
                $newPaths[] = new MetaPath(2, $path);
                return $newPaths;
            }
            else {
                // we only care to go in the forward direction
                outputDebug("continuePath() :: already visited.", $debug);
                continue;
            }
        }
        $lines = $currentStation->getOverlapLines($nextStation);
        $newPath = new Path($path, $nextStation, $currentLine, $connection);
        $pathDestination = $path->getDestination();
        if ($destination instanceof Station2 && $nextStation->getID() == $destination->getID()) {
            outputDebug("found match, creating completed path", $debug);
            $newPaths[] = new MetaPath(1, $newPath);
            continue;
        }
        if ($pathDestination instanceof Station2 && $nextStation->getID() == $pathDestination->getID()) {
            outputDebug("found path destination, creating completed path", $debug);
            $newPaths[] = new MetaPath(3, $newPath);
            continue;
        }
        outputDebug("adding path to list = " . $newPath->toString(), $debug);
        $newPaths[] = new MetaPath(0, $newPath);
    }
    return $newPaths;
}

function isVisited ($station, $visited) {
    foreach ($visited as $v) {
        if ($v->getName() == $station->getName()) {
            return true;
        }
    }
    return false;
}

function mergePaths ($startPath, $endPath) {
    $debug = true;
    outputDebug("algorithm.mergePaths()", $debug);
    outputDebug("start path = " . $startPath->toString(), $debug);
    outputDebug("end path = " . $endPath->toString(), $debug);
    $pathSize = $endPath->getCount() - 1;
    $currentSegment = $startPath->getCurrentSegment();
    $startLines = $currentSegment->getSegmentLines();
    $endLines = $endPath->getCurrentSegment()->getSegmentLines();
    $lines = linesIntersect($startLines, $endLines);
    $newPath = $startPath->createCopy();
    if (count($lines) == 0) {
        $newPath->addSegment(new Segment($startPath->getCurrentStation(), $endPath->getCurrentStation(), $lines, "60", "transfer"), new Box($currentSegment->m2->point, $startPath->getDestination()->point));
    }
    for ($i = $pathSize; $i > -1; $i--) {
        $segment = $endPath->getSegment($i);
        $reverseSegment = $segment->reverseSegment();
        $newPath->addSegment($reverseSegment, new Box($segment->m2->point, $newPath->getDestination()->point));
    }
    return $newPath;
}

/**
    Builds the marker from data in the database and returns it.
    @param id<String> the marker id
    @return <Station2> a Station2 object
*/
function retrieveStation ($id) {
    global $stations;
    $debug = false;
    outputDebug("algorithm.retrieveStation($id)", $debug);
    $marker = $stations[$id];
    if ($marker != null) {
        outputDebug("$id retrieved from cache.", true);
        return $marker;
    }
    $query = "SELECT * FROM markers WHERE id='$id'";
    outputDebug($query, $debug);
    $result = mysql_query($query) or die("Unable to retrieve marker of $id from the database.");
    if (count($result) > 1) {
        // error! there should only be one result
        echo "algorithm.getMarker() returned more than one result.<br/>\n";
    }
    $query_array = mysql_fetch_array($result, MYSQL_BOTH);
    $marker = new Station2($query_array['lat'], $query_array['lng'], $query_array['id'], $query_array['name']);
    $query = "SELECT * FROM connections WHERE marker_id='$id'";
    outputDebug($query, $debug);
    $result = mysql_query($query) or die("Unable to retrieve connections for $id from the database.");
    while ($c_array = mysql_fetch_array($result, MYSQL_BOTH)) {
        $connection = new Connection($c_array['id'], $c_array['type'], $c_array['start'], $c_array['end'], $c_array['day'], $c_array['duration']);
        $line = $marker->getLine($c_array['line']);
        if ($line == null) {
            // line doesn't exist yet, so create it
            $line_query = "SELECT * FROM trains WHERE name='" . $c_array['line'] . "'";
            outputDebug($line_query, $debug);
            $line_result = mysql_query($line_query) or die("Unable to retrieve line information for " . $c_array['line'] . " from the database.");
            if (count($line_result) > 1) {
                echo "algorithm.getMarker() returned more than one result when retrieving line " . $c_array['line'];
            }
            $line_array = mysql_fetch_array($line_result, MYSQL_BOTH);
            $line = new Line($line_array['img'], $line_array['url'], $line_array['name']);
            // add the line to the marker
            $marker->addLine($line);
        }
        // add the connection to the line
        $line->addConnection($connection);
    } // connection loop
    // done, return marker
    if ($debug) {
        $marker->printInfo();
    }
    $stations[$id] = $marker;
    outputDebug("$id retrieved from database.", true);
    return $marker;
}

function outputDebug ($text, $debug) {
    if ($debug) {
        echo $text . "<br/>\n";
    }
}

?>
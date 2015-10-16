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
    
    function cleanupDescription ($desc) {
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
    private $isJunction;
    
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
    
    function getOverlapLines ($station) {
        $lines = array();
        $keys = array_keys($this->lines);
        $mkeys = array_keys($station->lines);
        for ($i = 0; $i < count($keys); $i++) {
            $name = $this->lines[$keys[$i]]->name;
            for ($j = 0; $j < count($mkeys); $j++) {
                $name2 = $station->lines[$mkeys[$j]]->name;
                if ($name == $name2) {
                    $line = $this->lines[$keys[$i]];
                    $lines[] = $line;
                }
            }
        }
        return $lines;
    }
    
    function getConnectingLines ($station, $type) {
        $lines = array();
        $keys = array_keys($this->lines);
        $mkeys = array_keys($station->lines);
        for ($i = 0; $i < count($keys); $i++) {
            $name = $this->lines[$keys[$i]]->name;
            for ($j = 0; $j < count($mkeys); $j++) {
                $name2 = $station->lines[$mkeys[$j]]->name;
                if ($name == $name2) {
                    $line = $this->lines[$keys[$i]];
                    $connections = $line->getConnectionType($type);
                    foreach ($connections as $connection) {
                        if ($connection->id == $station->id) {
                            $lines[] = $line;
                            break;
                        }
                    }
                }
            }
        }
        return $lines;
    }
    
    function getURL () {
        return $this->m_url;
    }
    
    /**
        Returns true if the station is a junction. This is determined by
        counting all the connections and counting them if the station
        hasn't been already visited. This provides the number of unique
        stations that is connected to this station. If the number is 2,
        then we know that it's a station between two other stations. If
        the number is 1, then we know that it's an end station.
    */
    function isJunction () {
        if ($this->isJunction == null) {
            $visitedStations = array();
            $stationCount = 0;
            // walk through each line
            foreach ($this->lines as $line) {
                // walk through
                foreach ($line->connections as $connection) {
                    if (!in_array($connection->id, $visitedStations)) {
                        $stationCount++;
                        $visitedStations[] = $connection->id;
                    }
                }
            }
            if ($stationCount <= 2) {
                $this->isJunction = false;
            }
            else {
                $this->isJunction = true;
            }
        }
        return $this->isJunction;
    }
    
    function setLastLine ($line) {
        $this->last_line = $line->name;
    }
    
    function setURL ($url) {
        $this->m_url = $url;
    }
    
    function toString () {
        return $this->name . " (" . $this->id . ")";
    }
    
    function printInfo () {
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
        $lines = $currentStation->getConnectingLines($station, $connection->type);
        //$lines = $currentStation->getOverlapLines($station);
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
        $box = new Box($currentStation->point, $destination->point);
        $this->bounding_box = $box;
        if ($v != null) {
            $this->visited = array_merge($this->visited, $v);
        }
        $this->destination = $destination;
        $this->currentLines = $segment->lines;
    }
    
    function constructor2 ($path, $path2) {
        $this->pathArray = array_merge($path->pathArray, $path2->pathArray);
        $this->duration = $path->duration + $path2->duration;
        $this->visited = array_merge($path->visited, $path2->visited);
        $this->destination = $path->destination;
        $this->currentLines = $path2->currentLines;
        $this->numSegments = $path->numSegments + $path2->numSegments;
        $this->bounding_box = new Box($path->getCurrentStation()->point, $destination->point);
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
    
    function addSegment ($segment) {
        $this->pathArray[] = $segment;
        $this->duration = $this->duration + $segment->t;
        $box = new Box($segment->m2->point, $this->getDestination()->point);
        $this->bounding_box = $box;
        $this->visited[] = $segment->m2;
        $this->numSegments = $this->numSegments + 1;
        $this->currentLines = $segment->lines;
    }
    
    function toString()
    {
        $s = "Path(" . $this->duration . "," . count($this->pathArray) . "," . $this->bounding_box->getArea() . ") = ";
        foreach ($this->pathArray as $segment) {
            //if ($segment->connection != "transfer") {
                $s .= "{";
                $s .= $segment->toString();
                $s .= "}";
            //}
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
    
    function getFirstStation () {
        return $this->pathArray[0]->m1;
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

/**
 *  A MetaPath carries not just a Path, but also metadata for that path.
 *
 *  "status" is the state of the path. The possible
 */
class MetaPath
{
    public static $complete = "completed";
    private $metadata;
    public $path;
    
    function MetaPath ($status, $path) {
        $this->metadata = array();
        $this->metadata["status"] = $status;
        $this->path = $path;
    }
    
    function getStatus () {
        return $this->metadata["status"];
    }
    
    function toString () {
        return "(" . $this->metadata["status"] . ") " . $this->path->toString();
    }
}

function addToArray ($pathArray, $metaPath) {
    $area = $metaPath->path->getBox()->getArea();
    $array = $pathArray["$area"];
    if ($array == null) {
        $array = array();
    }
    $array[] = $metaPath;
    $pathArray["$area"] = $array;
    return $pathArray;
}

/**
    The location is the user inputted address or station object. However, it may not
    be the best place to start. There are several different scenarios that have
    obvious path choices. If it is a station and is the last station of the line, the
    algorithm doesn't need to determine options until it hits a station with multiple
    lines. If it isn't the last station, it may be one where either there is only one
    line or has multiple lines that have the same destination stations. If it is an 
    address and the stations are all on the same line or group of lines, then the
    station closest to the destination should be used.
*/
function findStartStations ($location, $destination) {
    $paths = array();
    $debug = true;
    $factor = 1.2;
    outputDebug("findStartStations (" . $location->toString() . ", " . $destination->toString() . ")", $debug);
    $originalBox = new Box($location->point, $destination->point);
    #outputDebug("original box = " . $originalBox->getArea(), $debug);
    if ($location instanceof Station2) {
        $visitedStations = array();
        foreach ($location->getLines() as $line) {
            foreach ($line->getConnections() as $connection) {
                // ignore already visited stations
                $cid = $connection->id;
                outputDebug("Looking at next connection " . $line->getName() . "-" . $cid, $debug);
                if (in_array($cid, $visitedStations)) {
                    outputDebug("visited. skipping.", $debug);
                    continue;
                }
                $nextStation = retrieveStation($cid);
                $visitedStations[] = $cid;
                // create a new path with this connection
                $segment = new Segment($location, $nextStation, $location->getOverlapLines($nextStation), $connection->duration, $connection->type);
                $visited = array();
                if ($location->isJunction()) {
                    $visited[] = $location;
                }
                if ($nextStation->isJunction()) {
                    #outputDebug("nextStation is a junction.", $debug);
                    $visited[] = $nextStation;
                    #outputDebug("segment = " . $segment->toString(), $debug);
                    $path = new Path($segment, $visited, $destination);
                    $originalArea = $originalBox->getArea();
                    $pathArea = $path->getBox()->getArea();
                    outputDebug("findStartStations.compareArea = " . $pathArea . " > " . $originalArea, $debug);
                    if ($originalBox->getArea()*$factor > $path->getBox()->getArea()) {
                        $metaPath = new MetaPath("start", $path);
                        outputDebug("creating path: " . $metaPath->toString(), $debug);
                        $paths = addToArray($paths, $metaPath);
                    }
                    else {
                        #outputDebug("path (" . $path->getBox()->getArea() . ") is greater than originalBox (" . $originalBox->getArea() . ")", $debug);
                    }
                    continue;
                }
                outputDebug("nextStation is NOT a junction.", $debug);
                // here we know that the nextStation is not a junction.
                $path = new Path($segment, $visited, $destination);
                if ($originalBox->getArea()*$factor < $path->getBox()->getArea()) {
                    outputDebug("path (" . $path->getBox()->getArea() . ") is greater than originalBox (" . $originalBox->getArea() . ")", $debug);
                    continue;
                }
                // extend the path until we find a junction point
                $metaPath = expandPath($path);
                if ($metaPath != null) {
                    outputDebug("creating path: " . $metaPath->toString(), $debug);
                    $paths = addToArray($paths, $metaPath);
                }
            }
        }
    }
    outputDebug("FINISHED findStartStations (" . $location->toString() . ", " . $destination->toString() . ")", $debug);
    return $paths;
}

/**
 *  This method returns a MetaPath. It needs to return that type because
 *  during expansion, the destination station may be found. If that is the
 *  case, then we need a way to indicate that we found a completed path. If
 *  there is no path found, then null is returned.
 *
 *  @return MetaPath the path that is extended or null if the path is no
 *  longer needed (hit a dead-end)
 */
function expandPath ($path) {
    // TODO modify method to check for path duration and to check whether
    // path has found a destination station.
    $debug = false;
    $currentStation = $path->getCurrentStation();
    outputDebug("current station = " . $currentStation->toString(), $debug);
    $lastStation = $path->getLastStation();
    outputDebug("last station = " . $lastStation->toString(), $debug);
    // if we're here, we know that currentStation is not a junction
    $visitedStations = array();
    $finished = false;
    foreach ($currentStation->getLines() as $line) {
        outputDebug("expandPath on line " . $line->name, $debug);
        foreach ($line->getConnections() as $connection) {
            $cid = $connection->id;
            outputDebug("current path : " . $path->toString(), $debug);
            outputDebug("looking at station = " . $cid, $debug);
            // TODO this if statement may not be necessary
            if (in_array($cid, $visitedStations)) {
                // already visited, so we ignore
                outputDebug("already visited " . $cid . ". ignoring.", $debug);
                continue;
            }
            if ($cid == $lastStation->getID()) {    
                if (count($line->getConnections()) == 1) {
                    // this is a terminal path. it won't go where we want it to go
                    outputDebug("terminal path = " . $path->toString(), $debug);
                    return;
                }
                // we don't want to backtrack. ignore.
                continue;
            }
            // if we're here, we found the other station
            // there should be only one station, so no point
            $nextStation = retrieveStation($cid);
            outputDebug("found next station = " . $nextStation->toString(), $debug);
            $path->addSegment(new Segment($currentStation, $nextStation, $currentStation->getConnectingLines($nextStation, $connection->type), $connection->duration, $connection->type));
            if ($nextStation == $path->getDestination()) {
                // we found the destination station
                return new MetaPath("complete", $path);
            }
            if ($nextStation->isJunction()) {
                return new MetaPath("extended", $path);
            }
            $newPath = expandPath($path);
            #outputDebug("recursive complete.", $debug);
            //if ($newPath != null) {
                return $newPath;
            //}
        }
    }
    // we hit an end point and the only connection is backtracking.
    // don't care about the path at this point because it isn't useful.
    // we should check, however, to see if it is the destination point.
    return null;
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
    $completedPaths = array();
    $starting = array();
    $finishing = array();
    $startPaths = array();
    $endPaths = array();
    
    $starts = findStartStations($start, $end);
    foreach ($starts as $metaPathArray) {
        foreach ($metaPathArray as $metaPath) {
            if ($metaPath->getStatus() == "complete") {
                $completedPaths = addPath($completedPaths, $metaPath->path);
                continue;
            }
            $currentStation = $metaPath->path->getCurrentStation();
            $startPaths = createPaths2($currentStation, $end, $startPaths);
            $array = $starting[$currentStation->getID()];
            if ($array == null) {
                $array = array();
            }
            $duration = $metaPath->path->duration;
            $array["$duration"] = $metaPath->path;
            $starting[$currentStation->getID()] = $array;
        }
    }
    $ends = findStartStations($end, $start);
    foreach ($ends as $metaPathArray) {
        foreach ($metaPathArray as $metaPath) {
            if ($metaPath->getStatus() == "complete") {
                $completedPaths = addPath($completedPaths, $metaPath->path);
                continue;
            }
            $currentStation = $metaPath->path->getCurrentStation();
            $endPaths = createPaths2($currentStation, $start, $endPaths);
            //$starting[$currentStation->getID()] = $metaPath->path;
            $array = $finishing[$currentStation->getID()];
            if ($array == null) {
                $array = array();
            }
            $duration = $metaPath->path->duration;
            $array["$duration"] = $metaPath->path;
            $finishing[$currentStation->getID()] = $array;
        }
    }
    // determine start type and, if necessary, create the first segment
    
    /*if ($start instanceof Station2) {
        outputDebug("*** creating startPaths ***", $debug);
        $startPaths = createPaths($start, $end);
        outputDebug("*** creating startPaths : DONE ***", $debug);
    }
    
    // determine end type and, if necessary, create the end segment
    if ($end instanceof Station2) {
        outputDebug("*** creating endPaths ***", $debug);
        $endPaths = createPaths($end, $start);
        outputDebug("*** creating endPaths : DONE ***", $debug);
    }*/
    
    $finished = array();
    outputDebug("start search.", $debug);
    $completedPaths = algorithmLoop3($startPaths, $endPaths, $completedPaths);
    outputDebug("total number of paths found = " . count($completedPaths), $debug);
    foreach ($completedPaths as $path) {
        outputDebug("completedPath is " . $path->toString(), $debug);
        $station = $path->getFirstStation();
        $startArray = $starting[$station->getID()];
        ksort($startArray);
        $startPath = array_shift($startArray);
        if ($startPath != null) {
            outputDebug("adding startPath: " . $startPath->toString(), $debug);
            $path = new Path($startPath, $path);
        }
        $station = $path->getCurrentStation();
        $finishArray = $finishing[$station->getID()];
        ksort($finishArray);
        $finishPath = array_shift($finishArray);
        if ($finishPath != null) {
            outputDebug("adding finishPath: " . $finishPath->toString(), $debug);
            $path = mergePaths($path, $finishPath);
        }
        outputDebug("real path is " . $path->toString(), $debug);
        $finished[] = $path;
    }
    return $finished;
    //return algorithmLoop2($starts, $ends);
}

function algorithmLoop ($startPaths, $endPaths) {
    $debug = false;
    // this variable is an array, but at the same time, it's only for paths of the same time.
    $completedPaths = array();
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
                    $limit = $completedPaths[0]->duration;
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
                        $completedPaths = addPath($completedPaths, $startPath);
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
                        //outputDebug($endcount++ . " of " . $endACount, $debug);
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
                        if (count($completedPaths) > 0) {
                            $limit = $completedPaths[0]->duration;
                            if ($startPath->stationBox != null) {
                                $cArea = $completedPaths[0]->getBox()->getArea();
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
                            $completedPaths = addPath($completedPaths, $path);
                            $limit = $completedPaths[0]->duration;
                            $startAdded = true;
                        }
                        // see if the paths have an overlapping line
                        $line = compareLines($startPath, $endPath);
                        if ($line != null) {
                            outputDebug("found line match of " . $line->name, $debug);
                            // we have an overlapping line
                            if (count($completedPaths) > 0) {
                                $limit = $completedPaths[0]->duration;
                            }
                            $path = completePath($startPath, $endPath, $line, $limit);
                            if ($path != null) {
                                $completedPaths = addPath($completedPaths, $path);
                                $limit = $completedPaths[0]->duration;
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
        echo "completedPath count = " . count($completedPaths) . "<br/>\n";
        foreach ($completedPaths as $cpath) {
            if ($cpath instanceof Path) {
                echo $cpath->toString() . "<br/>\n";
            }
        }
        echo "<br/>\n";
    }
    return $completedPaths;
}

function algorithmLoop2 ($startPaths, $endPaths) {
    $debug = true;
    // this variable is an array, but at the same time, it's only for paths of the same time.
    $completedPaths = array();
    /*
        This loop will continue to run until we have exhausted all possibilities.
        For each start path that exists, compare the lines with those of the end paths.
    */
    $limit = 0;
    while (count($startPaths) > 0) {
        ksort($startPaths);
        ksort($endPaths);
        //$keys = array_keys($startPaths);
        //$startArray = array_shift($startPaths);
        #outputDebug("startPaths count = " . count($startPaths), $debug);
        $newEndPaths = array();
        $newStartPaths = array();
        $buildOnce = true;
        foreach ($startPaths as $startKey => $startArray) {
            #outputDebug("startKey = " . $startKey, $debug);
            #outputDebug("startArray count = " . count($startArray), $debug);
            /*
                $startArray denotes a list of paths that share the same area, where area
                is denoted by the last station in the path and the destination.
            */
            foreach ($startArray as $startMetaPath) {
                $startPath = $startMetaPath->path;
                if ($startMetaPath->getStatus() == "complete") {
                    $completedPaths = addPath($completedPaths, $path);
                    continue;
                }
                #outputDebug("##foreach startArray", $debug);
                $startAdded = false;
                if (count($completedPath) > 0) {
                    $limit = $completedPaths[0]->duration;
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
                        $completedPaths = addPath($completedPaths, $startPath);
                        continue;
                    }
                }
                $endACount = 0;
                foreach ($endPaths as $endKey => $endArray) {
                    $endcount = 0;
                    if ($debug) {
                        #echo "endKey = " . $endKey . "<br/>\n";
                        #echo "endPaths count = " . count($endPaths) . "<br/>\n";
                        #echo "endArray count = " . count($endArray) . "<br/>\n";
                    }
                    foreach ($endArray as $endMetaPath) {
                        $endPath = $endMetaPath->path;
                        if ($endMetaPath->getStatus() == "complete") {
                            $completed = true;
                            $completedPaths = addPath($completedPaths, $path);
                            continue;
                        }
                        //outputDebug($endcount++ . " of " . $endACount, $debug);
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
                        if (count($completedPaths) > 0) {
                            $limit = $completedPaths[0]->duration;
                            if ($startPath->stationBox != null) {
                                $cArea = $completedPaths[0]->getBox()->getArea();
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
                            $completedPaths = addPath($completedPaths, $path);
                            $limit = $completedPaths[0]->duration;
                            $startAdded = true;
                        }
                        // see if the paths have an overlapping line
                        $line = compareLines($startPath, $endPath);
                        if ($line != null) {
                            outputDebug("found line match of " . $line->name, $debug);
                            // we have an overlapping line
                            if (count($completedPaths) > 0) {
                                $limit = $completedPaths[0]->duration;
                            }
                            $path = completePath($startPath, $endPath, $line, $limit);
                            if ($path != null) {
                                $completedPaths = addPath($completedPaths, $path);
                                $limit = $completedPaths[0]->duration;
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
                        if ($buildOnce && !$completed) {
                            $newEndPaths = extendPath2($endPath, $newEndPaths, $limit);
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
                    $newStartPaths = extendPath2($startPath, $newStartPaths, $limit);
                }
            } // foreach ($startArray)
        } // foreach ($startPaths)
        // next loop, we want to overwrite with the new possibilities.
        /*outputDebug("newStartPaths count = " . count($newStartPaths), $debug);
        outputDebug("startPaths count = " . count($startPaths), $debug);*/
        $startPaths = $newStartPaths;
        #outputDebug("startPaths count = " . count($startPaths), $debug);
        /*outputDebug("newEndPaths count = " . count($newEndPaths), $debug);
        outputDebug("endPaths count = " . count($endPaths), $debug);*/
        $endPaths = $newEndPaths;
        #outputDebug("endPaths count = " . count($endPaths), $debug);
    } // while
    if ($debug) {
        echo "completedPath count = " . count($completedPaths) . "<br/>\n";
        foreach ($completedPaths as $cpath) {
            if ($cpath instanceof Path) {
                echo $cpath->toString() . "<br/>\n";
            }
        }
        echo "<br/>\n";
    }
    return $completedPaths;
}

function algorithmLoop3 ($startPaths, $endPaths, $completedPaths) {
    $debug = true;
    outputDebug("algorithmLoop3()", $debug);
    // this variable is an array, but at the same time, it's only for paths of the same time.
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
                    $limit = $completedPaths[0]->duration;
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
                        $completedPaths = addPath($completedPaths, $startPath);
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
                        //outputDebug($endcount++ . " of " . $endACount, $debug);
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
                        if (count($completedPaths) > 0) {
                            $limit = $completedPaths[0]->duration;
                            if ($startPath->stationBox != null) {
                                $cArea = $completedPaths[0]->getBox()->getArea();
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
                            $completedPaths = addPath($completedPaths, $path);
                            $limit = $completedPaths[0]->duration;
                            $startAdded = true;
                        }
                        // see if the paths have an overlapping line
                        $line = compareLines($startPath, $endPath);
                        if ($line != null) {
                            outputDebug("found line match of " . $line->name, $debug);
                            // we have an overlapping line
                            if (count($completedPaths) > 0) {
                                $limit = $completedPaths[0]->duration;
                            }
                            $path = completePath($startPath, $endPath, $line, $limit);
                            if ($path != null) {
                                $completedPaths = addPath($completedPaths, $path);
                                $limit = $completedPaths[0]->duration;
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
        echo "completedPath count = " . count($completedPaths) . "<br/>\n";
        foreach ($completedPaths as $cpath) {
            if ($cpath instanceof Path) {
                echo $cpath->toString() . "<br/>\n";
            }
        }
        echo "<br/>\n";
    }
    return $completedPaths;
}

function extendPath ($path, $pathTable, $limit) {
    $debug = false;
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
                //outputDebug("path has already been created or station has been visited.", $debug);
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

function extendPath2 ($path, $pathTable, $limit) {
    $debug = false;
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
                //outputDebug("path has already been created or station has been visited.", $debug);
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
            $pathArray[] = new MetaPath("searching", $newPath);
            //outputDebug("adding to $area array", $debug);
            $pathTable["$area"] = $pathArray;
        }
    }
    return $pathTable;
}

function addPath ($completedPaths, $path) {
    $debug = false;
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

function createPaths2 ($station, $destination, $open) {
    $debug = false;
    $factor = 1.2;
    if ($debug) {
        echo "algorithm.createPaths()<br/>\n";
        echo "looking at station: " . $station->toString() . "<br/>\n";
    }
    $originalBox = new Box($station->point, $destination->point);
    outputDebug("createPaths2().originalBox = " . $originalBox->getArea(), $debug);
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
            $segment = new Segment($station, $station2, $lines, $connection->duration, $connection->type);
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
            outputDebug("box comparison: " . $area . " < " . $originalBox->getArea(), $debug);
            if ($area < $originalBox->getArea()*$factor) {
                outputDebug("adding path: " . $path->toString(), $debug);
                $array = $open["$area"];
                if ($array == null) {
                    $array = array();
                }
                $array[] = $path;
                $open["$area"] = $array;
            }
        }
    }
    return $open;
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
            $segment = new Segment($station, $station2, $lines, $connection->duration, $connection->type);
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
    outputDebug("algorithm.completePath($duration)", $debug);
    outputDebug("duration = " . $duration, $debug);
    /*
        The startPath runs from start to finish. The endPath runs from finish to start. We need to combine based
        on the line that we found. We know that eventually we'll go down the line until we hit the other path.
        We don't know if we're on the line or not. If we already are, then we're only going in one direction.
    */
    
    $finished = "continue";
    $currentStation = $startPath->getCurrentStation();
    $currentLine = $currentStation->getLine($line->name);
    $connections = $currentLine->getConnections();
    foreach ($connections as $connection) {
        outputDebug("completePath() connection = " . $connection->id, $debug);
        if ($connection->id == $startPath->getLastStation()->getID()) {
            continue;
        }
       // $path = $startPath->createCopy();
        // create a new segment from the connection
        $nextStation = retrieveStation($connection->id);
        // check to see if it's the destination or if it's visited
        $visited = isVisited($nextStation, $startPath->visited);
        if ($visited) {
            // we only care to go in the forward direction
            outputDebug("continuePath() :: already visited.", $debug);
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
        outputDebug("current path = " . $path->toString(), $debug);
        if ($duration > 0 && $path->duration > $duration) {
            outputDebug("duration is longer than an already completed path. discard.", $debug);
            return;
        }
        if ($nextStation == $endPath->getCurrentStation()) {
            outputDebug("algorithm.completePath() : found match, creating completed path", $debug);
            return mergePaths($path, $endPath);
        }
        $pathArray = array();
        $pathArray[] = $path;
        $newPathArray = array();
        while (count($pathArray) > 0) {
            $newPath = array_shift($pathArray);
            if ($debug) {
                echo "loop:<br/>\n" . $newPath->toString() . "<br/>\n";
            }
            $array = continuePath($newPath, $endPath->getCurrentStation(), $currentLine->name);
            outputDebug("found " . count($array) . " paths.", $debug);
            foreach ($array as $metaPath) {
                outputDebug($metaPath->getStatus() . " " . $metaPath->path->toString(), $debug);
                /*
                    status: 0 = continued path, 1 = match with end path found, 2 = end of line, 3 = found destination
                */
                if ($metaPath->getStatus() == "found") {
                    if ($duration <= 0 && $metaPath->path->duration < $duration) {
                        $area = $metaPath->path->getBox()->getArea();
                        $oldArea = $startPath->getBox()->getArea();
                        if ($area < $oldArea) {
                            return $metaPath->path;
                        }
                    }
                }
                if ($metaPath->getStatus() == "match") {
                    $pathDuration = $metaPath->path->duration;
                    if ($duration <= 0 || $metaPath->path->duration < $duration) {
                        $area = $metaPath->path->getBox()->getArea();
                        $oldArea = $startPath->getBox()->getArea();
                        if ($area < $oldArea) {
                            outputDebug("returning completed path: " . $metaPath->path->toString(), $debug);
                            return mergePaths($metaPath->path, $endPath);
                        }
                    }
                }
                // check area of the path
                $area = $metaPath->path->getBox()->getArea();
                $oldArea = $newPath->getBox()->getArea();
                if ($area < $oldArea) {
                    if ($duration <= 0 || $duration > $metaPath->path->duration) {
                        outputDebug("adding path to list.", $debug);
                        $newPathArray[] = $metaPath->path;
                    }
                }
            }
            if (count($pathArray) == 0) {
                $newCount = count($newPathArray);
                if ($newCount > 0) {
                    outputDebug("pathArray exhausted. newPathArray ($newCount) set.", $debug);
                    $pathArray = $newPathArray;
                    $newPathArray = array();
                }
            }
        }
    }
    outputDebug("1. directions have already been visited or 2. end of the line or 3. there is already a better path.", $debug);
    return null;
}

function continuePath ($path, $destination, $lineName) {
    $debug = true;
    outputDebug("algorithm.continuePath()", $debug);
    $currentStation = $path->getCurrentStation();
    $currentLine = $currentStation->getLine($lineName);
    $connections = $currentLine->getConnections();
    if ($debug) {
        echo "currentStation = " . $currentStation->getName() . " (" . $currentStation->getID() . ")<br/>\n";
        echo "currentLine = " . $currentLine->name . "<br/>\n";
        echo "continuePath() :: found " . count($connections) . " connections.<br/>\n";
    }
    $newPaths = array();
    foreach ($connections as $connection) {
        if ($connection->id == $path->getLastStation()->getID()) {
            continue;
        }
        // create a new segment from the connection
        outputDebug("connection->id = " . $connection->id, $debug);
        $nextStation = retrieveStation($connection->id);
        $visited = isVisited($nextStation, $path->visited);
        if ($visited) {
            if (count($connections) == 1) {
                // end of the line. no match.
                outputDebug("end of line.", $debug);
                $newPaths[] = new MetaPath("EOL", $path);
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
            $newPaths[] = new MetaPath("match", $newPath);
            continue;
        }
        if ($pathDestination instanceof Station2 && $nextStation->getID() == $pathDestination->getID()) {
            outputDebug("found path destination, creating completed path", $debug);
            $newPaths[] = new MetaPath("found", $newPath);
            continue;
        }
        outputDebug("adding path to list = " . $newPath->toString(), $debug);
        $newPaths[] = new MetaPath("continue", $newPath);
    }
    outputDebug("algorithm.continuePath() : DONE", $debug);
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
    $debug = false;
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
        outputDebug("$id retrieved from cache.", $debug);
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
    outputDebug("$id retrieved from database.", $debug);
    return $marker;
}

function outputDebug ($text, $debug) {
    if ($debug) {
        echo $text . "<br/>\n";
    }
}

?>
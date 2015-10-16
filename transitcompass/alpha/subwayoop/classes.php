<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #
    #   classes.php
    #   This file contains classes that are used in the algorithm.
    #

/**
    lat = y, lng = x
*/
class Box {
    public $top;
    public $bottom;
    public $left;
    public $right;
    
    /**
        Constructor. Builds the box based on the start point and end point.
        The box is built upon the outer bounds found by analysing the two 
        points plus two points found at the equidistant edges of a line
        intersecting the midpoint of the line between the start and end
        point with the same distance.
    */
    function Box($start_point, $end_point) {
        if ($start_point == $end_point) {
            $top = $start_point->lat;
            $bottom = $top;
            $left = $start_point->lng;
            $right = $left;
            return;
        }
        #echo "Box Constructor<br/>";
        #echo "P1={" . $start_point->lat . "," . $start_point->lng . "}<br/>";        
        #echo "P2={" . $end_point->lat . "," . $end_point->lng . "}<br/>";
        $midpoint = $this->findMidpoint($start_point, $end_point);
        #echo "P3={" . $midpoint->lat . "," . $midpoint->lng . "}<br/>";
        $slope = $this->findSlope($start_point, $end_point);
        #echo "getting distance<br/>";
        $distance = $midpoint->getDistanceMiles($start_point);
        #echo "new distance is $distance<br/>";
        //$p1 = $this->findP1($slope, $midpoint, $distance/4);
        $miles_per_degree_latitude = 69.023;
        #echo "slope = " . $slope . "<br/>";
        $p1 = new Point($midpoint->lng,$distance/$miles_per_degree_latitude);
        $p2 = new Point($midpoint->lng,$distance/$miles_per_degree_latitude);
        if ($slope != "vertical") {
            $p1 = $this->gP1($slope, $start_point, $end_point);
            $p2 = $this->gP2($slope, $start_point, $end_point);
        }
        #echo "P4={" . $p1->lat . "," . $p1->lng . "}<br/>";
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
        $lng = $p1->lng - $p2->lng;
        if ($lng != 0) {
            return ($p1->lat - $p2->lat)/$lng;
        }
        return "vertical";
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

class Connection {
    public $id;
    public $type;
    public $start;
    public $end;
    public $duration;
    public $day;
    
    function Connection($param1 = null, $param2 = null, $param3 = null, $param4 = null, $param5 = null, $param6 = null) {
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
    
    function constructor1 ($id) {
        $this->id = $id;
    }
    
    function constructor2 ($id, $type) {
        $this->id = $id;
        $this->type = $type;
    }
    
    function constructor6 ($param1, $param2, $param3, $param4, $param5, $param6) {
        $this->id = $param1;
        $this->type = $param2;
        $this->start = $param3;
        $this->end = $param4;
        $this->day = $param5;
        $this->duration = $param6;
        //echo "day = $this->day<br/>\n";
    }
    
    function toString () {
        return $id;
    }
}

class Line {
    public $img;
    public $url;
    public $name;
    public $connections;
    
    function Line($img, $url, $name) {
        $this->img = $img;
        $this->url = $url;
        $this->name = $name;
        $this->connections = array();
    }
    
    function addConnection($connection) {
        $this->connections[] = $connection;
    }
    
    function getConnection($i) {
        return $this->connections[$i];
    }

    function getConnections () {
        return $this->connections;
    }
    
    function getConnectionCount() {
        return count($this->connections);
    }
    
    function getConnectionType ($type) {
        $array = array(); 
        for ($i = 0; $i < count($this->connections); $i++) {
            if ($this->connections[$i]->type == $type) {
                $array[] = $this->connections[$i];
            }
        }
        return $array;
    }
    
    function getName () {
        return $this->name;
    }
    
    function getUniqueConnectionCount() {
        $unique = array();
        foreach($this->connections as $connection) {
            $unique[] = $connection->id;
        }
        $unique = array_unique($unique);
        return count($unique);
    }
    
    function printInfo() {
        echo $this->name . " {<br/>";
        $count = count($this->connections);
        for ($i = 0; $i < $count; $i++) {
            $connection = $this->connections[$i];
            echo $connection->id . "<br/>";
        }
        echo "}<br/>\n";
    }
}

class Location2
{
    public $id;
    public $point;
    public $name;
    public $number;
    public $street;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $phone;

    function Location2 ($i, $la, $ln, $na, $nu, $st, $ci, $sta, $zi, $co, $ph) {
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

class Marker {
    public $point;
    public $id;
    public $name;
    public $lines;
    public $m_url;
    private $last_line;
    
    function Marker($lat, $lng, $id, $name) {
        $this->point = new Point($lat, $lng);
        $this->id = $id;
        $this->name = $name;
        $this->lines = array();
    }
    
    function addLine($line) {
        $this->lines[$line->name] = $line;
        $this->last_line = $line->name;
    }
    
    function getLat() {
        return $this->point->lat;
    }
    
    function getLng() {
        return $this->point->lng;
    }
    
    function getLine($i) {
        return $this->lines[$i];
    }
    
    function getLines() {
        return $this->lines;
    }
    
    function getLineCount() {
        return count($this->lines);
    }
    
    function getLineNames() {
        $names = array();
        foreach ($this->lines as $line) {
            $names[] = $line->name;
        }
        return $names;
    }
    
    function getLastLine() {
        return $this->lines[$this->last_line];
    }
    
    function getOverlap($marker) {
        $lines = array();
        for ($i = 0; $i < count($this->lines); $i++) {
            $name = $this->lines[$i]->name;
            for ($j = 0; $j < count($marker->lines); $j++) {
                $name2 = $marker->lines[$j]->name;
                if ($name == $name2) {
                    $lines[] = $name;
                }
            }
        }
        return $lines;
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
                    $lines[] = $this->lines[$keys[$i]];
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
        echo "(" . $this->point->lat . "," . $this->point->lng . ") " . $this->name . " [" . $this->id . "]<br/>";
    }
}

class Path2 {
    public $path;
    public $t;
    public $bounding_box;
    public $visited = array();
    public $single_path_length = 0;
    
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
    
    function constructor8 ($path, $marker, $line, $time, $connection, $box, $v, $length) {
        $this->path = $path->path;
        $this->t = $path->t;
        $last_marker = $this->getLastMarker();
        $lines = array();
        $lines[] = $line;
        if ($v != null) {
            $this->visited = array_merge($this->visited, $v);
        }
        $this->single_path_length = $length;
        $this->addSegment(new Segment($last_marker, $marker, $lines, $time, $connection), $box);
    }
    
    function constructor5 ($path, $time, $bounding_box, $visited, $single_path_length) {
        $this->path = $path;
        $this->t = $time;
        $this->bounding_box = $bounding_box;
        $this->visited = $visited;
        $this->single_path_length = $single_path_length;
    }
    
    function constructor3 ($segment, $box, $v) {
        $this->path = array();
        $this->path[] = $segment;
        $this->t = $segment->t;
        $this->bounding_box = $box;
        if ($v != null) {
            $this->visited = array_merge($this->visited, $v);
        }
    }
    
    function createCopy () {
        return new Path2($this->path, $this->t, $this->bounding_box, $this->visited, $this->single_path_length); 
    }
    
    function addSegment($segment, $box) {
        $this->path[] = $segment;
        $this->t = $this->t + $segment->t;
        $this->bounding_box = $box;
        $this->visited[] = $segment->m1;
        $this->visited[] = $segment->m2;
    }
    
    function getLastMarker() {
        return $this->getLastSegment()->m2;
    }
    
    function getLastSegment() {
        return $this->path[count($this->path) - 1];
    }
    
    function getSTLMarker() {
        return $this->getLastSegment()->m1;
    }
    
    function toString()
    {
        $s = "Path2(" . $this->t . "," . count($this->path) . ") = ";
        foreach ($this->path as $segment) {
            $s .= "{";
            $s .= $segment->toString();
            $s .= "}";
        }
        $s .= "<br/>";
        return $s;
    }
}

class Point {
    public $lat;
    public $lng;
    
    function Point($param1 = null, $param2 = null) {
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
    
    function constructor0() {
        $this->lat = "";
        $this->lng = "";
    }
    
    function constructor2($lat, $long) {
        $this->lat = $lat;
        $this->lng = $long;
    }
    
    function getLat() {
        return $this->lat;
    }
    
    function getLng() {
        return $this->lng;
    }
        
    function getDistanceMiles($point) {
        $miles_per_degree_latitude = 69.023;
        $miles_per_degree_longitude = 51.075;
        $m1_lat = $miles_per_degree_latitude * $this->lat;
        $m1_lng = $miles_per_degree_longitude * $this->lng;
        $m2_lat = $miles_per_degree_latitude * $point->lat;
        $m2_lng = $miles_per_degree_longitude * $point->lng;
        
        return sqrt(pow(($m1_lat - $m2_lat),2) + pow(($m1_lng - $m2_lng),2));
    }
    
    function getDistance($point) {
        return sqrt(pow(($lat - $point->lat),2) + pow(($lng - $point->lng),2));
    }
    
    function toString() {
        echo "lat: $this->lat<br/>";
        echo "lng: $this->lng<br/>";
    }
}
    
    
/**
	This class defines a station that is closest to a given marker.
*/
class Station
{
	// the marker for the station
	public $marker;
	// the distance to the input address
	public $dist2loc;
	
	/**
		The constructor for this station.
		
		@param $m the marker for this station
		@param $d the distance from the input address to this station
	*/
	function Station($m, $d) {
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

class Segment {
    public $m1;
    public $m2;
    public $lines;
    public $t;
    // this is the connection type: local, express, transfer
    public $connection;
    
    function Segment($m1, $m2, $lines, $time, $connection) {
        $this->m1 = $m1;
        $this->m2 = $m2;
        $this->lines = $lines;
        #echo "segment " . $this->m1->toString() . "-&gt;" . $this->m2->toString() . " has " . count($lines) . " lines<br/>\n";
        $this->t = $time;
        $this->connection = $connection;
    }
    
    function getSegmentLines () {
        return $this->m1->getOverlapLines($this->m2);
    }
    
    function reverseSegment () {
        return new Segment($this->m2, $this->m1, $this->lines, $this->t, $this->connection);
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
        }
        $s .= " *" . $this->t . "*";
        $s .= " #" . $this->connection . "#";
        return $s;
    }
}

class TimePath extends Path2 {
    public $finish_time;
    
    function TimePath ($param1 = null, $param2 = null, $param3 = null, $param4 = null, $param5 = null, $param6 = null, $param7 = null, $param8 = null, $param9 = null, $param10 = null) {
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
    
    function constructor10 ($path, $marker, $line, $t, $connection, $box, $v, $length, $finish_time) {
        $this->finish_time = $finish_time;
        parent::constructor9($path, $marker, $line, $t, $connection, $box, $v, $length);
    }
    
    function constructor9 ($path, $marker, $line, $t, $connection, $box, $v, $length, $finish_time) {
        $this->finish_time = $finish_time;
        parent::constructor8($path, $marker, $line, $t, $connection, $box, $v, $length);
    }
    
    function constructor6 ($path, $t, $bounding_box, $visited, $single_path_length, $finish_time) {
        $this->finish_time = $finish_time;
        parent::constructor5($path, $t, $bounding_box, $visited, $single_path_length);
    }
    
    function constructor4 ($segment, $box, $v, $start_time) {
        $this->finish_time = $start_time + $segment->t;
        parent::constructor3($segment, $box, $v);
    }
    
    function createCopy () {
        return new TimePath($this->path, $this->t, $this->bounding_box, $this->visited, $this->single_path_length, $this->finish_time); 
    }
    
    function toString()
    {
        $s = "TimePath(" . $this->t . "," . count($this->path) . "," . $this->finish_time . ") = ";
        foreach ($this->path as $segment) {
            $s .= "{";
            $s .= $segment->toString();
            $s .= "}";
        }
        $s .= "<br/>";
        return $s;
    }
}

/**
    @deprecated
*/
/*class Location {
    public $point;
    public $desc;
    
    function Location($param1 = null, $param2 = null, $param3 = null) {
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
    
    function constructor1($param1) {
        $this->desc = $param1;
        $this->point = new Point();
    }
    
    function constructor3($lat, $long, $description) {
        $this->point = new Point($lat, $long);
        $string = $this->cleanupDescription($description);
        $this->desc = $string;
    }
    
    function getLat() {
        return $this->point->getLat();
    }
    
    function getLng() {
        return $this->point->getLng();
    }
    
    function printInfo() {
        echo "$this->desc {<br/>";
            echo $this->point->toString();
        echo "}<br/>";
    }
    
    function cleanupDescription($desc) {
        $desc = str_replace("+", " ", $desc);
        $desc = str_replace("%2C", ",", $desc);
        return $desc;
    }
}*/

/**
    @deprecated
*/
class Node {
    public $g;
    public $h;
    public $f;
    public $p;
    public $m;
    public $t;
    
    function Node($param1 = null, $param2 = null, $param3 = null, $param4 = null) {
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
    
    function constructor1($marker) {
        $this->m = $marker;
        $this->g = "";
        $this->h = "";
        $this->f = "";
        $this->p = "";
        $this->t = "";
    }
    
    function constructor3($param1, $param2, $param3) {
        $this->g = $param1;
        $this->h = $param2;
        $this->f = $param1 + $param2;
        $this->p = "";
        $this->t = "";
        $this->m = $param3;
    }
    
    function constructor4($param1, $param2, $param3, $param4) {
        $this->g = $param1;
        $this->h = $param2;
        $this->f = $param1 + $param2;
        $this->p = $param3;
        $this->t = "";
        $this->m = $param4;
    }
}
?>
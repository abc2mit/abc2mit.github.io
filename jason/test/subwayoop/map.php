<?php
    // creates an icon
    function createIcon($image) {
        //echo "$image\n";
        echo "// [[icon]]\n";
        echo "var icon = new GIcon();\n";
        echo "icon.image = \"images/$image\";\n";
        echo "icon.shadow = \"images/mm_20_shadow.png\";\n";
        echo "icon.iconSize = new GSize(12, 20);\n";
        echo "icon.shadowSize = new GSize(22, 20);\n";
        echo "icon.iconAnchor = new GPoint(6, 20);\n";
        echo "icon.infoWindowAnchor = new GPoint(5, 1);\n";
        echo "// [[end_icon]]\n";
    }
    
    function map($type, $data) {
        switch ($type) {
            case 'location':
                mapLocation($data[0], $data[1]);
                break;
            case 's2s':
                break;
            case 'dir':
                break;
        }
    }
    
    /**
        @param $starts[]<Location> the list of start locations
        @param $ends[]<Location> the list of end locations
        @param $path[]<Segment> the list of segments that denote the fastest path
    */
    
    function mapStations($stations, $marker_name) {
        // plot all stations in list
        foreach ($stations as $station) {
            echo "var points = [];\n";
            echo "points.push($marker_name);\n";
            $station_marker = $station->marker;
            markStation($station_marker);
            echo "points.push(point);\n";
            echo "map.addOverlay(new GPolyline(points));\n";
        }
    }
    
    function mapPath($path_array, $cpath) {
            // center on start
            $start_marker = $path_array[0]->m1;
            $s_lng = $start_marker->getLng();
            $s_lat = $start_marker->getLat();
            echo "map.setCenter(new GLatLng($s_lat,$s_lng), 13);\n";
            
            // mark the transfer points
            for($i = 1; $i < count($cpath); $i++) {
                $marker = $cpath[$i]->m1;
                markTransfer($marker);
            }
            
            // plot ending point
            $end_marker = $path_array[count($path_array) - 1]->m2;
            
            // display path
            echo "var points = [];\n";
            foreach ($path_array as $segment) {
                $marker = $segment->m1;
                $lng = $marker->getLng();
                $lat = $marker->getLat();
                echo "var point = new GLatLng($lat, $lng);\n";
                echo "points.push(point);\n";
            }
            $marker = $path_array[count($path_array) - 1]->m2;
            $lng = $marker->getLng();
            $lat = $marker->getLat();
            echo "//last marker<br/>\n";
            echo "var point = new GLatLng($lat, $lng);\n";
            echo "points.push(point);\n";
            echo "map.addOverlay(new GPolyline(points, \"#FF0000\", 10));\n";
    }
        
    function mapLocation($locations, $stations) {
        global $location_marker, $default_zoom, $center_lng, $center_lat;
        // more than one location found
        if (count($locations) > 1) {
            echo "map.setCenter(new GLatLng($center_lat,$center_lng), $default_zoom);\n";
            foreach ($locations as $location) {
                markLocation($location, $location_marker, "point");
            }
        }
        // only one location found
        else if (count($locations) == 1) {
            markLocation($locations[0], $location_marker, "loc_point");
            echo "map.setCenter(loc_point, $default_zoom);\n";
            foreach ($stations as $loc) {
                echo "var points = [];\n";
                echo "points.push(loc_point);\n";
                $station = $loc['m'];
                markStation($station);
                echo "points.push(point);\n";
                echo "map.addOverlay(new GPolyline(points));\n";
            }
        }
        // no locations found
        else {
            echo "map.setCenter(new GLatLng($center_lat,$center_lng), $default_zoom);";
        }    
    }
    
    
    
    /**
        @param $location<Location> the location of the object
        @param $marker_img<String> the image file to use for display
        @param $name<String> the name to display on the popup
    */
    function markLocation($location, $marker_img, $name) {
        //global $location_marker;
        echo "// mark location\n";
        $loc_name = $location->desc;
        $lat = $location->getLat();
        $lng = $location->getLng();
        echo "var $name = new GLatLng($lat,$lng);\n";
        echo "var name = \"$loc_name\";\n";
        createIcon($marker_img);
        echo "var marker = createMarker2($name, name, icon);\n";
        echo "map.addOverlay(marker);\n";
        echo "// end location\n\n";
    }
    
    function markMarker($marker, $marker_img, $name) {
        echo "// mark marker\n";
        $loc_name = $marker->name;
        $lat = $marker->getLat();
        $lng = $marker->getLng();
        echo "var $name = new GLatLng($lat,$lng);\n";
        echo "var name = \"$loc_name\";\n";
        createIcon($marker_img);
        echo "var marker = createMarker2($name, name, icon);\n";
        echo "map.addOverlay(marker);";
        echo "// end marker\n\n";
    }
    
    function markStation($marker) {
        global $station_marker_img;
        echo "// mark station\n";
        $station_name = $marker->name;
        echo "var name = \"$station_name\";\n";
        $station_lat = $marker->getLat();
        $station_lng = $marker->getLng();
        echo "var point = new GLatLng($station_lat,$station_lng);\n";
        $lines = $marker->getLines();
        $length = count($lines);
        //$length = count($station) - 1;
        echo "var lines = new Array($length);\n";
        for ($i = 0; $i < $length; $i++) {
            $line = $lines[$i];
            echo "lines[$i] = \"$line->name\";\n";
        }
        createIcon($station_marker_img);
        echo "var marker = createStation2(point, name, lines, icon);\n";
        echo "map.addOverlay(marker);\n\n";
    }
    
    function markTransfer($marker) {
        global $transfer_marker_img;
        echo "// mark station\n";
        $station_name = $marker->name;
        echo "var name = \"$station_name\";\n";
        $station_lat = $marker->getLat();
        $station_lng = $marker->getLng();
        echo "var point = new GLatLng($station_lat,$station_lng);\n";
        $lines = $marker->getLines();
        $length = count($lines);
        //$length = count($station) - 1;
        echo "var lines = new Array($length);\n";
        for ($i = 0; $i < $length; $i++) {
            $line = $lines[$i];
            echo "lines[$i] = \"$line->name\";\n";
        }
        createIcon($transfer_marker_img);
        echo "var marker = createStation2(point, name, lines, icon);\n";
        echo "map.addOverlay(marker);\n\n";
    }
    
    /*function mapPath($path, $cpath) {
        echo "var points = [];\n";
        foreach ($path as $marker) {
            $lng = $marker->getLng();
            $lat = $marker->getLat();
            echo "var point = new GLatLng($lat,$lng);\n";
            foreach ($cpath as $disp_marker) {
                if ($marker == $disp_marker) {
                    markStation($marker);
                }
            }
            echo "points.push(point);\n";
        }
        echo "map.addOverlay(new GPolyline(points));\n";
    }*/
?>
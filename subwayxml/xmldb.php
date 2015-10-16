<?php
    // initialise the database
    function init_db() {
        global $markers, $indent, $current_element, $file;
        $xml_parser = xml_parser_create();
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 1);
        xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
        xml_set_element_handler($xml_parser, "start_element", "end_element");
        xml_set_character_data_handler($xml_parser, "character_data");
        
        if (!($fp = @fopen($file, "r"))) {
            echo "could not open file<br/>";
            return false;
        }
        
        while ($data = fread($fp, 8192)) {
            if (!xml_parse($xml_parser, $data, feof($fp))) {
                die(sprintf("XML error: %s at line %d\n",xml_error_string(xml_get_error_code($xml_parser)),xml_get_current_line_number($xml_parser)));
            }
        }
        
        xml_parser_free($xml_parser);
    }
    
    // capture start element
    function start_element($parser, $name, $attribs) {
        global $indent, $markers, $current_element;
        //$current_element = $name;
        //echo "getting $name<br/>\n";
        // grab marker information
        if ($name == "MARKER") {
            $id = "";
            //$marker = array();
            // get attributes
            if (count($attribs)) {
                //$attrs = array();
                foreach ($attribs as $k => $v) {
                    //$attrs[$k] = $v;
                    //echo "$k = $attrs[$k]<br/>\n";
                    if ($k == "LAT") {
                        $lat = $v;
                    }
                    if ($k == "LNG") {
                        $lng = $v;
                    }
                    if ($k == "ID") {
                        $id = $v;
                    }
                    if ($k == "NAME") {
                        $name = $v;
                    }
                }
                // add attributes to marker
                //$marker[] = $attrs;
                $marker = new Marker($lat, $lng, $id, $name);
                $marker_name = $marker->name;
                $marker_id = $marker->id;
                //echo "xmldb: added $marker_name ($marker_id)<br/>\n";
            }
            // add marker to end of markers
            $current_element = $id;
            $markers[$id] = $marker;
        }
        // grab line information
        else if ($name == "LINE") {
            // grab last marker
            //$marker = $markers[count($markers) - 1];
            $marker = $markers[$current_element];
            //$line = array();
            // grab attributes
            if (count($attribs)) {
                //$attrs = array();
                foreach ($attribs as $k => $v) {
                    //$attrs[$k] = $v;
                    //echo "$k = $attrs[$k]<br/>\n";
                    if ($k == "IMG") {
                        $img = $v;
                    }
                    if ($k == "URL") {
                        $url = $v;
                    }
                    if ($k == "NAME") {
                        $name = $v;
                    }
                }
                // add attributes to line
                //$line[] = $attrs;
                $line = new Line($img, $url, $name);
            }
            // add line to end of marker data
            //$marker[] = $line;
            $marker->addLine($line);
            // save marker back to markers
            $markers[$current_element] = $marker;
        }
        else if ($name == "CONNECTION") {
            // grab last marker
            $marker = $markers[$current_element];
            // grab last line
            //$count = count($marker) - 1;
            //$line = $marker[$count];
            $line = $marker->getLastLine();
            //$connection = array();
            if (count($attribs)) {
                //$attrs = array();
                foreach ($attribs as $k => $v) {
                    //$attrs[$k] = $v;
                    if ($k == "ID") {
                        $id = $v;
                    }
                    if ($k == "TYPE") {
                        $type = $v;
                    }
                    if ($k == "START") {
                        $start = $v;
                    }
                    if ($k == "END") {
                        $end = $v;
                    }
                    if ($k == "DAY") {
                        $day = $v;
                    }
                    if ($k == "DURATION") {
                        $duration = $v;
                    }
                }
                // add attributes to connection
                //$connection[] = $attrs;
                //echo "$id,$type,$start,$end,$duration<br/>\n";
                $connection = new Connection($id, $type, $start, $end, $day, $duration);
            }
            // add connection to end of line data
            //$line[] = $connection;
            $line->addConnection($connection);
            // add line back to marker
            //$marker[$count] = $line;
            $marker->setLastLine($line);
            // add marker back to markers
            $markers[$current_element] = $marker;
        }
    }
    
    // capture end element
    function end_element($parser, $name) {
    }
    
    // capture character data
    function character_data($parser, $data) {
    }
?>
<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #
    #   location_functions.php
    #   This file contains functions that are used to retrieve location information.
    #
    
    function findNearestStations2($location) {
        global $markers;
        $debug = false;
        // the list of stations to return
        $stations = array();
        $maxDist = 0;
        foreach ($markers as $marker) {
            $currentStation = new Station($marker, distance2Loc($location, $marker));
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
        if ($debug) {
            echo "end stations:<br/>\n";
            print_r($stations);
            echo "<br/>\n";
        }
        // this array should be the three closest stations	
        return $stations;
    }


    function fixSearchString ($search) {
        $debug = false;
        // replace all backslashes
        $search = str_replace("\\", "", $search);
        // replace all empty strings
        $search = str_replace("%20", " ", $search);
        if ($debug) {
            echo "search string after fix = $search<br/>";
        }
        return $search;
    }
    
    function fixLocationString ($location) {
        $debug = false;
        if ($debug) {
            echo "original string = $location<br/>"; 
        }
        // replace all &
        $location = str_replace('\x26', '&', $location);
        if ($debug) {
            echo "new string = $location<br/>";
        }
        return $location;
    }
    
    /**
    	Retrieves the set of locations. If Google returns a single set,
    	then we get the geocode data and attach it to our query as a
    	location object. Otherwise, we return a list of possible locations.
    	
    	@param $q<String> the incoming query
    	@return TODO
    	@deprecated
    */
    function getLocations($q) {
    	global $locations;
    	// TODO this function needs to be refined
    	$locations = array();
    	
    	$googleData = getGoogleData($q);
    	$count = substr_count($googleData, ",markers:[");
    	#echo "count = $count<br/>";
    	if ($count > 0) {
    		#echo "marker found<br/>";
			$location = getLocation($googleData, $q);
			$locations[] = $location;
			return $locations;
    	}
    	// want to retrieve all the different address choices
    	#echo "location_functions.getLocations::multiple locations<br/>";
    	return getLocationOptions($googleData);
    }
    
    /**
        An improved version of the getLocations method.
    */
    function getLocations2($q) {
        $gData = getGoogleData($q);
        $locations = getGoogleLocations($gData);
        return $locations;
    }
    
    /**
        Convert an incoming address to a URL-compliant format and run a
        query to Google. Parse the data and return. Note that it only 
        reads the first 5000 characters.
        
        @param $address<String> the incoming address to search for
        
        @return the result of the query or false if there is nothing
    */
    function getGoogleData ($address) {
        $debug = false;
        // if this data is null, then we return false
        if (!isset($address)) {
            return false;
        }
        
        // encode the address to URL format
        $address_url = urlencode($address);
        if ($debug) {
            echo $address . "--" . $address_url . "<br/>";
        }
        // create the query
        $url = "http://maps.google.com/maps?q=$address_url&output=js";
        // open the stream
        $h = fopen($url, "r");
        // read in 100000 characters and store
        $googleData = fread($h, 50000);
        // close the stream
        fclose($h);
    
    	return $googleData; 
    }
    
    /**
        Returns an array of locations. There are many different possible combinations.
 
        id:"addr",lat:40.773069999999997,lng:-73.875640000000004,image:"http://maps.google.com/intl/en_us/mapfiles/arrow.png",
        drg:false,adr:true,elms:[3,2,6,10,1,9],laddr:"LaGuardia Airport (LaGuardia Airport)",sxti:"LaGuardia Airport",
        sxst:"",sxsn:"",sxct:"",sxpr:"",sxpo:"",sxcn:"US",sxph:"",svaddr:"LaGuardia Airport"
        
        id:"addr",lat:40.732112999999998,lng:-73.979840999999993,image:"http://www.google.com/intl/en_us/mapfiles/arrow.png",
        drg:true,adr:true,elms:[3,2,6,10,1,9,2,11],laddr:"9 Stuyvesant Oval, New York, NY 10009",sxti:"",
        sxst:"Stuyvesant Oval",sxsn:"9",sxct:"New York",sxpr:"NY",sxpo:"10009",sxcn:"US",sxph:"",svaddr:"9 Stuyvesant Oval, New York, NY 10009"
        
        id:"A",lat:40.737158999999998,lng:-73.992245999999994,image:"http://www.google.com/intl/en_us/mapfiles/markerA.png",
        drg:true,elms:[4,1,6,1,10,2,9,1,5,2,11],laddr:"21 E 16th St # 1, New York, NY 10003 (Union Square Cafe)",sxti:"Union Square Cafe",
        sxst:"E 16th St # 1",sxsn:"21",sxct:"New York",sxpr:"NY",sxpo:"10003",sxcn:"US",sxph:"+12122434020"
        
        id:"A",lat:40.762931999999999,lng:-73.983745999999996,image:"http://www.google.com/intl/en_us/mapfiles/markerA.png",
        drg:true,elms:[4,1,6,1,10,2,9,1,5,2,11],laddr:"236 W 52nd St # 1, New York, NY 10019 (Victor's Cafe)",sxti:"Victor's Cafe",
        sxst:"W 52nd St # 1",sxsn:"236",sxct:"New York",sxpr:"NY",sxpo:"10019",sxcn:"US",sxph:"+12125867714",
        
        id:"addr",lat:40.648139999999998,lng:-73.949299999999994,image:"http://maps.google.com/intl/en_us/mapfiles/arrow.png",
        drg:false,adr:true,elms:[3,2,6,10,1,9],laddr:"Nostrand Ave \x26 Albemarle Rd, Brooklyn, NY 11226",sxti:"",
        sxst:"Nostrand Ave",sxsn:"",sxct:"",sxpr:"NY",sxpo:"11226",sxcn:"US",sxph:"",svaddr:"Nostrand Ave \x26 Albemarle Rd, Brooklyn, NY 11226"
        
        id:"addr",lat:40.735880000000002,lng:-73.987390000000005,image:"http://maps.google.com/intl/en_us/mapfiles/arrow.png",
        drg:false,adr:true,elms:[3,2,6,10,1,9],laddr:"E 17th St \x26 Irving Pl, New York, NY 10003",sxti:"",
        sxst:"E 17th St",sxsn:"",sxct:"New York",sxpr:"NY",sxpo:"10003",sxcn:"US",sxph:"",svaddr:"E 17th St \x26 Irving Pl, New York, NY 10003"

    */
    function getGoogleLocations ($gData) {
        $debug = false;
        $debug_all = false;
        // {id:" denotes a marker that has been found by Google
        // we need to grab the information from each one.
        // finding the beginning of the relevant data by "markers:"
        $marker_pos = strpos($gData, "markers:[");
        $gData_sub = substr($gData, $marker_pos);
        if ($debug_all) {
            echo $gData_sub . "<br/>";
        }
        $locations = array();
        $len = strlen($gData_sub);
        // TODO need to clean this loop
        while (($strpos = strpos($gData_sub, "{id:")) !== false) {
            $pos = strpos($gData_sub, "{id:") + 4;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos);
            $id = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "id=$id<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "lat:") + 4;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos);
            $lat = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "lat=$lat<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "lng:") + 4;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos);
            $lng = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "lng=$lng<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "laddr:") + 7;
            if ($pos > $len) {
                return $locations;
            }
            
            $quote_pos = strpos($gData_sub,"\"", $pos);
            $address_string = substr($gData_sub, $pos, $quote_pos-$pos);
            if ($debug) {
                echo "laddr=$address_string<br/>";
            }
            
            //$gData_sub = substr($gData_sub, $comma_pos);
            $gData_sub = substr($gData_sub, $quote_pos+1);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "sxti:") + 6;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos)-1;
            $name = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "sxti=$name<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "sxst:") + 6;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos)-1;
            $street = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "sxst=$street<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "sxsn:") + 6;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos)-1;
            $number = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "sxsn=$number<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "sxct:") + 6;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos)-1;
            $city = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "sxct=$city<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "sxpr:") + 6;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos)-1;
            $state = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "sxpr=$state<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "sxpo:") + 6;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos)-1;
            $zip = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "sxpo=$zip<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "sxcn:") + 6;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos)-1;
            $country = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "sxcn=$country<br/>";
            }
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "sxph:") + 6;
            if ($pos > $len) {
                return $locations;
            }
            $comma_pos = strpos($gData_sub,",", $pos)-1;
            $phone = substr($gData_sub, $pos, $comma_pos-$pos);
            if ($debug) {
                echo "sxph=$phone<br/>";
            } 
            
            $gData_sub = substr($gData_sub, $comma_pos);
            $len = strlen($gData_sub);
            $pos = strpos($gData_sub, "svaddr:") + 8;
            if ($pos < $len) {
                $quote_pos = strpos($gData_sub,"\"", $pos);
                $address_string = substr($gData_sub, $pos, $quote_pos-$pos);
                if ($debug) {
                    echo "svaddr=$address_string<br/>";
                }
                if (strpos($address_string, ",") !== false) {
                    if ($city == null || $city == "") {
                        $short_string = stristr($address_string, ",");
                        $city_string = substr($short_string, 1, strpos($short_string, ",", 1) - 1);
                        $city_string = trim($city_string);
                        if ($debug) {
                            echo "city_string=$city_string<br/>";
                        }
                        $city = $city_string;
                    }
                    if (($name == null || $name == "") && ($number == null || $number == "")) {
                        // there's no name (not a restaurant or location search)
                        // no number (not an address)
                        // that means that it's a location (i.e. Union Square)
                        $name_string = substr($address_string, 0, strpos($address_string, ",", 1));
                        $name_string = trim($name_string);
                        $string = fixLocationString($name_string);
                        if (strpos($string, "&") != -1) {
                            $street = $string;
                        }
                        else {
                            $name = $string;
                        }
                        if ($debug) {
                            echo "string=$string<br/>";
                        }
                    }
                }
            }
            
            $name = fixLocationString($name);
            
            $location = new Location2($id, $lat, $lng, $name, $number, $street, $city, $state, $zip, $country, $phone);
            if ($debug) {
                echo $location->toString() . "<br/>";
            }
            $locations[] = $location;
        }
        return $locations;
    }
    
    /**
        Retrieves the location data based on what was retrieved from Google.
        Parses into an array which is then used to create a Location.
        
        @param $googleData<String> the data retrieved by Google
        @param $address<String>the address of this Location
        @return a new Location of the address or false if none is found
        @deprecated
    */
    function getLocation($googleData, $address) {        
        #$n = ereg('lat:([0-9.-]*),lng:([0-9.-]*),', $str, $data);
        $geocode_found = ereg('lat:([0-9.-]*),lng:([0-9.-]*),', $googleData, $data);
        #$address_found = preg_match('addressLines:[([.]*)]'
        
        if ($geocode_found) {
            // create a new Location object
            $ar = new Location($data['1'], $data['2'], $address);
        }
        else {
            $ar = false;
        }
        return $ar;
    }
    
    /**
        Retrieves the list of locations from the data. Some string replacements
        are performed to make it easier to search.
        
        @param $googleData<String> the data from Google
        @return TODO
        @deprecated
    */
    function getLocationOptions($googleData) {
    	// the location options are stored in the "panel:" subcomponent
    	// need to parse it out
    	#echo "getLocationOptions<br/>";
    	// it's too hard without converting these.
    	$new_googleData = str_replace('\\x3c', '<', $googleData);
    	$new_googleData = str_replace('\\x3e', '>', $new_googleData);
    	$new_googleData = str_replace('\\x26amp;', '&', $new_googleData);
    	$new_googleData = str_replace('\\"', '"', $new_googleData);
    	#echo "new_string: $new_googleData<br/>";
    	
    	#$num_locations = preg_match_all("<div class=\\"ref_desc\\">(.*)[0-9]<\/div>", $new_googleData, $data);
    	// search for all data within <div class="ref_desc">...</div>
    	$num_locations = preg_match_all('|<[^>]+ref_desc\\">(.*)</div>|U', $new_googleData, $data);
    	echo "data options:<br/>";
    	print_r($data[1]);
    	echo "<br/>";
    	return $data[1];
    }
?>
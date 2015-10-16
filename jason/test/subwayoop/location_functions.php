<?php
    /**
    	Updated: 2007-10-28
    	Ironically, most of 
    */
    
    #require_once('minixml.inc.php');
    #set_time_limit(0);
    
    /**
    	Retrieves the set of locations. If Google returns a single set,
    	then we get the geocode data and attach it to our query as a
    	location object. Otherwise, we return a list of possible locations.
    */
    function getLocations($q) {
    	global $locations;
    	$locations = array();
    	
    	$googleData = getGoogleData($q);
    	$count = substr_count($googleData, ",markers:[");
    	#echo "count = $count<br/>";
    	if ($count > 0) {
    		#echo "marker found<br/>";
			$location = getGeocode($googleData, $q);
			$locations[] = $location;
			return $locations;
    	}
    	// want to retrieve all the different address choices
    	#echo "location_functions.getLocations::multiple locations<br/>";
    	return getLocationOptions($googleData);
    }
    
    function getGoogleData($address) {
        if (!isset($address)) {
            return false;
        }
        
        $address_url = urlencode($address);
        $url = "http://maps.google.com/maps?q='$address_url'&output=js";
        $h = fopen($url, "r");
        $googleData = fread($h, 5000);
        fclose($h);
    
    	return $googleData; 
    }
    
    function getGeocode($googleData, $address) {        
        #$n = ereg('lat:([0-9.-]*),lng:([0-9.-]*),', $str, $data);
        $geocode_found = ereg('lat:([0-9.-]*),lng:([0-9.-]*),', $googleData, $data);
        #$address_found = preg_match('addressLines:[([.]*)]'
        
        if ($geocode_found) {
            $ar = new Location($data['1'], $data['2'], $address);
        }
        else {
            $ar = false;
        }
        return $ar;
    }
    
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
    	#echo "data:<br/>";
    	#print_r($data[1]);
    	#echo "<br/>";
    	return $data[1];
    }

// might need this proxy stuff later
    /*function proxy_url($url, $proxy_url, $proxy_port) {
        $proxy_cont = '';
        
        $proxy_fp = fsockopen($proxy_url, $proxy_port);
        if (!$proxy_fp) {
            return false;
        }
        fputs($proxy_fp, "GET $url HTTP/1.0\r\nHost: $proxy_url\r\n\r\n");
        while (!feof($proxy_fp)) {
            $proxy_cont .= fread($proxy_fp,4096);
        }
        fclose($proxy_fp);
        $proxy_cont = substr($proxy_cont, strpos($proxy_cont,"\r\n\r\n")+4);
        return $proxy_cont;
    } 

    function filter_proxies($proxy) {
        // seems like all hosts w/ 3127 are similar and need something kooky to work
        return( strpos($proxy, ":3127") === FALSE );
    }
      
    function fetch_proxy_list() {
        $url = "http://www.proxy-list.net/anonymous-proxy-lists.shtml"; 
        $h = fopen($url, "r"); 
        $str = '';
        while (!feof($h)) {
            $str .= fread($h,4096);
        }
        fclose($h); 
        
        if( !$str ) {
            return false;
        }
        
        $matches = array();
        if (! preg_match_all( "/(\d+\.\d+\.\d+\.\d+:\d+)\s*United States/", $str, $matches)) {
          return false; 
        }
        
        // $matches[0] contains whole matches
        // $matches[1] contains matches for subgroup 1
        $filtered = array_filter($matches[1], "filter_proxies");
        $ret = array();
        foreach ($filtered as $pxy) {
          $ret[] = explode(':', $pxy);
        }
        return $ret;
    }*/

?>
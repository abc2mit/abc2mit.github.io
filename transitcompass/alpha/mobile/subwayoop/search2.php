<?php
    #
    #   transitcompass.com
    #   search2.php
    #   v0.1
    #
    #   Integrated into main.php, this file is used to prepare the incoming parameters for the search required.
    #
    
    /*
        We no longer have types of searches, rather we determine based on the incoming. That way, we are more flexible with the inputs. However, we will still determine a "type" based on what we get. There should be four parameters as listed:
        sid: start station id
        did: destination station id
        
        sa: start address
        da: destination address
        
        These are used accordingly in the following cases:
        1. Address to Address. Standard searching, we go from point A to point B using the shortest method possible.
            sid: null
            did: null
            sa: not null
            da: not null
        
        2. Address to Station. This query goes from a point A to a station B.
            sid: not null
            did: null
            sa: not null, should match name of sid
            da: not null
            
            Alternate case: did is not null and sid is null. sa is address, da is name of did.
        
        3. Station to Station. This query goes from a station A to a station B.
            sid: not null
            did: not null
            sa: not null, should match name of sid
            da: not null, should match name of did
        
        4. Location. This query is to find the nearest stations to point A.
            sid: null
            did: null
            sa: not null
            da: null
            
            Alternate case: da is not null and sa is null.
    */
    $sid = $_GET['sid'];
    $did = $_GET['did'];
    $sa = $_GET['sa'];
    $da = $_GET['da'];
    
    if (($sid == "") || ($did == "")) {
        // either A2A or location
        if ($sa == "") {
            if ($da == "") {
                
            }
        }
    }
    
    
?>
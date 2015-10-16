<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #
    
    $oop = "../subwayoop";
    require_once($oop . '/display_classes.php');
    
    $title = "Directions through Public Transporation Systems";
    $head_title = "transitcompass.com // changelist // ";
    $link = "http://transitcompass.com";
    
    $debug = false;
    $logging = false;
    
    include('../subwaydb/db.php');
    // connect to database
    connectDB("digressi_nyc");
    
    $slogan = getSlogan();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta name="Description" content="Directions via Public Transportation Systems (Subway) using Google Maps." />
    <meta name="Keywords" content="geocode, subway, google maps, latitude, longitude, new york, new york city, nyc, directions, mta, map, boston, T, changelist, changes" />
    <meta name="save" content="history" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/map.css" />
    <title><?php echo $head_title . $slogan; ?></title>
  </head>
  <body>
<?php
    $poweredByDiv = new Div("about", "noselect", null, "uses ");
    $poweredByDiv->addDataElement(new Link("http://maps.google.com", "Google Maps"));
    $affiliateDiv = new Div(null, "noselect", "font-size:7pt;color:#CCCCCC;", "(not affiliated with Google)");
    $poweredByDiv->addDataElement($affiliateDiv);
    echo $poweredByDiv->toString();
?>
<table id="header"><tr>
<?php
    $logo_img = new Image("logo", null, "images/logo.png", "transitcompass", null, null); 
    $logo_link = new Link($link, $logo_img);
    $logo_data = new Cell("logo", null, "color:#008000", $logo_link);
    echo $logo_data->toString();
?>
    <td width="100%">
        <table class="form" width="100%">
            <tr>
                <td align="right" rowspan="2" valign="top" style="padding-right:5px;width:250px">
                    <!-- credits -->
<?php
    //$poweredByDiv = new Div("poweredby", "noselect", "vertical-align:top;float:right;text-align:right;font-size:10pt;z-index:-1;", "uses ");
    //$poweredByDiv->addDataElement(new Link("http://maps.google.com", "Google Maps"));
    //$affiliateDiv = new Div(null, "noselect", "font-size:7pt;color:#CCCCCC;", "(not affiliated with Google)");
    //$poweredByDiv->addDataElement($affiliateDiv);
    //echo $poweredByDiv->toString();
?>
                </td>
            </tr>
        </table>
    </td></tr>
</table> <!-- end header table -->
<table id="maptable" style="font-size: 12px;"><tr><td>
<div id="updates">
    <dl>
        <dt class="date">2008 May 15</dt>
        <dd>
            <ul>
                <li>Added total time to the directions display. Thanks for the feedback, Hideko S!</li>
            </ul>
        </dd>
        <dt class="date">2008 May 9</dt>
        <dd>
            <ul>
                <li>Fixed clearing of station data when new data is being entered into the field.</li>
                <li>Fixed instructions listing algorithm.</li>
                <li>Fixed display of subway names on drop down.</li>
            </ul>
        </dd>
        <dt class="date">2008 May 7</dt>
        <dd>
            <ul>
                <li>Updated instructions table look-and-feel.</li>
            </ul>
        </dd>
        <dt class="date">2008 May 3</dt>
        <dd>
            <ul>
                <li>Fixed direction flip.</li>
                <li>References now to / rather than to index.php</li>
            </ul>
        </dd>
        <dt class="date">2008 May 1</dt>
        <dd>
            <ul>
                <li>Finally finished coding a new interface. Address-to-Address, Station-to-Station, Address-to-Station, Station-to-Address, and Location searches are supported.</li>
                <li>Removed the image button which was producing really annoying (and unnecessary) additions to the search URL.</li>
                <li>Added a new button that now looks good.</li>
                <li>Added coloration to the tables to make them prettier.</li>
            </ul>
        </dd>
        <dt class="date">2008 February 5</dt>
        <dd>
            <ul>
                <li>Fixed problem where intersections weren't being found correctly.</li>
                <li>Fixed/improved reporting when search did not find the location.</li>
                <li>Fixed a bug in the marker link next to an end location if the start had multiple locations or no locations.</li>
            </ul>
        </dd>
        <dt class="date">2008 February 4</dt>
        <dd>
            <ul>
                <li>Fixed location display where street is not available.</li>
            </ul>
        </dd>
        <dt class="date">2008 February 2</dt>
        <dd>
            <ul>
                <li>Remembers what map type was last used.</li>
            </ul>
        </dd>
        <dt class="date">2008 February 1</dt>
        <dd>
            <ul>
                <li>Added randomly generated slogans.</li>
            </ul>
        </dd>
        <dt class="date">2008 January 31</dt>
        <dd>
            The big things:
            <ul>
                <li>Alternate start and end stations has been restored to working order.</li>
                <li>Code has been reorganized and cleaned up. End user won't notice, but will make it easier to roll out new fixes/features.</li>
                <li>Fixed bug in algorithm where a path would end at the first station it finds without checking to see if another station is closer.</li>
                <li>New and improved location finding code. It's much more accurate than before and increases granularity of the search results.</li>
                <li>New functionality for determining addresses.</li>
            </ul>
            The little things:
            <ul>
                <li>Fixed output of Start address and End address.</li>
                <li>Improved output of multiple locations.</li>
                <li>If only one address is inserted into the directions form, then it will run an address search instead.</li>
            </ul>
        </dd>
        <dt class="date">2008 January 15</dt>
        <dd>
            <ul>
                <li>Some minor tweaks with the algorithm. Path directions displayed on the right side have been improved.</li>
            </ul>
        </dd>
        <dt class="date">2008 January 10</dt>
        <dd>
            <ul>
                <li>Fixed issues with displaying directions.</li>
            </ul>
        </dd>
        <dt class="date">2008 January 7</dt>
        <dd>
            Wow.. it's been a long day and many things were fixed. I don't think I can list them all, but here goes.
            <ul>
                <li>Copyright added.</li>
                <li>Start and End addresses now use marker icon instead of number.</li>
                <li>Fixed listing of steps. There was a quirkiness of choosing which lines to take.</li>
                <li>Map link was added to the PDF map of the system.</li>
                <li>Transfer times are now factored into the algorithm.</li>
                <li>UI was cleaned up a bit (no more confusing time, transfer, waiting columns).</li>
                <li>Fixed bug where lines from potential end stations to the end location weren't being drawn correctly.</li>
                <li>Fixed some database issues.</li>
            </ul>
        </dd>
        <dt class="date">2008 January 6</dt>
        <dd>
            Defect fixing release! We're still going through the database to make sure everything works right. The algorithm was tweaked to improve performance, especially on locations of long distance. Major changes have been made, so please try it out!
            <ul>
                <li>Fixed issue where certain paths could not be found</li>
                <li>Fixed output problem with times that had seconds &lt; 10</li>
                <li>Modified algorithm to improve accuracy. Also made it more flexible for finding obscure routes.</li>
            </ul>
        </dd>
        <dt class="date">2008 January 4</dt>
        <dd>
        <p>Great news!  After sacrificing a good proportion of our winter vacations, we are able to bring you a massive upgrade to our previous search algorithms.  The number of criteria being considered when searching for the optimal path has been drastically increased.  The search algorithm now considers distance traveled, time traveled, transfer time, walk distance, distance from stop in question to end point (only previous criteria), etc.</p>
<p>After completing the algorithm, copious tests were performed, and compared to our personal experience with those paths.  The tests performed were long paths (Columbia to Jamaica) and paths as short as one stop.  Our new algorithm has exceeded all expectations, and have even taught us faster routes than we ourselves knew.  We tried our best to optimize the speed at which these calculations are performed.</p>
<p>Additional, the drastic changes in our algorithm has caused the output to be significantly different.  Painstaking effort has been made such that the clean, crisp user interface has been preserved, and has been made more reliable.</p>
<p>This vacation has given ample time to plan the future of the sight.  Very exciting things are in store for us as the new year continues.</p>
            <ul>
                <li>Version 3.0 released.</li>
                <li>New algorithm! Search is more accurate than ever before.</li>
            </ul>
        </dd>
    	<dt class="date">2007 December 14</dt>
    	<dd>
            Back and better than ever!<br/>
            I want to post this quick since it's 01:32 and I'm tired. This site has been down and out of commission for a while, but I decided it was time to bring it back.<br/><br/>
            Some things I want to point out:
            <ul>
            <li>It now allows selection of multiple addresses. It will also find the fastest path (not the shortest), which means it <b>can</b> and does differentiate between local and express trains.</li>
            <li>There was no previous inspiration other than my deep frustration with the lack of direction finding while living in NYC.</li>
            <li>This is always a work in progress, so please, feedback is much appreciated! digression-dawt-net-at-gmail-dawt-com</li>
            <li>Yes, I know the look needs to freshen up a bit.. I wanted to bring the functionality back first.. I also have to bring <a href="http://www.bostonsubway.info">bostonsubway.info</a> back as well....</li>
            <li>I do this on my own and in my spare time. It's fun and it's challenging. And no, it doesn't take me away from my girlfriend.</li>
            <li>The database is entirely done <b><i>by hand</i></b>. That's right, folks, I looked at a subway map, measure out the distances and determined a heuristic for differentiating between local and express. The geocodes may be off a bit for the stations; if they are, please shoot me an email and let me know!</li>
            </ul>
            <br/>
    		<ul>
    			<li>Fixed problems with station info when clicking on marker.</li>
    			<li>Changed "Station" search to "Address" search.</li>
    			<li>Added an address example so people know how to use the box.</li>
    			<li>Updated instruction list to use icons instead of letters.</li>
    			<li>Updated instruction list to highlight station names using bold.</li>
    			<li>Updated to Google Maps API v2</li>
    			<li>Thought about update to icon.</li>
    			<li>Made the feedback e-mail links more sane. (Boo to SPAM!)</li>
    		</ul>
    	</dd>
    	<dt class="date">2007 October 29</dt>
    	<dd>
    		<ul>
    			<li>Revitalized the project after letting it go stale.</li>
    			<li>Brought the new algorithm online.</li>
    			<li>Fixed some station connection issues.</li>
    			<li>Fixed some geocoding issues.</li>
    			<li>Changed the default zoom</li>
    			<li>Search no longer relies on Geocoder.us</li>
    			<li>Fixed/Optimized multiple locations found dialog.</li>
    		</ul>
    	</dd>
        <dt class="date">2005 August 8</dt>
        <dd>Improved address finding.</dd>
        <dt class="date">2005 August 7</dt>
        <dd>Increased website performance by creating PHP sessions.</dd>
        <dt class="date">2005 August 4</dt>
        <dd>
            <ul>
                <li>Markers have been changed.</li>
                <li>Fixed bug where searching with intersection without state or city returned error.</li>
                <li>Added functionality where clicking on link on sidepanel brings up zoom window.</li>
            </ul>
        </dd>
        <dt class="date">2005 August 2</dt>
        <dd>Only relevant stops are now shown. Same with direction information.</dd>
        <dt class="date">2005 August 1</dt>
        <dd>Debut for nysubway.info! A few kinks that need to be worked out, but for the most part, it works as planned.</dd>
        <dt class="date">2005 July 26</dt>
        <dd>Display works; however, showing all stations is slow.</dd>
        <dt class="date">2005 July 23</dt>
        <dd>Idea conceived to superimpose the New York City Subway system onto a map. Also include a direction finder. Development begins.</dd>
    </dl>
    </div>
    </td></tr></table>
<div class="noselect" style="font-size:8pt;margin:20px 0 20px 0;text-align:center;">
&copy; 2005-2008 Michael Ho. All code is proprietary property.<br/>Hacking, discovering or reproducing the algorithm or anything involved thereof will be responded to with a lawsuit. Besides, this service is free. Why would you want to?
</div>
</body>
</html>

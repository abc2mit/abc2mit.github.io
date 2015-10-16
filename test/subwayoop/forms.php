<table>
    <tr>
        <td>
        <?php
            echo "<form id=\"formq\" action=\"$current_url\" onSubmit=\"return storeMapType(map)\" method=\"get\">\n";
            if ($type == 'location') {
                echo "<table id=\"maps_form\">\n";
            }
            else {                            
                echo "<table id=\"maps_form\" style=\"display: none;\">\n";
            }
        ?>
                <tr>
                    <td>
                    <?php
                      if (isset($loc)) {
                          echo "<input tabindex=\"0\" name=\"l\" id=\"l\" size=\"50\" value=\"$loc\" type=\"text\" />\n";
                      }
                      else {
                          echo "<input tabindex=\"0\" name=\"l\" id=\"l\" size=\"50\" value=\"\" type=\"text\" />\n";
                      }
                    ?>
                    </td>
                    <td rowspan="2" class="submit">
                      &nbsp;<!-- <input tabindex="5" id="submitq" value="Search" type="submit" /> -->
                      <input tabindex="5" id="submitq" type="image" value="Search" alt="Search" src="images/searchbutton.png" />
                    </td>
                </tr>
                <tr>
                    <td class="boxlabel">
                        Location Address&nbsp;&nbsp;<span class="example">e.g., <?php echo $location_example; ?></span>
                    </td>
                </tr>
            </table> <!--end maps_form-->
                <input type="hidden" name="type" value="location" />
                </form>
                <!-- this is the station to station form -->
                <?php
                    echo "<form id=\"forml\" action=\"$current_url\" onSubmit=\"return storeMapType(map)\" method=\"get\">\n";
                    if ($type == 's2s') {
                        echo "<table id=\"local_form\">\n";
                    }
                    else {
                        echo "<table id=\"local_form\" style=\"display: none;\">\n";
                    }
                ?>
                <tr>
                    <td style="white-space: nowrap;">
                        <SELECT NAME="start">;
                        <OPTION VALUE="">Pick a Starting Point...</OPTION>;
                        <?php
                            listStations($_GET['start'], $markers);
                        ?>
                        </SELECT>
                    </td>
                    <td>
                        <SELECT NAME="end">
                        <OPTION VALUE="">Pick a Ending Point...</OPTION>
                        <?php
                            listStations($_GET['end'], $markers);
                        ?>
                        </SELECT>
                    </td>
                    <td rowspan="2" class="submit">
                        &nbsp;<!-- <input tabindex="5" id="submitl" value="Search" type="submit"> -->
                      <input tabindex="5" id="submitl" type="image" value="Search" alt="Search" src="images/searchbutton.png" />
                    </td>
                </tr>
                <tr>
                    <td class="boxlabel">Start&nbsp;&nbsp;<span class="example">e.g., <?php echo $start_station_example; ?></span></td>
                    <td class="boxlabel">End&nbsp;&nbsp;<span class="example">e.g., <?php echo $end_station_example; ?></span></td>
                </tr>
            </table> <!-- end local_form -->
            <input type="hidden" name="type" value="s2s" />
        </form>
            <!-- this is the directions form -->
            <?php
                echo "<form id=\"formd\" action=\"$current_url\" onSubmit=\"return storeMapType(map)\" method=\"get\">\n";
                if ($type == 'location' || $type == 's2s') {
                    // if we're not at default or on 'dir', hide the form
                    echo "<table id=\"directions_form\" style=\"display: none;\">\n";
                }
                else {
                    echo "<table id=\"directions_form\">\n";
                }
            ?>
                <tr>
                    <td>
                    <?php
                    if (isset($saddr)) {
                        // we have a start address, so we want to display it
                        echo "<input tabindex=\"3\" name=\"saddr\" id=\"saddr\" size=\"28\" value=\"" . $saddr . "\" type=\"text\" />\n";
                    }
                    else {
                        // blank field
                        echo "<input tabindex=\"3\" name=\"saddr\" id=\"saddr\" size=\"28\" value=\"\" type=\"text\" />\n";
                    }
                    ?>
                    </td>
                    <td class="reverse">
                    <a href="" onclick="return _fd()">
                        <img src="images/ddirflip.gif" alt="Switch start and end address" title="Switch start and end address" height="14" width="10">
                    </a>
                    </td>
                    <td>
                    <?php
                    if (isset($daddr)) {
                        echo "<input tabindex=\"3\" name=\"daddr\" id=\"daddr\" size=\"28\" value=\"" . $daddr . "\" type=\"text\" />\n";
                    }
                    else {
                        echo "<input tabindex=\"3\" name=\"daddr\" id=\"daddr\" size=\"28\" value=\"\" type=\"text\" />\n";
                    }
                    ?>
                    </td>
                    <td rowspan="3" class="submit">
                        &nbsp;<!-- <input tabindex="5" id="submitd" value= "Search" type="submit"> -->      
                      <input tabindex="5" id="submitd" type="image" value="Search" alt="Search" src="images/searchbutton.png" />
                    </td>
                </tr>
                <tr><td class="boxlabel">Start address</td><td></td><td class="boxlabel">End address</td></tr>
                </table>
                <input type="hidden" name="type" value="dir" />
            </form> <!-- end directions_form -->
        </td>
        <!-- <td class="help"><div><a href="faq_maps.html">Help</a></div></td> -->
    </tr>
</table>
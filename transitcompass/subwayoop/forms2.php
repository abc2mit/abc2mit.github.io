<!-- this is the directions form -->
<?php
    #echo "<form name=\"inputform\" id=\"formd\" action=\"$current_url\" onSubmit=\"return storeMapType(map)\" method=\"get\">\n";
    echo "<form name=\"inputform\" id=\"formd\" action=\"$link\" onSubmit=\"return storeMapType(map)\" method=\"get\">\n";
    /*if ($type == 'location' || $type == 's2s') {
        // if we're not at default or on 'dir', hide the form
        echo "<table id=\"directions_form\" style=\"display: none;\">\n";
    }
    else {*/
        echo "<table id=\"directions_form\">\n";
    //}
?>
<tr>
    <td>
    <?php
    /*if (isset($sa)) {
        // we have a start address, so we want to display it
        echo "<input tabindex=\"3\" name=\"sa\" id=\"sa\" size=\"28\" value=\"" . $sa . "\" type=\"text\" />\n";
    }
    else {
        // blank field
        echo "<input tabindex=\"3\" name=\"sa\" id=\"sa\" size=\"28\" value=\"\" type=\"text\" />\n";
    }*/
    echo "<input tabindex=\"3\" name=\"sa\" id=\"sa\" size=\"28\" value=\"" . $sa . "\" type=\"text\" onclick=\"this.select()\" onkeydown=\"clearField('sid')\" />\n";
    
    ?>
    </td>
    <td class="reverse">
    <a href="<?php echo $link; ?>" onclick="return flip()">
        <img src="images/ddirflip.gif" alt="Switch start and end address" title="Switch start and end address" height="14" width="10">
    </a>
    </td>
    <td>
    <?php
    /*if (isset($da)) {
        echo "<input tabindex=\"3\" name=\"da\" id=\"da\" size=\"28\" value=\"" . $da . "\" type=\"text\" />\n";
    }
    else {
        echo "<input tabindex=\"3\" name=\"da\" id=\"da\" size=\"28\" value=\"\" type=\"text\" />\n";
    }*/
    echo "<input tabindex=\"3\" name=\"da\" id=\"da\" size=\"28\" value=\"" . $da . "\" type=\"text\" onclick=\"this.select()\" onkeydown=\"clearField('did')\" />\n";
    ?>
    </td>
    <td id="submit">
      <!--<input id="submitd" type="image" value="Search" alt="Search" src="images/searchbutton.png" />-->
      <input tabindex="5" type="submit" value="Search" id="submitb" class="btn" />
      <!--<input tabindex="5" type="button" value="Search" id="submitb" class="btn" onclick="javascript:submitForm()" />-->
    </td>
</tr>
<tr><td class="boxlabel">Start address</td><td></td><td class="boxlabel">End address</td></tr>
</table>
<?php
    if (isset($sid)) {
        echo "<input type=\"hidden\" name=\"sid\" id=\"sid\" value=\"" . $sid . "\" />\n";
    }
    else {
        echo "<input type=\"hidden\" name=\"sid\" id=\"sid\" />\n";
    }
    
    if (isset($did)) {
        echo "<input type=\"hidden\" name=\"did\" id=\"did\" value=\"" . $did . "\" />\n";
    }
    else {
        echo "<input type=\"hidden\" name=\"did\" id=\"did\" />\n";
    }
?>
</form> <!-- end directions_form -->
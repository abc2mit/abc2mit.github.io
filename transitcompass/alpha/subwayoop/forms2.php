<!-- this is the directions form -->
<?php
    #echo "<form name=\"inputform\" id=\"formd\" action=\"$current_url\" onSubmit=\"return storeMapType(map)\" method=\"get\">\n";
    echo "<form name=\"inputform\" id=\"formd\" action=\"$link\" onSubmit=\"return storeMapType(map)\" method=\"get\">\n";
    echo "<table id=\"directions_form\">\n";
?>
<tr>
    <td>
<?php
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
<tr><td class="boxlabel" style="text-align:right;">Starting Time</td><td></td><td>
<?php
    if (isset($time_string)) {
        echo "<input type=\"text\" name=\"time_string\" id=\"time_string\" size=\"28\" value=\"" . $time_string . "\" onclick=\"this.select()\" />\n";
    }
    else {
        echo "<input type=\"text\" name=\"time_string\" id=\"time_string\" size=\"28\" onclick=\"this.select()\" />\n";
    }
?>
</td></tr></table>
<?php
    //if (isset($sid)) {
    echo "<input type=\"hidden\" name=\"sid\" id=\"sid\" value=\"" . $sid . "\" />\n";
    //}
    //else {
    //    echo "<input type=\"hidden\" name=\"sid\" id=\"sid\" />\n";
    //}
    
    //if (isset($did)) {
    echo "<input type=\"hidden\" name=\"did\" id=\"did\" value=\"" . $did . "\" />\n";
    //}
    //else {
    //    echo "<input type=\"hidden\" name=\"did\" id=\"did\" />\n";
    //}
    #echo "<input type=\"text\" name=\"t\" id=\"t\" />\n";
    echo "<!--now is " . time() . "-->\n";
?>
<script type="text/javascript">
    var time = new Date();
    //document.inputform.t.value = time.getTime();
    var val = document.inputform.time_string.value;
    if (val == "") {
        document.inputform.time_string.value = time;
    }
</script>
</form> <!-- end directions_form -->
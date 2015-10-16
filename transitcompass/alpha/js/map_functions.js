/**
    This function creates a marker
*/
function createMarker (point, text, icon) {
  var marker = new GMarker(point, icon);

  // Show this marker's index in the info window when it is clicked
  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(text);
  });

  return marker;
}

    
// Creates a marker whose info window displays the given number
function createStation2(point, name, lines, icon) {
  var marker = new GMarker(point, icon);

  // Show this marker's index in the info window when it is clicked
  var html = "<b>" + name + "</b><br/>";
  for (var j = 0; j < lines.length; j++) {
  	// list each line by adding its gif to the html of the popup
    html = html + "<img width=\"20\" src=\"images/" + lines[j].toLowerCase() + ".gif\" /> ";
  }
  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(html);
  });

  return marker;
}

// @deprecated
function createMarker2(point, name, icon) {
  var marker = new GMarker(point, icon);

  // Show this marker's index in the info window when it is clicked
  var html = "<b>" + name + "</b>";
  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(html);
  });

  return marker;
}
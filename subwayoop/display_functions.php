<?php
    function createCityTableRow ($city, $marker_img, $class_name, $link) {
        $city_name = $city->name;
        #echo "createCityTableRow: " . $city_name . "<br/>";
        $city_row = new Row($city_name, $class_name);
        $marker_link = new MarkerLink($city->getLat(), $city->getLng(), new Image("images/$marker_img", 0));
        $city_row->addCell(new Cell(null, "stationMarkerLink", "text-align: center", $marker_link));
        $city_row->addCell(new Cell("cityName", $city_name));
        $city_row->addCell(new Cell("cityLink", new Link("http://" . $link, $link)));
        return $city_row;
    }

    /**
        @param $station<Station>
    */
    function createStationTableRow ($station, $marker_img, $class_name) {
        global $website;
        $station_marker = $station->marker;
        $station_name = $station_marker->name;
        $marker_link = new MarkerLink($station_marker->getLat(), $station_marker->getLng(), new Image("images/$marker_img", 0));
        #echo $marker_link->toString() . "<br/>";
        $station_row = new Row($station_name, $class_name);
        //$station_cell = new Cell(null, null, null, $marker_link);
        #echo $station_cell->toString();
        #$station_row->addCell($station_cell);
        $station_row->addCell(new Cell(null, "stationMarkerLink", "text-align: center", $marker_link));
        $station_row->addCell(new Cell("stationName", $station_name));
        $lines_cell = new Cell("stationLines");
        $lines = $station_marker->getLines();
        foreach ($lines as $line) {
            $line_img = $line->img;
            $line_url = $website . $line->url;
            $link = new Link("javascript:void(0);", new Image(null, null, "images/$line_img", $line->name, null, 20), "window.open('$line_url');");
            //echo $link->toString();
            $lines_cell->addData($link);
        }
        $station_row->addCell($lines_cell);
        #echo "<!--" . $station_row->toString() . "-->";
        return $station_row;
    }
?>
<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #
    #   display_classes.php
    #   This file contains classes that are used to aid display of content.
    #
    
class Div
{
    var $id;
    var $class_string;
    var $data;
    var $style;

    function Div ($param1 = null, $param2 = null, $param3 = null, $param4 = null) {
        $num_args = func_num_args();
        $arg_list = func_get_args();
        $args = "";
        for ($i = 0; $i < $num_args; $i++) {
            if ($i != 0) {
                $args .= ", ";
            }
            $args .= "\$param" . ($i + 1);
        }
        eval("\$this->constructor" . $i . "(" . $args . ");");
    }
    
    function constructor2 ($id, $data) {
        $this->id = $id;
        $this->data = array();
        $this->data[] = $data;
    }
    
    function constructor3 ($id, $class, $data) {
        $this->id = $id;
        $this->class_string = $class;
        $this->data = array();
        $this->data[] = $data;
    }
    
    function constructor4 ($id, $class, $style, $data) {
        $this->id = $id;
        $this->class_string = $class;
        $this->style = $style;
        $this->data = array();
        $this->data[] = $data;
    }
    
    function addDataElement ($data) {
        $this->data[] = $data;
    }
    
    function toString () {
        $div = "<div";
        if ($this->id != null || $this->id != "") {
            $div .= " id=\"" . $this->id . "\"";
        }
        if ($this->class_string != null || $this->class_string != "") {
            $div .= " class=\"" . $this->class_string . "\"";
        }
        if ($this->style != null || $this->style != "") {
            $div .= " style=\"" . $this->style . "\"";
        }
        $div .= ">\n";
        foreach ($this->data as $d) {
            if (is_string($d)) {
                $div .= $d;
            }
            else {
                $div .= $d->toString();
            }
        }
        $div .= "</div>\n";
        return $div;
    }
}

/**
    Essentially a <table></table>.
*/
class Table
{
    var $class_string;
    var $id;
    var $style;
    var $rows;
    var $width;
    
    function Table ($param1 = null, $param2 = null, $param3 = null) {
        $num_args = func_num_args();
        $arg_list = func_get_args();
        $args = "";
        for ($i = 0; $i < $num_args; $i++) {
            if ($i != 0) {
                $args .= ", ";
            }
            $args .= "\$param" . ($i + 1);
        }
        eval("\$this->constructor" . $i . "(" . $args . ");");
    }
    
    function constructor1 ($id) {
        $this->id = $id;
    }
    
    function constructor2 ($id, $class) {
        $this->id = $id;
        $this->class_string = $class;
    }
    
    function constructor4 ($id, $class, $style, $width) {
        $this->id = $id;
        $this->class_string = $class;
        $this->style = $style;
        $this->width = $width;
        $rows = array();
    }
        
    function addRow ($row) {
        $this->rows[] = $row;
    }
    
    function toString () {
        $table = "<table";
        if ($this->id != null) {
            $table .= " id=\"" . $this->id . "\"";
        }
        if ($this->class_string != null) {
            $table .= " class=\"" . $this->class_string . "\"";
        }
        if ($this->style != null) {
            $table .= " style=\"" . $this->style . "\"";
        }
        if ($this->width != null) {
            $table .= " width=\"" . $this->width . "\"";
        }
        $table .= ">\n";
        foreach ($this->rows as $row) {
            $table .= $row->toString();
        }
        $table .= "</table>\n";
        return $table;
    }
}

/**
    Essentially a <tr></tr>.
*/
class Row
{
    var $class_string;
    var $id;
    var $style;
    var $cells;
    
    function Row ($param1 = null, $param2 = null, $param3 = null) {
        $num_args = func_num_args();
        $arg_list = func_get_args();
        $args = "";
        for ($i = 0; $i < $num_args; $i++) {
            if ($i != 0) {
                $args .= ", ";
            }
            $args .= "\$param" . ($i + 1);
        }
        eval("\$this->constructor" . $i . "(" . $args . ");");
    }
    
    function constructor0 () {
        $this->cells = array();
    }
    
    function constructor1 ($id) {
        $this->id = $id;
        $this->cells = array();
    }
    
    function constructor2 ($id, $class) {
        $this->id = $id;
        $this->class_string = $class;
        $this->cells = array();
    }
    
    function constructor3 ($id, $class, $style) {
        $this->class_string = $class;
        $this->id = $id;
        $this->style = $style;
        $this->cells = array();
    }
    
    function addCell ($cell) {
        $this->cells[] = $cell;
    }
    
    function toString () {
        $row = "<tr";
        if ($this->id != null) {
            $row .= " id=\"" . $this->id . "\"";
        }
        if ($this->class_string != null) {
            $row .= " class=\"" . $this->class_string . "\"";
        }
        if ($this->style != null) {
            $row .= " style=\"" . $this->style . "\"";
        }
        $row .= ">\n";
        foreach ($this->cells as $cell) {
            $row .= $cell->toString();
        }
        $row .= "</tr>\n";
        return $row;
    }
}

/**
    Essentially a <td></td>.
*/
class Cell
{
    var $class_string;
    var $id;
    var $style;
    var $data;
    
    function Cell ($param1 = null, $param2 = null, $param3 = null, $param4 = null) {
        $num_args = func_num_args();
        $arg_list = func_get_args();
        $args = "";
        for ($i = 0; $i < $num_args; $i++) {
            if ($i != 0) {
                $args .= ", ";
            }
            $args .= "\$param" . ($i + 1);
        }
        eval("\$this->constructor" . $i . "(" . $args . ");");
    }
    
    function constructor1 ($class) {
        $this->class_string = $class;
        $this->data = array();
    }
    
    function constructor2 ($class, $data) {
        $this->class_string = $class;
        $this->data = array();
        $this->data[] = $data;
    }
    
    function constructor4 ($id, $class, $style, $data) {
        $this->class_string = $class;
        $this->id = $id;
        $this->style = $style;
        $this->data = array();
        $this->data[] = $data;
    }
    
    function addData ($data) {
        $this->data[] = $data;
    }
    
    function toString () {
        $cell = "<td";
        if ($this->id != null) {
            $cell .= " id=\"" . $this->id . "\"";
        }
        if ($this->class_string != null) {
            $cell .= " class=\"" . $this->class_string . "\"";
        }
        if ($this->style != null) {
            $cell .= " style=\"" . $this->style . "\"";
        }
        $cell .= ">";
        foreach ($this->data as $d) {
            if ($d != null) {
                if (!is_string($d)) {
                    $cell .= $d->toString();
                }
                else {
                    $cell .= $d;
                }
            }
        }
        $cell .= "</td>\n";
        return $cell;
    }
}

class Link
{
    var $href;
    var $data;
    var $onclick;
    
    function Link ($param1 = null, $param2 = null, $param3 = null) {
        $num_args = func_num_args();
        $arg_list = func_get_args();
        $args = "";
        for ($i = 0; $i < $num_args; $i++) {
            if ($i != 0) {
                $args .= ", ";
            }
            $args .= "\$param" . ($i + 1);
        }
        eval("\$this->constructor" . $i . "(" . $args . ");");
    }
    
    function constructor2 ($url, $data) {
        $this->href = $url;
        $this->data = $data;
    }
    
    function constructor3 ($url, $data, $onclick) {
        $this->href = $url;
        $this->data = $data;
        $this->onclick = $onclick;
    }
    
    function toString() {
        $link = "<a href=\"" . $this->href . "\"";
        if ($this->onclick != null) {
            $link .= " onclick=\"" . $this->onclick . "\"";
        }
        $link .= ">";
        if (is_object($this->data)) {
            $link .= $this->data->toString();
        }
        else {
            $link .= $this->data;
        }
        $link .= "</a>";
        return $link;
    }
}

class Image
{
    var $class_string;
    var $id;
    var $src;
    var $alt;
    var $height;
    var $width;
    var $border;
    
    function Image ($param1 = null, $param2 = null, $param3 = null, $param4 = null, $param5 = null, $param6 = null) {
        $num_args = func_num_args();
        $arg_list = func_get_args();
        $args = "";
        for ($i = 0; $i < $num_args; $i++) {
            if ($i != 0) {
                $args .= ", ";
            }
            $args .= "\$param" . ($i + 1);
        }
        eval("\$this->constructor" . $i . "(" . $args . ");");
    }
    
    function constructor2 ($src, $border) {
        $this->src = $src;
        $this->border = $border;
    }
    
    function constructor5 ($id, $src, $alt, $height, $width) {
        $this->id = $id;
        $this->src = $src;
        $this->alt = $alt;
        $this->height = $height;
        $this->width = $width;
    }
    
    function constructor6 ($id, $class, $src, $alt, $height, $width) {
        $this->class_string = $class;
        $this->id = $id;
        $this->src = $src;
        $this->alt = $alt;
        $this->height = $height;
        $this->width = $width;
    }
    
    function toString () {
        $image = "<img";
        if ($this->id != null || $this->id != "") {
            $image .= " id=\"" . $this->id . "\"";
        }
        if ($this->class_string != null || $this->class_string != "") {
            $image .= " class_string=\"" . $this->class_string . "\"";
        }
        if ($this->src != null || $this->src != "") {
            $image .= " src=\"" . $this->src . "\"";
        }
        if ($this->alt != null || $this->alt != "") {
            $image .= " alt=\"" . $this->alt . "\"";
        }
        if ($this->height != null || $this->height != "") {
            $image .= " height=\"" . $this->height . "\"";
        }
        if ($this->width != null || $this->width != "") {
            $image .= " width=\"" . $this->width . "\"";
        }
        if ($this->border != null || $this->border != "") {
            $image .= " border=\"" . $this->border . "\"";
        }
        $image .= "/>";
        return $image;
    }
}

class MarkerLink extends Link
{
    function MarkerLink ($lat, $lng, $data) {
        $this->href = "javascript:focusOn(new GLatLng($lat,$lng), map.getZoom());";
        $this->data = $data;
    }
}
?>
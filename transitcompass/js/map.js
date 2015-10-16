var _u = navigator.userAgent.toLowerCase();

function _ua(t) {
    return _u.indexOf(t) != -1;
}

function _fd() {
    var se = _el('saddr');
    var ee = _el('daddr');
    var s = se.value;
    se.value = ee.value;
    ee.value = s;
    return false;
}

function flip() {
    var se = _el('sa');
    var ee = _el('da');
    var s = se.value;
    se.value = ee.value;
    ee.value = s;
    
    var sd = _el('sid');
    var ed = _el('did');
    var d = sd.value;
    sd.value = ed.value;
    ed.value = d;
    return false;
}

function _gd(type, value) {
    _form('directions', true);
    if (type == 'to') {
        var se = _el('saddr');
        se.value = "";
        var ee = _el('daddr');
        ee.value = value;
    }
    else {
        var se = _el('saddr');
        se.value = value;
        var ee = _el('daddr');
        ee.value = "";
    }
}

function _compat() {
    return ((_ua('opera') && (_ua('opera 7.5') || _ua('opera/7.5') || _ua('opera 8') || _ua('opera/8'))) || (_ua('safari') && _uan('safari/') >= 125) || (_ua('msie') && !_ua('msie 4') && !_ua('msie 5.0') && !_ua('msie 5.1') && !_ua('msie 3') && !_ua('powerpc')) || (document.getElementById && window.XSLTProcessor && window.XMLHttpRequest && !_ua('netscape6') && !_ua('netscape/7.0')));
}

_fc = false;
_c = _fc || _compat();
//_c = true;

function _el(i) {
    return document.getElementById(i);
}

var _forms = ['maps','local','directions'];
var _defaults = {'maps': 'q','local': 'start','directions': 'saddr'};

function _form(name, focus) {
    if (!_c) return true;
    for (var i = 0; i < _forms.length; i++) {
        var n = _forms[i];
        var t = _el(n);
        var f = _el(n + '_form');
        if (t) {
            t.className = (n == name) ? 'selected' : null;
        }
        if (f) {
            f.style.display = (n == name) ? '' : 'none';
        }
    }
    //if (focus) {
    //    _el(_defaults[name]).focus();
    //}
    return false;
}

/*function clearField(field, id_field) {
    field.select();
    var el = _el(id_field);
    if (el.value != "") {
        el.value = "";
    }
}*/

function clearField(id_field) {
    //alert('Popup!');
    var el = _el(id_field);
    if (el.value != "") {
        el.value = "";
    }
}

/*
    This method is supposed to be used by a button in the search form so that
    a more customizable button can be used. However, this isn't really needed,
    since CSS takes care of the look of the submit button.
    Saving this for legacy purposes.

    @deprecated
*/
function submitForm() {
    //var submit_form = document.getElementById(form);
    //submit_form.submit();
    document.inputform.submit();
}

function focusOn(point, zoom) {
   map.setCenter(point, zoom);
   map.showMapBlowup(point);
}

function getWindowHeight() {
    if (window.self && self.innerHeight) {
        return self.innerHeight;
    }
    if (document.documentElement && document.documentElement.clientHeight) {
        return document.documentElement.clientHeight;
    }
    return 0;
}

function setMapSize(map) {
    mapResize();
    //var mapElement = document.getElementById("map");
    map.checkResize();
    //map.reconfigureAllImages();
}

function mapResize() {
    var mapCell = document.getElementById("map");
    var height = getWindowHeight();
    //mapCell.style.height = height*0.8 + 'px';
    mapCell.style.height = height-100 + 'px';
}
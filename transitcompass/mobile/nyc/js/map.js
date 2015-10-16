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

function focusOn(point, zoom) {
   map.setCenter(point, zoom);
   map.showMapBlowup(point);
}

function fixLocationString() {
    document.formq.l = escape(document.formq.l);
    document.write("fixLocationString");
    document.write(document.formq.l + "<br/>");
    return true;
}
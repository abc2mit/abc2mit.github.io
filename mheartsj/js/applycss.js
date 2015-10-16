/* determine which css to apply */
var cssDir = "css/";
var cssFile = "default.css";

if (BrowserDetect.browser.indexOf("Safari") != -1) {
    if (BrowserDetect.OS == "Mac") {
        cssFile = "safari.css";
    }
    else {
        cssFile = "safari-win.css";
    }
}
else if (BrowserDetect.browser.indexOf("Firefox") != -1) {
    if (BrowserDetect.OS == "Mac") {
        cssFile = "firefox.css";
    }
    else {
        cssFile = "firefox-win.css";
    }
}
else if (BrowserDetect.browser.indexOf("Explorer") != -1) {
    if (BrowserDetect.version == "6") {
        cssFile = "ie.css";
    }
}

document.write('<link rel="stylesheet" type="text/css" href="' + cssDir + cssFile + '" media="screen">');
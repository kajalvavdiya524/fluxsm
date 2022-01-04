var former = console.log;
window.consoleview = window.consoleview || [];
var loggingEnabled = true;
var ua = window.navigator.userAgent;
if(ua.indexOf('MSIE ') > 0 || ua.indexOf('Trident/') > 0 || ua.indexOf('Edge/') > 0) {
	loggingEnabled = false;
}

console.log = function(msg){
	if(loggingEnabled) {
		former(msg);
	}
    if(msg.constructor === Array) {
    	consoleview.push(JSON.stringify(msg));
    } else if(typeof msg == 'object') {
    	consoleview.push(JSON.stringify(msg));
    } else {
    	consoleview.push(msg);
    }
    
}

window.onerror = function(message, url, linenumber) {
    console.log("JavaScript error: " + message + " on line " + linenumber + " for " + url);
}

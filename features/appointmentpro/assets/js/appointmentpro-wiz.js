//<![CDATA[
if(window.attachEvent) {
    window.attachEvent('onload', loadWidget);
} else {
    if(window.onload) {
        var curronload = window.onload;
        var newonload = function(evt) {
            curronload(evt);
            loadWidget(evt);
        };
        window.onload = newonload;
    } else {
        window.onload = loadWidget;
    }
}
    
function loadWidget(){
    var cont = document.getElementById("wiz-appointment");
    var iframe = document.createElement('iframe');
    var atr = {};
    Array.prototype.slice.call(cont.attributes).forEach(function(item) {
      atr[item.name] = item.value;
    });
    var uri;
    if(typeof atr.uri != 'undefined') {
      uri = atr.uri;
    } else {
      alert("Please provide app URI");
      return false;
    }
  /*  uri += (typeof atr.bgcolor != 'undefined') ? '/'+atr.bgcolor : '/null';
    uri += (typeof atr.hdrcolor != 'undefined') ? '/'+atr.hdrcolor : '/null';
    uri += (typeof atr.btncolor != 'undefined') ? '/'+atr.btncolor : '/null';
    uri += (typeof atr.icncolor != 'undefined') ? '/'+atr.icncolor : '/null';*/
    iframe.src = encodeURI(uri);
    iframe.width = (typeof atr.width != 'undefined') ? atr.width : 300;
    iframe.height = (typeof atr.height != 'undefined') ? atr.height : 480;
    cont.appendChild(iframe);
}
//]]>
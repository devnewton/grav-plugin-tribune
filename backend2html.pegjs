{
var backend2html = {};
backend2html.tagStack = [];

backend2html.encode = function encode(e) {
    return e.replace(/[.]/g, function(e) {
        return "&#"+e.charCodeAt(0)+";";
    });
};

}

post
 = items:postItem*
 {
 	var result = items.join("");
    while(backend2html.tagStack.length > 0) {
    	result += "</" + backend2html.tagStack.pop() + ">";
    }
    return result;
 }
 
postItem
 = url
 / totoz
 / bigorno
 / norloge
 / openTag
 / closeTag
 / xmlSpecialChar
 / .
 
xmlSpecialChar
 = (lt / gt / amp / quot / apos)
 
lt
 = "<"
 { return "&lt;"; }
 
gt
 = ">"
 { return "&gt;"; }
 
amp
 = "&"
 { return "&amp;"; }

quot
 = '"'
 { return "&quot;"; }
 
apos
 = "'"
 { return "&apos;"; }
 
url
 = protocol:$((("http" "s"?) / "ftp") "://") url:$([^< \t\r\n])+
 { return [].concat('<a href="', protocol, encodeURI(url), '" target="_blank" rel="noreferrer">url</a>').join("");}

openTag
 = "<" tag:validFormatTag ">"
 {
 	backend2html.tagStack.push(tag);
 	return "<" + tag + ">";
 }

closeTag
 = "</" tag:validFormatTag ">"
 {
 	var result = "";
 	for(;;) {
      var poppedTag = backend2html.tagStack.pop();
      if(poppedTag == undefined) {
      	break;
      }
      if( poppedTag == tag) {
      	result += "</" + tag + ">";
        break;
      } else {
      	result += "</" + poppedTag + ">";
      }
    }
    return result;
 }

validFormatTag
 = (spoiler / "b" / "i" / "s" / "u" / "tt" / "code" )

spoiler
 = "spoiler"
 { return "mark"; }

invalidOpenTag
 = "<" tag:invalidTag ">"
 { return "&lt" + tag + "&gt"; }

invalidCloseTag
 = "</" tag:invalidTag ">"
 { return  "&lt/" + tag + "&gt"; }
 
invalidTag
 = [A-Za-z] (xmlSpecialChar / [^>])*
 
tagAttributes
 = attributes:(separator:" " attribute:tagAttribute { return attribute;})*
 {  var result = {};
 	for(var a in attributes) {
    	result[attributes[a].name] = attributes[a].value;
    }
 	return result;
 }

tagAttribute
 = name:$[a-z]+ value:("=\"" value:$[^"]* "\"" {return value;} )?
 {return { name: name, value: value}}

norloge
 = fullNorloge / normalNorloge / shortNorloge

fullNorloge
 = y: norlogeYear "-" m: norlogeMonth "-" d:norlogeDay [T# ] h:norlogeHours ":" mi:norlogeMinutes ":" s:norlogeSeconds
 {
 var time = h + ':' + mi  + ':' + s;
 return '<time title="' + y + "-" + m + "-" + d + "T" + time + '">' + time + '</time>';
 }
 
norlogeYear
 = digits: [0-9]+
 { return digits.join(""); }
 
norlogeMonth
 = first: [0-1] last: [0-9]
 { return first + last; }

norlogeDay
 = first: [0-3] last: [0-9]
 { return first + last; }

normalNorloge
 = h:norlogeHours ":" mi:norlogeMinutes ":" s:norlogeSeconds
 {
 var time = h + ':' + mi  + ':' + s;
 return '<time title="' + time + '">' + time + '</time>';
 }
 
shortNorloge
 = h:norlogeHours ":" mi:norlogeMinutes
 {
 var time = h + ':' + mi;
 return '<time title="' + time + '">' + time + '</time>';
 }

norlogeHours
 = first: [0-2] last: [0-9]
 { return first + last; }
 
norlogeMinutes
 = first: [0-5] last: [0-9]
 { return first + last; }
 
norlogeSeconds
 = first: [0-5] last: [0-9]
 { return first + last; }

bigorno
 = spaces:$(inputStart / whitespaces) s2:whitespaces? bigorno:$[a-zA-Z0-9-_]+ "<" &(whitespaces / [<[] / !.)
 { return spaces + '<cite>' + bigorno + '</cite>';}

totoz
  = first:"[:" totoz:[^\]]+ third:"]"
  { var totozId = totoz.join(""); 
  return '<figure>' + backend2html.encode(totozId) + '<img src="https://totoz.eu/img/' + encodeURI(totozId) + '"></figure>'; }
  
whitespaces
 = [ \t\r\n]

inputStart
 = & { return location().start.offset == 0; }

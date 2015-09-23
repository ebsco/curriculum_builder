
function preAddToWebsites(xmlhttp, listid, authorid) {
	var url = document.getElementById("ws-url").value;
	var title = document.getElementById("ws-title").value;

	if (url.substring(0,4) != "http") {
		alert("Please enter in a URL that begins with HTTP.");
	} else if ((url.length > 0) && (title.length > 0)) {
		url = encodeURIComponent(url);
		addToWebsites(xmlhttp, listid, authorid, url, title);
	} else if (url.length > 0) {
		alert("You must enter a link label");
	} else if (title.length > 0) {
		alert("You must enter a URL.");
	} else {
	}
}

function preAddToInstructions(xmlhttp, listid, authorid) {
	var insttext = document.getElementById("inst-text").value;
	insttext = insttext.replace(/(<([^>]+)>)/ig,"");
	
	if (insttext.length > 0) {
		insttext = encodeURIComponent(insttext);
		addToInstructions(xmlhttp, listid, authorid, insttext);
	} else {
		alert("Text cannot be empty.");
	}
}

function addToInstructions(xmlhttp, listid, authorid, insttext) {

var queryString = "?listid=" + listid + "&authorid=" + authorid + "&an=none&db=none&url=none&title=none&instruct=" + insttext + "&action=1&priority=1&type=3"; 
xmlhttp.open("GET","folder.php" + queryString,true);
xmlhttp.send();
		
document.getElementById("inst-text").value = "";

}

function addToWebsites(xmlhttp, listid, authorid, url, title) {

var queryString = "?listid=" + listid + "&authorid=" + authorid + "&an=none&db=none&url=" + url + "&text=none&title=" + title + "&action=1&priority=1&type=2"; 

xmlhttp.open("GET","folder.php" + queryString,true);
xmlhttp.send();
		
document.getElementById("ws-url").value = "";
document.getElementById("ws-title").value = "";

}

if (window.XMLHttpRequest)
{// code for IE7+, Firefox, Chrome, Opera, Safari
	xmlhttp=new XMLHttpRequest();
}
else
{// code for IE6, IE5
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
}

xmlhttp.onreadystatechange=function()
{
	if (xmlhttp.readyState==4 && xmlhttp.status==200)
	{
		document.location.reload();
		document.getElementById("hiddenXMLresponse").innerHTML=xmlhttp.responseText;
	}
}

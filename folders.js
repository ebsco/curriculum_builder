
function addToFolder(xmlhttp, listid, authorid, an, db, url, instruct, title, action, resultID, priority, type) {
url = encodeURIComponent(url);
var queryString = "?listid=" + listid + "&authorid=" + authorid + "&an=" + an + "&db=" + db + "&url=" + url + "&instruct=" + instruct + "&title=" + title + "&action=" + action + "&priority=" + priority + "&type=" + type;
xmlhttp.open("GET","folder.php" + queryString,true);
xmlhttp.send();
		
var panel1 = "#notinfolder" + resultID;
var panel2 = "#infolder" + resultID;

if (action != 1) {
	var focusbutton = "#addbutton" + resultID;
} else {
	var focusbutton = "#removebutton" + resultID;	
}

$(panel1).slideToggle(0);
$(panel2).slideToggle(0);

$(focusbutton).focus();

}

var xmlhttp;
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
		document.getElementById("hiddenXMLresponse").innerHTML=xmlhttp.responseText;
	}
}

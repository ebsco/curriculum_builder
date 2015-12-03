
function preAddToWebsites(xmlhttp, listid, authorid) {
	var url = document.getElementById("ws-url").value;
	var title = document.getElementById("ws-title").value;
	if (!(document.getElementById("folderselector-ws") === null)) {
		var targetfolder = document.getElementById("folderselector-ws").value;
	} else {
		targetfolder = "0";
	}
	
	if (url.substring(0,4) != "http") {
		alert("Please enter in a URL that begins with HTTP.");
	} else if ((url.length > 0) && (title.length > 0)) {
		url = encodeURIComponent(url);
		addToWebsites(xmlhttp, listid, authorid, url, title, targetfolder);
	} else if (url.length > 0) {
		alert("You must enter a link label");
	} else if (title.length > 0) {
		alert("You must enter a URL.");
	} else {
	}
}

function addreadingtofolder(myselectbox,readingid) {
	var queryString = "?folderid=" + myselectbox.options[myselectbox.selectedIndex].value + "&readingid=" + readingid; 
	xmlhttp.open("GET","add_to_folder.php" + queryString,true);
	xmlhttp.send();
}

function deletefolderobject(folderid,listid) {
	if (confirm('Are you sure?  This will delete the folder and all readings inside of it. This cannot be undone.')) {
		var queryString = "?action=deletefolder&listid=" + listid + "&folderid=" + folderid; 
		xmlhttp.open("GET","add_to_folder.php" + queryString,true);
		xmlhttp.send();
		return true;
	} else {
		return false;
	}
}

function preAddToInstructions(xmlhttp, listid, authorid) {

	var insttext = document.getElementById("inst-text").value;
	if (!(document.getElementById("folderselector-inst") === null)) {
		var targetfolder = document.getElementById("folderselector-inst").value;
	} else {
		targetfolder = "0";
	}
	
	insttext = insttext.replace(/(<([^>]+)>)/ig,"");
	
	if (insttext.length > 0) {
		addToInstructions(xmlhttp, listid, authorid, insttext, targetfolder);
	} else {
		alert("Text cannot be empty.");
	}
}

function preAddToFolder(xmlhttp, listid) {
	var insttext = document.getElementById("folder-text").value;
	
	insttext = insttext.replace(/(<([^>]+)>)/ig,"");
	
	if (insttext.length > 0) {
		insttext = encodeURIComponent(insttext);
		addNewFolderObj(xmlhttp, listid, insttext);
	} else {
		alert("Label cannot be empty.");
	}
}

function addNewFolderObj(xmlhttp,listid,insttext) {
	var queryString = "?action=newfolder&listid=" + listid + "&label=" + insttext; 
	xmlhttp.open("GET","add_to_folder.php" + queryString,true);
	xmlhttp.send();
			
	document.getElementById("inst-text").value = "";	
}

function addToInstructions(xmlhttp, listid, authorid, insttext, targetfolder) {

if(typeof targetfolder === 'undefined'){
	targetfolder = '';
} else if (targetfolder == "0") {
	targetfolder = '';
}

var queryString = "?listid=" + listid + "&authorid=" + authorid + "&an=none&db=none&url=none&title=none&instruct=" + insttext + "&action=1&priority=1&type=3&folder="+targetfolder; 
xmlhttp.open("GET","folder.php" + queryString,true);
xmlhttp.send();
		
document.getElementById("inst-text").value = "";

}

function addToWebsites(xmlhttp, listid, authorid, url, title, targetfolder) {

if(typeof targetfolder === 'undefined'){
	targetfolder = '';
} else if (targetfolder == "0") {
	targetfolder = '';
}

var queryString = "?listid=" + listid + "&authorid=" + authorid + "&an=none&db=none&url=" + url + "&text=none&title=" + title + "&action=1&priority=1&type=2&folder="+targetfolder; 

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

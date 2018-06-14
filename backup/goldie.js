
function sendcommand(command, item, extra) {
	var res = encodeURIComponent(item);
	var s = 'goldserver.php?a=' + command + '&b=' + res + '&c=' + extra;
	$.get(s)
	    .done(function(data) {
	        console.log(data);
	    })
	    .fail(function(data) {
	        console.log('Error: ' + data);
	    });

}

function fillmasters() {
	var masters = document.getElementsByClassName("mastername");
	var teamname = document.getElementById("teamselector").value;
	var yearvalue = document.getElementById("yearselector").value;
	var monthvalue = document.getElementById("monthselector").value;
	history.replaceState("","page 2",'?viewpage=masters&b=' + teamname + '&c=' + yearvalue + '&d=' + monthvalue);
	for(var i = 0; i < masters.length; i++) {
		getsomething('masters', teamname, yearvalue, i, monthvalue);
	}
}

function getsomething(getwhat, paraone, paratwo, parathree, parafour) {
	var masters = document.getElementsByClassName("mastername");
	var s = 'goldserver.php?a=' + getwhat + '&b=' + paraone + '&c=' + paratwo + '&d=' + parathree + '&e=' + parafour;
	$.get(s)
	    .done(function(data) {
	        masters.item(parathree).innerHTML = data;
	    })
	    .fail(function(data) {
	        console.log('Error: ' + data);
	    });
}

function getfullname() {
	//sendcommand('getfullname',q('settings_ntid'));
	var s = 'goldserver.php?a=getfullname&b=' + q('settings_ntid');
	$.get(s)
	    .done(function(data) {
	        document.getElementById('settings_fullname').value = data;
	    })
	    .fail(function(data) {
	        console.log('Error: ' + data);
	    });
}

function q(o){return document.getElementById(o).value;}

function drawmicrograph(metric, team, startmonth, endmonth) {

}

function buildmetricvariable(sqltable) {
	var v,f;
	v=q('varname');
	f="SELECT "+q('functions')+'('+q('metric_column')+')';
	f+=' AS '+q('varname')+' FROM ' + sqltable;
	f+=" WHERE "+q('ifcol')+" "+q('operator')+" '"+q('filter')+"'";
	sendcommand("savemetricvar", v,f);
	document.getElementById("varsaver").value = 'Saved';
}

function updatetrigger(o,metric) {
	sendcommand("updatetrigger",o.value,metric);
}

function closecell(o){
	document.getElementById("cell"+o).style.display="none";
	sendcommand("removemetric",o);
}
function savecalc() {
	var v,x;
	v=q('calculation');
	x=q('d');
	sendcommand("savemetriccalculation",v,x);
	document.getElementById("saver").value = 'Saved';
}
function s(view,extra,t) {
	sendcommand('saveview',view);
	var e = '/gold.locked/goldie.php?viewpage=' + view;
	if (extra != '') { e = e + '&' + extra; }
	history.replaceState("","page 2",e);
	window.location.url = e;
	window.location.reload(false);;
}

function showhide(o) {
	var obj = document.getElementById(o);
	console.log(obj.style.display);
	if(obj.style.display == 'block') {
		hovershow = false;
		obj.style.display = 'none';
	}
	else {
		obj.style.display = 'block';
		hovershow = true;
	}
}

function checkkey() {
	// q
	if (event.keyCode==81) {
		var elements = document.getElementsByClassName('team-box');
		for (var i in elements) {
				if (elements[i].oldclass) {
					elements[i].className = elements[i].oldclass;
					elements[i].oldclass = '';
				}
				else {
					elements[i].oldclass = elements[i].className;
					elements[i].className += ' floater';
				}
		}
	}
}

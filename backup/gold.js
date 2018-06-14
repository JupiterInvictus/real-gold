var showExtra = false;
var seatingtool = 'desk';
var seatingperson;
var seatingteam;
var people;

function toggleShowExtra() {
	var elems = document.getElementsByClassName("xtoggle");
	if (!showExtra) {
		// Toggle everything off
		for (var i = 0, el; el = elems[i]; i++) {
			el.style.display = 'inline';
		}
		showExtra = true;
	}
	else {
		// Toggle everything on.
		for (var i = 0, el; el = elems[i]; i++) {
			el.style.display = 'none';
		}
		showExtra = false;
	}
}
function getAjax(url, success) {
    var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
    xhr.open('GET', url);
    xhr.onreadystatechange = function() {
        if (xhr.readyState>3 && xhr.status==200) success(xhr.responseText);
    };
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send();
    return xhr;
}

document.addEventListener("click", function(evt) {
	if (evt.target.id == 'wall') {
		seatingtool = 'wall';
		unsetteam();
		evt.target.style.background = '#555';
		document.getElementById("desk").style.background = '#222';
		document.getElementById("door").style.background = '#222';
		document.getElementById("assignperson").style.background = '#222';
		history.pushState("", "", "?a=seating&tool=wall");
	}
	else if (evt.target.id == 'unassign') {
		seatingtool = 'unassign';
		evt.target.style.background = '#555';
		document.getElementById("wall").style.background = '#222';
		document.getElementById("door").style.background = '#222';
		document.getElementById("assignperson").style.background = '#222';
		document.getElementById("desk").style.background = '#222';
		unsetteam();
	}
	else if (evt.target.id == 'desk') {
		seatingtool = 'desk';
		evt.target.style.background = '#555';
		document.getElementById("wall").style.background = '#222';
		document.getElementById("door").style.background = '#222';
		document.getElementById("assignperson").style.background = '#222';
		history.pushState("", "", "?a=seating&tool=desk");
		unsetteam();
	}
	else if (evt.target.id == 'issues') {
		seatingtool = 'issues';
		evt.target.style.background = '#555';
		document.getElementById("desk").style.background = '#222';
		document.getElementById("wall").style.background = '#222';
		document.getElementById("door").style.background = '#222';
		document.getElementById("assignperson").style.background = '#222';
		history.pushState("", "", "?a=seating&tool=issues");
		unsetteam();

	}
	else if (evt.target.id == 'door') {
		seatingtool = 'door';
		evt.target.style.background = '#555';
		document.getElementById("wall").style.background = '#222';
		document.getElementById("desk").style.background = '#222';
		document.getElementById("assignperson").style.background = '#222';
		history.pushState("", "", "?a=seating&tool=door");
		unsetteam();
	}
	else if (evt.target.id == 'assignperson') {
		if (seatingteam > 0) {
			seatingtool = 'person';
			seatingperson = evt.target.value;
			evt.target.style.background = '#555';
			document.getElementById("wall").style.background = '#222';
			document.getElementById("desk").style.background = '#222';
			document.getElementById("door").style.background = '#222';
			history.pushState("", "", "?a=seating&tool=assignperson");
			if (evt.target.value) {
				history.pushState("", "", "?a=seating&tool=assignperson&assignperson=" + evt.target.value);
			}
		//unsetteam();
		}
	}
	else if (evt.target.id == 'assignteam') {
		seatingperson = document.getElementById('assignperson');
		seatingtool = 'team';
		evt.target.style.background = '#555';
		document.getElementById("wall").style.background = '#222';
		document.getElementById("desk").style.background = '#222';
		document.getElementById("door").style.background = '#222';
		document.getElementById("assignperson").style.background = '#222';
		history.pushState("", "", "?a=seating&tool=assignteam");
		if (seatingteam != evt.target.value) {
			if (evt.target.value > -1) {
				seatingteam = evt.target.value;
				history.pushState("", "", "?a=seating&tool=assignteam&assignteam=" + seatingteam);
				for (var i = seatingperson.options.length - 1; i >= 0; i--) {
					seatingperson.remove(i);
				}
				seatingperson.focus();
			}
			else {
				seatingteam = evt.target.value;
				for (var i = seatingperson.options.length - 1; i >= 0; i--) {
					seatingperson.remove(i);
				}

			}
			var r = getAjax("goldserver.php?a=getteam&b=" + evt.target.value, function(a) {
				people = a.substr(0,a.length-1).split(",");
				var el = document.createElement("option");
				el.textContent = "--"
				el.value = '--';
				seatingperson.appendChild(el);
				for (var i = 0; i < people.length; i++) {
					var el = document.createElement("option");
					el.textContent = people[i];
					el.value = people[i];
					seatingperson.appendChild(el);
				}
			});
		}
	}
else if (evt.target.id.substring(0,4) == 'seat') {
		var c = evt.target.className;
		var n, r = -2, t = -2;
		if (seatingtool == 'desk') {
			if (c.substr(0,8) == "seatable") {
				n = "seatedbottom"; r=0;
			}
			else if (c.substr(0,12) == "seatedbottom") { n = "seatedleft" + c.substr(12); r=1; }
			else if (c.substr(0,10) == "seatedleft") { n = "seatedtop" + c.substr(10); r=2; }
			else if (c.substr(0,9) == "seatedtop") {	n = "seatedright" + c.substr(9); r=3; }
			else if (c.substr(0,11) == "seatedright") { n = "seatable" + c.substr(11); r=-1; }
			else if ((c == "wall") || (c == "door")) { n = "seatable"; r = "-1"; }
		}
		else if (seatingtool == 'wall') { if (c == "wall") { n = "seatable"; r = "-1"; } else { n = "wall"; r=4; } }
		else if (seatingtool == 'door') { if (c == "door") { n = "seatable"; r = "-1"; } else { n = "door"; r=5; } }
		else if (seatingtool == 'issues') { if (c == "issues") { n = "seatable"; r = "-1"; } else { n = "issues"; r=6; } }
		else if (seatingtool == 'team') {
			if (seatingteam>0) {
				if (c.split(" ")[1] =='team' + seatingteam) { n = c.split(" ")[0]; }
				else { n = c + ' team' + seatingteam; t = seatingteam; }
			}
		}

		evt.target.className = n;
		var c = evt.target.id.split("-");
		var r = getAjax("goldserver.php?a=seat&xcoord=" + c[1] + "&ycoord=" + c[2] + "&rotation=" + r + "&teamid=" + t + "&ntid=" + seatingperson, function(a) {
			console.log(a);
		});
	}
});

function unsetteam() {
	seatingteam = 0;
	document.getElementById("assignteam").style.background = '#222';
	document.getElementById("assignteam").value = '--';

}

"use strict";
/*

	Black Gold Frontend
	jupiter@isolate.world
	2018

*/

var users = [];
var isAdminValue = false;
var d = document;
var htmlObjects = 0;
var blackGold = {
	loggedIn: false,
	userName: '',
	uid: ''
};
var logoutButton;
var listUsersButton;

var teams = [];

isAdmin();

function Person(first, last) {
	this.name = {
		first,
		last
	};
}

function User(first, last, userName, isActive) {
	Person.call(this, first, last);
	this.username = userName;
	this.isActive = isActive;
}

function Agent(first, last, userName, startDate, endDate, team) {
	Person.call(this, first, last);
	this.userName = userName;
	this.startDate = startDate;
	this.team = team;
}

Agent.prototype = Object.create(Person.prototype);

var mainBox = createElem('div', 'overflow-hidden px-4 py-4 text-orange-darkest');

update(createElem('p', 'text-center text-yellow-darkest text-5xl pb-4 font-hairline tracking-tight antialiased', 'mainLogo', mainBox),"black<span class='text-yellow-darker'>g<span class='text-gold'>&ofcir;</span>ld</span>");
update(createElem('p', 'text-center text-grey-darker text-xs pb-4 fixed pin-b pin-x'),"&copy; <a href='http://isolate.world/'>Isolate World</a> 2018. All rights reserved.");

// Check whether we are logged in, according to server.
checkLoggedIn();



/********************************
	support functions
*********************************/
// Update text in an element.
function update(element, text = '', formatting = ''){
	if (element != false) {
		if (formatting) {
			element.innerHTML = numeral(text).format(formatting);
		}
		else {
			element.innerHTML = text;
		}
	}
	return element;
}
function element(element) {
	if (element == ''){
		return false;
	}
	return d.getElementById(element);
}
function random(max, min=1 ){
	return Math.random() * (max - min) + min;
}
function randomfloor(max, min=1) {
	min=Math.ceil(min);
	max=Math.floor(max);
	return Math.floor(Math.random() * (max - min)) + min;
}
function createElem(elemType, className='', newId = 'z-' + htmlObjects, parent = d.body) {
	if (newId == '') { newId = 'z-' + htmlObjects; htmlObjects++; }
	var newElem = d.createElement(elemType);
	newElem.className = className;
	newElem.id = newId;
	parent.appendChild(newElem);
	return newElem;
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
function logIn() {
	blackGold.loggedIn = true;

	// Hide login window.
	if (element('logindiv')) { element('logindiv').remove(); }

	// Show log out button if logged in.
	logoutButton = createElem('button', 'fixed bg-grey-dark font-bold hover:bg-grey-darker text-yellow-lightest py-2 px-4 rounded logout-button');
	logoutButton.innerHTML = 'Log out ' + blackGold.userName + '';
	logoutButton.addEventListener("click", function(event) {
		logOut();
	});

	if (isAdmin()) {
		listUsersButton = createElem('button', 'bg-grey-dark font-bold hover:bg-grey-darker text-yellow-lightest py-2 px-4 rounded');
		listUsersButton.innerHTML = '&#128275; List users';
		listUsersButton.addEventListener("click", function(event) {
			listUsers();
		});
	}
}

function isAdmin() {
	getAjax("server/?action=isAdmin", function(text) {
		if (text == "0") { isAdminValue = false; }
		else { isAdminValue = true; }
	});
	return isAdminValue;
}

function listUsers() {
	if (!users.length) {
		getAjax("server/?action=getUsers", function(text) {
			users = JSON.parse(text);
			if (!element('usersList')) {
				createElem('div', 'w-full bg-black text-grey shadow-lg rounded px-8 pt-6 pb-8 mb-4 mt-4', 'usersList');
			}
			var usersText;
			usersText = "<h1>Users</h1>";
			usersText += "<button class='bg-grey-dark hover:bg-grey-darker text-yellow-lightest font-bold py-2 px-4 rounded my-2'>Add User</button>";
			usersText += "<table class='userx'>";
			usersText += "<thead><th>User name</th>";
			usersText += "<th>New?</th>";
			usersText += "<th>Admin?</th>";
			usersText += "<th>Manager?</th>";
			usersText += "<th>Show photos?</th>";
			usersText += "<th>Active?</th>";
			usersText += "<th>Preferred team</th>";
			usersText += "<th>Actions</th>";
			usersText += "</thead>";
			users.forEach(function(el) {
				usersText += "<tr class='user-"+el.user_name+"'>";
				usersText += "<td>" + el.user_name + "</td>";
				usersText += "<td>";
				if(el.user_new){ usersText += "<span class='ic text-green font-bold'>&#x2714;</span>"; } else { usersText += "<span class='ic text-red-dark'>&#x2715;</span>";}
				usersText +="</td>";
				usersText += "<td>";
				if(el.user_admin){ usersText += "<span class='ic text-green font-bold'>&#x2714;</span>"; } else { usersText += "<span class='ic text-red-dark'>&#x2715;</span>";}
				usersText +="</td>";
				usersText += "<td>";
				if(el.user_manager){ usersText += "<span class='ic text-green font-bold'>&#x2714;</span>"; } else { usersText += "<span class='ic text-red-dark'>&#x2715;</span>";}
				usersText +="</td>";
				usersText += "<td>";
				if(el.user_showphotos){ usersText += "<span class='ic text-green font-bold'>&#x2714;</span>"; } else { usersText += "<span class='ic text-red-dark'>&#x2715;</span>";}
				usersText +="</td>";
				usersText += "<td><span class='bg-grey-darkest hover:bg-grey-darker font-bold py-2 px-4 rounded cursor-pointer'";
				usersText += " onClick='toggleUser(\""+ el.user_name +"\", this);'";
				usersText += " title='Enable/disable user'>";
				if(el.user_active){ usersText += "<span class='ic text-green font-bold'>&#x2714;</span>"; } else { usersText += "<span class='ic text-red-dark'>&#x2715;</span>";}
				usersText +="</span></td>";
				usersText += "<td>";
				usersText += "<i class='em em-flag-" + el.team_shortname.toLowerCase() + "'></i> ";
				usersText += el.team_name;
				usersText += "</td>";
				usersText += "</tr>";
			});
			usersText += "</table>";
			update(element('usersList'), usersText);
		});
	}
}

function toggleUser(userName, obj) {
	console.log(userName, obj);
	// send command to backend to disable account.
	getAjax("server/?action=toggleUser&b=" + userName, function(text) {
		console.log(text);
		if (text) {
			obj.innerHTML = "<span class='ic text-green font-bold'>&#x2714;</span>";
		}
		else {
			obj.innerHTML = "<span class='ic text-red-dark'>&#x2715;</span>";
		}
	});



	/*// delete tr in userslist.
	Array.from(document.getElementsByClassName('user-' + userName)).forEach(function(el) {
		el.remove();
	});*/
}


function getTeamName(teamId) {
	getAjax("server/?action=getTeamName&b=" + teamId, function(text) {
		return text;
	});
}

function logOut() {
	getAjax("server/?action=logOut", function(text) {
		if (logoutButton) { logoutButton.remove(); }
	});
	checkLoggedIn();

}
function showLogin() {
	if (!blackGold.loggedIn) {
		// Display log in prompt.
		let loginDiv = createElem('div', 'w-full max-w-xs absolute', 'logindiv', mainBox);
		let loginForm = createElem('div', 'bg-black shadow-lg rounded px-8 pt-6 pb-8 mb-4', '', loginDiv);

		// Username part
		let loginFormDivUsername = createElem('div', 'mb-4', '', loginForm);
		let loginFormDivUsernameLabel = createElem('label', 'block text-grey text-sm font-bold mb-2', '', loginFormDivUsername);
			loginFormDivUsernameLabel.htmlFor = 'username';
			loginFormDivUsernameLabel.innerHTML = 'Username';
		let loginFormDivUsernameInput = createElem('input', 'shadow appearance-none bg-grey-darkest rounded w-full py-2 px-3 text-grey', 'username', loginFormDivUsername);
			loginFormDivUsernameInput.placeholder = 'Username';


		// Password part
		let loginFormDivPassword = createElem('div', 'mb-6', '', loginForm);
		let loginFormDivPasswordLabel = createElem('label', 'block text-grey text-sm font-bold mb-2', '', loginFormDivPassword);
			loginFormDivPasswordLabel.htmlFor = 'password';
			loginFormDivPasswordLabel.innerHTML = 'Password';
		let loginFormDivPasswordInput = createElem('input', 'shadow appearance-none bg-grey-darkest rounded w-full py-2 px-3 text-grey mb-3', 'password', loginFormDivPassword);
			loginFormDivPasswordInput.placeholder = '******************';
			loginFormDivPasswordInput.type = 'password';

		// Login button
		let loginFormDivSignInDiv = createElem('div', 'flex items-center justify-between', '', loginForm);
		let loginFormDivSignInDivButton = createElem('button', 'bg-grey-dark hover:bg-grey-darker text-yellow-lightest font-bold py-2 px-4 rounded', '', loginFormDivSignInDiv);
			loginFormDivSignInDivButton.innerHTML = 'Sign In';
			loginFormDivSignInDivButton.addEventListener("click", function(event) {
				// check if a username has been filled out, otherwise show red border and error.
				if (!loginFormDivUsernameInput.value) {
					loginFormDivUsernameInput.classList.add("border");
					loginFormDivUsernameInput.classList.add("border-red");
					loginFormDivUsernameInput.focus();
					return;
				}

				// check if a password has been filled out, otherwise show red border and error.
				if (!loginFormDivPasswordInput.value) {
					loginFormDivPasswordInput.classList.add("border");
					loginFormDivPasswordInput.classList.add("border-red");
					return;
				}

				var url = "server/?action=logIn&username=" + loginFormDivUsernameInput.value +
				"&password=" + loginFormDivPasswordInput.value;

				getAjax(url, function(text) {
					var ajaxReplyObject = JSON.parse(text);
					if (!ajaxReplyObject.loggedIn) {
						blackGold.loggedIn = false;
					}
					else {
						blackGold.loggedIn = true;
						blackGold.uid = ajaxReplyObject.uid;
						blackGold.userName = ajaxReplyObject.userName;
						logIn();
					}
				});
				event.preventDefault();
			});
		update(createElem('a', 'inline-block align-baseline font-bold text-sm text-blue hover:text-blue-light', '', loginFormDivSignInDiv), 'Forgot Password?');
	}
}
function checkLoggedIn() {
	// This is just an indicator. For every request, the session is validated against the backend.
	getAjax("server/?action=loggedIn", function(text) {
		var ajaxReplyObject = JSON.parse(text);
		if (!ajaxReplyObject.loggedIn) {
			blackGold.loggedIn = false;
			showLogin();
		}
		else {
			blackGold.loggedIn = true;
			blackGold.uid = ajaxReplyObject.uid;
			blackGold.userName = ajaxReplyObject.userName;
			logIn();
		}
	});


}

// Client ID and API key from the Developer Console
var CLIENT_ID = '1079673860784-13aa7jbs3nrmo7t3j5pqk75lu795elec.apps.googleusercontent.com';
var API_KEY = 'AIzaSyA7LQdawUT1QbBXoVJDogivuTydgBHBcJ8';

// Array of API discovery doc URLs for APIs used by the quickstart
var DISCOVERY_DOCS = ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest"];

// Authorization scopes required by the API; multiple scopes can be
// included, separated by spaces.
var SCOPES = "profile openid https://www.googleapis.com/auth/calendar";

var authorizeButton;
var signoutButton;

/**
*  On load, called to load the auth2 library and API client library.
*/
function handleClientLoad() {
  console.log("LOAD");
  authorizeButton = document.getElementById('signInButton');
  console.log(authorizeButton);
  signoutButton = document.getElementById('signOutButton');
  gapi.load('client:auth2', initClient);
}

/**
*  Initializes the API client library and sets up sign-in state
*  listeners.
*/
function initClient() {
  gapi.client.init({
    apiKey: API_KEY,
    clientId: CLIENT_ID,
    discoveryDocs: DISCOVERY_DOCS,
    scope: SCOPES
  }).then(function () {
    // Listen for sign-in state changes.
    gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

    // Handle the initial sign-in state.
    updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
    authorizeButton.onclick = handleAuthClick;
    signoutButton.onclick = handleSignoutClick;
  });
}

/**
*  Called when the signed in status changes, to update the UI
*  appropriately. After a sign-in, the API is called.
*/
function updateSigninStatus(isSignedIn) {
  if (isSignedIn) {
    authorizeButton.style.display = 'none';
    signoutButton.style.display = 'block';
    getProfileInfo();
    calendarButtonListener();
  } else {
    authorizeButton.style.display = 'block';
    signoutButton.style.display = 'none';
  }
}

function calendarButtonListener() {
  $('#calendarTest').click(function() {
    calendarTest(courseList);
  });
}

/**
*  Sign in the user upon button click.
*/
function handleAuthClick(event) {
  gapi.auth2.getAuthInstance().signIn();
}

function getProfileInfo() {
  var GoogleUser = gapi.auth2.getAuthInstance().currentUser.get();
  //var id_token = GoogleUser.getId();
  var profile = GoogleUser.getBasicProfile();
  var id_token = GoogleUser.getAuthResponse().id_token;
  //var id_token = profile.getId();

  var xhr = new XMLHttpRequest();
  var email = profile.getEmail();
  var name = profile.getName();
  xhr.open('POST', '/public/verifyIdToken');
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function() {
    signInSuccess(name);
  }
  xhr.send('idtoken=' + id_token + '&email=' + email + '&name=' + name);
}

/**
*  Sign out the user upon button click.
*/
function handleSignoutClick(event) {
  gapi.auth2.getAuthInstance().signOut();
  $.get('/public/logout');
}

function calendarTest(classArray) {
  for (i=0; i < classArray.length; i++){
    var startDateTime = classArray[i].startDate;
    startDateTime = startDateTime.concat("T");
    startDateTime= startDateTime.concat(classArray[i].startTime);


    var endDateTime = classArray[i].startDate;
    endDateTime = endDateTime.concat("T");
    endDateTime= endDateTime.concat(classArray[i].endTime);

    var rrule = "RRULE:FREQ=WEEKLY;BYDAY=";
    for (var i = 0; i < classArray[i].days.length; j++){
        if (var j <classARray[i].days.length-1){
          rrule = rrule.concat(classArrray[i].days[j]);
          rrule = rrule.concat(",");
        }
        else rrule.concat(classArray[i].days[j]);
    }

    rrule = rrule.concat(";UNTIL=");
    rrule=rrule.concat(classArray[i].endDate);
    rrule = rrule.concat("T");
    rrule = rrule.concat(classArray[i].endTime);
    rrule=rrule.concat(";");
      var event = {
        'summary': classArray[i].courseName,
        'location': classArray[i].location,
        'start': {
          'dateTime': startDateTime,
          'timeZone': 'America/Detroit'
        },
        'end': {
          'dateTime': endDateTime,
          'timeZone': 'America/Detroit'
        },
        'recurrence': [
          rrule
          //'RRULE:FREQ=WEEKLY;BYDAY=MO,WE,FR;UNTIL=2017-12-14T21:00:00;'
        ]
    };

      var request = gapi.client.calendar.events.insert({
        'calendarId': 'primary',
        'resource': event
      });

      request.execute(function(event) {
        if(event.status == "confirmed"){
          showGreenAlert("Your event has been added to your calendar! "+event.htmlLink);
        }else{
          showDangerAlert("Your event could not be added to calendar.");
          console.log(event);
        }
      });
    }
  }
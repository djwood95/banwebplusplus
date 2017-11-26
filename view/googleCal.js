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
    $('#signInButton').hide();
    $('.signedInOnly').show();
    getProfileInfo();
    navButtonListener();
  } else {
    $('#signInButton').show();
    $('.signedInOnly').hide();
  }
}

/**
 * Listener for nav buttons - triggered after user is signed in
 * and buttons are shown
 */
function navButtonListener() {
  $('#calendarTest').click(function() {
    addScheduleToGoogleCal(courseList);
  });
}

/**
*  Sign in the user upon button click.
*/
function handleAuthClick(event) {
  gapi.auth2.getAuthInstance().signIn();
}

/**
 * Verifies id token on backend, sets PHP session vars with account info
 */
function getProfileInfo() {
  var GoogleUser = gapi.auth2.getAuthInstance().currentUser.get();
  var profile = GoogleUser.getBasicProfile();
  var id_token = GoogleUser.getAuthResponse().id_token;

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

/**
 * Adds courses currently shown on calendar to Google Calendar
 */
function addScheduleToGoogleCal(classArray) {
  
  var errorCount = 0;
  for (var i=0; i < classArray.length; i++){

    var startDateTime = classArray[i].startDate;
    startDateTime = startDateTime.concat("T");
    startDateTime= startDateTime.concat(classArray[i].startTime);

    var endDateTime = classArray[i].startDate;
    endDateTime = endDateTime.concat("T");
    endDateTime= endDateTime.concat(classArray[i].endTime);

    var jsDate = new Date(startDateTime);
    var dayOfWeek = jsDate.getDay();
    
    var days = ["SU", "MO", "TU", "WE", "TH", "FR", "SA"];
    var dayOne = classArray[i].days[0];

    switch(dayOne){
      case "M":
        dayOne = 1;
        break;
      case "T":
        dayOne = 2;
        break;
      case "W":
        dayOne = 3;
        break;
      case "R":
        dayOne = 4;
        break;
      case "F":
        dayOne = 5;
        break;
    }

    var dayShift = dayOne - dayOfWeek;
    dayShift = Math.abs(dayShift);
    startDateTime = startDateTime.split('-');
    var layerStartDateTime = startDateTime[2].split('T');
    layerStartDateTime[0] = (parseInt(layerStartDateTime[0]) + parseInt(dayShift)).toString();
    startDateTime[2] = layerStartDateTime.join('T');
    startDateTime = startDateTime.join('-');
	
    endDateTime = endDateTime.split('-');
    var layerEndDateTime = endDateTime[2].split('T');
    layerEndDateTime[0] = (parseInt(layerEndDateTime[0]) + parseInt(dayShift)).toString();
    endDateTime[2] = layerEndDateTime.join('T');
    endDateTime = endDateTime.join('-');
    
    var rrule = "RRULE:FREQ=WEEKLY;BYDAY=";
    for (var j = 0; j < classArray[i].days.length; j++){
      switch(classArray[i].days[j]){
        case "M":
          rrule = rrule.concat("MO");
          break;
        case "T":
          rrule = rrule.concat("TU");
          break;
        case "W":
          rrule = rrule.concat("WE");
          break;
        case "R":
          rrule = rrule.concat("TH");
          break;
        case "F":
          rrule = rrule.concat("FR");
          break;
        default:
          break;
      }

      if (j <classArray[i].days.length-1){
        rrule = rrule.concat(",");
      }

    }

    var endDate = classArray[i].endDate;
    var endTime = classArray[i].endTime;
    rrule = rrule.concat(";UNTIL=");
    endDate = endDate.replace(/[-]+/g, '');
    rrule=rrule.concat(endDate);
    rrule = rrule.concat("T");
    endTime = endTime.replace(/[:]+/g, '');
    rrule = rrule.concat(endTime);
    rrule=rrule.concat("Z");
    
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
      ]
    };

    var request = gapi.client.calendar.events.insert({
      'calendarId': 'primary',
      'resource': event
    });

    request.execute(function(event) {
      if(event.status != "confirmed"){
        errorCount++;
      }
    });

  }

  if(errorCount == 0){
    showGreenAlert("Your schedule has been added to Google Calendar!");
  }else{
    showDangerAlert("Your schedule could not be added to calendar.");
    console.log(event);
  }
}

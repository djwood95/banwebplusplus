// Client ID and API key from the Developer Console
var CLIENT_ID = '1079673860784-13aa7jbs3nrmo7t3j5pqk75lu795elec.apps.googleusercontent.com';
var API_KEY = 'AIzaSyA7LQdawUT1QbBXoVJDogivuTydgBHBcJ8';

// Array of API discovery doc URLs for APIs used by the quickstart
var DISCOVERY_DOCS = ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest"];

// Authorization scopes required by the API; multiple scopes can be
// included, separated by spaces.
var SCOPES = "https://www.googleapis.com/auth/calendar";

var authorizeButton = document.getElementById('authorize-button');
var signoutButton = document.getElementById('signout-button');

/**
*  On load, called to load the auth2 library and API client library.
*/
function handleClientLoad() {
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
    listUpcomingEvents();
  } else {
    authorizeButton.style.display = 'block';
    signoutButton.style.display = 'none';
  }
}

/**
*  Sign in the user upon button click.
*/
function handleAuthClick(event) {
  var GoogleUser = gapi.auth2.getAuthInstance().signIn();
  var id_token = googleUser.getAuthResponse().id_token;
  var profile = googleUser.getBasicProfile();

  var xhr = new XMLHttpRequest();
  var email = profile.getEmail();
  var name = profile.getName();
  xhr.open('POST', '/public/verifyIdToken');
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function() {
    console.log('Signed in as: ' + xhr.responseText);
    signInSuccess();
  };
}

/**
*  Sign out the user upon button click.
*/
function handleSignoutClick(event) {
  gapi.auth2.getAuthInstance().signOut();
}

function addCalendar(CalendarId, className, startDate, endDate, days, startTime, endTime){
      var postURL = "https://www.googleapis.com/calendar/v3/calendars/";
      postURL = postURL.concat(CalendarId);
      postURL = postURL.concat("/events");
      accessURL = "https://www.googleapis.com/auth/calendar/";
      accessURL = accessURL.concat("111592634476270077742");
      var recurrenceRule = "RRULE:FREQ=WEEKLY;BYDAY=";
      recurrenceRule = recurrenceRule.concat(days);
      recurrenceRule = recurrenceRule.concat(";UNTIL=");
      recurrenceRule = recurrenceRule.concat(endDate);
      recurrenceRule = recurrenceRule.concat(";");
      var startDateTime = startDate;
      startDateTime = startDateTime.concat("T");
      startDateTime = startDateTime.concat(startTime);
      var endDateTime = startDate; //supposed to be start date
      endDateTime = endDateTime.concat("T");
      endDateTime = endDateTime.concat(endTime);
      var timeZone = "America/New_York";
       $.post(accessURL);
        $.post(postURL,
        {
          "summary": className,
          "start": {
            startDateTime,
            timeZone
          },
          "end": {
            endDateTime,
            timeZone
          },
          "recurrence": {
          recurrenceRule
          },
        },
        function(data,status){
            alert("Data: " + data + "\nStatus: " + status);
        });
}
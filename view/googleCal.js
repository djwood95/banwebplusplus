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
    calendarButtonListener();
  } else {
    $('#signInButton').show();
    $('.signedInOnly').hide();
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
  console.log(classArray);
  //difference in code
  for (var i=0; i < classArray.length; i++){

    //var makeDate = new Date(classArray[i].startDate);
    //makeDate.setDate(makeDate.getDate() + difference);


    //classArray[i].startDate = makeDate.toString();

    var startDateTime = classArray[i].startDate;
    startDateTime = startDateTime.concat("T");
    startDateTime= startDateTime.concat(classArray[i].startTime);
  //  console.log(startDateTime);


    var endDateTime = classArray[i].startDate;
    endDateTime = endDateTime.concat("T");
    endDateTime= endDateTime.concat(classArray[i].endTime);
    //console.log(endDateTime);


    var jsDate = new date(startDateTime);
    var dayOfWeek = jsDate.getDay();
    console.log("DAY IS: " + dayOfWeek);


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
	//'iCalUID': 'DELETEME',
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

    console.log(event);
    console.log(rrule);

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

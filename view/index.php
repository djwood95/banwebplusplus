<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="shortcut icon" href="DonaldTrump.ico"/>
    <title>MAKE SCHEDULING GREAT AGAIN</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <script src="googleCal.js"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <script src="https://apis.google.com/js/platform.js" async defer></script>

    <!-- Calendar CSS -->
    <link rel="stylesheet" href="cal.css" />
  </head>
  <body>




  <nav class="navbar navbar-expand-md navbar-dark bg-primary">
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-collapse collapse" id="collapsingNavbar">
      <!--<a class="navbar-nav mr-auto"></a>-->
      <a class="navbar-brand mx-auto justify-content-center" href="#" font-size="larger"><img src="logo.png" height="10%" width="10%"></a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><input id="addToCalendar" type="button" value="Button to test fucntion on my google calendar"
          onclick="addCalendar('mtu.edu_oqjo36vmkooiovc897nnutplro@group.calendar.google.com','test', '2017-11-13', '2017-12-13', 'MO,WE,FR', '12:00:00.000', '13:00:00.000');" /></li>
            <li class="nav-item">
              <a class="g-signin2" data-onsuccess="onSignIn" style='display:inline-block;'></a>
              <a class="nav-link" href="" data-target="#myModal" data-toggle="modal" onclick="signOut();" style='float:right;'>Sign Out</a>
              <script>
                function signOut() {
                  var auth2 = gapi.auth2.getAuthInstance();
                  auth2.signOut().then(function () {
                    $.get('/public/logout');
                  });
                }
            </script>
            </li>
            <!--
            <li class="nav-item">
                <a class="nav-link" href="" data-target="#myModal" data-toggle="modal" onclick="signOut();">Sign Out</a>
              <script>
                function signOut() {
                  var auth2 = gapi.auth2.getAuthInstance();
                  auth2.signOut().then(function () {
                    console.log('User signed out.');
                  });
                }
            </script>
            </li>-->
        </ul>
    </div>
  </nav>
  <!--
	<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
      <a class="navbar-brand" href="index.html">Banweb++<span class="sr-only">(current)</span></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="nav navbar-nav navbar-right">
          <li class="nav-item">
            
          </li>
          <li class="nav-item">
            
          </li>
        </ul>
      </div>
    </nav>
 -->
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <div class='container-fluid mt-5'>

      <div class="alert alert-danger" role="alert" style='display:none;'></div>

      <div class="row">
        <div class='col-sm'>
          <p>Search for courses to add:</p>
          <div class='form-row'>
            <div class='col-8'>
              <input type='text' id='searchBox' class='form-control' placeholder='Search by course number, course name, or instructor'/>
            </div>
            <div class='col'>
              <select class="custom-select mb-2 mr-sm-2 mb-sm-0" id="semester">
                <!-- values filled with javascript on page load -->
              </select>
            </div>
          </div>
          <ul id='searchResults' class='list-group' style='max-height:500px;overflow:auto;'></ul>
        </div>

        <div class='col-xl'>
          <table id='calendar'>
            <colgroup>
              <col span='1' class='timeLabelCol' />
            </colgroup>
            <tr class='labels'>
              <th class='timeLabel'></th>
              <th>Monday</th>
              <th>Tuesday</th>
              <th>Wednesday</th>
              <th>Thursday</th>
              <th>Friday</th>
            </tr>

              <?php
                $timeLabels = ['6am', '7am', '8am', '9am', '10am', '11am', '12pm', '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm'];
                $timeList = ['0600am', '0630am', '0700am', '0730am', '0800am', '0830am', '0900am', '0930am', '1000am', '1030am', 
                             '1100am', '1130am', '1200pm', '1230pm', '0100pm', '0130pm', '0200pm', '0230pm', '0300pm', '0330pm', 
                             '0400pm', '0430pm', '0500pm', '0530pm', '0600pm', '0630pm', '0700pm', '0730pm', '0800pm', '0830pm', 
                             '0900pm', '0930pm', '1000pm', '1030pm'];
                $days = ['M', 'T', 'W', 'R', 'F'];
                foreach($timeLabels as $i => $time) {
                  echo "<tr>";
                  echo "<td class='timeLabel'><div class='timeLabelText'>$time</div></td>";
                  $timeListIndex = $i*2;
                  $topTime = $timeList[$timeListIndex];
                  $bottomTime = $timeList[$timeListIndex + 1];
                  foreach($days as $day) {
                    echo "<td class='normal $day-$topTime'>";
                      echo "<div class='courseFiller top $day-$topTime'></div>";
                    echo "</td>";
                  }
                  echo "</tr>";
                }
                //echo "<tr><td class='timeLabel'><div class='timeLabelText'>11pm</div></td></tr>";
              ?>
            </tr>
          </table>
        </div>

        <div class='col-xs-6'>TEST</div>

      </div>
    </div>
	  
	</br></br></br></br></br></br></br></br>
  <meta name="google-signin-client_id" content="1079673860784-13aa7jbs3nrmo7t3j5pqk75lu795elec.apps.googleusercontent.com">

    <script> function onSignIn(googleUser) {

      var id_token = googleUser.getAuthResponse().id_token;
      console.log(id_token);
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
      xhr.send('idtoken=' + id_token + '&email=' + email + '&name=' + name);
    } </script>
	  
	  
    <div class='modal fade courseInfoBox' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" area-hidden="true">
      <div class='modal-dialog modal-lg'>
        <div class='modal-content p-2 text-center'></div>
      </div>
    </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

    <script src="search.js"></script>
    <script src="calendar.js"></script>

  </body>
</html>

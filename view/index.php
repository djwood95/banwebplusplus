<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <script src="https://apis.google.com/js/platform.js" async defer></script>

    <!-- Calendar CSS -->
    <link rel="stylesheet" href="cal.css" />
  </head>
  <body>

	<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
      <a class="navbar-brand" href="index.html">Banweb++<span class="sr-only">(current)</span></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse" align="right">
        <ul class="navbar-nav mr-auto" float="right">
          <li class="nav-item" align="right">
            <a class="nav-link" href="view/view.html"><div class="g-signin2" data-onsuccess="onSignIn"></div></a>
          </li>
          <li class="nav-item" align="right">
            <a class="nav-link" href="update/update.html"><a href="#" onclick="signOut();" class="nav-link">Sign out</a>
              <script>
                function signOut() {
                  var auth2 = gapi.auth2.getAuthInstance();
                  auth2.signOut().then(function () {
                    console.log('User signed out.');
                  });
                }
            </script>
            </a>
          </li>
        </ul>
      </div>
    </nav>

  </br></br>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <div class='container-fluid mt-5'>
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
                $times = ['6am', '7am', '8am', '9am', '10am', '11am', '12pm', '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm'];
                $days = ['M', 'T', 'W', 'R', 'F'];
                foreach($times as $time) {
                  echo "<tr>";
                  echo "<td class='timeLabel'>$time</td>";
                  foreach($days as $day) {
                    echo "<td>";
                      echo "<div class='courseFiller $day-$time'></div>";
                    echo "</td>";
                  }
                  echo "</tr>";
                }
              ?>
            </tr>
          </table>
        </div>

      </div>
    </div>
	  
	</br></br></br></br></br></br></br></br>
  <meta name="google-signin-client_id" content="1079673860784-13aa7jbs3nrmo7t3j5pqk75lu795elec.apps.googleusercontent.com">

    <script> function onSignIn(googleUser) {
  var profile = googleUser.getBasicProfile();
  console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
  console.log('Name: ' + profile.getName());
  console.log('Image URL: ' + profile.getImageUrl());
  console.log('Email: ' + profile.getEmail()); 
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

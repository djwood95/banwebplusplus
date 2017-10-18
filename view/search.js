$(document).ready(function(){

	$('#searchBox').change(function(){search();});

});

function search() {
	var query = $('#searchBox').val();
	console.log("Trying to search for " + query);
	$.get('/public/search/' + query, function(responseTxt, status) {
		var data = JSON.parse(responseTxt);
		displaySearchResults(data);
	});
}

function displaySearchResults(data) {
	var html = "<ul>";
	for(var i = 0; i < data.length; i++){
		html += "<li>" + data[i].CourseName + "</li>";
	}
	html += "</ul>";

	$("#searchResults").html(html);
}
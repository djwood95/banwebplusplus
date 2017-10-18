function search() {
	var query = $('#searchBox').val();
	alert(query);
	$('#test').load('http://localhost:8080/search/' + query, function(responseTxt) {
		alert('test');
	});
}
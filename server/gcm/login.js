function init_login() {
	$(".toggle_login_form").bind("click", function(event) {
		$(".register").toggleClass("none");
		$(".login").toggleClass("none");
	});
}

// Document Ready
$(document).ready(function() {
	init_login();
});

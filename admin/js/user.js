var userJS = {
		init: function() {

			$(document).on("click", "#loginBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var params = {};
				params.username = $("#inputEmail1").val();
				params.password = $("#inputPassword1").val();
				this.classList.add("disabled");
				$('#loginMessageContainer').html("");
            	$.ajax({
            		url: './api/v1/user/authenticate', 
            		type : "POST",
            		dataType : 'json',
            		data : JSON.stringify(params),
            		contentType: "application/json",
            		success : function(result) {
            			$("#loginBtn").removeClass("disabled");
            			Cookies.set('token', result.token, { expires: 1/12, secure: true });
            			Cookies.set('fluxur', result.ur, {expires: 1/12 ,secure: true });
            			window.location = "index.php";
            		},
            		error: function(xhr, resp, text) {
            			var message = "An error occurred while trying to log in";
            			if(xhr.hasOwnProperty("responseJSON") && xhr.responseJSON.hasOwnProperty("error")) {
            				message = xhr.responseJSON.error;
            			}
            			$('#loginMessageContainer').html("");
            			$('#loginMessageContainer').html('<div class="alert alert-danger" role="alert">'+message+'</div>');
            			$("#loginBtn").removeClass("disabled");
            		}
            	});
			});
			
			$(document).on("keyup", "#inputEmail1, #inputPassword1", function(event) {
			    if (event.keyCode === 13) {
			        $("#loginBtn").click();
			    }
			});
		},
		
		initModal: function() {

			$(document).on("click", "#loginBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var params = {};
				params.username = $("#inputEmail1").val();
				params.password = $("#inputPassword1").val();
				this.classList.add("disabled");
				$('#loginMessageContainer').html("");
            	$.ajax({
            		url: './api/v1/user/authenticate', 
            		type : "POST",
            		dataType : 'json',
            		data : JSON.stringify(params),
            		contentType: "application/json",
            		success : function(result) {
            			var userrole = Cookies.get("fluxur");
            			$("#loginBtn").removeClass("disabled");
            			Cookies.set('token', result.token, { expires: 1/12, secure: false });
            			Cookies.set('fluxur', result.ur, { expires: 1/12, secure: false });
            			
            			if(userrole != result.ur) {
                            location.reload();
                        }
                                
            			$("#loginModal").modal("hide");
                        $(window).trigger($.Event('reloggedin'));
            		},
            		error: function(xhr, resp, text) {
            			var message = "An error occurred while trying to log in";
            			if(xhr.hasOwnProperty("responseJSON") && xhr.responseJSON.hasOwnProperty("error")) {
            				message = xhr.responseJSON.error;
            			}
            			$('#loginMessageContainer').html("");
            			$('#loginMessageContainer').html('<div class="alert alert-danger" role="alert">'+message+'</div>');
            			$("#loginBtn").removeClass("disabled");
            		}
            	});
			});
			
			$(document).on("keyup", "#inputEmail1, #inputPassword1", function(event) {
			    if (event.keyCode === 13) {
			        $("#loginBtn").click();
			    }
			});
		}
};
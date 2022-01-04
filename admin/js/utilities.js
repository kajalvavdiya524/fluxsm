var utilitiesJS = {
	getQueryVariable: function(variable) {
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
			var pair = vars[i].split("=");
			if(pair[0] == variable){return pair[1];}
		}
		return false;
	},
	
	serializeObject: function(form,cls) {
		var retObj = {};
		$.each($("#" + form + " ." + cls), function() {
			if(this.value != "") {
				retObj[this.name] = this.value;
			}
		});	  
		return retObj;
	},
	
	sortResults: function(arr, prop, asc) {
		return arr.sort(function(a, b) {
			if (asc) {
				return (a[prop].trim().toUpperCase() > b[prop].trim().toUpperCase()) ? 1 : ((a[prop].trim().toUpperCase() < b[prop].trim().toUpperCase()) ? -1 : 0);
			} else {
				return (b[prop].trim().toUpperCase() > a[prop].trim().toUpperCase()) ? 1 : ((b[prop].trim().toUpperCase() < a[prop].trim().toUpperCase()) ? -1 : 0);
			}
		});
	},
	
	sessionCheck: function() {
		$.ajax('./api/v1/user/', {cache: false, type: "GET", headers:{"Authorization":"Bearer " + Cookies.get('token')}}).always(function (request, textStatus) {
			if(request.statusText == "Unauthorized") {
				window.location = "login.php"; 
			} else {
				$(document).ajaxError(function(event, request, settings) {
					if(request.statusText == "Unauthorized") {
						$("#loginModal").modal({backdrop: "static", keyboard: false, show: true});
						userJS.initModal();
					}
				});
				
				$(document).ajaxSuccess(function( event, request, settings, data) {
					if(data != null && data.hasOwnProperty("refreshToken")) {
						Cookies.set('token', data.refreshToken, { expires: 1/12, secure: true });
						$(window).trigger($.Event('reloggedin'));
					}
				});
				$("#userwelcome").html(request.email);
			}
		});
		
		$(document).on("click", "#logout", function(event) {
			$.ajax('./api/v1/user/authenticate', {cache: false, type: "DELETE", headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
				Cookies.remove('token');
				Cookies.remove('fluxur');
				window.location = "/";
			});
		});
	},
	
	setPatternFilter: function(id, pattern) {
		utilitiesJS.setInputFilter(document.getElementById(id), function(value) { return pattern.test(value); });
	},
	
	setInputFilter: function(textbox, inputFilter) {
		  ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
		    textbox.addEventListener(event, function() {
		      if (inputFilter(this.value)) {
		        this.oldValue = this.value;
		        this.oldSelectionStart = this.selectionStart;
		        this.oldSelectionEnd = this.selectionEnd;
		      } else if (this.hasOwnProperty("oldValue")) {
		        this.value = this.oldValue;
		        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
		      }
		    });
		  });
		},

	storeNavigation: function() {
		$.ajax('./api/v1/user/stores', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			var storeDropDown = "";
			$.each(rs.stores, function(i, s) {
				storeDropDown += '		<option value="'+s.id+'">'+s.identifier+'</option>';
			});
			$("#storeDropdown").html(storeDropDown);
			$(window).trigger($.Event('storelistloaded'));
		});
	}	
}
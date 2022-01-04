var customerListJS = {
		init: function() {
			$(document).on("change","#storeDropdown",function(event) {
				Cookies.set("storeSelected", $("#storeDropdown").val(), { expires: 1/12, secure: true });
				$(window).trigger($.Event('storelistloaded'));
			});
			
			$(window).on('storelistloaded', function (e) {
				var selectedStoreCookie = Cookies.get('storeSelected');
				if(selectedStoreCookie) {
					$("#storeDropdown").val(selectedStoreCookie);
				}
				$.ajax('./api/v1/customer/all/1?store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
					customerListJS.initDisplayCustomers(rs);
				});
			});
			
			$(document).on('click', '#customerSearchBtn', function(event) {
				var urlParams = "";
				if($('#searchFirstName').val() != "") {
					if(urlParams != "") { urlParams += "&"; }
					urlParams += "firstName=" + $('#searchFirstName').val();
				}
				if($('#searchLastName').val() != "") {
					if(urlParams != "") { urlParams += "&"; }
					urlParams += "lastName=" + $('#searchLastName').val();
				}
				if($('#searchPhone').val().replace(/\D/g,'') != "") {
					if(urlParams != "") { urlParams += "&"; }
					urlParams += "phone=" + $('#searchPhone').val().replace(/\D/g,'');
				}
				if($('#searchBusiness').val() != "") {
					if(urlParams != "") { urlParams += "&"; }
					urlParams += "businessname=" + $('#searchBusiness').val();
				}
				if(urlParams != "") {
					$.ajax('./api/v1/customer/all/1?store=' + $("#storeDropdown").val() + '&' + urlParams, {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
						customerListJS.initDisplayCustomers(rs);
					});
				} else if($("#searchInvoice").val() != "") {
					window.location = "customer.php?invoiceId="+$("#searchInvoice").val()+"&store="+$("#storeDropdown").val();
				}
				event.preventDefault();
			});
		},
		
		initDisplayCustomers: function(rs) {
			this.displayCustomers(rs);
			this.initPagination(rs.totalPages,rs.page);
		},
		
		displayCustomers: function(rs) {
			var customers = "";
			$.each(rs.rows, function(i, c) {
				if(!c.firstname) {c.firstname = "";}
				if(!c.lastname) {c.lastname = "";}
				if(!c.phone1) {c.phone1 = "";}
				if(!c.addressline1) {c.addressline1 = "";}
				if(!c.city) {c.city = "";}
				if(!c.state) {c.state = "";}
				if(!c.zip) {c.zip = "";}
				var name = c.firstname + " " + c.lastname;
				if(c.businessname) {
					name = "<strong>" + c.businessname + "</strong><br />" + name; 
				} else {
					name = "<strong>" + name + "</strong>";
				}
				 
				customers += '<tr><td><a href="customer.php?id='+c.id+'&store='+$("#storeDropdown").val()+'">'+name+'</a></td>';
				customers += '<td>'+c.phone1+'</td>';
				customers += '<td>'+c.addressline1+'</td>';
				customers += '<td>'+c.city+'</td>';
				customers += '<td>'+c.state+'</td>';
				customers += '<td>'+c.zip;
				customers += '</td></tr>';
			});
			$("#customerTableBody").html(customers);
		},
		
		initPagination: function(total,page) {
			$("#paginationControls").bootpag({
			    total: total,
			    page: page,
			    maxVisible: 5,
			    leaps: true,
			    firstLastUse: true,
			    first: '←',
			    last: '→',
			    wrapClass: 'pagination justify-content-center',
			    activeClass: 'active',
			    disabledClass: 'disabled',
			    nextClass: 'next',
			    prevClass: 'prev',
			    lastClass: 'last',
			    firstClass: 'first'
			}).on("page", function(event, num){
				var urlParams = "store="+$("#storeDropdown").val();
				if($('#searchFirstName').val() != "") {
					if(urlParams != "") { urlParams += "&"; }
					urlParams += "firstName=" + $('#searchFirstName').val();
				}
				if($('#searchLastName').val() != "") {
					if(urlParams != "") { urlParams += "&"; }
					urlParams += "lastName=" + $('#searchLastName').val();
				}
				if($('#searchPhone').val().replace(/\D/g,'') != "") {
					if(urlParams != "") { urlParams += "&"; }
					urlParams += "phone=" + $('#searchPhone').val().replace(/\D/g,'');
				}
				if($('#searchBusiness').val() != "") {
					if(urlParams != "") { urlParams += "&"; }
					urlParams += "businessname=" + $('#searchBusiness').val();
				}
				if(urlParams != "") {
					$.ajax('./api/v1/customer/all/' + num + '?' + urlParams, {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
						customerListJS.displayCustomers(rs);
					});
				} else {
					$.ajax('./api/v1/customer/all/'+num, {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
						customerListJS.displayCustomers(rs);
					});
				}
			});
			$("ul.bootpag li").addClass("page-item");
			$("li.page-item a").addClass("page-link");			
		}
};
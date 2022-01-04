var unsaved=false;
function getQueryVariables(variable) {	
	var query = window.location.search.substring(1);	
	var vars = query.split("&");	
	for (var i=0;i<vars.length;i++) {	
		var pair = vars[i].split("=");	
		if(pair[0] == variable){return pair[1];}	
	}	
	return false;	
}

function isPage(variable) {	
	var pages = window.location.pathname.substring(1);	
	var vars = pages.split("/");	
	for (var i=0;i<vars.length;i++) {	
		if(vars[i] == variable){return true;}	
	}	
	return false;		
}

(function($) {
  "use strict"; // Start of use strict
  
  var configObject;
  
  $(window).on('storelistloaded', function (e) {
	  	var selectedStoreCookie = Cookies.get('storeSelected');
		if(selectedStoreCookie) {
			$("#storeDropdown").val(selectedStoreCookie);
		}
		$.ajax('./api/v1/store/details?store='+$("#storeDropdown").val(), {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			initWorkOrderData(rs);
		});
  });

  $(document).on("change","#storeDropdown",function(event) {
	  Cookies.set("storeSelected", $("#storeDropdown").val(), { expires: 1/12, secure: true });
	  $(window).trigger($.Event('storelistloaded'));
	  $('#partDataTable').DataTable().ajax.url("./api/v1/inventory/partlist?format=datatable&store="+$("#storeDropdown").val()).load();
  });
  
  function EACTemplateOption() {
	  var options = {
		url: function(phrase) {
			return "api/v1/template/filter/" + phrase + "?store=" +$("#storeDropdown").val() + "&_=" + new Date().getTime();
		},
		getValue: "name",
		list: {
			onChooseEvent: function() {
				$("#templateListSelected").val($(".templateList").getSelectedItemData().id);
				$("#temaplateAddBtn").removeClass("disabled");
				$("#templateDeleteBtn").removeClass("disabled");
			}
		},
		adjustWidth: false,
		ajaxSettings: {
			headers:{"Authorization":"Bearer " + Cookies.get('token')}
		}
	  };
	  
	  return options;
  }

  $(".templateList").easyAutocomplete(EACTemplateOption());
  
  $(window).on('reloggedin', function (e) {
	  $(".templateList").easyAutocomplete(EACTemplateOption());
  });
  
  window.onbeforeunload = confirmExit;
  
  function confirmExit() {
      if (unsaved) {
          return "New information not saved. Do you wish to leave the page?";
      }
  }
  
  $(window).on("workorderdetailsdisplayed", function (e) {
	  unsaved=false;
  });
  
  function initWorkOrderData(rs) {
	 configObject = rs;
	 populateItemTypes(rs.itemtypes);
	 populateTaxRates(rs.rates);
	 populatePrintStoreDetails(rs.store);
	 populatePromisedTime();
	 populateStartTime();
	 populateTeamMembers(rs.team);
	 populateVendors(rs.vendors);
	 populateStartDateTime();
	 if (!$.fn.DataTable.isDataTable('#partDataTable')) {
	   populatePartDataTable();
	 }
	 $(window).trigger($.Event('workorderdatacomplete'));
	 unsaved=false;
  }
  
  function populatePartDataTable() {
  	  $.fn.dataTable.ext.errMode = 'none';
	  $('#partDataTable').DataTable({
		  "ajax": {
	    	  url: "./api/v1/inventory/partlist?format=datatable&store="+$("#storeDropdown").val(),
	    	  headers:{"Authorization":"Bearer " + Cookies.get('token')}
	      },
		  "columns": [
		      {
		    	  "data": null,
		    	  "render":function ( data, type, row, meta ) {
		    		  return '<input type="radio" class="stockItem" name="stock" value="'+data.id+'" data-vendor="'+data.vendor_id+'" data-partnumber="'+data.partnumber+'" data-description="'+data.description+'" data-cost="'+data.cost+'" data-retail="'+data.retail+'" />';
		    	  }
		      },
		      { "data": "manufacturer" },
		      { "data": "partnumber" },
		      { "data": "description" },
		      { "data": "cost" },
		      { "data": "retail" },
		      { "data": "stock" }
		  ]
	  });
  }

  function populateVendors(rs) {
  	  $("#vendorDropdown").empty().append('<option value="" selected>Vendor</option>');
  	  $("#hiddenPartRow .orderItemVendor").empty().append('<option value="" selected>Vendor</option>');
	  $.each(rs, function(i, m) {
		  $("#vendorDropdown").append('<option value="' + m.id + '">' + m.vendorname + '</option>');
		  $("#hiddenPartRow .orderItemVendor").append('<option value="' + m.id + '">' + m.vendorname + '</option>');
	  });
  }
  
  function populateTeamMembers(rs) {
  	  $("#teamMemberDropdown").html('');
	  $.each(rs, function(i, m) {
		  $("#teamMemberDropdown").append('<option value="' + m.id + '">' + m.name + '</option>');
	  });
  }
  
  function populatePromisedTime() {
	  var halfHours = ['00','30'];
	  for(var i = 8; i < 18; i++){
		  for(var j = 0; j < 2; j++){
			  var m = "AM";
			  var display = i;
			  if(display >= 12) {
				  if(display > 12) { display = display-12; }
				  m = "PM";
			  }
			  var hours = ('0' + i).slice(-2);
			  $('#promisedTime').append('<option value="' + ('0' + i).slice(-2) + ":" + halfHours[j] + '">' + ('0' + display).slice(-2) + ":" + halfHours[j] + ' ' + m + '</option>');
		  }
	  }
  }

  function populateStartTime() {
	  var halfHours = ['00','30'];
	  for(var i = 8; i < 18; i++){
		  for(var j = 0; j < 2; j++){
			  var m = "AM";
			  var display = i;
			  if(display >= 12) {
				  if(display > 12) { display = display-12; }
				  m = "PM";
			  }
			  var hours = ('0' + i).slice(-2);
			  $('#startTime').append('<option value="' + ('0' + i).slice(-2) + ":" + halfHours[j] + '">' + ('0' + display).slice(-2) + ":" + halfHours[j] + ' ' + m + '</option>');
		  }
	  }
  }

  function populateStartDateTime() {
  	var startdate = getQueryVariables("startdate"),
		starttime = getQueryVariables("starttime");
  	if (startdate) {
	 	$("#startDate").val(startdate);
	}
	if (starttime) {
	 	$("#startTime").val(starttime);
	}
  }
  
  function populatePrintStoreDetails(rs) {
	  $("#printStoreName").html(rs.name);
	  $("#printStoreAddress1").html(rs.address1);
	  
	  if(rs.address2 && rs.address2 != "") {
		  $("#printStoreAddress2").html(rs.address2 + "<br />");
	  } else {
		  $("#printStoreAddress2").remove();
	  }
	  
	  $("#printStoreCity").html(rs.city);
	  $("#printStoreState").html(rs.state);
	  $("#printStoreZip").html(rs.zip);
	  $("#printStorePhone").html("Phone: " + rs.phone);
	  $("#printStoreFax").html("Fax: " + rs.fax);
  }
  
  function populateItemTypes(rs) {
	  rs = sortResults(rs, "name", true);
	  $('#itemTypeDropdown').empty();
	  $('#hiddenItemTypeDropdown').empty();
	  $.each(rs, function(i, m) {
		  $('#itemTypeDropdown').append('<option value="' + m.id + '" data-category="'+m.category+'" data-dotrequired="'+m.dotrequired+'">' + m.name + '</option>');
		  $('#hiddenItemTypeDropdown').append('<option value="' + m.id + '" data-category="'+m.category+'" data-dotrequired="'+m.dotrequired+'">' + m.name + '</option>');
	  });
  }
  
  function populateTaxRates(rs) {
	  rs = sortResults(rs, "name", false);
	  $('#taxRateDropdown').empty();
	  $('#hiddenTaxRateDropdown').empty();
	  $.each(rs, function(i, m) {
		  $('#taxRateDropdown').append('<option value="' + m.rate + '" data-category="'+m.category+'" data-exemption="'+m.exemption+'">' + m.name + '</option>');
		  $('#hiddenTaxRateDropdown').append('<option value="' + m.rate + '" data-category="'+m.category+'" data-exemption="'+m.exemption+'">' + m.name + '</option>');
	  });
  }
  
  $('#customerInfoUserType').on('change',function(event) {
	  if($(this).val() == 'B') {
		  $('#businessNameContainer').show();
	  } else {
		  $('#businessNameContainer').hide();
		  $('#customerInfoBusinessName').val("");
	  }
  });
  
  $('#ticketType').on('change',function(event) {
	  //console.log(configObject);
	  $("#customerInfoForm").removeClass('was-validated');
	  $("#customerInfoAddressToggleBtn").removeClass('btn-outline-danger');
	  $("#customerInfoAddressToggleBtn").addClass('btn-outline-primary');
	  if($(this).val() == 'W') {
		  $('#workOrderDetails').show();
		  $('#workOrderDetails2').show();
		  $( ".estimateRequired" ).each(function() {
		    $(this).prop("required",false);
		  });
		  $( ".workOrderRequired" ).each(function() {
		    $(this).prop("required",true);
		  });
	  } else {
		  $('#workOrderDetails').hide();
		  $('#workOrderDetails2').hide();
		  $( ".estimateRequired" ).each(function() {
			    $(this).prop("required",true);
		  });
		  $( ".workOrderRequired" ).each(function() {
		    $(this).prop("required",false);
		  });
	  }
  });
  
  $(document).on("change",'.itemtype',function(event) {
	  var row =  $(this).closest(".form-row");
	  var selectedCategory = row.find(".itemtype option:selected").data("category");
	  row.find(".taxbracket > option").each(function() {
		  if($(this).data("category") == selectedCategory) {
			  $(this).prop('selected', true);
			  row.find(".taxbracket").trigger("change");
		  }
	  });
  });
  
  $(document).on("change blur",'.orderItemCost, .orderItemQuantity',function(event) {
	  var row =  $(this).closest(".form-row");
	  if(row.find(".orderItemCost:visible").length < 1) {
		  return;
	  }
	  

	  var qty = parseFloat(row.find(".orderItemQuantity").first().val());
	  if(isNaN(qty)) {
		  qty = 1;
	  }
	  
	  var itemCost = parseFloat(row.find(".orderItemCost:visible").first().val());
	  if(isNaN(itemCost)) {
		  itemCost = 0;
	  }
	  
	  row.find(".totalItemCost").first().val("$"+(qty * itemCost).toFixed(2));
  });
  
  $(document).on("change blur",'.priceChange',function(event) {
	  var row =  $(this).closest(".form-row");
	  var qty = parseFloat(row.find(".orderItemQuantity").first().val());
	  var retail = parseFloat(row.find(".orderItemRetail").first().val());
	  if(isNaN(retail)) { retail = 0.0;}
	  if(isNaN(qty)) {
		  qty = 1;
		  row.find(".orderItemQuantity").first().val(1);
	  }
	  row.find(".orderItemRetail").first().val(retail.toFixed(2));
	  row.find(".totalPrice").first().val("$"+(qty * retail).toFixed(2));
	  
	  var isExempt = false;
	  if($("input[name=customer]:checked").length > 0) {
		  if($("input[name=customer]:checked").data("taxexempt") == 1) {
			  isExempt = true;
		  }
	  } else if($("#customerInfoTaxExempt").val() == 1) {
		  isExempt = true;
	  }
	  
	  var taxBracket =  row.find(".taxbracket option:selected");
      var rate =  taxBracket.val();
      var exemptPercent = taxBracket.data("exemption");
      
      if(isExempt) {
      	rate = rate - (rate * (exemptPercent/100));
      }
      
      var itemTax = ((rate/100)*(qty * retail).toFixed(3));
      row.find(".taxPrice").first().val(itemTax.toFixed(3));

	  var sum = 0, part = 0, labor = 0, fee = 0, discount = 0, tax = 0;
	  var totals = {};
	  var firstDiscountContainer = false;
	    $('.totalPrice').each(function() {
	    	var curPrice = Number($(this).val().replace(/[^0-9-.]/g, ''));
	    	var selection = $(this).closest(".form-row").find(".itemtype option:selected");
	        var cat =  selection.data("category");
	        
	        if(cat == 'part') {
	        	part += curPrice;
	        	sum += curPrice;
	        }else if(cat == 'labor') {
	        	labor += curPrice;
	        	sum += curPrice;
	        }else if(cat == 'fee') {
	        	fee += curPrice;
	        	sum += curPrice;
	        }else if(cat == 'discount') {
	        	discount += curPrice;
	        	if(rate > 0) { firstDiscountContainer = true;}
	        	sum -= curPrice;
	        } 
	    });
	    
	    $('.taxPrice').each(function() {
	    	var selection = $(this).closest(".form-row").find(".itemtype option:selected");
	        var cat =  selection.data("category");
	        if(cat != 'discount') {
	        	tax += Number($(this).val());
	        } else {
	        	tax -= Number($(this).val());
	        }
	    });

	    sum += Number((Math.round((tax+.001) * 100 ) / 100).toFixed(2));
	    
	    if(fee != 0) {
	    	$("#feesTotalContainer").show();
	    	$("#feesTotal").val("$"+fee.toFixed(2));
	    } else {
	    	$("#feesTotalContainer").hide();
	    }
	    
	    if(discount > 0) {
	    	if(firstDiscountContainer) {
	    		$("#discountsTotalSecondContainer").hide();
	    		$("#discountsTotalFirstContainer").show();
	    		$("#discountsTotalFirst").val("$-"+discount.toFixed(2));
	    	} else {
	    		$("#discountsTotalFirstContainer").hide();
	    		$("#discountsTotalSecondContainer").show();
	    		$("#discountsTotalSecond").val("$-"+discount.toFixed(2));
	    	}
	    } else {
	    	$("#discountsTotalFirstContainer").hide();
	    	$("#discountsTotalSecondContainer").hide();
	    }
	    
	    $("#partsTotal").val("$"+part.toFixed(2));
		$("#laborTotal").val("$"+labor.toFixed(2));
		$("#taxTotal").val("$"+ (Math.round((tax+.001) * 100 ) / 100).toFixed(2));
		
	    $("#grandTotal").val("$"+(Math.round(sum * 100 ) / 100).toFixed(2));
  });
  
  $("#orderItemAddBtn").on('click',function(event) {
	  event.preventDefault();
      var container = document.getElementById("orderItemContainer");
      var newRow = document.createElement("div");
      var originalRow = document.getElementById("hiddenPartRow");
      newRow.innerHTML = originalRow.innerHTML;
      newRow.className = "form-row align-items-center mb-3 mb-0-print orderItemSerialize";
      $(container).append(newRow);
  });
  
  $(document).on('click','.remove',function(event) {
	  var row =  $(this).closest(".form-row");
	  row.remove();
	  $(".taxbracket").trigger("change");
  });
  
  $("#txtCustomerNotes").on('input propertychange', function(event) {
	  $("#printCustomerNotes").text($(this).val());
  });
  
  $(document).on('input propertychange', ".orderItemDescription", function(event) {
	  var row =  $(this).closest(".form-row");
	  row.find(".orderItemDescriptionPrint").first().text($(this).val());
  });
  
  $(document).on('input propertychange', ".orderItemPartNumber", function(event) {
	  var row =  $(this).closest(".form-row");
	  row.find(".orderItemPartNumberPrint").first().text($(this).val())
  });
  
  $(document).on("input propertychange", ".orderItemDotNumber", function(event) {
	  var dots = "";
	  $.each($("#orderItemContainer .orderItemDotNumber:visible"), function() {
		  if(this.value != "") {
			  if(dots != "") { dots += ", "; }
			  dots += this.value;
		  }
	  });
	  $("#printDotNumbers").text(dots);
  });
  
  $(document).on("click", ".showVehicleHistory", function(event) {
	  var vid = $(this).data("vehicle-id");
	  if($("#vehicleHistory"+vid).hasClass("show")) {
		  $("#vehicleHistory"+vid).collapse("hide");
	  } else {
		  if($("#vehicleHistory"+vid).data("fetched-history") != true) {
			  if(vid != "") {
				  $.ajax('./api/v1/order/byVehicle/'+vid + '?store='+$('#storeDropdown').val(), {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {displayVehicleHistory(rs);});
			  } else {
				  var contactId = $(".customerRadio:checked").val();
				  if(!contactId) { contactId = $("#customerInfoContactId").val(); }
				  $.ajax('./api/v1/order/noVehicle/'+contactId + '?store='+$('#storeDropdown').val(), {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {displayVehicleHistory(rs);});
			  }
		  }
		  $("#vehicleHistory"+vid).collapse("show");
		  $("#vehicleHistory"+vid).data("fetched-history",true);
	  }
	  event.preventDefault();
      event.stopPropagation();
  });
  
  function displayVehicleHistory(rs) {
	  var history = "";
	  var openOrders = "";
	  var invoices = "";
	  $.each(rs, function(i, o) {
		  if(o.type == 'I') {
			  if(o.technician == null) { o.technician = "";}
			  invoices += '<tr><td>' + o.orderdate + '</td><td><a class="font-weight-bold" href="workorderedit.php?orderId=' + o.id + '&store='+$('#storeDropdown').val() + '">'+o.id+'</td><td>' + (Number(o.ordertotal)).toFixed(2) + '</td><td>' + o.technician + '</td></tr>';
		  } else if(o.type == 'W') {
			  if(o.technician == null) { o.technician = "";}
			  openOrders += '<tr><td>' + o.orderdate + '</td><td><a class="font-weight-bold" href="workorderedit.php?orderId=' + o.id + '&store='+$('#storeDropdown').val() + '">'+o.id+'</td><td>' + (Number(o.ordertotal)).toFixed(2) + '</td><td>' + o.technician + '</td></tr>';
		  }
	  });
				
	  if(openOrders != "") {
		  history += '<Strong>In Progress</Strong><table class="table">';
		  history += '<tr><th>Date</th><th>Number</th><th>Total</th><th>Technician</th></tr>';
		  history += openOrders + '</table>';
	  }
			
	  if(invoices != "") {
		  history += '<Strong>Completed</Strong><table class="table">';
		  history += '<tr><th>Date</th><th>Number</th><th>Total</th><th>Technician</th></tr>';
		  history += invoices + '</table>';
	  }
	  if(rs[0].vehicle_id == null) {rs[0].vehicle_id = "";}
	  if($("#vehicleHistory"+rs[0].vehicle_id + " td").length > 0) {
		  $("#vehicleHistory"+rs[0].vehicle_id + " td").html(history);
	  } else {
		  $("#vehicleHistory"+rs[0].vehicle_id).html(history);
	  }
  }
  
  $("#workOrderForm").on("change",function(event) {
	  unsaved = true;
  });
  
  $("#workOrderSaveBtn").on('click', function(event) {
	  event.preventDefault();
      event.stopPropagation();
	  var obj = generateWorkOrderObject();
	  obj.store = $("#storeDropdown").val(); 
	  if(obj != null) {
	  	  var appointment_id = getQueryVariables("appointment");
	  	  if (appointment_id) {
	  	  	obj.appointment_id = appointment_id;
	  	  }

		  this.classList.add("disabled");
	      $("#saveAndPrintBtn").addClass("disabled");
	      unsaved = false;
		  $.ajax({
	          url: './api/v1/order/', // url where to submit the request
	          type : "POST", // type of action POST || GET
	          dataType : 'json', // data type
	          data : JSON.stringify(obj), // post data || get data
	          contentType: "application/json",
	          headers:{"Authorization":"Bearer " + Cookies.get('token')},
	          success : function(result) {
	        	  if(result.id) {
	        		  window.location = "index.php";
	              } else {
	            	  $('#orderMessageContainer').html("");
	            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+JSON.stringify(result)+'</div>');
	            	  $("#workOrderSaveBtn").removeClass("disabled");
	            	  $("#saveAndPrintBtn").removeClass("disabled");
	              }
	          },
	          error: function(xhr, resp, text) {
	        	  $('#orderMessageContainer').html("");
            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+text+'</div>');
            	  $("#workOrderSaveBtn").removeClass("disabled");
            	  $("#saveAndPrintBtn").removeClass("disabled");
	          }
	      });
	  }
  });
  
  $("#saveAndPrintBtn").on('click', function(event) {
	  event.preventDefault();
      event.stopPropagation();
      var obj = generateWorkOrderObject();
      obj.store = $("#storeDropdown").val();
	  if(obj != null) {
		  this.classList.add("disabled");
	      $("#workOrderSaveBtn").addClass("disabled");
	      unsaved = false;
		  $.ajax({
	          url: './api/v1/order/', // url where to submit the request
	          type : "POST", // type of action POST || GET
	          dataType : 'json', // data type
	          data : JSON.stringify(obj), // post data || get data
	          contentType: "application/json",
	          headers:{"Authorization":"Bearer " + Cookies.get('token')},
	          success : function(result) {
	        	  if(result.id) {
	        	  	  $.ajax('./api/v1/order/'+result.id+'?store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
		        		  $(window).trigger($.Event('workordersaved'));
		        		  var detail = "Order Number: " + result.id + "<br />";
		        		  var rs = rs[0];
		        		  if (rs.show_reference_no == 1) {
						  	detail+= "Ref: " + rs.reference_number + "<br />";
						  }
	  
		        		  detail += "Date: " + rs.updated + "<br />";

		        		  var techs = "";
						  $('#teamMemberDropdown option:selected').each(function() {
							  if(techs != "") {
								  techs = techs + ", ";
							  }
							  
							  techs = techs + $(this).text();
						  });
						  
						  if($('#teamMemberDropdown option:selected').length > 1) {
							  detail += "Technicians: " + techs + "<br />";
						  } else {
							  detail += "Technician: " + techs + "<br />";
						  }
		        		  $("#printDetail").html(detail);	
		        		  window.print();
		        		  window.location = "index.php";
	        		  });
	              } else {
	            	  $('#orderMessageContainer').html("");
	            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+JSON.stringify(result)+'</div>');
	            	  $("#workOrderSaveBtn").removeClass("disabled");
	            	  $("#saveAndPrintBtn").removeClass("disabled");
	              }
	          },
	          error: function(xhr, resp, text) {
	        	  $('#orderMessageContainer').html("");
            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+text+'</div>');
            	  $("#workOrderSaveBtn").removeClass("disabled");
            	  $("#saveAndPrintBtn").removeClass("disabled");
	          }
	      });
	  }
  });
  
  $("#addTemplateBtn").on('click', function(event) {
	  event.preventDefault();
      event.stopPropagation();
      var obj = {};
      var itemsObj = [];
	  $.each($("#workOrderForm" +  " ." + "orderItemSerialize"), function() {
		  if(this.value != "") {
			  var item = {};
			  item.type = $(this).find(".itemtype").val();
			  item.partnumber = $(this).find(".orderItemPartNumber").val();
			  item.description = $(this).find(".orderItemDescription").val();
			  item.quantity = $(this).find(".orderItemQuantity").val();
			  item.retail = $(this).find(".orderItemRetail").val();
			  item.cost = $(this).find(".orderItemCost").val();
			  item.taxcat = $(this).find(".taxbracket option:selected").data("category");
			  item.dotnumber = $(this).find(".orderItemDotNumber").val();
			  item.vendor_id = $(this).find(".orderItemVendor").val();
			  item.invoicenumber = $(this).find(".orderItemInvoiceNumber").val();
			  item.tax = $(this).find(".taxPrice").val();
			  itemsObj.push(item);
		  }
	  });
	  
	  obj.items = itemsObj;

	  if(itemsObj.length > 0 && $("#newTemplateName").val() != "") {
		  obj.templatename = $("#newTemplateName").val();
		  obj.store = $("#storeDropdown").val();
		  this.classList.add("disabled");
		  $.ajax({
	          url: './api/v1/template/', // url where to submit the request
	          type : "POST", // type of action POST || GET
	          dataType : 'json', // data type
	          data : JSON.stringify(obj), // post data || get data
	          contentType: "application/json",
	          headers:{"Authorization":"Bearer " + Cookies.get('token')},
	          success : function(result) {
	        	  $("#addTemplateBtn").removeClass("disabled");
	        	  if(result.id) {
	        		  $("#newTemplateName").val("");
	        		  $('#newTemplateMessageContainer').html("");
	        		  $("#newTemplateModal").modal("hide");
	        	  } else if(result.error) {
	        		  $('#newTemplateMessageContainer').html("");
	            	  $('#newTemplateMessageContainer').html('<div class="alert alert-danger" role="alert">'+result.error+'</div>');
	              } else {
	            	  $('#newTemplateMessageContainer').html("");
	            	  $('#newTemplateMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Template<br />'+JSON.stringify(result)+'</div>');
	            	  
	              }
	          },
	          error: function(xhr, resp, text) {
	        	  $('#newTemplateMessageContainer').html("");
            	  $('#newTemplateMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+text+'</div>');
            	  $("#addTemplateBtn").removeClass("disabled");
	          }
	      });
	  }
  });
  
  function generateWorkOrderObject() {
	  var isError = false;
	  var workOrderObj = serializeObject("workOrderForm","workOrderSerialize");
	  if($("input[name=customer]:checked").length < 1 && ($("#selectedCustomerId").length < 1 || $("#selectedCustomerId").val() == "")) {
		  $("#customerCard").addClass("border border-danger");
		  isError = true;
	  } else {
		  $("#customerCard").removeClass("border border-danger");
		  workOrderObj.contact_id = $("input[name=customer]:checked").val();
	  }
	  
	  if($("input[name=vehicle]:checked").length < 1 && ($("#selectedVehicleId").length < 1 || $("#selectedVehicleId").val() == "")) {
		  $("#vehicleCard").addClass("border border-danger");
		  isError = true;
	  } else {
		  $("#vehicleCard").removeClass("border border-danger");
		  if($("input[name=vehicle]:checked").val() > 0) {
			  workOrderObj.vehicle_id = $("input[name=vehicle]:checked").val();
		  }
	  }
	  
	  var orderTax = $("#taxTotal").val().replace(/[^0-9-.]/g, '');
	  var orderTotal = $("#grandTotal").val().replace(/[^0-9-.]/g, '');
	  var orderMargin = $("#orderMarginAmount").val()
	  if(orderMargin) {
		  orderMargin = orderMargin.replace(/[^0-9-.]/g, '');
	  }

	  if(orderTotal) {
		  workOrderObj.ordertotal = orderTotal;
	  }
	  if(orderTax) {
		  workOrderObj.ordertax = orderTax;
	  }
	  if(orderMargin) {
		  workOrderObj.ordermargin = orderMargin;
	  }
	  
	  if(isError) {
		  return null;
	  }
	  
	  if($("#currentMileage").length > 0) {
		  workOrderObj.mileage = $("#currentMileage").val();
	  }
	  
	  workOrderObj.type = $("#ticketype").val();
	  workOrderObj.status = $("#ticketStatus").val();
	  workOrderObj.promisedtime = $("#promisedTime").val();
      workOrderObj.teammember_id = $("#teamMemberDropdown").val();
      workOrderObj.starttime = $("#startTime").val();
	  workOrderObj.startdate = $("#startDate").val();
	  
	  var itemsObj = [];
	  $.each($("#workOrderForm" +  " ." + "orderItemSerialize"), function() {
		  if(this.value != "") {
			  var item = {};
			  item.type = $(this).find(".itemtype").val();
			  item.partnumber = $(this).find(".orderItemPartNumber").val();
			  item.description = $(this).find(".orderItemDescription").val();
			  item.quantity = $(this).find(".orderItemQuantity").val();
			  item.retail = $(this).find(".orderItemRetail").val();
			  item.cost = $(this).find(".orderItemCost").val();
			  item.taxcat = $(this).find(".taxbracket option:selected").data("category");
			  item.dotnumber = $(this).find(".orderItemDotNumber").val();
			  item.vendor_id = $(this).find(".orderItemVendor").val();
			  item.invoicenumber = $(this).find(".orderItemInvoiceNumber").val();
			  item.tax = $(this).find(".taxPrice").val();
			  itemsObj.push(item);
		  }
	  });
	  
	  workOrderObj.items = itemsObj;
	  return workOrderObj;
	  
  }
  
  function generateWorkOrderPaymentsObject() {
	  var paymentsObj = [];
	  $.each($("#paymentContainer .form-row"), function() {
		  var amount = $(this).find(".paymentAmount").val();
		  if(amount > 0 || $("#paymentContainer .form-row").length == 1) {
			  var payment = {};
			  payment.method = $(this).find(".paymentMethod").val();
			  payment.amount = amount;
			  var checkNum = $(this).find(".paymentCheckNum:visible");
			  if(checkNum) {payment.checknumber = checkNum.val();}
			  paymentsObj.push(payment);
		  }
	  });
	  return paymentsObj;
  }
  
  $("#workOrderUpdateBtn").on('click', function(event) {
	  event.preventDefault();
      event.stopPropagation();
	  var obj = generateWorkOrderObject();
	  obj.store = $("#storeDropdown").val();
	  obj.contact_id = $("#selectedCustomerId").val();
	  if($("#selectedVehicleId").val() > 0) {
		  obj.vehicle_id = $("#selectedVehicleId").val();
	  }
	  if(obj != null) {
		  this.classList.add("disabled");
		  unsaved = false;
		  $.ajax({
	          url: './api/v1/order/'+orderId, // url where to submit the request
	          type : "PUT", // type of action POST || GET
	          dataType : 'json', // data type
	          data : JSON.stringify(obj), // post data || get data
	          contentType: "application/json",
	          headers:{"Authorization":"Bearer " + Cookies.get('token')},
	          success : function(result) {
	        	  if(result.id) {
	        		  window.location = "index.php";
	              } else {
	            	  $('#orderMessageContainer').html("");
	            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+JSON.stringify(result)+'</div>');
	              }
	        	  $("#workOrderUpdateBtn").removeClass("disabled");
	          },
	          error: function(xhr, resp, text) {
	        	  $('#orderMessageContainer').html("");
            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+text+'</div>');
            	  $("#workOrderUpdateBtn").removeClass("disabled");
	          }
	      });
	  }
  });
  
  $("#workOrderUpdateAndPrintBtn").on('click', function(event) {
	  event.preventDefault();
      event.stopPropagation();
	  var obj = generateWorkOrderObject();
	  obj.store = $("#storeDropdown").val();
	  obj.contact_id = $("#selectedCustomerId").val();
	  obj.vehicle_id = $("#selectedVehicleId").val();
	  if(obj != null) {
		  this.classList.add("disabled");
		  unsaved = false;
		  $.ajax({
	          url: './api/v1/order/'+orderId, // url where to submit the request
	          type : "PUT", // type of action POST || GET
	          dataType : 'json', // data type
	          data : JSON.stringify(obj), // post data || get data
	          contentType: "application/json",
	          headers:{"Authorization":"Bearer " + Cookies.get('token')},
	          success : function(result) {
	        	  if(result.id) {
	        	  	  $.ajax('./api/v1/order/'+orderId+'?store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
		        		  var detail = "Order Number: " + result.id + "<br />";
		        		  var rs = rs[0];
		        		  if (rs.show_reference_no == 1) {
						  	detail+= "Ref: " + rs.reference_number + "<br />";
						  }

		        		  detail += "Date: " + rs.updated + "<br />";
		        		  var techs = "";
		        		  $('#teamMemberDropdown option:selected').each(function() {
		        			  if(techs != "") {
		        				  techs = techs + ", ";
		        			  }
		        			  
		        			  techs = techs + $(this).text();
		        		  });
		        		  
		        		  if($('#teamMemberDropdown option:selected').length > 1) {
		        			  detail += "Technicians: " + techs + "<br />";
		        		  } else {
		        			  detail += "Technician: " + techs + "<br />";
		        		  }
		        		  $("#printDetail").html(detail);	
		        		  window.print();
		        		  window.location = "index.php";
	        		  });
	              } else {
	            	  $('#orderMessageContainer').html("");
	            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+JSON.stringify(result)+'</div>');
	              }
	        	  $("#workOrderUpdateAndPrintBtn").removeClass("disabled");
	          },
	          error: function(xhr, resp, text) {
	        	  $('#orderMessageContainer').html("");
            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+text+'</div>');
            	  $("#workOrderUpdateAndPrintBtn").removeClass("disabled");
	          }
	      });
	  }
  });
  
  $("#workOrderCloseBtn").on('click', function(event) {
	  event.preventDefault();
      event.stopPropagation();
      
      var isError = false;
      $('#invoiceMessageContainer').html("");
      $("#ticketType").removeClass("border border-danger");
	  $("#teamMemberDropdown").removeClass("border border-danger");
          $(".select2-selection--multiple").removeClass("border border-danger");
	  $("#customerCard").removeClass("border border-danger");
	  $("#vehicleCard").removeClass("border border-danger");
	  $("#currentMileage").removeClass("border border-danger");
	  $("#orderItemContainer .orderItemRetail").removeClass("border border-danger");
	  $("#orderItemContainer .orderItemVendor").removeClass("border border-danger");
	  $("#orderItemContainer .orderItemInvoiceNumber").removeClass("border border-danger");
	  $("#orderItemContainer .orderItemCost").removeClass("border border-danger");
	  $("#orderItemContainer .orderItemDotNumber").removeClass("border border-danger");
	  $(".paymentAmount").removeClass("border border-danger");
	  $("#paymentContainer .paymentCheckNum:visible").removeClass("border border-danger");
	  
	  if($("#ticketType").val() != "W") {
		  isError = true;
		  $("#ticketType").addClass("border border-danger");
	  }
	  
	  if($("#teamMemberDropdown").val() == "") {
		  isError = true;
		  $("#teamMemberDropdown").addClass("border border-danger");
                  $(".select2-selection--multiple").addClass("border border-danger");
	  }

	  if($("#selectedCustomerId").val() == "" || $("#selectedCustomerAddress1").val() == "" || 
			  $("#selectedCustomerCity").val() == "" || $("#selectedCustomerZip").val() == "") {
		  isError = true;
		  $("#customerCard").addClass("border border-danger");
	  }
	  
	  if($("#selectedVehicleId").val() == "") {
		  isError = true;
		  $("#vehicleCard").addClass("border border-danger");
	  }
	  
	  if($("#currentMileage").val() == "" && $("#selectedVehicleId").val() > 0) {
		  isError = true;
		  $("#currentMileage").addClass("border border-danger");
	  }
	  
	  $("#orderItemContainer .orderItemRetail").each(function() {
		  if($(this).val() == "") {
			  isError = true;
			  $(this).addClass("border border-danger");
		  }
	  });
	  
	  $("#orderItemContainer .orderItemVendor:visible").each(function() {
		  if($(this).val() == "") {
			  isError = true;
			  $(this).addClass("border border-danger");
		  }
	  });
	  
	  $("#orderItemContainer .orderItemInvoiceNumber:visible").each(function() {
		  if($(this).val() == "") {
			  isError = true;
			  $(this).addClass("border border-danger");
		  }
	  });
	  
	  $("#orderItemContainer .orderItemCost:visible").each(function() {
		  if(isNaN(parseFloat($(this).val()))) {
			  isError = true;
			  $(this).addClass("border border-danger");
		  }
	  });
	  
	  $("#orderItemContainer .orderItemDotNumber:visible").each(function() {
		  if($(this).val() == "") {
			  isError = true;
			  $(this).addClass("border border-danger");
		  }
	  });
	  
	  $("#paymentContainer .paymentMethod").each(function() {
		  if($(this).val() == "") {
			  isError = true;
			  $(this).addClass("border border-danger");
		  }
	  });
	  
	  var total = Number($("#grandTotal").val().replace(/[^0-9-.]/g, ''));
      var totalPaid = 0;
      var ZeroDollarCounter = 0;
      $("#paymentContainer .paymentAmount").each(function() {
    	  var curPrice = Number($(this).val().replace(/[^0-9-.]/g, ''));
    	  totalPaid += curPrice;
    	  if(curPrice === 0) {
    		  ZeroDollarCounter++;
    	  }
	  });

      if(($("#paymentContainer .form-row").length > 1 && ZeroDollarCounter > 0) || ZeroDollarCounter > 1) {
    	  isError = true;
    	  $(".paymentAmount").addClass("border border-danger")
      }
      
      if(totalPaid.toFixed(2) != total.toFixed(2)) {
    	  isError = true;
    	  $(".paymentAmount").addClass("border border-danger");
      }
      
      $("#paymentContainer .paymentCheckNum:visible").each(function() {
		  if($(this).val() == "") {
			  isError = true;
			  $(this).addClass("border border-danger");
		  }
	  });
      
      if(!isError) {
		  var obj = generateWorkOrderObject();
		  obj.store = $("#storeDropdown").val();
		  if(obj != null) {
			  obj.contact_id = $("#selectedCustomerId").val();
			  obj.vehicle_id = $("#selectedVehicleId").val();
			  obj.payments = generateWorkOrderPaymentsObject();
			  this.classList.add("disabled");
			  unsaved = false;
			  $.ajax({
		          url: './api/v1/order/'+orderId, // url where to submit the request
		          type : "PUT", // type of action POST || GET
		          dataType : 'json', // data type
		          data : JSON.stringify(obj), // post data || get data
		          contentType: "application/json",
		          headers:{"Authorization":"Bearer " + Cookies.get('token')},
		          success : function(result) {
		        	  if(result.id) {
		        	  	  $.ajax('./api/v1/order/'+orderId+'?store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			        		  $(window).trigger($.Event('workordersaved'));
			        		  var detail = "Invoice: " + result.id + "<br />";
			        		  var rs = rs[0];
			        		  if (rs.show_reference_no == 1) {
							  	detail+= "Ref: " + rs.reference_number + "<br />";
							  }

			        		  detail += "Date: " + rs.updated + "<br />";
			        		  var techs = "";
			        		  $('#teamMemberDropdown option:selected').each(function() {
			        			  if(techs != "") {
			        				  techs = techs + ", ";
			        			  }
			        			  
			        			  techs = techs + $(this).text();
			        		  });
			        		  
			        		  if($('#teamMemberDropdown option:selected').length > 1) {
			        			  detail += "Technicians: " + techs + "<br />";
			        		  } else {
			        			  detail += "Technician: " + techs + "<br />";
			        		  }
			        		  detail += "Payment: ";
			        		  var pays = [];
			        		  $.each($("#paymentContainer .paymentMethod option:selected"), function(){
			        			  var checkNum = $(this).closest(".form-row").find(".paymentCheckNum:visible");
			        			  if(checkNum.length) {
			        				  pays.push($(this).text() + " #" + checkNum.val());
		        				  } else {
		        					  pays.push($(this).text());
		        				  }
			        		  })
			        		  detail += pays.join(", ");
			        		  $("#printDetail").html(detail);
			        		  $("#printOdometer").addClass("d-print-none");
			        		  $("#workOrderPrintFooter").removeClass("col-print-12");
			        		  $("#workOrderPrintFooter").addClass("d-print-none");
			        		  $("#invoicePrintFooter").removeClass("d-print-none");
			        		  $("#invoicePrintFooter").addClass("col-print-12");
			        		  window.print();
			        		  window.location = "index.php";
		        		  });
		              } else {
		            	  console.log("Error saving order - No Id: " + JSON.stringify(result));
		            	  $('#orderMessageContainer').html("");
		            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+JSON.stringify(result)+'</div>');
		              }
		        	  $("#workOrderCloseBtn").removeClass("disabled");
		          },
		          error: function(xhr, resp, text) {
		        	  console.log("Error saving order: " + text);
		        	  $('#orderMessageContainer').html("");
	            	  $('#orderMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Order<br />'+text+'</div>');
	            	  $("#workOrderCloseBtn").removeClass("disabled");
		          }
		      });
		  }
      } else {
    	  $('#invoiceMessageContainer').html('<div class="alert alert-danger" role="alert">Please validate highlighted fields and try again.</div>');
      }
  });
  
  $(document).on('change','.itemtype', function(event) {
	  if($(this).find(":selected").data("category") == "part") {
		  $(this).closest(".form-row").children(".nonlabor").show();
		  if($(this).find(":selected").data("dotrequired") == "1") {
			  $(this).closest(".form-row").children(".nonlaborfirst").removeClass("offset-md-4");
			  $(this).closest(".form-row").children(".dotrequired").show();
		  } else {
			  $(this).closest(".form-row").children(".nonlaborfirst").addClass("offset-md-4");
			  $(this).closest(".form-row").children(".dotrequired").hide();
		  }
	  } else {
		  $(this).closest(".form-row").children(".nonlabor").hide();
		  $(this).closest(".form-row").children(".dotrequired").hide();
		  if($(this).find(":selected").data("category") == "labor") {
			  var retail = $(this).closest(".form-row").find(".orderItemRetail");
			  if(retail.val() == "" || retail.val() == 0) {
				  retail.val(configObject.store.laborrate);
				  retail.trigger("change");
			  }
		  }
	  }
	  
	  if($(this).val() == 3 || $(this).val() == 4) {
		  $(".stockItem:checked").prop('checked', false);
		  $("#stockPartModal").modal("show");
		  var selectedIndex = $(".form-row").index($(this).closest(".form-row"));
		  $("#selectedStockRow").val(selectedIndex);
	  }
	  
	  if($(this).val() == 1 || $(this).val() == 2) {
		  $(this).closest(".form-row").children(".nonlaborfirst").removeClass("offset-md-4");
		  if($(this).find(":selected").data("dotrequired") == "1") {
			  $(this).closest(".form-row").children(".dotrequired").removeClass("col-md-4").addClass("col-md-2").show();
			  $(this).closest(".form-row").children(".outsidepurchase").removeClass("col-md-4").addClass("col-md-2").show();
		  } else {
			  $(this).closest(".form-row").children(".outsidepurchase").removeClass("col-md-2").addClass("col-md-4").show();
			  $(this).closest(".form-row").children(".dotrequired").hide();
			  $(this).closest(".form-row").children(".dotrequired").removeClass("col-md-2").addClass("col-md-4");
		  }
	  } else {
		  $(this).closest(".form-row").children(".outsidepurchase").hide();
		  $(this).closest(".form-row").children(".dotrequired").removeClass("col-md-2").addClass("col-md-4");
	  }
  });
  
  $(document).on('click','#addStockItemBtn', function(event) {
	  event.preventDefault();
      event.stopPropagation();
      var workOrderItem = $(".form-row").get($("#selectedStockRow").val());
      var selectedInventory = $(".stockItem:checked");
	  $(workOrderItem).find(".orderItemDescription").val(selectedInventory.data("description"));
	  $(workOrderItem).find(".orderItemDescription").trigger("input");
	  $(workOrderItem).find(".orderItemPartNumber").val(selectedInventory.data("partnumber"));
	  $(workOrderItem).find(".orderItemPartNumber").trigger("input");
	  $(workOrderItem).find(".orderItemCost").val(selectedInventory.data("cost"));
	  $(workOrderItem).find(".orderItemCost").trigger("blur");
	  $(workOrderItem).find(".orderItemRetail").val(selectedInventory.data("retail"));
	  $(workOrderItem).find(".orderItemRetail").trigger("blur");
	  $(workOrderItem).find(".orderItemVendor").val(selectedInventory.data("vendor"));
	  $("#stockPartModal").modal("hide");
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
		  this.classList.add("disabled");
		  $.ajax('./api/v1/customer/search?' + urlParams + '&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			  displayCustomerList(rs,'customerRadio');
		  });
		  this.classList.remove("disabled");
	  }
	  event.preventDefault();
  });
  
  $(document).on('click', '#customerAddBtn', function(event) {
	  if($('#searchFirstName').val() != "") {
		  $('#customerInfoFirstName').val($('#searchFirstName').val());
	  }
	  if($('#searchLastName').val() != "") {
		  $('#customerInfoLastName').val($('#searchLastName').val());
	  }
	  if($('#searchPhone').val() != "") {
		  $('#customerInfoPhone1').val($('#searchPhone').val().replace(/\D/g,''));
	  }
	  $('#addCustomerModal').modal('show');
	  event.preventDefault();
  });
  
  $(document).on('click', '#vehicleSearchBtn', function(event) {
	  var urlParams = "";
	  if($('#searchLicensePlate').val() != "") {
		  if(urlParams != "") { urlParams += "&"; }
		  urlParams += "license=" + $('#searchLicensePlate').val();
	  }
	  
	  if(urlParams != "") {
		  this.classList.add("disabled");
		  $.ajax('./api/v1/vehicle/search?' + urlParams + '&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			  displayVehicleList(rs,'vehicleRadio',false);
		  });
		  this.classList.remove("disabled");
	  }
	  event.preventDefault();
  });
  
  function displayCustomerList(rs, cls) {
	  if(rs.data.length > 0) {
	  	  var rs = rs.data;	
		  var displayTable = '<table class="table table-striped"><thead class="thead-light"><tr><th scope="col"></th><th scope="col">Business Name</th><th scope="col">First Name</th><th scope="col">Last Name</th><th scope="col">Phone</th><th scope="col"></th></tr></thead><tbody>';
		  for(var c in rs) {
			   if(!rs[c].hasOwnProperty("taxexempt")) {rs[c].taxexempt = 0;}
			   if(!rs[c].hasOwnProperty("businessname")) {rs[c].businessname = "";}
			   displayTable += '<tr><td scope="row"><input name="customer" type="radio" class="'+cls+'" value="'+rs[c].contact.id+'" data-taxexempt="'+rs[c].taxexempt+'" data-customerid="'+rs[c].contact.customer_id+'" /></td><td>'+rs[c].businessname+'</td><td>'+rs[c].contact.firstname+'</td><td>'+rs[c].contact.lastname+'</td><td>'+rs[c].contact.phone1+'</td>';
			   displayTable += '<td><div class="custInfo" style="display:none;">';
			   if(rs[c].hasOwnProperty('businessname') && rs[c].businessname != "") {
				   displayTable += rs[c].businessname + '<br />';
			   } else {
				   displayTable += rs[c].contact.firstname + ' ' + rs[c].contact.lastname + '<br />';
			   }
			   
			   
			   if(rs[c].hasOwnProperty('addressline1') && rs[c].addressline1 != "") {
				   displayTable += rs[c].addressline1 + '<br />';
			   }
			   if(rs[c].hasOwnProperty('addressline2') && rs[c].addressline2 != "") {
				   displayTable += rs[c].addressline2 + '<br />';
			   }
			   if(rs[c].hasOwnProperty('addressline3') && rs[c].addressline3 != "") {
				   displayTable += rs[c].addressline3 + '<br />';
			   }
			   if(rs[c].hasOwnProperty('city') && rs[c].city != "" && rs[c].hasOwnProperty('zip') && rs[c].zip != "") {
				   displayTable += rs[c].city + ', ' + rs[c].state + ' ' + rs[c].zip + '<br />';
			   }
			   
			   if(rs[c].hasOwnProperty('businessname') && rs[c].businessname != "") {
				   displayTable += rs[c].contact.firstname + ' ' + rs[c].contact.lastname + '<br />';
			   }
			   displayTable += rs[c].contact.phone1;
			   displayTable += '</div></td></tr>';
			}
		  displayTable += '</tbody></table>';
		  $('#customerSearchResults').html(displayTable);
		  $('#vehicleAddBtn').addClass('disabled');
	  } else {
		  $('#customerSearchResults').html("No Results Found");
		  $('#vehicleAddBtn').addClass('disabled');
	  }
  }
  
  function displayVehicleList(rs, cls, displayNoVehicle) {
  	  var rs = rs.data;	
	  if(rs.length > 0) {
		  var displayTable = '<table class="table"><thead class="thead-light"><tr><th scope="col"></th><th scope="col">Year</th><th scope="col">Make</th><th scope="col">Model</th><th scope="col">License</th><th scope="col"></th></tr></thead><tbody>';
		  for(var v in rs) {
			  if(!rs[v].hasOwnProperty('vin')) { rs[v].vin = ""; }
			  if(!rs[v].hasOwnProperty('license')) { rs[v].license = ""; }
			  if(!rs[v].hasOwnProperty('trim')) { rs[v].trim = ""; }
			  if(!rs[v].hasOwnProperty('mileage')) { rs[v].mileage = ""; }
			   var vehicleid = (rs[v].extrnid)? rs[v].extrnid : rs[v].id; 
			   displayTable += '<tr><td scope="row"><input name="vehicle" type="radio" class="'+cls+'" value="'+vehicleid+'" /></td><td>'+rs[v].year+'</td><td>'+rs[v].make+'</td><td>'+rs[v].model+'</td><td>'+rs[v].license+'</td>';
			   displayTable += '<td><a href="#" class="showVehicleHistory font-weight-bold" data-vehicle-id="'+vehicleid+'">History</a><div class="vehicleInfo" style="display:none;">';
			   displayTable += 'VIN: ' + rs[v].vin + '<br />';
			   displayTable += 'Vehicle: ' + rs[v].year + ' ' + rs[v].make + ' ' + rs[v].model + ' ' + rs[v].trim + '<br />';
			   displayTable += 'License: ' + rs[v].license + '<br />';
			   if(rs[v].hasOwnProperty("fleetnum")) {
				   displayTable += 'Fleet Number: ' + rs[v].fleetnum + '<br />';
			   }
			   displayTable += '<div id="printOdometer">Odometer: ' + rs[v].mileage + '<br /></div>';
			   displayTable += 'Current Odometer:';
			   displayTable += '</div></td></tr>';
			   displayTable += '<tr class="collapse" id="vehicleHistory'+vehicleid+'"><td colspan="6">No History Found</td></tr>';
			}
		  if(displayNoVehicle) {
			  displayTable += '<tr><td scope="row"><input name="vehicle" type="radio" class="'+cls+'" value="-1" /></td><td colspan="4">No Vehicle</td><td><a href="#" class="showVehicleHistory font-weight-bold" data-vehicle-id="">History</a><div class="vehicleInfo" style="display:none;">No Vehicle</div></td></tr>';
			  displayTable += '<tr class="collapse" id="vehicleHistory"><td colspan="6">No History Found</td></tr>';
		  }
		  displayTable += '</tbody></table>';
		  $('#vehicleList').html(displayTable);
	  } else if(displayNoVehicle) {
		  var displayTable = '<table class="table"><thead class="thead-light"><tr><th scope="col"></th><th scope="col">Year</th><th scope="col">Make</th><th scope="col">Model</th><th scope="col">License</th><th scope="col"></th></tr></thead><tbody>';
		  displayTable += '<tr><td scope="row"><input name="vehicle" type="radio" class="'+cls+'" value="-1" /></td><td colspan="4">No Vehicle</td><td><a href="#" class="showVehicleHistory font-weight-bold" data-vehicle-id="">History</a><div class="vehicleInfo" style="display:none;">No Vehicle</div></td></tr>';
		  displayTable += '<tr class="collapse" id="vehicleHistory"><td colspan="6">No History Found</td></tr>';
		  displayTable += '</tbody></table>';
		  $('#vehicleList').html(displayTable);
	  } else {
		  $('#vehicleList').html("No Results Found");
	  }
  }

  $(document).on('change', '.customerRadio', function(e){
	   if(e.target.checked){
		   $.ajax('./api/v1/vehicle/byContact/' + $(this).data('customerid') + '?store='+$("#storeDropdown").val(), {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			   displayVehicleList(rs,'vehicleFromCustomerRadio',true);
		   });
		   $('#vehicleAddBtn').removeClass('disabled');
		   $("#printCustomer").html($(this).closest("tr").find(".custInfo").html());
	   }
	});
  
  $(document).on('change', '.customerFromVehicleRadio', function(e){
	   if(e.target.checked){
		   $('#vehicleAddBtn').removeClass('disabled');
		   $("#printCustomer").html($(this).closest("tr").find(".custInfo").html());
	   }
	});
  
  $(document).on('change', '.vehicleRadio', function(e){
	   if(e.target.checked){
		   $.ajax('./api/v1/customer/byVehicle/' + e.target.value + '&store='+$("#storeDropdown").val(), {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			   displayCustomerList(rs,'customerFromVehicleRadio');
		   });
		   $("#printVehicle").html($(this).closest("tr").find(".vehicleInfo").html());
	   }
	});
  
  $(document).on('change', '.vehicleFromCustomerRadio', function(e){
	   if(e.target.checked){
		   $("#printVehicle").html($(this).closest("tr").find(".vehicleInfo").html());
	   }
	});
  
  $(document).on('click', '#customerInfoSaveBtn', function(event) {
	  var form = document.getElementById("customerInfoForm");
	  if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        console.log("It is: " + $('#collapseAddress .form-control:invalid').length);
        if($('#collapseAddress .form-control:invalid').length > 0) {
        	$("#customerInfoAddressToggleBtn").removeClass("btn-outline-primary");
        	$("#customerInfoAddressToggleBtn").addClass("btn-outline-danger");
        }
        return;
      }
	  var customerHtml  =    "";
          var phonetype1    =   "";
          var phonetype2    =   "";
          var phonetype3    =   "";
          var phone1        =   "";
          var phone2        =   "";
          var phone3        =   "";
	  var isInteral = $("#customerInternal").prop('checked');
	  var isPrimary = $("#customerPrimaryContact").prop('checked');
	  
	  var formData = serializeCustomer("customerInfoForm");
	  formData['store'] = $("#storeDropdown").val();
	  if(isInteral) {
		  formData["internal"] = 1;
	  }
	  if(isPrimary) {
		  formData["contact"]["isPrimary"] = 1;
	  }
	  var submitType = "POST";
	  
	  if(($("#customerInfoContactId").length > 0 && $("#customerInfoContactId").val() != "") && ($("#customerInfoCustomerId").length > 0 && $("#customerInfoCustomerId").val() != "")) {
		  submitType = "PUT";
		  formData.contact_id = $("#customerInfoContactId").val();
		  formData.customer_id = $("#customerInfoCustomerId").val();
	  }
	  
	  this.classList.add("disabled");
	  $.ajax({
          url: './api/v1/customer/', // url where to submit the request
          type : submitType, // type of action POST || GET
          dataType : 'json', // data type
          data : JSON.stringify(formData), // post data || get data
          contentType: "application/json",
          headers:{"Authorization":"Bearer " + Cookies.get('token')},
          success : function(result) {
              if(result.id) {
            	  formData.contact.id = result.contact_id;
            	  formData.contact.customer_id = result.id;
            	  if(submitType === "POST") {
            		  displayCustomerList({"data":[formData]},'customerRadio');
            		  $('#addCustomerModal').modal("hide");
            	  } else {
            		  $("#editCustomerModal").modal("hide");
            		  var customerHtml = "";
            		  if(formData.hasOwnProperty('businessname') && formData.businessname != "") {
            			  customerHtml += formData.businessname + '<br />';
            		  } else {
            			  customerHtml += formData.contact.firstname + ' ' + formData.contact.lastname + '<br />';
            		  }
            		  
            		  if(formData.hasOwnProperty('addressline1') && formData.addressline1 != "") {
            			  customerHtml += formData.addressline1 + '<br />';
            			  $("#selectedCustomerAddress1").val(formData.addressline1);
            		  }
            		  if(formData.hasOwnProperty('addressline2') && formData.addressline2 != "") {
            			  customerHtml += formData.addressline2 + '<br />';
            		  }
            		  if(formData.hasOwnProperty('addressline3') && formData.addressline3 != "") {
            			  customerHtml += formData.addressline3 + '<br />';
            		  }
            		  if(formData.hasOwnProperty('city') && formData.city != "" && formData.hasOwnProperty('zip') && formData.zip != "") {
            			  customerHtml += formData.city + ', ' + formData.state + ' ' + formData.zip + '<br />';
            			  $("#selectedCustomerCity").val(formData.city);
                		  $("#selectedCustomerZip").val(formData.zip);
            		  }
            		  
            		  if(formData.hasOwnProperty('businessname') && formData.businessname != "") {
            			  customerHtml += formData.contact.firstname + ' ' + formData.contact.lastname + '<br />';
            		  }
            		  if(formData.contact.phone1type == 'C') {
                            phonetype1 = "Cell";
                          }
                          if(formData.contact.phone1type == 'W') {  
                            phonetype1 = "Work";
                          }
                          if(formData.contact.phone1type == 'H') {
                            phonetype1 = "Home";
                          }
                          if(formData.contact.phone2type == 'C') {
                            phonetype2 = "Cell";
                          }

                          if(formData.contact.phone2type == 'W') {  
                            phonetype2 = "Work";
                          }
                          if(formData.contact.phone2type == 'H') {
                            phonetype2 = "Home";
                          }
                          if(formData.contact.phone3type == 'C') {
                            phonetype3 = "Cell";
                          }

                          if(formData.contact.phone3type == 'W') {  
                            phonetype3 = "Work";
                          }
                          if(formData.contact.phone3type == 'H') {
                            phonetype3 = "Home";
                          }
                          phone1 = formData.contact.phone1;
                          phone2 = formData.contact.phone2;
                          phone3 = formData.contact.phone3;
                          if(typeof(phone1) != "undefined" && phone1 !== null) {
                            customerHtml += phonetype1+'  :   ' + phone1 + '<br />';
                          }
                          if(typeof(phone2) != "undefined" && phone2 !== null) {
                            customerHtml += phonetype2+'  :   ' + phone2 + '<br />';
                          }
                          if(typeof(phone3) != "undefined" && phone3 !== null) {  
                            customerHtml += phonetype3+'  :   ' + phone3 + '<br />';
                          }
            		  $("#printCustomer").html(customerHtml);
            		  $("#customerDetails").html(customerHtml);
            	  }
            	  
              } else {
            	  $('#customerInfoFormMessageContainer').html("");
            	  $('#customerInfoFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Customer<br />'+JSON.stringify(result)+'</div>');
              }
              $("#customerInfoSaveBtn").removeClass("disabled");
          },
          error: function(xhr, resp, text) {
        	  $('#customerInfoFormMessageContainer').html("");
        	  $('#customerInfoFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Customer<br />'+text+'</div>');
        	  $("#customerInfoSaveBtn").removeClass("disabled");
          }
      })
	  event.preventDefault();
  });
  
  function serializeCustomer(form) {
	  var retObj = serializeObject(form,"customerSerialize");
	  var contact = serializeObject(form,"contactSerialize");
	  retObj['contact'] = contact;
	  return retObj;
  }
  
  function serializeObject(form,cls) {
	  var retObj = {};
	  $.each($("#" + form + " ." + cls), function() {
		  if(this.value != "") {
			  retObj[this.name] = this.value;
		  }
	  });	  
	  return retObj;
  }
  
  $(document).on('click', '#vehicleAddSaveBtn', function(event) {
	  var form = document.getElementById("vehicleAddForm");
	  var customerId = $('input[name=customer]:checked').data('customerid') || $('#selectedCustomerId').val();
	  var vehicleId = $("#selectedVehicleId").val();
	  
	  if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        return;
      }
	  
	  if(typeof customerId == 'undefined' && typeof vehicleId == 'undefined') {
		  $('#vehicleAddFormMessageContainer').html("");
    	  $('#vehicleAddFormMessageContainer').html('<div class="alert alert-danger" role="alert">No customer selected</div>');
    	  return;
	  }
	  
	  $('#vehicleAddCustomerId').val(customerId);
	  var formData = serializeObject("vehicleAddForm","vehicleSerialize");
	  formData.store = $('#storeDropdown').val();
	  
	  var submitType = "POST";
	  
	  if(typeof vehicleId != 'undefined' && vehicleId > 0) {
		  submitType = "PUT";
		  formData.id = vehicleId;
	  }
	  
	  this.classList.add("disabled");
	  $.ajax({
          url: './api/v1/vehicle/', // url where to submit the request
          type : submitType, // type of action POST || GET
          dataType : 'json', // data type
          data : JSON.stringify(formData), // post data || get data
          contentType: "application/json",
          headers:{"Authorization":"Bearer " + Cookies.get('token')},
          success : function(result) {
              if(result.id) {
            	  if(submitType === "POST" && !(isPage('workorderedit.php'))) {
	            	  formData.id = result.id;
	            	  displayVehicleList({"data":[formData]},'vehicleFromCustomerRadio',true);
	            	  $('#addVehicleModal').modal("hide");
            	  } else {
            		  if(!formData.hasOwnProperty('vin')) { formData.vin = ""; }
            		  if(!formData.hasOwnProperty('license')) { formData.license = ""; }
            		  if(!formData.hasOwnProperty('trim')) { formData.trim = ""; }
            		  if(!formData.hasOwnProperty('mileage')) { formData.mileage = ""; }
            		  var vehicleHtml = "";
            		  vehicleHtml += 'VIN: ' + formData.vin + '<br />';
            		  vehicleHtml += 'Vehicle: ' + formData.year + ' ' + formData.make + ' ' + formData.model + ' ' + formData.trim + '<br />';
            		  vehicleHtml += 'License: ' + formData.license + '<br />';
            		  if(formData.hasOwnProperty("fleetnum")) {
            			  vehicleHtml += 'Fleet Number: ' + formData.fleetnum + '<br />';
            		  }
            		  vehicleHtml += '<div id="printOdometer">Odometer: ' + formData.mileage + '<br /></div>';
            		  $("#vehicleDetails").html(vehicleHtml);
            		  
            		  vehicleHtml += 'Current Odometer: <span id="printCurrentOdometer">' + $("#currentMileage").val() + '</span>';
            		  $("#printVehicle").html(vehicleHtml);
            		  $("#editVehicleModal").modal("hide");
            		  if (Number.isInteger(result.id)) {
            		  	  $('#selectedVehicleId').val(result.id);
            		  	  $("#vehicleInfoBtn").text('Edit');
            		  }
            	  }
            	  
              } else {
            	  $('#vehicleAddFormMessageContainer').html("");
            	  $('#vehicleAddFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving New Vehicle<br />'+JSON.stringify(result)+'</div>');
              }
              $("#vehicleAddSaveBtn").removeClass("disabled");
          },
          error: function(xhr, resp, text) {
        	  $('#vehicleAddFormMessageContainer').html("");
        	  $('#vehicleAddFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving New Vehicle<br />'+text+'</div>');
        	  $("#vehicleAddSaveBtn").removeClass("disabled");
          }
      })
	  event.preventDefault();
  });
  
  $("#yearDropdownToggle").on("click", function(e){
	  if($("#yearDropdown").css("display") == "block") {
		  $("#yearDropdown").css("display","none");
		  $("#yearDropdownOverride").css("display","block");
		  $("#yearDropdownOverride").prop("name","year");
		  $("#yearDropdownOverride").prop("required",true);
		  $("#yearDropdownOverride").addClass("vehicleSerialize");
		  $("#yearDropdown").removeClass("vehicleSerialize");
		  $("#yearDropdown").prop("name","");
		  $("#yearDropdown").prop("required",false);
	  } else {
		  $("#yearDropdown").css("display","block");
		  $("#yearDropdownOverride").css("display","none");
		  $("#yearDropdownOverride").prop("name","");
		  $("#yearDropdownOverride").prop("required",false);
		  $("#yearDropdownOverride").removeClass("vehicleSerialize");
		  $("#yearDropdown").addClass("vehicleSerialize");
		  $("#yearDropdown").prop("name","year");
		  $("#yearDropdown").prop("required",true);
	  }
	  e.preventDefault();
  });
  
  $("#makeDropdownToggle").on("click", function(e){
	  if($("#makeDropdown").css("display") == "block") {
		  $("#makeDropdown").css("display","none");
		  $("#makeDropdownOverride").css("display","block");
		  $("#makeDropdownOverride").prop("name","make");
		  $("#makeDropdownOverride").prop("required",true);
		  $("#makeDropdownOverride").addClass("vehicleSerialize");
		  $("#makeDropdown").removeClass("vehicleSerialize");
		  $("#makeDropdown").prop("name","");
		  $("#makeDropdown").prop("required",false);
	  } else {
		  $("#makeDropdown").css("display","block");
		  $("#makeDropdownOverride").css("display","none");
		  $("#makeDropdownOverride").prop("name","");
		  $("#makeDropdownOverride").prop("required",false);
		  $("#makeDropdownOverride").removeClass("vehicleSerialize");
		  $("#makeDropdown").addClass("vehicleSerialize");
		  $("#makeDropdown").prop("name","make");
		  $("#makeDropdown").prop("required",true);
	  }
	  e.preventDefault();
  });
  
  $("#modelDropdownToggle").on("click", function(e){
	  if($("#modelDropdown").css("display") == "block") {
		  $("#modelDropdown").css("display","none");
		  $("#modelDropdownOverride").css("display","block");
		  $("#modelDropdownOverride").prop("name","model");
		  $("#modelDropdownOverride").prop("required",true);
		  $("#modelDropdownOverride").addClass("vehicleSerialize");
		  $("#modelDropdown").removeClass("vehicleSerialize");
		  $("#modelDropdown").prop("name","");
		  $("#modelDropdown").prop("required",false);
	  } else {
		  $("#modelDropdown").css("display","block");
		  $("#modelDropdownOverride").css("display","none");
		  $("#modelDropdownOverride").prop("name","");
		  $("#modelDropdownOverride").prop("required",false);
		  $("#modelDropdownOverride").removeClass("vehicleSerialize");
		  $("#modelDropdown").addClass("vehicleSerialize");
		  $("#modelDropdown").prop("name","model");
		  $("#modelDropdown").prop("required",true);
	  }
	  e.preventDefault();
  });
  
  $(document).on('click', '#vehicleAddBtn', function(e){
	  if($('#makeDropdown > option').length < 1) {
		  $.ajax('https://vpic.nhtsa.dot.gov/api/vehicles/GetMakesForVehicleType/MPV?format=json').then(function (rs) {
			  if(rs.Message == "Execution Error") {
				  $.ajax('./api/v1/GetMakesForVehicleType.json').then(function (rs) {
					  populateNewVehicleMakes(rs.Results);
				  });
			  } else {
				  populateNewVehicleMakes(rs.Results);
			  }
		  });
		  var d = new Date();
		  var newest = d.getFullYear();
		  if(d.getMonth() > 8) { newest++; }
		  for (var i = newest; i > 1940; i--) {
		      $('#yearDropdown').append($('<option />').val(i).html(i));
		  }
	  }
	});
  
  $(document).on('click', '#vehicleInfoBtn', function(e){
	  var year = $("#selectedVehicleYear").val();
	  if($('#makeDropdown > option').length < 1) {
		  $.ajax('https://vpic.nhtsa.dot.gov/api/vehicles/GetMakesForVehicleType/MPV?format=json').then(function (rs) {
			  if(rs.Message == "Execution Error") {
				  $.ajax('./api/v1/GetMakesForVehicleType.json').then(function (rs) {
					  populateNewVehicleMakes(rs.Results);
				  });
			  } else {
				  populateNewVehicleMakes(rs.Results);
			  }
		  });
		  var d = new Date();
		  var newest = d.getFullYear();
		  if(d.getMonth() > 8) { newest++; }
		  for (var i = newest; i > 1940; i--) {
			  $('#yearDropdown').append($('<option />').val(i).html(i));
		  }
		  
		  if(year) {
			  $('#yearDropdown').val(year);
			  if(year != $('#yearDropdown').val()) {
				  $("#yearDropdownOverride").val(year);
				  $("#yearDropdownToggle").trigger("click");
			  }
		  }
	  }
	});
  
  $(document).on('change', '#makeDropdown, #yearDropdown', function(e){
	  if($('#makeDropdown').val() != "") {
		  $('#modelDropdown').empty();
		  var year =  $('#yearDropdown').val();
		  var makeId = $('#makeDropdown option:selected').data('id');
		  $.ajax('https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeIdYear/makeId/'+makeId+'/modelyear/'+year+'?format=json').then(function (rs) {
			  populateNewVehicleModels(rs.Results,'');
		  });
	  }
	});
  
  $(document).on('click', '#vinDecode', function(e){
	  if($('#yearDropdown').val() != "" && $('#vinText').val() != "") {
		  var vin = $('#vinText').val();
		  var year =  $('#yearDropdown').val();
		  $.ajax('https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvaluesextended/'+vin+'?format=json&modelyear='+year).then(function (rs) {
			  populateNewVehicleFromVIN(rs.Results);
		  });
	  }
	});
  
  $(document).on('click', '#temaplateAddBtn', function(e){
	  event.preventDefault();
      event.stopPropagation();
	  if($("#templateListSelected").val() != "") {
		  $.ajax('./api/v1/template/'+$("#templateListSelected").val()+ '?store=' +$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			  addTemplateItemsToOrder(rs);
		  });
	  }
  });
  
  $(document).on("click", "#deleteTemplateBtn", function(e){
	  event.preventDefault();
      event.stopPropagation();
	  if($("#templateListSelected").val() != "") {
		  $.ajax('./api/v1/template/'+$("#templateListSelected").val()+ '?store=' +$("#storeDropdown").val(), {cache: false, type: 'DELETE', headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			  $("#deleteTemplateModal").modal("hide");
			  $("#templateListSelected").val("");
			  $(".templateList").val("");
			  $(".templateList").trigger("change");
		  });
	  }
  });
  
  function addTemplateItemsToOrder(rs) {
	  var firstItemContainer = document.getElementById("firstOrderItem");
	  if(firstItemContainer) {
		  var partnumber = $(firstItemContainer).find(".orderItemPartNumber").val();
		  var description = $(firstItemContainer).find(".orderItemDescription").val();
		  var quantity = $(firstItemContainer).find(".orderItemQuantity").val();
		  var cost = $(firstItemContainer).find(".orderItemCost").val();
		  
		  if(partnumber == "" && description == "" && quantity == "" && cost == "") {
			  $(firstItemContainer).find(".remove").trigger("click");
		  }
	  }
	  var container = document.getElementById("orderItemContainer");
	  for(var i in rs.items) {
		  var newRow = document.createElement("div");
	      var originalRow = document.getElementById("hiddenPartRow");
	      newRow.innerHTML = originalRow.innerHTML;
	      newRow.className = "form-row align-items-center mb-3 mb-0-print orderItemSerialize";
	      $(container).append(newRow);
	      $(newRow).find(".itemtype").val(rs.items[i].itemtype_id);
	      $(newRow).find(".itemtype").trigger("change");
	      $(newRow).find(".taxbracket").find("[data-category='" + rs.items[i].taxcat + "']").prop('selected', true);
	      $(newRow).find(".orderItemPartNumber").val(rs.items[i].partnumber);
	      $(newRow).find(".orderItemPartNumber").trigger("input");
	      $(newRow).find(".orderItemDescription").val(rs.items[i].description);
	      $(newRow).find(".orderItemDescription").trigger("input");
	      $(newRow).find(".orderItemQuantity").val(rs.items[i].quantity);
	      $(newRow).find(".orderItemCost").val(rs.items[i].cost);
	      $(newRow).find(".orderItemDotNumber").val(rs.items[i].dotnumber);
	      $(newRow).find(".orderItemVendor").val(rs.items[i].vendor_id);
	      $(newRow).find(".orderItemInvoiceNumber").val(rs.items[i].invoicenumber);
	      $(newRow).find(".taxPrice").val(rs.items[i].tax);
	      $(newRow).find(".orderItemRetail").val(rs.items[i].retail);
	      $(newRow).find(".orderItemCost").trigger("blur");
	      $(newRow).find(".orderItemRetail.priceChange").trigger("change");
	  }
  }
  
  $(document).on('change blur', '.templateList', function(e){
	  $("#templateListSelected").val("");
	  $("#temaplateAddBtn").addClass("disabled");
	  $("#templateDeleteBtn").addClass("disabled");
  });
  
  $(document).on('click', '#templateDeleteBtn', function(e){
	  e.preventDefault();
	  if(this.classList.contains("disabled")) {return;}
	  $("#deleteTemplateLabel").html($(".templateList").val());
	  $("#deleteTemplateModal").modal("show");
  });
  
  $(document).on("click","#orderItemAddBtn, .remove", function(e){
  	  $(".taxbracket").trigger("change");
	  if($("#workOrderForm .orderItemSerialize").length < 1) {
		  $("#templateCreateBtn").addClass("disabled");
	  } else {
		  $("#templateCreateBtn").removeClass("disabled");
	  }
  });
  
  $(document).on("click", "#templateCreateBtn", function(e){
	  e.preventDefault();
	  if(this.classList.contains("disabled")) {return;}
	  if($("#templateListSelected").val() == "") {
		  $("#newTemplateName").val($(".templateList").val());
	  }
	  $("#newTemplateModal").modal("show");
  });
  
  $(document).on("click", "#templateCreateCloseBtn, #templateCreateCancelBtn", function(e){
	  e.preventDefault();
	  $("#newTemplateName").val("");
	  $('#newTemplateMessageContainer').html("")
  });
  
  function populateNewVehicleMakes(rs) {
	  $("#vehicleAddFormMessageContainer").html("");
	  var make = $("#selectedVehicleMake").val();
	  var selectMake = false;
	  var makeNotFound = true;
	  if(make && make.length > 0) {
		  selectMake = true;
	  }
	  
	  if(rs[0].hasOwnProperty("Message")) {
		  $("#vehicleAddFormMessageContainer").html('<div class="alert alert-danger" role="alert">'+rs[0].Message+'</div>');
	  }
	  
	  rs = sortResults(rs, "MakeName", true);
	  $.each(rs, function(i, m) {
		  if(make && make.length > 0 && make == m.MakeName) {
			  $('#makeDropdown').append('<option value="' + m.MakeName + '" data-id='+m.MakeId+' selected>' + m.MakeName + '</option>');
			  makeNotFound = false;
		  } else {
			  $('#makeDropdown').append('<option value="' + m.MakeName + '" data-id='+m.MakeId+'>' + m.MakeName + '</option>');
		  }
	  });
	  $('#makeDropdown').trigger("change");
	  if(selectMake && makeNotFound) {
		  $("#makeDropdownOverride").val(make);
		  $("#makeDropdownToggle").trigger("click");
	  }
  }
  
  function populateNewVehicleModels(rs,selVal) {
	  var model = $("#selectedVehicleModel").val();
	  var selectModel = false;
	  var modelNotFound = true;
	  if(model && model.length > 0 && selVal == "") {
		  selVal = model;
		  selectModel = true;
	  } else if(typeof selVal != "undefined" && selVal != "") {
		  selectModel = true;
	  }
	  rs = sortResults(rs, "Model_Name", true);
	  $.each(rs, function(i, m) {
		  if(selVal == m.Model_Name) {
			  $('#modelDropdown').append('<option value="' + m.Model_Name + '" selected>' + m.Model_Name + '</option>');
			  modelNotFound = false;
		  } else {
			  $('#modelDropdown').append('<option value="' + m.Model_Name + '">' + m.Model_Name + '</option>');
		  }
	  });
	  
	  if(selectModel && modelNotFound) {
		  $("#modelDropdownOverride").val(selVal);
		  $("#modelDropdownToggle").trigger("click");
	  }
  }
  
  function populateNewVehicleFromVIN(rs) {
	  $('#vehicleAddFormMessageContainer').html("");
	  if(rs.length > 0) {//SAJDA01P61GM09754
		  if(rs[0].Make == "") {
			  $('#vehicleAddFormMessageContainer').html("");
			  $('#vehicleAddFormMessageContainer').html('<div class="alert alert-danger" role="alert">'+rs[0].ErrorCode+'</div>');
		  } else {
			  $('#makeDropdown option').each(function() {
				    if(rs[0].Make.trim().toUpperCase() == this.text.trim().toUpperCase()) {
				    	$('#makeDropdown').val(this.value);
				    }
			  });
	
			  var model = rs[0].Model;
	
			  $('#modelDropdown').empty();
			  var year =  $('#yearDropdown').val();
			  var makeId = $('#makeDropdown option:selected').data('id');
			  $.ajax('https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeIdYear/makeId/'+makeId+'/modelyear/'+year+'?format=json').then(function (rs) {
				  populateNewVehicleModels(rs.Results, model);
			  });
			  var trim = rs[0].Trim;
			  if(rs[0].DisplacementL) {
				  trim += ' ' + rs[0].DisplacementL + 'L';
			  }
			  trim.trim();
			  $('#trimText').val(trim);
		  }
	  } else {
		  $('#vehicleAddFormMessageContainer').html("");
    	  $('#vehicleAddFormMessageContainer').html('<div class="alert alert-danger" role="alert">An error occurred attempting to decode the VIN.</div>');
	  }
	  
  }
  
  function sortResults(arr, prop, asc) {
	  return arr.sort(function(a, b) {
		  if (asc) {
			  return (a[prop].trim().toUpperCase() > b[prop].trim().toUpperCase()) ? 1 : ((a[prop].trim().toUpperCase() < b[prop].trim().toUpperCase()) ? -1 : 0);
		  } else {
			  return (b[prop].trim().toUpperCase() > a[prop].trim().toUpperCase()) ? 1 : ((b[prop].trim().toUpperCase() < a[prop].trim().toUpperCase()) ? -1 : 0);
		  }
	  });
  }
  
  $('body').on('focus', 'input', function (e) {
	    $(this)
	        .one('mouseup', function () {
	            $(this).select();
	            return false;
	        })
	        .select();
	});
  
  $('.input-date').datepicker({
	    format: 'yyyy-mm-dd',
	    autoclose: true
	});
  
})(jQuery); // End of use strict

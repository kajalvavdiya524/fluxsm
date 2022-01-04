function getQueryVariable(variable) {
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	for (var i=0;i<vars.length;i++) {
		var pair = vars[i].split("=");
		if(pair[0] == variable){return pair[1];}
	}
	return false;
}

var orderId = getQueryVariable("orderId");

(function($) {
  "use strict"; // Start of use strict
  
  var workOrderDetails;
  var optCounter = 1;

  
  $.ajax('./api/v1/order/'+orderId+'?store='+utilitiesJS.getQueryVariable("store"), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
	  initEditWorkOrderData(rs);
  });
  
  
  utilitiesJS.setPatternFilter("currentMileage", /^\d*$/);
  
  function reloadPage() {
	  $.ajax('./api/v1/order/'+orderId+'?store='+utilitiesJS.getQueryVariable("store"), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
		  initEditWorkOrderData(rs);
		  $("#orderItemContainer .remove").trigger("click");
		  var newRow = document.createElement("div");
	      var originalRow = document.getElementById("hiddenPartRow");
	      newRow.innerHTML = originalRow.innerHTML;
	      newRow.className = "form-row align-items-center mb-3 mb-0-print orderItemSerialize";
	      $("#orderItemContainer").append(newRow);
	      $(newRow).prop("id", "firstOrderItem");
		  $(window).trigger($.Event('workorderdatacomplete'));
	  });
  }
  
  
  function initEditWorkOrderData(rs) {
	  workOrderDetails = rs[0];
	  populateCustomerDetails(rs[0].customer);
	  populateVehicleDetails(rs[0].vehicle);
	  optCounter = workOrderDetails.optcounter;
	  window.toId = setTimeout(pollForEdits, 10000);
  }
  
  $(window).on('workordersaved', function (e) {
	  optCounter++;
  });
  
  $(window).on('workorderdatacomplete', function (e) {
	  if(workOrderDetails) {
		  populateOrderDetails(workOrderDetails);
		  if(workOrderDetails.type == 'I' || workOrderDetails.type == 'D') {
			  populateInvoiceItemDetails(workOrderDetails.items);
			  $("#workOrderForm .btn").css("display","none");
			  $("#workOrderForm input").prop("readonly",true);
			  $("#workOrderForm select").prop("disabled",true);
			  $("#workOrderForm textarea").prop("disabled",true);
			  $("#workOrderPrintFooter").removeClass("col-print-12");
			  $("#workOrderPrintFooter").addClass("d-print-none");
			  $("#invoicePrintFooter").removeClass("d-print-none");
			  $("#invoicePrintFooter").addClass("col-print-12");
			  $("#ticketTypeCard").hide();
			  $("#printBtn").show();
			  $("#templateSection").css("display","none");
			  unsaved = false;
		  } else {
			  populateItemDetails(workOrderDetails.items);
		  }
		  $(window).trigger($.Event('workorderdetailsdisplayed'));
	  } else {
		  setTimeout(function(){ $(window).trigger($.Event('workorderdatacomplete')); }, 1000);
	  }
  });
  
  function populateOrderDetails(rs) {
  	  if(Cookies.get('fluxur') == 1) {
  		  $('.fluxur1').removeClass('fluxur1');
  	  }
  	  var tm = [];
	  for(var t in rs.teammembers) {
		  tm.push(rs.teammembers[t].teammember_id);
	  }
	  
	  $("#teamMemberDropdown").val(tm);
	  $("#teamMemberDropdown").trigger("change");
	  $("#txtCustomerNotes").val(rs.customernotes);
	  $('#txtCustomerNotes').trigger("input");
	  $("#ticketType").val(rs.type);
	  $("#ticketStatus").val(rs.status);
	  $("#promisedTime").val(rs.promisedtime);
	  $("#startTime").val(rs.starttime);
	  $("#startDate").datepicker('setDate', rs.startdate);
	  $("#duration").val(rs.duration);
	  $('#ticketType').trigger("change");
	  $("#currentMileage").val(rs.mileage);
	  $("#currentMileage").trigger("blur");
	  $("#orderId").html("#"+orderId);
	  if (rs.show_reference_no == 1) {
	  	$("#referenceNo").html("Ref: " + rs.reference_number);
	  }
	  
	  var detail = "Order Number: " + orderId + "<br />";

	  if (rs.show_reference_no == 1) {
	  	detail+= "Ref: " + rs.reference_number + "<br />";
	  }

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
	  detail += "Date: " + rs.updated + "<br />";
	  if(rs.hasOwnProperty("paymethod")) {
		detail += "<br />Payment: " + rs.paymethod;
	  }
	  $("#printDetail").html(detail);
  }
  
  function populateCustomerDetails(rs) {
	  var customerHtml = "";
      var phonetype1 = "";
      var phonetype2 = "";
      var phonetype3 = "";
      var phone1 = "";
      var phone2 = "";
      var phone3 = "";     
          
	  if(rs.hasOwnProperty('businessname') && (rs.businessname != "" && rs.businessname != null)) {
		  customerHtml += rs.businessname + '<br />';
	  } else {
		  customerHtml += rs.contact.firstname + ' ' + rs.contact.lastname + '<br />';
	  }
	  
	  if(rs.hasOwnProperty('addressline1') && (rs.addressline1 != "" && rs.addressline1 != null)) {
		  customerHtml += rs.addressline1 + '<br />';
	  }
	  if(rs.hasOwnProperty('addressline2') && (rs.addressline2 != "" && rs.addressline2 != null)) {
		  customerHtml += rs.addressline2 + '<br />';
	  }
	  if(rs.hasOwnProperty('addressline3') && (rs.addressline3 != "" && rs.addressline3 != null)) {
		  customerHtml += rs.addressline3 + '<br />';
	  }
	  if(rs.hasOwnProperty('city') && rs.city != "" && rs.hasOwnProperty('zip') && rs.zip != "") {
		  customerHtml += rs.city + ', ' + rs.state + ' ' + rs.zip + '<br />';
	  }
	  if(rs.hasOwnProperty('businessname') && (rs.businessname != "" && rs.businessname != null)) {
		  customerHtml += rs.contact.firstname + ' ' + rs.contact.lastname + '<br />';
	  }
          if(rs.contact.phone1type == 'C') {
            phonetype1  =   "Cell";
           
          }
          if(rs.contact.phone1type == 'W') {  
            phonetype1  =   "Work";
          }
          if(rs.contact.phone1type == 'H') {
            phonetype1  =   "Home";
          }
          if(rs.contact.phone2type == 'C') {
            phonetype2  =   "Cell";
          }
          
          if(rs.contact.phone2type == 'W') {  
            phonetype2  =   "Work";
          }
          if(rs.contact.phone2type == 'H') {
            phonetype2  =   "Home";
          }
          if(rs.contact.phone3type == 'C') {
            phonetype3  =   "Cell";
          }
          
          if(rs.contact.phone3type == 'W') {  
            phonetype3  =   "Work";
          }
          if(rs.contact.phone3type == 'H') {
            phonetype3  =   "Home";
          }
          phone1 = rs.contact.phone1;
          phone2 = rs.contact.phone2;
          phone3 = rs.contact.phone3;
          if(typeof(phone1) != "undefined" && phone1 !== null) {
            customerHtml += phonetype1+'  :   ' + phone1 + '<br />';
          }
          if(typeof(phone2) != "undefined" && phone2 !== null) {
            customerHtml += phonetype2+'  :   ' + phone2 + '<br />';
            $("#collapsePhone").show();
          }
	  if(typeof(phone3) != "undefined" && phone3 !== null) {  
            customerHtml += phonetype3+'  :   ' + phone3+ '<br />';
            $("#collapsePhone").show();
          }
         
	  $("#printCustomer").html(customerHtml);
	  $("#customerDetails").html(customerHtml);
	  $("#customerEditBtn").data("contact-id",rs.id);
	  $("#selectedCustomerId").val(rs.contact.id);
	  $("#customerInfoUserType").val(rs.usertype);
	  $("#customerInfoUserType").trigger("change");
	  $("#customerInfoTaxExempt").val(rs.taxexempt);
	  $("#customerInfoTaxExemptNum").val(rs.taxexemptnum);
	  $("#customerInfoBusinessName").val(rs.businessname);
	  $("#selectedCustomerAddress1").val(rs.addressline1);
	  $("#customerInfoAddressLine1").val(rs.addressline1);
	  $("#customerInfoAddressLine2").val(rs.addressline2);
	  $("#customerInfoAddressLine3").val(rs.addressline3);
	  $("#selectedCustomerCity").val(rs.city);
	  $("#customerInfoCity").val(rs.city);
	  $("#customerInfoState").val(rs.state);
	  $("#selectedCustomerZip").val(rs.zip);
	  $("#customerInfoZip").val(rs.zip);
	  $("#customerInfoFirstName").val(rs.contact.firstname);
	  $("#customerInfoLastName").val(rs.contact.lastname);
	  $("#customerInfoPhone1Type").val(rs.contact.phone1type);
	  $("#customerInfoPhone1").val(rs.contact.phone1);
	  $("#customerInfoPhone2Type").val(rs.contact.phone2type);
	  $("#customerInfoPhone2").val(rs.contact.phone2);
	  $("#customerInfoPhone3Type").val(rs.contact.phone3type);
	  $("#customerInfoPhone3").val(rs.contact.phone3);
	  $("#customerInfoEmail").val(rs.contact.email);
	  $("#customerInfoContactId").val(rs.contact.id);
	  $("#customerInfoCustomerId").val(rs.id);
	  
	  if(rs.hasOwnProperty("internal") && rs.internal == "1") {
		  $("#customerInternal").prop("checked",true);
	  } else {
		  $("#customerInternal").prop("checked",false);
	  }

	  if(rs.contact.hasOwnProperty("isprimary") && rs.contact.isprimary == "true") {
		  $("#customerPrimaryContact").prop("checked",true);
	  } else {
		  $("#customerPrimaryContact").prop("checked",false);
	  }
  }
  
  function populateVehicleDetails(rs) {
	  var vehicleHtml = "";
	  if(typeof rs !== "undefined") {
		  if(!rs.hasOwnProperty('vin') || rs.vin == null) { rs.vin = ""; }
		  if(!rs.hasOwnProperty('license') || rs.license == null) { rs.license = ""; }
		  if(!rs.hasOwnProperty('trim') || rs.trim == null) { rs.trim = ""; }
		  if(!rs.hasOwnProperty('mileage') || rs.mileage == null) { rs.mileage = ""; }
		  if(!rs.hasOwnProperty('fleetnum') || rs.fleetnum == null) { rs.fleetnum = ""; }
		  
		  vehicleHtml += 'VIN: ' + rs.vin + '<br />';
		  vehicleHtml += 'Vehicle: ' + rs.year + ' ' + rs.make + ' ' + rs.model + ' ' + rs.trim + '<br />';
		  vehicleHtml += 'License: ' + rs.license + '<br />';
		  vehicleHtml += 'Fleet Number: ' + rs.fleetnum + '<br />';
		  
		  $("#vehicleDetails").html(vehicleHtml);
		  vehicleHtml += 'Odometer: <span id="printCurrentOdometer">' + rs.mileage + '</span>';
		  $("#printVehicle").html(vehicleHtml);
		  $("#selectedVehicleId").val(rs.id);
		  $("#selectedVehicleYear").val(rs.year);
		  $("#selectedVehicleMake").val(rs.make);
		  $("#selectedVehicleModel").val(rs.model);
		  $("#trimText").val(rs.trim);
		  $("#vinText").val(rs.vin);
		  $("#vehicleAddMileage").val(rs.mileage);
		  $("#vehicleAddLicense").val(rs.license);
		  $("#vehicleAddFleetNum").val(rs.fleetnum);
		  $("#orderMileageContainer").show();
		  $("#vehicleInfoBtn").show();
		  $("#vehicleInfoShowHistory").show();
		  $("#vehicleInfoShowHistory").data("vehicle-id",rs.id);
		  $("#vehicleHistory").attr("id","vehicleHistory"+rs.id);
	  } else {
		  vehicleHtml = "No Vehicle";
		  $("#selectedVehicleId").val(-1);
		  $("#orderMileageContainer").hide();
		  var customerId = $('#selectedCustomerId').val();
		  if (customerId) {
		  	  $("#vehicleInfoBtn").text('Add');
		  }
		  $("#vehicleInfoShowHistory").data("vehicle-id","");
		  $("#vehicleHistory").attr("id","vehicleHistory");
		  $("#printVehicle").html(vehicleHtml);
		  $("#vehicleDetails").html(vehicleHtml);
	  }
  }
    
  function populateItemDetails(rs) {
	  $("#stockPartModal").prop("id", "stockPartModalNoShow");
      if(rs.length > 0) {
    	  $("#firstOrderItem .itemtype").val(rs[0].itemtype_id);
    	  $("#firstOrderItem .itemtype").trigger("change");
    	  $("#firstOrderItem .taxbracket").find("[data-category='" + rs[0].taxcat + "']").prop('selected', true); 
    	  $("#firstOrderItem .orderItemPartNumber").val(rs[0].partnumber);
    	  $("#firstOrderItem .orderItemPartNumber").trigger("input");
    	  $("#firstOrderItem .orderItemDescription").val(rs[0].description);
    	  $("#firstOrderItem .orderItemDescription").trigger("input");
    	  $("#firstOrderItem .orderItemQuantity").val(rs[0].quantity);
    	  $("#firstOrderItem .orderItemRetail").val(rs[0].retail);
    	  $("#firstOrderItem .orderItemCost").val(rs[0].cost);
    	  $("#firstOrderItem .orderItemDotNumber").val(rs[0].dotnumber);
    	  $("#firstOrderItem .orderItemVendor").val(rs[0].vendor_id);
    	  $("#firstOrderItem .orderItemInvoiceNumber").val(rs[0].invoicenumber);
    	  $("#firstOrderItem .taxPrice").val(rs[0].tax);

    	  var container = document.getElementById("orderItemContainer");
		  for(var i in rs) {
			  if(i > 0) {
				  var newRow = document.createElement("div");
			      var originalRow = document.getElementById("hiddenPartRow");
			      newRow.innerHTML = originalRow.innerHTML;
			      newRow.className = "form-row align-items-center mb-3 mb-0-print orderItemSerialize";
			      $(container).append(newRow);
			      $(newRow).find(".itemtype").val(rs[i].itemtype_id);
			      $(newRow).find(".itemtype").trigger("change");
			      $(newRow).find(".taxbracket").find("[data-category='" + rs[i].taxcat + "']").prop('selected', true);
			      $(newRow).find(".orderItemPartNumber").val(rs[i].partnumber);
			      $(newRow).find(".orderItemPartNumber").trigger("input");
			      $(newRow).find(".orderItemDescription").val(rs[i].description);
			      $(newRow).find(".orderItemDescription").trigger("input");
			      $(newRow).find(".orderItemQuantity").val(rs[i].quantity);
			      $(newRow).find(".orderItemRetail").val(rs[i].retail);
			      $(newRow).find(".orderItemCost").val(rs[i].cost);
			      $(newRow).find(".orderItemDotNumber").val(rs[i].dotnumber);
			      $(newRow).find(".orderItemVendor").val(rs[i].vendor_id);
			      $(newRow).find(".orderItemInvoiceNumber").val(rs[i].invoicenumber);
			      $(newRow).find(".taxPrice").val(rs[i].tax);
			  }
		  }
      }

      $(".orderItemCost").trigger("blur");
      $(".orderItemDotNumber").trigger("input");
      $(".priceChange").trigger("blur");
      $("#stockPartModalNoShow").prop("id", "stockPartModal");
  }
  
  function populateInvoiceItemDetails(rs) {
	  $("#stockPartModal").prop("id", "stockPartModalNoShow");
      if(rs.length > 0) {
    	  $("#firstOrderItem .itemtype").val(rs[0].itemtype_id);
    	  $("#firstOrderItem .itemtype").trigger("change");
    	  $("#firstOrderItem .taxbracket").find("[data-category='" + rs[0].taxcat + "']").prop('selected', true); 
    	  $("#firstOrderItem .orderItemPartNumber").val(rs[0].partnumber);
    	  $("#firstOrderItem .orderItemPartNumber").trigger("input");
    	  $("#firstOrderItem .orderItemDescription").val(rs[0].description);
    	  $("#firstOrderItem .orderItemDescription").trigger("input");
    	  $("#firstOrderItem .orderItemQuantity").val(rs[0].quantity);
    	  $("#firstOrderItem .orderItemRetail").val(rs[0].retail);
    	  $("#firstOrderItem .orderItemCost").val(rs[0].cost);
    	  $("#firstOrderItem .orderItemDotNumber").val(rs[0].dotnumber);
    	  $("#firstOrderItem .orderItemVendor").val(rs[0].vendor_id);
    	  $("#firstOrderItem .orderItemInvoiceNumber").val(rs[0].invoicenumber);
    	  $("#firstOrderItem .taxPrice").val(rs[0].tax);

    	  var container = document.getElementById("orderItemContainer");
		  for(var i in rs) {
			  if(i > 0) {
				  var newRow = document.createElement("div");
			      var originalRow = document.getElementById("hiddenPartRow");
			      newRow.innerHTML = originalRow.innerHTML;
			      newRow.className = "form-row align-items-center mb-3 mb-0-print orderItemSerialize";
			      $(container).append(newRow);
			      $(newRow).find(".itemtype").val(rs[i].itemtype_id);
			      $(newRow).find(".itemtype").trigger("change");
			      $(newRow).find(".taxbracket").find("[data-category='" + rs[i].taxcat + "']").prop('selected', true);
			      $(newRow).find(".orderItemPartNumber").val(rs[i].partnumber);
			      $(newRow).find(".orderItemPartNumber").trigger("input");
			      $(newRow).find(".orderItemDescription").val(rs[i].description);
			      $(newRow).find(".orderItemDescription").trigger("input");
			      $(newRow).find(".orderItemQuantity").val(rs[i].quantity);
			      $(newRow).find(".orderItemRetail").val(rs[i].retail);
			      $(newRow).find(".orderItemCost").val(rs[i].cost);
			      $(newRow).find(".orderItemDotNumber").val(rs[i].dotnumber);
			      $(newRow).find(".orderItemVendor").val(rs[i].vendor_id);
			      $(newRow).find(".orderItemInvoiceNumber").val(rs[i].invoicenumber);
			      $(newRow).find(".taxPrice").val(rs[i].tax);
			  }
		  }
		  
		  $(".orderItemCost").trigger("blur");
		  $(".orderItemDotNumber").trigger("input");
		  $.each($("#workOrderForm" +  " ." + "orderItemSerialize"), function() {
			  var row =  $(this);
			  var qty = parseFloat(row.find(".orderItemQuantity").first().val());
			  var retail = parseFloat(row.find(".orderItemRetail").first().val());
			  if(isNaN(retail)) { retail = 0.0;}
			  if(isNaN(qty)) {
				  qty = 1;
				  row.find(".orderItemQuantity").first().val(1);
			  }
			  row.find(".orderItemRetail").first().val(retail.toFixed(2));
			  row.find(".totalPrice").first().val("$"+(qty * retail).toFixed(2));
			  
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
			        	if($(this).closest(".form-row").find(".taxPrice").val() > 0) { firstDiscountContainer = true;}
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

      }
      $("#stockPartModalNoShow").prop("id", "stockPartModal");
  }
  
  $("#addPaymentBtn").on('click',function(event) {
	  event.preventDefault();
      event.stopPropagation();
      var container = document.getElementById("paymentContainer");
      var newRow = document.createElement("div");
      var originalRow = document.getElementById("hiddenPaymentRow");
      newRow.innerHTML = originalRow.innerHTML;
      newRow.className = "form-row mb-3";
      $(container).append(newRow);
      
      var total = Number($("#grandTotal").val().replace(/[^0-9-.]/g, ''));
      var totalPaid = 0;
      $('.paymentAmount').each(function() {
    	  var curPrice = Number($(this).val().replace(/[^0-9-.]/g, ''));
    	  totalPaid += curPrice;
	  });
      $(newRow).find(".paymentAmount").val((total-totalPaid).toFixed(2));
  });
  
  $("#printBtn").on('click',function(event) {
	  event.preventDefault();
      event.stopPropagation();
      window.print();
  });
  
  $(document).on("blur","#currentMileage",function(event) {
	  $("#printCurrentOdometer").html($(this).val());	  
  });
  
  $(document).on("blur",".paymentAmount",function(event) {
	  if(isNaN(parseFloat($(this).val()))) { $(this).val(0); }
	  $(this).val(parseFloat($(this).val()).toFixed(2));	  
  });
  
  $(document).on("blur",".orderItemCost, .priceChange",function(event) {
	  var total = Number($("#grandTotal").val().replace(/[^0-9-.]/g, ''));
	  total = total -  Number($("#taxTotal").val().replace(/[^0-9-.]/g, ''));
      var cost = 0;
      $(".totalItemCost").each(function() {
    	  var curPrice = Number($(this).val().replace(/[^0-9-.]/g, ''));
    	  cost += curPrice;
	  });
      var p = total-cost;
      var m = ((p/total) * 100).toFixed(2);
      $("#orderMarginAmount").val(m);
      if(m < 0) {
    	  $("#workOrderCloseBtn").removeClass("btn-success");
    	  $("#workOrderCloseBtn").removeClass("btn-warning");
    	  $("#workOrderCloseBtn").addClass("btn-danger");
      } else if( m < 30) {
    	  $("#workOrderCloseBtn").removeClass("btn-success");
    	  $("#workOrderCloseBtn").removeClass("btn-danger");
    	  $("#workOrderCloseBtn").addClass("btn-warning");    	  
      } else {
    	  $("#workOrderCloseBtn").removeClass("btn-danger");
    	  $("#workOrderCloseBtn").removeClass("btn-warning");
    	  $("#workOrderCloseBtn").addClass("btn-success");    	  
      }
  });

  $("#invoiceBtn").on('click',function(event) {
	  $(".orderItemCost").trigger("blur");
      if($('#paymentMethodDropdown > option').length < 1) {
		  $.ajax('./api/v1/store/paymentmethods?store='+utilitiesJS.getQueryVariable("store"), {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			  populatePaymentMethods(rs);
		  });
	  }
      if($("#firstPaymentAmmount").val() == "") {
    	  $("#firstPaymentAmmount").val($("#grandTotal").val().replace(/[^0-9-.]/g, ''));
      }
  });
  
  $(document).on("change",".paymentMethod",function(event) {
	  if($(this).find("option:selected").data("type") == 2) {
		  $(this).closest(".form-row").find(".paymentCheckContainer").show();
	  } else {
		  $(this).closest(".form-row").find(".paymentCheckContainer").hide();
	  }
  });
  
  $("#reloadBtn").on('click',function(event) {
	  reloadPage();
	  $("#refreshRequiredModal").modal("hide");
  });
    
  function populatePaymentMethods(rs) {
	  $('#paymentMethodDropdown').append('<option value="">Select Payment Method</option>');
	  $('#hiddenPaymentRow .paymentMethod').append('<option value="">Select Payment Method</option>');
	  $.each(rs.paymentmethods, function(i, m) {
		  var selected = "";
		  if(m.default == 1) { selected = " selected"; }
		  $('#paymentMethodDropdown').append('<option data-type="'+m.paymenttype_id+'" value="' + m.id + '" '+ selected +'>' + m.name + '</option>');
		  $('#hiddenPaymentRow .paymentMethod').append('<option data-type="'+m.paymenttype_id+'" value="' + m.id + '" '+ selected +'>' + m.name + '</option>');
	  });
  }
  
  function pollForEdits() {
	  if(($("#loginModal").data('bs.modal') || {})._isShown) {
		  window.toId = setTimeout(pollForEdits, 10000);
	  } else {
		  $.ajax('./api/v1/order/revision/'+orderId+'?store='+utilitiesJS.getQueryVariable("store"), {cache: false, type: "GET", headers:{"Authorization":"Bearer " + Cookies.get('token')}}).always(function (request, textStatus) {
			  if(request && request.hasOwnProperty("rev") && request.rev != optCounter) {
				  $("#refreshRequiredModal").modal("show");
			  } else {
				  window.toId = setTimeout(pollForEdits, 10000);
			  }
		  });
	  }
  }

  $("#deleteOrderBtn").on('click',function(event) {
	event.preventDefault();
	event.stopPropagation();
	var result = confirm("Are you sure you want to delete?");	
	if (result) {
		$.ajax('./api/v1/order/orderDelete/'+orderId+'?store='+utilitiesJS.getQueryVariable("store"), {cache: false, type: 'PUT', headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {	
			window.location = "index.php";
		});	
	}	
  });

})(jQuery); // End of use strict

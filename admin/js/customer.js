var customerJS = {
		init: function() {
			$(window).on('storelistloaded', function (e) {
				$("#storeDropdown").val(utilitiesJS.getQueryVariable("store"));
			});
			
			if(utilitiesJS.getQueryVariable("invoiceId")) {
				$.ajax('./api/v1/customer/detail/?invoiceId=' + utilitiesJS.getQueryVariable("invoiceId") + '&store=' + utilitiesJS.getQueryVariable("store"), {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
					customerJS.initDisplayDetails(rs);
				});
			} else {
				$.ajax('./api/v1/customer/checkcache/' + utilitiesJS.getQueryVariable("id") + '?store='+ utilitiesJS.getQueryVariable("store"), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (cached_version) {
					$.ajax('./api/v1/customer/detail/' + utilitiesJS.getQueryVariable("id") + '?store=' + utilitiesJS.getQueryVariable("store") + '&_=' + cached_version, {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
						customerJS.initDisplayDetails(rs);
					});
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
			
			$("#addContactBtn").on('click', function(event) {
				$("#contactInfoModalTitle").html("Add New Contact");
				$("#contactInfoId").val("");
				$("#contactInfoFirstName").val("");
				$("#contactInfoLastName").val("");
				$("#contactInfoPhone1Type")[0].selectedIndex = 0;
				$("#contactInfoPhone1").val("");
				$("#contactInfoPhone2Type")[0].selectedIndex = 0;
				$("#contactInfoPhone2").val("");
				$("#contactInfoPhone3Type")[0].selectedIndex = 0;
				$("#contactInfoPhone3").val("");
				$("#contactInfoEmail").val("");
				$("#contactInfoIsPrimary").prop("checked", false);
				$("#contactInfoModal").modal("show");
				event.preventDefault();
		        event.stopPropagation();
			});
			
			$(document).on('click', ".editContactLink", function(event) {
				var cid = $(this).data("contact-id");
				$("#contactInfoId").val(cid);
				$("#contactInfoModalTitle").html("Edit Contact");
				$("#contactInfoFirstName").val($("#contactFName"+cid).val());
				$("#contactInfoLastName").val($("#contactLName"+cid).val());
				$("#contactInfoPhone1Type").val($("#contactPhone1Type"+cid).val());
				$("#contactInfoPhone1").val($("#contactPhone1"+cid).val());
				$("#contactInfoPhone1").val($("#contactInfoPhone1").val().replace(/[^0-9]/g, ''));
				if($("#contactPhone2Type"+cid).val()) {
					$("#contactInfoPhone2Type").val($("#contactPhone2Type"+cid).val());
				}
				$("#contactInfoPhone2").val($("#contactPhone2"+cid).val());
				$("#contactInfoPhone2").val($("#contactInfoPhone2").val().replace(/[^0-9]/g, ''));
				if($("#contactPhone3Type"+cid).val()) {
					$("#contactInfoPhone3Type").val($("#contactPhone3Type"+cid).val());
				}
				$("#contactInfoPhone3").val($("#contactPhone3"+cid).val());
				$("#contactInfoPhone3").val($("#contactInfoPhone3").val().replace(/[^0-9]/g, ''));
				$("#contactInfoEmail").val($("#contactEmail"+cid).val());
				if($("#contactIsPrimary"+cid).val() == "true") {
					$("#contactInfoIsPrimary").prop("checked",true);
				} else {
					$("#contactInfoIsPrimary").prop("checked",false);
				}
				$("#contactInfoModal").modal("show");
				event.preventDefault();
		        event.stopPropagation();
			});
			
			$("#addVehicleBtn").on('click', function(event) {
				$("#selectedVehicleModel").val("");
				$("#vehicleInfoId").val("");
				if($("#yearDropdown").css("display") != "block") {
					$("#yearDropdownToggle").trigger("click");
				}
				if($("#makeDropdown").css("display") != "block") {
					$("#makeDropdownToggle").trigger("click");
				}
				if($("#modelDropdown").css("display") != "block") {
					$("#modelDropdownToggle").trigger("click");
				}
				customerJS.populateVehicleDropDowns("","");
				$("#vehicleInfoModal").modal("show");
				$("#vehicleInfoModalTitle").html("Add New Vehicle");
				$("#yearDropdown")[0].selectedIndex = 0;
				$("#makeDropdown")[0].selectedIndex = 0;
				$("#makeDropdown").trigger("change");
				$("#trimText").val("");
				$("#vinText").val("");
				$("#yearDropdownOverride").val("");
				$("#makeDropdownOverride").val("");
				$("#modelDropdownOverride").val("");
				$("#vehicleAddMileage").val("");
				$("#vehicleAddLicense").val("");
				$("#vehicleAddFleetNum").val("")
				$("#vehicleAddActive").prop("checked",true);;
				event.preventDefault();
		        event.stopPropagation();
			});
			
			$(document).on('click', ".editVehicleLink", function(event) {
				if($("#yearDropdown").css("display") != "block") {
					$("#yearDropdownToggle").trigger("click");
				}
				if($("#makeDropdown").css("display") != "block") {
					$("#makeDropdownToggle").trigger("click");
				}
				if($("#modelDropdown").css("display") != "block") {
					$("#modelDropdownToggle").trigger("click");
				}
				$("#yearDropdownOverride").val("");
				$("#makeDropdownOverride").val("");
				$("#modelDropdownOverride").val("");
				var vid = $(this).data("vehicle-id");
				$("#vehicleInfoId").val(vid);
				$("#selectedVehicleModel").val($("#vehicleModel"+vid).val())
				customerJS.populateVehicleDropDowns($("#vehicleYear"+vid).val(),$("#vehicleMake"+vid).val());
				$("#trimText").val($("#vehicleTrim"+vid).val());
				$("#vinText").val($("#vehicleVin"+vid).val());
				$("#vehicleAddMileage").val($("#vehicleMileage"+vid).val());
				$("#vehicleAddLicense").val($("#vehicleLicense"+vid).val());

                                var vehicleFleetValue = '';
                                if ($("#vehicleFleet"+vid).length > 0) {
                                    if ($("#vehicleFleet"+vid).val() != 'undefined') {
                                        vehicleFleetValue = $("#vehicleFleet"+vid).val();
                                    }
                                }
				$("#vehicleAddFleetNum").val(vehicleFleetValue);

				if($("#vehicleActive"+vid).val() == 1) {
					$("#vehicleAddActive").prop("checked",true);
				} else {
					$("#vehicleAddActive").prop("checked",false);
				}
				$("#vehicleInfoModalTitle").html("Edit Vehicle");
				$("#vehicleInfoModal").modal("show");
				event.preventDefault();
		        event.stopPropagation();
			});
			
			$(document).on('click', '#customerInfoSaveBtn', function(event) {
				var form = document.getElementById("customerInfoForm");
				if (form.checkValidity() === false) {
					event.preventDefault();
			        event.stopPropagation();
			        form.classList.add('was-validated');
			        return;
				}
				  
				var formData = utilitiesJS.serializeObject("customerInfoForm","customerSerialize");
				var isInteral = $("#customerInternal").prop('checked');

				if(isInteral) {
					formData["internal"] = 1;
				}
				var submitType = "PUT";
				formData.customer_id = utilitiesJS.getQueryVariable("id");
				  
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
							$("#editCustomerModal").modal("hide");
							customerJS.displayCustomerDetails(formData);
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
				});
				event.preventDefault();
			});
			
			$(document).on('change', '#makeDropdown, #yearDropdown', function(e){
				if($('#makeDropdown').val() != "") {
					$('#modelDropdown').empty();
					var year =  $('#yearDropdown').val();
					var makeId = $('#makeDropdown option:selected').data('id');
					if(year && makeId) {
						$.ajax('https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeIdYear/makeId/'+makeId+'/modelyear/'+year+'?format=json').then(function (rs) {
							customerJS.populateNewVehicleModels(rs.Results,'');
						});
					} else {
						customerJS.populateNewVehicleModels([],"");
					}
				}
			});
			  
			$(document).on('click', '#vinDecode', function(e){
				if($('#yearDropdown').val() != "" && $('#vinText').val() != "") {
					var vin = $('#vinText').val();
					var year =  $('#yearDropdown').val();
					$.ajax('https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvaluesextended/'+vin+'?format=json&modelyear='+year).then(function (rs) {
						customerJS.populateNewVehicleFromVIN(rs.Results);
					});
				}
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
			
			$(document).on('click', '#vehicleInfoSaveBtn', function(event) {
				var form = document.getElementById("vehicleInfoForm");
				  
				if (form.checkValidity() === false) {
					event.preventDefault();
			        event.stopPropagation();
			        form.classList.add('was-validated');
			        return;
				}

				var formData = utilitiesJS.serializeObject("vehicleInfoForm","vehicleSerialize");
				formData.customer_id = utilitiesJS.getQueryVariable("id").replace('C', '');
				formData.store = utilitiesJS.getQueryVariable("store");
				
				if($("#vehicleAddActive").prop("checked")) {
					formData['active'] = 1;
				} else {
					formData['active'] = 0;
				}
				
				var submitType = "POST";

				if($("#vehicleInfoId").val() != '') {
					submitType = "PUT";
					formData.id = $("#vehicleInfoId").val();
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
							if(submitType === "POST") {
								formData.id = result.id;
								if(!formData.hasOwnProperty('vin')) { formData.vin = ""; }
								if(!formData.hasOwnProperty('license')) { formData.license = ""; }
								if(!formData.hasOwnProperty('trim')) { formData.trim = ""; }
								if(!formData.hasOwnProperty('mileage')) { formData.mileage = ""; }
								if(!formData.hasOwnProperty('fleetnum')) { formData.fleetnum = ""; }
								var newVehicle = "";
								newVehicle += '		<tr><td>'+formData.year+'</td><td>'+formData.make+'</td><td>'+formData.model+'</td><td>'+formData.license+'</td><td><a class="font-weight-bold" href="#historyCollapse'+formData.id+'" data-toggle="collapse">History</a></td><td><a class="editVehicleLink font-weight-bold" href="#" data-vehicle-id="'+formData.id+'">Edit</a>';
								newVehicle += '<input type="hidden" id="vehicleYear'+formData.id+'" value="'+formData.year+'" />';
								newVehicle += '<input type="hidden" id="vehicleMake'+formData.id+'" value="'+formData.make+'" />';
								newVehicle += '<input type="hidden" id="vehicleModel'+formData.id+'" value="'+formData.model+'" />';
								newVehicle += '<input type="hidden" id="vehicleTrim'+formData.id+'" value="'+formData.trim+'" />';
								newVehicle += '<input type="hidden" id="vehicleVin'+formData.id+'" value="'+formData.vin+'" />';
								newVehicle += '<input type="hidden" id="vehicleMileage'+formData.id+'" value="'+formData.mileage+'" />';
								newVehicle += '<input type="hidden" id="vehicleLicense'+formData.id+'" value="'+formData.license+'" />';
								newVehicle += '<input type="hidden" id="vehicleFleet'+formData.id+'" value="'+formData.fleetnum.replace(/^\s+|\s+$/g, "")+'" />';
								newVehicle += '<input type="hidden" id="vehicleActive'+formData.id+'" value="'+formData.active+'" />';
								newVehicle += '</td></tr>';
								$("#vehicleTableBody").prepend(newVehicle);
								$('#vehicleInfoModal').modal("hide");
							} else {
								$.ajax('./api/v1/customer/detail/' + utilitiesJS.getQueryVariable("id") + '?store=' + utilitiesJS.getQueryVariable("store"), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
									customerJS.displayVehicles(rs);
								});
								$("#vehicleInfoModal").modal("hide");
							}
			            	  
						} else {
							$('#vehicleAddFormMessageContainer').html("");
							$('#vehicleAddFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving New Vehicle<br />'+JSON.stringify(result)+'</div>');
						}
						$("#vehicleInfoSaveBtn").removeClass("disabled");
					},
					error: function(xhr, resp, text) {
						$('#vehicleAddFormMessageContainer').html("");
						$('#vehicleAddFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving New Vehicle<br />'+text+'</div>');
						$("#vehicleInfoSaveBtn").removeClass("disabled");
					}
				})
				event.preventDefault();
			});
			
			  $(document).on('click', '#contactInfoSaveBtn', function(event) {
				  var form = document.getElementById("contactInfoForm");
				  if (form.checkValidity() === false) {
			        event.preventDefault();
			        event.stopPropagation();
			        form.classList.add('was-validated');
			        return;
			      }
			      var isPrimary = $("#contactInfoIsPrimary").prop('checked');
				  
				  var formData = utilitiesJS.serializeObject("contactInfoForm","contactSerialize");

				  if(isPrimary) {
					  formData["isPrimary"] = 1;
				  }

				  var submitType = "POST";
				  formData.customer_id = utilitiesJS.getQueryVariable("id").replace('C', '');
				  formData.store = utilitiesJS.getQueryVariable("store");
				  
				  if($("#contactInfoId").val() != '') {
					  submitType = "PUT";
					  formData.contact_id = $("#contactInfoId").val();
					  
				  }

				  this.classList.add("disabled");
				  $.ajax({
			          url: './api/v1/contact/', // url where to submit the request
			          type : submitType, // type of action POST || GET
			          dataType : 'json', // data type
			          data : JSON.stringify(formData), // post data || get data
			          contentType: "application/json",
			          headers:{"Authorization":"Bearer " + Cookies.get('token')},
			          success : function(result) {
			              if(result.id) {
			            	  formData.id = result.id;
			            	  if(submitType === "POST") {
			            		  var newContact = "";
			            		  if(formData.phone2type == null) {formData.phone2type = "C";}
			            		  if(formData.phone2 == null) {formData.phone2 = "";}
			            		  if(formData.phone3type == null) {formData.phone3type = "C";}
			            		  if(formData.phone3 == null) {formData.phone3 = "";}
			            		  if(formData.email == null) {formData.email = "";}
			            		  if(formData.isPrimary == 1) {formData.isPrimary = "true";}
			            		  newContact += '		<tr><td>'+formData.firstname+'</td><td>'+formData.lastname+'</td><td>'+formData.phone1+'</td><td><a href="#" class="editContactLink font-weight-bold" data-contact-id="'+formData.id+'">Edit</a>';
			            		  newContact += '<input type="hidden" id="contactFName'+formData.id+'" value="'+formData.firstname+'" />';
			            		  newContact += '<input type="hidden" id="contactLName'+formData.id+'" value="'+formData.lastname+'" />';
			            		  newContact += '<input type="hidden" id="contactPhone1Type'+formData.id+'" value="'+formData.phone1type+'" />';
			            		  newContact += '<input type="hidden" id="contactPhone1'+formData.id+'" value="'+formData.phone1+'" />';
			            		  newContact += '<input type="hidden" id="contactPhone2Type'+formData.id+'" value="'+formData.phone2type+'" />';
			            		  newContact += '<input type="hidden" id="contactPhone2'+formData.id+'" value="'+formData.phone2+'" />';
			            		  newContact += '<input type="hidden" id="contactPhone3Type'+formData.id+'" value="'+formData.phone3type+'" />';
			            		  newContact += '<input type="hidden" id="contactPhone3'+formData.id+'" value="'+formData.phone3+'" />';
			            		  newContact += '<input type="hidden" id="contactEmail'+formData.id+'" value="'+formData.email+'" />';
			            		  newContact += '<input type="hidden" id="contactIsPrimary'+formData.id+'" value="'+formData.isPrimary+'" />';
			            		  newContact += '</td></tr>';
			            		  $("#contactTableBody").prepend(newContact);
			            		  $('#contactInfoModal').modal("hide");
			            	  } else {
			            		  $.ajax('./api/v1/customer/detail/' + utilitiesJS.getQueryVariable("id") + '?store=' + utilitiesJS.getQueryVariable("store"), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			            			  customerJS.displayContacts(rs.contacts);
			            		  });
			            		  $("#contactInfoModal").modal("hide");
			            	  }
			            	  
			              } else {
			            	  $('#contactInfoFormMessageContainer').html("");
			            	  $('#contactInfoFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Contact<br />'+JSON.stringify(result)+'</div>');
			              }
			              $("#contactInfoSaveBtn").removeClass("disabled");
			          },
			          error: function(xhr, resp, text) {
			        	  $('#contactInfoFormMessageContainer').html("");
			        	  $('#contactInfoFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Contact<br />'+text+'</div>');
			        	  $("#contactInfoSaveBtn").removeClass("disabled");
			          }
			      })
				  event.preventDefault();
			  });
			  
			  utilitiesJS.setPatternFilter("contactInfoPhone1", /^\d*$/);
			  utilitiesJS.setPatternFilter("contactInfoPhone2", /^\d*$/);
			  utilitiesJS.setPatternFilter("contactInfoPhone3", /^\d*$/);

		},
		
		initDisplayDetails: function(rs) {
			this.displayCustomerDetails(rs.customer);
			this.displayContacts(rs.contacts);
			this.displayVehicles(rs);
		},
		
		displayCustomerDetails: function(rs) {
			$("#customerInfoUserType").val(rs.usertype);
			if(rs.usertype == "B") {
				$("#usertype").html("Business Account");
				$("#businessname").html(rs.businessname);
				$("#businessNameContainer").show();
				$("#customerInfoBusinessName").val(rs.businessname);
			} else {
				$("#usertype").html("Personal Account");
				$("#businessname").hide();
			}
			
			$("#customerInfoTaxExempt").val(rs.taxexempt);
			$("#customerInfoTaxExemptNum").val(rs.taxexemptnum);
			if(rs.taxexempt == "0") {
				$("#taxexemption").html("Not Tax Exempt");
				$("#taxid").hide();
			} else {
				$("#taxexemption").html("Tax Exempt");
				$("#taxid").html(rs.taxexemptnum);
			}

			$("#customerInfoAddressLine1").val(rs.addressline1);
			$("#address1").html(rs.addressline1);
			if(rs.hasOwnProperty("addressline2") && rs.addressline2 != "") {
				$("#address2").html(rs.addressline2);
				$("#customerInfoAddressLine2").val(rs.addressline2);
			} else {
				$("#address2").hide();
			}
			
			if(rs.hasOwnProperty("addressline3") && rs.addressline3 != "") {
				$("#address3").html(rs.addressline3);
				$("#customerInfoAddressLine3").val(rs.addressline3);
			} else {
				$("#address3").hide();
			}

			$("#customerInfoCity").val(rs.city);
			if(rs.state.length == 2) {
				$("#customerInfoState").val(rs.state);
			}
			$("#customerInfoZip").val(rs.zip);
			$("#citystatezip").html(rs.city + ", " + rs.state + " " + rs.zip);
			
			if(rs.hasOwnProperty("internal") && rs.internal == "1") {
				$("#customerInternal").prop("checked",true);
			} else {
				$("#customerInternal").prop("checked",false);
			}
		},
		
		displayContacts: function(rs) {
			var contacts = "";
			$.each(rs, function(i, c) {
				if(c.phone2type == null) {c.phone2type = "";}
				if(c.phone2 == null) {c.phone2 = "";}
				if(c.phone3type == null) {c.phone3type = "";}
				if(c.phone3 == null) {c.phone3 = "";}
				if(c.email == null) {c.email = "";}
				contacts += '		<tr><td>'+c.firstname+'</td><td>'+c.lastname+'</td><td>'+c.phone1+'</td><td><a href="#" class="editContactLink font-weight-bold" data-contact-id="'+c.id+'">Edit</a>';
				contacts += '<input type="hidden" id="contactFName'+c.id+'" value="'+c.firstname+'" />';
				contacts += '<input type="hidden" id="contactLName'+c.id+'" value="'+c.lastname+'" />';
				contacts += '<input type="hidden" id="contactPhone1Type'+c.id+'" value="'+c.phone1type+'" />';
				contacts += '<input type="hidden" id="contactPhone1'+c.id+'" value="'+c.phone1+'" />';
				contacts += '<input type="hidden" id="contactPhone2Type'+c.id+'" value="'+c.phone2type+'" />';
				contacts += '<input type="hidden" id="contactPhone2'+c.id+'" value="'+c.phone2+'" />';
				contacts += '<input type="hidden" id="contactPhone3Type'+c.id+'" value="'+c.phone3type+'" />';
				contacts += '<input type="hidden" id="contactPhone3'+c.id+'" value="'+c.phone3+'" />';
				contacts += '<input type="hidden" id="contactEmail'+c.id+'" value="'+c.email+'" />';
				contacts += '<input type="hidden" id="contactIsPrimary'+c.id+'" value="'+c.isprimary+'" />';
				contacts += '</td></tr>';
			});
			
			$("#contactTableBody").html(contacts);
			
			if(rs.length > 6) {
				$("#contactsDisplay").addClass("contactContainer");
			}
		},
		
		displayVehicles: function(rs) {
			var vehicles = "";
			var selectedInvoiceVehicleId = "";
			
			$.each(rs.vehicles, function(i, v) {
				if(!v.hasOwnProperty("mileage") || v.mileage == null) {v.mileage = "";}
				if(!v.hasOwnProperty("license") || v.license == null) {v.license = "";}
				if(!v.hasOwnProperty("trim") || v.trim == null) {v.trim = "";}
				if(!v.hasOwnProperty("vin") || v.vin == null) {v.vin = "";}
				if(!v.hasOwnProperty("fleetnum") || v.fleetnum == null) {v.fleetnum = "";}
				var openOrders = "";
				var invoices = "";
				vehicles += '		<tr><td>'+v.year+'</td><td>'+v.make+'</td><td>'+v.model+'</td><td>'+v.license+'</td><td><a class="font-weight-bold" href="#historyCollapse'+v.id+'" data-toggle="collapse">History</a></td><td><a class="editVehicleLink font-weight-bold" href="#" data-vehicle-id="'+v.id+'">Edit</a>';
				vehicles += '<input type="hidden" id="vehicleYear'+v.id+'" value="'+v.year+'" />';
				vehicles += '<input type="hidden" id="vehicleMake'+v.id+'" value="'+v.make+'" />';
				vehicles += '<input type="hidden" id="vehicleModel'+v.id+'" value="'+v.model+'" />';
				vehicles += '<input type="hidden" id="vehicleTrim'+v.id+'" value="'+v.trim+'" />';
				vehicles += '<input type="hidden" id="vehicleVin'+v.id+'" value="'+v.vin+'" />';
				vehicles += '<input type="hidden" id="vehicleMileage'+v.id+'" value="'+v.mileage+'" />';
				vehicles += '<input type="hidden" id="vehicleLicense'+v.id+'" value="'+v.license+'" />';
				vehicles += '<input type="hidden" id="vehicleFleet'+v.id+'" value="'+v.fleetnum+'" />';
				vehicles += '<input type="hidden" id="vehicleActive'+v.id+'" value="'+v.active+'" />';
				vehicles += '</td></tr>';
				
				if(rs.orders.hasOwnProperty(v.id)) {
					vehicles += '<tr class="collapse" id="historyCollapse'+v.id+'"><td colspan="4">';
					$.each(rs['orders'][v.id], function(i, o) {
						if(utilitiesJS.getQueryVariable("invoiceId") == o.id) {
							selectedInvoiceVehicleId = v.id;
						}
						if(o.type == 'I') {
							if(o.technician == null) { o.technician = "";}
							invoices += '<tr><td>' + o.orderdate + '</td><td><a class="font-weight-bold" href="workorderedit.php?orderId=' + o.orderid + '&store='+utilitiesJS.getQueryVariable("store")+'">'+o.orderid+'</td><td>' + (Number(o.ordertotal)).toFixed(2) + '</td><td>' + o.technician + '</td></tr>';
						} else if(o.type == 'W') {
							if(o.technician == null) { o.technician = "";}
							openOrders += '<tr><td>' + o.orderdate + '</td><td>' + o.id + '</td><td>' + (Number(o.ordertotal)).toFixed(2) + '</td><td>' + o.technician + '</td></tr>';
						}
					});
						
					if(openOrders != "") {
						vehicles += '<Strong>In Progress</Strong><table class="table">';
						vehicles += '<tr><th>Date</th><th>Number</th><th>Total</th><th>Technician</th></tr>';
						vehicles += openOrders + '</table>';
					}
					
					if(invoices != "") {
						vehicles += '<Strong>Completed</Strong><table class="table">';
						vehicles += '<tr><th>Date</th><th>Number</th><th>Total</th><th>Technician</th></tr>';
						vehicles += invoices + '</table>';
					}
					vehicles += '</td></tr>';
				}
			});
			
			if(rs.orders.hasOwnProperty("")) {
				var openOrders = "";
				var invoices = "";
				vehicles += '		<tr><td colspan="4">No Vehicle</td><td><a class="font-weight-bold" href="#historyCollapse" data-toggle="collapse">History</a></td><td></td></tr>';
				
				vehicles += '<tr class="collapse" id="historyCollapse"><td colspan="4">';
				$.each(rs['orders'][''], function(i, o) {
					if(utilitiesJS.getQueryVariable("invoiceId") == o.id) {
						selectedInvoiceVehicleId = "";
					}
					if(o.type == 'I') {
						if(o.technician == null) { o.technician = "";}
						invoices += '<tr><td>' + o.orderdate + '</td><td><a class="font-weight-bold" href="workorderedit.php?orderId=' + o.id + '&store='+utilitiesJS.getQueryVariable("store") + '">'+o.id+'</td><td>' + (Number(o.ordertotal)).toFixed(2) + '</td><td>' + o.technician + '</td></tr>';
					} else if(o.type == 'W') {
						if(o.technician == null) { o.technician = "";}
						openOrders += '<tr><td>' + o.orderdate + '</td><td>' + o.id + '</td><td>' + (Number(o.ordertotal)).toFixed(2) + '</td><td>' + o.technician + '</td></tr>';
					}
				});
						
				if(openOrders != "") {
					vehicles += '<Strong>In Progress</Strong><table class="table">';
					vehicles += '<tr><th>Date</th><th>Number</th><th>Total</th><th>Technician</th></tr>';
					vehicles += openOrders + '</table>';
				}
					
				if(invoices != "") {
					vehicles += '<Strong>Completed</Strong><table class="table">';
					vehicles += '<tr><th>Date</th><th>Number</th><th>Total</th><th>Technician</th></tr>';
					vehicles += invoices + '</table>';
				}
				vehicles += '</td></tr>';
			}
			
			$("#vehicleTableBody").html(vehicles);
			if(selectedInvoiceVehicleId != "") {
				$("#historyCollapse"+selectedInvoiceVehicleId).collapse("toggle");
			}
		},
		
		populateVehicleDropDowns: function(year,make) {
			var dropdownsNeedPopulated = true;
			if($('#makeDropdown > option').length < 1) {
				$.ajax('https://vpic.nhtsa.dot.gov/api/vehicles/GetMakesForVehicleType/MPV?format=json').then(function (rs) {
					customerJS.populateNewVehicleMakes(rs.Results,make);
				});
				var d = new Date();
				var newest = d.getFullYear();
				if(d.getMonth() > 8) { newest++; }
				for (var i = newest; i > 1940; i--) {
					$('#yearDropdown').append($('<option />').val(i).html(i));
				}
			} else {
				dropdownsNeedPopulated = false;
			}
			
			if(year != "") {
				$('#yearDropdown').val(year);
				if(year != $('#yearDropdown').val()) {
					$("#yearDropdownOverride").val(year);
				  	$("#yearDropdownToggle").trigger("click");
				}
			}
			
			if(make != "" && !dropdownsNeedPopulated) {
				console.log("Make is not empty");
				$('#makeDropdown').val(make);
				if(make != $('#makeDropdown').val()) {
					$("#makeDropdownOverride").val(make);
				  	$("#makeDropdownToggle").trigger("click");
				}
				$('#makeDropdown').trigger("change");
			}
			
						
		},
		
		populateNewVehicleMakes: function(rs,make) {
			var selectMake = false;
			var makeNotFound = true;
			if(make && make.length > 0) {
				selectMake = true;
			}
			rs = utilitiesJS.sortResults(rs, "MakeName", true);
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
		},
		
		populateNewVehicleModels: function(rs,selVal) {
			var model = $("#selectedVehicleModel").val();
			var selectModel = false;
			var modelNotFound = true;
			if(model && model.length > 0 && selVal == "") {
				selVal = model;
				selectModel = true;
			} else if(typeof selVal != "undefined" && selVal != "") {
				selectModel = true;
			}
			rs = utilitiesJS.sortResults(rs, "Model_Name", true);
			$.each(rs, function(i, m) {
				console.log(selVal + " and " + m.Model_Name);
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
		},
		
		populateNewVehicleFromVIN: function(rs) {
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
						customerJS.populateNewVehicleModels(rs.Results, model);
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
};
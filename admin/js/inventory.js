var inventoryJS = {
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
				$.ajax({
	        		"url": "api/v1/inventory/all?format=datatable&allLocations=true&store="+$("#storeDropdown").val(),
	        		"cache": false,
	        		"headers": {"Authorization":"Bearer " + Cookies.get('token')},
	        		"success": function(json) {
	        			if(json.hasOwnProperty("data") && json.data.length > 0) {
							var tableHeaders = "";
							var isAdmin = false;
		                	$.each(json.columns, function(i, val){
		                    	tableHeaders += "<th>" + val.label + "</th>";
		                    	if(val.label == "Edit") {
		                    		isAdmin = true;
		                    	}
		                	});
		                 
							$("#tableDiv").empty();
							$("#tableDiv").append('<table id="displayTable" class="table table-bordered table-striped table-responsive" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');
		                 
							if(isAdmin) {
								json.columns[json.columns.length-1].render = function (data, type, full, meta) {
									var returnData = '<button data-inventory-id="'+full.id+'" class="btn btn-secondary inventoryEdit" type="button">Edit</button>';
									returnData += '<input type="hidden" id="iItem-manufacturer-'+full.id+'" value="'+full.manufacturer+'" />';
									returnData += '<input type="hidden" id="iItem-partnumber-'+full.id+'" value="'+full.partnumber+'" />';
									returnData += '<input type="hidden" id="iItem-description-'+full.id+'" value="'+full.description+'" />';
									returnData += '<input type="hidden" id="iItem-cost-'+full.id+'" value="'+full.cost+'" />';
									returnData += '<input type="hidden" id="iItem-retail-'+full.id+'" value="'+full.retail+'" />';
									returnData += '<input type="hidden" id="iItem-quantity-'+full.id+'" value="'+full.quantity+'" />';
									returnData += '<input type="hidden" id="iItem-reserved-'+full.id+'" value="'+full.reserved+'" />';
									return returnData;
				                }
							}
							
							$('#displayTable').dataTable(json);
	        			} else {
	        				$("#tableDiv").empty();
	        				$("#tableDiv").append('No inventory available.');
	        			}
	            	},
	            	"dataType": "json"
	    		});
				
				$.ajax('./api/v1/inventory/vendorpartnum?store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) { inventoryJS.initAddInvoiceData(rs);});

				$.ajax('./api/v1/inventory/storepartnum?store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) { inventoryJS.initAddPartNumberData(rs);});
				
				$('#invoiceItemContainer .partnumberList').easyAutocomplete(inventoryJS.EacOptions());
			});
			
			$("#invoiceItemAddBtn").on('click',function(event) {
				event.preventDefault();
				event.stopPropagation();
				var container = document.getElementById("invoiceItemContainer");
				var newRow = document.createElement("div");
				var originalRow = document.getElementById("hiddenInvoiceItemRow");
				newRow.innerHTML = originalRow.innerHTML;
				newRow.className = "mb-3 mb-0-print invoiceItemSerialize";
				$(container).append(newRow);
				$(newRow).find(".partnumberList").easyAutocomplete(inventoryJS.EacOptions());
			});
			
			$(document).on('partnumberSelected',function(event) {
				$('.partnumberList').each(function() {
					if($(this).getSelectedItemData().description) {
						var row = $(this).closest(".invoiceItemSerialize");
						var description =  row.find(".itemDescription");
						description.val($(this).getSelectedItemData().description);
						var selected = row.find(".partnumberListSelected");
						selected.val($(this).getSelectedItemData().id);
					}
				});
			});
			
			$(document).on('change', '.itemQuantity, .itemCost', function(event) {
				var row =  $(this).closest(".form-row");
				var qty = parseFloat(row.find(".itemQuantity").first().val());
				var cost = parseFloat(row.find(".itemCost").first().val());
				if(isNaN(cost)) { cost = 0.0;}
				if(isNaN(qty)) {
					qty = 1;
					row.find(".itemQuantity").first().val(1);
				}
				var total = parseFloat($("#invoiceTotal").text());
				$("#invoiceTotal").text(((qty * cost) + total).toFixed(2));
			});
			
			$(document).on('click','.remove',function(event) {
				var row =  $(this).closest(".invoiceItemSerialize");
				var qty = parseFloat(row.find(".itemQuantity").first().val());
				var cost = parseFloat(row.find(".itemCost").first().val());
				if(isNaN(cost)) { cost = 0.0;}
				if(isNaN(qty)) {
					qty = 1;
					row.find(".itemQuantity").first().val(1);
				}
				var total = parseFloat($("#invoiceTotal").text());
				$("#invoiceTotal").text((total-(qty * cost)).toFixed(2));
				row.remove();
			});

			$(document).on("click", "#addInvoiceBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				$('#inventoryInfoModal').modal('show');
			});
			
			$(document).on("click", "#invoiceSaveBtn", function(event) {
				var form = document.getElementById("inventoryForm");
				var error = false;
				if (form.checkValidity() === false) {
					error = true;
				}

				$.each($("#invoiceItemContainer .partnumberList"), function() {
					if(this.value == "") {
						this.classList.add("border-danger");
						error = true;
					} else {
						this.classList.remove("border-danger");
					}
				});
				
				$.each($("#invoiceItemContainer .partnumberListSelected"), function() {
					if(this.value == "") {
						$(this).closest(".form-group").find(".partnumberList").addClass("border-danger");
						error = true;
					} else {
						$(this).closest(".form-group").find(".partnumberList").removeClass("border-danger");
					}
				});
				
				$.each($("#invoiceItemContainer .itemQuantity"), function() {
					if(this.value == "") {
						this.classList.add("border-danger");
						error = true;
					} else {
						this.classList.remove("border-danger");
					}
				});
				
				$.each($("#invoiceItemContainer .itemCost"), function() {
					if(this.value == "") {
						this.classList.add("border-danger");
						error = true;
					} else {
						this.classList.remove("border-danger");
					}
				});
				
				if(error) {
					form.classList.add('was-validated');
					event.preventDefault();
			        event.stopPropagation();
			        return;
				}
				
				var submitType = "POST";
				var inventoryObj = {};
				var inventoryItems = [];
				inventoryObj['vendor_id'] = $("#vendorDropdown").val();
				inventoryObj['invoice'] = $("#txtInvoiceNumber").val();
				inventoryObj['store'] = $("#storeDropdown").val();
				
				$.each($("#invoiceItemContainer .invoiceItemSerialize"), function() {
					var item = {};
					item.partnumber = $(this).find(".partnumberListSelected").val();
					item.quantity = $(this).find(".itemQuantity").val();
					item.cost = $(this).find(".itemCost").val();
					inventoryItems.push(item);
				});
				
				inventoryObj['items'] = inventoryItems;
				this.classList.add("disabled");
				$.ajax({
			          url: './api/v1/inventory/', // url where to submit the request
			          type : submitType, // type of action POST || GET
			          dataType : 'json', // data type
			          data : JSON.stringify(inventoryObj), // post data || get data
			          contentType: "application/json",
			          cache: false,
			          headers: {"Authorization":"Bearer " + Cookies.get('token')},
			          success : function(result) {
			              if(result.id) {
			            	  $.ajax({
			              		"url": "api/v1/inventory/all?format=datatable&allLocations=true&store="+$("#storeDropdown").val(),
			              		"cache": false,
			              		"headers": {"Authorization":"Bearer " + Cookies.get('token')},
			              		"success": function(json) {
			      					var tableHeaders = "";
			    					var isAdmin = false;
			                    	$.each(json.columns, function(i, val){
			                        	tableHeaders += "<th>" + val.label + "</th>";
			                        	if(val.label == "Edit") {
			                        		isAdmin = true;
			                        	}
			                    	});
			                     
			    					$("#tableDiv").empty();
			    					$("#tableDiv").append('<table id="displayTable" class="table table-bordered table-striped table-responsive" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');
			                     
			    					if(isAdmin) {
			    						json.columns[json.columns.length-1].render = function (data, type, full, meta) {
			    							var returnData = '<button data-inventory-id="'+full.id+'" class="btn btn-secondary inventoryEdit" type="button">Edit</button>';
			    							returnData += '<input type="hidden" id="iItem-manufacturer-'+full.id+'" value="'+full.manufacturer+'" />';
			    							returnData += '<input type="hidden" id="iItem-partnumber-'+full.id+'" value="'+full.partnumber+'" />';
			    							returnData += '<input type="hidden" id="iItem-description-'+full.id+'" value="'+full.description+'" />';
			    							returnData += '<input type="hidden" id="iItem-cost-'+full.id+'" value="'+full.cost+'" />';
			    							returnData += '<input type="hidden" id="iItem-retail-'+full.id+'" value="'+full.retail+'" />';
			    							returnData += '<input type="hidden" id="iItem-quantity-'+full.id+'" value="'+full.quantity+'" />';
			    							returnData += '<input type="hidden" id="iItem-reserved-'+full.id+'" value="'+full.reserved+'" />';
			    							return returnData;
			    		                }
			    					}
			      					
			      					$('#displayTable').dataTable(json);
			                  	},
			                  	"dataType": "json"
			            	  });
			            	  var form = document.getElementById("inventoryForm");
			            	  form.classList.remove('was-validated');
			            	  $('#inventoryInfoModal').modal('hide');
			            	  $('#inventoryInfoFormMessageContainer').html("");
			              } else {
			            	  $('#inventoryInfoFormMessageContainer').html("");
			            	  $('#inventoryInfoFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Vendor<br />'+JSON.stringify(result)+'</div>');
			              }
			              $("#invoiceSaveBtn").removeClass("disabled");
			          },
			          error: function(xhr, resp, text) {
			        	  $('#inventoryInfoFormMessageContainer').html("");
			        	  $('#inventoryInfoFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Vendor<br />'+text+'</div>');
			        	  $("#invoiceSaveBtn").removeClass("disabled");
			          }
			      })
			});

			$(document).on("click", ".inventoryEdit", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var iId = $(this).data("inventory-id");
				$("#inventoryModalTitle").html("Edit Part Number");
				$("#IEditUpdateBtn").html("Update");
				$("#txtIEditManufacturer").val($("#iItem-manufacturer-"+iId).val());
				$("#txtIEditPartnumber").val($("#iItem-partnumber-"+iId).val());
				$("#txtIEditDescription").val($("#iItem-description-"+iId).val());
				$("#txtIEditCost").val($("#iItem-cost-"+iId).val());
				$("#txtIEditRetail").val($("#iItem-retail-"+iId).val());
				$("#txtIEditQuantity").val($("#iItem-quantity-"+iId).val());
				$("#txtIEditReserved").val($("#iItem-reserved-"+iId).val());
				$("#txtIEditId").val(iId);
				$('#inventoryEditModal').modal('show');
			});
			
			$(document).on("click", "#IEditCancelBtn, button.close", function(event) {
				var form = document.getElementById("inventoryEditForm");
				form.classList.remove('was-validated');
				$(form).find(".numeric_only").removeClass("btn-outline-danger");
				$('#inventoryEditModal').modal('hide');
				$('#inventoryEditFormMessageContainer').html("");
			});
			
			$(document).on("click", "#addPartNumberBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				$("#inventoryModalTitle").html("Add Part Number");
				$("#IEditUpdateBtn").html("Add");
				$("#txtIEditManufacturer").val("");
				$("#txtIEditPartnumber").val("");
				$("#txtIEditDescription").val("");
				$("#txtIEditCost").val("");
				$("#txtIEditRetail").val("");
				$("#txtIEditQuantity").val("");
				$("#txtIEditReserved").val("");
				$("#txtIEditId").val("-1");
				$("#txtIEditStore").val("");
				$('#inventoryEditFormMessageContainer').html("");
				$('#inventoryEditModal').modal('show');
			});
			
			$(document).on("click", "#IEditUpdateBtn", function(event) {
				var form = document.getElementById("inventoryEditForm");
				var qty = Number($("#txtIEditQuantity").val());
				var reservedQty = Number($("#txtIEditReserved").val());
				var error = false;
				if (form.checkValidity() === false) {
					error = true;
				}

				if (reservedQty > qty) {
					$('#inventoryEditFormMessageContainer').html("");
					$('#inventoryEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Reserved is greater than on hand quantity<br /></div>');
					error = true;
				}

				var numeric_fields = $(form).find(".numeric_only");
				numeric_fields.each(function() {
					if(isNaN($( this ).val())) {
						error = true;
						$(this).addClass("btn-outline-danger");
					}
					else {
						$(this).removeClass("btn-outline-danger");
					}
				});

				if($('#txtIEditStore').val().length == 0) {
					$('.multiselect').addClass('has-error');
				}
				
				if(error) {
					form.classList.add('was-validated');
					event.preventDefault();
			        event.stopPropagation();
			        return;
				}
				var formData = utilitiesJS.serializeObject("inventoryEditForm","IEditSerialize");
				formData.store = $("#storeDropdown").val();
				formData.storeID =  $('#txtIEditStore').val();
				var submitType = "PUT";
				if(formData.inventory_id == -1) {
					submitType = "POST";
				}
				this.classList.add("disabled");
				$.ajax({
			          url: './api/v1/inventory/item/', // url where to submit the request
			          type : submitType, // type of action POST || GET
			          dataType : 'json', // data type
			          data : JSON.stringify(formData), // post data || get data
			          contentType: "application/json",
			          cache: false,
			          headers:{"Authorization":"Bearer " + Cookies.get('token')},
			          success : function(result) {
			              if(result.id) {
			            	  $.ajax({
			              		"url": "api/v1/inventory/all?format=datatable&allLocations=true&store="+$("#storeDropdown").val(),
			              		"cache": false,
			              		"headers":{"Authorization":"Bearer " + Cookies.get('token')},
			              		"success": function(json) {
			              			var tableHeaders = "";
			    					var isAdmin = false;
			                    	$.each(json.columns, function(i, val){
			                        	tableHeaders += "<th>" + val.label + "</th>";
			                        	if(val.label == "Edit") {
			                        		isAdmin = true;
			                        	}
			                    	});
			                     
			    					$("#tableDiv").empty();
			    					$("#tableDiv").append('<table id="displayTable" class="table table-bordered table-striped table-responsive" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');
			                     
			    					if(isAdmin) {
			    						json.columns[json.columns.length-1].render = function (data, type, full, meta) {
			    							var returnData = '<button data-inventory-id="'+full.id+'" class="btn btn-secondary inventoryEdit" type="button">Edit</button>';
			    							returnData += '<input type="hidden" id="iItem-manufacturer-'+full.id+'" value="'+full.manufacturer+'" />';
			    							returnData += '<input type="hidden" id="iItem-partnumber-'+full.id+'" value="'+full.partnumber+'" />';
			    							returnData += '<input type="hidden" id="iItem-description-'+full.id+'" value="'+full.description+'" />';
			    							returnData += '<input type="hidden" id="iItem-cost-'+full.id+'" value="'+full.cost+'" />';
			    							returnData += '<input type="hidden" id="iItem-retail-'+full.id+'" value="'+full.retail+'" />';
			    							returnData += '<input type="hidden" id="iItem-quantity-'+full.id+'" value="'+full.quantity+'" />';
			    							returnData += '<input type="hidden" id="iItem-reserved-'+full.id+'" value="'+full.reserved+'" />';
			    							return returnData;
			    		                }
			    					}
			      					
			      					$('#displayTable').dataTable(json);
			                  	},
			                  	"dataType": "json"
			            	  });
			            	  var form = document.getElementById("inventoryEditForm");
			            	  form.classList.remove('was-validated');
			            	  $('#inventoryEditModal').modal('hide');
			            	  $('#inventoryEditFormMessageContainer').html("");
			              } else {
			            	  $('#inventoryEditFormMessageContainer').html("");
			            	  $('#inventoryEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Part Number<br />'+JSON.stringify(result)+'</div>');
			              }
			              $("#IEditUpdateBtn").removeClass("disabled");
			          },
			          error: function(xhr, resp, text) {
			        	  $('#inventoryEditFormMessageContainer').html("");
			        	  $('#inventoryEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Part Number<br />'+text+'</div>');
			        	  $("#IEditUpdateBtn").removeClass("disabled");
			          }
			      });
			});
			
			if(Cookies.get('fluxur') == 1) {
				$('.fluxur1').removeClass('fluxur1');
			}
		},
		
		initAddInvoiceData: function(rs) {
			$('#vendorDropdown').html('');
			$.each(rs.vendors, function(i, v) {
				$('#vendorDropdown').append('<option value="' + v.id +'">' + v.vendorname + '</option>');
			});
		},

		initAddPartNumberData: function(rs) {
			$('#txtIEditStore').html('');
			$.each(rs.stores, function(i, s) {
				$('#txtIEditStore').append('<option value="' + s.id +'">' + s.identifier + '</option>');
				$(".select").multiselect('rebuild');
			});
		},
		
		EacOptions: function() {
			var options = {
				url: function(phrase) {
					return "api/v1/inventory/filter/" + phrase + "?store="+$("#storeDropdown").val();
				},
				getValue: "partnumber",
				list: {
					onChooseEvent: function() {
						$(document).trigger($.Event('partnumberSelected'));
					}
				},
				listLocation: "partnums",
				adjustWidth: false,
				ajaxSettings: {
					headers:{"Authorization":"Bearer " + Cookies.get('token')}
				}
			};
			
			return options;
		}
};
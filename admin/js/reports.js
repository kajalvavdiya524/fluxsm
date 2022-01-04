var reportsJS = {
		noDataTmplt: '<p class="mb-0 text-danger">No Data Found</p>',
		myLineChart: undefined,
		unsaved: false,
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
				$.ajax('./api/v1/store/details?store='+$("#storeDropdown").val(), {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (storeRs) { 
					reportsJS.populatePrintStoreDetails(storeRs);
				});
				$("#reportType").on("change", function() {
					if ($("#reportType").val() == 11) {
						$("#endDateLabel").text('As on Date');
						$("#startDateLabel").hide();
						$("#startDate").hide();
					} else {
						$("#startDate").show();
						$("#startDateLabel").show();
						$("#endDateLabel").text('End Date');
					}
				});
			});
			$("#viewReportBtn").on("click", function(event) {
				event.preventDefault();
				event.stopPropagation();
				if(reportsJS.confirmExit()) {
					var r = confirm("New information not saved. Do you wish to leave the page?");
				    if (r !== true) {
				        return false;
				    }
				}
				if($("#startDate").val() != "" || $("#endDate").val() != "") {
					$("#postedTotalReport").hide();
					$("#ARReport").hide();
					$("#APReport").hide();
					$("#LowMarginReport").hide();
					$("#SalesTaxExemptReport").hide();
					$("#TechProductivitytReport").hide();
					$("#InventoryItemsSoldReport").hide();
					$("#OutsidePurchaseTiresSoldReport").hide();
					$("#DeletedOrdersReport").hide();
					$("#BestSellerReport").hide();
					$("#InventoryDollarsReport").hide();
					var anchor;
					if($("#reportType").val() == 1) {
						$.ajax('./api/v1/report/postedtotals/?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayPostedTotalsReport(rs);});
						$("#postedTotalReport").show();
						anchor = $("#postedTotalReport");
					} else if($("#reportType").val() == 2) {
						$.ajax('./api/v1/report/accountsreceivable/?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayAccountsReceivableReport(rs);});
						$("#ARReport").show();
						anchor = $("#ARReport");
					} else if($("#reportType").val() == 3) {
						$.ajax('./api/v1/report/accountspayable/?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayAccountsPayableReport(rs);});
						$("#APReport").show();
						anchor = $("#APReport");
					} else if($("#reportType").val() == 4) {
						$.ajax('./api/v1/report/lowmargin/?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayLowMarginReport(rs);});
						$("#LowMarginReport").show();
						anchor = $("#LowMarginReport");
					} else if($("#reportType").val() == 5) {
						$.ajax('./api/v1/report/salestaxexempt/?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displaySalesTaxExemptReport(rs);});
						$("#SalesTaxExemptReport").show();
						anchor = $("#SalesTaxExemptReport");
					} else if($("#reportType").val() == 6) {
						$.ajax('./api/v1/report/techproductivity/?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayTechProductivitytReport(rs);});
						$("#TechProductivitytReport").show();
						anchor = $("#TechProductivitytReport");
					} else if($("#reportType").val() == 7) {
						$.ajax('./api/v1/report/inventorysold/?format=datatable&start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayInventoryItemsSoldReport(rs);});
						$("#InventoryItemsSoldReport").show();
						anchor = $("#InventoryItemsSoldReport");
					} else if($("#reportType").val() == 8) {
						$.ajax('./api/v1/report/outsidepurchasetiressold/?format=datatable&start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayOutsidePurchaseTiresSoldReport(rs);});
						$("#OutsidePurchaseTiresSoldReport").show();
						anchor = $("#OutsidePurchaseTiresSoldReport");
					} else if($("#reportType").val() == 10) {
						$.ajax('./api/v1/report/bestseller/?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayBestSellerReport(rs);});
						$("#BestSellerReport").show();
					} else if($("#reportType").val() == 9) {
						$.ajax('./api/v1/report/deletedorders/?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayDeletedOrdersReport(rs);});
						$("#DeletedOrdersReport").show();
						anchor = $("#DeletedOrdersReport");
					} else if($("#reportType").val() == 11) {
						$.ajax('./api/v1/report/inventoryDollars/?format=datatable&start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {reportsJS.displayInventoryDollarsReport(rs);});
						$("#InventoryDollarsReport").show();
					}
					
				    $('html, body').stop().animate({
				      scrollTop: anchor.offset().top - $("#mainNav").outerHeight()
				    }, 1000, 'easeInOutExpo');
				}
				reportsJS.unsaved = false;
			});

			$(document).on("click", ".printStatement", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var statement =  $(this).closest(".statementCustomer");
				$("#printCustomer").html(statement.find(".customerDetails").html());
				
				$(".statementCustomer").each(function() {
					  $(this).addClass("d-print-none");
				});
				
				statement.removeClass("d-print-none");
				window.print();
			});
			
			$(document).on("change", ".closedPayMeth", function(event) {
				reportsJS.unsaved = true;
				if($(this).find("option:selected").data("type") == 2) {
					$(this).closest("td").find(".paymentCheckNum").show();
				} else {
					$(this).closest("td").find(".paymentCheckNum").hide();
				}
			});
			
			$(document).on("click", "#APBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var paidInvoices = [];
                $(".invoicePaid:checked").each(function() {
                	paidInvoices.push(this.value);
                });

                if(paidInvoices.length > 0) {
                	this.classList.add("disabled");
                	$.ajax({
                		url: './api/v1/accountspayable/paid?store='+$("#storeDropdown").val(), 
                		type : "PUT",
                		dataType : 'json',
                		data : JSON.stringify(paidInvoices),
                		contentType: "application/json",
                		headers:{"Authorization":"Bearer " + Cookies.get('token')},
                		success : function(result) {
                			$("#APBtn").removeClass("disabled");
                			$("#viewReportBtn").trigger("click");
                		},
                		error: function(xhr, resp, text) {
                			$('#APMessageContainer').html("");
                			$('#APMessageContainer').html('<div class="alert alert-danger" role="alert">Error Marking Invoices Paid<br />'+text+'</div>');
                			$("#APBtn").removeClass("disabled");
                		}
                	});
                }
			});
			
			$(document).on("click", "#ARBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				
				$(".paymentCheckNum:visible").removeClass("border border-danger");
				
				var paidInvoices = [];
                $(".closedPayMeth").each(function() {
                	if(this.value != "") {
	                	var inv = {};
	                	inv.id = $(this).data("id");
	                	inv.payid = this.value;
	                	var checkNum = $(this).closest("td").find(".paymentCheckNum:visible");
	                	if(checkNum.length) {
	                		inv.checknumber = checkNum.val();
	                	}
	                	paidInvoices.push(inv);
                	}
                });
                
                var isError = false;
                
                $(".paymentCheckNum:visible").each(function() {
                	if($(this).val() == "") {
          			  isError = true;
          			  $(this).addClass("border border-danger");
          		  	}
                });
                

                if(!isError && paidInvoices.length > 0) {
                	this.classList.add("disabled");
                	$.ajax({
                		url: './api/v1/accountsreceivable/paid', 
                		type : "PUT",
                		dataType : 'json',
                		data : JSON.stringify(paidInvoices),
                		contentType: "application/json",
                		headers:{"Authorization":"Bearer " + Cookies.get('token')},
                		success : function(result) {
                			$("#ARBtn").removeClass("disabled");
                			reportsJS.unsaved = false;
                			$("#viewReportBtn").trigger("click");
                		},
                		error: function(xhr, resp, text) {
                			$('#ARMessageContainer').html("");
                			$('#ARMessageContainer').html('<div class="alert alert-danger" role="alert">Error Marking Invoices Paid<br />'+text+'</div>');
                			$("#ARBtn").removeClass("disabled");
                		}
                	});
                }
			});
			
			window.onbeforeunload = reportsJS.confirmExit;
                        
		},
		
		displayPostedTotalsReport: function(rs) {
			$("#totalInvoices").html(rs.totals.invoicecount);
			$("#printedOn").html("<small>Printed on " + new Date(Date.now()).toLocaleString() + "</small>");
			var totalsList = '';
			var reportCol1 = '';
			var reportCol2 = '';
			var reportCol3 = '';
			totalsList += '<table class="table table-striped">';
			totalsList += '	<thead>';
			totalsList += '		<tr><th class="w-50">Name</th><th class="w-50 text-right">Amount</th></tr>';
			totalsList += '	</thead>';
			totalsList += '	<tbody>';
			$.each(rs.totals.paymeth, function(i, m) {
				totalsList += '		<tr><td class="w-50">'+m.name+'</td><td class="w-50 text-right">'+m.amount.toFixed(2)+'</td></tr>';
			});
			if(!rs.totals.hasOwnProperty("partsales")) { rs.totals.partsales = 0; }
			if(!rs.totals.hasOwnProperty("tiresales")) { rs.totals.tiresales = 0; }
			if(!rs.totals.hasOwnProperty("laborsales")) { rs.totals.laborsales = 0; }
			if(!rs.totals.hasOwnProperty("feesales")) { rs.totals.feesales = 0; }
			if(!rs.totals.hasOwnProperty("discountsales")) { rs.totals.discountsales = 0; }
			
			totalsList += '		<tr><td class="w-50 font-weight-bold">Grand Total</td><td class="w-50 font-weight-bold text-right">'+rs.totals.grand.toFixed(2)+'</td></tr>';
			totalsList += '		<tr><td class="w-50">Average Invoice</td><td class="w-50 text-right">'+rs.totals.average.toFixed(2)+'</td></tr>';
			
			reportCol1 += '<table class="table table-striped">';
			reportCol1 += '	<thead>';
			reportCol1 += '		<tr><th class="w-50">Sales</th><th class="w-50 text-right"></th></tr>';
			reportCol1 += '	</thead>';
			reportCol1 += '	<tbody>';
			reportCol1 += '		<tr><td class="w-50">Parts</td><td class="w-50 text-right">'+rs.totals.partsales.toFixed(2)+'</td></tr>';
			reportCol1 += '		<tr><td class="w-50">Tires</td><td class="w-50 text-right">'+rs.totals.tiresales.toFixed(2)+'</td></tr>';
			reportCol1 += '		<tr><td class="w-50">Labor</td><td class="w-50 text-right">'+rs.totals.laborsales.toFixed(2)+'</td></tr>';
			reportCol1 += '		<tr><td class="w-50">Fees</td><td class="w-50 text-right">'+rs.totals.feesales.toFixed(2)+'</td></tr>';
			reportCol1 += '		<tr><td class="w-50">Discounts</td><td class="w-50 text-right">'+rs.totals.discountsales.toFixed(2)+'</td></tr>';
			
			
			reportCol2 += '<table class="table table-striped">';
			reportCol2 += '	<thead>';
			reportCol2 += '		<tr><th class="w-50">Cost</th><th class="w-50 text-right"></th></tr>';
			reportCol2 += '	</thead>';
			reportCol2 += '	<tbody>';
			reportCol2 += '		<tr><td class="w-50">Part Cost</td><td class="w-50 text-right">'+rs.totals.partcost.toFixed(2)+'</td></tr>';
			reportCol2 += '		<tr><td class="w-50">Tire Cost</td><td class="w-50 text-right">'+rs.totals.tirecost.toFixed(2)+'</td></tr>';
			reportCol2 += '		<tr><td class="w-50 font-weight-bold">Taxes Collected</td><td class="w-50 text-right"> </td></tr>';
			$.each(rs.totals.taxes, function(i, m) {
				reportCol2 += '<tr><td class="w-50">'+m.name+'</td><td class="w-50 text-right">'+m.amount.toFixed(2)+'</td></tr>';
			});
			
			reportCol3 += '<table class="table table-striped">';
			reportCol3 += '	<thead>';
			reportCol3 += '		<tr><th class="w-50">AR Payments</th><th class="w-50 text-right"></th></tr>';
			reportCol3 += '	</thead>';
			reportCol3 += '	<tbody>';
			$.each(rs.arpayments, function(i, m) {
				reportCol3 += '<tr><td class="w-50">'+m.name+'</td><td class="w-50 text-right">'+m.amount+'</td></tr>';
			});
			
			$("#postedTotalsList").html(totalsList);
			$("#reportcol1").html(reportCol1);
			$("#reportcol2").html(reportCol2);
			$("#reportcol3").html(reportCol3);
			
			var invoiceSummary = '';
			$.each(rs.summary, function(i, m) {
				invoiceSummary += '<h3>'+i+'</h3>';
				invoiceSummary += '<table class="table table-striped">';
				invoiceSummary += '	<thead>';
				invoiceSummary += '		<tr><th class="w-25">Date</th><th class="w-25">Invoice</th><th class="w-30">Name</th><th class="w-20 text-right">Amount</th></tr>';
				invoiceSummary += '	</thead>';
				invoiceSummary += '	<tbody>';
				
				$.each(m.invoices, function(idx, inv) {
					var trclass = "";
					if(inv.internal == "1") {
						trclass = "alert-warning font-italic";
					}
					invoiceSummary += '		<tr class="'+trclass+'"><td>'+inv.date+'</td><td><a href="workorderedit.php?orderId='+inv.number+'&store='+$("#storeDropdown").val()+'">'+inv.number+'</a></td><td>'+inv.name+'</td><td class="text-right">'+inv.amount+'</td></tr>';
				});
				
				invoiceSummary += '		<tr><td colspan="4" class="text-right">Total: '+m.total.toFixed(2)+'</td></tr>';
				invoiceSummary += '	</tbody>';
				invoiceSummary += '</table>';
			});

			$("#postedTotalsInvoiceSummary").html(invoiceSummary);
			Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
			Chart.defaults.global.defaultFontColor = '#292b2c';
			// -- Area Chart Example
			var ctx = document.getElementById("postedTotalsChart");
			
			if(this.myLineChart) {
				this.myLineChart.destroy();
			}
			
			this.myLineChart = new Chart(ctx, 
					{  
				   "type":"bar",
				   "data": rs.chartjs,
			  options: {
				  scales:{"yAxes":[{"ticks":{"beginAtZero":true}}]},
				  legend: {
				      display: false
				    }
			  }
			});
		},
		
		displayAccountsReceivableReport: function(rs) {
			if (!rs.hasOwnProperty('accounts')) {
				$("#ARBtn").hide();
				$("#ARSummary").html(reportsJS.noDataTmplt);
				return false;
			}

			var payMethSelect = '<option value="">Unpaid</option>';
			var arSummary = '';
			
			$.each(rs.paymentmethods, function(i, p) {
				payMethSelect += '<option data-type="'+p.paymenttype_id+'" value="'+p.id+'">'+p.name+'</option>';
			});
			
			$.each(rs.accounts, function(c, i) {
				arSummary += '<div class="statementCustomer">';
				arSummary += '<h3 class="d-print-none">'+c+'</h3>';
				arSummary += '<table class="table table-striped arreport">';
				arSummary += '	<thead>';
				arSummary += '		<tr><th class="w-15">Date</th><th class="w-15">Invoice</th><th class="w-15">Fleet Number</th><th class="w-20 d-print-none">Name</th><th class="w-15 text-right">Amount</th><th class="w-20 text-right d-print-none">Mark Paid</th></tr>';
				arSummary += '	</thead>';
				arSummary += '	<tbody>';
                                
				$.each(i.orders, function(idx, inv) {
					arSummary += '		<tr><td>'+inv.date+'</td><td><a href="workorderedit.php?orderId='+inv.number+'&store='+$("#storeDropdown").val()+'">'+inv.number+'</a></td><td>'+inv.fleetnum+'</td><td class="d-print-none">'+inv.name+'</td><td class="text-right">'+inv.amount+'</td><td class="text-right d-print-none">';
					arSummary += '<select data-id="'+inv.orderpayinfoid+'" class="form-control closedPayMeth">';
					arSummary += payMethSelect;
					arSummary += '</select>';
					arSummary += '<input type="text" class="form-control paymentCheckNum" style="display:none;" placeholder="Check Number">';
					arSummary += '</td></tr>';
				});
                                
				arSummary += '<tr><td colspan="6" class="text-right"><strong>Total:</strong> $'+i.details.total.toFixed(2)+'</td></tr>';
				arSummary += '<tr class="d-print-none"><td colspan="6" class="text-right"><button class="btn btn-secondary printStatement">Print Statement</button></td></tr>';
				arSummary += '	</tbody>';
				arSummary += '</table>';
				arSummary += '<div class="customerDetails" style="display:none;">';
				arSummary += i.details.name + '<br />';
				  
				if(i.details.hasOwnProperty('addressline1') && i.details.addressline1 && i.details.addressline1 != "") {
					arSummary += i.details.addressline1 + '<br />';
				}
				if(i.details.hasOwnProperty('addressline2') && i.details.addressline2 && i.details.addressline2 != "") {
					arSummary += i.details.addressline2 + '<br />';
				}
				if(i.details.hasOwnProperty('addressline3') && i.details.addressline3 && i.details.addressline3 != "") {
					arSummary += i.details.addressline3 + '<br />';
				}
				if(i.details.hasOwnProperty('city') && i.details.city != "" && i.details.hasOwnProperty('zip') && i.details.zip != "") {
					arSummary += i.details.city + ', ' + i.details.state + ' ' + i.details.zip + '<br />';
				}
  
				arSummary += i.details.phone1;
				arSummary += '</div>';
				arSummary += '</div>';
			});
			
			var currentDate = new Date();
			var day = currentDate.getDate();
			var month = currentDate.getMonth() + 1;
			var year = currentDate.getFullYear();
			var detail = "Date: " + month + "/" + day + "/" + year + "<br />";
			$("#printDetail").html(detail);
			$("#ARBtn").show();
			$("#ARSummary").html(arSummary);
		},
		
		displayAccountsPayableReport: function(rs) {
			var rs = rs.data;
			if (rs.length == 0) {
				$("#APBtn").hide();
				$("#APSummary").html(reportsJS.noDataTmplt);
				return false;
			}

			var apSummary = '';
			$.each(rs, function(v, i) {
				apSummary += '<h3>'+v+'</h3>';
				apSummary += '<table class="table table-striped arreport">';
				apSummary += '	<thead>';
				apSummary += '		<tr><th class="w-25">Date</th><th class="w-25">Invoice</th><th class="w-15 text-right">Amount</th><th class="w-25 text-right">Mark Paid</th></tr>';
				apSummary += '	</thead>';
				apSummary += '	<tbody>';
				$.each(i, function(idx, inv) {
					apSummary += '		<tr><td>'+inv.date+'</td><td><a href="invoicehistory.php?id='+inv.id+'&store='+$("#storeDropdown").val()+'">'+inv.number+'</a></td><td class="text-right">'+inv.amount+'</td><td class="text-right">';
					apSummary += '		<label class="custom-control custom-checkbox">';
					apSummary += '		  <input type="checkbox" class="custom-control-input invoicePaid" value="'+inv.id+'">';
					apSummary += '		  <span class="custom-control-indicator"></span>';
					apSummary += '		</label>';
					apSummary += '		</td></tr>';
				});
				
				apSummary += '	</tbody>';
				apSummary += '</table>';
			});
			$("#APBtn").show();
			$("#APSummary").html(apSummary);
		},
		
		displayLowMarginReport: function(rs) {
			var rs = rs.data;
			if (rs.length == 0) {
				$("#LowMarginSummary").html(reportsJS.noDataTmplt);
				return false;
			}

			var invoiceSummary = '';
			invoiceSummary += '<h3>Invoices</h3>';
			invoiceSummary += '<table class="table table-striped">';
			invoiceSummary += '	<thead>';
			invoiceSummary += '		<tr><th class="w-20">Date</th><th class="w-20">Invoice</th><th class="w-30">Name</th><th class="w-15 text-right">Amount</th><th class="w-15 text-right">Margin</th></tr>';
			invoiceSummary += '	</thead>';
			invoiceSummary += '	<tbody>';
			
			$.each(rs, function(idx, inv) {
				invoiceSummary += '		<tr><td>'+inv.date+'</td><td><a href="workorderedit.php?orderId='+inv.number+'&store='+$("#storeDropdown").val()+'">'+inv.number+'</a></td><td>'+inv.name+'</td><td class="text-right">'+inv.amount+'</td><td class="text-right">'+inv.margin+'%</td></tr>';
			});
			
			invoiceSummary += '	</tbody>';
			invoiceSummary += '</table>';


			$("#LowMarginSummary").html(invoiceSummary);
		},
		
		displaySalesTaxExemptReport: function(rs) {	
			var rs = rs.data;
			if (rs.length == 0) {
				$("#SalesTaxExemptSummary").html(reportsJS.noDataTmplt);
				return false;
			}
	
			var invoiceSummary = '';
			var total = 0;
			invoiceSummary += '<h3>Invoices</h3>';
			invoiceSummary += '<table class="table table-striped">';
			invoiceSummary += '	<thead>';
			invoiceSummary += '		<tr><th class="w-20">Date</th><th class="w-20">Invoice</th><th class="w-20">Name</th><th class="w-20">Tax Exempt Number</th><th class="w-20 text-right">Amount</th></tr>';
			invoiceSummary += '	</thead>';
			invoiceSummary += '	<tbody>';
			
			$.each(rs, function(idx, inv) {
				if(inv.taxexemptnum == null) {inv.taxexemptnum = "";}
				total += Number(inv.amount);
				invoiceSummary += '		<tr><td>'+inv.date+'</td><td><a href="workorderedit.php?orderId='+inv.id+'&store='+$("#storeDropdown").val()+'">'+inv.id+'</a></td><td>'+inv.name+'</td><td>'+inv.taxexemptnum+'</td><td class="text-right">'+inv.amount+'</td></tr>';
			});
			
			invoiceSummary += '		<tr><td colspan="4" class="text-right font-weight-bold">Total:</td><td class="text-right font-weight-bold">'+total.toFixed(2)+'</td></tr>'
			invoiceSummary += '	</tbody>';
			invoiceSummary += '</table>';

			$("#SalesTaxExemptSummary").html(invoiceSummary);
		},
		
		displayTechProductivitytReport: function(rs) {	
			if (rs.length == 0) {
				$("#TechProductivitySummary").html(reportsJS.noDataTmplt);
				return false;
			}

			var invoiceSummary = '';
			$.each(rs, function(i, m) {
				invoiceSummary += '<h3>'+i+'</h3>';
				invoiceSummary += '<table class="table table-striped">';
				invoiceSummary += '	<thead>';
				invoiceSummary += '		<tr><th class="w-25">Date</th><th class="w-25">Invoice</th><th class="w-30">Name</th><th class="w-20 text-right">Amount</th></tr>';
				invoiceSummary += '	</thead>';
				invoiceSummary += '	<tbody>';
				
				$.each(m.invoices, function(idx, inv) {
					invoiceSummary += '		<tr><td>'+inv.date+'</td><td><a href="workorderedit.php?orderId='+inv.number+'&store='+$("#storeDropdown").val()+'">'+inv.number+'</a></td><td>'+inv.name+'</td><td class="text-right">'+inv.amount+'</td></tr>';
				});
				
				invoiceSummary += '		<tr><td colspan="4" class="text-right">Total: '+m.total.toFixed(2)+'</td></tr>';
				invoiceSummary += '	</tbody>';
				invoiceSummary += '</table>';
			});

			$("#TechProductivitySummary").html(invoiceSummary);
		},

		displayInventoryItemsSoldReport: function(rs) {
			var rs = rs.inventorySold;
			if (rs.data.length == 0) {
				$("#inventoryItemsSoldTableDiv").html(reportsJS.noDataTmplt);
				return false;
			}

			var tableHeaders = "";
        	$.each(rs.columns, function(i, val){
            	tableHeaders += "<th>" + val.label + "</th>";
        	});
         	
			$("#inventoryItemsSoldTableDiv").empty();
			$("#inventoryItemsSoldTableDiv").append('<table id="inventoryItemsSoldDisplayTable" class="table table-bordered table-striped table-responsive" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');
			$('#inventoryItemsSoldDisplayTable').dataTable(rs);
		},

		displayInventoryDollarsReport: function(rs) {
			if (rs.data.length == 0) {
				$("#inventoryDollarsTableDiv").html(reportsJS.noDataTmplt);
				return false;
			}

			var tableHeaders = "";
        	$.each(rs.columns, function(i, val){
            	tableHeaders += "<th>" + val.label + "</th>";
        	});

        	var totalQuantity = 0;
        	var totalCost = 0;
        	$.each(rs.data, function(i, m){
        		totalQuantity += Number(m.total_quantity);
        		totalCost += Number(m.total_cost);
        	});

			$("#inventoryDollarsTableDiv").empty();
			$("#inventoryDollarsTableDiv").append('<table id="inventoryDollarsDisplayTable" class="table table-bordered table-striped" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');
			rs.order = [ 0, "asc" ];
			rs.dom = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
			"<'row'<'col-sm-12 text-right'B>>" +
			"<'row'<'col-sm-12'tr>>" +
			"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
			rs.buttons = ['copyHtml5','excelHtml5','csvHtml5'];
			rs.autoWidth = false;
			rs.responsive = true;

			$('#inventoryDollarsDisplayTable').dataTable(rs);
			$('#inventoryDollarsDisplayTable').append('<tfoot><tr style="font-weight:bold"><td></td><td></td><td>Total</td><td>' +totalQuantity+ '</td><td></tfooter>' +totalCost.toFixed(2)+ '</td></tr></tfoot>');
		},

		displayBestSellerReport: function(rs) {
			if (rs.length == 0) {
				$("#bestSellerTableDiv").html(reportsJS.noDataTmplt);
				return false;
			} else {
				var table = '<table id="bestSellerTable" class="table table-bordered table-striped" style="width:100%">' +
							'	<thead>' +
							'		<tr>' +
							'			<th style="width:20%">Part Number</th>' +
							'			<th style="width:20%">Description</th>' +
							'			<th style="width:20%">Unit Price</th>' +
							'			<th style="width:20%">Quantity Sold</th>' +
							'			<th style="width:20%">Total Price of Sold Items</th>' +
							'		</tr>' +
							'	</thead>' +
							'</table>';
				$("#bestSellerTableDiv").html(table);
			}
			
			var t = $("#bestSellerTable").DataTable( {
				"autoWidth": false,
				"retrieve": true,
				"responsive": true,
				"dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
				"<'row'<'col-sm-12 text-right'B>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
				"buttons": ['copyHtml5','excelHtml5','csvHtml5']
		    } );

        	bestSeller = [];
        	$.each(rs, function(i, m){
        		bestSeller.push([
        			m.partnumber,
        			m.description,
        			m.unitprice,
        			m.total_quantity_sold,
        			m.totalprice,
        		]);
        	});

			t.clear();
			t.rows.add(bestSeller);
			t.order( [ 3, 'desc' ] );
			t.draw(false);
		},
		
		displayOutsidePurchaseTiresSoldReport: function(rs) {
			var rs = rs.purchaseTiresSold;
			if (rs.data.length == 0) {
				$("#outsidePurchaseTiresSoldTableDiv").html(reportsJS.noDataTmplt);
				return false;
			}

			var tableHeaders = "";
        	$.each(rs.columns, function(i, val){
            	tableHeaders += "<th>" + val.label + "</th>";
        	});
         	
			$("#outsidePurchaseTiresSoldTableDiv").empty();
			$("#outsidePurchaseTiresSoldTableDiv").append('<table id="outsidePurchaseTiresSoldDisplayTable" class="table table-bordered table-striped table-responsive" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');
			$('#outsidePurchaseTiresSoldDisplayTable').dataTable(rs);
		},
		
		displayDeletedOrdersReport: function(rs) {
			var rs = rs.data;	
			if (rs.length == 0) {
				$("#DeletedOrdersSummary").html(reportsJS.noDataTmplt);
				return false;
			}

			var deletedOrders = '';
			deletedOrders += '<table class="table table-striped">';
			deletedOrders += '	<thead>';
			deletedOrders += '		<tr><th class="w-25">Date</th><th class="w-25">Number</th><th class="w-25">Customer</th><th class="w-25 text-right">Amount</th></tr>';
			deletedOrders += '	</thead>';
			deletedOrders += '	<tbody>';
			$.each(rs, function(i, m) {
				if(!m.firstname) {m.firstname="";}
				if(!m.lastname) {m.lastname="";}
				deletedOrders += '<tr><td>'+m.date+'</td><td>'+m.id+'</td><td>'+m.firstname + " " + m.lastname+'</td><td class="text-right">'+m.ordertotal+'</td></tr>';
			});
			deletedOrders += '	</tbody>';
			deletedOrders += '</table>';

			$("#DeletedOrdersSummary").html(deletedOrders);
		},
		
		populatePrintStoreDetails: function(rs) {
			$("#printStoreName").html(rs.store.name);
			$("#printStoreAddress1").html(rs.store.address1);
			  
			if(rs.store.address2 && rs.store.address2 != "") {
				$("#printStoreAddress2").html(rs.store.address2 + "<br />");
			} else {
				$("#printStoreAddress2").remove();
			}
			  
			$("#printStoreCity").html(rs.store.city);
			$("#printStoreState").html(rs.store.state);
			$("#printStoreZip").html(rs.store.zip);
			$("#printStorePhone").html("Phone: " + rs.store.phone);
			$("#printStoreFax").html("Fax: " + rs.store.fax);
		},
		
		confirmExit: function() {
			if (reportsJS.unsaved) {
				return "New information not saved. Do you wish to leave the page?";
			}
		}
};
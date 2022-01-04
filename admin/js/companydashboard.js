var companydashboardJS = {
		noDataTmplt: '<p class="mb-0 text-danger">No Data Found</p>',
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
				$("#viewReportBtn").click();
			});

			
			$("#viewReportBtn").on("click", function(event) {
				event.preventDefault();
				event.stopPropagation();
				if($("#startDate").val() == "") {
					$("#startDate").datepicker('setDate', new Date());
				}
				if($("#endDate").val() == "") {
					$("#endDate").datepicker('setDate', new Date());
				}
				
				$.ajax('./api/v1/order/ordersReport?start='+$("#startDate").val()+'&end='+$("#endDate").val()+'&store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {companydashboardJS.displayOrdersReport(rs);});		
			});
		},

		displayOrdersReport: function(rs) {
			if (rs.length == 0) {
				$("#ordersReportTableDiv").html(companydashboardJS.noDataTmplt);
				return false;
			} else {
				var table = '<table id="ordersReportTable" class="table table-bordered table-striped" style="width:100%">' +
							'	<thead>' +
							'		<tr>' +
							'			<th style="width:30%">Location</th>' +
							'			<th style="width:15%">Order Total</th>' +
							'			<th style="width:15%">Order Tax</th>' +
							'			<th style="width:20%">Cost of Goods</th>' +
							'			<th style="width:20%">Margin</th>' +
							'		</tr>' +
							'	</thead>' +
							'</table>';
				$("#ordersReportTableDiv").html(table);
			}
			
			var t = $("#ordersReportTable").DataTable( {
				"autoWidth": false,
				"retrieve": true,
				"responsive": true,
				"dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
				"<'row'<'col-sm-12 text-right'B>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
				"buttons": ['copyHtml5','excelHtml5','csvHtml5']
		    } );

        	orders = [];
        	$.each(rs, function(i, m){
        		orders.push([
        			m.nodeName,
        			m.orderTotal,
        			m.orderTax,
        			m.cog,
        			m.margin
        		]);
        	});

			t.clear();
			t.rows.add(orders);
			t.order( [ 0, 'asc' ] );
			t.draw(false);
		},
};
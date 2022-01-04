var inventoryHistoryJS = {
		init: function() {
			$(window).on('storelistloaded', function (e) {
				$("#storeDropdown").val(utilitiesJS.getQueryVariable("store"));
			});
			
			$.ajax('./api/v1/inventory/history/'+utilitiesJS.getQueryVariable("id")+'?store='+utilitiesJS.getQueryVariable("store"), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
				inventoryHistoryJS.displayDetails(rs);
			});
		},
		
		displayDetails: function(rs) {
			console.log(rs);
			if(rs.detail.length > 0) {
				$("#vendorName").text(rs.detail[0].vendorname);
				$("#vendorNamePrint").text(rs.detail[0].vendorname);
				$("#invoiceNum").text("Invoice #" + rs.detail[0].number);
				$("#invoiceNumPrint").text("Invoice #" + rs.detail[0].number);
				$("#invoiceDate").text(rs.detail[0].date);
				$("#invoiceDatePrint").text(rs.detail[0].date);
				if(rs.detail[0].paid == 1) {
					$("#invoiceStatus").text("Paid");
					$("#invoiceStatus").addClass("text-success");
					$("#invoiceStatusPrint").text("Paid");
				} else {
					$("#invoiceStatus").text("Unpaid");
					$("#invoiceStatus").addClass("text-danger");
					$("#invoiceStatusPrint").text("Unpaid");
				}
				
			}
			var invoiceItems;
			var grandTotal = 0;
			$.each(rs.items, function(i, m) {
				if(!m.partnumber) {m.partnumber="";}
				if(!m.description) {m.description="";}
				if(!m.quantity) {m.quantity=0;}
				if(!m.cost) {m.cost=0;}
				if(!m.total) {m.total=0;}
				invoiceItems += '		<tr><td>'+m.partnumber+'</td><td>'+m.description+'</td><td>'+m.quantity+'</td><td class="text-right">'+Number(m.cost).toFixed(2)+'</td><td class="text-right">'+Number(m.total).toFixed(2)+'</td></tr>';
				grandTotal += Number(m.total);
			});
			invoiceItems += '		<tr><td colspan="4" class="text-right font-weight-bold">Total </td><td class="text-right font-weight-bold">'+grandTotal.toFixed(2)+'</td></tr>';
			$("#invoiceItemList").html(invoiceItems);

		}
};
var dashboardJS = {
		init: function() {
			$(window).on('storelistloaded', function (e) {
				var selectedStoreCookie = Cookies.get('storeSelected');
				if(selectedStoreCookie) {
					$("#storeDropdown").val(selectedStoreCookie);
				}
				$.ajax('./api/v1/order/open?store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
					dashboardJS.displayOpenOrders(rs);
				});
			});
			
			$(document).on("change","#storeDropdown",function(event) {
				Cookies.set("storeSelected", $("#storeDropdown").val(), { expires: 1/12, secure: true });
				$(window).trigger($.Event('storelistloaded'));
			});

			$(document).on("click", ".appointmentDecline", function(event){
				event.preventDefault();
				event.stopPropagation();
				var appId = $(this).data("appointment-id");
				var result = confirm("Are you sure you want to decline?");
				if (result) {
					$.ajax('./api/v1/appointment/decline/'+appId+'&store='+$("#storeDropdown").val(), {cache: false, type: 'PUT', headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
						$.ajax('./api/v1/order/open?store='+$("#storeDropdown").val(), {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
							$('#workOrderTable').dataTable().fnClearTable();
    						$('#workOrderTable').dataTable().fnDestroy();
    						$('#scheduledWorkOrderTable').dataTable().fnClearTable();
    						$('#scheduledWorkOrderTable').dataTable().fnDestroy();
    						$('#estimateTable').dataTable().fnClearTable();
    						$('#estimateTable').dataTable().fnDestroy();
    						$('#requestedAppointmentsTable').dataTable().fnClearTable();
    						$('#requestedAppointmentsTable').dataTable().fnDestroy();
							dashboardJS.displayOpenOrders(rs);
						});
						alert("Appointment declined successfully.");
					});
				}
			});
		},
		
		displayOpenOrders: function(rs) {
			$(document).ajaxStop(function () {
		        $('table.table').each(function() {
		            if ( ! $(this).hasClass("hidden") ) {
		                $(this).DataTable().columns.adjust()
		                                   .responsive.recalc();
		            }
		        })
		    });

			if(rs.orders.length > 0) {
				openWorkOrders = [];
				scheduledWorkOrders = [];
				$.each(rs.orders, function(i, m) {
					if(!m.firstname) {m.firstname="";}
					if(!m.lastname) {m.lastname="";}
					if(!m.year) {m.year="";}
					if(!m.make) {m.make="";}
					if(!m.model) {m.model="";}
					if(!m.trim) {m.trim="";}
					if(!m.name) {m.name="";}
					if(!m.status) {m.status="";}
					if(!m.phone1) {m.phone1="";}
					if(!m.promisedFormatted) {m.promisedFormatted="";}
					var rowClass;
					if(!m.minstopromised) {
						rowClass = "";
					} else if(m.minstopromised < 0) {
						rowClass = "table-danger";
					} else if(m.minstopromised < 60) {
						rowClass = "table-warning";
					} else {
						rowClass = "table-success";
					}

					if(m.status == "10") {
						m.status = "In Progress";
					} else if(m.status == "20") {
						m.status = "Waiting on Parts";
					} else if(m.status == "30") {
						m.status = "Waiting on Customer";
					} else if(m.status == "40") {
						m.status = "Pending Pickup";
					} else {
						m.status = "Scheduled";						
					}

					var name = m.firstname + " " + m.lastname;
					var vehicle = m.year + ' ' + m.make + ' ' + m.model + ' ' + m.trim;
					if(m.business_name) {
						name += '<br /><span class="businesname">'+m.business_name+'</span>';
					}
					if(m.license) {
						vehicle += '<br /><span class="font-weight-bold">License Plate:</span> ' + m.license;
					}

					var inspectionStatus = (m.inspectionreport_id)? 'Yes' : 'No';
					vehicle += '<br /><span class="font-weight-bold">Inspection Report:</span> '+ inspectionStatus;

					if(m.startdate && (new Date(m.startdate) > new Date())) {
						scheduledWorkOrders.push([
				            '<a href="workorderedit.php?orderId='+m.id+'&store='+$("#storeDropdown").val()+'">'+m.id+'</a>',
				            name,
				            vehicle,
				            m.phone1,
				            m.name,
				            m.status,
				            m.startdate
				        ]);
					} else {
						openWorkOrders.push([
				            '<a href="workorderedit.php?orderId='+m.id+'&store='+$("#storeDropdown").val()+'">'+m.id+'</a>',
				            name,
				            vehicle,
				            m.phone1,
				            m.name,
				            m.status,
				            m.promisedFormatted,
				            rowClass
				        ]);
					}
				});

				$("#workOrderList").show();
				$("#scheduledWorkOrderList").show();

				var t = $('#workOrderTable').DataTable( {
					"autoWidth": false,
					"responsive": true,
					"pageLength": 10,
			        "rowCallback": function( row, data, index ) {
			          if (data[7] != "") {
			            $('td', row).addClass(data[7]);
			          }
			        }
			    } );

			    var st = $('#scheduledWorkOrderTable').DataTable( {
					"autoWidth": false,
					"responsive": true
			    } );

			    t.rows.add(openWorkOrders);
				t.order( [[ 5, 'asc' ], [ 6, 'asc' ]] );
				t.columns.adjust().responsive.recalc().draw(false);
				st.rows.add(scheduledWorkOrders);
				st.order( [[ 6, 'asc' ]] );
				st.columns.adjust().responsive.recalc().draw(false);
			}

			if(rs.estimates.length > 0) {
				var e = $('#estimateTable').DataTable( {
					"autoWidth": false,
					"pageLength": 25
			    } );
				openEstimates = [];
				$.each(rs.estimates, function(i, m) {
					if(!m.firstname) {m.firstname="";}
					if(!m.lastname) {m.lastname="";}
					if(!m.year) {m.year="";}
					if(!m.make) {m.make="";}
					if(!m.model) {m.model="";}
					if(!m.trim) {m.trim="";}
					if(!m.phone1) {m.phone1="";}
					var name = m.firstname + " " + m.lastname;
					var vehicle = m.year + ' ' + m.make + ' ' + m.model + ' ' + m.trim;
					if(m.business_name) {
						name += '<br /><span class="businesname">'+m.business_name+'</span>';
					}
					if(m.license) {
						vehicle += '<br /><span class="font-weight-bold">License Plate:</span> ' + m.license;
					}

					var inspectionStatus = (m.inspectionreport_id)? 'Yes' : 'No';
					vehicle += '<br /><span class="font-weight-bold">Inspection Report:</span> '+ inspectionStatus;

					openEstimates.push([
			            '<a href="workorderedit.php?orderId='+m.id+'&store='+$("#storeDropdown").val()+'">'+m.id+'</a>',
			            name,
			            vehicle,
			            m.phone1
			        ]);
				});
				e.rows.add(openEstimates);
				e.order( [[ 1, 'asc' ]] );
				$("#estimateList").show();
				e.columns.adjust().responsive.recalc().draw(false);
			}

			if(rs.requestedAppointments.length > 0) {
				var ra = $('#requestedAppointmentsTable').DataTable( {
					"autoWidth": false,
					"responsive": true
				} );
				requested_appointments = [];
				$.each(rs.requestedAppointments, function(i, m) {
					var verifyUrl = 'workorder.php?startdate='+m.requested_date+'&fullName='+m.full_name+'&appointment='+m.id+'&phone='+m.phone+'&workRequested='+m.work_requested+'&email='+m.email; 
					var action = '<div class="btn-group" role="group">'+
									'<button id="btnGroupDrop3" type="button" class="btn btn-secondary dropdown-toggle form-control" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>'+
									'<div class="dropdown-menu" aria-labelledby="btnGroupDrop3">'+
										'<a class="dropdown-item" href="'+verifyUrl+'">Verify</a>'+
										'<a data-appointment-id="'+m.id+'" class="dropdown-item appointmentDecline" href="#">Decline</a>'+
									'</div>'+
								'</div>';

					requested_appointments.push([
						m.full_name,
						m.email,
						m.phone,
						m.work_requested,
						m.requested_date,
						action
					]);
				});
				ra.rows.add(requested_appointments);
				ra.order( [[ 4, 'asc' ]] ).draw( false );
				$("#requestedAppointmentsList").show();
			}
		}
};
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Dashboard | Flux Shop Manager</title>
  <?php include "includes/head.php"; ?>
</head>

<body class="fixed-nav sticky-footer bg-light" id="page-top">
  <?php include "includes/navigation.php"; ?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb d-print-none">
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-md-10 col-sm-12">
          <h1>Dashboard</h1>
        </div>
      </div>

      <div class="row d-print-none" id="requestedAppointmentsList" style="display:none">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Requested Appointments
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-md-12">						
							<table id="requestedAppointmentsTable" class="table table-bordered table-striped">
								<thead>
								<tr>
									<th style="width:20%">Customer</th>
									<th style="width:15%">Email</th>
									<th style="width:10%">Phone</th>
									<th style="width:30%">Work Requested</th>
									<th style="width:15%">Requested Date</th>
									<th style="width:10%">Action</th>
								</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>

      <div class="row d-print-none" id="workOrderList" style="display:none">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Open Work Orders
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-md-12">
							<table id="workOrderTable" class="table table-bordered table-striped">
								<thead>
								<tr>
									<th style="width:10%">Number</th>
									<th style="width:25%">Customer</th>
									<th style="width:25%">Vehicle</th>
									<th style="width:10%">Phone</th>
									<th style="width:10%">Tech</th>
									<th style="width:10%">Status</th>
									<th style="width:10%">Promised</th>
								</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>

      <div class="row d-print-none" id="scheduledWorkOrderList" style="display:none">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Scheduled Work Orders
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-md-12">						
							<table id="scheduledWorkOrderTable" class="table table-bordered table-striped">
								<thead>
								<tr>
									<th style="width:10%">Number</th>
									<th style="width:25%">Customer</th>
									<th style="width:25%">Vehicle</th>
									<th style="width:10%">Phone</th>
									<th style="width:10%">Tech</th>
									<th style="width:10%">Status</th>
									<th style="width:10%">Scheduled</th>
								</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>

      <div class="row d-print-none" id="estimateList" style="display:none">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Open Estimates
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-md-12">
							<table id="estimateTable" class="table table-bordered table-striped">
								<thead>
								<tr>
									<th style="width:15%">Number</th>
									<th style="width:35%">Customer</th>
									<th style="width:35%">Vehicle</th>
									<th style="width:15%">Phone</th>
								</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>

	 </div>
    </div>

	<?php include "includes/footer.php"; ?>
    <script src="js/dashboard.js"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	utilitiesJS.storeNavigation();
    	dashboardJS.init();
    })(jQuery);
    </script>
</body>

</html>


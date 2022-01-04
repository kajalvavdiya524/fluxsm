<!DOCTYPE html>
<html lang="en">

<head>
  <title>Company Dashboard | Flux Shop Manager</title>
  <?php include "includes/head.php"; ?>
</head>

<body class="fixed-nav sticky-footer bg-light" id="page-top">
  <?php include "includes/navigation.php"; ?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb d-print-none">
        <li class="breadcrumb-item">
          <a href="/admin/index.php">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Company Dashboard</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1>Company Dashboard</h1>
        </div>
      </div>
      

      <div class="row" id="OrdersReport">
		<div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Company Sales
			  </div>
			  <div class="card-body">
			    <div class="card-text">
			    	<div class="row">
						<div class="col-md-6" id="reportOptions">
							<div class="input-daterange">
								<div class="row">
									<div class="form-group col-md-6">
										<label for="reportType" id="startDateLabel">Start Date</label> 
									    <input id="startDate" type="text" class="form-control date-picker" data-date-format="yyyy-mm-dd" value="">
									 </div>
									 <div class="form-group col-md-6">
									    <label for="reportType" id="endDateLabel">End Date</label> 
									    <input id="endDate" type="text" class="form-control date-picker" data-date-format="yyyy-mm-dd" value="">
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mt-3">
							<a href="#" id="viewReportBtn" class="btn btn-primary form-control d-print-none">View Report</a>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-md-12" id="ordersReportTableDiv">
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
    <script src="js/companydashboard.js"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	utilitiesJS.storeNavigation();
    	companydashboardJS.init();
    })(jQuery);
    </script>
</body>

</html>


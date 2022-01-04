<!DOCTYPE html>
<html lang="en">

<head>
  <title>Customer List | Flux Shop Manager</title>
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
        <li class="breadcrumb-item active">Customers</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1>Customers</h1>
        </div>
      </div>
      
      <div class="row d-print-none table-responsive">
      	<div class="col-12 text-right">
			<button class="btn btn-primary" id="customerListSearchBtn" data-target="#customerListSearchForm" data-toggle="collapse" type="button"><i class="fa fa-fw fa-search"></i> Search</button>
        </div>
        <div class="collapse col" id="customerListSearchForm">
        	<h4 class="card-title">Search for Customer</h4>
			<div class="row">
				<div class="col-lg-6">
					<div class="input-group">
						<input type="text" id="searchFirstName" class="form-control form-control-lg" placeholder="First name">
					</div>
				</div>
				<div class="col-lg-6">
					<div class="input-group">
						<input type="text" id="searchLastName" class="form-control form-control-lg" placeholder="Last name">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="input-group">
						<input type="text" id="searchBusiness" class="form-control form-control-lg" placeholder="Business Name">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-6">
					<div class="input-group">
						<input type="text" id="searchPhone" class="form-control form-control-lg" placeholder="Phone Number">
					</div>
				</div>
				<div class="col-lg-6">
					<div class="input-group">
						<input type="text" id="searchInvoice" class="form-control form-control-lg" placeholder="Invoice Number">
					</div>
				</div>
			</div>
			<p></p>
			<div class="row">
				<div class="col-lg-6">
					<a href="#" id="customerSearchBtn" class="btn btn-primary form-control form-control-lg">Search</a>
				</div>
			</div>
        </div>
        <table class="table table-striped">
	        <thead>
		        <tr>
		        	<th>Name</th>
		        	<th>Phone</th>
		        	<th>Address</th>
		        	<th>City</th>
		        	<th>State</th>
		        	<th>Zip</th>
		       	</tr>
	       	</thead>
	       	<tbody id="customerTableBody">
	     	</tbody>
        </table>
      </div> 
      <div id="paginationControls">
	      
      </div>
	</div>


    <?php include "includes/footer.php"; ?>
    <script src="js/customerlist.js"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	utilitiesJS.storeNavigation();
    	customerListJS.init();
    })(jQuery);
    </script>
</body>

</html>


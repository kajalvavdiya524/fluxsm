<!DOCTYPE html>
<html lang="en">

<head>
  <title>Vendor Invoice History | Flux Shop Manager</title>
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
        <li class="breadcrumb-item active">Vendor Invoice</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1 id="vendorName"></h1>
          <h4 id="invoiceNum"></h4>
          <h5 id="invoiceDate"></h5>
          <h5 id="invoiceStatus"></h5>
        </div>
      </div>
      
      <div class="row">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header d-print-none">
			    Details
			  </div>
			  <div class="card-body">
			    <div id="orderItemContainer" class="card-text table-responsive">
			    	<div class="d-print-block col-print-4  text-center">
				    	<h2 id="vendorNamePrint"></h2>
			    	</div>
			    	<div class="d-print-block col-print-4  text-center">
				    	<h2 id="invoiceNumPrint"></h2>
				    	<h2 id="invoiceStatusPrint"></h2>
			    	</div>
			    	<div class="d-print-block col-print-4  text-center">
				    	<h2 id="invoiceDatePrint"></h2>
			    	</div>
					<table class="table table-striped">
						<thead>
							<tr>
								<th class="w-25">Part Number</th>
								<th class="w-30">Description</th>
								<th class="w-15">Quantity</th>
								<th class="w-15 text-right">Unit Price</th>
								<th class="w-15 text-right">Total Price</th>
							</tr>
						</thead>
						<tbody id="invoiceItemList">
						</tbody>
					</table>
				</div>
			  </div>
			</div>
        </div>
      </div>
      
	 </div>
    </div>
    
	<?php include "includes/footer.php"; ?>
    <script src="js/inventoryhistory.js"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	utilitiesJS.storeNavigation();
    	inventoryHistoryJS.init();
    })(jQuery);
    </script>
</body>

</html>
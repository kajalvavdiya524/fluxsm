<!DOCTYPE html>
<html lang="en">

<head>
  <title>Reports | Flux Shop Manager</title>
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
        <li class="breadcrumb-item active">Reports</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1>Reports</h1>
        </div>
      </div>
      
      <div class="row d-print-none">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Report Details
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-md-6">
							<label for="reportType">Type</label> 
							<select name="reportType" class="form-control" id="reportType">
							  <option value="3">Accounts Payable</option>
							  <option value="2">Accounts Receivable</option>
							  <option value="10">Best Seller</option>
							  <option value="9">Deleted Orders</option>
							  <option value="11">Inventory Dollars</option>
							  <option value="7">Inventory Items Sold</option>
							  <option value="4">Low Margin Invoices</option>
							  <option value="8">Outside Purchase Tires</option>
							  <option value="1" selected="selected">Posted Totals by Invoice</option>
							  <option value="5">Sales Tax Exempt</option>
							  <option value="6">Technician Productivity</option>
							</select>
							<div class="mt-3">
								<a href="#" id="viewReportBtn" class="btn btn-primary form-control d-print-none">View Report</a>
							</div>
						</div>
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
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row" id="postedTotalReport" style="display:none;">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Posted Totals by Invoice <span id="totalInvoices" class="badge badge-secondary"></span>
			    <div id="printedOn" class="d-print-block"></div>
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-sm-6 col-print-7">
							<canvas id="postedTotalsChart" width="100%" height="60"></canvas>
						</div>
						<div class="col-sm-6 col-print-5" id="postedTotalsList">
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 col-print-4" id="reportcol1">
						</div>
						<div class="col-md-4 col-print-4" id="reportcol2">
						</div>
						<div class="col-md-4 col-print-4" id="reportcol3">
						</div>
					</div>
					<div class="row table-responsive">
						<div class="form-group col-md-12"  id="postedTotalsInvoiceSummary">
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row" id="ARReport" style="display:none;">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold d-print-none">
			    Accounts Receivable
			  </div>
			  <div class="card-body">
			  	<div class="d-print-block">
					<div class="row no-gutters">
						<div class="media mb-3" id="printHeader">
							<img class="mr-3" src="MLT-Logo.jpg" style="max-height:50px">
				  			<div class="media-body text-center">
								<span id="printStoreName"></span>
								<span id="printStoreAddress1"></span>
		  						<span id="printStoreAddress2"></span>
		  						<span id="printStoreCity"></span>, <span id="printStoreState"></span> <span id="printStoreZip"></span>
		  						<br />
		  						<span id="printStorePhone"></span>
								<span id="printStoreFax"></span>
				  			</div>
						</div>
						
						<div class="col-print-4" id="printCustomer">
						</div>
				
						<div class="col-print-5" id="printVehicle">
						</div>
				
						<div class="col-print-3" id="printDetail">
						</div>
					</div>
	  			</div>
			    <div class="card-text">
					<div class="row table-responsive">
						<div class="form-group col-md-12"  id="ARSummary">
						</div>
					</div>
					<div class="col-md-12 d-print-block text-center">
						Thank you for your business!
					</div>
					<div class="form-group col-md-12 d-print-none">
						<button class="btn btn-primary" id="ARBtn" type="button">Update</button>
					</div> 
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row" id="APReport" style="display:none;">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Accounts Payable
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row table-responsive">
						<div class="form-group col-md-12"  id="APSummary">
						</div>
						<div class="form-group col-md-12"  id="APMessageContainer">
						</div>
						<div class="form-group col-md-12">
							<button class="btn btn-primary" id="APBtn" type="button">Update</button>
						</div> 
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row" id="LowMarginReport" style="display:none;">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Low Margin Invoices
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row table-responsive">
						<div class="form-group col-md-12"  id="LowMarginSummary">
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row" id="SalesTaxExemptReport" style="display:none;">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Sales Tax Exempt Invoices
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row table-responsive">
						<div class="form-group col-md-12"  id="SalesTaxExemptSummary">
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row" id="TechProductivitytReport" style="display:none;">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Technician Productivity
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row table-responsive">
						<div class="form-group col-md-12"  id="TechProductivitySummary">
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>

      <div class="row" id="InventoryItemsSoldReport" style="display:none;">
		<div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Inventory Items Sold
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="col-md-12" id="inventoryItemsSoldTableDiv">
	  					</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>

      <div class="row" id="BestSellerReport" style="display:none;">
		<div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Best Seller
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="col-md-12" id="bestSellerTableDiv">
	  					</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row" id="OutsidePurchaseTiresSoldReport" style="display:none;">
		<div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Outside Purchase Tires Sold
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="col-md-12" id="outsidePurchaseTiresSoldTableDiv">
	  					</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>

      <div class="row" id="InventoryDollarsReport" style="display:none;">
		<div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Inventory Dollars
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="col-md-12" id="inventoryDollarsTableDiv">
	  					</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row" id="DeletedOrdersReport" style="display:none;">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header font-weight-bold">
			    Deleted Orders
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-md-12"  id="DeletedOrdersSummary">
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
    <script src="js/reports.js"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	utilitiesJS.storeNavigation();
    	reportsJS.init();
    })(jQuery);
    </script>
</body>

</html>


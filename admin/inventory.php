<!DOCTYPE html>
<html lang="en">

<head>
  <title>Inventory | Flux Shop Manager</title>
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
        <li class="breadcrumb-item active">Inventory</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1>Inventory</h1>
        </div>
      </div>
      
      <div class="col-12 text-right">
		<button class="btn btn-primary" id="addInvoiceBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add Invoice</button>
		<button class="btn btn-secondary fluxur1" id="addPartNumberBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add Part Number</button>
      </div>
      <div id="tableDiv">
	  </div>
            
	 </div>
    </div>
    
    <div class="modal fade" id="inventoryInfoModal" tabindex="-1" role="dialog" aria-labelledby="vendorInfoModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Add Vendor Invoice</h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="inventoryInfoFormMessageContainer"></div>
					<form id="inventoryForm">
						<div class="form-row">
							<div class="form-group col-md-6">
								<select class="form-control" id="vendorDropdown" required>
						  		</select>
							</div>
							<div class="form-group col-md-6">
								<input type="text" class="form-control" name="invoicenumber" id="txtInvoiceNumber" placeholder="Invoice Number" required>
							</div>
						</div>
						
						<div id="invoiceItemContainer">
							<div id="firstOrderItem" class="mb-3 mb-0-print invoiceItemSerialize">
								<div class="form-row">
									<div class="form-group col-md-4">
										<input type="text" class="form-control partnumberList" placeholder="Part Number" name="partnumber" />
										<input type="hidden" class="partnumberListSelected" value="" />
									</div>
									<div class="form-group col-md-8">
										<input type="text" class="form-control-plaintext itemDescription" readonly name="itemdescription" id="txtInvoiceItemDescription">
									</div>
								</div>
								<div class="form-row">
									<div class="form-group col-md-5">
										<input type="text" class="form-control itemQuantity" placeholder="Quantity" name="itemquantity" id="txtInvoiceItemQuantity">
									</div>
									<div class="form-group col-md-5">
										<div class="input-group mb-2 mb-sm-0">
									        <div class="input-group-addon d-print-none">$</div>
									        <input type="text" class="form-control itemCost" id="txtInvoiceItemCost" placeholder="Cost">
										</div>
									</div>
									<div class="form-group col-md-2">
										<div class="input-group-btn"> 
											<button class="btn btn-danger remove" type="button"><i class="fa fa-fw fa-close"></i> </button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<a href="#" id="invoiceItemAddBtn" class="btn btn-primary form-control d-print-none">Add Item</a>
					</form>
					<div id="hiddenInvoiceItemRow" style="display:none">
						<div class="form-row">
							<div class="form-group col-md-4">
								<input type="text" class="form-control partnumberList" placeholder="Part Number" name="partnumber" />
								<input type="hidden" class="partnumberListSelected" value="" />
							</div>
							<div class="form-group col-md-8">
								<input type="text" class="form-control-plaintext itemDescription" readonly name="itemdescription">
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-5">
								<input type="text" class="form-control itemQuantity" placeholder="Quantity" name="itemquantity">
							</div>
							<div class="form-group col-md-5">
								<div class="input-group mb-2 mb-sm-0">
							        <div class="input-group-addon d-print-none">$</div>
							        <input type="text" class="form-control itemCost" placeholder="Cost">
								</div>
							</div>
							<div class="form-group col-md-2">
								<div class="input-group-btn"> 
									<button class="btn btn-danger remove" type="button"><i class="fa fa-fw fa-close"></i> </button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-row mt-3">
						<div class="form-group col-md-12 text-right">
							Total: $<span id="invoiceTotal">0.00</span>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
					<a class="btn btn-primary" id="invoiceSaveBtn" href="#">Save</a>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="inventoryEditModal" tabindex="-1" role="dialog" aria-labelledby="inventoryEditModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="inventoryModalTitle">Edit Part Number</h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="inventoryEditFormMessageContainer"></div>
					<form id="inventoryEditForm">
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="txtFirstname">Manufacturer</label> 
								<input type="text" class="form-control IEditSerialize" name="manufacturer" id="txtIEditManufacturer" required="">
							</div>
							<div class="form-group col-md-6">
								<label for="txtFirstname">Part Number</label> 
								<input type="text" class="form-control IEditSerialize" name="partnumber" id="txtIEditPartnumber" required="">
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="txtFirstname">Stores</label>
								<select class="form-control select IEditSerialize" name="storeID" id="txtIEditStore" multiple="multiple" required="">
								</select>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="txtFirstname">Description</label> 
								<input type="text" class="form-control IEditSerialize" name="description" id="txtIEditDescription" required="">
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="txtFirstname">Cost</label> 
								<div class="input-group mb-2 mb-sm-0">
							        <div class="input-group-addon d-print-none">$</div>
							        <input type="text" class="form-control IEditSerialize numeric_only" name="cost" id="txtIEditCost" required="">
								</div>
							</div>
							<div class="form-group col-md-6">
								<label for="txtFirstname">Retail Price</label> 
								<div class="input-group mb-2 mb-sm-0">
							        <div class="input-group-addon d-print-none">$</div>
							        <input type="text" class="form-control IEditSerialize numeric_only" name="retail" id="txtIEditRetail" required="">
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="txtFirstname">On Hand</label> 
								<input type="text" class="form-control IEditSerialize numeric_only" name="quantity" id="txtIEditQuantity" required="">
							</div>
							<div class="form-group col-md-6">
								<label for="txtFirstname">Reserved</label> 
								<input type="text" class="form-control IEditSerialize numeric_only" name="reserved" id="txtIEditReserved" required="">
								<input type="hidden" class="IEditSerialize" name="inventory_id" id="txtIEditId">
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button class="btn btn-secondary" id="IEditCancelBtn" type="button" data-dismiss="modal">Cancel</button>
					<a class="btn btn-primary" id="IEditUpdateBtn" href="#">Update</a>
				</div>
			</div>
		</div>
	</div>
    
	<?php include "includes/footer.php"; ?>
	<script src="js/inventory.js"></script>
	<script src="vendor/bootstrap/js/bootstrap-multiselect.js"></script>
    <script>
	    (function($) {
	    	inventoryJS.init();
	    	utilitiesJS.sessionCheck();
	    	utilitiesJS.storeNavigation();
	    })(jQuery);

		$(document).ready(function() {
			$('.select').multiselect({
				includeSelectAllOption: true,
				onChange: function () {
					$('.multiselect').addClass('has-success');
				}
			});
		});
    </script>
</body>

</html>


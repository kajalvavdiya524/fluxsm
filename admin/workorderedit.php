<!DOCTYPE html>
<html lang="en">

<head>
  <title>Edit Work Order | Flux Shop Manager</title>
  <?php include "includes/head.php"; ?>
</head>

<body class="fixed-nav sticky-footer bg-light" id="page-top">
  <?php include "includes/navigation.php"; ?>
  <div class="content-wrapper">
  	<form id="workOrderForm">
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
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb d-print-none">
        <li class="breadcrumb-item">
          <a href="/admin/index.php">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Work Order</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-8">
          <h1 id="orderId"></h1>
        </div>
        <div class="col-4 text-right">
        	<button class="btn btn-secondary fluxur1" id="deleteOrderBtn">Delete Order</button>
        </div>
      </div>
      
           <div class="row d-print-none">
        <div id="ticketTypeCard" class="col">
			<div class="card mb-3">
			  <div class="card-header">
			    Details
			    <span class="float-right" id="referenceNo"></span>
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-md-6">
							<div class="row">
								<div class="form-group col-md-12">
									<label for="ticketType">Type</label> 
									<select name="tickettype" class="form-control workOrderSerialize" id="ticketType">
									  <option value="E">Estimate</option>
									  <option value="W" selected>Work Order</option>
									  <option value="A" >Appointment</option>
									</select>
								</div>
							</div>
							
							<div id="workOrderDetails2">
								<div class="row">
									<div class="form-group col-md-6">
										<label for="startDate">Start Date</label>
										<input name="startdate" id="startDate" type="text" class="form-control input-date workOrderSerialize" value="">
									</div>
									<div class="form-group col-md-6">
										<label for="startTime">Start Time</label> 
										<select name="starttime" id="startTime" class="form-control workOrderSerialize">
										  <option value="">None</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-md-12">
										<label for="startTime">Duration</label> 
										<select name="duration" id="duration" class="form-control workOrderSerialize">
										  <option value="15">15 Minutes</option>
										  <option value="30">30 Minutes</option>
										  <option value="45">45 Minutes</option>
										  <option value="60">1 Hour</option>
										  <option value="75">1.25 Hours</option>
										  <option value="90">1.5 Hours</option>
										  <option value="105">1.75 Hours</option>
										  <option value="120">2 Hours</option>
										  <option value="135">2.25 Hours</option>
										  <option value="150">2.5 Hours</option>
										  <option value="165">2.75 Hours</option>
										  <option value="180">3 Hours</option>
										  <option value="195">3.25 Hours</option>
										  <option value="210">3.5 Hours</option>
										  <option value="225">3.75 Hours</option>
										  <option value="240">4 Hours</option>
										  <option value="255">4.25 Hours</option>
										  <option value="270">4.5 Hours</option>
										  <option value="285">4.75 Hours</option>
										  <option value="300">5 Hours</option>
										  <option value="315">5.25 Hours</option>
										  <option value="330">5.5 Hours</option>
										  <option value="345">5.75 Hours</option>
										  <option value="360">6 Hours</option>
										  <option value="375">6.25 Hours</option>
										  <option value="390">6.5 Hours</option>
										  <option value="405">6.75 Hours</option>
										  <option value="420">7 Hours</option>
										  <option value="435">7.25 Hours</option>
										  <option value="450">7.5 Hours</option>
										  <option value="465">7.75 Hours</option>
										  <option value="480">8 Hours</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6" id="workOrderDetails">
							<div class="row">
								<div class="form-group col-md-6">
									<label for="customerAddFirstName">Technician</label>
									<select name="technician_id" id="teamMemberDropdown" multiple="multiple" class="form-control workOrderSerialize">
									</select>
								</div>
								<div class="form-group col-md-6">
									<label for="customerAddFirstName">Status</label> 
									<select name="ticketstatus" id="ticketStatus" class="form-control workOrderSerialize">
									  <option value="00">Scheduled</option>
									  <option value="10">In Progress</option>
									  <option value="20">Waiting on Parts</option>
									  <option value="30">Waiting on Customer</option>
									  <option value="40">Pending Pickup</option>
									  <option value="50">Missed Appointment</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="form-group col-md-12">
									<label for="customerAddFirstName">Promised Time</label> 
									<select name="promisedtime" id="promisedTime" class="form-control workOrderSerialize">
									  <option value="">None</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="row d-print-none">
        <div class="col-lg-6">
			<div class="card mb-3" id="customerCard">
			  <div class="card-header">
			    Customer
			  </div>
			  <div class="card-body">
			    <div id="customerDetails"></div>
			    <input type="hidden" id="selectedCustomerId" value="" />
			    <input type="hidden" id="selectedCustomerAddress1" value="" />
				<input type="hidden" id="selectedCustomerCity" value="" />
				<input type="hidden" id="selectedCustomerZip"  value="" />
			    <button class="btn btn-primary" type="button" id="customerInfoBtn" data-toggle="modal" data-target="#editCustomerModal">Edit</button>
			  </div>
			</div>
        </div>
        <div class="col-lg-6">
			<div class="card mb-3" id="vehicleCard">
			  <div class="card-header">
			    Vehicle
			  </div>
			  <div class="card-body">
			  	<div id="vehicleDetails"></div>
			  	<div class="form-group mb-3 mt-3" id="orderMileageContainer">
					<label for="vehicleAddMileage">Current Mileage</label>
					<input type="tel" class="form-control" name="currentMileage" id="currentMileage">
				</div>
			  	<input type="hidden" value="" id="selectedVehicleId" />
			  	<input type="hidden" value="" id="selectedVehicleYear" />
				<input type="hidden" value="" id="selectedVehicleMake" />
				<input type="hidden" value="" id="selectedVehicleModel" />
				<input type="hidden" value="" id="selectedVehicleTrim" />
				<input type="hidden" value="" id="selectedVehicleVin" />
				<input type="hidden" value="" id="selectedVehicleMileage" />
				<input type="hidden" value="" id="selectedVehicleLicense" />
				<input type="hidden" value="" id="selectedVehicleFleetNum" />
			  	 <button class="btn btn-primary" type="button" id="vehicleInfoBtn" data-toggle="modal" data-target="#editVehicleModal">Edit</button>
			  	<button class="btn btn-secondary showVehicleHistory" type="button" id="vehicleInfoShowHistory">Show History</button>
			  	<div class="collapse table-responsive" id="vehicleHistory">No History Found</div>
		      </div>
			</div>
        </div>
      </div>
      
      <div class="row">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header d-print-none">
			    Line Items
			  </div>
			  <div id="templateSection" class="card-body border border-top-0 border-right-0 border-left-0 d-print-none">
			  	<div class="form-row align-items-center mb-4">
			    	<div class="col-md-4 offset-md-2 templateContainer">
						<input type="text" class="form-control templateList" placeholder="Add From Template" name="template" maxlength="100" />
						<input type="hidden" id="templateListSelected" value="" />
					</div>
					<div class="col-md-3">
						<button class="btn btn-primary form-control disabled" id="temaplateAddBtn" type="button">Add</button>
					</div>
					<div class="col-md-3">
						<div class="btn-group" role="group">
							<button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle form-control" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      							More
							</button>
							<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
								<a class="dropdown-item" id="templateCreateBtn" href="#">Create New Template</a>
								<a class="dropdown-item disabled" id="templateDeleteBtn" href="#">Delete Template</a>
							</div>
						</div>
    				</div>					
				</div>				
			  </div>
			  <div class="card-body">
			    <div id="orderItemContainer" class="card-text">
			    	  <div id="printHeader" class="form-row align-items-center mb-3 d-print-inline">
					    <div class="col-print-2">
					      Part Number
					    </div>
					    <div class="col-print-4">
					      Description
					    </div>
					    <div class="col-print-2">
					      Quantity
					    </div>
					    <div class="col-print-2">
					      Unit Price
					    </div>
					    <div class="col-print-2">
					      Total Price
					    </div>
					  </div>
					  <div id="firstOrderItem" class="form-row align-items-center mb-3 mb-0-print orderItemSerialize">
					    <div class="col-md-2 d-print-none">
					      <select class="form-control itemtype priceChange" id="itemTypeDropdown">
							  <option selected>Type</option>
						  </select>
					    </div>
					    <div class="col-md-2 d-print-none">
						  <select class="form-control taxbracket priceChange" id="taxRateDropdown">
							  <option selected>Type</option>
						  </select>
					    </div>
					    <div class="col-md-1 col-print-2">
					        <div class="d-print-inline orderItemPartNumberPrint"></div>
					        <input name="partnum" type="text" class="form-control orderItemPartNumber d-print-none" placeholder="Part #">
					    </div>
					    <div class="col-md-2 col-print-4">
					        <div class="d-print-inline orderItemDescriptionPrint"></div>
					        <input type="text" class="form-control orderItemDescription d-print-none" placeholder="Description">
					    </div>
					    <div class="col-md-1 col-print-2">
					        <input type="text" class="form-control priceChange orderItemQuantity" placeholder="Quantity">
					    </div>
					    <div class="col-md-2 col-print-2">
					      <div class="input-group mb-2 mb-sm-0">
					        <div class="input-group-addon d-print-none">$</div>
					        <input type="text" class="form-control priceChange orderItemRetail" placeholder="Retail">
					      </div>
					    </div>
					    <div class="col-md-1 col-print-2">
					        <input type="text" readonly class="form-control-plaintext totalPrice" placeholder="$0.00">
					        <input type="hidden" class="taxPrice">
					    </div>
					    
					    <div class="col-md-1 d-print-none">
					      <div class="input-group mb-2 mb-sm-0">
					        <div class="input-group-btn"> 
				              <button class="btn btn-danger remove" type="button"><i class="fa fa-fw fa-close"></i> </button>
				            </div>
					      </div>
					    </div>
					    
					    

					    <div class="col-md-4 dotrequired d-print-none" style="display:none;">
					        <input type="text" class="form-control orderItemDotNumber" placeholder="DOT Number">
					    </div>
					    <div class="col-md-2 outsidepurchase d-print-none" style="display:none;">
					        <input type="text" class="form-control orderItemInvoiceNumber" placeholder="Invoice #">
					    </div>
					    <div class="col-md-4 nonlabor nonlaborfirst d-print-none" style="display:none;">
					        <select name="vendor" id="vendorDropdown" class="form-control orderItemVendor">
							  <option value="" selected>Vendor</option>
							</select>
					    </div>
					    <div class="col-md-2 nonlabor d-print-none" style="display:none;">
					      <div class="input-group mb-2 mb-sm-0">
					        <div class="input-group-addon">$</div>
					        <input type="text" class="form-control orderItemCost" placeholder="Cost">
					      </div>
					    </div>
					    <div class="col-md-1 nonlabor d-print-none" style="display:none;">
					        <input type="text" readonly class="form-control-plaintext totalItemCost" placeholder="$0.00">
					    </div>

					  </div>
				</div>
			    <a href="#" id="orderItemAddBtn" class="btn btn-primary form-control d-print-none">Add Item</a>
			  </div>
			</div>
        </div>
      </div>
      
      

		  <div id="hiddenPartRow" class="" style="display:none;">
		    <div class="col-md-2 d-print-none">
		      <select class="form-control itemtype priceChange" id="hiddenItemTypeDropdown">
				  <option selected>Type</option>
			  </select>
		    </div>
		    <div class="col-md-2 d-print-none">
			  <select class="form-control taxbracket priceChange" id="hiddenTaxRateDropdown">
				  <option selected>Type</option>
			  </select>
		    </div>
		    <div class="col-md-1 col-print-2">
		        <div class="d-print-inline orderItemPartNumberPrint"></div>
		        <input name="partnum" type="text" class="form-control orderItemPartNumber d-print-none" placeholder="Part #">
		    </div>
		    <div class="col-md-2 col-print-4">
		        <div class="d-print-inline orderItemDescriptionPrint"></div>
		        <input type="text" class="form-control orderItemDescription d-print-none" placeholder="Description">
		    </div>
		    <div class="col-md-1 col-print-2">
		        <input type="text" class="form-control priceChange orderItemQuantity" placeholder="Quantity">
		    </div>
		    <div class="col-md-2 col-print-2">
		      <div class="input-group mb-2 mb-sm-0">
		        <div class="input-group-addon d-print-none">$</div>
		        <input type="text" class="form-control priceChange orderItemRetail" placeholder="Retail">
		      </div>
		    </div>
		    <div class="col-md-1 col-print-2">
		        <input type="text" readonly class="form-control-plaintext totalPrice" placeholder="$0.00">
		        <input type="hidden" class="taxPrice">
		    </div>
		    
		    <div class="col-md-1 d-print-none">
		      <div class="input-group mb-2 mb-sm-0">
		        <div class="input-group-btn"> 
	              <button class="btn btn-danger remove" type="button"><i class="fa fa-fw fa-close"></i> </button>
	            </div>
		      </div>
		    </div>
		    
		    

		    <div class="col-md-4 dotrequired d-print-none" style="display:none;">
		        <input type="text" class="form-control orderItemDotNumber" placeholder="DOT Number">
		    </div>
		    <div class="col-md-2 outsidepurchase d-print-none" style="display:none;">
		        <input type="text" class="form-control orderItemInvoiceNumber" placeholder="Invoice #">
		    </div>
		    <div class="col-md-4 nonlabor nonlaborfirst d-print-none" style="display:none;">
		        <select name="vendor" class="form-control orderItemVendor">
				  <option value="" selected>Vendor</option>
				</select>
		    </div>
		    <div class="col-md-2 nonlabor d-print-none" style="display:none;">
		      <div class="input-group mb-2 mb-sm-0">
		        <div class="input-group-addon">$</div>
		        <input type="text" class="form-control orderItemCost" placeholder="Cost">
		      </div>
		    </div>
		    <div class="col-md-1 nonlabor d-print-none" style="display:none;">
		        <input type="text" readonly class="form-control-plaintext totalItemCost" placeholder="$0.00">
		    </div>
					    
		  </div>

      
      
      <div class="row">
        <div class="form-group col-md-6 col-print-6">
			<div class="card mb-3">
			  <div class="card-header">
			    Customer Notes
			  </div>
			  <div class="card-body">
			    <div class="card-text d-print-none"><textarea id="txtCustomerNotes" name="customernotes" class="form-control workOrderSerialize" rows="3"></textarea></div>
			    <p id="printCustomerNotes" class="card-text d-print-inline"></p>
			  </div>
			  <div id="printDotNumbers" class="card-text d-print-block"></div>
			</div>
        </div>
        <div class="form-group col-md-6 col-print-6">
			<div class="card" id="totalsCard">
			  <div class="card-header">
			    Totals
			  </div>
			  <div class="card-body">
				  <div class="form-group row">
				    <label for="staticEmail" class="col-md-4 col-form-label col-print-5 text-right">Parts</label>
				    <div class="col-md-3 col-print-5">
				      <input type="text" readonly class="form-control-plaintext text-right totalsSummary" id="partsTotal" value="$0.00">
				    </div>
				  </div>
				  
				  <div class="form-group row">
				    <label for="staticEmail" class="col-md-4 col-form-label col-print-5 text-right">Labor</label>
				    <div class="col-md-3 col-print-5">
				      <input type="text" readonly class="form-control-plaintext text-right totalsSummary" id="laborTotal" value="$0.00">
				    </div>
				  </div>
				  
				  <div class="form-group row" id="discountsTotalFirstContainer" style="display:none">
				    <label for="staticEmail" class="col-md-4 col-form-label col-print-5 text-right">Discounts</label>
				    <div class="col-md-3 col-print-5">
				      <input type="text" readonly class="form-control-plaintext text-right totalsSummary" id="discountsTotalFirst" value="$0.00">
				    </div>
				  </div>
				  
				  <div class="form-group row">
				    <label for="staticEmail" class="col-md-4 col-form-label col-print-5 text-right">Tax</label>
				    <div class="col-md-3 col-print-5">
				      <input type="text" readonly class="form-control-plaintext text-right totalsSummary" id="taxTotal" value="$0.00">
				    </div>
				  </div>
				  
				  <div class="form-group row" id="feesTotalContainer" style="display:none">
				    <label for="staticEmail" class="col-md-4 col-form-label col-print-5 text-right">Fees</label>
				    <div class="col-md-3 col-print-5">
				      <input type="text" readonly class="form-control-plaintext text-right totalsSummary" id="feesTotal" value="$0.00">
				    </div>
				  </div>
				  
				  <div class="form-group row" id="discountsTotalSecondContainer" style="display:none">
				    <label for="staticEmail" class="col-md-4 col-form-label col-print-5 text-right">Discounts</label>
				    <div class="col-md-3 col-print-5">
				      <input type="text" readonly class="form-control-plaintext text-right totalsSummary" id="discountsTotalSecond" value="$0.00">
				    </div>
				  </div>
				  
				  <div class="form-group row" id="grandTotalRow">
				    <label for="staticEmail" class="col-md-4 col-form-label col-print-5 text-right font-weight-bold">Grand Total</label>
				    <div class="col-md-3 col-print-5">
				      <input type="text" readonly class="form-control-plaintext text-right totalsSummary font-weight-bold" id="grandTotal" value="$0.00">
				    </div>
				  </div>
				  
				  <div class="form-group row">
				  	<div class="col-md-4">
				  		<a href="#" id="workOrderUpdateBtn" class="btn btn-primary form-control d-print-none">Save</a>
				  	</div>
				  	<div class="col-md-4">
				  		<a href="#" id="printBtn" class="btn btn-secondary form-control d-print-none">Print</a>
				  	</div>
				  	<div class="col-md-4">
				  		<a href="#" id="workOrderUpdateAndPrintBtn" class="btn btn-secondary form-control d-print-none">Save & Print</a>
				  	</div>
				  </div>
				  
				  <div class="form-group row mt-3">
				  	<div class="col-md-12">
				  		<a id="invoiceBtn" class="btn btn-primary form-control d-print-none" data-toggle="collapse" href="#invoiceOrder" aria-expanded="false" aria-controls="invoiceOrder">Invoice</a>
				  	</div>
				  </div>
			    
			    <div id="orderMessageContainer" class="d-print-none"></div>
			    <div class="d-print-block mt-3" id="signatureLine">
			Signature:
		</div>
			  </div>
			</div>
        </div>
      </div>
      
      <div class="collapse row d-print-none" id="invoiceOrder">
      	<div class="col">
			<div class="card mb-3">
				<div class="card-header">
					Invoice
				</div>
				<div class="card-body">
					<label>Payment</label> 
					<div id="paymentContainer">
						<div class="form-row mb-3">
							<div class="col-md-4 d-print-none">
								<select id="paymentMethodDropdown" class="form-control paymentMethod">
								</select>
							</div>
							<div class="col-md-2 d-print-none">
								<div class="input-group mb-2 mb-sm-0">
									<div class="input-group-addon d-print-none">$</div>
							    	<input type="text" id="firstPaymentAmmount" class="form-control paymentAmount" placeholder="0.00">
								</div>
							</div>
							<div class="col-md-2 d-print-none paymentCheckContainer" style="display:none;">
								<div class="input-group mb-2 mb-sm-0">
							    	<input type="text" class="form-control paymentCheckNum" placeholder="Check Number">
								</div>
							</div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="col-md-2 d-print-none">
							<a href="#" id="addPaymentBtn" class="btn btn-primary form-control d-print-none">Add Payment</a>
						</div>
						<div class="col-md-2 d-print-none">
							<a href="#" id="workOrderCloseBtn" class="btn form-control d-print-none">Close Ticket</a>
						</div>
						
						<div class="col-md-2 d-print-none">
							<div class="input-group mb-2 mb-sm-0">
						    	<input type="text" class="form-control" placeholder="" id="orderMarginAmount" readonly>
						    	<div class="input-group-addon d-print-none">%</div>
							</div>
						</div>
					</div>
					
					<div class="form-row mt-3">
						<div class="col-md-6 d-print-none">
					    	<div id="invoiceMessageContainer"></div>
						</div>
					</div>
					
					<div id="hiddenPaymentRow" class="" style="display:none;">
						<div class="col-md-4 d-print-none">
							<select class="form-control paymentMethod">
							</select>
						</div>
						<div class="col-md-2 d-print-none">
							<div class="input-group mb-2 mb-sm-0">
								<div class="input-group-addon d-print-none">$</div>
						    	<input type="text" class="form-control paymentAmount" placeholder="0.00">
							</div>
						</div>
						<div class="col-md-2 d-print-none paymentCheckContainer" style="display:none;">
							<div class="input-group mb-2 mb-sm-0">
						    	<input type="text" class="form-control paymentCheckNum" placeholder="Check Number">
							</div>
						</div>
						<div class="col-md-4 d-print-none">
							<div class="input-group-btn"> 
				              <button class="btn btn-danger remove" type="button"><i class="fa fa-fw fa-close"></i> </button>
				            </div>
						</div>
				    </div>
				</div>
			</div>
		</div>
	  </div>


      <div class="row printFooter d-print-block">
      	<div id="workOrderPrintFooter" class="col-print-12">
		    <p>I hereby authorize the repair work herein set forth to be done along with the necessary materials
			and agree that you are not responsible for loss or damage to vehicle or articles left in vehicle. In
			case of fire, theft, or any other cause beyond your control or for delays caused by unavailability of
			parts or delays in parts shipments by the supplier or transporter. I hereby grant you and your
			employees permission to operate the vehicle herein described on streets, highways, or elsewhere
			for the purpose of testing and/or inspection.</p>
			
			<p>Unless otherwise provided by law, the seller (above named entity) hereby expressly disclaims all warranties, either express or implied,
			including any implied warranty of merchantability or fitness of particular purpose, and neither assumes nor authorizes any other person to
			assume for it any liability with the sale of said products. An express mechanic's lien is hereby acknowledged on above vehicle to secure the
			amount or repairs thereto.</p>
		</div>
		
		<div id="invoicePrintFooter"  class="d-print-none">
		    <p>I have received the above goods and/or services. If this is a credit card purchase, I agree to
			pay and comply with my cardholder agreement with the issuer.</p>
			
			<p>A finance charge of 1.5%/mo (18% annual rate) will be charged on all past due accounts. All parts are new unless otherwise specified.
			Re-check torque after the first 50 to 100 miles of service.</p>
		</div>
	</div>
	
	    </div>
	    </form>
    </div>
    
		<div class="modal fade" id="editCustomerModal" tabindex="-1"
			role="dialog" aria-labelledby="editCustomerModalLabel"
			aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Edit Customer</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="customerInfoFormMessageContainer"></div>
						<form id="customerInfoForm">
							<div class="form-row">
								<div class="form-group col-md-3">
									<label for="customerInfoUserType">User Type</label> 
									<select id="customerInfoUserType" name="usertype" class="form-control customerSerialize" >
										<option value="P">Personal</option>
										<option value="B">Business</option>
									</select>
								</div>
								<div class="form-group col-md-3">
									<label for="customerInfoTaxExempt">Tax Exempt</label>
									<select id="customerInfoTaxExempt" name="taxexempt" class="form-control customerSerialize" >
										<option value="0">No</option>
										<option value="1">Yes</option>
									</select>
								</div>
								<div class="form-group col-md-6">
									<label for="customerInfoTaxExemptNum">Tax Exempt Number</label>
									<input type="text" class="form-control customerSerialize" name="taxexemptnum" id="customerInfoTaxExemptNum" placeholder="Tax Exempt Number">
								</div>
							</div>
							<div class="form-row">
								<div id="businessNameContainer" class="form-group col-md-12" style="display:none">
									<label for="customerInfoBusinessName">Business Name</label> 
									<input type="text" class="form-control customerSerialize" name="businessname" id="customerInfoBusinessName" placeholder="First Name">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="customerInfoFirstName">First Name</label> 
									<input type="text" class="form-control contactSerialize" name="firstname" id="customerInfoFirstName" placeholder="First Name" required>
								</div>
								<div class="form-group col-md-6">
									<label for="customerInfoLastName">Last Name</label>
									<input type="text" class="form-control contactSerialize" name="lastname" id="customerInfoLastName" placeholder="Last Name" required>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-3">
									<label for="customerInfoPhone1Type">Phone Type</label> 
									<select id="customerInfoPhone1Type" name="phone1type" class="form-control contactSerialize" >
										<option value="C">Cell</option>
										<option value="W">Work</option>
										<option value="H">Home</option>
									</select>
								</div>
								<div class="form-group col-md-9">
									<label for="customerInfoPhone1">Phone 1</label>
									<input type="tel" class="form-control contactSerialize" name="phone1" id="customerInfoPhone1" placeholder="1234567890" required>
								</div>
							</div>

							<div class="form-group">
								<label for="customerInfoEmail">Email</label>
								<input type="email" class="form-control contactSerialize" name="email" id="customerInfoEmail" placeholder="Email">
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label class="custom-control custom-checkbox">
									  <input type="checkbox" name="internal" class="custom-control-input" id="customerInternal" value="1">
									  <span class="custom-control-indicator"></span>
									  <span class="custom-control-description">Internal Customer</span>
									</label>
								</div>
								<div class="form-group col-md-6">
									<label class="custom-control custom-checkbox">
									  <input type="checkbox" name="isPrimary" class="custom-control-input" id="customerPrimaryContact" value="1">
									  <span class="custom-control-indicator"></span>
									  <span class="custom-control-description">Primary Contact</span>
									</label>
								</div>
							</div>
							<div class="mb-4">
								<a href="#collapseAddress" id="customerInfoAddressToggleBtn" data-toggle="collapse" class="btn btn-outline-primary">Address</a>
								<a href="#collapsePhone" data-toggle="collapse" class="btn btn-outline-primary">Additional Contacts</a>
							</div>
							<div class="collapse" id="collapseAddress">
								<div class="form-group">
									<label for="customerInfoAddressLine1">Address 1</label>
									<input type="text" class="form-control customerSerialize workOrderRequired" name="addressline1" id="customerInfoAddressLine1" placeholder="1234 Main St" required>
								</div>
								<div class="form-group">
									<label for="customerInfoAddressLine2">Address 2</label>
									<input type="text" class="form-control customerSerialize" name="addressline2" id="customerInfoAddressLine2" placeholder="Apartment/Suite Number">
								</div>
								<div class="form-group">
									<label for="customerInfoAddressLine3">Address 3</label>
									<input type="text" class="form-control customerSerialize" name="addressline3" id="customerInfoAddressLine3" placeholder="Address Line 3">
								</div>
								<div class="form-row">
									<div class="form-group col-md-6">
										<label for="customerInfoCity">City</label>
										<input type="text" class="form-control customerSerialize workOrderRequired" name="city" id="customerInfoCity" required>
									</div>
									<div class="form-group col-md-4">
										<label for="customerInfoState">State</label>
										<select class="form-control customerSerialize workOrderRequired" name="state" id="customerInfoState"> 
												<option value="AL">Alabama</option>
												<option value="AK">Alaska</option>
												<option value="AZ">Arizona</option>
												<option value="AR">Arkansas</option>
												<option value="CA">California</option>
												<option value="CO">Colorado</option>
												<option value="CT">Connecticut</option>
												<option value="DE">Delaware</option>
												<option value="DC">District Of Columbia</option>
												<option value="FL">Florida</option>
												<option value="GA">Georgia</option>
												<option value="HI">Hawaii</option>
												<option value="ID">Idaho</option>
												<option value="IL">Illinois</option>
												<option value="IN">Indiana</option>
												<option value="IA">Iowa</option>
												<option value="KS">Kansas</option>
												<option value="KY">Kentucky</option>
												<option value="LA">Louisiana</option>
												<option value="ME">Maine</option>
												<option value="MD">Maryland</option>
												<option value="MA">Massachusetts</option>
												<option value="MI">Michigan</option>
												<option value="MN">Minnesota</option>
												<option value="MS">Mississippi</option>
												<option value="MO">Missouri</option>
												<option value="MT">Montana</option>
												<option value="NE">Nebraska</option>
												<option value="NV">Nevada</option>
												<option value="NH">New Hampshire</option>
												<option value="NJ">New Jersey</option>
												<option value="NM">New Mexico</option>
												<option value="NY">New York</option>
												<option value="NC">North Carolina</option>
												<option value="ND">North Dakota</option>
												<option value="OH" selected>Ohio</option>
												<option value="OK">Oklahoma</option>
												<option value="OR">Oregon</option>
												<option value="PA">Pennsylvania</option>
												<option value="RI">Rhode Island</option>
												<option value="SC">South Carolina</option>
												<option value="SD">South Dakota</option>
												<option value="TN">Tennessee</option>
												<option value="TX">Texas</option>
												<option value="UT">Utah</option>
												<option value="VT">Vermont</option>
												<option value="VA">Virginia</option>
												<option value="WA">Washington</option>
												<option value="WV">West Virginia</option>
												<option value="WI">Wisconsin</option>
												<option value="WY">Wyoming</option>
										</select>
									</div>
									<div class="form-group col-md-2">
										<label for="customerInfoZip">Zip</label>
										<input type="text" class="form-control customerSerialize workOrderRequired" id="customerInfoZip" name="zip" required>
									</div>
								</div>
							</div>
							<div class="collapse " id="collapsePhone">
								<div class="form-row">
									<div class="form-group col-md-3">
                                                                            <label for="customerInfoPhone2Type">Phone Type</label>
                                                                            <select id="customerInfoPhone2Type" name="phone2type" class="form-control contactSerialize">
                                                                                <option value="C">Cell</option>
                                                                                <option value="W">Work</option>
                                                                                <option value="H">Home</option>
                                                                            </select>
									</div>
									<div class="form-group col-md-9">
                                                                            <label for="customerInfoPhone2">Phone 2</label>
                                                                            <input type="tel" class="form-control contactSerialize" name="phone2" id="customerInfoPhone2" placeholder="1234567890">
									</div>
								</div>
								<div class="form-row">
									<div class="form-group col-md-3">
                                                                            <label for="customerInfoPhone3Type">Phone Type</label>
                                                                            <select id="customerInfoPhone3Type" name="phone3type" class="form-control contactSerialize">
                                                                                    <option value="C">Cell</option>
                                                                                    <option value="W">Work</option>
                                                                                    <option value="H">Home</option>
                                                                            </select>
									</div>
									<div class="form-group col-md-9">
                                                                            <label for="customerInfoPhone3">Phone 3</label>
                                                                            <input type="tel" class="form-control contactSerialize" name="phone3" id="customerInfoPhone3" placeholder="1234567890">
									</div>
								</div>
								<div class="form-group">
									<input type="hidden" id="customerInfoContactId" name="contact_id" value="">
									<input type="hidden" id="customerInfoCustomerId" name="customer_id" value="">
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary" id="customerInfoSaveBtn" href="#">Save</a>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="editVehicleModal" tabindex="-1" role="dialog" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Add New Vehicle</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="vehicleAddFormMessageContainer"></div>
						<form id="vehicleAddForm">
							<div class="form-row">
								<div class="form-group col-md-3">
									<label for="yearDropdown">Year</label> 
									<select id="yearDropdown" name="year" class="form-control vehicleSerialize" required>

									</select>
									<input type="text" id="yearDropdownOverride" name="yearoverride" class="form-control" style="display:none"/>
									<a href="#" id="yearDropdownToggle">Not Found?</a>
								</div>
								<div class="form-group col-md-9">
									<label for="makeDropdown">Make</label> 
									<select id="makeDropdown" name="make" class="form-control vehicleSerialize" required>

									</select>
									<input type="text" id="makeDropdownOverride" name="makeOverride" class="form-control" style="display:none"/>
									<a href="#" id="makeDropdownToggle">Not Found?</a>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="modelDropdown">Model</label> 
									<select id="modelDropdown" name="model" class="form-control vehicleSerialize" required>

									</select>
									<input type="text" id="modelDropdownOverride" name="modelOverride" class="form-control" style="display:none"/>
									<a href="#" id="modelDropdownToggle">Not Found?</a>
								</div>
								<div class="form-group col-md-6">
									<label for="trimText">Trim/Engine</label> 
									<input type="text" id="trimText" name="trim" class="form-control vehicleSerialize" />
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="vehicleAddUserType">VIN</label> 
										<div class="input-group">
										<input type="text" id="vinText" name="vin" class="form-control vehicleSerialize" />
										<button class="btn btn-outline-secondary" id="vinDecode" type="button">Decode</button>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-4">
									<label for="vehicleAddMileage">Mileage</label>
									<input type="tel" class="form-control vehicleSerialize" name="mileage" id="vehicleAddMileage">
								</div>
								<div class="form-group col-md-4">
									<label for="vehicleAddLicense">License Plate</label>
									<input type="text" name="license" class="form-control vehicleSerialize" id="vehicleAddLicense">
								</div>
								<div class="form-group col-md-4">
									<label for="vehicleAddFleetNum">Fleet Number</label>
									<input type="tel" class="form-control vehicleSerialize" name="fleetnum" id="vehicleAddFleetNum">
									<input type="hidden" class="vehicleSerialize" name="active" value="1">
									<input type="hidden" class="vehicleSerialize" name="customer_id" value="" id="vehicleAddCustomerId">
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary" id="vehicleAddSaveBtn" href="#">Save</a>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade d-print-none" id="refreshRequiredModal" tabindex="-1" role="dialog" aria-labelledby="refreshRequiredModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Work Order Modified</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>This order has been modified since loading. For the latest changes, refresh this page.</p>
						<p>Any changes you make without refreshing will overwrite the existing modifications.</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary" id="reloadBtn" href="#">Refresh</a>
					</div>
				</div>
			</div>
		</div>
		
		<?php include "includes/stockitemmodal.php"; ?>
		<?php include "includes/templatemodal.php"; ?>

	<?php include "includes/footer.php"; ?>
    <script src="js/workorder.js"></script>
    <script src="js/workorderedit.js"></script>
    <script>
	    (function($) {
	    	utilitiesJS.sessionCheck();
	    	utilitiesJS.storeNavigation();
	    	$('#teamMemberDropdown').select2({
            	placeholder: 'Select a Technician'
        	});
	    })(jQuery);
    </script>
</body>

</html>


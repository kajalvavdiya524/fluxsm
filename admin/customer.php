<!DOCTYPE html>
<html lang="en">

<head>
  <title>Customer | Flux Shop Manager</title>
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
        <li class="breadcrumb-item active">Customer</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1>Customer</h1>
        </div>
      </div>
      
      <div class="row d-print-none">        
		<div class="col-lg-5">
			<div class="card mb-3" id="customerCard">
				<div class="card-header">
					Customer Details
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div id="usertype"></div>
							<div id="taxexemption"></div>
							<div id="taxid"></div>
						</div>
						<div class="col-md-6">
							<div id="businessname"></div>
							<div id="address1"></div>
							<div id="address2"></div>
							<div id="address3"></div>
							<div id="citystatezip"></div>
						</div>
					</div>
					<a href="#editCustomerModal" data-toggle="modal"><strong>Edit</strong></a>
				</div>
			</div>
		</div>

		<div class="col-lg-7">
			<div class="card mb-3" id="customerCard">
				<div class="card-header">
					Contacts
				</div>
				<div class="card-body table-responsive">
					<div id="contactsDisplay">
						<table class="table table-striped"><thead class="thead-light">
							<thead><tr><th scope="col">First Name</th><th scope="col">Last Name</th><th scope="col">Phone</th><th></th></tr></thead>
							<tbody id="contactTableBody"></tbody>
						</table>
					</div>
					
					<div class="col-12 text-right">
			        	<button class="btn btn-primary" id="addContactBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add Contact</button>
			        </div>
				</div>
			</div>
		</div>
      </div>

	  <div class="row d-print-none">
		<div class="col-lg-12">
			<div class="card mb-3" id="vehicleCard">
				<div class="card-header">
					Vehicle
				</div>
				<div class="card-body table-responsive">
					<div class="col-12 text-right">
			        	<button class="btn btn-primary" id="addVehicleBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add Vehicle</button>
			        </div>
			    	<div id="vehicleList">
			    		<table class="table">
			    			<thead>
			    				<tr>
			    					<th scope="col">Year</th>
			    					<th scope="col">Make</th>
			    					<th scope="col">Model</th>
			    					<th scope="col">License</th>
			    					<th scope="col"></th>
			    					<th scope="col"></th>
		    					</tr>
	    					</thead>
	    					<tbody id="vehicleTableBody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
        </div>
      </div>      
	</div>
	
	
	<div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addCustomerModal">Edit Customer</h5>
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
									<input type="text" class="form-control customerSerialize" name="businessname" id="customerInfoBusinessName" placeholder="Business Name">
								</div>
							</div>
							<div>
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
										<select class="form-control customerSerialize workOrderRequired" name="state" id="customerInfoState" required> 
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
										<input type="text" class="form-control customerSerialize workOrderRequired" id="customerInfoZip" name="zip" maxlength="10" required>
									</div>
								</div>
								<div class="form-row">
									<div class="form-group col-md-12">
										<label class="custom-control custom-checkbox">
										  <input type="checkbox" name="internal" class="custom-control-input" id="customerInternal" value="1">
										  <span class="custom-control-indicator"></span>
										  <span class="custom-control-description">Internal Customer</span>
										</label>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
						 <a class="btn btn-primary" id="customerInfoSaveBtn" href="#">Save</a>
					</div>
				</div>
			</div>
		</div>
		
		
		<div class="modal fade" id="vehicleInfoModal" tabindex="-1" role="dialog" aria-labelledby="vehicleInfoModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="vehicleInfoModalTitle">Add New Vehicle</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="vehicleAddFormMessageContainer"></div>
						<form id="vehicleInfoForm">
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
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-12">
									<label class="custom-control custom-checkbox">
									  <input type="checkbox" name="active" class="custom-control-input vehicleSerialize" id="vehicleAddActive" value="1">
									  <span class="custom-control-indicator"></span>
									  <span class="custom-control-description">Active</span>
									</label>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
						<a class="btn btn-primary" id="vehicleInfoSaveBtn" href="#">Save</a>
						<input type="hidden" value="" id="vehicleInfoId" />
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" id="selectedVehicleModel" />
		
		<div class="modal fade" id="contactInfoModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="contactInfoModalTitle">Add New Customer</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="contactInfoFormMessageContainer"></div>
						<form id="contactInfoForm">
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="contactInfoFirstName">First Name</label> 
									<input type="text" class="form-control contactSerialize" name="firstname" id="contactInfoFirstName" placeholder="First Name" required>
								</div>
								<div class="form-group col-md-6">
									<label for="contactInfoLastName">Last Name</label>
									<input type="text" class="form-control contactSerialize" name="lastname" id="contactInfoLastName" placeholder="Last Name" required>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-3">
									<label for="contactInfoPhone1Type">Phone Type</label> 
									<select id="contactInfoPhone1Type" name="phone1type" class="form-control contactSerialize" >
										<option value="C">Cell</option>
										<option value="W">Work</option>
										<option value="H">Home</option>
									</select>
								</div>
								<div class="form-group col-md-9">
									<label for="contactInfoPhone1">Phone 1</label>
									<input type="tel" class="form-control contactSerialize" name="phone1" id="contactInfoPhone1" placeholder="1234567890" required>
								</div>
							</div>
							<div>
								<div class="form-row">
									<div class="form-group col-md-3">
										<label for="contactInfoPhone2Type">Phone Type</label> 
										<select id="contactInfoPhone2Type" name="phone2type" class="form-control contactSerialize" >
											<option value="C">Cell</option>
											<option value="W">Work</option>
											<option value="H">Home</option>
										</select>
									</div>
									<div class="form-group col-md-9">
										<label for="contactInfoPhone2">Phone 2</label>
										<input type="tel" class="form-control contactSerialize" name="phone2" id="contactInfoPhone2" placeholder="1234567890">
									</div>
								</div>
								<div class="form-row">
									<div class="form-group col-md-3">
										<label for="contactInfoPhone3Type">Phone Type</label> 
										<select id="contactInfoPhone3Type" name="phone3type" class="form-control contactSerialize" >
											<option value="C">Cell</option>
											<option value="W">Work</option>
											<option value="H">Home</option>
										</select>
									</div>
									<div class="form-group col-md-9">
										<label for="contactInfoPhone3">Phone 3</label>
										<input type="tel" class="form-control contactSerialize" name="phone3" id="contactInfoPhone3" placeholder="1234567890">
									</div>
								</div>
								<div class="form-group">
									<label for="contactInfoEmail">Email</label>
									<input type="email" class="form-control contactSerialize" name="email" id="contactInfoEmail" placeholder="Email">
								</div>
								<div class="form-group">
									<label class="custom-control custom-checkbox">
									  <input type="checkbox" class="custom-control-input" id="contactInfoIsPrimary" name="isPrimary" value="false">
									  <span class="custom-control-indicator"></span>
									  <span class="custom-control-description">Primary Contact</span>
									</label>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
						<a class="btn btn-primary" id="contactInfoSaveBtn" href="#">Save</a>
						<input type="hidden" value="" id="contactInfoId" />
					</div>
				</div>
			</div>
		</div>

    <?php include "includes/footer.php"; ?>
    <script src="js/customer.js"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	utilitiesJS.storeNavigation();
    	customerJS.init();
    })(jQuery);
    </script>
</body>

</html>


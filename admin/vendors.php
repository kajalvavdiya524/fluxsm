<!DOCTYPE html>
<html lang="en">

<head>
  <title>Vendors | Flux Shop Manager</title>
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
        <li class="breadcrumb-item active">Vendors</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1>Vendors</h1>
        </div>
      </div>
      
      <div class="row d-print-none table-responsive">
      	<div class="col-12 text-right">
        	<button class="btn btn-primary" id="addVendorBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add Vendor</button>
        </div>
        <table class="table">
	        <thead>
		        <tr>
		        	<th>Vendor</th>
		        	<th>Phone</th>
		        	<th>Email</th>
		        	<th>Adress</th>
		        	<th>City</th>
		        	<th>State</th>
		        	<th>Zip</th>
		        	<th></th>
		       	</tr>
	       	</thead>
	       	<tbody id="vendorTableBody">
	     	</tbody>
        </table>
      </div>
 
	</div>

		<div class="modal fade" id="vendorInfoModal" tabindex="-1"
			role="dialog" aria-labelledby="vendorInfoModalLabel"
			aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Vendor Info</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="vendorInfoFormMessageContainer"></div>
						<form id="vendorForm">
							<div class="form-row">
								<div id="businessNameContainer" class="form-group col-md-12">
									<label for="txtVendorname">Vendor Name</label> 
									<input type="text" class="form-control vendorSerialize" name="vendorname" id="txtVendorname" maxlength="40" required>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="txtFirstname">First Name</label> 
									<input type="text" class="form-control vendorSerialize" name="firstname" id="txtFirstname" maxlength="40" required>
								</div>
								<div class="form-group col-md-6">
									<label for="txtLastname">Last Name</label>
									<input type="text" class="form-control vendorSerialize" name="lastname" id="txtLastname" maxlength="40" required>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="txtPhone1">Primary Phone</label> 
									<input type="text" class="form-control vendorSerialize" name="phone1" id="txtPhone1" maxlength="12" required>
								</div>
								<div class="form-group col-md-6">
									<label for="txtPhone2">Secondary Phone</label>
									<input type="text" class="form-control vendorSerialize" name="phone2" id="txtPhone2" maxlength="12">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="txtPhone1">Email</label> 
									<input type="text" class="form-control vendorSerialize" name="email" id="txtEmail" maxlength="40">
								</div>
							</div>
							<div class="form-group">
								<label for="txtAddress1">Address 1</label>
								<input type="text" class="form-control vendorSerialize" name="address1" id="txtAddress1" maxlength="100" required>
							</div>
							<div class="form-group">
								<label for="txtAddress2">Address 2</label>
								<input type="text" class="form-control vendorSerialize" name="address2" id="txtAddress2" maxlength="100">
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="txtCity">City</label>
									<input type="text" class="form-control vendorSerialize" name="city" id="txtCity" maxlength="40" required>
								</div>
								<div class="form-group col-md-4">
									<label for="selectCity">State</label>
									<select class="form-control vendorSerialize" name="state" id="selectState"> 
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
									<label for="txtZip">Zip</label>
									<input type="text" class="form-control vendorSerialize" id="txtZip" name="zip" maxlength="12" required>
									<input type="hidden" class="form-control vendorSerialize" id="hiddenVendorId" name="vendorid">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-12">
									<label class="custom-control custom-checkbox">
									  <input type="checkbox" class="custom-control-input vendorSerialize" id="chkActive" value="1">
									  <span class="custom-control-indicator"></span>
									  <span class="custom-control-description">Active</span>
									</label>
								</div>
							</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
						<a class="btn btn-primary" id="vendorInfoSaveBtn" href="#">Save</a>
					</div>
				</div>
			</div>
		</div>

    <?php include "includes/footer.php"; ?>
    <script src="js/vendors.js"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	utilitiesJS.storeNavigation();
    	vendorsJS.init();
    })(jQuery);
    </script>
</body>

</html>


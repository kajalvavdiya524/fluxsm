<!DOCTYPE html>
<html lang="en">

<head>
  <title>Appointments | Flux Shop Manager</title>
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
        <li class="breadcrumb-item active">Appointments</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1>Appointments</h1>
        </div>
      </div>
      
      <div class="row d-print-none">
        <div class="col">
        	<div class="card mb-3">
				<div class="card-header">
					Schedule
				</div>
				<div class="card-body">
					<div id="appointments"></div>
				</div>
			</div>
        </div>
      </div>
      
	 </div>
    </div>

    <!-- date time select modal -->
    <div class="modal fade" id="newTicketModal" tabindex="-1" role="dialog" aria-labelledby="newTicketModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Create New Ticket</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">   
            <div id="newTicketMessageContainer"></div>
            <div class="form-row">
              <div class="form-group col-md-8">
                <label for="ends-at">Start Date</label>
                <input type="text" class="form-control" name="startdate" id="startDate" readonly />
              </div>
              <div class="form-group col-md-4">
                <label for="ends-at">Start Time</label>
                <input type="text" class="form-control" name="starttime" id="startTime" readonly />
                <input type="hidden" name="starttimehdn" id="startTimeHdn" />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button id="newTicketCreateCancelBtn" class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
            <a class="btn btn-primary" href="#" id="createNewTicketBtn">Create Ticket</a>
          </div>
        </div>
      </div>
    </div><!-- /.modal -->
    
	<?php include "includes/footer.php"; ?>
    <script src="js/appointments.js"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	utilitiesJS.storeNavigation();
    	appointmentsJS.init();
    })(jQuery);
    </script>
</body>

</html>


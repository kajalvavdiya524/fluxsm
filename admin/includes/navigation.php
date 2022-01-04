  <!-- Navigation-->
  <nav class="navbar navbar-expand-lg navbar-light bg-flux-light fixed-top d-print-none" id="mainNav">
    <a class="navbar-brand d-print-none" href="index.php">Flux</a>
    <div id="storeSelectContainer"><select class="form-control ml-auto" id="storeDropdown"></select></div>
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav navbar-sidenav d-print-none">
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Dashboard">
                <a class="nav-link" href="index.php">
                    <i class="fa fa-fw fa-dashboard"></i>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
        	<li class="nav-item" data-toggle="tooltip" data-placement="right" title="Appointments">
          		<a class="nav-link" href="appointments.php">
            		<i class="fa fa-fw fa-calendar"></i>
            		<span class="nav-link-text">Appointments</span>
          		</a>
        	</li>
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Work Order">
                <a class="nav-link" href="workorder.php">
                    <i class="fa fa-fw fa-wrench"></i>
                    <span class="nav-link-text">New Work Order</span>
                </a>
            </li>
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Customers">
                <a class="nav-link" href="customerlist.php">
                    <i class="fa fa-fw fa-group"></i>
                    <span class="nav-link-text">Customer List</span>
                </a>
            </li>
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Vendors">
                <a class="nav-link" href="vendors.php">
                    <i class="fa fa-fw fa-shopping-cart"></i>
                    <span class="nav-link-text">Vendors</span>
                </a>
            </li>
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Inventory">
                <a class="nav-link" href="inventory.php">
                    <i class="fa fa-fw fa-cubes"></i>
                    <span class="nav-link-text">Inventory</span>
                </a>
            </li>
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Settings">
                <a class="nav-link" href="settings.php">
                    <i class="fa fa-fw fa-cogs"></i>
                    <span class="nav-link-text">Settings</span>
                </a>
            </li>
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Reports">
                <a class="nav-link" href="reports.php">
                    <i class="fa fa-fw fa-line-chart"></i>
                    <span class="nav-link-text">Reports</span>
                </a>
            </li>
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Reports">
                <a class="nav-link" href="companydashboard.php">
                    <i class="fa fa-fw fa-pie-chart"></i>
                    <span class="nav-link-text">Company Dashboard</span>
                </a>
            </li>
            <div id="bugMessage"></div>
            <button class="btn btn-danger remove" type="button" data-toggle="modal" data-target="#reportBugModal"><i class="fa fa-fw fa-bug"></i><span class="nav-link-text">Report Bug</span></button>
        </ul>
        <ul class="navbar-nav sidenav-toggler d-print-none">
            <li class="nav-item">
                <a class="nav-link text-center" id="sidenavToggler">
                    <i class="fa fa-fw fa-angle-left"></i>
                </a>
            </li>
        </ul>
        <div class="navbar-nav dropdown ml-auto ">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span id="userwelcome"></span>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li>
                    <a class="" href="#" id="logout">
                    <i class="fa fa-fw fa-sign-out"></i>Logout</a>
                </li>
            </ul>
        </div>
    </div>
  </nav>
		<input type="hidden" id="selectedStockRow" />
		<div class="modal fade" id="stockPartModal" tabindex="-1" role="dialog" aria-labelledby="stockPartModalLabel" aria-hidden="true">
 			<div class="modal-dialog" id="partTable" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="stockPartModalLabel">Select Stock Part</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
 						</button>
          			</div>
          			<div class="modal-body">   
						<div class="table-responsive">
							<table class="table table-bordered table-striped" id="partDataTable">
								<thead>
									<tr>
										<th>Select</th>
										<th>Manufacturer</th>
										<th>Part Number</th>
										<th>Description</th>
										<th>Cost</th>
										<th>Retail</th>
										<th>In Stock</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
 		  			</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary" href="#" id="addStockItemBtn">Add</a>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="newTemplateModal" tabindex="-1" role="dialog" aria-labelledby="newTemplateModalLabel" aria-hidden="true">
 			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="stockPartModalLabel">Add New Template</h5>
						<button id="templateCreateCloseBtn" class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
 						</button>
          			</div>
          			<div class="modal-body">   
						<div id="newTemplateMessageContainer"></div>
						<div class="form-row">
							<div class="form-group col-md-12">
								<input type="text" class="form-control" name="newTemplateName" id="newTemplateName" placeholder="New Template Name" required>
							</div>
						</div>
 		  			</div>
					<div class="modal-footer">
						<button id="templateCreateCancelBtn" class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary" href="#" id="addTemplateBtn">Add</a>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="deleteTemplateModal" tabindex="-1" role="dialog" aria-labelledby="deleteTemplateModalLabel" aria-hidden="true">
 			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="stockPartModalLabel">Delete Template</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
 						</button>
          			</div>
          			<div class="modal-body">   
						<div id="deleteTemplateMessageContainer"></div>
						<div class="form-row">
							<div class="form-group col-md-12">
								Are you sure you want to delete the <span id="deleteTemplateLabel" class="font-weight-bold"></span> template?
							</div>
						</div>
 		  			</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary" href="#" id="deleteTemplateBtn">Delete</a>
					</div>
				</div>
			</div>
		</div>
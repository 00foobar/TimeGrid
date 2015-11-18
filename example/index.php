<!DOCTYPE HTML>

<?php
	// include the TimeGrid library
	require_once('../lib/TimeGrid.php');

	$request_ok = true;

	// check if category is given to map
	if ( !isset($_GET['category']) || empty($_GET['category']) || !is_string($_GET['category']) )
	{
		$errors[] = 'You must set a category.';
		$request_ok = false;
	}

	// set category
	if ( $request_ok )
	{
		$category = strip_tags($_GET['category']);
	}

	if ( !isset($_GET['from_date']) || empty($_GET['from_date']) )
	{
		$errors[] = 'You must set a from_date.';
		$request_ok = false;
	}

	// set from_date
	if ( $request_ok )
	{
		$from_date = strip_tags($_GET['from_date']);
	}

	// get the to_date or set it to the same value as from_date if not set by user
	if ( !isset($_GET['to_date']) || empty($_GET['to_date']) )
	{
		// if not set use from_date
		if ( $request_ok )
		{
			$to_date = $from_date;
		}
	}
	else
	{
		// use the user-given to_date value
		$to_date = strip_tags($_GET['to_date']);
	}

	// initialize TimeGrid class
	$timegrid = new TimeGrid();

	// get list of the categories
	$categories = $timegrid->getCategories();

	if ( $request_ok )
	{
		// generate our grid
		$timegrid->setYWidth(10);
		$timegrid->setCategory($category);
		$timegrid->setFromDate($from_date);
		$timegrid->setToDate($to_date);
		$timegrid->createGrid();
	}
?>

<html>
	<head>

		<title>TimeGrid Example #1</title>

		<!-- Timegrid UI Styles (not necessary / only for example) -->
		<link href="css/style.css" type="text/css" rel="stylesheet">

		<!-- Timegrid CSS *LIBRARY IMPORTANT* -->
		<link href="../lib/timegrid.css" type="text/css" rel="stylesheet">

		<!-- Bootstrap CSS (not necessary / only for example) -->
		<link href="../thirdparty/bootstrap-3.3.5/css/bootstrap.min.css" type="text/css" rel="stylesheet">

		<!-- jQuery UI CSS (not necessary / only for example) -->
		<link href="../thirdparty/jquery-ui-1.11.4/jquery-ui.min.css" type="text/css" rel="stylesheet">

		<!-- jQuery (not necessary / only for example) -->
		<script type="text/javascript" src="../thirdparty/jquery-1.11.3/jquery.min.js"></script>

	</head>

	<body>
		<div class="container">

			<br>

			<div class="row">

				<div class="hidden-xs">
					<div class="col-sm-8">
						<button type="button" class="btn btn-info btn-lg menu" data-toggle="modal" data-target="#timeframe-modal">Settings</button>
						<button type="button" class="btn btn-info btn-lg menu" data-toggle="modal" data-target="#object-new-modal">Add</button>
					</div>

					<div class="col-sm-4">
						<h2 class="pull-right capitalize" id="category-header">
							<small><?php if ( isset($from_date) && !empty($from_date) ) echo $from_date . ' - ' . $to_date; ?></small>
							<?php if ( $request_ok ) echo $category; ?>
						</h2>
					</div>
				</div>

				<div class="hidden-sm hidden-md hidden-lg">
					<div class="col-xs-8">
						<button type="button" class="btn btn-info btn-md menu" data-toggle="modal" data-target="#timeframe-modal">Settings</button>
						<button type="button" class="btn btn-info btn-md menu" data-toggle="modal" data-target="#object-new-modal">Add</button>
					</div>

					<div class="col-xs-4">
						<h4 class="pull-right capitalize" id="category-header">
							<small><?php if ( isset($from_date) && !empty($from_date) ) echo $from_date . ' - ' . $to_date; ?></small>
							<?php if ( $request_ok ) echo $category; ?>
						</h4>
					</div>
				</div>

			</div>

			<br>

			<div class="row">
				<div class="col-xs-12">
					<?php 
						if ( $request_ok )
						{
							$timegrid->showGrid();
						}
						else
						{
							foreach ($errors as $error)
							{
								echo '<div class="alert alert-info"><strong>Info!</strong> ' . $error . '</div>';
							}
						}
					?>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12">
				
					<!-- Modals -->
					<!-- select timeframe modal -->
					<div id="timeframe-modal" class="modal fade" role="dialog">
						<div class="modal-dialog modal-lg">

							<!-- modal content-->
							<div class="modal-content">
								
								<!-- modal header -->
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Settings</h4>
								</div>
								
								<!-- modal body -->
								<div class="modal-body">
									<p>Choose a time window in which you want to check if the objects are reserved or not.</p>

									<!-- registration form -->
									<form id="timeframe-form" role="form">

										<div class="form-group">
											<label for="timeframe-from_date">From:</label>
											<input type="text" class="form-control" id="timeframe-from_date" name="timeframe-from_date" required>
										</div>

										<div class="form-group">
											<label for="timeframe-to_date">To:</label>
											<input type="text" class="form-control" id="timeframe-to_date" name="timeframe-to_date">
										</div>

										<div class="form-group">
											<label for="timeframe-category">Category:</label>
											<select class="form-control capitalize" id="timeframe-category" name="timeframe-category" required>
												<?php	
													foreach ($categories as $category)
													{
														echo '<option>' . $category . '</option>';
													}
												?>
											</select>
										</div>


										<br>

										<div class="alert alert-success alert-hide" id="timeframe-success">
											<strong>Success!</strong> You will be redirected to the map. Please wait.
										</div>

								</div> <!-- /.modal body -->
							
								<!-- modal footer -->
								<div class="modal-footer">
									<button type="submit" class="btn btn-success">Submit</button>
									</form> <!-- registration form -->
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div> <!-- /.modal body -->

							</div> <!-- /.modal content -->
						</div> <!-- /.modal dialog -->
					</div> <!-- /.timeframe modal -->

					<!-- add object modal -->
					<div id="object-new-modal" class="modal fade" role="dialog">
						<div class="modal-dialog modal-lg">

							<!-- modal content-->
							<div class="modal-content">
								
								<!-- modal header -->
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Add Object</h4>
								</div>
								
								<!-- modal body -->
								<div class="modal-body">
									<p>Add a new object in a existing category by using the select-box or create a new category by using the input-field below.</p>

									<!-- registration form -->
									<form id="object-new-form" role="form">

										<div class="form-group">
											<label for="object-new-name">Name:</label>
											<input type="text" class="form-control" id="object-new-name" name="object-new-name" required>
										</div>

										<div class="form-group">
											<label for="object-new-x">X (Number):</label>
											<input type="number" class="form-control" id="object-new-x" name="object-new-x" min="1" required>
										</div>

										<div class="form-group">
											<label for="object-new-y">Y (Letter):</label>
											<select class="form-control" id="object-new-y" name="object-new-y" required>
												<option value="1">A</option>
												<option value="2">B</option>
												<option value="3">C</option>
												<option value="4">D</option>
												<option value="5">E</option>
												<option value="6">F</option>
												<option value="7">G</option>
												<option value="8">H</option>
												<option value="9">I</option>
												<option value="10">J</option>
												<option value="11">K</option>
												<option value="12">L</option>
												<option value="13">M</option>
												<option value="14">N</option>
												<option value="15">O</option>
												<option value="16">P</option>
												<option value="17">Q</option>
												<option value="18">R</option>
												<option value="19">S</option>
												<option value="20">T</option>
												<option value="21">U</option>
												<option value="22">V</option>
												<option value="23">W</option>
												<option value="24">X</option>
												<option value="25">Y</option>
												<option value="26">Z</option>
											</select>
										</div>

										<div class="form-group">
											<label for="object-new-category">Category:</label>
											<select class="form-control capitalize" id="object-new-category" name="object-new-category">
												<?php
													foreach ($categories as $category)
													{
														echo '<option>' . $category . '</option>';
													}
												?>
											</select>
											
											<input type="text" class="form-control capitalize" id="object-new-category-new" name="object-new-category-new">
										</div>


										<br>

										<div class="alert alert-success alert-hide" id="object-new-success">
											<strong>Success!</strong> You will be redirected to the map. Please wait.
										</div>

										<div class="alert alert-danger alert-hide" id="object-new-failure">
											<strong>Error!</strong> Please correct your input values.
										</div>

								</div> <!-- /.modal body -->
							
								<!-- modal footer -->
								<div class="modal-footer">
									<button type="submit" class="btn btn-success">Submit</button>
									</form> <!-- registration form -->
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div> <!-- /.modal body -->

							</div> <!-- /.modal content -->
						</div> <!-- /.modal dialog -->
					</div> <!-- /.add object modal -->

					<!-- select modal -->
					<div id="select-modal" class="modal fade" role="dialog">
						<div class="modal-dialog modal-lg">

							<!-- modal content-->
							<div class="modal-content">
								
								<!-- modal header -->
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Please Select</h4>
								</div>
								
								<!-- modal body -->
								<div class="modal-body">
									<p>Choose if you want to edit the object or the reservation.</p>

									<br>

									<button type="button" id="edit-obj" class="btn btn-success btn-lg">Edit Object</button>
									<button type="button" id="edit-res" class="btn btn-danger btn-lg">Edit Reservation</button>
								</div> <!-- /.modal body -->
							
								<br>

								<!-- modal footer -->
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div> <!-- /.modal body -->

							</div> <!-- /.modal content -->
						</div> <!-- /.modal dialog -->
					</div> <!-- /.select modal -->

					<!-- edit object modal -->
					<div id="object-edit-modal" class="modal fade" role="dialog">
						<div class="modal-dialog modal-lg">

							<!-- modal content-->
							<div class="modal-content">
								
								<!-- modal header -->
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Object</h4>
								</div>
								
								<!-- modal body -->
								<div class="modal-body">
									<p>.</p>

									<!-- edit object form -->
									<form id="object-edit-form" role="form">

										<div class="form-group">
											<label for="object-edit-name">Name:</label>
											<input type="text" class="form-control" id="object-edit-name" name="object-edit-name" required>
										</div>

										<div class="form-group">
											<label for="object-edit-x">X (Number):</label>
											<input type="number" class="form-control" id="object-edit-x" name="object-edit-x" min="1" required>
										</div>

										<div class="form-group">
											<label for="object-edit-y">Y (Letter):</label>
											<select class="form-control" id="object-edit-y" name="object-edit-y" required>
												<option value="1">A</option>
												<option value="2">B</option>
												<option value="3">C</option>
												<option value="4">D</option>
												<option value="5">E</option>
												<option value="6">F</option>
												<option value="7">G</option>
												<option value="8">H</option>
												<option value="9">I</option>
												<option value="10">J</option>
												<option value="11">K</option>
												<option value="12">L</option>
												<option value="13">M</option>
												<option value="14">N</option>
												<option value="15">O</option>
												<option value="16">P</option>
												<option value="17">Q</option>
												<option value="18">R</option>
												<option value="19">S</option>
												<option value="20">T</option>
												<option value="21">U</option>
												<option value="22">V</option>
												<option value="23">W</option>
												<option value="24">X</option>
												<option value="25">Y</option>
												<option value="26">Z</option>
											</select>
										</div>

										<div class="form-group">
											<label for="object-edit-category">Category:</label>
											<select class="form-control capitalize" id="object-edit-category" name="object-edit-category">
												<?php
													foreach ($categories as $category)
													{
														echo '<option id="' . $category . '">' . $category . '</option>';
													}
												?>
											</select>
											
											<input type="text" class="form-control capitalize" id="object-edit-category-new" name="object-edit-category-new">
										</div>


										<br>

										<div class="alert alert-success alert-hide" id="object-edit-success">
											<strong>Success!</strong> You will be redirected to the map. Please wait.
										</div>

										<div class="alert alert-danger alert-hide" id="object-edit-failure">
											<strong>Error!</strong> <span id="object-edit-error"></span>
										</div>

										<div class="alert alert-success alert-hide" id="object-edit-delete-success">
											<strong>Success!</strong> You will be redirected to the map. Please wait.
										</div>

										<div class="alert alert-danger alert-hide" id="object-edit-delete-failure">
											<strong>Error!</strong> <span id="object-edit-error"></span>
										</div>

								</div> <!-- /.modal body -->
							
								<!-- modal footer -->
								<div class="modal-footer">
									<button type="submit" class="btn btn-success">Submit</button>
									</form> <!-- registration form -->

									<form id="object-edit-delete-form" role="form">
										<button type="submit" class="btn btn-danger">Delete</button>
									</form>

									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div> <!-- /.modal body -->

							</div> <!-- /.modal content -->
						</div> <!-- /.modal dialog -->
					</div> <!-- /.edit object modal -->

					<!-- edit reservation modal -->
					<div id="reservation-edit-modal" class="modal fade" role="dialog">
						<div class="modal-dialog modal-lg">

							<!-- modal content-->
							<div class="modal-content">
								
								<!-- modal header -->
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Reservation</h4>
								</div>
								
								<!-- modal body -->
								<div class="modal-body">
									<p>Object-ID: <b id="reservation-info-object_id"></b></p>
									<p>Reservation-ID: <b id="reservation-info-reservation_id"></b></p>
									<p>Position: <b id="reservation-info-position"></b></p>

									<!-- edit reservation form -->
									<form id="reservation-edit-form" role="form">

										<div class="form-group">
											<label for="reservation-edit-from_date">From:</label>
											<input type="text" class="form-control" id="reservation-edit-from_date" name="reservation-edit-from_date" required>
										</div>

										<div class="form-group">
											<label for="reservation-edit-to_date">To:</label>
											<input type="text" class="form-control" id="reservation-edit-to_date" name="reservation-edit-to_date">
										</div>

										<input type="hidden" id="reservation-edit-id">

										<br>

										<div class="alert alert-success alert-hide" id="reservation-edit-success">
											<strong>Success!</strong> You will be redirected to the map. Please wait.
										</div>

										<div class="alert alert-danger alert-hide" id="reservation-edit-failure">
											<strong>Error!</strong> Please correct your input values.
										</div>

										<div class="alert alert-success alert-hide" id="reservation-edit-delete-success">
											<strong>Success!</strong> You will be redirected to the map. Please wait.
										</div>

										<div class="alert alert-danger alert-hide" id="reservation-edit-delete-failure">
											<strong>Error!</strong> Please correct your input values.
										</div>

								</div> <!-- /.modal body -->
							
								<!-- modal footer -->
								<div class="modal-footer">
									<button type="submit" class="btn btn-success">Submit</button>
									</form> <!-- registration form -->

									<form id="reservation-edit-delete-form" role="form">
										<button type="submit" class="btn btn-danger">Delete</button>
									</form>

									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div> <!-- /.modal body -->

							</div> <!-- /.modal content -->
						</div> <!-- /.modal dialog -->
					</div> <!-- /.edit reservation modal -->

				</div>
			</div>
		</div>

	<!-- jQuery UI (not necessary / only for example) -->
	<script type="text/javascript" src="../thirdparty/jquery-ui-1.11.4/jquery-ui.min.js"></script>
	<!-- Bootstrap (not necessary / only for example) -->
	<script type="text/javascript" src="../thirdparty/bootstrap-3.3.5/js/bootstrap.min.js"></script>
	<!-- Formular2Controller (not necessary / only for example) -->
	<script type="text/javascript" src="../thirdparty/formular2controller/f2c_modal.js"></script>
	<!-- Timegrid.js (not necessary / only for example) -->
	<script type="text/javascript" src="js/timegrid.js"></script>

	</body>
</html>
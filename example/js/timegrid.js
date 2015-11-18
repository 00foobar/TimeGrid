$(document).ready(function() {

	var object = null;
	var reservation = null;

	// path to controller file
	var controller = 'actions.php';

	/* Datepicker */
	// TIMEFRAME
	$('#timeframe-from_date').datepicker({ dateFormat: 'yy-mm-dd' });
	$('#timeframe-to_date').datepicker({ dateFormat: 'yy-mm-dd' });
	// RESERVATION EDIT/ADD
	$('#reservation-edit-from_date').datepicker({ dateFormat: 'yy-mm-dd' });
	$('#reservation-edit-to_date').datepicker({ dateFormat: 'yy-mm-dd' });

	/* Modals & Forms */
	// SELECT TIMEFRAME
	$('#timeframe-form').submit(function(event)
	{
		event.preventDefault();

		$('.alert-hide').hide();

		var from_date = $('#timeframe-from_date').val();
		var to_date = $('#timeframe-to_date').val();
		var category = $('#timeframe-category').val();

		$('#timeframe-success').fadeIn();
							
		setTimeout(function()
		{
			window.location.replace('index.php?category=' + category + '&from_date=' + from_date + '&to_date=' + to_date);
		}, 1000);

	});

	// ADD OBJECT
	$('#object-new-form').submit(function(event)
	{
		event.preventDefault();

		//$('.alert-hide').hide();

		var modal_prefix = '#object-new-';

		var f2c = new Formular2Controller($('#object-new-form *'), 'object_add', controller, modal_prefix);
		
		f2c.requestPublic();
	});

	// ADD/EDIT RESERVATION
	$('#reservation-edit-form').submit(function(event)
	{
		event.preventDefault();

		//$('.alert-hide').hide();

		var modal_prefix = '#reservation-edit-';

		if ( !reservation['id'] )
		{
			var f2c = new Formular2Controller($('#reservation-edit-form *'), 'reservation_add', controller, modal_prefix, object['id']);
		}
		else
		{
			var f2c = new Formular2Controller($('#reservation-edit-form *'), 'reservation_edit', controller, modal_prefix, object['id'], reservation['id']);
		}
		
		f2c.requestPublic();	
	});

	// EDIT OBJECT
	$('#object-edit-form').submit(function(event)
	{
		event.preventDefault();

		//$('.alert-hide').hide();

		var modal_prefix = '#object-edit-';

		var f2c = new Formular2Controller($('#object-edit-form *'), 'object_edit', controller, modal_prefix, object['id']);

		f2c.requestPublic();	
	});

	// DELETE RESERVATION
	$('#reservation-edit-delete-form').submit(function(event)
	{
		event.preventDefault();

		//$('.alert-hide').hide();

		var modal_prefix = '#reservation-edit-delete-';

		var f2c = new Formular2Controller($('#reservation-edit-delete-form *'), 'reservation_delete', controller, modal_prefix, object['id'], reservation['id']);
		
		f2c.requestPublic();
	});

	// DELETE OBJECT
	$('#object-edit-delete-form').submit(function(event)
	{
		event.preventDefault();

		//$('.alert-hide').hide();

		var modal_prefix = '#object-edit-delete-';

		var f2c = new Formular2Controller($('#object-edit-delete-form *'), 'object_delete', controller, modal_prefix, object['id']);
		
		var result = f2c.requestPublic();

		if ( result === false )
		{
			// @TODO 
			$('#object-edit-error').html('There are reservations for this object in future. Please delete future reservations first.');
		}
	});

	/* onClick Events */
	$(document).on('click', '.map-cell', function ()
	{
		// get the object & reservation array for the selected cell from the hidden inputs
		object = $(this).find('#obj_id').val();
		reservation = $(this).find('#res_id').val();

		// make the strings to json arrays
		object = $.parseJSON(object);
		reservation = $.parseJSON(reservation);

		// open the select modal
		$('#select-modal').modal();
	});

	/* PRE SELECTIONS */
	// object edit
	$('#edit-obj').click(function()
	{
		// reset the form values
		$('#object-edit-form')[0].reset();

		// close select modal and open object edit modal
		$('#select-modal').modal('hide');
		$('#object-edit-modal').modal();

		// load values into form
		$('#object-edit-name').val(object['name']);
		$('#object-edit-x').val(object['pos_x']);
		$('#object-edit-y').val(object['pos_y']);
		$('#object-edit-category').val(object['category']);
	});

	// reservation edit
	$('#edit-res').click(function()
	{
		// set the object / reservations information
		$('#reservation-info-object_id').html(object['id']);
		$('#reservation-info-reservation_id').html(reservation['id']);
		$('#reservation-info-position').html(String.fromCharCode(64 + parseInt(object['pos_y'])) + object['pos_x']);

		// reset the form values
		$('#reservation-edit-form')[0].reset();

		// close select modal and open reservation edit modal
		$('#select-modal').modal('hide');
		$('#reservation-edit-modal').modal();

		// load values into form
		$('#reservation-edit-from_date').val(reservation['from_date']);
		$('#reservation-edit-to_date').val(reservation['to_date']);
	});

});
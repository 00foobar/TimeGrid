<?php
require_once('../lib/TimeGrid.php');

$timegrid = new TimeGrid();

/*
@TODO Implement controller as a class with *Action() methods and do the rouing at index.php to prevent exit() calls and provide more clarity.
*/

// delete object
if ( isset($_POST['action']) && $_POST['action'] == 'object_delete' )
{
	if ( isset($_POST['object_id']) && is_numeric($_POST['object_id']) )
	{
		$object_id = intval($_POST['object_id']);

		if ( $timegrid->deleteObject($object_id) )
		{
			echo json_encode(true);
			exit();
		}

		echo json_encode(false);
		exit();
	}
	else
	{
		echo json_encode(false);
		exit();
	}
}

// delete reservation
if ( isset($_POST['action']) && $_POST['action'] == 'reservation_delete' )
{
	if ( isset($_POST['reservation_id']) && is_numeric($_POST['reservation_id']) )
	{
		$reservation_id = intval($_POST['reservation_id']);

		if ( $timegrid->deleteReservation($reservation_id) )
		{
			echo json_encode(true);
			exit();
		}

		echo json_encode(false);
		exit();
	}
	else
	{
		echo json_encode(false);
		exit();
	}	
}


// edit object
if ( isset($_POST['action']) && $_POST['action'] == 'object_edit' )
{
	// validate user inputs
	if ( isset($_POST['object-edit-name']) && !empty($_POST['object-edit-name']) && isset($_POST['object-edit-x']) && !empty($_POST['object-edit-x']) && is_numeric($_POST['object-edit-x']) && isset($_POST['object-edit-y']) && !empty($_POST['object-edit-y']) && is_numeric($_POST['object-edit-y']) && isset($_POST['object_id']) && !empty($_POST['object_id']) && is_numeric($_POST['object_id']) )
	{
		// new category or a existing category
		if ( isset($_POST['object-edit-category-new']) && !empty($_POST['object-edit-category-new']) && is_string($_POST['object-edit-category-new']) )
		{
			// new category
			$object_category = strip_tags($_POST['object-edit-category-new']);
		}
		else
		{
			// existing category
			$object_category = strip_tags($_POST['object-edit-category']);
		}

		// get & validate the values
		$object_name = strip_tags($_POST['object-edit-name']);
		$object_pos_x = intval($_POST['object-edit-x']);
		$object_pox_y = intval($_POST['object-edit-y']);
		$object_id = intval($_POST['object_id']);

		if ( $timegrid->editObject($object_id, $object_name, $object_pos_x, $object_pos_y, $object_category) )
		{
			echo json_encode(true);
			exit();
		}
	}

	echo json_encode(false);
	exit();
}

// edit reservation
if ( isset($_POST['action']) && $_POST['action'] == 'reservation_edit' )
{
	if ( isset($_POST['reservation-edit-from_date']) && !empty($_POST['reservation-edit-from_date']) && $timegrid->validateDate($_POST['reservation-edit-from_date']) == true && isset($_POST['reservation_id']) && !empty($_POST['reservation_id']) && is_numeric($_POST['reservation_id']) )
	{
		$from_date = strip_tags($_POST['reservation-edit-from_date']);
		$reservation_id = intval($_POST['reservation_id']);

		if ( isset($_POST['reservation-edit-to_date']) && !empty($_POST['reservation-edit-to_date']) && $timegrid->validateDate($_POST['reservation-edit-to_date']) == true )
		{
			$to_date = strip_tags($_POST['reservation-edit-to_date']);
		}
		else
		{
			$to_date = $from_date;
		}

		$result = $timegrid->editReservation($reservation_id, $from_date, $to_date);

		if ( $result == true )
		{
			echo json_encode(true);
			exit();
		}
	}

	echo json_encode(false);
	exit();
}

// add object
if ( isset($_POST['action']) && $_POST['action'] == 'object_add' )
{
	// validate user inputs
	if ( isset($_POST['object-new-name']) && !empty($_POST['object-new-name']) && isset($_POST['object-new-x']) && !empty($_POST['object-new-x']) && is_numeric($_POST['object-new-x']) && isset($_POST['object-new-y']) && !empty($_POST['object-new-y']) && is_numeric($_POST['object-new-y']) )
	{
		// new category or a existing category
		if ( isset($_POST['object-new-category-new']) && !empty($_POST['object-new-category-new']) && is_string($_POST['object-new-category-new']) )
		{
			// new category
			$object_category = strip_tags($_POST['object-new-category-new']);
		}
		else
		{
			// existing category
			$object_category = strip_tags($_POST['object-new-category']);
		}

		// get & validate the values
		$object_name = strip_tags($_POST['object-new-name']);
		$object_pos_x = intval($_POST['object-new-x']);
		$object_pos_y = intval($_POST['object-new-y']);

		if ( $timegrid->createObject($object_name, $object_pos_x, $object_pos_y, $object_category) )
		{
			echo json_encode(true);
			exit();
		}
	}

	echo json_encode(false);
	exit();
}

// add reservation
if ( isset($_POST['action']) && $_POST['action'] == 'reservation_add' )
{
	if ( isset($_POST['object_id']) && !empty($_POST['object_id']) && is_numeric($_POST['object_id']) && isset($_POST['reservation-edit-from_date']) && !empty($_POST['reservation-edit-from_date']) && $timegrid->validateDate($_POST['reservation-edit-from_date']) == true )
	{
		$from_date = strip_tags($_POST['reservation-edit-from_date']);
		$object_id = intval($_POST['object_id']);

		if ( isset($_POST['to_date']) && !empty($_POST['to_date']) && $timegrid->validateDate($_POST['to_date']) == true )
		{
			$to_date = strip_tags($_POST['reservation-edit-to_date']);
		}
		else
		{
			$to_date = $from_date;
		}

		$result = $timegrid->setReservation($object_id, $from_date, $to_date);

		if ( $result == false )
		{
			echo json_encode(false);
			exit();
		}
		else
		{
			echo json_encode(true);
			exit();
		}
	}
	else
	{
		echo json_encode(false);
		exit();
	}
}


?>
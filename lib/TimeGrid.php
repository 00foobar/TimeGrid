<?php

class TimeGrid
{
	/* DATABASE OPTIONS */
	private $db_host = 'mysql:host=localhost;dbname=timegrid';
	private $db_user = 'root';
	private $db_pass = 'password';
	private $db_char = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

	// database handler
	private $dbh;

	// grid values
	private $category;
	private $from_date;
	private $to_date;

	// data array
	private $objects;
	private $reservations;

	// created map html code
	private $map_html;

	// grid values for creation/edit
	private $object_id;
	private $reservation_id;

	// dimensions of grid
	private $col = 0;
	private $row = 0;

	// grid configurations
	private $y_width = 10; // in percent
	private $cell_content = null; // html or text to show inside a cell

	// error array
	public $error = array();

	public function __construct()
	{
		try
		{
			$this->dbh = new PDO($this->db_host, $this->db_user, $this->db_pass, $this->db_char);
		}
		catch (Exception $e)
		{
			echo 'Error: ' . $e->getMessage();
		}
	}

	/* SETTER */
	public function setCategory($category)
	{
		if ( isset($category) && !empty($category) && is_string($category) )
		{
			$this->category = strtolower(strip_tags($category));

			return true;
		}

		return false;
	}

	public function setFromDate($from_date)
	{
		if ( isset($from_date) && !empty($from_date) && $this->validateDate($from_date) )
		{
			$this->from_date = strip_tags($from_date);

			return true;
		}

		return false;
	}

	public function setToDate($to_date)
	{
		if ( isset($to_date) && !empty($to_date) && $this->validateDate($to_date) )
		{
			$this->to_date = strip_tags($to_date);

			return true;
		}

		return false;
	}

	public function setObjectID($object_id)
	{
		if ( isset($object_id) && !empty($object_id) && is_numeric($object_id) )
		{
			$this->object_id = intval($object_id);

			return true;
		}

		return false;
	}

	public function setYWidth($width)
	{
		if ( is_int($width) )
		{
			$this->y_width = $width;

			return true;
		}

		return false;
	}

	public function setCellContent($html)
	{
		if ( is_string($html) )
		{
			$this->cell_content = '<div>' . $html . '</div>';
			return true;
		}

		return false;
	}

	/* GETTER */
	public function getErrors()
	{
		return $this->error;
	}

	public function getGridColumns()
	{
		return $this->col;
	}

	public function getGridRows()
	{
		return $this->row;
	}

	public function getObjectByID($object_id)
	{
		$sql = "SELECT * FROM objects WHERE id = :object_id";
		$sth = $this->dbh->prepare($sql);

		$sth->execute(array(':object_id' => $object_id));

		if ( $sth->rowCount() == 1 )
		{
			$result = $sth->fetchAll();

			return $result[0];
		}

		return false;
	}

	public function getReservationByID($reservation_id)
	{
		$sql = "SELECT * FROM reservations WHERE id = :reservation_id";
		$sth = $this->dbh->prepare($sql);

		$sth->execute(array(':reservation_id' => $object_id));

		if ( $sth->rowCount() == 1 )
		{
			$result = $sth->fetchAll();

			return $result[0];
		}

		return false;
	}

	public function getCategories()
	{
		$sql = "SELECT category FROM objects";
		$sth = $this->dbh->prepare($sql);

		$sth->execute();

		if ( $sth->rowCount() > 0 )
		{
			$result = $sth->fetchAll(PDO::FETCH_COLUMN);

			return array_unique($result);
		}

		return false;
	}

	/* OBJECT FUNCTIONS */
	private function getObjectsByCategory()
	{
		if ( $this->existsObjectsInCategory() == false )
		{
			throw new Exception("Error: No objects in this category (" . $this->category . ")", 1);
		}

		$sql = "SELECT * FROM objects WHERE category = :category";
		$sth = $this->dbh->prepare($sql);

		$sth->execute(array(	':category' => $this->category));

		if ( $sth->rowCount() > 0 )
		{
			$result = $sth->fetchAll(); //@TODO
			$this->objects = $result;

			return true;
		}

		return false;
	}

	private function existsObjectsInCategory()
	{
		if ( !isset($this->category) || empty($this->category) )
		{
			throw new Exception("Error: No category set.", 1);
		}

		$sql = "SELECT category FROM objects WHERE category = :category LIMIT 1";
		$sth = $this->dbh->prepare($sql);

		$sth->execute(array(	':category' => $this->category));

		if ( $sth->rowCount() > 0 )
		{
			return true;
		}

		return false;
	}

	public function createObject($name, $posX, $posY, $category)
	{
		if ( $this->isObjectPositionEmpty($posX, $posY, $category) == false )
		{
			return false;
		}

		$sql = "INSERT INTO objects (name, pos_x, pos_y, category) VALUES (:name, :pos_x, :pos_y, :category)";
		$sth = $this->dbh->prepare($sql);

		$req_return = $sth->execute(array(	':name' => $name,
											':pos_x' => $posX,
											':pos_y' => $posY,
											':category' => $category));

		if ( $req_return )
		{
			// return $this->dbh->lastInsertId();
			return true;
		}

		return false;
	}

	public function editObject($object_id, $name, $posX, $posY, $category)
	{
		if ( $this->isObjectPositionEmpty($posX, $posY, $category) == false )
		{
			return false;
		}

		$sql = "UPDATE objects SET name = :name, pos_x = :pos_x, pos_y = :pos_y, category = :category WHERE id = :object_id";
		$sth = $this->dbh->prepare($sql);

		$req_return = $sth->execute(array(	':name' => $name,
											':pos_x' => $posX,
											':pos_y' => $posY,
											':category' => $category,
											':object_id' => $object_id));

		if ( $reg_return )
		{
			return true;
		}

		return false;
	}

	public function deleteObject($object_id)
	{
		// check if there any reservations for this object with actual date or later
		if ( $this->isObjectReservedInFuture($object_id) == true )
		{
			$this->setError('Object has reservations in future time.');

			return false;
		}

		$sql = "DELETE FROM objects WHERE id = :object_id";
		$sth = $this->dbh->prepare($sql);

		$req_return = $sth->execute(array(	':object_id' => $object_id));

		if ( $req_return )
		{
			return true;
		}

		return false;
	}

	/* RESERVATION FUNCTIONS */
	private function getReservationByObjectID($object_id)
	{
		if ( !is_numeric($object_id) )
		{
			throw new Exception("Error: object_id must be numeric", 1);
		}

		if ( isset($this->from_date) && !empty($this->from_date) && $this->validateDate($this->from_date) )
		{
			$from_date = $this->from_date;
		}
		else
		{
			throw new Exception("Error: from_date is not set or invalid.", 1);	
		}

		if ( isset($this->to_date) && !empty($this->to_date) )
		{
			if ( $this->validateDate($from_date) )
			{
				$to_date = $this->to_date;
			}
			else
			{
				throw new Exception("Error: to_date is invalid.", 1);
			}
		}
		else
		{
			$to_date = $this->from_date;
		}		

		$sql = "SELECT * FROM reservations WHERE from_date <= :to_date AND to_date >= :from_date AND object_id = :object_id";
		$sth = $this->dbh->prepare($sql);

		$sth->execute(array(	':to_date' => $to_date,
							 	':from_date' => $from_date,
							 	':object_id' => $object_id ));

		if ( $sth->rowCount() == 1 )
		{
			$result = $sth->fetchAll();
			return $result[0];
		}

		return false;
	}

	public function setReservation($object_id, $from_date, $to_date)
	{
		if ( $this->isObjectReservedInTimeframe($object_id, $from_date, $to_date) == true )
		{
			$this->setError('Object is already reserved in selected timeframe.');

			return false;
		}

		$sql = "INSERT INTO reservations (object_id, from_date, to_date) VALUES (:object_id, :from_date, :to_date)";
		$sth = $this->dbh->prepare($sql);

		$req_return = $sth->execute(array(	':object_id' => $object_id,
											':from_date' => $from_date,
											':to_date' => $to_date));

		if ( $req_return )
		{
			//return $this->dbh->lastInsertId();
			return true;
		}

		return false;
	}

	public function editReservation($reservation_id, $from_date, $to_date)
	{
		/*
		if ( $this->isObjectReservedInTimeframe($object_id, $from_date, $to_date) == true )
		{
			$this->setError('Object is already reserved in selected timeframe.');

			return false;
		}
		*/

		$sql = "UPDATE reservations SET from_date = :from_date, to_date = :to_date WHERE id = :reservation_id";
		$sth = $this->dbh->prepare($sql);

		$req_return = $sth->execute(array(	':from_date' => $from_date,
											':to_date' => $to_date,
											':reservation_id' => $reservation_id));

		if ( $req_return )
		{
			return true;
		}

		return false;
	}

	public function deleteReservation($reservation_id)
	{
		$sql = "DELETE FROM reservations WHERE id = :reservation_id";
		$sth = $this->dbh->prepare($sql);

		$req_return = $sth->execute(array(	'reservation_id' => $reservation_id));

		if ( $req_return )
		{
			return true;
		}

		return false;
	}

	/* GRID/MAP FUNCTIONS */
	private function setGridDimensions()
	{
		if ( !empty($this->objects) && is_array($this->objects) )
		{
			// get the number of columns and rows for the whole map
			foreach ($this->objects as $object)
			{
				if ($object['pos_y'] > $this->col)
				{
					$this->col = $object['pos_y'];
				}
				if ($object['pos_x'] > $this->row)
				{
					$this->row = $object['pos_x'];
				}
			}

			if ( $this->col > 0 || $this->row > 0 )
			{
				return true;
			}
		}

		throw new Exception("Error: No objects loaded.", 1);
	}

	public function createGrid()
	{
		// get the object in the setted category
		$this->getObjectsByCategory();

		// set the dimensions of the grid
		$this->setGridDimensions();

		// calculate the width of a column
		$col_width = $this->getColumnWidth();

		// create header with letters in it
		// add opening ul-tag
		$this->map_html .= '<ul class="map">';

		// add li-elements
		$i = 1;

		// empty li-element as placeholder
		$this->map_html .= '<li class="map-x" style="width: ' . $this->y_width . '%;"></li>';

		while ($i <= $this->col)
		{
			$this->map_html .= '<li class="map-x" style="width: ' . $col_width . '%;"><b>' . $this->numberToChar($i) . '</b></li>';
			$i++;
		}

		// add closing ul-tag
		$this->map_html .= '</ul>';

		// create the all columns for each row

		// create the rows / rows loop
		for ($i = 1; $i <= $this->row; $i++)
		{
			// add the opening ul-tag for a row
			$this->map_html .= '<ul class="map" id="map-row">';

			// empty li element as placeholder
			$this->map_html .= '<li class="map-y" style="width: ' . $this->y_width . '%;"><b>' . $i . '</b></li>';

			for ($col_count = 1; $col_count <= $this->col; $col_count++)
			{
				// if $nope is set to true the field on the map is empty. we set it to false on the end of the first if-statement in the object loop if we get no match for a database entry on the requested position
				$nope = true;

				foreach ($this->objects as $object)
				{
					// if the object is placed on the current position in the map
					if ( $object['pos_x'] == $i && $object['pos_y'] == $col_count )
					{
						// get the reservation data for this object if the object is reservated in the previous setted timeframe
						$reservation = $this->getReservationByObjectID($object['id']);

						// if object has a reservation
						if ( $reservation != false )
						{
							$cell_class = 'closed';
						}
						else
						{
							// the object is not reserved
							$cell_class = 'free';
						}

						// add a reserved object opening li-element
						$this->map_html .= '<li class="map map-cell ' . $cell_class . ' text-center" id="map-col-' . $object['id'] . '" style="width: ' . $col_width . '%; height: 100%;">';

						// add hidden inputfields
						$this->map_html .= '<input type="hidden" name="obj_id" id="obj_id" value="' . htmlspecialchars(json_encode($object)) . '">';
						
						if ( $reservation == false )
						{
							$reservation = 0;
						}

						$this->map_html .= '<input type="hidden" name="res_id" id="res_id" value="' . htmlspecialchars(json_encode($reservation)) . '">';

						// add information about the reservation/object to the li-element
						if ( $this->cell_content == null )
						{
							
							$this->map_html .= '<div><b>' . $this->numberToChar($col_count) . $i . '</b></div>';
							
						}
						else
						{
							$this->map_html .= $this->cell_content;
						}

						// add a reserved object closing li-tag
						$this->map_html .= '</li>'; 

						// a object/reservation was placed in the map so there is no need to add a empty field
						$nope = false;
					}
				}

				// if $nope is false at this location we had no hit for this x/y position in the database. that means the field is a empty field with no object in it
				if ($nope == true)
				{
					// we put an empty field on this position
					$this->map_html .= '<li class="map none" id="map-col" style="width: ' . $col_width . '%;">';
				}
			}

			// add the closing ul-tag
			$this->map_html .= '</ul>';
		}
	}

	public function showGrid()
	{
		if ( !empty($this->map_html) )
		{
			echo $this->map_html;

			return true;
		}

		return false;
	}

	/* HELPER FUNCTIONS */
	public function getColumnWidth()
	{
		if ( isset($this->col) && !empty($this->col) && $this->col > 0 )
		{
			return (100 - $this->y_width) / $this->col;
		}

		throw new Exception("Error: No number of columns set.", 1);
	}

	public function numberToChar($number)
	{
		return strtoupper(chr($number + 96)); 
	}

	public function validateDate($date)
	{
		$datetime_result = DateTime::createFromFormat("Y-m-d", $date);

		if ( $datetime_result != false )
		{
			return true;
		}

		return false;
	}

	private function getDaysBetweenDates($from_date, $to_date)
	{
		// check from_date
		if ( !isset($from_date) || empty($from_date) )
		{
			throw new Exception("Error: from_date is not set.", 1);
		}
		else
		{
			if ( $this->validateDate($from_date) == false )
			{
				throw new Exception("Error: from_date is invalid.", 1);
			}
		}

		// check to_date
		if ( !isset($to_date) || empty($to_date) || $from_date == $to_date )
		{
			// if no to_date is set or it is the same as from_date its always one day
			return 1;
		}
		else
		{
			if ( $this->validateDate($to_date) == false )
			{
				throw new Exception("Error: to_date is invalid.", 1);
			}
		}

		// calculate the days between the dates
		$from_date = new DateTime($from_date);
		$to_date = new DateTime($to_date);

		$interval = $from_date->diff($to_date);
		$days = $interval->format('%a');

		return intval($days + 1);
	}

	private function getDate()
	{
		return date('Y-m-d');
	}

	private function isObjectReservedInTimeframe($object_id, $from_date, $to_date = null)
	{
		if ( is_numeric($object_id) )
		{
			$object_id = intval($object_id);

			if ( $this->validateDate($from_date) == false )
			{
				return false;
			}

			if ( $to_date == null )
			{
				$to_date = $from_date;
			}
			else
			{
				if ( $this->validateDate($to_date) == false )
				{
					return false;
				}
			}

			$sql = "SELECT * FROM reservations WHERE from_date <= :to_date AND to_date >= :from_date AND object_id = :object_id";
			$sth = $this->dbh->prepare($sql);

			$sth->execute(array(	':to_date' => $to_date,
								 	':from_date' => $from_date,
								 	':object_id' => $object_id ));

			if ( $sth->rowCount() > 0 )
			{
				return true;
			}

			return false;
		}
		else
		{
			throw new Exception("Error: Object ID must be numeric.", 1);
		}

		return false;
	}

	private function isObjectReservedInFuture($object_id)
	{
		if ( is_numeric($object_id) )
		{
			$object_id = intval($object_id);

			$actual_date = $this->getDate();

			$sql = "SELECT * FROM reservations WHERE from_date >= :actual_date OR to_date >= :actual_date AND object_id = :object_id";
			$sth = $this->dbh->prepare($sql);

			$sth->execute(array(':actual_date' => $actual_date,
								'object_id' => $object_id));

			if ( $sth->rowCount() > 0 )
			{
				return true;
			}
		}
		else
		{
			throw new Exception("Error: Object ID must be numeric.", 1);
		}

		return false;
	}

	private function isObjectPositionEmpty($pos_x, $pos_y, $category)
	{
		$sql = "SELECT * FROM objects WHERE category = :category";
		$sth = $this->dbh->prepare($sql);

		$sth->execute(array(':category' => $category));

		if ( $sth->rowCount() == 0 )
		{
			return true;
		}

		$sql = "SELECT * FROM objects WHERE category = :category AND pos_x = :pos_x AND pos_y = :pos_y";
		$sth = $this->dbh->prepare($sql);

		$sth->execute(array(':category' => $category,
							':pos_x' => $pos_x,
							':pos_y' => $pos_y));

		if ( $sth->rowCount() == 0 )
		{
			return true;
		}

		return false;
	}

	private function setError($error_text)
	{
    	$trace = debug_backtrace();
    	$function_name = $trace[1]['function'];

    	$error_text = strip_tags($error_text);
    
    	$this->error[] = array(	'function' => $function_name,
    							'text' => $error_text);
	}

	private function debug($var)
	{
		$logfile = fopen('debug.log', 'a+');
		fwrite($logfile, print_r($var, 1));
		fclose($logfile);
	}

}

?>
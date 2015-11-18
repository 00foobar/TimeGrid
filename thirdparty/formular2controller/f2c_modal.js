	/* Formular2Controller -> EXTENDED FOR MODALS marked with @MODAL*/
	function Formular2Controller(formular, controller_action, controller_path, modal_prefix, object_id, reservation_id)
	{
		// reference to the "object" itself because this is context-based
		var that = this;

		this.formular = formular;
		this.controller_action = controller_action;
		this.controller_path = controller_path;
		this.modal_prefix = modal_prefix;
		this.formular_elements = {};

		// @MODAL
		if( typeof object_id != 'undefined' )
		{
			this.object_id = object_id;
		}
		else
		{
			this.object_id = false;
		}
		// @MODAL
		if( typeof reservation_id != 'undefined' )
		{
			this.reservation_id = reservation_id;
		}
		else
		{
			this.reservation_id = false;
		}		

		// fire
		this.requestPublic = function()
		{
			var result = requestController();

			return result;
		}

		// private method - calling (POST) the controller with an AJAX-request
		function requestController()
		{
			// add the form elements to that.formular_elements
			getFormElements();

			// get the property-count of the object (used as assoc array)
			var size = getObjectSize(that.formular_elements);

			// if no elements where send dont do the request
			if ( size > 0 )
			{
				// add the action parameter
				that.formular_elements['action'] = controller_action;

				// clear object (assoc array) from empty properties (keys) exp. submit button field
				delete that.formular_elements[''];

				// @MODAL
				$(modal_prefix + 'success').hide();
				$(modal_prefix + 'failure').hide();
				
				//@MODAL
				if ( that.object_id != false )
				{
					that.formular_elements['object_id'] = that.object_id;
				}

				//@MODAL
				if ( that.reservation_id != false )
				{
					that.formular_elements['reservation_id'] = that.reservation_id;
				}

				// do the POST request
				$.ajax({
					type: "POST",
					url: that.controller_path,
					data: that.formular_elements,
					success: function(result) {
						// success code
						var success = $.parseJSON(result);

						if ( success === true )
						{
							successReaction();
							return true;
						}
						else
						{
							failureReaction();
							return false;
						}
					},
					error: function(result) {
						// failure code
						console.log('Error: ');
						console.log(result);
						return false;
					}

				});
			}
			else
			{
				return false;
			}
		}

		// private method - add the form elements with element-id and element-value to the object (used as assoc array)
		function getFormElements()
		{
			$(that.formular).filter(':input').each(function()
			{
				that.formular_elements[this.id] = this.value;
			});
		}

		// private method - count properties of the object 
		function getObjectSize(obj)
		{
			var size = 0, key;
					
			for (key in obj)
			{
				if (obj.hasOwnProperty(key)) size++;
			}

			return size;
		};

		// private method - stuff to do if AJAX-request was successfull
		function successReaction()
		{
			// maybe add code here
			// @MODAL
			$(that.modal_prefix + 'success').fadeIn();

			// @TODO AUSKOMMENTIEREN
			/*
			setTimeout(function()
			{
				location.reload();
			}, 1000);
			*/
		}

		// private method - stuff to do if AJAX-request was NOT successfull
		function failureReaction()
		{
			// maybe add code here
			// @MODAL
			$(that.modal_prefix + 'failure').fadeIn();
		}

	}
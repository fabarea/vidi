"use strict"; // jshint ;_;

/** @namespace Vidi */

/**
 * Object for handling "edit" actions.
 *
 * @type {Object} Vidi.Edit
 */
Vidi.Edit = {

	/**
	 * Object for storing labels of the Dialog window.
	 */
	label: {
		save: 'Save'
	},

	/**
	 * jQuery reference to the cell being edited, can be many.
	 */
	editedCells: null,

	/**
	 * The content being edited.
	 */
	contentIdentifier: null,

	/**
	 * The content being edited.
	 */
	isMassEditingRelation: false,

	/**
	 * Initialize listener
	 *
	 * @return void
	 */
	initialize: function () {

		/**
		 * Load the generic "edit relation" form for mm relations.
		 */
		$(document).on('click', '.dataTable tbody .btn-edit-relation', function (e) {
			e.preventDefault();

			// Call the Edit routine which will pop-up the modal window.
			Vidi.Edit
				.setLabelSave('Save relation')
				.setIsMassEditingRelation(false)
				.setEditedCells($(this).closest('td'))
				.setRowIdentifier(Vidi.Grid.getRowIdentifier(this))
				.loadContent($(this).attr('href'))
				.showWindow();
		});

		/**
		 * Add handler against pencil icon located in the header on the grid.
		 */
		$('.mass-edit-relation').click(function(e) {
			e.preventDefault();
			e.stopPropagation(); // Important to stop event propagation to not change ordering and a Grid reload.

			var columnPosition = $(this).parent().index(); // corresponds to "th".
			var editedCells = Vidi.Edit.getEditedCells(columnPosition);
			var url = Vidi.Edit.getMassEditUrl($(this).attr('href'));

			// Call the Edit routine which will pop-up the modal window.
			Vidi.Edit
				.setLabelSave('')
				.setIsMassEditingRelation(true)
				.setEditedCells(editedCells)
				.setRowIdentifier(null)
				.loadContent(url)
				.showWindow();
		});

		/**
		 * Add handler against pencil icon located in the header on the grid.
		 */
		$('.mass-edit-scalar').click(function(e) {
			e.preventDefault();
			e.stopPropagation(); // Important to stop event propagation to not change ordering and a Grid reload.

			var columnPosition = $(this).parent().index(); // corresponds to "th".
			var editedCells = Vidi.Edit.getEditedCells(columnPosition);
			var url = Vidi.Edit.getMassEditUrl($(this).attr('href'));

			// Call the Edit routine which will pop-up the modal window.
			Vidi.Edit
				.setLabelSave('Save')
				.setIsMassEditingRelation(false)
				.setEditedCells(editedCells)
				.setRowIdentifier(null)
				.loadContent(url)
				.showWindow();
		});
	},

	/**
	 * Bind edit buttons in list view.
	 *
	 * @return void
	 */
	attachHandlerInGrid: function() {

		// bind the click handler script to the newly created elements held in the table
		$('.btn-edit').bind('click', function(e) {
			Vidi.Session.set('lastEditedUid', $(this).data('uid'));
		});

		// Make a row selectable.
		$('.checkbox-row').bind('click', function(e) {
			var checkboxes;

			$(this)
				.closest('tr')
				.toggleClass('active');
			e.stopPropagation(); // We don't want the event to propagate.

			checkboxes = $('#content-list').find('.checkbox-row').filter(':checked');

			// Update the mass action menu label.
			Vidi.Grid.updateMassActionMenu();
		});

		// Add listener on the row as well
		$('.checkbox-row')
			.parent()
			.css('cursor', 'pointer')
			.bind('click', function(e) {
				$(this).find('.checkbox-row').click()
			});
	},

	/**
	 * Get reference to cells being edited.
	 *
	 * @param {int} columnPosition
	 * @return string
	 * @private
	 */
	getEditedCells: function (columnPosition) {
		var editedCells;

		if (Vidi.Grid.hasSelectedRows()) {

			var selectedRows = Vidi.Grid.getSelectedRows();

			// Case 1: mass editing for selected rows.
			editedCells = $(selectedRows).find('td:nth-child(' + (columnPosition + 1) + ')');

		} else {

			// Case 2: mass editing for all rows.
			editedCells = $('#content-list').find('tr td:nth-child(' + (columnPosition + 1) + ')');
		}

		return editedCells;
	},

	/**
	 * Get the mass edit URL.
	 *
	 * @param {string} url
	 * @return string
	 * @private
	 */
	getMassEditUrl: function (url) {

		var uri = new Uri(url);
		var parametersToKeep = ['iSortCol_0', 'sSortDir_0'];

		if (Vidi.Grid.hasSelectedRows()) {
			// Case 1: mass editing for selected rows.

			// Add parameters to the Uri object.
			uri.addQueryParam(Vidi.module.parameterPrefix + '[matches][uid]', Vidi.Grid.getSelectedIdentifiers().join(','));

		} else {

			// Case 2: mass editing for all rows.
			parametersToKeep.push('sSearch');
		}

		// Keep only certain parameters which make sense to transmit.
		for (var index in Vidi.Grid.getStoredParameters()) {
			var parameter = Vidi.Grid.getStoredParameters()[index];

			// Keep only certain parameters which make sense to transmit.
			if ($.inArray(parameter.name, parametersToKeep) > -1) {
				uri.addQueryParam(parameter.name, parameter.value);
			}
		}

		// Fix a bug in URI object. URL should looks like mod.php?xyz and not mod.php/?xyz
		return uri.toString().replace('.php/?', '.php?');
	},

	/**
	 * Load content by ajax.
	 *
	 * @param {string} url
	 * @return Vidi.Edit
	 */
	loadContent: function (url) {
		// Load content by ajax for the modal window.
		$.ajax(
			{
				type: 'get',
				url: url
			})
			.done(function (data) {
				$('.modal-body').html(data);

				// bind submit handler to form.
				$('#form-edit').on('submit', function (e) {

					// Prevent native submit.
					e.preventDefault();

					// Register
					$(this).ajaxSubmit({

						/**
						 * Before submit handler.
						 * @param arr
						 * @param $form
						 * @param options
						 * @returns {boolean}
						 */
						beforeSubmit: function (arr, $form, options) {

							// Only submit if button is not disabled
							if ($('.btn-save-relation').hasClass('disabled')) {
								return false;
							}

							// Else submit form
							$('.btn-save-relation').addClass('disabled');
						},

						/**
						 * On success call back
						 * @param response
						 */
						success: function (response) {

							// Hide the modal window
							bootbox.hideAll();

							Vidi.Response.processResponse(response, 'update');
						}
					})
				});

			})
			.fail(function (data) {
				alert('Something went wrong! Check out console log for more detail');
				console.log(data);
			});

		return Vidi.Edit;
	},

	/**
	 * Show the popup window.
	 *
	 * @return void
	 */
	showWindow: function () {

		// Display the empty modal box with default loading icon.
		// Its content is going to be replaced by the content of the Ajax request.
		var template = '<div style="text-align: center">' +
			'<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="" height="" alt="" />' +
			'</div>';

		var modalWindowConfiguration = [
			{
				'label': 'Cancel'
			}
		];

		// Mass editing requires to change the buttons of the modal window: append, remove, replace button.
		if (Vidi.Edit.isMassEditingRelation) {

			// Push configuration for "relation" editing: "remove" relation case.
			modalWindowConfiguration.push({
				'label': 'Remove Relation',
				'class': 'btn-save-relation',
				'callback': function() {

					// Set "hidden" controller by JavaScript.
					$('#savingBehaviorRemove').click();

					// Show to the User the grid is being refreshed.
					Vidi.Edit.editedCells.html('<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" alt="" />');

					$('#form-edit').submit();

					// Prevent modal closing ; modal window will be closed after submitting.
					return false;
				}
			});

			// Push configuration for "relation" editing: "append" relation case.
			modalWindowConfiguration.push({
				'label': 'Append Relation',
				'class': 'btn-save-relation',
				'callback': function() {

					// Set "hidden" controller by JavaScript.
					$('#savingBehaviorAppend').click();

					// Show to the User the grid is being refreshed.
					Vidi.Edit.editedCells.html('<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" alt="" />');

					$('#form-edit').submit();

					// Prevent modal closing ; modal window will be closed after submitting.
					return false;
				}
			});

			// Push configuration for "relation" editing: "replace" relation case.
			modalWindowConfiguration.push({
				'label': 'Replace Relation',
				'class': 'btn-primary btn-save-relation',
				'callback': function() {

					// Set "hidden" controller by JavaScript.
					$('#savingBehaviorReplace').click();

					// Show to the User the grid is being refreshed.
					Vidi.Edit.editedCells.html('<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" alt="" />');

					$('#form-edit').submit();

					// Prevent modal closing ; modal window will be closed after submitting.
					return false;
				}
			});


		} else {

			// Push configuration for "scalar" editing.
			modalWindowConfiguration.push({
				'label': Vidi.Edit.label.save,
				'class': 'btn-primary btn-save-relation',
				'callback': function() {

					// Show to the User the grid is being refreshed.
					Vidi.Edit.editedCells.html('<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" alt="" />');

					$('#form-edit').submit();

					// Prevent modal closing ; modal window will be closed after submitting.
					return false;
				}
			});
		}
		bootbox.dialog(
			template,
			modalWindowConfiguration, {
			onEscape: function () {
				// Empty but required function to have escape keystroke hiding the modal window.
			}
		});
	},

	/**
	 * @param label
	 * @return Vidi.Edit
	 */
	setLabelSave: function(label) {
		Vidi.Edit.label.save = label;
		return Vidi.Edit;
	},

	/**
	 * @param editedCells
	 * @returns Vidi.Edit
	 */
	setEditedCells: function(editedCells) {
		Vidi.Edit.editedCells = editedCells;
		return Vidi.Edit;
	},

	/**
	 * @param isMassEditingRelation
	 * @returns Vidi.Edit
	 */
	setIsMassEditingRelation: function(isMassEditingRelation) {
		Vidi.Edit.isMassEditingRelation = isMassEditingRelation;
		return Vidi.Edit;
	},

	/**
	 * @param {object} contentIdentifier
	 * @returns Vidi.Edit
	 */
	setRowIdentifier: function(contentIdentifier) {
		Vidi.Edit.contentIdentifier = contentIdentifier;
		return Vidi.Edit;
	}

};

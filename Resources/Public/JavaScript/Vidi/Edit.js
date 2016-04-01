// jshint ;_;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: Fab/Vidi/Vidi/Edit
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Modal',
	'Fab/Vidi/Vidi/Grid',
	'Fab/Vidi/Vidi/Response',
	'Fab/Vidi/Vidi/Session'
], function($, Modal) {
	'use strict';

	var Edit = {

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
		 * The DOM element
		 */
		element: null,

		/**
		 * The URL to be used.
		 */
		url: '',

		/**
		 * Initialize listener
		 *
		 * @return void
		 */
		attachHandler: function() {

			/**
			 * Load the generic "edit relation" form for mm relations.
			 */
			$(document).on('click', '.dataTable tbody .btn-edit-relation', function(e) {
				e.preventDefault();

				// Call the Edit routine which will pop-up the modal window.
				Vidi.Edit
					//.setLabelSave(TYPO3.l10n.localize('save.relations'))
					.setElement(this)
					.setIsMassEditingRelation(false)
					.setEditedCells($(this).closest('td'))
					.setRowIdentifier(Vidi.Grid.getRowIdentifier(this))
					.setUrl($(this).attr('href'))
					.showWindow();
			});

			/**
			 * Add handler against pencil icon located in the header on the grid.
			 */
			$(document).on('click', '.mass-edit-relation', function(e) {
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
					.setUrl(url)
					.showWindow();
			});

			/**
			 * In case the User hit "enter", submit the form.
			 */
			$(document).on('keydown', '.form-control-expression', function(e) {
				if (e.keyCode === 13) {
					$('.btn-save-relation').click();
					e.preventDefault();
				}
			});

			/**
			 * Add handler against pencil icon located in the header on the grid.
			 */
			$(document).on('click', '.mass-edit-scalar', function(e) {
				e.preventDefault();
				e.stopPropagation(); // Important to stop event propagation to not change ordering and a Grid reload.

				var columnPosition = $(this).parent().index(); // corresponds to "th".
				var editedCells = Vidi.Edit.getEditedCells(columnPosition);
				var url = Vidi.Edit.getMassEditUrl($(this).attr('href'));

				// Call the Edit routine which will pop-up the modal window.
				Vidi.Edit
					.setLabelSave(TYPO3.l10n.localize('save'))
					.setIsMassEditingRelation(false)
					.setEditedCells(editedCells)
					.setRowIdentifier(null)
					.setUrl(url)
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
		getEditedCells: function(columnPosition) {
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
		getMassEditUrl: function(url) {

			var uri = new Uri(url);

			// Keep order in any case.
			var storedParameters = Vidi.Grid.getStoredParameters();
			if (typeof storedParameters.order === 'object') {
				uri.addQueryParam('order[0][column]', storedParameters.order[0].column);
				uri.addQueryParam('order[0][dir]', storedParameters.order[0].dir);
			}

			// Case 1: mass editing for selected rows.
			if (Vidi.Grid.hasSelectedRows()) {

				// Add parameters to the Uri object.
				uri.addQueryParam(Vidi.module.parameterPrefix + '[matches][uid]', Vidi.Grid.getSelectedIdentifiers().join(','));

			} else { // Case 2: mass editing for all rows.

				if (typeof storedParameters.search === 'object') {
					uri.addQueryParam('search[value]', storedParameters.search.value);
				}
			}

			// Fix a bug in URI object. URL should looks like index.php?xyz and not index.php/?xyz
			return uri.toString().replace('.php/?', '.php?');
		},

		/**
		 * Set possible additional parameters for Ajax.
		 *
		 * @param {string} url
		 * @return {string}
		 */
		setAjaxAdditionalParameters: function(url) {

			var additionalParametersList = $('#ajax-additional-parameters').val();
			if (additionalParametersList) {
				var additionalParameters = additionalParametersList.split('&');
				for (var i = 0; i < additionalParameters.length; i++) {
					var splitValues = additionalParameters[i].split('=');
					if (splitValues.length === 2) {
						var parameterName = splitValues[0];
						var parameterValue = splitValues[1];

						// Add parameters to the Uri object.
						url += '&' + Vidi.module.parameterPrefix + '[' + parameterName + ']=' + parameterValue;
					}
				}
			}
			return url;
		},

		/**
		 * @return array
		 */
		getButtons: function() {

			var buttons = [
				{
					text: TYPO3.l10n.localize('cancel'),
					btnClass: 'btn btn-default',
					trigger: function() {
						Modal.dismiss();
					}
				}];

			// Mass editing requires to change the buttons of the modal window: append, remove, replace button.
			if (Vidi.Edit.isMassEditingRelation) {


				// Push configuration for "relation" editing: "remove" relation case.
				buttons.push({
					text: TYPO3.l10n.localize('relation.remove'),
					btnClass: 'btn btn-default btn-save-relation',
					trigger: function() {

						// Set "hidden" controller by JavaScript.
						$('#savingBehaviorRemove', Vidi.modal).click();
						Vidi.Edit.submit();
					}
				});

				// Push configuration for "relation" editing: "append" relation case.
				buttons.push({
					text: TYPO3.l10n.localize('relation.append'),
					btnClass: 'btn  btn-default btn-save-relation',
					trigger: function() {

						// Set "hidden" controller by JavaScript.
						$('#savingBehaviorAppend', Vidi.modal).click();
						Vidi.Edit.submit();
					}
				});

				// Push configuration for "relation" editing: "replace" relation case.
				buttons.push({
					text: TYPO3.l10n.localize('relation.replace'),
					btnClass: 'btn btn-primary btn-save-relation',
					trigger: function() {

						// Set "hidden" controller by JavaScript.
						$('#savingBehaviorReplace', Vidi.modal).click();
						Vidi.Edit.submit();
					}
				});

			} else {
				buttons.push({
					text: TYPO3.l10n.localize('save.relations'),
					btnClass: 'btn btn-primary btn-save-relation',
					trigger: function() {
						Vidi.Edit.submit();
					}
				});
			}
			return buttons;
		},

		/**
		 * @return void
		 */
		submit: function() {

			// Show to the User the grid is being refreshed.
			Vidi.Edit.editedCells.html('<div style="padding-left: 20px"><img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" alt="" /></div>');

			// Ajax request
			$.ajax({
				url: $('#form-edit', Vidi.modal).attr('action'),
				data: $('#form-edit', Vidi.modal).serialize(),
				method: 'POST',
				beforeSend: function(arr, $form, options) {

					// Only submit if button is not disabled
					if ($('.btn-save-relation', Vidi.modal).hasClass('disabled')) {
						return false;
					}

					// Else submit form
					$('.btn-save-relation', Vidi.modal).addClass('disabled');
				},
				/**
				 * On success call back
				 *
				 * @param response
				 */
				success: function(response) {

					// Hide the modal window
					Modal.dismiss();

					Vidi.Response.processResponse(response, 'update');
				}
			});
		},

		/**
		 * Show the popup window.
		 *
		 * @return void
		 */
		showWindow: function() {

			Vidi.modal = Modal.loadUrl(
				TYPO3.l10n.localize('action.edit') + ' ' + this.getFieldLabel(),
				top.TYPO3.Severity.warning,
				this.getButtons(),
				this.url,
				function() { // callback

					// Update modal title
					var numberOfObjects = $('#numberOfObjects', Vidi.modal).html();

					var modalTitle = $('.modal-title', Vidi.modal).html() + ' - ' + numberOfObjects + ' ';
					if (numberOfObjects > 1) {
						modalTitle += TYPO3.l10n.localize('records');
					} else {
						modalTitle += TYPO3.l10n.localize('record');
					}
					//console.log(modalTitle);
					$('.modal-title', Vidi.modal).html(modalTitle);
				}
			);
		},

		/**
		 * @return string
		 */
		getFieldLabel: function() {
			var label = $(this.element).data('field-label');
			return typeof label === 'string' ? label : '';
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
		 * @param {Object} element
		 * @returns Vidi.Edit
		 */
		setElement: function(element) {
			this.element = element;
			return Vidi.Edit;
		},

		/**
		 * @returns Vidi.Edit
		 */
		setUrl: function(url) {
			// Inject additional parameters for the ajax request
			this.url = Vidi.Edit.setAjaxAdditionalParameters(url);
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

	// Expose in Vidi object for compatibility reason.
	Vidi.Edit = Edit;
	return Edit;
});
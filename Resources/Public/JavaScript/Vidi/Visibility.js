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
 * Module: Fab/Vidi/Vidi/Visibility
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Notification',
	'TYPO3/CMS/Backend/Icons'
], function($, Notification, Icons) {
	'use strict';

	var Visibility = {

		/**
		 * {string}
		 */
		loadingIcon: '',

		/**
		 * @return object
		 */
		attachHandler: function() {

			/**
			 * Handler for visibility toggle.
			 */
			$(document).on('click', '.btn-visibility-toggle', function(e) {
				e.preventDefault();

				// Compute row position before updating the cell.
				var position = Vidi.Grid.getRowPosition(this);

				// Store values.
				var $container = $(this).parent();

				// Add visible columns in the request.
				var url = $(this).attr('href');
				url += '&' + Vidi.module.parameterPrefix + '[columns]=' + Vidi.Grid.getListOfVisibleColumns();

				// GUI: set loading icon.
				if (!Visibility.loadingIcon) {

					// Fetch loading icon if not already fetched and store its value for later use.
					Icons.getIcon('spinner-circle-dark', Icons.sizes.small).done(function(icon) {
						icon = '<a class="btn btn-default btn-sm" href="#">' + icon + '</a>';
						$container.html(icon);
						Visibility.loadingIcon = icon; // save value to be more efficient next time.
					});
				} else {
					$container.html(Visibility.loadingIcon);
				}

				// GUI remove tooltip
				$('.tooltip').remove();

				$.ajax(
					{
						url: url,
						context: this
					})
					.done(function(data) {

						if (data.errorMessages.length === 0) {

							// Update row content
							Vidi.grid.fnUpdate(data.row, position, null, false);
							Vidi.Grid.animateRow(data.processedObject.uid);
						} else {
							$.each(data.errorMessages, function(index) {
								Notification.error('', data.errorMessages[index]);
							})
						}

						// activate tooltip.
						var options = {
							container: 'body',
							placement: 'auto'
						};
						//$('[data-toggle="tooltip"]').tooltip('destroy').tooltip(options);
						$('[data-toggle="tooltip"]').tooltip(options);

					})
					.fail(function(data) {
						alert('Something went wrong! Check out console log for more detail');
						console.log(data);
					});

			});
		}
	};

	return Visibility;
});

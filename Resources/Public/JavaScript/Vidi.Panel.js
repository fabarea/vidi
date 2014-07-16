"use strict"; // jshint ;_;

/** @namespace Vidi */

/**
 * Object for handling panels
 *
 * @type {Object} Panel
 */
Vidi.Panel = {

	/**
	 * Display THE "form" panel
	 *
	 * @return void
	 */
	showForm: function () {

		// Store the loading message
		if (typeof Vidi.icons.loading == 'undefined') {
			Vidi.icons.loading = $('#container-main-sub').html();
		}
		this.togglePanel();
	},

	/**
	 * Display THE "list" panel
	 *
	 * @param {boolean} reloadTable
	 * @return void
	 */
	showList: function (reloadTable) {
		if (typeof (reloadTable) == 'undefined') {
			reloadTable = true;
		}

		// Remove footer and header markup.
		$('#footer > *').remove();
		$('#navbar-sub > *').remove();

		// Add loading message for the next time the panel is displayed
		$('#container-main-sub').html(Vidi.icons.loading);
		this.togglePanel();

		if (reloadTable) {
			Vidi.grid.fnDraw(false); // false = for keeping the pagination.
		}
	},

	/**
	 * Toggle visibility of various panels
	 *
	 * @private
	 * @return void
	 */
	togglePanel: function () {
		// Expand / Collapse widgets
		$(['container-main-top', 'container-main-sub', 'navbar-main', 'navbar-sub']).each(function (index, value) {
			$('#' + value).toggle();
		});

	}
};

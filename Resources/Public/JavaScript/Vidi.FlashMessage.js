"use strict";

/** @namespace Vidi */

/**
 * Object for handling flash messages
 *
 * @type {Object} FlashMessage
 */
Vidi.FlashMessage = {

	/**
	 * The stack containing the message
	 */
	stack: [],

	/**
	 * Stack
	 *
	 * @param {string} message
	 * @param {string} severity
	 */
	add: function (message, severity) {
		if (typeof severity == 'undefined') {
			severity = 'success';
		}
		this.stack.push({"message": message, "severity": severity});
	},

	/**
	 * Tell whether the stack contains messages.
	 *
	 * @return bool
	 */
	containsMessages: function () {
		return this.count() > 0;
	},

	/**
	 * Count the number of message whitin the stack.
	 *
	 * @return int
	 */
	count: function () {
		return this.stack.length;
	},

	/**
	 * Returns the last element of the stack and pops it out
	 *
	 * @return {Object}
	 */
	pop: function () {
		return this.stack.pop();
	},

	/**
	 * Display all message from the stack
	 *
	 * @param {boolean} fadeOut
	 * @return void
	 */
	showAll: function (fadeOut) {
		var flashMessage, message, output, index;

		// Clear stack first
		$(".flash-message").html('');

		while (flashMessage = this.pop()) {
			this.show(flashMessage['message'], flashMessage['severity'], fadeOut);
		}
	},

	/**
	 * Pop-up a flash message
	 *
	 * @param {string} message
	 * @param {string} severity
	 * @param {boolean} fadeOut
	 */
	show: function (message, severity, fadeOut) {

		if (typeof fadeOut === "undefined") {
			fadeOut = true;
		}

		var positionWidthCss, width, output;

		// Compute positioning of the flash message box
		width = $('.flash-message').outerWidth();
		positionWidthCss = '-' + width / 2 + 'px';

		// Prepare output
		output = '<div class="alert alert-' + severity + '"><button type="button" class="close" data-dismiss="alert">&times;</button>' + message + '</div>';

		// Manipulate DOM to display flash message
		$(".flash-message").append($(output)).css("margin-left", positionWidthCss);
		if (fadeOut) {
			$(".alert").delay(2000).fadeOut("slow", function () {
				$(this).remove();
			});
		}
	}
};


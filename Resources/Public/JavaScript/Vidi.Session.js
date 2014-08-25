"use strict";

/** @namespace Vidi */

/**
 * Object for handling session
 *
 * @type {Object} Event
 */
Vidi.Session = {

	/**
	 * Get a key in from the session.
	 *
	 * @param {string} key corresponds to an identifier
	 * @return mixed
	 */
	get: function (key) {
		var result;
		result = null;
		if (window.sessionStorage) {
			key = this._getKey(key);
			result = sessionStorage.getItem(key);
		}
		return result;
	},

	/**
	 * Set a key in session.
	 *
	 * @param {string} key corresponds to an identifier
	 * @param {string} value
	 * @return void
	 */
	set: function (key, value) {
		if (window.sessionStorage) {
			key = this._getKey(key);
			sessionStorage.setItem(key, value);
		}
	},

	/**
	 * Reset a key from the session.
	 *
	 * @param {string} key corresponds to an identifier
	 * @return void
	 */
	reset: function (key) {
		if (window.sessionStorage) {
			key = this._getKey(key);
			sessionStorage.setItem(key, '');
		}
	},

	/**
	 * Tell whether a value exists for a key.
	 *
	 * @param {string} key corresponds to an identifier
	 * @return bool
	 */
	has: function (key) {
		return this.get(key) != null && this.get(key) != '';
	},

	/**
	 * Get a "formatted" key according the current module.
	 *
	 * @param {string} key corresponds to an identifier
	 * @return string
	 * @private
	 */
	_getKey: function (key) {
		return 'vidi.' + Vidi.module.dataType + '.' + key;
	}
};

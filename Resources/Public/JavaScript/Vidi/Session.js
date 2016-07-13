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
 * Module: Fab/Vidi/Vidi/Session
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Notification'
], function($, Notification) {
	'use strict';

	var Session = {

		/**
		 * Get a key in from the session.
		 *
		 * @param {string} key corresponds to an identifier
		 * @return mixed
		 */
		get: function(key) {
			var value = null;

			if (Vidi.Session.isPersistedPreference(key)) {

				// Possible value coming from the internal storage.
				value = Vidi.Session.getInternalStorage(key);

				// Fetch info from the User Data if possible value and make sure the preference signature corresponds.
				if (!value && Vidi.userPreferences.preferenceSignature === Vidi.module.preferenceSignature) {
					value = Vidi.userPreferences[key];
				}
			} else {
				var compositeKey = this.getKey(key);
				value = sessionStorage.getItem(compositeKey);
			}

			return value;
		},

		/**
		 * Set a key in session.
		 *
		 * @param {string} key corresponds to an identifier
		 * @param {string} value
		 * @return void
		 */
		set: function(key, value) {

			if (Vidi.Session.isPersistedPreference(key)) {

				Vidi.Session.setInternalStorage(key, value);
				$.ajax(
					{
						url: $('#link-user-preferences').attr('href'),
						method: 'post',
						data: Vidi.Session.getParameters(key, value)
					})
					.error(function(data) {
						Notification.error('Communication error', 'I could not save your preferences, something went wrong in the Ajax request!');
					});
			} else {
				var compositeKey = this.getKey(key);
				sessionStorage.setItem(compositeKey, value);
			}
		},

		/**
		 * Reset a key from the session.
		 *
		 * @param {string} key corresponds to an identifier
		 * @return void
		 */
		reset: function(key) {
			if (window.sessionStorage) {
				key = this.getKey(key);
				sessionStorage.setItem(key, null);
			}
		},

		/**
		 * Tell whether a value exists for a key.
		 *
		 * @param {string} key corresponds to an identifier
		 * @return bool
		 */
		has: function(key) {
			return this.get(key) != null && this.get(key) != '';
		},

		/**
		 * @param {string} key
		 * @return boolean
		 * @private
		 */
		getInternalStorage: function(key) {
			Vidi.Session.initialize(key);
			return Vidi.InternalStorage[key];
		},

		/**
		 * @param {string} key
		 * @param {string} value
		 * @return boolean
		 * @private
		 */
		setInternalStorage: function(key, value) {
			Vidi.Session.initialize(key);
			Vidi.InternalStorage[key] = value;
		},

		/**
		 * @param {string} key
		 * @return boolean
		 * @private
		 */
		isPersistedPreference: function(key) {
			return typeof Vidi.userPreferences[key] != 'undefined'
		},

		/**
		 *
		 * @param {string} key
		 * @param {string} value
		 * @returns {object}
		 * @private
		 */
		getParameters: function(key, value) {
			var parameters = {};
			var keyParameter = '{0}[key]'.format(Vidi.module.parameterPrefix);
			var valueParameter = '{0}[value]'.format(Vidi.module.parameterPrefix);
			parameters[keyParameter] = key;
			parameters[valueParameter] = value;
			return parameters;
		},

		/**
		 * Get a "formatted" key according the current module.
		 *
		 * @param {string} key corresponds to an identifier
		 * @return string
		 * @private
		 */
		getKey: function(key) {
			return 'vidi.' + Vidi.module.dataType + '.' + Vidi.module.preferenceSignature + '.' + key;
		},

		/**
		 * @private
		 */
		initialize: function(key) {
			Vidi.InternalStorage = Vidi.InternalStorage || {};
			if (!Vidi.InternalStorage[key]) {
				Vidi.InternalStorage[key] = null;
			}
		}
	};

	// Expose in Vidi object for compatibility reason.
	Vidi.Session = Session;
	return Session;
});


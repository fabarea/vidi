"use strict";

/** @namespace Vidi */

/**
 * Language object
 * @todo replace me with Core API lang
 *
 * @type {Object}
 */
Vidi.Label = {

	/**
	 * array containing all labels
	 */
	labels: Vidi.merge(TYPO3.lang, Vidi._labels),

	/**
	 *
	 * @param key
	 * @return string
	 */
	get: function (key) {
		return this.labels[key];
	}
};


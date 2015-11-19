$ = jQuery = TYPO3.jQuery.noConflict(true);


window.Vidi = window.Vidi || {};

/**
 * Merge second object into first one
 *
 * @param {object} set1
 * @param {object} set2
 * @return {object}
 */
Vidi.merge = function(set1, set2) {
	for (var key in set2) {
		if (set2.hasOwnProperty(key))
			set1[key] = set2[key]
	}
	return set1
};

/**
 * Add Format method to String
 *
 * "{0} is dead, but {1} is alive! {0} {2}".format("foo", "bar");
 * http://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format
 */
if (!String.prototype.format) {
	String.prototype.format = function() {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) {
			return typeof args[number] != 'undefined'
				? args[number] : match;
		});
	};
}

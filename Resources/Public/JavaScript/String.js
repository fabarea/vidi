/**
 * Prototype method for String object
 */

/**
 * Add Format method to String
 *
 * "{0} is dead, but {1} is alive! {0} {2}".format("foo", "bar");
 * http://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format
 */
if (!String.prototype.format) {
	String.prototype.format = function () {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function (match, number) {
			return typeof args[number] != 'undefined'
				? args[number] : match;
		});
	};
}
"use strict";

/** @namespace Vidi */

/**
 * Object for handling the Visual Search.
 *
 * @type {Object}
 */
Vidi.VisualSearch = {

	/**
	 * Store an instance of
	 */
	instance: null,

	/**
	 * @return void
	 */
	initialize: function() {

		Vidi.VisualSearch.instance = VS.init({
			container: $('.visual-search-container'),
			//query: '', // default query string.
			showFacets: true,
			unquotable: [], // unquotable for integer values.
			callbacks: {
				search: function(query, searchCollection) {

					var jsonQuery = JSON.stringify(searchCollection.facets());

					// Store in session the visual search query
					Vidi.Session.set('visualSearch.query', query);

					// Inject value in data table search and trigger a refresh
					$('input[aria-controls=content-list]').val(jsonQuery).keyup();
				},
				facetMatches: function(callback) {
					var facets = [];

					_.each(Vidi.module.grid.facets, function(label) {
						facets.push(label);
					});
					callback(facets);
				},
				valueMatches: function(facetLabel, searchTerm, callback) {

					// "text" is a special facet and must never suggest values.
					if (facetLabel === 'text') {
						return;
					}

					// Retrieve the facet name and suggest values to the User.
					var facetName = Vidi.VisualSearch.retrieveFacetName(facetLabel);
					if (facetName) {
						Vidi.VisualSearch.suggest(facetName, searchTerm, callback);
					}
				}
			}
		});
	},

	/**
	 * Retrieve a facet name according to a label.
	 *
	 * @param {string} facetLabel
	 * @return string
	 */
	retrieveFacetName: function(facetLabel) {

		// If no facet name is found for a label (e.g for "text"), returns the facet label as such.
		// The server will know how to handle that.
		var facetName = facetLabel;

		_.each(Vidi.module.grid.facets, function(label, _facetName) {
			if (label == facetLabel) {
				facetName = _facetName;
			}
		});

		return facetName;
	},

	/**
	 * Convert the Visual Search expression containing labels to values
	 * to be understand by the server such as field name and numerical value.
	 *
	 * @param {string} searchExpression
	 * @return string
	 */
	convertExpression: function(searchExpression) {

		var convertedExpression = [];
		if (searchExpression) {

			// In case the search expression has been fetched from the URL.
			searchExpression = decodeURIComponent(searchExpression);

			var facets = JSON.parse(searchExpression);
			_.each(facets, function(facet) {

				_.each(facet, function(searchTerm, facetLabel) {
					var facetName = Vidi.VisualSearch.retrieveFacetName(facetLabel);
					var value = Vidi.VisualSearch.retrieveValue(facetName, searchTerm);

					var convertedFacets = {};
					convertedFacets[facetName] = value;
					convertedExpression.push(convertedFacets);
				});
			});
		}

		return JSON.stringify(convertedExpression);
	},

	/**
	 * Retrieve the real value of a search term.
	 * If a corresponding value is not found, simply returns the search term.
	 *
	 * @param {string} facetName
	 * @param {string} searchTerm
	 * @return string
	 */
	retrieveValue: function(facetName, searchTerm) {

		var value = searchTerm;

		// Search for an equivalence label <-> value.
		_.each(Vidi.module.grid.suggestions[facetName], function(label, _value) {
			if (label == searchTerm) {
				value = _value;
			}
		});

		return value;
	},

	/**
	 * Suggest values to the User.
	 * Fetch the values from the value storage if possible, otherwise query the server.
	 *
	 * @param {string} facetName
	 * @param {string} searchTerm
	 * @param {function} callback
	 * @return void
	 * @private
	 */
	suggest: function(facetName, searchTerm, callback) {

		if (Vidi.module.grid.suggestions[facetName] == undefined) {

			// BEWARE! This code is never used as implemented but should be in the future.
			// @todo suggestions[facetName] must be destroyed in some cases after inline editing.

			// Fetch the suggestion values for the facet.
			$.ajax({
				url: $('#link-facet-suggest').attr('href'),
				dataType: "json",
				data: Vidi.VisualSearch.getParameters(facetName, searchTerm),
				success: function(data) {
					Vidi.module.grid.suggestions[facetName] = data;
					callback(Vidi.module.grid.suggestions[facetName])
				},
				error: function() {
					Vidi.VisualSearch.showError();
				}
			});
		} else {
			callback(Vidi.module.grid.suggestions[facetName]);
		}
	},

	/**
	 * Compute parameters with the prefix for the Vidi module.
	 *
	 * @return object
	 * @private
	 */
	getParameters: function(facetName, searchTerm) {
		var parameters = {};
		var facetParameter = '{0}[facet]'.format(Vidi.module.parameterPrefix);
		var searchTermParameter = '{0}[searchTerm]'.format(Vidi.module.parameterPrefix);
		parameters[facetParameter] = facetName;
		parameters[searchTermParameter] = searchTerm;
		return parameters;
	},

	/**
	 * Display a message error to the User that the Ajax request went wrong.
	 *
	 * @return void
	 * @private
	 */
	showError: function() {
		var message = 'Oups! Something went wrong when retrieving auto-suggestion values... Investigate the problem in the Network Monitor. <br />';
		Vidi.FlashMessage.add(message, 'error');
		var fadeOut = false;
		Vidi.FlashMessage.showAll(fadeOut);
	}
};

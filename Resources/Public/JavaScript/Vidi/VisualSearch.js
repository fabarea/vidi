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
 * Module: Fab/Vidi/VidiVisualSearch
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Notification'
], function($, Notification) {
	'use strict';

	var VisualSearch = {

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
				unquotable: [], // unquotable for integer values.
				callbacks: {
					search: function(query, searchCollection) {

						var jsonQuery = JSON.stringify(searchCollection.facets());

						// Store in session the visual search query.
						Vidi.Session.set('visualSearchQuery', query);

						// Inject value in data table search and trigger a refresh.
						$('input[aria-controls=content-list]').val(jsonQuery).keyup();
					},
					facetMatches: function(callback) {
						var facets = [];

						_.each(Vidi.module.search.facets, function(label) {
							facets.push(label);
						});
						callback(facets, {preserveOrder: true});
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

			// Load facet suggestions.
			Vidi.VisualSearch.loadAutoSuggestions();
		},

		/**
		 * Load facet suggestions.
		 *
		 * @return void
		 * @private
		 */
		loadAutoSuggestions: function() {

			// Fetch the suggestion values for the facet.
			$.ajax({
				url: $('#link-auto-suggests').attr('href'),
				dataType: "json",
				success: function(collections) {
					_.each(collections, function(values, fieldName) {
						Vidi.VisualSearch.processValues(fieldName, values);
					});

					// Set facet suggestions as loaded.
					Vidi.module.areFacetSuggestionsLoaded = true;
				},
				error: function() {
					Vidi.VisualSearch.showError();
				}
			});
		},

		/**
		 * Dispatch values between:
		 *
		 * - "labels" which only contains the label for the VisualSearch bar.
		 * - "values" which contains a key => value object to convert back label to value later.
		 *
		 * @param {string} fieldName
		 * @param {array|object} values
		 * @private
		 */
		processValues: function(fieldName, values) {

			var labels = [];
			var valueObject = {};

			_.each(values, function(value) {

				if (typeof(value) === 'object' && value) {

					// retrieve keys.
					var keys = Object.keys(value);
					var key = keys[0];
					var label = value[key];

					// Feed array labels
					labels.push(label);

					// Feed value object.
					valueObject[key] = label

				} else {
					labels.push(value)
				}
			});

			Vidi.module.search.values[fieldName] = valueObject;
			Vidi.module.search.labels[fieldName] = labels;
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

			_.each(Vidi.module.search.facets, function(label, _facetName) {
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
			_.each(Vidi.module.search.values[facetName], function(label, _value) {
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

			if (Vidi.module.search.labels[facetName] == undefined) {

				// BEWARE! This code is never used as implemented but should be in the future.
				// @todo suggestions[facetName] must be destroyed in some cases after inline editing.

				// Fetch the suggestion values for the facet.
				$.ajax({
					url: $('#link-auto-suggest').attr('href'),
					dataType: "json",
					data: Vidi.VisualSearch.getParameters(facetName, searchTerm),
					success: function(values) {
						Vidi.VisualSearch.processValues(facetName, values);
						callback(Vidi.module.search.labels[facetName])
					},
					error: function() {
						Vidi.VisualSearch.showError();
					}
				});
			} else {
				callback(Vidi.module.search.labels[facetName]);
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
			var message = 'Oups! Something went wrong when retrieving auto-suggestion values... Investigate the problem in the Network Monitor.';
			Notification.error('Communication error', message);
		}

	};

	// Expose in Vidi object for compatibility reason.
	Vidi.VisualSearch = VisualSearch;
	return VisualSearch;
});
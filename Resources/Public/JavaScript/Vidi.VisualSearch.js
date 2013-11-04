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
	 * @return object
	 */
	initialize: function () {

		var storage = [];

		Vidi.VisualSearch.instance = VS.init({
			container: $('.search_box_container'),
			// default query string
			//query: '',
			showFacets: true,
			// unquotable for integer values
			unquotable: [],
			callbacks: {
				search: function (query, searchCollection) {

					var jsonQuery = JSON.stringify(searchCollection.facets());

					// Store in session the visual search query
					Vidi.Session.set('visualSearch.query', query);

					// Inject value in data table search and trigger a refresh
					$('input[aria-controls=content-list]').val(jsonQuery).keyup();
				},
				facetMatches: function (callback) {
					callback(Vidi.module.tca.ctrl.searchFields);
				},
				valueMatches: function (facet, searchTerm, callback) {

					// Facet must never return values.
					if (facet === 'text') {
						return;
					}

					if (storage[facet] == undefined) {

						// compute parameters with specific prefix.
						var parameters = {};
						var facetParameter = '{0}[facet]'.format(Vidi.module.parameterPrefix);
						var searchTermParameter = '{0}[searchTerm]'.format(Vidi.module.parameterPrefix);
						parameters[facetParameter] = facet;
						parameters[searchTermParameter] = searchTerm;

						// compute data object
						$.ajax({
							type: 'post',
							url: Vidi.computeUrl('list', 'FacetValue'),
							dataType: "json",
							data: parameters,
							success: function (data, xhr, textStatus) {
								storage[facet] = data;
								callback(storage[facet])
							},
							error: function (jqXHR, textStatus, errorThrown) {
								console.log(jqXHR);
								console.log(textStatus);
								console.log(errorThrown);
							}
						});
					} else {
						callback(storage[facet]);
					}
				}
			}
		});
	}
}

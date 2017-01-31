/**
* Data-pesticides application
*/
$(function () {

	// Base map options to inherit from
	var baseMapOptions = {
		map: {
			name: "france_departments",
			zoom: {
				enabled: true,
				animDuration: 0,
				maxLevel: 12,
				buttons: {
					reset: {title: 'Réinitialiser'},
					in: {title: 'Zoomer'},
					out: {title: 'Dézoomer'}
				}
			},
			defaultArea: {
				attrs: {
					fill: "#23282e",
					stroke: "#424c59",
					"stroke-width": 1
				},
				attrsHover: {fill: "#23282e"}
			},
			defaultPlot: {
				attrs: {
					stroke: "#000",
					"stroke-width": 0.5
				},
				size: 5,
				attrsHover: {"stroke-width": 1, stroke: "#fff"}
			}
		},
		legend: {
			redrawOnResize: false,
			plot: {
				cssClass: 'legend',
				title: ' ', // Concentration en µg/L
				//titleAttrs: {"font-size": 16},
				labelAttrs: {"font-size": 14, fill: "#e1ffff"},
				labelAttrsHover: {fill: "#7AACFF", animDuration: 0},
				hideElemsOnClick: {opacity: 0, animDuration: 0},
				marginBottom: 12
			},
			area: {
				cssClass: 'legend',
				title: ' ', // Concentration en µg/L
				//titleAttrs: {"font-size": 16},
				labelAttrs: {"font-size": 14, fill: "#e1ffff"},
				labelAttrsHover: {fill: "#7AACFF", animDuration: 0},
				hideElemsOnClick: {opacity: 0.2, animDuration: 0},
				marginBottom: 12
			}
		},
		plots: {},
		areas: {}
	};

	// Base chart options for spline charts and areaspline charts
	var baseSplineChartOptions = {
		chart: {type: 'spline', backgroundColor: '#F5F5F5'},
		credits: {enabled: false},
		plotOptions: {
			series: {
				marker: {enabled: true},
				lineWidth: 4
			}
		},
		title: {text: ' '},
		xAxis: {
			categories: null
		},
		yAxis: {
			title: {text: 'Concentration en µg/L'},
			min: 0
		},
		tooltip: {valueSuffix: ' µg/L'},
		legend: {
			align: 'center',
			verticalAlign: 'bottom',
			borderWidth: 0
		},
		series: null
	};

	// Base chart options for bar charts and column charts
	var baseBarChartOptions = {
		chart: {type: 'bar', backgroundColor: '#F5F5F5'},
		credits: {enabled: false},
		plotOptions: {
			series: {}
		},
		title: {text: ''},
		xAxis: {
			categories: null
		},
		yAxis: {
			title: {text: 'Concentration (µg/L)'}
		},
		tooltip: {valueSuffix: 'µg/L'},
		legend: {
			align: 'center',
			verticalAlign: 'bottom',
			borderWidth: 0
		},
		series: null
	};

	/**
	 * Render a HTML template ith its content
	 * @param templateName
	 * @param data
	 * @returns {string|*|jQuery}
	 */
	var renderTemplate = function(templateName, data) {
		var template = $('script[type="text/template"]#' + templateName).text().trim();

		if (typeof data === 'object') {
			for (var id in data) {
				template = template.replace('%' + id + '%', data[id]);
			}
		}
		return template;
	};

	/**
	 * Get data from API through a promise
	 * @param url
	 * @returns Promise
	 */
	var getData = function (url) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: url,
				success: function (data) {
					resolve(data);
				},
				error: reject
			});
		});
	};

	// Handle welcome modal hidding
	$('body, .goButton').one('click', function () {
		$('.welcome').hide(1000);
	});

	$('.welcome').on('click', function (event) {
		event.stopPropagation();
	});

	// Init global options for Highchart
	Highcharts.setOptions({
		lang: {
			thousandsSep: ' ',
			decimalPoint: ','
		}
	});

	// Init empty map at page load
	$(".mapcontainer").mapael(baseMapOptions);

	/**
	 * Run the application initialisation when all the data from API are retrieved
	 */
	Promise.all([
		getData('/api/data/getYears'),
		getData('/api/data/getFamilies'),
		getData('/api/data/getStations'),
		getData('/api/data/getDepartments'),
		getData('/api/data/getCountryData'),
		getData('/api/data/getTotalConcentrationsByStation'),
		getData('/api/data/getTotalConcentrationsByDepartment'),
		getData('/api/data/getConcentrationsByFamilyAndStation'),
		getData('/api/data/getConcentrationsByFamilyAndDepartment'),
		getData('/api/data/getPesticidesInExcessByStation')
	]).then(function (data) {

		// Data retrieved from the API
		var years = data[0];
		var families = data[1];
		var stations = data[2];
		var departments = data[3];
		var countryData = data[4];
		var totalConcentrationsByStation = data[5];
		var totalConcentrationsByDepartment = data[6];
		var concentrationsByFamilyAndStation = data[7];
		var concentrationsByFamilyAndDepartment = data[8];
		var pesticidesInExcessByStation = data[9];

		// current application state
		var currentYear = '';
		var currentFamily = 'allFamilies';
		var currentEntityType = 'Station';
		var optimizedMode = false;

		// Year slider timeout
		var sliderTo = null;

		// Pesticides families extra data
		var pesticideFamiliesExtraData = {
			'pesticide-family-aldehydes-et-cetones': {code: 52},
			'pesticide-family-amides': {roles: 'Insecticides, Fongicides', code: 116},
			'pesticide-family-amines': {code: 107},
			'pesticide-family-azoles': {roles: 'Fongicides', code: 120},
			'pesticide-family-benzene-et-derives': {roles: 'Insecticides, Fongicides', code: 56},
			'pesticide-family-carbamates': {roles: 'Herbicides, Insecticides, Fongicides', code: 57},
			'pesticide-family-composes-phenoliques': {code: 55},
			'pesticide-family-diazines': {roles: 'Herbicides'},
			'pesticide-family-divers-organiques': {code: 61},
			'pesticide-family-organochlores': {roles: 'Insecticides', code: 64},
			'pesticide-family-organometalliques': {code: 45},
			'pesticide-family-organophosphores': {roles: 'Insecticides', code: 65},
			'pesticide-family-pyrethrinoides': {roles: 'Insecticides', code: 119},
			'pesticide-family-triazines-et-metabolites': {roles: 'Herbicides', code: 71},
			'pesticide-family-urees': {roles: 'Herbicides, Fongicides', code: 72}
		};

		// Entities labels
		var entityTypeLabels = {
			'Station': 'station de mesures',
			'Department': 'département'
		};

		/**
		 * Return map title depending on the current selected facets
		 * @param entityType Department|Station
		 * @param family family id (or 'allFamilies' for totals)
		 * @param year
		 * @returns {string}
		 */
		var getMapTitle = function (entityType, family, year) {
			var entityTypeLabel = entityTypeLabels[entityType];
			var title = '';

			if (family === 'allFamilies') {
				title = 'Concentration totale en pesticides';
			} else if (typeof families[family] !== 'undefined') {
				title = 'Concentration totale en ' + families[family]['label'].toLowerCase();
			} else {
				title = 'Concentration en pesticides';
			}
			title += ' par ' + entityTypeLabel + ' en ' + year;
			return title;
		};

		/**
		 * Return entity label (department or station) with prefix
		 * @param label string raw entity label to prefix
		 * @param type string Station|Department
		 * @returns {string}
		 */
		var getPrefixedEntityLabel = function (label, type) {
			var voyels = ['A', 'E', 'I', 'O', 'U', 'Y'];

			var plurialDepartments = ['Alpes-de-Haute-Provence', 'Hautes-Alpes', 'Alpes-Maritimes', 'Ardennes',
				'Bouches-du-Rhône', 'Côtes-d\'Armor', 'Landes', 'Pyrénées-Atlantiques', 'Hautes-Pyrénées',
				'Pyrénées-Orientales', 'Yvelines', 'Deux-Sèvres', 'Vosges', 'Hauts-de-Seine'];

			var feminineDepartments = ['Charente', 'Charente-Maritime', 'Corrèze', 'Corse-du-Sud', 'Haute-Corse',
				'Côte-d\'Or', 'Dordogne', 'Creuse', 'Drôme', 'Haute-Garonne', 'Gironde', 'Loire', 'Haute-Loire',
				'Loire-Atlantique', 'Lozère', 'Manche', 'Marne', 'Haute-Marne', 'Mayenne', 'Meurthe-et-Moselle',
				'Meuse', 'Moselle', 'Nièvre', 'Haute-Saône', 'Saône-et-Loire', 'Sarthe', 'Savoie', 'Haute-Savoie',
				'Seine-Maritime', '	Seine-et-Marne', 'Somme', 'Vendée', 'Vienne', 'Haute-Vienne',
				'Seine-Saint-Denis', 'Guadeloupe', 'Martinique', 'Guyane', 'La Réunion', 'Mayotte'
			];

			var apostrophDepartments = 'Hérault';

			if (type === 'Station') {
				if (voyels.indexOf(label.charAt(0)) !== -1) {
					return "dans la station d'" + label;
				}
				return "dans la station de " + label;

			} else {
				if (feminineDepartments.indexOf(label) !== -1) {
					return "dans les stations de la " + label;
				}
				if (plurialDepartments.indexOf(label) !== -1) {
					return "dans les stations des " + label;
				}
				if (voyels.indexOf(label.charAt(0)) !== -1 || apostrophDepartments.indexOf(label) !== -1) {
					return "dans les stations de l'" + label;
				}
				return "dans les stations du " + label;
			}
		};

		/**
		 * Returns tooltip title
		 * @param family
		 * @param year
		 * @returns string
		 */
		var getTooltipTitle = function (family, year) {
			var title = '';

			if (family === 'allFamilies') {
				title += 'Concentration totale en pesticides';
			} else if (typeof families[family] !== 'undefined') {
				title += 'Concentration en ' + families[family]['label'].toLowerCase();
			} else {
				title += 'Concentration en pesticides';
			}
			title += ' (' + year + ')';
			return title;
		};

		/**
		 * Get comparison string between an entity value and the country value
		 * @param value
		 * @returns {string}
		 */
		var getCountryComparisonString = function (value) {
			var color = 'darkred';
			var direction = '+';
			var comparisonValue = '';

			if (currentFamily === 'allFamilies') {
				comparisonValue = countryData['total']['country-fra'][currentYear][currentFamily];
			} else {
				comparisonValue = countryData['families']['country-fra'][currentYear][currentFamily];
			}
			comparisonValue = Math.round((value - comparisonValue) / comparisonValue * 1000) / 10;

			if (comparisonValue < 0) {
				color = "darkgreen";
				direction = '';
			}

			return "<b><span style=\"color:" + color + ";\">"
				+ direction + comparisonValue.toLocaleString()
				+ "%</span></b> par rapport à la moyenne nationale";
		};

		/**
		 * Display entity (station or department) details within a modal
		 * @param e
		 * @param id
		 */
		var displayEntityDetails = function (e, id) {
			var $modal = $('#dpmodal');
			var currentData = {};
			var concentrationLabel = '';
			var title = '';
			var body = '';
			var entityLabel = '';
			var prefixedEntityLabel = '';

			// Set current datasets to use
			if (currentEntityType === 'Station') {
				if (currentFamily === 'allFamilies') {
					currentData = totalConcentrationsByStation['data'];
				} else {
					currentData = concentrationsByFamilyAndStation['data'];
				}
			} else {
				if (currentFamily === 'allFamilies') {
					currentData = totalConcentrationsByDepartment['data'];
				} else {
					currentData = concentrationsByFamilyAndDepartment['data'];
				}
			}

			// Entity not found, don't display the modal
			if (typeof currentData[id] === 'undefined'
				|| typeof currentData[id][currentYear] === 'undefined'
				|| typeof currentData[id][currentYear][currentFamily] === 'undefined'
			) {
				return;
			}

			// Build entity title
			if (currentEntityType === 'Station') {
				entityLabel = stations[id]['label'] + ' (' + stations[id]['departmentLabel'] + ')';
			} else {
				entityLabel = departments[id]['label'];
			}
			prefixedEntityLabel = getPrefixedEntityLabel(entityLabel, currentEntityType);

            // Build modal title
			if (currentFamily === 'allFamilies') {
				concentrationLabel = 'concentration totale en pesticides';
				//title = 'Evolution de la ' + concentrationLabel + ' ' + prefixedEntityLabel;
				title = 'Les pesticides ' + prefixedEntityLabel;
			} else {
				concentrationLabel = 'concentration totale en ' + families[currentFamily]['label'].toLowerCase();
				//title = 'Evolution de la ' + concentrationLabel + ' ' + prefixedEntityLabel;
				title = 'Les pesticides ' + prefixedEntityLabel;
			}

			// Build label for the head line (with an external link for stations)
			var headLabel = prefixedEntityLabel;
			if (currentEntityType === 'Station' && typeof stations[id]['code'] !== 'undefined') {
				headLabel = '<a href="http://www.ades.eaufrance.fr/FichePtEau.aspx?code=' + stations[id]['code']
					+ '" target="_blank">' + headLabel + '</a>';
			}

			// Build modal head line
			body += renderTemplate('entity-headline', {
				'year': currentYear,
				'label': headLabel,
				'concentrationLabel': concentrationLabel,
				'value': currentData[id][currentYear][currentFamily].toLocaleString(),
				'comparisonString': getCountryComparisonString(currentData[id][currentYear][currentFamily])
			});

			if (currentEntityType === 'Station'
				&& typeof pesticidesInExcessByStation['pesticides'][id] !== 'undefined'
				&& typeof pesticidesInExcessByStation['pesticides'][id][currentYear] !== 'undefined'
			) {
			    // Display a warning text if one or more pesticides exceed the limit
				body += ' ' + renderTemplate('pesticides-in-excess-title', {"year": currentYear}) + " ";

				var first = true;
				var pesticidesInExcess = pesticidesInExcessByStation['pesticides'][id][currentYear]['pesticidesInExcess'];

				for (var pesticideName in pesticidesInExcess) {
					if (!first) {
						body += ', ';
					}
					body += '<a href="http://id.eaufrance.fr/par/' + pesticidesInExcess[pesticideName]['code']
						+ '" target="_blank">' + pesticideName
						+ '</a> (' + pesticidesInExcess[pesticideName]['concentration'].toLocaleString() + ' µg/L)';
					first = false;
				}
				body += '.';

                // Add a text line if the total concentration display the limit
				if (currentEntityType === 'Station'
					&& typeof pesticidesInExcessByStation['totalConcentrations'][id] !== 'undefined'
					&& typeof pesticidesInExcessByStation['totalConcentrations'][id][currentYear] !== 'undefined'
				) {
					body += '<br />De plus, La concentration totale des pesticides quantifiés était de '
						+ pesticidesInExcessByStation['totalConcentrations'][id][currentYear].toLocaleString()
						+ ' µg/L (seuil maximal de 0,5µg/L).';
				}
			} else {
                // Display a text line if the total concentration display the limit
				if (currentEntityType === 'Station'
					&& typeof pesticidesInExcessByStation['totalConcentrations'][id] !== 'undefined'
					&& typeof pesticidesInExcessByStation['totalConcentrations'][id][currentYear] !== 'undefined'
				) {
					body += ' L\'eau analysée dans cette station était <b><span style="color:darkred;">'
						+ 'impropre à la consommation</span></b> en ' + currentYear
						+ ' car la concentration totale des pesticides quantifiés était de '
						+ pesticidesInExcessByStation['totalConcentrations'][id][currentYear].toLocaleString()
						+ ' µg/L (seuil maximal de 0,5µg/L).';
				}
			}

			body += '<h4 class="timeGraphTitle"></h4>' +
				'<div class="timeGraph"></div>' +
				'<h4 class="familiesGraphTitle"></h4>' +
				'<div class="familiesGraph"></div>';

			$('.modal-title', $modal).text(title);
			$('.modal-body', $modal).html(body);

            /**
             * On modal open : load the graphs through highcharts
             */
			$modal.off().on('shown.bs.modal', function () {

				// Time graph : evolution of pesticide concentration by year (compared to department and country)
				$('.timeGraphTitle').text('Evolution de la ' + concentrationLabel + ' ' + prefixedEntityLabel);
				var series = [];

				// Build Country serie
				serie = {
					name: 'Moyenne France',
					data: [],
					color: '#cccccc'
				};

				years.forEach(function (year) {
					if (currentFamily === 'allFamilies') {
						if (typeof countryData['total']['country-fra'][year] !== 'undefined') {
							serie.data.push(countryData['total']['country-fra'][year]['allFamilies']);
						} else {
							serie.data.push(null);
						}
					} else {
						if (typeof countryData['families']['country-fra'][year] !== 'undefined'
							&& typeof countryData['families']['country-fra'][year][currentFamily] !== 'undefined'
						) {
							serie.data.push(countryData['families']['country-fra'][year][currentFamily]);
						} else {
							serie.data.push(null);
						}
					}
				});
				series.push(serie);

				// Build department serie
				var department;

				if (currentEntityType === 'Station') {
					department = stations[id]['relatedDepartment'];
				} else {
					department = id;
				}

				serie = {
					name: 'Moyenne ' + departments[department]['label'],
					data: [],
					color: (currentEntityType === 'Station') ? '#a5a5a5' : '#5F9BDB'
				};

				years.forEach(function (year) {
					if (currentFamily === 'allFamilies') {
						if (typeof totalConcentrationsByDepartment['data'][department] !== 'undefined'
							&& typeof totalConcentrationsByDepartment['data'][department][year] !== 'undefined') {
							serie.data.push(totalConcentrationsByDepartment['data'][department][year]['allFamilies']);
						} else {
							serie.data.push(null);
						}
					} else {
						if (typeof concentrationsByFamilyAndDepartment['data'][department] !== 'undefined'
							&& typeof concentrationsByFamilyAndDepartment['data'][department][year] !== 'undefined'
							&& typeof concentrationsByFamilyAndDepartment['data'][department][year][currentFamily] !== 'undefined'
						) {
							serie.data.push(concentrationsByFamilyAndDepartment['data'][department][year][currentFamily]);
						} else {
							serie.data.push(null);
						}
					}
				});
				series.push(serie);

				// Build station serie
				var serie = {};
				if (currentEntityType === 'Station') {
					serie = {
						name: stations[id]['label'],
						data: [],
						color: '#5F9BDB'
					};
					years.forEach(function (year) {
						if (currentFamily === 'allFamilies') {
							if (typeof totalConcentrationsByStation['data'][id] !== 'undefined'
								&& typeof totalConcentrationsByStation['data'][id][year] !== 'undefined'
							) {
								serie.data.push(totalConcentrationsByStation['data'][id][year]['allFamilies']);
							} else {
								serie.data.push(null);
							}
						} else {
							if (typeof concentrationsByFamilyAndStation['data'][id] !== 'undefined'
								&& typeof concentrationsByFamilyAndStation['data'][id][year] !== 'undefined'
								&& typeof concentrationsByFamilyAndStation['data'][id][year][currentFamily] !== 'undefined'
							) {
								serie.data.push(concentrationsByFamilyAndStation['data'][id][year][currentFamily]);
							} else {
								serie.data.push(null);
							}
						}
					});
					series.push(serie);
				}

				// Render chart
				$('.timeGraph', $modal).highcharts(
					jQuery.extend(true, {}, baseSplineChartOptions, {
						xAxis: {
							categories: years
						},
						series: series
					})
				);

				// Families graph : repartition of pesticides concentrations among families (compared to department
                // and country data)
				$('.familiesGraphTitle').text('Concentrations par famille de pesticides en '
					+ currentYear + ' ' + prefixedEntityLabel
				);

				var familiesSeries = [],
					familiesIds = [],
					familiesLabels = [];

				for (var familyId in families) {
					if ((currentEntityType === 'Station'
						&& typeof concentrationsByFamilyAndStation['data'][id] !== 'undefined'
						&& typeof concentrationsByFamilyAndStation['data'][id][currentYear] !== 'undefined'
						&& typeof concentrationsByFamilyAndStation['data'][id][currentYear][familyId] !== 'undefined')
						|| (typeof concentrationsByFamilyAndDepartment['data'][id] !== 'undefined'
						&& typeof concentrationsByFamilyAndDepartment['data'][id][currentYear] !== 'undefined'
						&& typeof concentrationsByFamilyAndDepartment['data'][id][currentYear][familyId] !== 'undefined')
					) {
						familiesIds.push(familyId);
						familiesLabels.push(families[familyId]['label'])
					}
				}

				// Build country serie
				serie = {
					name: 'Moyenne France',
					data: [],
					color: '#cccccc'
				};

				familiesIds.forEach(function (familyId) {
					if (typeof countryData['families']['country-fra'][currentYear] !== 'undefined'
						&& typeof countryData['families']['country-fra'][currentYear][familyId] !== 'undefined'
					) {
						serie.data.push(countryData['families']['country-fra'][currentYear][familyId]);
					} else {
						serie.data.push(null);
					}
				});
				familiesSeries.push(serie);

				// Build department serie
				serie = {
					name: 'Moyenne ' + departments[department]['label'],
					data: [],
					color: (currentEntityType === 'Station') ? '#a5a5a5' : '#5F9BDB'
				};

				familiesIds.forEach(function (familyId) {
					if (typeof concentrationsByFamilyAndDepartment['data'][department] !== 'undefined'
						&& typeof concentrationsByFamilyAndDepartment['data'][department][currentYear] !== 'undefined'
						&& typeof concentrationsByFamilyAndDepartment['data'][department][currentYear][familyId] !== 'undefined'
					) {
						if (familyId === currentFamily) {
							serie.data.push({
								y: concentrationsByFamilyAndDepartment['data'][department][currentYear][familyId],
								color: 'darkred'
							});
						} else {
							serie.data.push(concentrationsByFamilyAndDepartment['data'][department][currentYear][familyId]);
						}
					} else {
						serie.data.push(null);
					}
				});
				familiesSeries.push(serie);

				// Build station serie
				if (currentEntityType === 'Station') {
					serie = {
						name: stations[id]['label'],
						data: [],
						color: '#5F9BDB'
					};

					familiesIds.forEach(function (familyId) {
						if (typeof concentrationsByFamilyAndStation['data'][id] !== 'undefined'
							&& typeof concentrationsByFamilyAndStation['data'][id][currentYear] !== 'undefined'
							&& typeof concentrationsByFamilyAndStation['data'][id][currentYear][familyId] !== 'undefined'
						) {
							if (familyId === currentFamily && currentEntityType === 'Department') {
								serie.data.push({
									y: concentrationsByFamilyAndStation['data'][id][currentYear][familyId],
									color: 'darkred'
								});
							} else {
								serie.data.push(concentrationsByFamilyAndStation['data'][id][currentYear][familyId]);
							}
						} else {
							serie.data.push(null);
						}
					});
					familiesSeries.push(serie);
				}

				// Render the chart
				$('.familiesGraph', $modal).highcharts(
					jQuery.extend(true, {}, baseBarChartOptions, {
						chart: {type: 'column'},
						xAxis: {
							categories: familiesLabels
						},
						series: familiesSeries
					})
				);
			});

			$modal.modal('show');
		};

		/**
		 * Refresh the map depending on the current selected facets
		 * @param entityType Department|Station
		 * @param family family id (or 'allFamilies' for totals)
		 * @param year
		 * @param isInitialCall true if it's the first initial call
		 */
		var refreshMap = function (entityType, family, year, isInitialCall) {
			var currentData = {};
			var currentMetadata = {};
			var currentEntities = {};
			var newMapOptions = jQuery.extend(true, {}, baseMapOptions);

			// Update current state
			currentYear = year;
			currentFamily = family;
			currentEntityType = entityType;

            // Display the radio button to choose the display mode (optimized or normal)
			if (currentEntityType !== 'Station') {
				$(".choose-mode").css('display', 'none');
			} else {
				$(".choose-mode").css('display', 'block');
			}

			// Don't display the spinner for the initial call
			if (isInitialCall !== true) {
				$('.loader-container').css('display', 'block');
			}

			// Init current datasets to use
			if (entityType === 'Station') {
				currentEntities = stations;
				if (family === 'allFamilies') {
					currentData = totalConcentrationsByStation['data'];
					currentMetadata = totalConcentrationsByStation['metadata'];
				} else {
					currentData = concentrationsByFamilyAndStation['data'];
					currentMetadata = concentrationsByFamilyAndStation['metadata'];
				}
			} else {
				currentEntities = departments;
				if (family === 'allFamilies') {
					currentData = totalConcentrationsByDepartment['data'];
					currentMetadata = totalConcentrationsByDepartment['metadata'];
				} else {
					currentData = concentrationsByFamilyAndDepartment['data'];
					currentMetadata = concentrationsByFamilyAndDepartment['metadata'];
				}
			}

			// Set map title
			$('.mapTitle').text(getMapTitle(entityType, family, year));

			// Build mapael options object
			if (entityType === 'Station') {
				newMapOptions.map.defaultPlot.attrs.cursor = 'pointer';
				newMapOptions.map.defaultPlot.eventHandlers = {'click': displayEntityDetails};
			} else {
				newMapOptions.map.defaultArea.attrs.fill = '#ccc';
				newMapOptions.map.defaultArea.attrs.cursor = 'pointer';
				newMapOptions.map.defaultArea.attrsHover.fill = "#5F9BDB";
				newMapOptions.map.defaultArea.eventHandlers = {'click': displayEntityDetails};
				newMapOptions.map.defaultArea.tooltip = {content: 'Pas de données pour ce département'};
			}

			var hasData = false;
			var stationsCountByDepartments = {};
			for (var id in currentData) {

				if (typeof currentData[id][year] !== 'undefined'
					&& typeof currentData[id][year][family] !== 'undefined'
					&& typeof currentEntities[id] !== 'undefined'
				) {
					hasData = true;

					var relatedDepartment = '';
					if (currentEntityType === 'Station') {
						relatedDepartment = ' (' + departments[currentEntities[id]['relatedDepartment']]['label'] + ')';
					}

					var elementOptions = {
						tooltip: {
							content: '<h4>' + currentEntities[id]['label'] + relatedDepartment + '</h4><p>'
							+ getTooltipTitle(family, year) + " : <b>"
							+ currentData[id][year][family].toLocaleString() + " µg/L</b><br />"
							+ getCountryComparisonString(currentData[id][year][family]) + "</p>"
						},
						value: currentData[id][year][family]
					};

					if (entityType === 'Station') {
						if (typeof stationsCountByDepartments[currentEntities[id]['relatedDepartment']] === 'undefined') {
							stationsCountByDepartments[currentEntities[id]['relatedDepartment']] = 0;
						}

						if (!optimizedMode
							|| stationsCountByDepartments[currentEntities[id]['relatedDepartment']] <= 11
							|| id === currentMetadata[currentFamily][currentYear]['minEntity']
							|| id === currentMetadata[currentFamily][currentYear]['maxEntity']
						) {
							elementOptions.latitude = currentEntities[id]['latitude'];
							elementOptions.longitude = currentEntities[id]['longitude'];
							newMapOptions.plots[id] = elementOptions;

							stationsCountByDepartments[currentEntities[id]['relatedDepartment']]++;
						}
					} else {
						newMapOptions.areas[id] = elementOptions;
					}
				}
			}

			// If there is no data with the current settings, load an empty map
			if (!hasData) {
				$(".mapcontainer").mapael(newMapOptions);
				return;
			}

			// Build map legend options
			var slices = currentMetadata[family][year]['slices'];
			// '#9fe6f9', '#a7a9b0', '#9d6d6d', '#873030'
			var sliceColors = ['#ffe5e5','#f2a495','#d9644b','#b70000'];
			var sliceOptions = {};
			var legendType = 'plot';

			if (entityType === 'Department') {
				legendType = 'area';
			}

			newMapOptions['legend'][legendType]['slices'] = [];

			for (var i = 0; i < slices.length; i++) {
				sliceOptions = {};

				if (slices.length === 2 && i === 1) {
					sliceOptions.attrs = {fill: sliceColors[2]};
				} else {
					sliceOptions.attrs = {fill: sliceColors[i]};
				}

				if (typeof slices[i]['min'] !== 'undefined' && typeof slices[i]['max'] !== 'undefined') {
					sliceOptions.min = slices[i]['min'];
					sliceOptions.max = slices[i]['max'];
					sliceOptions.label = "Entre " + sliceOptions['min'].toLocaleString() + " µg/L et "
						+ sliceOptions['max'].toLocaleString() + " µg/L";
				} else if (typeof slices[i]['min'] !== 'undefined') {
					sliceOptions.min = slices[i]['min'];
					sliceOptions.label = "Plus de " + sliceOptions['min'].toLocaleString() + " µg/L";
				} else {
					sliceOptions.max = slices[i]['max'];
					sliceOptions.label = "Moins de " + sliceOptions['max'].toLocaleString() + " µg/L";
				}

				if (entityType === 'Department') {
					sliceOptions.legendSpecificAttrs = {width: 12, height: 8};
				}

				newMapOptions.legend[legendType]['slices'].push(sliceOptions);
			}

			// Display the map
			setTimeout(function () {
				var $body = $('body');
				var scrollPosition = $body.scrollTop();

				$(".mapcontainer").mapael(newMapOptions);
				$('.loader-container').css('display', 'none');
				$body.scrollTop(scrollPosition);

			}, 50);

			// Display min & max entities and the median
			var relatedMinDepartment = '';
			var relatedMaxDepartment = '';

			if (entityType === 'Station') {
				relatedMinDepartment = ', '
					+ departments[currentEntities[currentMetadata[family][year]['minEntity']]['relatedDepartment']]['insee'];
				relatedMaxDepartment = ', '
					+ departments[currentEntities[currentMetadata[family][year]['maxEntity']]['relatedDepartment']]['insee'];
			}

			var pesticidesInExcess = '';
			if (currentEntityType === 'Station') {
				pesticidesInExcess = '<li class="metadata-item pesticides-in-excess">Normes non respectées (';
				pesticidesInExcess += pesticidesInExcessByStation['counts'][currentYear].toLocaleString();
				pesticidesInExcess += ' stations)</li>';
			}

			$('.metadataContainer').html('<ul class="metadata">'
				+ pesticidesInExcess
				+ '<li class="metadata-item" data-entity="' + currentMetadata[family][year]['minEntity']
				+ '">Min : ' + currentMetadata[family][year]['min'].toLocaleString() + ' µg/L ('
				+ currentEntities[currentMetadata[family][year]['minEntity']]['label'] + relatedMinDepartment + ')</li>'
				+ '<li class="metadata-item" data-entity="' + currentMetadata[family][year]['maxEntity']
				+ '">Max : ' + currentMetadata[family][year]['max'].toLocaleString() + ' µg/L ('
				+ currentEntities[currentMetadata[family][year]['maxEntity']]['label'] + relatedMaxDepartment + ')</li>'
				+ '</ul>'
			);

			// Handle click on min or max entity
			$('.metadata-item[data-entity]').on('click', function () {
				var $this = $(this);
				var entity = $(this).attr('data-entity');

				$this.toggleClass('clicked');

				var updatedOptions = {'areas': {}, 'plots': {}};

				if ($this.hasClass('clicked')) {
					if (entityType === 'Station') {
						updatedOptions.plots[entity] = {
							"attrs": {
								"stroke": "#5F9BDB",
								"stroke-width": 3
							},
							"toFront": true,
							"size": 30
						};
					} else {
						updatedOptions.areas[entity] = {
							"attrs": {
								"transform": "s1.75",
								"stroke": "#5F9BDB",
								"stroke-width": 3
							},
							"toFront": true
						};
					}
				} else {
					if (entityType === 'Station') {
						updatedOptions.plots[entity] = {
							"attrs": {
								"stroke-width": 0.5,
								stroke: "#000"
							},
							"size": baseMapOptions.map.defaultPlot.size
						};
					} else {
						updatedOptions.areas[entity] = {
							"attrs": {
								"transform": "s1",
								"stroke-width": 1,
								stroke: "#424c59"
							}
						};
					}
				}

				$(".mapcontainer").trigger('update', [{
					"mapOptions": updatedOptions,
					"animDuration": 1000
				}]);
			});

            // Handle click on pesticides in excess link
			$('.metadata-item.pesticides-in-excess').on('click', function () {
				var $this = $(this);
				var updatedOptions = {'areas': {}, 'plots': {}};
				var id = '';

				$this.toggleClass('clicked');

				if ($this.hasClass('clicked')) {
					for (id in pesticidesInExcessByStation['pesticides']) {
						if (typeof pesticidesInExcessByStation['pesticides'][id][currentYear] !== 'undefined') {
							updatedOptions.plots[id] = {
								attrs: {
									stroke: "#5F9BDB",
									'stroke-width': 1.5
								},
								toFront: true
							};
						}
					}
				} else {
					for (id in pesticidesInExcessByStation['pesticides']) {
						if (pesticidesInExcessByStation['pesticides'][id][currentYear] !== 'undefined') {
							updatedOptions.plots[id] = {
								attrs: {
									stroke: "#000",
									'stroke-width': 0.5
								},
								toFront: true
							};
						}
					}
				}

				$(".mapcontainer").trigger('update', [{
					mapOptions: updatedOptions,
					animDuration: 0
				}]);
			});
		};

		/**
		 * Display graphs for country data within a modal
		 */
		var onShowCountryModal = function () {
			var $modal = $('#dpmodal');

			$('.modal-title', $modal).text('Les pesticides en France');
			$('.modal-body', $modal).html(renderTemplate('country-modal'));

            /**
             * On country modal show, load graphs through Highcharts
             */
			$modal.off().on('shown.bs.modal', function (e) {

				// Display Key data
				$('.keyDataTitle').text('Les chiffres clés de l\'année ' + currentYear);

				$('.key-data-box.median .number').text(
					totalConcentrationsByStation['metadata']['allFamilies'][currentYear]['median'].toLocaleString() +
					' µg/L'
				);

				$('.key-data-box.stationsCount .number').text(
					countryData['stationsCount'][currentYear]['value'].toLocaleString()
				);

				$('.key-data-box.analyzesCount .number').text(
					countryData['analyzesCount'][currentYear]['value'].toLocaleString()
				);

                // Display country graph with average concentration by year
                var countryTimeGraphSerie = [];
                var countryTimeGraphYears = [];

				if (currentFamily === 'allFamilies') {
					for (var year in countryData['total']['country-fra']) {
						countryTimeGraphYears.push(year);
						countryTimeGraphSerie.push(countryData['total']['country-fra'][year]['allFamilies']);
					}
				} else {
					for (var year in countryData['families']['country-fra']) {
						if (typeof countryData['families']['country-fra'][year][currentFamily] !== 'undefined') {
							countryTimeGraphYears.push(year);
							countryTimeGraphSerie.push(countryData['families']['country-fra'][year][currentFamily]);
						}
					}
				}

				var familyLabel = 'pesticides';

				if (currentFamily !== 'allFamilies') {
					familyLabel = families[currentFamily]['label'].toLowerCase();
				}
				$('.countryTimeGraphTitle').text('Evolution de la concentration totale en '
					+ familyLabel + ' (moyenne des stations en France)');

				$('.countryTimeGraph').highcharts(
					jQuery.extend(true, {}, baseSplineChartOptions, {
						xAxis: {
							categories: countryTimeGraphYears
						},
						series: [
							{
								name: 'Concentration totale en ' + familyLabel + ' (µg/L)',
								data: countryTimeGraphSerie,
								color: '#5F9BDB'
							}
						]
					})
				);

				// Display graph that shows the pesticides concentration repartition by family
				$('.countryFamiliesGraphTitle').text('Concentrations par famille de pesticides en '
					+ currentYear);

				// Families graph
				var familiesSeries = [];
				var familiesLabels = [];

				serie = {
					name: 'Concentration par famille (µg/L)',
					data: [],
					color: '#5F9BDB'
				};

				var familiesSerieData = [];

				for (var familyId in countryData['families']['country-fra'][currentYear]) {
					familiesSerieData.push({
						'family': familyId,
						'value': countryData['families']['country-fra'][currentYear][familyId]
					});
				}

				familiesSerieData.sort(function (a, b) {
					if (a.value < b.value) {
						return 1;
					} else if (a.value > b.value) {
						return -1;
					}
					return 0;
				});

				familiesSerieData.forEach(function (element) {
					if (element.family === currentFamily) {
						serie.data.push({
							y: element.value,
							color: 'darkred'
						});
					} else {
						serie.data.push(element.value);
					}
					familiesLabels.push(families[element.family]['label'])
				});

				familiesSeries.push(serie);

				$('.countryFamiliesGraph').highcharts(
					jQuery.extend(true, {}, baseBarChartOptions, {
						chart: {type: 'column'},
						xAxis: {
							categories: familiesLabels
						},
						series: familiesSeries
					})
				);

                // Display top 5 most polluted stations
				var countryTop5GraphSerieXAxis = [];
				var countryTop5GraphSerie = [];

				for (var i in countryData['top5'][currentYear]) {
					countryTop5GraphSerieXAxis.push(stations[i]['label'] + ' (' + stations[i]['departmentLabel'] + ')');
					countryTop5GraphSerie.push(countryData['top5'][currentYear][i]);
				}

				$('.countryTop5GraphTitle').text('Les 5 stations les plus polluées en ' + currentYear);
				$('.countryBottom5GraphTitle').text('Les 5 stations les moins polluées en ' + currentYear);

				$('.countryTop5Graph').highcharts(
					jQuery.extend(true, {}, baseBarChartOptions, {
						xAxis: {
							categories: countryTop5GraphSerieXAxis
						},
						series: [
							{
								name: 'Concentration totale en pesticides (µg/L)',
								data: countryTop5GraphSerie,
								color: 'darkred'
							}
						]
					})
				);

                // Display the 5 least polluted stations
				var countryBottom5GraphSerieXAxis = [];
				var countryBottom5GraphSerie = [];

				for (i in countryData['bottom5'][currentYear]) {
					countryBottom5GraphSerieXAxis.push(stations[i]['label'] + ' (' + stations[i]['departmentLabel'] + ')');
					countryBottom5GraphSerie.push(countryData['bottom5'][currentYear][i]);
				}

				$('.countryBottom5Graph').highcharts(
					jQuery.extend(true, {}, baseBarChartOptions, {
						xAxis: {
							categories: countryBottom5GraphSerieXAxis
						},
						series: [
							{
								name: 'Concentration totale en pesticides (µg/L)',
								data: countryBottom5GraphSerie,
								color: 'darkgreen'
							}
						]
					})
				);

                // Display a graph that shows the evolution of stations count
				var countryStationsGraphSerie = [];
				var countryStationsGraphYears = [];
				var countryStationsWithExcessGraphSerie = [];

				for (var year in countryData['stationsCount']) {
					countryStationsGraphYears.push(year);
					countryStationsGraphSerie.push(countryData['stationsCount'][year]['value']);

					if (typeof pesticidesInExcessByStation['counts'][year] !== 'undefined') {
						countryStationsWithExcessGraphSerie.push(pesticidesInExcessByStation['counts'][year]);
					}
				}

				$('.countryStationsGraphTitle').text('Evolution du nombre de stations de mesures');

				$('.countryStationsGraph').highcharts(
					jQuery.extend(true, {}, baseSplineChartOptions, {
						chart: {type: 'areaspline'},
						xAxis: {
							categories: countryStationsGraphYears
						},
						yAxis: {
							title: {text: 'Nombre de stations'},
							min: 0
						},
						tooltip: {valueSuffix: ''},
						series: [
							{
								name: 'Nombre de stations total',
								data: countryStationsGraphSerie,
								color: '#5F9BDB'
							},
							{
								name: 'Nombre de stations dans lesquelles les concentrations en pesticides dépassent les normes',
								data: countryStationsWithExcessGraphSerie,
								color: 'darkred'
							}
						]
					})
				);

			});

			$modal.modal('show');
		};

		// Enable optimized mode for IE browsers or mobile browsers in order to improve performance
		if (/MSIE|Trident|Edge|Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i
				.test(window.navigator.userAgent)
		) {
			optimizedMode = true;
			$(".choose-mode #display-mode-1").prop("checked", true);
		}

		// Initialize map
		refreshMap(currentEntityType, currentFamily, years[years.length - 1], true);

        // Enable collapse on menu
		$('#menu-content').collapse();

		$('.showCountryModal').on('click', onShowCountryModal);

		// Init entity type buttons (department / station)
		$('.entityTypeChoice button[data-entity-type]').on('click', function () {
			var $this = $(this);
			var entityType = $this.attr('data-entity-type');

			if ($this.hasClass('on')) {
				return;
			}

			$('.entityTypeChoice button[data-entity-type].on').toggleClass('on');
			$this.toggleClass('on');

			refreshMap(entityType, currentFamily, currentYear);
		});

		// Init slider for years
		var $yearSlider = $('#yearSlider');

		$yearSlider.slider({
			min: parseInt(years[0]),
			max: parseInt(years[years.length - 1]),
			value: parseInt(years[years.length - 1]),
			step: 1
		});
		$('.minSlider').text(years[0]);
		$('.maxSlider').text(years[years.length - 1]);
		$('.sliderValue').text(years[years.length - 1]);

		$yearSlider.on("change", function (e) {
			$('.sliderValue').text(e.value.newValue);
			window.clearTimeout(sliderTo);
			sliderTo = window.setTimeout(
				function () {
					refreshMap(currentEntityType, currentFamily, e.value.newValue);
				},
				100
			);
		});

		// Init pesticide families list
		var $pesticidesFamilies = $('.pesticideFamilies');
		for (var id in families) {
			var pesticideItem = '<li><a href="#" class="pesticide-map" data-id="' + id + '">' + families[id]['label'] +
				'</a>';

			if (typeof pesticideFamiliesExtraData[id] !== 'undefined') {
				pesticideItem += '<a href="#" class="pesticide-info" data-id="' + id + '" class="pesticideInfo">' +
					'<i class="fa fa-info-circle fa-lg"></i></a>';
			}
			pesticideItem += '</li>';

			$pesticidesFamilies.append(pesticideItem);
		}

		$pesticidesFamilies.on('click', 'li a.pesticide-map[data-id]', function (e) {
			var $this = $(this);
			var family = $this.attr('data-id');

			$('ul.pesticideFamilies li.on').toggleClass('on');
			$this.parent().toggleClass('on');

			refreshMap(currentEntityType, family, currentYear);
			e.preventDefault();
		});

        /**
         * Display additional info for pesticide family
         */
		$pesticidesFamilies.on('click', 'li a.pesticide-info[data-id]', function (e) {
			var $this = $(this);
			var family = $this.attr('data-id');
			var $modal = $('#dpmodal');
			var body = '';

			$('.modal-title', $modal).text('Plus d\'informations sur les ' + families[family]['label']);

			if (typeof pesticideFamiliesExtraData[family]['roles'] !== 'undefined') {
				body += '<h4>Utilisation(s) de cette famille de pesticides</h4><p>'
					+ pesticideFamiliesExtraData[family]['roles'] + '</p>';
			}

			if (typeof pesticideFamiliesExtraData[family]['code'] !== 'undefined') {
				var sandreLink = '<h4>Fiche Sandre</h4><p><a href="'
					+ 'http://www.sandre.eaufrance.fr/urn.php?urn=urn:sandre:donnees:GPR:FRA:code:'
					+ pesticideFamiliesExtraData[family]['code']
					+ ':::referentiel:3.1:html" target="_blank">Fiche Sandre "'
					+ families[family]['label'] + '"</a></p>';

				getData('/api/data/getSandreData?id=' + pesticideFamiliesExtraData[family]['code']).then(function (data) {

					if (typeof data['Referentiel'] !== 'undefined'
						&& typeof data['Referentiel']['GroupeParametres'] !== 'undefined'
					) {
						if (typeof data['Referentiel']['GroupeParametres']['DfGroupeParametres'] === 'string') {
							body += '<h4>Description</h4><p>'
								+ data['Referentiel']['GroupeParametres']['DfGroupeParametres'] + '</p>';
						}

						if (typeof data['Referentiel']['GroupeParametres']['GroupeParametresPere'] === 'string'
							&& typeof data['Referentiel']['GroupeParametres']
								['GroupeParametresPere']['NomGroupeParametres'] !== 'undefined'
						) {
							body += '<h4>Catégorie</h4><p>' + data['Referentiel']['GroupeParametres']
									['GroupeParametresPere']['NomGroupeParametres']
								+ '</p>';
						}

						if (typeof data['Referentiel']['GroupeParametres']['Parametre'] === 'object') {
							body += '<h4>Pesticides appartenant à cette famille</h4>';

							var first = true;

							data['Referentiel']['GroupeParametres']['Parametre'].forEach(function (pesticideData) {
								if (typeof pesticideData['CdParametre'] === 'string'
									&& typeof pesticideData['NomParametre'] === 'string'
								) {
									if (!first) {
										body += ', ';
									}

									first = false;

									body += '<a href="http://id.eaufrance.fr/par/' + pesticideData['CdParametre']
										+ '" target="_blank">' + pesticideData['NomParametre'] + '</a>';
								}
							});
						}
					}

					body += sandreLink;
					body += "<p>Source : <a href='http://www.sandre.eaufrance.fr/' target='_blank'>"
						+ "www.sandre.eaufrance.fr</a></p>";

					$('.modal-body', $modal).html(body);
					$modal.modal('show');
				});
			} else {
				$('.modal-body', $modal).html(body);
				$modal.modal('show');
			}
			e.preventDefault();
		});

        /**
         * Enable or disable optmized mode for the map
         */
		$('.choose-mode span').on('click', function () {
			var $this = $(this);

			$("input", $this).prop("checked", true);
			optimizedMode = ($('input:checked', $this).val() === "1");

			// Show the menu in order to display properly the legend as it can't be drawn
			// In an hidden container
			$('#menu-content').one('shown.bs.collapse', function (e) {
				refreshMap(currentEntityType, currentFamily, currentYear);
			});
			$('#menu-content').collapse('show');

			refreshMap(currentEntityType, currentFamily, currentYear);
		});

	});
});
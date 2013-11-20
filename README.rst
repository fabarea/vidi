Vidi for TYPO3 CMS
========================

Vidi stands for "versatile and interactive display" and is the code name of a list component
designed for listing all kind of records along with advanced filtering capabilities. By default the
extension ships two examples for FE Users / Groups but is configurable for any kind of data type.

Veni, vidi, vici!

.. image:: https://raw.github.com/TYPO3-extensions/vidi/master/Documentation/List-01.png

Project info and releases
-----------------------------------

Stable version (not yet released):
http://typo3.org/extensions/repository/view/vidi

Development version:
https://git.typo3.org/TYPO3CMS/Extensions/vidi.git

::

	git clone git://git.typo3.org/TYPO3CMS/Extensions/vidi.git

Github mirror:
https://github.com/TYPO3-extensions/vidi

Flash news about latest development are also announced on
http://twitter.com/fudriot


Installation and requirement
==============================

The extension **requires TYPO3 6.1**. In case, a fresh TYPO3 set-up is available at http://get.typo3.org/.
The extension is not yet released on the TER_. Download the source from the `master branch`_ and
install the extension as normal in the Extension Manager::

	# local installation
	cd typo3conf/ext

	# download the source
	git clone git://git.typo3.org/TYPO3CMS/Extensions/vidi.git

	# alternatively, it can be fetched from the Git mirror.
	git clone https://github.com/TYPO3-extensions/vidi.git

	# -> open the Extension Manager in the BE


.. _TER: typo3.org/extensions/repository/
.. _master branch: https://github.com/TYPO3-extensions/vidi.git


Configuration
=================

Configuration is mainly provided in the Extension Manager and is pretty much self-explanatory. Check possible options there.

User TSconfig
---------------

A pid (page id) is necessary to be defined when creating a new record for the need of TCEmain_.
This is not true for all records as some of them can be on the root level and consequently have a pid 0.
However most require a pid value greater than 0. In a first place, a global pid can be configured in the Extension Manager
which is taken as fallback value. Besides, User TSconfig can also be set which will configure a custom pid for each data type enabling to
be fine grained::

	# Short syntax for data type "tx_domain_model_foo":
	tx_vidi.dataType.tx_domain_model_foo.storagePid = 33

	# Extended syntax for data type "tx_domain_model_foo":
	tx_vidi {
		dataType {
			fe_users {
				storagePid = 33
			}
		}
	}

.. _TCEmain: http://docs.typo3.org/TYPO3/CoreApiReference/ApiOverview/Typo3CoreEngine/UsingTcemain/Index.html

Start a new BE module for a custom data type
===================================================

Loading a BE module for a custom data type can be summed up with:

#. Configure the module loader
#. Define a language file which contains some labels.
#. Define icon and JS / CSS files

The best way to get started is to install the Vidi Starter extension which is the ideal companion of Vidi
aiming to facilitate the initial steps. More info https://github.com/fudriot/vidi_starter.

Module Loader API was designed upon the work of `Steffen Ritter`_ .

.. _Steffen Ritter: http://forge.typo3.org/users/446

Grid TCA
===================================================

A Grid is an interactive list displayed in a BE module. TCA was extended to describe how a grid and its
columns should be rendered. Take inspiration of `this example`_ below for your own data type::

	'grid' => array(
		'columns' => array(
			'__checkbox' => array(
				'width' => '5px',
				'sortable' => FALSE,
				'html' => '<input type="checkbox" class="checkbox-row-top"/>',
			),
			'uid' => array(
				'visible' => FALSE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:uid',
				'width' => '5px',
			),
			'username' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:username',
			),
			'usergroup' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:usergroup',
			),
			'__buttons' => array(
				'sortable' => FALSE,
				'width' => '70px',
			),
		),
	),


.. _this example: https://github.com/TYPO3-extensions/vidi/blob/master/Configuration/TCA/fe_users.php

Grid TCA configuration
------------------------------

Key and values that can be used in TCA Grid

.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**sortable**

Datatype
	boolean

Description
	Whether the column is sortable or not.

Default
	TRUE


.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**visible**

Datatype
	boolean

Description
	Whether the column is visible by default or hidden. If the column is not visible by default
	it can be displayed with the column picker (upper right button in the BE module)

Default
	TRUE

.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**renderer**

Datatype
	string

Description
	A class name implementing Grid Renderer Interface

Default
	NULL

.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**label**

Datatype
	string

Description
	An optional label overriding the default label of the field - i.e. the label from TCA['tableName']['columns']['fieldName']['label']

Default
	NULL


.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**editable**

Datatype
	string

Description
	Whether the field is editable or not.

Default
	NULL

.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**class**

Datatype
	string

Description
	Will display the class name to every cell.

Default
	NULL

.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**wrap**

Datatype
	string

Description
	A possible wrapping of the content. Useful in case the content of the cell should be styled in a special manner.

Default
	NULL

.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**width**

Datatype
	int

Description
	A possible width of the column

Default
	NULL

System columns
-----------------

There a few columns that are considered as "system" which means they don't correspond to a property of an object
but are display to control the record. By convention, theses columns are prefixed with a double underscore e.g "__":


.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**__number**

Description
	Display a row number

.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**__checkbox**

Description
	Display a check box

.. ...............................................................
.. ...............................................................
.. container:: table-row

Key
	**__buttons**

Description
	Display "edit", "deleted", ... buttons to control the row


Grid Renderer
------------------

To render a custom column a class implementing Grid Renderer Interface must be given to the Grid TCA.

@todo write more...

::

			'access_codes' => array(
				'visible' => TRUE,
				'renderers' => array(
					'TYPO3\CMS\Vidi\GridRenderer\RelationCreate',
					'TYPO3\CMS\Vidi\GridRenderer\RelationCount' => array(
						'labelSingular' => 'LLL:EXT:ebook/Resources/Private/Language/locallang_db.xlf:tx_ebook_domain_model_accesscode',
						'labelPlural' => 'LLL:EXT:ebook/Resources/Private/Language/locallang_db.xlf:tx_ebook_domain_model_accesscodes',
						'sourceModule' => 'ebook_VidiTxEbookDomainModelBookM1',
						'targetModule' => 'ebook_VidiTxEbookDomainModelAccesscodeM1',
					),
				),
			),

Content Repository Factory
===========================

Each Content type (e.g. fe_users, fe_groups) has its own Content repository instance which is manged internally by the Repository Factory.
For getting the adequate instance, the repository can be fetched by this code::


	// Fetch the adequate repository for a known data type.
	$dataType = 'fe_users';
	$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance($dataType);

	// The data type can be omitted in the context of a BE module
	// Internally, the Factory ask the Module Loader to retrieve the main data type of the BE module.
	$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance();


TCA Service API
=================

This API enables to fetch info related to TCA in a programmatic way. Since TCA covers a very large set of data, the service is divided in types.
There are are four parts being addressed: table, field, grid and form. The "grid" TCA is not official and is extending the TCA for the needs of Vidi.

* table: deals with the "ctrl" part of the TCA. Typical info is what is the label of the table name, what is the default sorting, etc...
* field: deals with the "columns" part of the TCA. Typical info is what configuration, label, ... has a field name.
* grid: deals with the "grid" part of the TCA.
* form: deals with the "types" (and possible "palette") part of the TCA. Get what field compose a record type.

The API is meant to be generic and can be re-use for every record type within TYPO3.
Find below some code examples.

Instantiate a TCA service related to **fields**::

	$tableName = 'tx_domain_model_foo';
	$serviceType = \TYPO3\CMS\Vidi\Tca\TcaServiceInterface::TYPE_FIELD;

	/** @var $fieldService \TYPO3\CMS\Media\Tca\FieldService */
	$fieldService = \TYPO3\CMS\Media\Tca\TcaServiceFactory::getService($tableName, $serviceType);

	// Get all fields data type 'tx_domain_model_foo';
	// For more examples, refer to internal methods of the service.
	$fieldService->getFields();

Instantiate a TCA service related to **table**::

	$tableName = 'tx_domain_model_foo';
	$serviceType = \TYPO3\CMS\Vidi\Tca\TcaServiceInterface::TYPE_TABLE;

	/** @var $tableService \TYPO3\CMS\Media\Tca\TableService */
	$tableService = \TYPO3\CMS\Media\Tca\TcaServiceFactory::getService($tableName, $serviceType);

	// Get the label field of data type 'tx_domain_model_foo';
	// For more examples, refer to internal methods of the service.
	$tableService->getLabelField();

Instantiate a TCA service related to **form**::

	$tableName = 'tx_domain_model_foo';
	$serviceType = \TYPO3\CMS\Vidi\Tca\TcaServiceInterface::TYPE_FORM;

	/** @var $tableService \TYPO3\CMS\Media\Tca\TableService */
	$tableService = \TYPO3\CMS\Media\Tca\TcaServiceFactory::getService($tableName, $serviceType);

	// Refer to internal methods of the service...

Instantiate a TCA service related to **grid**::

	$tableName = 'tx_domain_model_foo';
	$serviceType = \TYPO3\CMS\Vidi\Tca\TcaServiceInterface::TYPE_GRID;

	/** @var $tableService \TYPO3\CMS\Media\Tca\TableService */
	$tableService = \TYPO3\CMS\Media\Tca\TcaServiceFactory::getService($tableName, $serviceType);

	// Refer to internal methods of the service...


Command line
===================================================

To check whether TCA is well configured, Vidi provides a Command that will scan the configuration and report potential problem. This feature is still experimental::

	# Check relations used in the grid.
	./typo3/cli_dispatch.phpsh extbase vidi:checkrelations
	./typo3/cli_dispatch.phpsh extbase vidi:checkrelations --table tx_domain_model_foo

	# Check labels of the Grid
	./typo3/cli_dispatch.phpsh extbase vidi:checkLabels


Example of TCA
---------------

@todo writing review is necessary.

Important to notice that for displaying relational columns in a Vidi module, the TCA configuration ``foreign_field``
must be defined in both side of the relations. This is needed for Vidi to retrieve the content in both direction.
Check example below which shows ``foreign_field`` set for each field.

One to Many relation and its opposite Many to One:

::

	#################
	# one-to-many
	#################
	$TCA['tx_foo_domain_model_book'] = array(
		'columns' => array(
			'access_codes' => array(
				'config' => array(
					'type' => 'inline',
					'foreign_table' => 'tx_foo_domain_model_accesscode',
					'foreign_field' => 'book',
					'maxitems' => 9999,
				),
			),
		),
	);

	#################
	# many-to-one
	#################
	$TCA['tx_foo_domain_model_accesscode'] = array(
		'columns' => array(
			'book' => array(
				'config' => array(
					'type' => 'select',
					'foreign_table' => 'tx_foo_domain_model_book',
					# IMPORTANT: DO NOT FORGET TO ADD foreign_field.
					'foreign_field' => 'access_codes',
					'minitems' => 1,
					'maxitems' => 1,
				),
			),
		),
	);


Bi-directional Many to Many relation::

	#################
	# many-to-many
	#################
	$TCA['tx_foo_domain_model_book'] = array(
		'columns' => array(
			'tx_myext_locations' => array(
				'config' => array(
					'type' => 'select',
					'foreign_table' => 'tx_foo_domain_categories',
					'MM_opposite_field' => 'usage_mm',
					'MM' => 'tx_foo_domain_categories_mm',
					'MM_match_fields' => array(
						'tablenames' => 'pages'
					),
					'size' => 5,
					'maxitems' => 100
				)
			)
		),
	);

	#################
	# many-to-many (opposite relation)
	#################
	$TCA['tx_foo_domain_categories'] = array(
		'columns' => array(
			'usage_mm' => array(
				'config' => array(
					'type' => 'group',
					'internal_type' => 'db',
					'allowed' => 'pages,tt_news',
					'prepend_tname' => 1,
					'size' => 5,
					'maxitems' => 100,
					'MM' => 'tx_foo_domain_categories_mm'
				)
			)
		),
	);

Legacy Many to Many relation with comma separated values (should be avoided in favour to proper MM relations). Notice field ``foreign_field`` is omitted::

	#################
	# Legacy MM relation (comma separated value)
	#################
	$TCA['tx_foo_domain_model_book'] = array(
		'columns' => array(
			'fe_groups' => array(
				'config' => array(
					'type' => 'inline',
					'foreign_table' => 'tx_foo_domain_model_accesscode',
					'foreign_field' => 'book',
					'maxitems' => 9999,
				),
			),
		),
	);



Tutorial: display a custom widget within the BE module
=======================================================

@todo put this into EXT:vidi_starter as a implemented option.

It is possible to load a custom form.

* In ext_tables.php::

	$moduleLoader->addJavaScriptFiles(array(sprintf('EXT:ebook/Resources/Public/JavaScript/%s.js', $dataType)));

	$controllerActions = array(
		'FrontendUser' => 'listFrontendUserGroup, addFrontendUserGroup',
	);

	/**
	 * Register some controllers for the Backend (Ajax)
	 * Special case for FE User and FE Group
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		$_EXTKEY,
		'Pi1',
		$controllerActions,
		$controllerActions
	);

	\TYPO3\CMS\Vidi\AjaxDispatcher::addAllowedActions(
		$_EXTKEY,
		'Pi1',
		$controllerActions
	);

* Create Controller for loading Wizard::

	touch EXT:ebook/Classes/Controller/Backend/AccessCodeController.php
	touch EXT:ebook/Resources/Private/Backend/Templates/AccessCode/ShowWizard.html
	touch EXT:ebook/Resources/Public/JavaScript/tx_ebook_domain_model_book.js
	touch EXT:ebook/ext_typoscript_constants.txt
	touch EXT:ebook/ext_typoscript_setup.txt
	touch EXT:ebook/Migrations/Code/ClassAliasMap.php


* TypoScript Constants in ``EXT:ebook/ext_typoscript_constants.txt``::

	module.tx_ebook {
		view {
			 # cat=module.tx_ebook/file; type=string; label=Path to template root (BE)
			templateRootPath = EXT:ebook/Resources/Private/Backend/Templates/
			 # cat=module.tx_ebook/file; type=string; label=Path to template partials (BE)
			partialRootPath = EXT:ebook/Resources/Private/Partials/
			 # cat=module.tx_ebook/file; type=string; label=Path to template layouts (BE)
			layoutRootPath = EXT:ebook/Resources/Private/Backend/Layouts/
		}
	}


* Configure TypoScript in ``EXT:ebook/ext_typoscript_setup.txt``::

	# Plugin configuration
	plugin.tx_vidi {
		settings {
		}
		view {
			templateRootPath = {$plugin.tx_vidi.view.templateRootPath}
			partialRootPath = {$plugin.tx_vidi.view.partialRootPath}
			layoutRootPath = {$plugin.tx_vidi.view.layoutRootPath}
			defaultPid = auto
		}
	}

	# Module configuration
	module.tx_vidi {
		settings < plugin.tx_vidi.settings
		view < plugin.tx_vidi.view
		view {
			templateRootPath = {$module.tx_vidi.view.templateRootPath}
			partialRootPath = {$module.tx_vidi.view.partialRootPath}
			layoutRootPath = {$module.tx_vidi.view.layoutRootPath}
		}
	}


* Migration file in ``EXT:ebook/Migrations/Code/ClassAliasMap.php`` (copy example from EXT:ebook).
* Backend Controller ``EXT:ebook/Classes/Controller/Backend/AccessCodeController.php`` (copy example from EXT:ebook).
* HTML Template ``EXT:ebook/Resources/Private/Backend/Templates/AccessCode/ShowWizard.html`` (copy example from EXT:ebook).
* JavaScript File ``EXT:ebook/Resources/Public/JavaScript/tx_ebook_domain_model_book.js`` (copy example from EXT:ebook).
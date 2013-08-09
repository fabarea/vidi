Vidi for TYPO3 CMS
========================

Vidi stands for "versatile and interactive display" and is the code name of a list component
designed for listing all kind of records along with advanced filtering capabilities.

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


Installation
=================

Download the source code either from the `Git repository`_ to get the master or from the TER for the stable releases. Install the extension as normal in the Extension Manager.

.. _Git repository: https://git.typo3.org/TYPO3CMS/Extensions/vidi.git

Configuration
=================

Configuration is mainly provided in the Extension Manager and is pretty much self-explanatory. Check possible options there.

TSconfig
------------

A pid (page id) is necessary to be defined when creating a new record for the need of TCEmain_.
This is not true for all records as some of them can be on the root level and consequently have a pid 0.
However most requires a pid value greater than 0. In a first place, a global pid can be configured in the Extension Manager
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

How to load a BE module for a custom data type?
===================================================

Loading a BE module for a custom data type can be summed up with:

#. Configure the module loader
#. Define an icon
#. Define a language file where to find the label of the fields. Make sure the file contains also the BE module name as example:

::

	<trans-unit id="mlang_labels_tablabel">
		<source>FE Group management</source>
	</trans-unit>
	<trans-unit id="mlang_tabs_tab" xml:space="preserve">
		<source>FE Group</source>
	</trans-unit>
	<trans-unit id="mlang_labels_tabdescr" xml:space="preserve">
		<source>Module for managing FE Groups</source>
	</trans-unit>

Module Loader configuration
-------------------------------

To load a custom BE module in the BE, the Module loader should be used as follows::

	// Make sure the class exists to avoid a Runtime Error
	if (class_exists('TYPO3\CMS\Vidi\ModuleLoader')) {

		$dataType = 'tx_domain_model_foo';
		$icon = 'EXT:foo/Resources/Public/Icons/tx_domain_model_foo.png';
		$languageFile = 'LLL:EXT:foo/Resources/Private/Language/locallang_db.xlf';

		/** @var \TYPO3\CMS\Vidi\ModuleLoader $moduleLoader */
		$moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ModuleLoader', $dataType);
		$moduleLoader->setIcon()
			->setModuleLanguageFile(sprintf('LLL:EXT:foo/Resources/Private/Language/%s.xlf', $dataType))
			->setDefaultPid(1) // used upon creation of a new record
			->register();
	}


Module Loader API was designed upon the work / ideas of `Steffen Ritter`_ .

.. _Steffen Ritter: http://forge.typo3.org/users/446

Grid TCA
-------------------------------

A Grid is a list view typically used within Backend modules. TCA was extended to describe how a grid and its
columns should be rendered. Take inspiration of the example below for your own data type::

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
			'name' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:name',
			),
			'email' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:email',
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


Content Repository Factory
===========================

Each Content type (e.g. fe_users, fe_groups) has its own Content repository instance which is manged internally by the Repository Factory.
In order to get the adequate instance, the repository can be fetched by this code::


	// Fetch the adequate repository for a known data type.
	$dataType = 'fe_users';
	$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance($dataType);

	// The data type can be omitted in the context of a BE module
	// Internally, the Factory ask the Module Loader to retrieve the main data type of the BE module.
	$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance();


TCA Service API
=================

This API enables to fetch info related to TCA in a programmatic way. Since TCA covers a very large set of data, the service is divided in types.
There are are four parts being addressed: table, field, grid and form. The "grid" part extends the TCA and is introduced for the need of the BE module of media.

* table: deal with the "ctrl" part of the TCA. Typical info is what is the label of the table name, what is the default sorting, etc...
* field: deal with the "columns" part of the TCA. Typical info is what configuration, label, ... has a field name.
* grid: deal with the "grid" part of the TCA.
* form: deal with the "types" (and possible "palette") part of the TCA. Get what field compose a record type.

The API is meant to be generic and can be re-use for every record type within TYPO3. Find below some code example making use of the service factory.

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

Gotchas
---------------

Important: whenever a relation is displayed within Vidi, the TCA configuration ``foreign_field``
must be defined in both side of the relations to properly work. This is needed for Vidi to retrieve the content in both direction.
Check example below which shows ``foreign_field`` set for each field.

@todo write a TCA validator if the case is too misleading.


One to Many relation and its opposite Many to One:

::

	#################
	# opposite many
	# one-to-many
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

	# opposite one
	# many-to-one
	$TCA['tx_foo_domain_model_accesscode'] = array(
		'columns' => array(
			'book' => array(
				'config' => array(
					'type' => 'select',
					'foreign_table' => 'tx_foo_domain_model_book',
					'foreign_field' => 'access_codes',
					'minitems' => 1,
					'maxitems' => 1,
				),
			),
		),
	);


Bi-directional Many to Many relation::

	#################
	# Many to many
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

Legacy Many to Many relation with comma separated values (should be avoided in favour of proper MM relations). Notice field ``foreign_field`` is omitted::

	#################
	# Legacy MM relation
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

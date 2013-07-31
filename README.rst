========================
Vidi for TYPO3 CMS
========================

Vidi stands for "versatile and interactive display" and is the code name of a list component
designed for listing all kind of records along with advanced filtering capabilities.

Veni, vidi, vici!

.. image:: https://raw.github.com/fudriot/vidi/master/Documentation/List-01.png

Project info and releases
=============================


What are the recommended releases?
------------------------------------------------

Stable version:
http://typo3.org/extensions/repository/view/vidi

Development version:
https://github.com/fudriot/vidi.git

<pre>
git clone https://github.com/fudriot/vidi.git
</pre>

Flash news about latest development are also announced on
<http://twitter.com/fudriot>

The home page of the project is at http://forge.typo3.org/projects/extension-list/


Installation
=================

Download the source code either from the `Git repository`_ to get the master or from the TER for the stable releases. Install the extension as normal in the Extension Manager.

.. _Git repository: https://github.com/fudriot/vidi.git

Configuration
=================

Configuration is mainly provided in the Extension Manager and is pretty much self-explanatory. Check possible options there.

How to load a BE module for a custom data type?
===================================================

Loading a BE module for a custom data type can be summed up with:

# Configure the module loader
# Define an icon
# Define a language file where to find the label of the fields. Make sure the file contains also the BE module name as example:

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


Module Loader API was inspired by the first draft made by `Steffen Ritter`_.


.. _Steffen Ritter:http://forge.typo3.org/users/446

Grid TCA configuration
-------------------------------

A grid is a list view of records typical of a Backend module. TCA was extended to describe how a grid and its
columns should be rendered and must be the same for your custom data type. Example::

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


Columns
---------

What attribute can be composed within array cell "columns"?

* sortable - default TRUE - whether the column is sortable or not.
* visible - default TRUE - whether the column is visible by default or hidden. There is a column picker on the GUI side controlling column visibility.
* renderer - default NULL - a class name to pass implementing
* label - default NULL - an optional label overriding the default label of the field - i.e. the label from TCA['tableName']['columns']['fieldName']['label']
* wrap - default NULL - a possible wrapping of the content. Useful in case the content of the cell should be styled in a special manner.
* width - default NULL - a possible width of the column

System columns
-----------------

There a few columns that are considered as "system" which means they don't correspond to a field but must be display to control the     GUI. By convention, theses columns are prefixed
with a double underscore e.g "__":

* __number: display a row number
* __checkbox: display a check box
* __buttons: display "edit", "deleted", ... buttons to control the row


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

	$tableName = 'sys_file';
	$serviceType = 'field';

	/** @var $fieldService \TYPO3\CMS\Media\Tca\FieldService */
	$fieldService = \TYPO3\CMS\Media\Tca\ServiceFactory::getService($tableName, $serviceType);

	// Refer to internal methods of the class.
	$fieldService->getFields();

Instantiate a TCA service related to **table**::

	$tableName = 'sys_file';
	$serviceType = 'table';

	/** @var $tableService \TYPO3\CMS\Media\Tca\TableService */
	$tableService = \TYPO3\CMS\Media\Tca\ServiceFactory::getService($tableName, $serviceType);

	// Refer to internal methods of the class.
	$tableService->getLabel();

The same would apply for the other part: form and grid.


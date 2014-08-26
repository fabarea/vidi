
Example of TCA
--------------

@todo re-writing this section is necessary.

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
======================================================

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
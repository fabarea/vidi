
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

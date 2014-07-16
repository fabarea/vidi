plugin.tx_vidi {
	settings {
		# cat=plugin.tx_vidi/a; type=int+; label=Editor FE Usergroup uid:Enter the uid of the FE Usergroup that should be allowed to edit Blogs and Post in the frontend
		editorUsergroupUid = 1
	}
	view {
		# cat=plugin.tx_vidi/file; type=string; label=Path to template root (FE)
		templateRootPath = EXT:vidi/Resources/Private/Templates/
		# cat=plugin.tx_vidi/file; type=string; label=Path to template partials (FE)
		partialRootPath = EXT:vidi/Resources/Private/Partials/
		# cat=plugin.tx_vidi/file; type=string; label=Path to template layouts (FE)
		layoutRootPath = EXT:vidi/Resources/Private/Layouts/
	}
}
module.tx_vidi {
	view {
		# cat=module.tx_vidi/file; type=string; label=Path to template root (BE)
		templateRootPath = EXT:vidi/Resources/Private/Backend/Templates/
		# cat=module.tx_vidi/file; type=string; label=Path to template partials (BE)
		partialRootPath = EXT:vidi/Resources/Private/Backend/Partials/
		# cat=module.tx_vidi/file; type=string; label=Path to template layouts (BE)
		layoutRootPath = EXT:vidi/Resources/Private/Backend/Layouts/
	}
}
# Plugin configuration
plugin.tx_vidi {
	settings {

		# Limit the number of records for auto-suggestion in the search bar.
		suggestionLimit = 1000
	}
	view {
		templateRootPaths {
			10 = {$plugin.tx_vidi.view.templateRootPath}
		}
		partialRootPaths {
			10 = {$plugin.tx_vidi.view.partialRootPath}
		}
		layoutRootPaths {
			10 = {$plugin.tx_vidi.view.layoutRootPath}
		}
		defaultPid = auto
	}
}

# Module configuration
module.tx_vidi {
	settings < plugin.tx_vidi.settings
	view < plugin.tx_vidi.view
	view {
		templateRootPaths {
			10 = {$module.tx_vidi.view.templateRootPath}
		}
		partialRootPaths {
			10 = {$module.tx_vidi.view.partialRootPath}
		}
		layoutRootPaths {
			10 = {$module.tx_vidi.view.layoutRootPath}
		}
	}
}


# Vidi persistence configuration
config.tx_vidi.persistence.backend.pages_language_overlay {
	respectSysLanguage = 0
}
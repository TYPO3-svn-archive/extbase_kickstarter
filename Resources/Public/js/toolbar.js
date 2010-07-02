var toolbarHeight = 90;

var mainToolbar = [{
	xtype: 'buttongroup',
	title: 'System',
	height: toolbarHeight,
	columns: 2,
	defaults: {
		scale: 'small'
	},
	items: [{
		xtype: 'splitbutton',
		text: 'New',
		iconCls: 't3-icon t3-icon-actions t3-icon-system-extension-install',
		menu: [
			{text: 'Simple wizard' },
			{text: 'Advanced wizard' },
			{text: 'No wizard'}
		]
	}, {
		text: 'Index',
		iconCls: 't3-icon t3-icon-actions t3-icon-system-list-open'
	}, {
		xtype: 'splitbutton',
		text: 'Load',
		iconCls: 't3-icon t3-icon-actions t3-icon-insert-record',
		menu: [
			{text: 'Load kickstarter extension'},
			{text: 'Reverse engineer extension'}
		]
	}]
}, {
	xtype: 'buttongroup',
	title: 'Extension',
	height: toolbarHeight,
	columns: 2,
	defaults: {
		scale: 'small'
	},
	items: [{
		text: 'Settings',
		iconCls: 't3-icon t3-icon-actions t3-icon-document-open'
	}, {
		text: 'Modeling',
		iconCls: 't3-icon t3-icon-actions t3-icon-version-swap-version'
	}, {
		text: 'Localization',
		iconCls: 't3-icon t3-icon-actions t3-icon-document-localize'
	}, {
		text: 'Remove scaffolding',
		iconCls: 't3-icon t3-icon-actions t3-icon-document-select'
	}, {
		text: 'Upload',
		iconCls: 't3-icon t3-icon-actions t3-icon-edit-upload'
	}]
}, {
	xtype: 'buttongroup',
	title: 'Help',
	height: toolbarHeight,
	columns: 2,
	defaults: {
		scale: 'small'
	},
	items: [{
		text: 'Extbase documentation',
		iconCls: 't3-icon t3-icon-actions t3-icon-system-extension-documentation'
	}, {
		text: 'Fluid documentation',
		iconCls: 't3-icon t3-icon-actions t3-icon-system-extension-documentation'
	}, {
		text: 'TYPO3 Core documentation',
		iconCls: 't3-icon t3-icon-actions t3-icon-system-extension-documentation'
	}, {
		text: 'TYPO3 Core API',
		iconCls: 't3-icon t3-icon-actions t3-icon-system-extension-documentation'
	}, {
		text: 'Extbase Kickstarter tutorials',
		iconCls: 't3-icon t3-icon-actions t3-icon-system-extension-documentation'
	}, {
		text: 'Tutorials',
		iconCls: 't3-icon t3-icon-actions t3-icon-system-extension-documentation'
	}]
}];
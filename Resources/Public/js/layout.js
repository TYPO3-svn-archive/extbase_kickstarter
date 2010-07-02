Ext.onReady(function(){
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	var viewport = new Ext.Viewport({
		layout: 'border',
		items: [
			{
				region: 'east',
				id: 'east-panel',
				title: 'Properties',
				collapsible: true,
				split: true,
				width: 225,
				minSize: 175,
				maxSize: 400,
				margins: '0 5 0 0',
				layout: {
					type: 'accordion',
					animate: true
				},
				items: [{
					title: 'General',
					border: false,
					iconCls: 'nav'
				}, {
					title: 'Settings',
					border: false,
					iconCls: 'settings'
				}]
			},
			{
				region: 'west',
				title: 'Extension tree',
				id: 'extension-tree',
				split: true,
				width: 200,
				minSize: 175,
				maxSize: 400,
				collapsible: true,
				margins: '0 0 0 5'
			},
			{
				region: 'center',
				collapsible: false,
				margins: '0 0 0 0'
			}
		]
	});
});
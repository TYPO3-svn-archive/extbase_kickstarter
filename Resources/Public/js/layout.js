Ext.onReady(function(){
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	var viewport = new Ext.Viewport({
		layout: 'border',
		items: [
			{
				region: 'north',
				collapsible: false,
				margins: '0 0 0 0',
				tbar: mainToolbar
			},
			{
				region: 'south',
				split: true,
				height: 100,
				minSize: 100,
				maxSize: 200,
				collapsible: true,
				title: 'Console',
				margins: '0 0 0 0'
			},
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
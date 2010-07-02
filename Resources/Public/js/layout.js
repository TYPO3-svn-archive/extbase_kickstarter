Ext.ns("T3.ExtbaseKickstarter");
T3.ExtbaseKickstarter.DocumentHeader = Ext.extend(Ext.Panel, {
	title: 'Doc header',
	region: 'north',
	html: 'I\'m a doc header',
});
Ext.reg('T3.ExtbaseKickstarter.DocumentHeader', T3.ExtbaseKickstarter.DocumentHeader);

Ext.onReady(function(){
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	var viewport = new Ext.Viewport({
		layout: 'border',
		items: [
			{
				region: 'west',
				split: true,
				width: 200,
				minSize: 175,
				maxSize: 400,
				//collapsible: true,
				margins: '0 0 0 5',
				items: [
					{
						xtype: 'T3.ExtbaseKickstarter.DocumentHeader'
					},
					{
						title: 'Extension Tree',
						region: 'south',
						id: 'extension-tree',
					},
				]
			},
			{
				region: 'center',
				collapsible: false,
				margins: '0 0 0 0',
				items: [
					{
						xtype: 'T3.ExtbaseKickstarter.DocumentHeader'
					},
					{
						region: 'south',
						layout: 'hbox',
						items: [
							{
								title: 'Main Content',
								region: 'west',
								html: 'I\'m an area',
								flex: 1,
								height: 500
							},
							{
								region: 'east',
								id: 'east-panel',
								title: 'Properties',
								width: 150,
								collapsible: true,
								split: true,
								width: 225,
								height: 500,
								minSize: 175,
								maxSize: 400,
								margins: '0 5 0 0',
								html: 'Properties'
							}
						]
					}
				]
			}
		]
	});
});
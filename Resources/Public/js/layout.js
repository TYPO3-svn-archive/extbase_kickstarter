Ext.ns("T3.ExtbaseKickstarter");
T3.ExtbaseKickstarter.DocumentHeader = Ext.extend(Ext.Panel, {
});
Ext.reg('T3.ExtbaseKickstarter.DocumentHeader', T3.ExtbaseKickstarter.DocumentHeader);

T3.ExtbaseKickstarter.ViewPort = Ext.extend(Ext.Viewport, {
	
	initComponent: function() {
		Ext.Viewport.superclass.initComponent.call(this);

		this.el = Ext.get(this.el) || Ext.getBody();
        if (this.el.dom === document.body) {
	        this.el.dom.parentNode.className += ' x-viewport';
        }
        this.el.setHeight = Ext.emptyFn;
        this.el.setWidth = Ext.emptyFn;
        this.el.setSize = Ext.emptyFn;
        this.el.dom.scroll = 'no';
        this.allowDomMove = false;
        this.autoWidth = true;
        this.autoHeight = true;
        Ext.EventManager.onWindowResize(this.fireResize, this);
        this.renderTo = this.el;
	}
});


Ext.onReady(function(){
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	var viewport = new T3.ExtbaseKickstarter.ViewPort({
		layout: 'border',
		el: 'ExtbaseKickstarter',
		defaults:	{
			height:			100,
			width:			100,
			collapsible:	true,
			collapseMode:	'mini',
			split:			true
		},
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
						title: 'Doc header',
						region: 'north',
						items: [
							{
								xtype: 'button',
								text: 'Add something',
								handler: function() {
									console.log('yeah');
								}
							}
						]
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
						title: 'Doc header',
						region: 'north',
						/*items: [
							{
								title: 'test',
								region: 'center'
							}
						]*/
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
Ext.ns("T3.ExtbaseKickstarter");
T3.ExtbaseKickstarter.DocumentHeader = Ext.extend(Ext.Panel, {});
Ext.reg('T3.ExtbaseKickstarter.DocumentHeader', T3.ExtbaseKickstarter.DocumentHeader);

var extbaseKickstarterMainToolbar = [{
	text: 'Action Menu',
	handler: function() {
		console.log('Action menu');
	}
}, {
	text: 'Action Menu item 2',
	handler: function() {
		console.log('Action menu item 2');
	}
}];

var extbaseKickstarterEastAccordionTabs = [{
	title: 'General',
	border: false,
	iconCls: 'nav'
}, {
	title: 'Settings',
	border: false,
	iconCls: 'settings'
}];

Ext.onReady(function(){
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	var viewport = new Ext.Viewport({
		layout: 'border',
		defaults: {
			collapsible:	false,
			split:			true,
			margins:		'0 0 0 0'
		},
		items: [{
			region:			'north',
			xtype:			'panel',
			tbar:			extbaseKickstarterMainToolbar
		}, {
			region:			'west',
			collapseMode:	'mini',
			width:			280,
			minWidth:		175,
			maxWidth:		300,
			items: [{
				id:			'extension-tree'
			}]
		}, {
			region:			'center',
			id:				'content'
		}, {
			region:			'east',
			collapseMode:	'mini',
			split:			true,
			width:			225,
			layout: {
				type:		'accordion',
				animate:	true
			},
			items:			extbaseKickstarterEastAccordionTabs
		}]
	});
});
Ext.ns("Tx.ExtbaseKickstarter.UserInterface");

Tx.ExtbaseKickstarter.UserInterface.Layout = Ext.extend(Ext.Viewport, {
	// TODO: document event
	initComponent: function() {
		var config = {
			layout: 'fit',
			layoutConfig: {
				align: 'stretch'
			},
			items: [
				{
					xtype: 'Tx.ExtbaseKickstarter.UserInterface.TabLayout'
				}
			]
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.UserInterface.Layout.superclass.initComponent.call(this);
	}
});
Ext.reg('Tx.ExtbaseKickstarter.UserInterface.Layout', Tx.ExtbaseKickstarter.UserInterface.Layout);
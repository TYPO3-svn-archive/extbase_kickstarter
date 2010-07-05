Ext.ns("Tx.ExtbaseKickstarter.Packages.Welcome");

Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {
		var config = {
			title: 'Welcome',
			layout: 'hbox',
			height: '100%',
			defaults: {
				xtype: 'panel',
				layout: 'vbox',
				width: '48%'
			},
			items: [{
				items: [{
					xtype: 'button',
					title: 'adasd',
					text: 'erwrwer'
				}]
			}, {

			}]
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen', Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen);
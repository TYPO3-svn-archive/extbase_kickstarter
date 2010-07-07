Ext.ns("Tx.ExtbaseKickstarter.Packages.Welcome");

Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {
		
		var config = {
			title: 'Welcome',
			layout: 'hbox',
			height: '400px',
			defaults: {
				xtype: 'panel',
				layout: 'vbox',
				height: '400px',
				width: '48%'
			},
			items: [{
//				items: [
//					new Tx.ExtbaseKickstarter.Packages.Welcome.Elements.Intro(), {
//						xtype: 'panel',
//						title: 'test'
//					}
//				]
			}, {

			}]
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen', Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen);
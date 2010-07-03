Ext.ns("Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen");

Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {
		var config = {
			title: 'Welcome'
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen', Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen);
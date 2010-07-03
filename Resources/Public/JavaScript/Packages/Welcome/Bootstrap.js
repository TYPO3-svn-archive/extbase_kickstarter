Ext.ns("Tx.ExtbaseKickstarter.Packages.Welcome");

Tx.ExtbaseKickstarter.Packages.Welcome.Bootstrap = Ext.apply(new Tx.ExtbaseKickstarter.Application.AbstractBootstrap, {
	initialize: function() {
		Tx.ExtbaseKickstarter.Application.on('Tx.ExtbaseKickstarter.UserInterface.TabLayout.afterInitialize', this._afterLayoutInitialized, this);
	},
	_afterLayoutInitialized: function(applicationLayout) {
		applicationLayout.add({
			xtype: 'Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen'
		});
	}

});
Tx.ExtbaseKickstarter.Application.registerBootstrap(Tx.ExtbaseKickstarter.Packages.Welcome.Bootstrap);

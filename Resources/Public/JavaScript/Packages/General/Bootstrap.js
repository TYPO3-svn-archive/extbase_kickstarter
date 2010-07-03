Ext.ns("Tx.ExtbaseKickstarter.Packages.General");

Tx.ExtbaseKickstarter.Packages.General.Bootstrap = Ext.apply(new Tx.ExtbaseKickstarter.Application.AbstractBootstrap, {
	initialize: function() {
		Tx.ExtbaseKickstarter.Application.on('Tx.ExtbaseKickstarter.UserInterface.TabLayout.afterInitialize', this._afterLayoutInitialized, this);
	},
	_afterLayoutInitialized: function(applicationLayout) {
		applicationLayout.add({
			xtype: 'Tx.ExtbaseKickstarter.Packages.General.General'
		});
	}

});
Tx.ExtbaseKickstarter.Application.registerBootstrap(Tx.ExtbaseKickstarter.Packages.General.Bootstrap);

Ext.ns("Tx.ExtbaseKickstarter.Packages.DomainModelling");

Tx.ExtbaseKickstarter.Packages.DomainModelling.Bootstrap = Ext.apply(new Tx.ExtbaseKickstarter.Application.AbstractBootstrap, {
	initialize: function() {
		Tx.ExtbaseKickstarter.Application.on('Tx.ExtbaseKickstarter.UserInterface.TabLayout.afterInitialize', this._afterLayoutInitialized, this);
	},
	_afterLayoutInitialized: function(applicationLayout) {
		applicationLayout.add({
			xtype: 'Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller'
		});
	}

});
Tx.ExtbaseKickstarter.Application.registerBootstrap(Tx.ExtbaseKickstarter.Packages.DomainModelling.Bootstrap);

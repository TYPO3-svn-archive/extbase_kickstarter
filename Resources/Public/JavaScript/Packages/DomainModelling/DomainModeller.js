Ext.ns("Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller");

Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {
		var config = {
			title: 'Domain modelling'
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller', Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller);
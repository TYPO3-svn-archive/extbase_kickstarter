Ext.ns("Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller");

Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {
		var config = {
			title: TYPO3.settings.extbase_kickstarter._LOCAL_LANG.domainModelling
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller', Tx.ExtbaseKickstarter.Packages.DomainModelling.DomainModeller);
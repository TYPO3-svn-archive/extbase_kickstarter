Ext.ns('Application');

/**
 * @class .Application
 * @namespace
 * @extends Ext.util.Observable
 *
 * The Extbase Kickstarter UI based on ExtJS
 *
 * @singleton
 */
Application = Ext.apply(new Ext.util.Observable, {

	/**
	 * List of all bootstrap objects which have been registered
	 * @private
	 */
	bootstrappers: [],
	
	/**
	 * Main bootstrap. This is called by Ext.onReady and calls all registered
	 * bootstraps.
	 *
	 * This method is called automatically.
	 */
	bootstrap: function() {
		this._registerDummyConsoleLogIfNeeded();
		Ext.util.Observable.capture(this, this._eventDisplayCallback, this);
		this._initializeConfiguration();
		this._invokeBootstrappers();
		Ext.QuickTips.init();

		this.fireEvent('Application.afterBootstrap');
	},
	
	/**
	 * Registers a new bootstrap class.
	 *
	 * Every bootstrap class needs to extend
	 * ApplicationAbstractBootstrap.
	 *
	 * @param {ApplicationAbstractBootstrap} bootstrap The bootstrap
	 * class to be registered.
	 * @api
	 */
	registerBootstrap: function(bootstrap) {
		this.bootstrappers.push(bootstrap);
	},

});

Ext.onReady(Application.bootstrap, Application);
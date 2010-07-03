Ext.ns("Tx.ExtbaseKickstarter.Application");

/**
 * @class Tx.ExtbaseKickstarter.Application.AbstractBootstrap
 * @namespace Tx.ExtbaseKickstarter.Application
 * @extends Ext.util.Observable
 *
 * Base class for all bootstrappers. This class provides convenience methods for extending the user interface.
 */
Tx.ExtbaseKickstarter.Application.AbstractBootstrap = Ext.extend(Ext.util.Observable, {

	/**
	 * This method is called by the main application, and inside, you should
	 * register event listeners as needed.<br />
	 *
	 * Example:
	 * <pre>Tx.ExtbaseKickstarter.Application.on([name of event], [callback], [scope]);</pre>
	 *
	 * @method initialize
	 */

	addEditorModule: function(path, items) {
		//F3.TYPO3.Application.MenuRegistry.addMenuItems(path, items);
	}
});
if(!ORYX) var ORYX = {};
if(!ORYX.CONFIG) ORYX.CONFIG = {};

function onOryxResourcesLoaded() {
	ORYX_LOGLEVEL = 5;
	ORYX.MashupAPI = {
		loadablePlugins: [
			'ORYX.Plugins.DragDropResize',
			'ORYX.Plugins.JSONSupport',
			'ORYX.Plugins.Undo',
			'ORYX.Plugins.Arrangement',
			'ORYX.Plugins.ShapeRepository',
			'ORYX.Plugins.PluginLoader',
			'ORYX.Plugins.Edit',
			//'ORYX.Plugins.Toolbar',
			'ORYX.Plugins.File',
			'ORYX.Plugins.CanvasResize',
			'ORYX.Plugins.HighlightingSelectedShapes',
			'ORYX.Plugins.Grouping',
			'ORYX.Plugins.File',
			'ORYX.Plugins.Save',
			'ORYX.Plugins.SSExtensionLoader',
			'ORYX.Plugins.DragDocker',
			'ORYX.Plugins.AddDocker',
			'ORYX.Plugins.RDFExport',
			'ORYX.Plugins.KeysMove',
			'ORYX.Plugins.RowLayouting',
			'ORYX.Plugins.ShapeHighlighting',
			'ORYX.Plugins.SelectionFrame',
			'ORYX.Plugins.ShapeMenuPlugin'
		]
	};
}
{namespace k=Tx_ExtbaseKickstarter_ViewHelpers}
<f:for each="{setters}" as="setter">
	/**
	 * Setter for {setter.property.name}
	 *
	 * @param {setter.parameters.0.typeForComment} ${setter.property.name} {setter.property.description}
	 * @return void
	 */
	public function set{setter.property.name -> k:uppercaseFirst()}({setter.parameters.0.typeHint} ${setter.property.name}) {
		$this->{setter.property.name} = ${setter.property.name};
	}
</f:for>
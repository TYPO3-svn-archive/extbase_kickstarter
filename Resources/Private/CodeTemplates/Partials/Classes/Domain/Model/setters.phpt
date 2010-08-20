{namespace k=Tx_ExtbaseKickstarter_ViewHelpers}
<f:for each="{setters}" as="setter">
	/**
	 * Setter for {setter.property.name}
	 *
	 
	 * @return void
	 */
	public function set{setter.property.name -> k:uppercaseFirst()}({setter.parameters.0.typeHint} ${setter.property.name}) {
		$this->{setter.property.name} = ${setter.property.name};
	}
</f:for>
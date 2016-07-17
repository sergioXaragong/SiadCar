<div class="row">
	<div class="col-xs-12">
		<div class="widget">
			<div class="widget__header">
				<h2>Datos Propietario</h2>
			</div>
			<div class="widget__body padding">
				<div class="row">
					<div class="col-sm-6">
						<strong>Nombre completo:</strong>
						<p><?php echo $user->nombres; ?> <?php echo $user->apellidos; ?></p>
					</div>
					<div class="col-sm-6">
						<strong>Identificacion:</strong>
						<p>CC: <?php echo $user->cedula; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<strong>Ciudad:</strong>
						<p><?php echo $vehiculo->propietario0->ciudad0->nombre; ?> - <?php echo $vehiculo->propietario0->ciudad0->depende0->nombre; ?></p>
					</div>
					<div class="col-sm-6">
						<strong>Dirección:</strong>
						<p><?php echo $vehiculo->propietario0->direccion; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<strong>Teléfono:</strong>
						<p><?php echo $user->telefono; ?></p>
					</div>
					<div class="col-sm-6">
						<strong>Celular:</strong>
						<p><?php echo $vehiculo->propietario0->celular; ?></p>
					</div>
					<div class="col-sm-6">
						<strong>Correo electrónico:</strong>
						<p><a href="<?php echo ($user->email != '')?('mailto:'.$user->email):'#'; ?>"><?php echo ($user->email != '')?$user->email:'<small>No registra</small>'; ?></a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xs-12">
		<div class="widget">
			<div class="widget__header">
				<h2>Datos Vehículo</h2>
			</div>
			<div class="widget__body padding">
				<div class="row">
					<div class="col-sm-4">
						<strong>Tipo:</strong>
						<p><?php echo $vehiculo->tipo0->nombre; ?></p>
					</div>
					<div class="col-sm-4">
						<strong>Marca:</strong>
						<p><?php echo $vehiculo->marca0->nombre; ?></p>
					</div>
					<div class="col-sm-4">
						<strong>Referencia:</strong>
						<p><?php echo $vehiculo->referencia; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<strong>Descripción:</strong>
						<p><?php echo ($vehiculo->descripcion != '')?$vehiculo->descripcion:'Ninguna...'; ?></p>
					</div>
					<div class="col-sm-3">
						<strong>Modelo:</strong>
						<p><?php echo $vehiculo->modelo; ?></p>
					</div>
					<div class="col-sm-3">
						<strong>Placas:</strong>
						<p><?php echo $vehiculo->placas; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4">
						<strong>Combustible:</strong>
						<p><?php echo $vehiculo->tipoCombustible->nombre; ?></p>
					</div>
					<div class="col-sm-4">
						<strong>Numero motor:</strong>
						<p><?php echo $vehiculo->numero_motor; ?></p>
					</div>
					<div class="col-sm-4">
						<strong>Numero chasis:</strong>
						<p><?php echo $vehiculo->numero_chasis; ?></p>
					</div>
					<div class="col-sm-4">
						<strong>Color:</strong>
						<p><?php echo $vehiculo->color; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<strong>Observaciones:</strong>
						<p><?php echo ($vehiculo->observaciones != '')?$vehiculo->observaciones:'Ninguna...'; ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="widget">
			<div class="widget__header">
				<h2>Datos Propietario</h2>
			</div>
			<div class="widget__body padding">
				<div class="row">
					<div class="col-sm-4">
						<strong>Nombre y apellidos:</strong>
						<p><?php echo $propietario->usuario0->nombres; ?> <?php echo $propietario->usuario0->apellidos; ?></p>
					</div>
					<div class="col-sm-4">
						<strong>Identificacion:</strong>
						<p>CC: <?php echo $propietario->usuario0->cedula; ?></p>
					</div>
					<div class="col-sm-4">
						<strong>Correo electrónico:</strong>
						<p><?php echo $propietario->usuario0->email; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-3">
						<strong>Ciduad:</strong>
						<p><?php echo $propietario->ciudad0->nombre; ?> - <?php echo $propietario->ciudad0->depende0->nombre; ?></p>
					</div>
					<div class="col-sm-3">
						<strong>Direcion:</strong>
						<p><?php echo $propietario->direccion; ?></p>
					</div>
					<div class="col-sm-3">
						<strong>Telefono:</strong>
						<p><?php echo $propietario->usuario0->telefono; ?></p>
					</div>
					<div class="col-sm-3">
						<strong>Celular:</strong>
						<p><?php echo $propietario->celular; ?></p>
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
				<h2>Ingreso del Vehiculo</h2>
			</div>
			<div class="widget__body padding">
				<div class="row">
					<div class="col-sm-6">
						<strong>Tipo de mantenimiento:</strong>
						<p><?php echo $ingreso->tipo0->nombre; ?></p>
					</div>
					<div class="col-sm-6">
						<?php $fechaIngreso = new DateTime($ingreso->fecha); ?>
						<strong>Fecha de ingreso:</strong>
						<p><?php echo $fechaIngreso->format('d \d\e F Y H:i:s'); ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<strong>Observaciones del cliente:</strong>
						<p><?php echo $ingreso->observaciones_cliente; ?></p>
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
				<h2>Estado del Vehiculo</h2>
			</div>
			<div class="widget__body padding">
				<div class="row">
					<div class="col-sm-12">
						<strong>Kilometraje:</strong>
						<p><?php echo $ingreso->kilmetraje; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<strong>Desperfetos:</strong>
						<p><?php echo $ingreso->desperfectos; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<strong>Elementos en el auto:</strong>
						<p>
							<?php $elementos = CJSON::decode($ingreso->elementos); ?>
							<ul class="row">
								<?php
									foreach ($elementos as $key => $elemento) {
										$elemento = ElementosVehiculo::model()->findByPk($elemento);
									?>
										<li class="col-sm-4 col-xs-12"><?php echo $elemento->nombre; ?></li>
									<?php }
								?>
							</ul>
							<?php echo (count($elementos) == 0)?'Ninguno':''; ?>
						</p>
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
				<h2>Observaciones</h2>
			</div>
			<div class="widget__body padding">
				<div class="row">
					<div class="col-xs-12">
						<p><?php echo $ingreso->observaciones; ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row end-xs">
	<div class="col-xs-12">
		<a href="<?php echo $this->createUrl('ingresos/print/'.$ingreso->id) ?>" class="btn">
			<i class="fa fa-print" aria-hidden="true"></i>
			Comprobante
		</a>
	</div>
</div>
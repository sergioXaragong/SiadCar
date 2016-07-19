<header class="content__header">
    <h1 class="header__title">Registro de Ingresos</h1>
    <h3 class="page__description">Listado de ingresos de vehículos</h3>
</header>
<!-- Page Heading End-->

<div class="row">
	<div class="col-xs-12">
		<div class="widget">
			<div class="widget__header">
				<h2>Lista Ingresos</h2>
			</div>
			<div class="widget__body padding">
				<br>
				<div class="table-responsive">
					<table class="table table__datatable table-striped" cellspacing="0" width="100%">
						<thead>
				            <tr>
				            	<th>No.</th>
				                <th>Propietario</th>
				                <th>Identificación</th>
				                <th>Vehiculo</th>
				                <th>Marca</th>
				                <th>Tipo</th>
				                <th>Fecha</th>
				                <th>Estado</th>
				                <th>Opciones</th>
				            </tr>
				        </thead>
				        <tfoot>
				            <tr>
				            	<th>No.</th>
				                <th>Propietario</th>
				                <th>Identificación</th>
				                <th>Vehiculo</th>
				                <th>Marca</th>
				                <th>Tipo</th>
				                <th>Fecha</th>
				                <th>Estado</th>
				                <th>Opciones</th>
				            </tr>
				        </tfoot>
				        <tbody>
				        	<?php foreach ($ingresos as $key => $ingreso) {
				        		$itemID = 'ingresos_tr_'.$key;
				        		$vehiculo = $ingreso->vehiculo0;
				        		$propietario = $vehiculo->propietario0;
				        	?>
				        		<tr id="<?php echo $itemID; ?>">
				        			<td><?php echo $key+1; ?></td>
				        			<td><?php echo $propietario->usuario0->nombres; ?> <?php echo $propietario->usuario0->apellidos; ?></td>
				        			<td><?php echo $propietario->usuario0->cedula; ?></td>
				        			<td><?php echo $vehiculo->placas; ?></td>
				        			<td><?php echo $vehiculo->marca0->nombre; ?></td>
				        			<td><?php echo $ingreso->tipo0->nombre; ?></td>
				        			<td><?php echo $ingreso->fecha; ?></td>
				        			<td class="tag__status">
				        				<?php if($ingreso->estado == 1){ ?>
				            				<span class="label label-primary">Entregado</span>
				            			<?php } elseif($ingreso->estado == 3){ ?>
				            				<span class="label label-warning">En revisión</span>
				            			<?php } elseif($ingreso->estado == 4){ ?>
				            				<span class="label label-success">Listo</span>
				            			<?php } else{ ?>
				            				<span class="label label-danger">En espera</span>
				            			<?php } ?>
				        			</td>
				        			<td width="100">
				        				<div class="btn-group btn-group-xs">
				        					<a href="<?php echo $this->createUrl('ingresos/'.$ingreso->id) ?>" data-toggle="tooltip" title="Ver" class="btn btn-primary"><i class="fa fa-external-link"></i></a>
				        					<a href="<?php echo $this->createUrl('ingresos/print/'.$ingreso->id) ?>" data-toggle="tooltip" title="Comprobante" class="btn btn-primary"><i class="fa fa-print"></i></a>
				        					
				        					<?php if(Yii::app()->user->getState('_rolUser') == 1){ ?>
												<a href="<?php echo $this->createUrl('ingresos/update/'.$ingreso->id); ?>" data-toggle="tooltip" title="Editar" class="btn btn-primary"><i class="fa fa-edit"></i></a>
											<?php } ?>

											<?php if($ingreso->estado == 0){ ?>
												<a href="<?php echo $this->createUrl('ingresos/change_estado/'.$ingreso->id); ?>" data-toggle="tooltip" title="En revisión" class="btn btn-primary link__ajax" data-callback="$changeStatus"><i class="fa fa-wrench"></i></a>
											<?php }
											elseif($ingreso->estado == 3){ ?>
												<a href="<?php echo $this->createUrl('ingresos/change_estado/'.$ingreso->id); ?>" data-toggle="tooltip" title="Listo" class="btn btn-primary link__ajax" data-callback="$changeStatus"><i class="fa fa-star-half-o"></i></a>
											<?php }
											elseif($ingreso->estado == 4){ ?>
												<a href="<?php echo $this->createUrl('ingresos/change_estado/'.$ingreso->id); ?>" data-toggle="tooltip" title="Entregado" class="btn btn-primary link__ajax" data-callback="$changeStatus"><i class="fa fa-share-square-o"></i></a>
											<?php } ?>
				        				</div>
				        			</td>
				        		</tr>
				        	<?php } ?>
				        </tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
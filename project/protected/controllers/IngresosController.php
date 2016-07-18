<?php

class IngresosController extends Controller{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'create','create__ajax',
					'print',
					'admin',
					'view',
					'update','update__ajax',
					'delete_vehiculo'
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionCreate(){
		$load = Yii::app()->getClientScript();
		$load->registerScriptFile(Yii::app()->request->baseUrl.'/js/controllers/ingresos.js',CClientScript::POS_END);

		$model = new RegistrosIngreso;
		
		$tipos = TiposIngreso::model()->findAll(array('order'=>'t.nombre ASC'));
		$elementos = ElementosVehiculo::model()->findAll(array('order'=>'t.nombre ASC'));

		$this->render('create', array(
			'model'=>$model,

			'tipos'=>Tools::toListSelect($tipos, 'id', 'nombre'),
			'elementos'=>$elementos
		));
	}

	public function actionCreate__ajax(){
		if(Yii::app()->getRequest()->getIsAjaxRequest() && isset($_POST['Vehiculos']) && isset($_POST['RegistrosIngreso'])){
			$response = array('status'=>'error');
			
			$vehiculo = Vehiculos::model()->findByAttributes(array('id'=>$_POST['RegistrosIngreso']['vehiculo'], 'placas'=>$_POST['Vehiculos']['placas'], 'estado'=>1));
			if($vehiculo != null){
				$model = new RegistrosIngreso;

				$model->attributes=$_POST['RegistrosIngreso'];
				$model->vehiculo = $vehiculo->id;
				$model->fecha = new CDbExpression('now()');
				$model->recibio = Yii::app()->user->getState('_idUser');

	            $elementos = array();
				if(isset($_POST['RegistrosIngreso']['elementos'])){
	            	foreach ($_POST['RegistrosIngreso']['elementos'] as $key => $elementoIn) {
	            		$elemento = ElementosVehiculo::model()->findByPk($elementoIn);
	            		if($elementoIn != null){
	            			if(!in_array($elementoIn,$elementos,false))
	            				$elementos[] = $elementoIn;
	            		}
	            	}
	            }
            	$model->elementos = CJSON::encode($elementos);

				if($model->save()){
					$response['title'] = 'Echo';
	            	$response['message'] = 'Se hizo el registro de ingreso del vehiculo con placas '.$vehiculo->placas.'. ¿Desea imprimir el comprobante de ingreso?';
	            	$response['status'] = 'success';
	            	$response['print'] = $this->createUrl('ingresos/print/'.$model->id);
				}
				else{
	            	$errors = $model->getErrors();
					$keyErrors = array_keys($model->getErrors());

					$response['title'] = 'Error validación';
	            	$response['message'] = $keyErrors[0].': '.$errors[$keyErrors[0]][0];
				}
			}
			else{
				$response['title'] = 'Error validación';
        		$response['message'] = 'Los datos del vehiculo no coinsiden en nuestro sistema. Verifique los datos e intente de nuevo.';
			}

			echo CJSON::encode($response);
		}
		else
            throw new CHttpException(404,'The requested page does not exist.');
	}

	public function actionPrint($id){
		$model = $this->loadModel($id);
		$vehiculo = $model->vehiculo0;
		$propietario = $vehiculo->propietario0;

		$this->render('print', array(
			'model'=>$model,
			'vehiculo'=>$vehiculo,
			'propietario'=>$propietario,
		));
	}

	public function actionAdmin(){
		$load = Yii::app()->getClientScript();
		$load->registerScriptFile(Yii::app()->request->baseUrl.'/js/controllers/vehiculos.js',CClientScript::POS_END);

		$vehiculos = Vehiculos::model()->findAll(array('condition'=>'t.estado != 2'));

		$this->render('admin', array(
			'vehiculos'=>$vehiculos
		));
	}

	public function actionView($id){
		$vehiculo = $this->loadModel($id);
		$user = $vehiculo->propietario0->usuario0;

		$this->render('view', array(
			'vehiculo'=>$vehiculo,
			'user'=>$user
		));
	}

	public function actionUpdate($id){
		$load = Yii::app()->getClientScript();
		$load->registerScriptFile(Yii::app()->request->baseUrl.'/js/controllers/vehiculos.js',CClientScript::POS_END);

		$model = $this->loadModel($id);
		$tipos = TiposVehiculo::model()->findAll(array('order'=>'t.nombre ASC'));
		$marcas = MarcasVehiculo::model()->findAll(array('order'=>'t.nombre ASC'));
		$combustibles = TiposCombustible::model()->findAll(array('order'=>'t.nombre ASC'));

		$modelUser = $model->propietario0->usuario0;
		$modelClient = $model->propietario0;

		$departamentos = Lugares::model()->findAllByAttributes(array('depende'=>1, 'tipo'=>2));
		$ciudades = Lugares::model()->findAllByAttributes(array('depende'=>$modelClient->ciudad0->depende, 'tipo'=>3));

		$this->render('update', array(
			'model'=>$model,

			'modelUser'=>$modelUser,
			'modelClient'=>$modelClient,

			'tipos'=>Tools::toListSelect($tipos, 'id', 'nombre'),
			'marcas'=>Tools::toListSelect($marcas, 'id', 'nombre'),
			'combustibles'=>Tools::toListSelect($combustibles, 'id', 'nombre'),

			'departamentos'=>$departamentos,
			'ciudades'=>Tools::toListSelect($ciudades, 'id', 'nombre'),
		));
	}

	public function actionUpdate__ajax($id){
		if(Yii::app()->getRequest()->getIsAjaxRequest() && isset($_POST['Usuarios']) && isset($_POST['Clientes']) && isset($_POST['Vehiculos'])){
			$response = $this->saveVehiculo($_POST['Usuarios'], $_POST['Clientes'], $_POST['Vehiculos'], $id);

			echo CJSON::encode($response);
		}
		else
            throw new CHttpException(404,'The requested page does not exist.');
	}

	public function actionDelete_vehiculo($id){
		if(Yii::app()->getRequest()->getIsAjaxRequest()){
			$response = array('status'=>'error');

			$model = $this->loadModel($id);
			$model->estado = 2;
			if($model->save()){
				$response['status'] = 'success';
				$response['title'] = 'Echo';
				$response['message'] = 'El vehiculo se a eliminado del sistema.';
			}
			else{
				$response['title'] = 'Error';
				$response['message'] = 'Ocurrio un error durante el proceso. Informe al desarrollor e intente mas tarde.';
			}

			echo CJSON::encode($response);
		}
		else
			throw new CHttpException(404,'The requested page does not exist.');	
	}

	private function saveVehiculo($postUsuarios, $postClientes, $postVehiculos, $existModel=null){
		$response = array('status'=>'error');
		$error = false;

		$cliente = null;

		if($postClientes['id'] != 0){
			$cliente = Clientes::model()->findByAttributes(array('id'=>$postClientes['id'], 'estado'=>1));
			if($cliente == null){
				$error = true;
				$response['title'] = 'Error validación';
        		$response['message'] = 'Los datos del cliente no muestran coincidencia en nuestro sistema. Por favor, verifique los datos e intente de nuevo.';
			}
		}
		else{
			$registerCliente = SIADCARClientes::createCliente($postUsuarios,$postClientes);
			$response = $registerCliente['response'];
			if($response['status'] != 'error')
				$cliente = $registerCliente['cliente'];
		}

		if($cliente != null){
			if($existModel == null){
				$vehiculo = new Vehiculos;
				$vehiculo->fecha_creacion = new CDbExpression('now()');
			}
			else{
				$vehiculo = $this->loadModel($existModel);
			}

			$vehiculo->propietario = $cliente->id;
			$vehiculo->attributes=$postVehiculos;

			$tipo = TiposVehiculo::model()->findByPk($vehiculo->tipo);
			if($tipo == null){
				$error = true;
				$response['title'] = 'Error validación';
        		$response['message'] = 'Los datos de tipo de vehiculo no muestran coincidencia en nuestro sistema. Por favor, verifique los datos e intente de nuevo.';
			}
			$marca = MarcasVehiculo::model()->findByPk($vehiculo->marca);
			if($tipo == null){
				$error = true;
				$response['title'] = 'Error validación';
        		$response['message'] = 'Los datos de marca no muestran coincidencia en nuestro sistema. Por favor, verifique los datos e intente de nuevo.';
			}
			$combustible = TiposCombustible::model()->findByPk($vehiculo->tipo_combustible);
			if($tipo == null){
				$error = true;
				$response['title'] = 'Error validación';
        		$response['message'] = 'Los datos de tipo de combustible no muestran coincidencia en nuestro sistema. Por favor, verifique los datos e intente de nuevo.';
			}

			if(!$error){
				if($vehiculo->save()){
					$response['title'] = 'Echo';
	            	$response['status'] = 'success';
	            	
	            	if($existModel == null)
	            		$response['message'] = 'El vehiculo del cliente '.$cliente->usuario0->nombres.' '.$cliente->usuario0->apellidos.' se agrego con exito en el sistema.';
	            	else
	            		$response['message'] = 'Los datos del vehículo con placas '.$vehiculo->placas.' se edito con exito.';
				}
				else{
					$response['title'] = 'Error validación';
            		$response['message'] = $vehiculo->getErrors();
				}
			}
		}

		return $response;
	}

	private function loadModel($id)
	{
		$model=RegistrosIngreso::model()->findByAttributes(array('id'=>$id), array('condition'=>'t.estado != 2'));
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
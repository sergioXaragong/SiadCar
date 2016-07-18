<?php

class VehiculosController extends Controller{
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
					'admin',
					'view',
					'update','update__ajax',
					'delete_vehiculo',

					'get_info'
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
		$load->registerScriptFile(Yii::app()->request->baseUrl.'/js/controllers/vehiculos.js',CClientScript::POS_END);

		$model = new Vehiculos;
		$tipos = TiposVehiculo::model()->findAll(array('order'=>'t.nombre ASC'));
		$marcas = MarcasVehiculo::model()->findAll(array('order'=>'t.nombre ASC'));
		$combustibles = TiposCombustible::model()->findAll(array('order'=>'t.nombre ASC'));

		$modelUser = new Usuarios;
		$modelClient = new Clientes;

		$departamentos = Lugares::model()->findAllByAttributes(array('depende'=>1, 'tipo'=>2));

		$this->render('create', array(
			'model'=>$model,

			'modelUser'=>$modelUser,
			'modelClient'=>$modelClient,

			'tipos'=>Tools::toListSelect($tipos, 'id', 'nombre'),
			'marcas'=>Tools::toListSelect($marcas, 'id', 'nombre'),
			'combustibles'=>Tools::toListSelect($combustibles, 'id', 'nombre'),

			'departamentos'=>$departamentos,
		));
	}

	public function actionCreate__ajax(){
		if(Yii::app()->getRequest()->getIsAjaxRequest() && isset($_POST['Usuarios']) && isset($_POST['Clientes']) && isset($_POST['Vehiculos'])){
			$response = $this->saveVehiculo($_POST['Usuarios'], $_POST['Clientes'], $_POST['Vehiculos']);

			echo CJSON::encode($response);
		}
		else
            throw new CHttpException(404,'The requested page does not exist.');
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

			$existVehiculo = Vehiculos::model()->findByAttributes(array('placas'=>$vehiculo->placas));
			if($existVehiculo != null){
				$error = true;
				$response['title'] = 'Error validación';
        		$response['message'] = 'Ya existe un vehiculo registrado con estas placas. Por favor, verifique los datos e intente de nuevo.';
			}
			$tipo = TiposVehiculo::model()->findByPk($vehiculo->tipo);
			if($tipo == null){
				$error = true;
				$response['title'] = 'Error validación';
        		$response['message'] = 'Los datos de tipo de vehiculo no muestran coincidencia en nuestro sistema. Por favor, verifique los datos e intente de nuevo.';
			}
			$marca = MarcasVehiculo::model()->findByPk($vehiculo->marca);
			if($marca == null){
				$error = true;
				$response['title'] = 'Error validación';
        		$response['message'] = 'Los datos de marca no muestran coincidencia en nuestro sistema. Por favor, verifique los datos e intente de nuevo.';
			}
			$combustible = TiposCombustible::model()->findByPk($vehiculo->tipo_combustible);
			if($combustible == null){
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

	public function actionGet_info(){
		if(isset($_GET['placa']) && $_GET['placa'] != ''){
			$response = array('status'=>'error');
			$placa = $_GET['placa'];

			$vehiculo = Vehiculos::model()->findByAttributes(array('placas'=>$placa, 'estado'=>1));
			if($vehiculo != null){
				$response['status'] = 'success';
				$response['vehiculo'] = array(
					'id'=>$vehiculo->id,
					'tipo'=>$vehiculo->tipo0->nombre,
					'marca'=>$vehiculo->marca0->nombre,
					'referencia'=>$vehiculo->referencia,
					'modelo'=>$vehiculo->modelo,
					'placas'=>$vehiculo->placas,
					'propietario'=>array(
						'nombres'=>$vehiculo->propietario0->usuario0->nombres,
						'apellidos'=>$vehiculo->propietario0->usuario0->apellidos,
						'identificacion'=>$vehiculo->propietario0->usuario0->cedula
					),
				);

				if(isset($_GET['template']))
					$response['render'] = $this->renderPartial($_GET['template'], $response['vehiculo'], true);
			}

			echo CJSON::encode($response);
		}
		else
			throw new CHttpException(404,'The requested page does not exist.');
	}

	private function loadModel($id)
	{
		$model=Vehiculos::model()->findByAttributes(array('id'=>$id), array('condition'=>'t.estado != 2'));
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
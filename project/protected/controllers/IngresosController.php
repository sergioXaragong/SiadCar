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
					'change_estado'
				),
				'users'=>array('@'),
				'expression'=>'Tools::hasPermission(4)',
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'update','update__ajax',
				),
				'users'=>array('@'),
				'expression'=>'Tools::hasPermission(4) && (Yii::app()->user->getState("_rolUser") == 1)',
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
	            	$response['message'] = 'Se hizo el registro de ingreso del vehiculo con placas '.$vehiculo->placas.'. ¿Desea ver el comprobante de ingreso?';
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
		$load->registerScriptFile(Yii::app()->request->baseUrl.'/js/controllers/ingresos.js',CClientScript::POS_END);

		$ingresos = RegistrosIngreso::model()->findAll(array('condition'=>'t.estado != 2'));

		$this->render('admin', array(
			'ingresos'=>$ingresos
		));
	}

	public function actionView($id){
		$ingreso = $this->loadModel($id);
		$vehiculo = $ingreso->vehiculo0;
		$propietario = $vehiculo->propietario0;

		$this->render('view', array(
			'ingreso'=>$ingreso,
			'vehiculo'=>$vehiculo,
			'propietario'=>$propietario
		));
	}

	public function actionUpdate($id){
		$load = Yii::app()->getClientScript();
		$load->registerScriptFile(Yii::app()->request->baseUrl.'/js/controllers/ingresos.js',CClientScript::POS_END);

		$model = $this->loadModel($id);
		
		$tipos = TiposIngreso::model()->findAll(array('order'=>'t.nombre ASC'));
		$elementos = ElementosVehiculo::model()->findAll(array('order'=>'t.nombre ASC'));

		$vehiculo = Vehiculos::model()->findByPk($model->vehiculo);

		$this->render('update', array(
			'model'=>$model,

			'tipos'=>Tools::toListSelect($tipos, 'id', 'nombre'),
			'elementos'=>$elementos,

			'vehiculo'=>$this->renderPartial('_propietario_info', array('vehiculo'=>$vehiculo), true),
		));
	}

	public function actionUpdate__ajax($id){
		if(Yii::app()->getRequest()->getIsAjaxRequest() && isset($_POST['RegistrosIngreso'])){
			$response = array('status'=>'error');

			$model = $this->loadModel($id);

			if($model->vehiculo == $_POST['RegistrosIngreso']['vehiculo']){
				$vehiculo = $model->vehiculo0;

				$model->attributes=$_POST['RegistrosIngreso'];
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
	            	$response['message'] = 'Se modifico el registro de ingreso del vehiculo con placas '.$vehiculo->placas.'. ¿Desea ver el comprobante de ingreso?';
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
	        		$response['message'] = 'Los datos no coinsiden en nuestro sistema. Verifique los datos e intente de nuevo.';
			}

			echo CJSON::encode($response);
		}
		else
            throw new CHttpException(404,'The requested page does not exist.');
	}

	public function actionChange_estado($id){
		if(Yii::app()->getRequest()->getIsAjaxRequest()){
			$response = array('status'=>'error');
			$ingreso = $this->loadModel($id);

			if($ingreso->estado == 0){
				$ingreso->estado = 3;
				$response['tag'] = '<span class="label label-warning">En revisión</span>';
				$response['new'] = '<a href="'.$this->createUrl('ingresos/change_estado/'.$ingreso->id).'" data-toggle="tooltip" title="Listo" class="btn btn-primary link__ajax" data-callback="$changeStatus"><i class="fa fa-star-half-o"></i></a>';
			}
			else if($ingreso->estado == 3){
				$ingreso->estado = 4;
				$response['tag'] = '<span class="label label-success">Listo</span>';
				$response['new'] = '<a href="'.$this->createUrl('ingresos/change_estado/'.$ingreso->id).'" data-toggle="tooltip" title="Entregado" class="btn btn-primary link__ajax" data-callback="$changeStatus"><i class="fa fa-share-square-o"></i></a>';
			}
			else if($ingreso->estado == 4){
				$ingreso->estado = 1;
				$response['tag'] = '<span class="label label-primary">Entregado</span>';
				$response['new'] = '';
			}

			if($ingreso->save())
				$response['status'] = 'success';

			echo CJSON::encode($response);
		}
		else
            throw new CHttpException(404,'The requested page does not exist.');
	}

	private function loadModel($id)
	{
		$model=RegistrosIngreso::model()->findByAttributes(array('id'=>$id), array('condition'=>'t.estado != 2'));
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
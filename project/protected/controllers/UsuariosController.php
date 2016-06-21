<?php

class UsuariosController extends Controller{
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
					'create', 'create__ajax',
					'admin',
				),
				'users'=>array('@'),
				'expression'=>'Tools::hasPermission(1)',
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	/**** CREACION DE USUARIOS *****/
	public function actionCreate(){
		$load = Yii::app()->getClientScript();
		$load->registerScriptFile(Yii::app()->request->baseUrl.'/js/controllers/usuarios.js',CClientScript::POS_END);


		$model = new Usuarios;
		$rolDefault = RolsUsuario::model()->findByPk(3);

		$rols = RolsUsuario::model()->findAll();
		$permissions = Permisos::model()->findAll(array('order'=>'t.nombre ASC'));

		$model->rol = $rolDefault->id;
		$model->permisos = $rolDefault->permisos;

		$this->render('create',array(
			'model'=>$model,

			'rols'=>$rols,
			'permissions'=>$permissions,

			'rolsList'=>Tools::toListSelect($rols, 'id', 'nombre'),
		));
	}
	public function actionCreate__ajax(){
		if(Yii::app()->getRequest()->getIsAjaxRequest() && isset($_POST['Usuarios'])){
			$response = array('status'=>'error');
			$error = false;

			$validator = new CEmailValidator;
			$email = $_POST['Usuarios']['email'];

			if($validator->validateValue($email)){
				$cedula = $_POST['Usuarios']['cedula'];
				$user = Usuarios::model()->findByAttributes(array('cedula'=>$cedula));
				if($user != null){
					$error = true;
					$response['title'] = 'Error validación';
            		$response['message'] = 'El documento de identificación ya se encuentra registrado por otro usuario. Por favor verifique los datos e intente de nuevo.';
            	}
			}
			else{
				$error = true;
				$response['title'] = 'Error validación';
        		$response['message'] = 'El correo electronico ingresado no es valido. Por favor verifique los datos e intente de nuevo.';
			}

			if(!$error){
				$passDefault = 'siadcar_'.rand(100, 1000);
	            $model=new Usuarios;

	            $model->attributes=$_POST['Usuarios'];
	            $model->password = Tools::crypt($passDefault);
	            
	            $model->fecha_creacion = new CDbExpression('now()');
	            $model->fecha_sesion_actual = $model->fecha_creacion;
	            $model->fecha_ultima_sesion = $model->fecha_creacion;

	            $rol = RolsUsuario::model()->findByPk($_POST['Usuarios']['rol']);
	            if($rol != null){
		            if(isset($_POST['Usuarios']['permissions'])){
		            	$permissions = CJSON::decode($rol->permisos);
		            	foreach ($_POST['Usuarios']['permissions'] as $key => $permissionIn) {
		            		$permission = Permisos::model()->findByPk($permissionIn);
		            		if($permission != null){
		            			if(!in_array($permissionIn,$permissions,false))
		            				$permissions[] = $permissionIn;
		            		}
		            	}
		            	$rol->permisos = CJSON::encode($permissions);
		            }
		            $model->permisos = $rol->permisos;
		            
		            if($model->validate(null, false)){
		            	$model->save();

		            	$response['title'] = 'Echo';
		            	$response['message'] = 'El nuevo usuario se agrego con exito. Estos son sus datos de acceso. <br> <p><strong>Usuario:</strong> '.$model->cedula.'</p><p><strong>Password:</strong> '.$passDefault.'</p>';
		            	$response['status'] = 'success';
		            }
		            else{
		            	$response['title'] = 'Error validación';
		            	$response['message'] = $model->getErrors();
		            }
	            }
	            else{
	            	$error = true;
					$response['title'] = 'Error validación';
	        		$response['message'] = 'Los datos de roll no muestran coincidencia en nuestro sistema. Por favor, verifique los datos e intente de nuevo.';
	            }
			}

			echo CJSON::encode($response);
		}
		else
            throw new CHttpException(404,'The requested page does not exist.');
	}
	/****** FIN CREACION DE USUARIOS *****/


	
}
<?php

class IngresosController extends Controller {

    /**
     * @return array action filters
     */
    public function filters(){
        return array(
            'accessControl',
            'postOnly + delete'
        );
    }

    public function accessRules(){
        return array(
            array('allow',
                'actions'=>array(
                    'get_data_form',
                    'create'
                ),
                'expression'=>'Tools::tokenAuthentication()',
            ),
            array('deny',
                'users'=>array('*')
            ),
        );
    }

    public function actionGet_data_form(){
        $response = array(
            'tipos'=>array(),
            'elementos'=>array()
        );
        $tipos = TiposIngreso::model()->findAll(array('order'=>'t.nombre ASC'));
        foreach ($tipos as $key=>$tipo){
            $response['tipos'][] = array(
                'id'=>$tipo->id,
                'nombre'=>$tipo->nombre
            );
        }
        $elementos = ElementosVehiculo::model()->findAll(array('order'=>'t.nombre ASC'));
        foreach ($elementos as $key=>$elemento){
            $response['elementos'][] = array(
                'id'=>$elemento->id,
                'nombre'=>$elemento->nombre
            );
        }

        $this->JsonResponse($response);
        return;
    }

    public function actionCreate(){
        $requestData = json_decode(file_get_contents("php://input"));
        $requestData = (array) $requestData;
        $error = false;

        if(isset($requestData['Vehiculos']) && isset($requestData['RegistrosIngreso'])){
            $requestData['Vehiculos'] = (array) $requestData['Vehiculos'];
            $requestData['RegistrosIngreso'] = (array) $requestData['RegistrosIngreso'];
            $requestData['ElementosIngreso'] = (array) $requestData['ElementosIngreso'];

            $user = Tools::tokenAuthUser();
            $vehiculo = Vehiculos::model()->findByAttributes(array(
                'id'=>$requestData['RegistrosIngreso']['vehiculo'],
                'placas'=>$requestData['Vehiculos']['placas'],
                'estado'=>1));
            if($vehiculo != null && $user != null){
                $model = new RegistrosIngreso;

                $model->attributes=$requestData['RegistrosIngreso'];
                $model->vehiculo = $vehiculo->id;
                $model->kilmetraje = $requestData['RegistrosIngreso']['kilometraje'];
                $model->fecha = new CDbExpression('now()');
                $model->recibio = $user->id;

                $elementos = array();
                if(isset($requestData['ElementosIngreso'])){
                    foreach ($requestData['ElementosIngreso'] as $key => $elementoIn) {
                        $elemento = ElementosVehiculo::model()->findByPk($elementoIn);
                        if($elemento != null){
                            if(!in_array($elementoIn,$elementos,false))
                                $elementos[] = $elementoIn;
                        }
                    }
                }
                $model->elementos = CJSON::encode($elementos);

                if($model->validate(null, true)){
                    $model->save();
                    $this->JsonResponse(array('status'=>true));
                }
                else
                    $error = true;
            }
            else
                $error = true;
        }
        else
            $error = true;

        if($error)
            $this->JsonResponse(array(), 501);
        return;
    }
}
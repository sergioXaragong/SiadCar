<?php

class VehiculosController extends Controller {

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
                    'get_info',
                ),
                'expression'=>'Tools::tokenAuthentication()',
            ),
            array('deny',
                'users'=>array('*')
            ),
        );
    }

    public function actionGet_info(){
        $requestData = json_decode(file_get_contents("php://input"));
        if(isset($requestData->placa)){
            $placa = $requestData->placa;
            $vehiculo = Vehiculos::model()->findByAttributes(array('placas'=>$placa, 'estado'=>1));
            if($vehiculo != null){
                $response = array(
                    'id'=>$vehiculo->id,
                    'tipo'=>$vehiculo->tipo0->nombre,
                    'marca'=>$vehiculo->marca0->nombre,
                    'placas'=>$vehiculo->placas,
                    'propietario'=>array(
                        'nombres'=>$vehiculo->propietario0->usuario0->nombres,
                        'apellidos'=>$vehiculo->propietario0->usuario0->apellidos,
                        'identificacion'=>$vehiculo->propietario0->usuario0->cedula
                    ),
                );

                $this->JsonResponse($response);
            }
            else
                $this->JsonResponse(array(), 404);
        }
        else
            $this->JsonResponse(array(), 404);
        return;
    }
}
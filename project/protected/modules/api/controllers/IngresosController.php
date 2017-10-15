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
                    'create',

                    'get_ingresos_cliente'
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
            'elementos'=>array(),
            'mecanicos'=>array()
        );
        $user = Tools::tokenAuthUser();
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
        if($user->rol != 3){
            $mecanicos = Usuarios::model()->findAllByAttributes(array('rol'=>3, 'estado'=>1));
            foreach ($mecanicos as $key=>$mecanico){
                $response['mecanicos'][] = array(
                    'id'=>$mecanico->id,
                    'nombre'=>$mecanico->nombres.' '.$mecanico->apellidos
                );
            }
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

    public function actionGet_ingresos_cliente(){
        $ingresos = RegistrosIngreso::model()->findAll(array(
            'condition'=>'t.estado != 2',
            'order'=>'t.fecha DESC'
        ));
        $user = Tools::tokenAuthUser();
        if($user != null){
            $lista = array();
            foreach ($ingresos as $key=>$ingreso){
                if($ingreso->vehiculo0->propietario0->usuario == $user->id)
                    $lista[] = $ingreso;
            }

            $response = array('items'=>array());
            foreach ($lista as $key=>$item){
                $response['items'][] = $this->serializeIngreso($item);
            }

            $this->JsonResponse($response);
        }
        else
            $this->JsonResponse(array('error'=>true), 401);

        return;
    }

    /**********************************************************/
    private function serializeIngreso(RegistrosIngreso $ingreso){
        $vehiculo = $ingreso->vehiculo0;
        $fechaIngreso = new DateTime($ingreso->fecha);
        if($ingreso->estado == 1)
            $estado = "Entregado";
        elseif($ingreso->estado == 3)
            $estado = "En revisión";
        elseif($ingreso->estado == 4)
            $estado = "Listo";
        else
            $estado = "En espera";

        $item = array(
            'id'=>$ingreso->id,
            'vehiculo'=>$vehiculo->placas,
            'marca'=>$vehiculo->marca0->nombre,
            'tipo'=>$ingreso->tipo0->nombre,
            'fecha'=>$fechaIngreso->format('d \d\e F Y H:i:s'),
            'estado'=>$estado
        );

        return $item;
    }
}
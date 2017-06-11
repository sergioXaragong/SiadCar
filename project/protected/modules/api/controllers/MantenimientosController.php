<?php

class MantenimientosController extends Controller {

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
                    'get_ingresos', 'get_ingreso',
                    'create'
                ),
                'expression'=>'Tools::tokenAuthentication()',
            ),
            array('deny',
                'users'=>array('*')
            ),
        );
    }

    public function actionGet_ingresos(){
        $lista = array(
            'items'=>array()
        );

        $ingresos = RegistrosIngreso::model()->findAllByAttributes(array('estado'=>3), array('order'=>'t.fecha DESC'));
        foreach ($ingresos as $key=>$ingreso){
            $lista['items'][] = $this->serializeIngreso($ingreso);
        }

        $this->JsonResponse($lista);
        return;
    }

    public function actionGet_ingreso($id){
        $ingreso = RegistrosIngreso::model()->findByAttributes(array(
            'id'=>$id,
            'estado'=>3
        ));
        if($ingreso != null)
            $this->JsonResponse($this->serializeIngreso($ingreso));
        else
            $this->JsonResponse(array(), 501);
        return;
    }

    public function actionCreate(){
        $requestData = json_decode(file_get_contents("php://input"));
        $requestData = (array) $requestData;
        $error = false;

        if(isset($requestData['Mantenimientos'])){
            $requestData['Mantenimientos'] = (array) $requestData['Mantenimientos'];
            $ingreso = $this->loadIngreso($requestData['Mantenimientos']['ingreso']);
            if($ingreso->estado == 3){
                $model = new Mantenimientos;
                $user = Tools::tokenAuthUser();

                $model->attributes = $requestData['Mantenimientos'];
                $model->ingreso = $ingreso->id;
                $model->usuario_registro = $user->id;
                $model->fecha = new CDbExpression('now()');

                if($model->mecanico == '')
                    $model->mecanico = $user->id;

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

    /**************************************************************/

    private function serializeIngreso($ingreso){
        $vehiculo = $ingreso->vehiculo0;
        $propietario = $vehiculo->propietario0;

        $item = array(
            'id'=>$ingreso->id,
            'propietario'=>$propietario->usuario0->nombres.' '.$propietario->usuario0->apellidos,
            'identificacion'=>$propietario->usuario0->cedula,
            'vehiculo'=>$vehiculo->placas,
            'marca'=>$vehiculo->marca0->nombre,
            'tipo'=>$ingreso->tipo0->nombre
        );

        return $item;
    }

    private function loadIngreso($id)
    {
        $model=RegistrosIngreso::model()->findByAttributes(array('id'=>$id), array('condition'=>'t.estado != 2'));
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }
}
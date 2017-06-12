<?php

class DefaultController extends Controller
{
    public function accessRules(){
        return array(
            array('allow',
                'actions'=>array(
                    'login',
                ),
            ),
            array('allow',
                'actions'=>array(
                    'get_user'
                ),
                'expression'=>'Tools::tokenAuthentication()',
            ),
            array('deny',
                'users'=>array('*')
            ),
        );
    }

    public function actionIndex()
    {
        $this->render('index');
    }

    public function actionLogin(){
        $request = Yii::app()->request;
        if($request->isPostRequest){
            $requestData = json_decode(file_get_contents("php://input"));

            /*$newTest = new Test();
            $newTest->request = file_get_contents("php://input");
            $newTest->save();*/

            if(isset($requestData->username) && isset($requestData->password)){
                $model = new LoginForm;
                $model->username = $requestData->username;
                $model->password = $requestData->password;
                $auth = $model->tokenAuthenticate();
                if($auth != null){
                    $this->JsonResponse($auth);
                    return;
                }
            }

            $this->JsonResponse(array('error'=>'Los datos enviados no son correctos.'), 401);
        }
    }

    public function actionGet_user(){
        $user = Tools::tokenAuthUser();
        if($user != null){
            $response = array(
                'id'=>$user->id,
                'nombre'=>$user->nombres.' '.$user->apellidos,
                'rol'=>$user->rol
            );
            $this->JsonResponse($response);
        }
        else
            $this->JsonResponse(array('error'=>'Finalizo la sesión.'), 401);
        return;
    }
}
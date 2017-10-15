<?php

class Tools extends CApplicationComponent{
	
	/**
	 * Codificar cadena de texto
	 */
	public static function crypt($password, $digito = 7){  
		$set_salt = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$salt = sprintf('$2a$%02d$', $digito);
		
		for($i = 0; $i < 22; $i++){
			$salt .= $set_salt[mt_rand(0, 63)];
		}
		return crypt($password, $salt);
	}

	public static function strToUpper($cadena){
		return strtr(strtoupper($cadena),"àèìòùáéíóúçñäëïöü","ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ");
	}

	public static function strBefore($that, $inthat){
        return substr($inthat, 0, strpos($inthat, $that));
    }

	public static function dateIsValid($value, $format='d/m/Y h:i A'){
		date_default_timezone_set('America/Bogota');
		
	    $isValid = false;
	    $value = trim($value);
		$date = @date_create(str_replace("/","-",$value), new DateTimeZone('Europe/London'));

		if(@date_format($date, $format) == $value)
			$isValid = true;

		return $isValid;
	}

	public static function hasPermission($permission){
		$user = Usuarios::model()->findByAttributes(array('id'=>Yii::app()->user->getState('_idUser'), 'estado'=>1));

		if($user != null){
			if($user->rol == 1)
				return true;
			else{
				$permissions = CJSON::decode($user->permisos);
				if(in_array($permission, $permissions, false))
					return true;
				return false;
			}
		}
		else
			return false;
	}


	public static function toListSelect($items, $key, $value){
		$models = $items;
		
		$_items=array();
		
		if(count($models) > 0)
			$_items[null] = '-- Seleccione una opción --';
		else if(count($models) == 0)
			$_items[null] = 'No hay disponibles';

		foreach($models as $model){
			$_items[$model->$key] = $model->$value;
		}
		
		return $_items;
	}


    public static function tokenAuthentication(){
        header("Access-Control-Allow-Headers: Authorization, content-type");

        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
            return true;

        $usuario = Tools::tokenAuthUser();
        if($usuario != null)
            return true;

        return false;
    }
    public static function tokenAuthUser(){
        $payload = Tools::parse_token();

        if($payload != null){
            $usuario = Usuarios::model()->findByAttributes(array('id'=>$payload['sub']));

            if($usuario != null && $usuario->estado == 1)
                return $usuario;
        }

        return null;
    }
    public static function parse_token(){
        $headers = getallheaders();

        if(!isset($headers['Authorization']))
            return null;
        $token = explode(' ', $headers['Authorization']);
        $secretKey = base64_decode(Yii::app()->params['jwtKey']);

        try{
            $payload = (array) JWT::decode($token[0], $secretKey, array('HS256'));
        } catch (Exception $e){
            $payload = null;
        }

        return $payload;
    }
}
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
}
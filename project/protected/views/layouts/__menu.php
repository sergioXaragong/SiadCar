<?php $path = explode("/",Yii::app()->request->pathInfo); ?>

        <ul class="menu">
          

          <li class="menu__item <?php echo (count($path) > 1)?((strtolower($path[0]) == 'index')?'active':''):'active'; ?>">
            <a href="<?php echo Yii::app()->homeUrl; ?>" class="item row middle-xs">
              <span class="item__icon"><i class="fa fa-home"></i></span>
              <span>Dashboard</span>
            </a>
          </li>
          
          
          <?php if(Tools::hasPermission(1)){ ?>
            <li class="menu__item has__submenu <?php echo (strtolower($path[0]) == 'usuarios')?'active':''; ?>">
              <p class="item row middle-xs">
                <span class="item__icon"><i class="fa fa-user"></i></span>
                <span>Usuarios</span>
                <span class="item__icon icon__small"><i class="fa fa-angle-down"></i></span>
              </p>
              <ul class="submenu">
                <li><a href="<?php echo $this->createUrl('usuarios/admin') ?>">Listar</a></li>
                <li><a href="<?php echo $this->createUrl('usuarios/create') ?>">Agregar</a></li>
              </ul>
            </li>
          <?php } ?>
        



        </ul>
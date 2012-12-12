<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

Autoloader::add_core_namespace('Pjax');

Autoloader::add_classes(array(
    'Pjax\\View'     => __DIR__.'/classes/view.php',
    'Pjax\\Response' => __DIR__.'/classes/response.php',
));


/* End of file bootstrap.php */
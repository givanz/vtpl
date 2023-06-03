<?php
define ( 'VTPL_DEBUG', true);//on debug templates are always recompiled and the console visible, useful for development
define ( 'PATH', dirname(__FILE__));

require_once('view.php');

$view = view :: get_instance();
$view->title = 'Foo company';
$view->description = 'Lorem ipsum';

$view->render('example1.html');

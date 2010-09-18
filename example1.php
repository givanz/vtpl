<?php
define ( 'PATH', dirname(__FILE__));

require_once('view.inc');

$view = view :: get_instance();
$view->title = 'Foo company';

$view->render('example1.html');

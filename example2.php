<?php
define ( 'PATH', dirname(__FILE__));
require_once('view.inc');


$view = view :: get_instance();
$view->title = 'psttt1 example 2'; 
$view->products = array(
    1 => array('title'=> 'Product 1', 'img' => '/img/products/1.jpg' , 'description' => 'the best product ever', price => '$10'),
    2 => array('title'=> 'Product 2', 'img' => '/img/products/2.jpg' , 'description' => 'the second best product ever', price => '$30')
);

$view->render('example2.html');

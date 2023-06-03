<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


define ( 'VTPL_DEBUG', true);//on debug templates are always recompiled and the console visible, useful for development
define ( 'PATH', dirname(__FILE__));
require_once('view.php');

$view = View :: get_instance();
$view->title = 'Vtpl example 3'; 
$view->products = array(
    1 => array('title'=> 'Product 1', 'img' => 'https://loremflickr.com/640/360?' . rand() /*'/img/products/1.jpg'*/ , 'description' => 'the best product ever', 'price' => '10'),
    2 => array('title'=> 'Product 2', 'img' => 'https://loremflickr.com/640/360?' . rand() /*'/img/products/2.jpg'*/ , 'description' => 'the second best product ever', 'price' => '30'),
    3 => array('title'=> 'Product 3', 'img' => 'https://loremflickr.com/640/360?' . rand() /*'/img/products/3.jpg'*/ , 'description' => 'the third best product ever', 'price' => '50')
);

$view->render('example3.html');

<?php
/* GMold Demo */
require('./gmold.php');
$G=new gmold();
$var=array(
	"var"=>"var's value",
	"title"=>"GMold!",
	"ifistrue"=>true,
	"ifvalueA"=>false,
	"ifvalueB"=>true,
	"switchval"=>'B',
	"forvar"=>array("gmold_a","gmold_b","gmold_c","gmold_d")
);
$G->set($var);
echo $G->get('example.html');

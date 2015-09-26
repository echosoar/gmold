<?php
require('./gmold.php');
$G=new gmold();
$var=array(
	"var"=>"var's value",
	"title"=>"GMold!",
	"ifistrue"=>true,
	"ifvalueA"=>false,
	"ifvalueB"=>true,
	"switchval"=>'B'
);
$G->set($var);
echo $G->get('example.html');

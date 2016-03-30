<?php
require_once('config.php');
require_once('database.class.php');
$form=$_POST;
var_dump($_POST);
$name=addslashes($form['name']);
$childname=addslashes($form['childname']);
$childsex=addslashes($form['childsex']);
$childbirth=addslashes($form['childbirth']);
$tel=addslashes($form['tel']);
$qq=addslashes($form['qq']);
$check=array();
for($i=1;$i<=16;$i++){
	if(isset($form['check'.(string)$i])){
		array_push($check,1);
	}else{
		array_push($check,0);
	}
}

$dataObj=new Mysql();
$dataNum=$dataObj->get_one('count(*)','questionnaire','1=1');
$id=$dataNum['count(*)']+1;
$question='';
foreach($check as $val){
	$question=$question.(string)$val;
}
$insertArray=array('id'=>$id,'name'=>$name,'childname'=>$childname,'childsex'=>$childsex,'childbirth'=>$childbirth,'tel'=>$tel,'qq'=>$qq,'question'=>$question);
$dataObj->insert('questionnaire',$insertArray);
header("Location:success.html"); 
exit();
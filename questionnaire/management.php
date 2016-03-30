<?php
header("Content-type: text/html; charset=utf-8"); 
require_once('config.php');
require_once('database.class.php');
$valid=false;
if(isset($_POST['username'])&&isset($_POST['password'])){
	$username=addslashes($_POST['username']);
	$password=addslashes($_POST['password']);
	$valid='start';
}
if($valid=='start'){
	if(($username=='admin')&&($password=='woaizhongguo')){
		$valid=true;
	}
}
$msg=null;
$dataObj=new Mysql();
if($valid){
	$msg=$dataObj->get_all('*','questionnaire','1=1');
}

?>
<!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="management.css">
	<title>自闭症测试后台查询系统</title>
</head>
<body>
	<header>
		<div id="title">
			自闭症测试后台查询系统
		</div>
	</header>
	<div id="content">
		<table class="result">
			<tr>
				<th>序号</th>
				<th>家长姓名</th>
				<th>孩子姓名</th>
				<th>孩子性别</th>
				<th>孩子生日</th>
				<th>移动电话</th>
				<th>QQ</th>
				<th>题目答案</th>
			</tr>
<?php
if($msg==null){
	echo "<tr><td>还没有家长消息</td></tr>";
}else{
	foreach($msg as $item){
		echo "<tr>";
		foreach($item as $val){
			echo "<td>{$val}</td>";
		}
		echo "</tr>";
	}
}
?>
		</table>
	</div>
</body>
</html>
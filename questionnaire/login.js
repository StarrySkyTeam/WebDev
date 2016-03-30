function sub(){
	username=document.getElementById('username').value;
	password=document.getElementById('password').value;
	wrong=document.getElementById('wrong');
	wrong.innerHTML='';
	if(username!='admin'&&password!='woaizhongguo'){
		wrong.innerHTML='用户名或密码错误';
	}else{
		document.getElementById('login').submit();	
	}
}
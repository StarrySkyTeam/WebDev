function sub(){
	var tip=document.getElementById('tip');
	tip.innerHTML='';
	var name=document.getElementById('name').value;
	var childname=document.getElementById('childname').value;
	var childage=document.getElementById('childbirth').value;
	var tel=document.getElementById('tel').value;
	var qq=document.getElementById('qq').value;
	if((name=='')||(childage=='')||(tel=='')||(qq='')){
		tip.innerHTML='请正确填写自己的信息';
	}else{
		document.getElementById('questionnaire').submit();
	}
}

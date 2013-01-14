function FaceChoose(n){
	var ClassName = "Face"+n;
	document.getElementById("Peview").setAttribute("class",ClassName);
	document.getElementById("Peview").setAttribute("className",ClassName);
	document.getElementById("face").value=n;
	//frmAdd.face.value = n;
}
function IconChange(n){
	var IconUrl = url+n+".gif";
	document.getElementById("IconImg").src = IconUrl;	
	frmAdd.icon.value = n;	
}
function changePic(n)
{
	var IconUrl = url+n+".gif";
	document.getElementById("IconImg").src = IconUrl;	
	document.getElementById("icon").value=n;
}
function contentChange(n){
	var IconUrl = url+".gif";
	document.getElementById("IconImg").src = IconUrl;	
	frmAdd.icon.value = n;	
}
function InputName(OriInput, GoalArea){
	document.getElementById(GoalArea).innerHTML = OriInput.value;
}

function strCounter(field){
	if (field.value.length > 85)
		field.value = field.value.substring(0, 85);
	else{
		document.getElementById("Char").innerHTML = 85 - field.value.length;
		var rContent;
		rContent=field.value;
		rContent = rContent.replace(/\[bq([0-9]*)]/ig, "<img src=\""+url+"/bq$1.gif\" alt=\"[bq$1]\" />");
		document.getElementById("content").innerHTML = rContent;
	}
}
function putFace(str)
{
	if(document.getElementById("idContent").value == '85字以内')
	{
		document.getElementById("idContent").value="["+str+"]";
	}
	else
	{
		document.getElementById("idContent").value += "["+str+"]";
	}
	strCounter(document.getElementById("idContent"));
}
function getTime(){
	var ThisTime = new Date();
	document.write(ThisTime.getFullYear()+"-"+(ThisTime.getMonth()+1)+"-"+ThisTime.getDate()+" "+ThisTime.getHours()+":"+ThisTime.getMinutes()+":"+ThisTime.getSeconds()); 
}

function step01()
{
	if(document.add.nickname.value=='')
	{alert('请输入昵称！');}
	else
	{
	document.getElementById('step1').style.display='none';
	document.getElementById('step2').style.display='block';
	}
}
function step02()
{
	document.getElementById('step1').style.display='block';
	document.getElementById('step2').style.display='none';
}
function chkAspk(obj){
	if(obj.nickname.value==""){
        alert("请填写[昵称]");
        obj.pick.focus();
        return false;
    }
	if(obj.content.value==""){
        alert("请填写[内容]");
        obj.send.focus();
        return false;
    }
    return true;
}
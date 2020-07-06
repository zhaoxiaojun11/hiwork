<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>天气</title>
</head>
<style>
    #box{
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 500px;
    }
    #txt{
        width: 400px;
        border: 1px solid mediumaquamarine;
    }
</style>
<body>
    <div id="box">
        <h1>天气信息定制</h1>
        <input type="text" placeholder="邮箱账户"><br>
        <select name="" id="" >
            <option value="">城市(天气)</option>
            <option value='zhengzhou'>郑州</option>
            <option value='xinyang'>信阳</option>
			<option value='luoyang'>洛阳</option>
			<option value='zhumadian'>驻马店</option>
        </select>
        <div id="txt">416641</div>
        <button>数据</button>
    </div>
</body>
<script type="text/javascript" src="/static/js/axios.min.js"></script>	
<script>
 var get = {
        one(slt) {
          return  document.querySelector(slt);
        },
        all(slt) {
            return  document.querySelectorAll(slt);
        },
    } 
   var option =get.all("option");
   var button =get.one("button");
   var select = get.one("select");
   button.onclick=function(){
    var mail = get.one("input").value;
    var city=select.value;
        axios({
                url:'Tq-axios',
                method:"post",
                data:{city,mail},
            }).then(function(r){
                r=r.data;
                console.log(r);
            }).catch(function(e){
                alert(e);
            });
   }
   
</script>
</html>
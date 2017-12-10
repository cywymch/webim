<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>WebSocket</title>
<script src="https://cdn.bootcss.com/jquery/1.12.3/jquery.js"></script>
<script type="text/javascript">
	$(function(){
		var ws = new WebSocket("ws://116.62.58.170:9502");
		if(ws.readyState!=1){
    		ws.onopen = function() {
    			ws.send("ok");
    			alert("连接成功！");
    		};
		}
		$("#send").click(function(){
			ws.send(JSON.stringify({fd:1,msg:$(".msg").val()}));
		});
		
		ws.onmessage = function(evt) {
			var received_msg = evt.data;
			alert(received_msg);
		};
	});

</script>
</head>
<body>
	<div id="sse">
		<input type="text" class="msg" value="">
		<button id="send">发送</button>
	</div>
</body>
</html>
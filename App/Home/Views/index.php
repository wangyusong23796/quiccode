<?php



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>
	<form action="/" method="get">
		<input type="text" name="nihao"/>
		<input type="submit" />
	</form>
	<?php foreach($a as $v){?>
	<h1><?php echo $v->title?></h1>
	<p><?php echo $v->common?></p>
	<?php }?>
</body>
</html>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Rating</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="bootstrap-3.2.0-dist/css/bootstrap.min.css">
</head>
<body>
	<div class="navbar navbar-inverse navbar-static-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Rating</a>
			</div>
			<div class="navbar-collapse collapse">
				<form class="navbar-form navbar-right" role="form">
					<div class="form-group">
						<input type="text" placeholder="Email" class="form-control">
					</div>
					<div class="form-group">
						<input type="password" placeholder="Password" class="form-control">
					</div>
					<button type="submit" class="btn btn-success">Sign in</button>
				</form>
			</div><!--/.navbar-collapse -->
		</div>
	</div>

	<div class="container">
		<div class="row">
			<div class="col-xs-8 col-xs-offset-2">
				<table class="table table-hover">
					<tr>
						<th>Имя</th>
						<th>Фамилия</th>
						<th>Отчествол</th>
						<th>Баллы</th>
					</tr>
					<tr>
						<td>Павел</td>
						<td>Сметанин</td>
						<td>Сергеевич</td>
						<td>100</td>
					</tr>
					<tr>
						<td>Олег</td>
						<td>Девятов</td>
						<td>Сергеевич</td>
						<td>98</td>
					</tr>
				</table>
			</div>
			<div class="col-xs-2"></div>
		</div>
	</div>

</body>
</html>
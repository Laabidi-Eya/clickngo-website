<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<title>recaptcha Google</title>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
<div class="container">
	<div class="col-md-4 col-md-offset-4">

	<form action="process.php" method="post">
		<div class="form-group">
			<input type="text" name="nome" placeholder="Nome" class="form-control">
		</div>
		<div class="form-group">
			<input type="email" name="email" placeholder="E-mail" class="form-control">
			</div>
		<div class="form-group">
			<input type="password" name="password" placeholder="Senha" class="form-control">
			</div>
			<div class="g-recaptcha" data-sitekey="6LfNwjAUAAAAAOe47FmL1-xHp6YaKKOZ07eQxj0t"></div>

		
			<button class="btn">Cadastrar</button>
		
	</form>	


	</div>
</div>
	
</body>
</html>
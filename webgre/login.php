<?php
session_start();
require_once 'config/config.php';
$token = bin2hex(openssl_random_pseudo_bytes(16));

// If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
	header('Location:index.php');
}

// If user has previously selected "remember me option":
if (isset($_COOKIE['series_id']) && isset($_COOKIE['remember_token'])) {
	// Get user credentials from cookies.
	$series_id = filter_var($_COOKIE['series_id']);
	$remember_token = filter_var($_COOKIE['remember_token']);
	$db = getDbInstance();
	// Get user By series ID:
	$db->where('series_id', $series_id);
	$row = $db->getOne('utenti');

	if ($db->count >= 1) {
		// User found. verify remember token
		if (password_verify($remember_token, $row['remember_token'])) {
			// Verify if expiry time is modified.
			$expires = strtotime($row['expires']);

			if (strtotime(date('Y-m-d H:i:s')) > $expires) {
				// Remember Cookie has expired.
				clearAuthCookie();
				header('Location:login.php');
				exit;
			}

			$_SESSION['user_logged_in'] = TRUE;
			$_SESSION['admin_type'] = $row['admin_type'];
			$_SESSION['nome'] = $row['nome'];
			$_SESSION['username'] = $row['user_name']; // Fixed index
			header('Location:index.php');
			exit;
		} else {
			clearAuthCookie();
			header('Location:login.php');
			exit;
		}
	} else {
		clearAuthCookie();
		header('Location:login.php');
		exit;
	}
}

include BASE_PATH . '/includes/header.php';
?>
<style>
	body {
		background-color: #f8f9fa !important;
		/* Sostituisci questo con il colore grigio desiderato */
	}

	.logo {
		margin-top: 5%;
		text-align: center;
	}

	/* Ridimensionamento per i dispositivi mobili */
	@media screen and (max-width: 768px) {
		.logo img {
			width: 50%;
			/* Puoi regolare la dimensione a tuo piacimento */
		}
	}
</style>
<div class="logo">
	<img src="src/img/logo.png" alt="Logo">
</div>
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-4">
			<form class="form loginform" method="POST" action="authenticate.php">
				<div class="card">
					<div class="card-header">ACCEDI</div>
					<div class="card-body">
						<div class="form-group">
							<label class="control-label">USERNAME</label>
							<input type="text" name="username" class="form-control" required="required">
						</div>
						<div class="form-group">
							<label class="control-label">PASSWORD</label>
							<input type="password" name="passwd" class="form-control" required="required">
						</div>
						<div class="form-check">
							<input name="remember" type="checkbox" class="form-check-input" value="1">
							<label class="form-check-label">Ricordami su questo computer.</label>
						</div>
						<?php if (isset($_SESSION['login_failure'])): ?>
							<div class="alert alert-danger alert-dismissable fade show mt-3">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
								<?php
								echo $_SESSION['login_failure'];
								unset($_SESSION['login_failure']);
								?>
							</div>
						<?php endif; ?>
						<button type="submit" class="btn btn-primary mt-3" style="width:100%;">Accedi</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
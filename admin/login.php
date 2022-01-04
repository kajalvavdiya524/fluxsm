<!DOCTYPE html>
<html lang="en">

<head>
  <title>Login | Flux Shop Manager</title>
  <?php include "includes/head.php"; ?>
</head>

<body class="bg-light">
  <div class="container">
    <div class="card card-login mx-auto mt-5">
      <div class="card-header">Login</div>
      <div class="card-body">
        <div id="loginMessageContainer"></div>
        <form id="loginFrm">
          <div class="form-group">
            <label for="exampleInputEmail1">Email address</label>
            <input class="form-control" id="inputEmail1" name="username" type="email" aria-describedby="emailHelp" placeholder="Enter email">
          </div>
          <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input class="form-control" id="inputPassword1" name="password" type="password" placeholder="Password">
          </div>
          <!-- <div class="form-group">
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox"> Remember Password</label>
            </div>
          </div> -->
          <a class="btn btn-primary btn-block" href="#" id="loginBtn">Login</a>
        </form>
        <!-- <div class="text-center">
          <a class="d-block small mt-3" href="register.html">Register an Account</a>
          <a class="d-block small" href="forgot-password.html">Forgot Password?</a>
        </div> -->
      </div>
    </div>
  </div>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/js.cookie.js"></script>
  <script src="js/user.js"></script>
    <script>
    (function($) {
    	userJS.init();
    })(jQuery);
    </script>
  
</body>

</html>

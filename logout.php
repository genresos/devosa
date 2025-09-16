<?php
  session_start();
  include_once("global.php");
  include_once("global/common_function.php");
  writeLog(ACTIVITY_LOGOUT);
  session_unset();
  session_destroy();
?>
<html>
<head>
  <title>Logout - PulseHR</title>
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
  <link rel="icon" href="./favicon.ico" type="image/ico">

  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #7b4397, #dc2430);
      color: #fff;
    }

    .logout-box {
      background: #ffffff; /* putih solid */
      border: 2px solid #800080; /* ungu solid */
      border-radius: 16px;
      padding: 40px;
      width: 100%;
      max-width: 380px;
      text-align: center;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      color: #333; /* teks gelap agar terbaca */
      animation: fadeIn 0.8s ease-out;
    }

    .logout-box img {
      max-width: 150px;
      margin-bottom: 20px;
    }

    .logout-box h2 {
      font-size: 20px;
      margin-bottom: 10px;
      background: linear-gradient(135deg, #ffdde1, #ee9ca7);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    .logout-box p {
      margin-bottom: 15px;
      font-size: 14px;
    }

    .logout-box a {
      display: inline-block;
      margin-top: 10px;
      padding: 10px 20px;
      border-radius: 8px;
      background: linear-gradient(135deg, #ff6ec7, #7b4397);
      color: white;
      font-weight: bold;
      text-decoration: none;
      transition: 0.3s;
    }

    .logout-box a:hover {
      transform: translateY(-2px);
      background: linear-gradient(135deg, #ff80d0, #9a57b6);
    }

    .copyright {
      margin-top: 15px;
      font-size: 12px;
      opacity: 0.8;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>

  <script type="text/javascript">
    var timer = null;
    var interval = 5; // waktu hitung mundur (detik)
    window.onload = function() {
      document.getElementById("second").innerHTML = interval;
      timer = setInterval(countDown, 1000);
    }

    function countDown() {
      var sec = document.getElementById("second");
      if (parseInt(sec.innerHTML) <= 1) {
        document.getElementById("refreshInfo").innerHTML = "Please wait, redirecting to login page...";
        location.href = "index.php";
        clearInterval(timer);
      } else {
        sec.innerHTML = parseInt(sec.innerHTML) - 1;
      }
    }
  </script>
</head>

<body>
  <div class="logout-box">
    <img src="https://i.ibb.co.com/SDMCbJ2d/RSIA-BUDI-LESTARI-001-removebg-preview.png" alt="logo" />
    <h2>You have been logged out</h2>
    <p id="refreshInfo">
      Redirecting to login page in <span id="second"></span> seconds...
    </p>
    <a href="index.php">Back to Login</a>
    <div class="copyright">
      <?php echo COPYRIGHT; ?>
    </div>
  </div>
</body>
</html>

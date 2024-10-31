<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <title>Port Checker</title>
  <link rel="icon" href="images/logo.png">
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="IP-tools/domain-ip.css" />
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="logo_item">
      <i class="bx bx-menu" id="sidebarOpen"></i>
      <img src="images/logo.png" alt="Logo" /> Port Checker by Minhaj
    </div>
    <div class="search_bar">
      <input type="text" placeholder="Search" />
    </div>
    <div class="navbar_content">
      <i class="bi bi-grid"></i>
      <i class="bx bx-sun" id="darkLight"></i>
      <i class="bx bx-bell"></i>
      <img src="images/p.jpg" alt="Profile" class="profile" />
    </div>
  </nav>

  <!-- Sidebar -->
  <?php include 'layout.php'; ?>

  <!-- Main Content -->
  <div class="container">
    <div class="input-container">
      <h1>Port Checker</h1>
      <input type="text" id="ip-input" placeholder="Enter IP or Domain" />
      <input type="number" id="port-input" placeholder="Enter Port Number" />
      <button id="check-port" onclick="showPortCheck()">Check Port</button>
    </div>

    <div id="info-card" class="row" style="display: none;">
      <div class="info-card">
        <div class="loader-container">
          <div id="loader" class="loader" style="display: none;"></div>
        </div>
        <div class="info">
          <p><strong>IP Address:</strong> <span id="ip">N/A</span></p>
          <p><strong>Port:</strong> <span id="port">N/A</span></p>
          <p><strong>Status:</strong> <span id="status">N/A</span></p>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script src="script.js"></script>
  <script>
    function showPortCheck() {
      const ip = document.getElementById("ip-input").value;
      const port = document.getElementById("port-input").value;
      const infoCard = document.getElementById("info-card");
      const loader = document.getElementById("loader");

      if (ip.trim() !== "" && port.trim() !== "") {
        infoCard.style.display = "flex"; // Show the info card
        loader.style.display = "block"; // Show the loader

        console.log("hitted");

        // Check port status
        fetch(`http://localhost:3000/?ip=${ip}&port=${port}`)

          .then(response => response.json())
          .then(data => {

            document.getElementById("ip").textContent = ip;
            
            document.getElementById("port").textContent = port;
            
            console.log("IP:", ip);
      console.log("Port:", port);


            document.getElementById("status").textContent = data.open ?
              `Port ${port} is open on ${ip}.` :
              `Port ${port} is closed on ${ip}.`;

            loader.style.display = "none"; // Hide the loader
          })
          .catch(error => {
            document.getElementById("status").textContent = 'Error: Could not check the port.';
            loader.style.display = "none"; // Hide the loader
            console.error(error);
          });
      } else {
        infoCard.style.display = "none"; // Hide the card if input is empty
        alert("Please enter a valid IP and port.");
      }
    }
  </script>

  <style>
    .loader {
      border: 4px solid #f3f3f3;
      border-top: 4px solid #007bff;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      animation: spin 1s linear infinite;
      position: absolute;
      top: 60%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }
  </style>
</body>

</html>
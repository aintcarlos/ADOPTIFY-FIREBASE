// ⭐ AUTO-CREATE ADMIN IF NO USERS EXIST (GitHub Safe) ⭐
if (!localStorage.getItem("users")) {
  const defaultAdmin = [
    {
      username: "admin",
      password: "admin123",
      contact: "00000000000",
      email: "admin@system.com",
      role: "admin"
    }
  ];
  localStorage.setItem("users", JSON.stringify(defaultAdmin));
}

// ------------------------ LOGIN LOGIC ------------------------

const form = document.getElementById('loginForm');
const loginScreen = document.getElementById('loginScreen');
const waitingScreen = document.getElementById('waitingScreen');

form.addEventListener('submit', function(e) {
  e.preventDefault();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();

  if (!username) {
    alert("⚠️ Please enter your username.");
    return;
  }

  if (!password) {
    alert("⚠️ Please enter your password.");
    return;
  }

  const users = JSON.parse(localStorage.getItem('users')) || [];
  const user = users.find(u => u.username === username && u.password === password);

  if (!user) {
    alert("❌ Incorrect username or password.");
    return;
  }

  // ⭐ SAVE LOGGED IN USER HERE ⭐
  localStorage.setItem("currentUser", JSON.stringify(user));

  // Show waiting screen
  loginScreen.style.display = 'none';
  waitingScreen.style.display = 'flex';

  // Simulate loading before redirect
  setTimeout(() => {
    alert("✅ Login successful! Welcome back, " + username + "!");

    // ⭐ ROLE CHECK ⭐
    if (user.role === "admin") {
      window.location.href = "admin-dashboard.html";
    } else {
      window.location.href = "index.html"; 
    }

  }, 2500);
});

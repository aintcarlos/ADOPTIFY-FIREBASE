const form = document.getElementById('registerForm');

form.addEventListener('submit', function(e) {
  e.preventDefault();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();
  const confirmPassword = document.getElementById('confirmPassword').value.trim();
  const contact = document.getElementById('contact').value.trim();
  const email = document.getElementById('email').value.trim();

  // Validation
  if (!username || !password || !confirmPassword || !contact || !email) {
    alert("⚠️ Please fill out all fields before submitting.");
    return;
  }

  if (password !== confirmPassword) {
    alert("❌ Passwords do not match!");
    return;
  }

  if (!/^[0-9]{11}$/.test(contact)) {
    alert("⚠️ Contact number must be exactly 11 digits (09xxxxxxxxx).");
    return;
  }

  const users = JSON.parse(localStorage.getItem('users')) || [];

  // Check if username already exists
  const exists = users.find(user => user.username === username);
  if (exists) {
    alert("⚠️ Username already exists. Please choose another.");
    return;
  }

  // ⭐ OPTION D: ALL registered accounts are normal users ⭐
  const role = "user";

  // Save new user with role = user
  users.push({ username, password, contact, email, role });
  localStorage.setItem('users', JSON.stringify(users));

  alert("✅ Registration successful!");
  window.location.href = "login.html";
});

// =========================

// Show username
document.getElementById("usernameDisplay").textContent =
  "USERNAME: " + currentUser.username;

// Show password as stars
document.getElementById("passwordHidden").textContent = "******";

// Show user info
document.getElementById("infoDisplay").textContent =
  currentUser.info || "None";

// Change Username
document.getElementById("changeUsernameBtn").addEventListener("click", () => {
  const newU = prompt("Enter new username:");
  if (!newU) return;

  const idx = users.findIndex(u => u.username === currentUser.username);
  users[idx].username = newU;
  currentUser.username = newU;

  localStorage.setItem("users", JSON.stringify(users));
  localStorage.setItem("currentUser", JSON.stringify(currentUser));

  alert("Username updated!");
  location.reload();
});

// Change Password
document.getElementById("changePasswordBtn").addEventListener("click", () => {
  const newP = prompt("Enter new password:");
  if (!newP) return;

  const idx = users.findIndex(u => u.username === currentUser.username);
  users[idx].password = newP;
  currentUser.password = newP;

  localStorage.setItem("users", JSON.stringify(users));
  localStorage.setItem("currentUser", JSON.stringify(currentUser));

  alert("Password changed!");
});

// Add Information
document.getElementById("addInfoBtn").addEventListener("click", () => {
  const info = prompt("Enter new info:");
  if (!info) return;

  const idx = users.findIndex(u => u.username === currentUser.username);
  users[idx].info = info;
  currentUser.info = info;

  localStorage.setItem("users", JSON.stringify(users));
  localStorage.setItem("currentUser", JSON.stringify(currentUser));

  alert("Information updated!");
  location.reload();
});

// Logout
document.getElementById("logoutBtn").addEventListener("click", () => {
  localStorage.removeItem("currentUser");
  alert("You have logged out.");
  location.href = "login.html";
});

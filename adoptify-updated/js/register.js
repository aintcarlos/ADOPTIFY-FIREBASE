const form = document.getElementById("registerForm");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  if (password !== confirmPassword) {
    alert("Passwords do not match!");
    return;
  }

  try {
    const userCredential = await firebase.auth().createUserWithEmailAndPassword(email, password);

    alert("✅ Registered successfully!");

    window.location.href = "login.html";

  } catch (error) {
    alert("❌ " + error.message);
  }
});
function logout() {
  firebase.auth().signOut().then(() => {
    localStorage.removeItem("currentUser");
    window.location.href = "login.html";
  });
}
const form = document.getElementById("forgotForm");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value;

  try {
    await firebase.auth().sendPasswordResetEmail(email);

    alert("📧 Password reset email sent!");

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
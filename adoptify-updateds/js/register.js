const form = document.getElementById("registerForm");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  const confirmPassword = document.getElementById("confirmPassword").value.trim();
  const contact = document.getElementById("contact").value.trim();

  // ✅ VALIDATION
  if (!username || !email || !password || !confirmPassword || !contact) {
    alert("Please fill all fields.");
    return;
  }

  if (password !== confirmPassword) {
    alert("Passwords do not match!");
    return;
  }

  try {
    // 🔥 CREATE USER (AUTH)
    const userCredential = await firebase.auth().createUserWithEmailAndPassword(email, password);

    const user = userCredential.user;

    // 🔥 SAVE EXTRA INFO TO FIRESTORE
    await firebase.firestore().collection("users").doc(user.uid).set({
      username: username,
      email: email,
      contact: contact,
      createdAt: new Date()
    });

    alert("✅ Registered successfully!");

    window.location.href = "login.html";

  } catch (error) {
    alert("❌ " + error.message);
  }
});
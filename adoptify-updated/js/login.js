// 🔥 YOUR FIREBASE CONFIG
const firebaseConfig = {
  apiKey: "YOUR_KEY",
  authDomain: "YOUR_DOMAIN",
  projectId: "YOUR_PROJECT_ID",
  appId: "YOUR_APP_ID"
};

firebase.initializeApp(firebaseConfig);

// =====================
// LOGIN
// =====================
const form = document.getElementById("loginForm");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  try {
    const userCredential = await firebase.auth().signInWithEmailAndPassword(email, password);

    alert("✅ Login successful!");

    // Save session
    localStorage.setItem("currentUser", JSON.stringify({
      email: userCredential.user.email,
      uid: userCredential.user.uid
    }));

    window.location.href = "index.html";

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
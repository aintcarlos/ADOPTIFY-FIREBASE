// 🔥 YOUR FIREBASE CONFIG
const firebaseConfig = {
    apiKey: "AIzaSyBS5BmYxSWu1Km8xyFUuUHLTfTXzbAL-8U",
    authDomain: "adoptify-64416.firebaseapp.com",
    projectId: "adoptify-64416",
    storageBucket: "adoptify-64416.firebasestorage.app",
    messagingSenderId: "562599307057",
    appId: "1:562599307057:web:ec9cb7d76aa6f71aac7210",
    measurementId: "G-KT973512PW"
  };

if (!firebase.apps.length) {
  firebase.initializeApp(firebaseConfig);
}
// =====================
// LOGIN
// =====================
const form = document.getElementById("loginForm");
const loginScreen = document.getElementById("loginScreen");
const waitingScreen = document.getElementById("waitingScreen");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("username").value.trim(); // email input
  const password = document.getElementById("password").value.trim();

  if (!email || !password) {
    alert("Please fill all fields.");
    return;
  }

  try {
    const userCredential = await firebase.auth().signInWithEmailAndPassword(email, password);

    // 🔥 SAVE USER (optional for UI use)
    const user = userCredential.user;
    localStorage.setItem("currentUser", JSON.stringify({
      uid: user.uid,
      email: user.email
    }));

    // UI loading
    loginScreen.style.display = "none";
    waitingScreen.style.display = "flex";

    setTimeout(() => {
      alert("✅ Login successful!");

      window.location.href = "index.html";
    }, 1500);

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
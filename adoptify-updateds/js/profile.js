// =========================
// LOAD USER PROFILE
// =========================

firebase.auth().onAuthStateChanged(async (user) => {

  if (!user) {
    window.location.href = "login.html";
    return;
  }

  const uid = user.uid;

  try {
    const doc = await firebase.firestore().collection("users").doc(uid).get();
    const data = doc.data();

    // DISPLAY
    document.getElementById("usernameDisplay").textContent =
      "USERNAME: " + (data.username || "No username");

    document.getElementById("passwordHidden").textContent = "******";

    document.getElementById("infoDisplay").textContent =
      data.info || "None";

  } catch (err) {
    console.error(err);
  }
});


// =========================
// CHANGE USERNAME
// =========================
document.getElementById("changeUsernameBtn").addEventListener("click", async () => {
  const newU = prompt("Enter new username:");
  if (!newU) return;

  const user = firebase.auth().currentUser;

  await firebase.firestore().collection("users").doc(user.uid).update({
    username: newU
  });

  alert("Username updated!");
  location.reload();
});


// =========================
// CHANGE PASSWORD (REAL FIREBASE)
// =========================
document.getElementById("changePasswordBtn").addEventListener("click", async () => {
  const newP = prompt("Enter new password:");
  if (!newP) return;

  const user = firebase.auth().currentUser;

  try {
    await user.updatePassword(newP);
    alert("Password updated!");
  } catch (err) {
    alert("❌ " + err.message);
  }
});


// =========================
// ADD INFORMATION
// =========================
document.getElementById("addInfoBtn").addEventListener("click", async () => {
  const info = prompt("Enter new info:");
  if (!info) return;

  const user = firebase.auth().currentUser;

  await firebase.firestore().collection("users").doc(user.uid).update({
    info: info
  });

  alert("Information updated!");
  location.reload();
});


// =========================
// LOGOUT
// =========================
document.getElementById("logoutBtn").addEventListener("click", () => {
  firebase.auth().signOut().then(() => {
    window.location.href = "login.html";
  });
});
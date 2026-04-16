// =============================
// FIREBASE SETUP (COMPAT)
// =============================
const firebaseConfig = {
    apiKey: "AIzaSyBS5BmYxSWu1Km8xyFUuUHLTfTXzbAL-8U",
    authDomain: "adoptify-64416.firebaseapp.com",
    projectId: "adoptify-64416",
    storageBucket: "adoptify-64416.appspot.com",
    messagingSenderId: "562599307057",
    appId: "1:562599307057:web:ec9cb7d76aa6f71aac7210",
    measurementId: "G-KT973512PW"
  };

if (!firebase.apps.length) {
  firebase.initializeApp(firebaseConfig);
}

const db = firebase.firestore();
const storage = firebase.storage();

// =============================
// WAIT FOR DOM
// =============================
document.addEventListener("DOMContentLoaded", function () {

  const dropzone = document.getElementById("dropzone");
  const fileInput = document.getElementById("petImageFile");
  const preview = document.getElementById("preview");
  const form = document.getElementById("addPetForm");

  let selectedFile = null;

  // CLICK
  dropzone.addEventListener("click", function () {
    fileInput.click();
  });

  // DRAG OVER
  dropzone.addEventListener("dragover", function (e) {
    e.preventDefault();
    dropzone.style.backgroundColor = "#cde8ce";
  });

  // DRAG LEAVE
  dropzone.addEventListener("dragleave", function () {
    dropzone.style.backgroundColor = "#eef6ee";
  });

  // DROP
  dropzone.addEventListener("drop", function (e) {
    e.preventDefault();
    dropzone.style.backgroundColor = "#eef6ee";
    handleImage(e.dataTransfer.files[0]);
  });

  // FILE SELECT
  fileInput.addEventListener("change", function () {
    handleImage(fileInput.files[0]);
  });

  // HANDLE IMAGE
  function handleImage(file) {
    if (!file) return;

    if (!file.type.startsWith("image/")) {
      alert("Please upload a valid image.");
      return;
    }

    selectedFile = file;

    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = "block";
      dropzone.textContent = "✅ " + file.name;
    };

    reader.readAsDataURL(file);
  }

  // =============================
  // SUBMIT (FINAL FIXED)
  // =============================
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const user = firebase.auth().currentUser;

    if (!user) {
      alert("Please login first.");
      return;
    }

    if (!selectedFile) {
      alert("Please upload an image first.");
      return;
    }

    try {
      const storageRef = storage.ref("pets/" + Date.now());

      await storageRef.put(selectedFile);

      const imageURL = await storageRef.getDownloadURL();

      await db.collection("pets").add({
        name: document.getElementById("petName").value,
        type: document.getElementById("petType").value,
        age: document.getElementById("petAge").value,
        breed: document.getElementById("petBreed").value,
        bio: document.getElementById("petBio").value,
        img: imageURL,

        // 🔥 USER CONNECTION
        ownerId: user.uid,
        ownerEmail: user.email,

        status: "Available",
        createdAt: new Date()
      });

      alert("Pet added successfully!");

      // RESET UI
      dropzone.textContent = "Click or Drag Image Here";
      preview.style.display = "none";

      window.location.href = "adopt-now.html";

    } catch (err) {
      console.error(err);
      alert("Upload failed. Check console.");
    }
  });

});
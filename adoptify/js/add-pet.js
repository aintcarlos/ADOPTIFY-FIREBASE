// =============================
// FIREBASE SETUP (COMPAT)
// =============================
const firebaseConfig = {
  apiKey: "YOUR_KEY",
  authDomain: "YOUR_DOMAIN",
  projectId: "YOUR_PROJECT_ID",
  storageBucket: "YOUR_BUCKET",
  appId: "YOUR_APP_ID"
};

firebase.initializeApp(firebaseConfig);

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

  // SUBMIT
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

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
        createdAt: new Date()
      });

      alert("Pet added successfully!");
      window.location.href = "adopt-now.html";

    } catch (err) {
      console.error(err);
      alert("Upload failed. Check console.");
    }
  });

});
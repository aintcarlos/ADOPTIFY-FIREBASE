// =============================
// FIREBASE SETUP
// =============================
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

const db = firebase.firestore();

// =============================
// GLOBAL
// =============================
const petsBody = document.getElementById("petsBody");
let pets = [];

// =============================
// AUTH CHECK + LOAD PETS
// =============================
firebase.auth().onAuthStateChanged(async (user) => {
  if (!user) {
    window.location.href = "login.html";
    return;
  }

  loadPets(user.uid);
});

// =============================
// LOAD PETS FROM FIREBASE
// =============================
async function loadPets(uid) {
  petsBody.innerHTML = "<tr><td colspan='7'>Loading...</td></tr>";

  const snapshot = await db.collection("pets")
    .where("ownerId", "==", uid)
    .get();

  pets = [];

  snapshot.forEach(doc => {
    pets.push({ id: doc.id, ...doc.data() });
  });

  renderPets();
}

// =============================
// RENDER TABLE
// =============================
function renderPets() {
  petsBody.innerHTML = "";

  if (pets.length === 0) {
    petsBody.innerHTML =
      `<tr><td colspan="7" style="text-align:center;">No pets posted yet.</td></tr>`;
    return;
  }

  pets.forEach(pet => {
    petsBody.innerHTML += `
    <tr>
      <td><img src="${pet.img}" width="70"></td>
      <td>${pet.name}</td>
      <td>${pet.type}</td>
      <td>${pet.breed}</td>
      <td>${pet.age}</td>
      <td>${pet.status || "Available"}</td>
      <td>
        <button onclick="openEditModal('${pet.id}')">Edit</button>
        <button onclick="deletePet('${pet.id}')">Delete</button>
      </td>
    </tr>
    `;
  });
}

// =============================
// DELETE PET (FIREBASE)
// =============================
async function deletePet(id) {
  if (!confirm("Delete this pet?")) return;

  await db.collection("pets").doc(id).delete();

  alert("Deleted!");
  location.reload();
}

// =============================
// EDIT MODAL (same UI)
// =============================
document.body.insertAdjacentHTML("beforeend", `
<div id="editModal" style="display:none;">
  <div class="modal-box">
    <h2>Edit Pet</h2>

    <input id="editName" placeholder="Name">
    <input id="editType" placeholder="Type">
    <input id="editBreed" placeholder="Breed">
    <input id="editAge" type="number" placeholder="Age">

    <select id="editStatus">
      <option value="Available">Available</option>
      <option value="Pending Adoption">Pending Adoption</option>
    </select>

    <input type="hidden" id="editId">

    <button onclick="saveEdit()">Save</button>
    <button onclick="closeEditModal()">Cancel</button>
  </div>
</div>
`);

const editModal = document.getElementById("editModal");

// =============================
// OPEN EDIT
// =============================
function openEditModal(id) {
  const pet = pets.find(p => p.id === id);

  document.getElementById("editId").value = pet.id;
  document.getElementById("editName").value = pet.name;
  document.getElementById("editType").value = pet.type;
  document.getElementById("editBreed").value = pet.breed;
  document.getElementById("editAge").value = pet.age;
  document.getElementById("editStatus").value = pet.status || "Available";

  editModal.style.display = "flex";
}

// =============================
// SAVE EDIT (FIREBASE)
// =============================
async function saveEdit() {
  const id = document.getElementById("editId").value;

  await db.collection("pets").doc(id).update({
    name: document.getElementById("editName").value,
    type: document.getElementById("editType").value,
    breed: document.getElementById("editBreed").value,
    age: document.getElementById("editAge").value,
    status: document.getElementById("editStatus").value
  });

  alert("Updated!");
  location.reload();
}

// =============================
function closeEditModal() {
  editModal.style.display = "none";
}

// =============================
// LOGOUT
// =============================
function logout() {
  firebase.auth().signOut().then(() => {
    window.location.href = "login.html";
  });
}
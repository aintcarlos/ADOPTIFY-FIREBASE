// =============================
// FIREBASE SETUP (COMPAT)
// =============================
const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "YOUR_PROJECT.firebaseapp.com",
  projectId: "YOUR_PROJECT_ID"
};

if (!firebase.apps.length) {
  firebase.initializeApp(firebaseConfig);
}

const db = firebase.firestore();

// =============================
(function () {
  const $ = id => document.getElementById(id);

  let pets = [];

  // =========================
  // 🔥 FETCH ALL PETS
  // =========================
  async function fetchPets() {
    const snapshot = await db.collection("pets").get();

    pets = [];

    snapshot.forEach(doc => {
      pets.push({
        id: doc.id,
        ...doc.data()
      });
    });

    renderPets(pets);
  }

  const petsGrid = $('petsGrid');

  function createPetCard(p){
    const el = document.createElement('article');
    el.className = 'pet-card';
    el.innerHTML = `
      <section class="pet-media" style="background-image:url('${p.img}')"></section>
      <section class="pet-body">
        <h4>${p.name}</h4>
        <p>${p.type} · ${p.age}y · ${p.gender || "N/A"}</p>
        <p>${p.bio}</p>
        <a href="#" class="view-btn" data-id="${p.id}">View Details</a>
      </section>`;
    return el;
  }

  function renderPets(list){
    petsGrid.innerHTML = '';
    list.forEach(p => petsGrid.appendChild(createPetCard(p)));
  }

  // =========================
  // 🔥 FILTER
  // =========================
  function applyFilters() {
    let list = [...pets];

    const qtype = $('filterType').value;
    const qbreed = $('filterBreed').value;
    const qage = parseFloat($('filterAge').value);

    if (qtype) list = list.filter(p => p.type === qtype);
    if (qbreed) list = list.filter(p => p.breed === qbreed);
    if (!isNaN(qage)) list = list.filter(p => p.age <= qage);

    renderPets(list);
  }

  $('btnApply').onclick = e => {
    e.preventDefault();
    applyFilters();
  };

  $('btnReset').onclick = e => {
    e.preventDefault();
    renderPets(pets);
  };

  // =========================
  // 🔥 MODAL
  // =========================
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".view-btn");
    if (!btn) return;
    e.preventDefault();

    const id = btn.dataset.id;
    const pet = pets.find(p => p.id == id);

    $("infoImage").src = pet.img;
    $("infoName").textContent = pet.name;
    $("infoType").textContent = pet.type;
    $("infoGender").textContent = pet.gender || "N/A";
    $("infoAge").textContent = pet.age;
    $("infoBio").textContent = pet.bio;

    $("openAddPetFromInfo").dataset.id = pet.id;
    $("petInfoModal").classList.remove("hidden");
  });

  $("closePetInfo").onclick = () => {
    $("petInfoModal").classList.add("hidden");
  };

  // =========================
  // 🔥 ADD TO MY PETS (FIXED)
  // =========================
  $("openAddPetFromInfo").onclick = async () => {
    const user = firebase.auth().currentUser;

    if (!user) {
      alert("Please login first.");
      return;
    }

    const id = $("openAddPetFromInfo").dataset.id;
    const pet = pets.find(p => p.id == id);

    try {
      await db.collection("myPets").add({
        petId: pet.id,
        name: pet.name,
        type: pet.type,
        breed: pet.breed,
        age: pet.age,
        img: pet.img,

        userId: user.uid, // 🔥 important
        userEmail: user.email,

        addedAt: new Date()
      });

      alert("Added to My Pets!");
    } catch (err) {
      console.error(err);
      alert("Error adding pet.");
    }
  };

  // =========================
  // 🔥 INIT
  // =========================
  document.addEventListener("DOMContentLoaded", fetchPets);

})();

// =============================
// LOGOUT
// =============================
function logout() {
  firebase.auth().signOut().then(() => {
    window.location.href = "login.html";
  });
}
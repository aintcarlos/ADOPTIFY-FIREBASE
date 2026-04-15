(function () {

  const $ = id => document.getElementById(id);

  const db = firebase.firestore();
  const tbody = $("adoptBody");

  const currentUser = JSON.parse(localStorage.getItem("currentUser"));

  let pets = [];

  // =========================
  // 🔥 LOAD PETS FROM FIREBASE
  // =========================
  async function loadPets() {
    tbody.innerHTML = "<tr><td colspan='6'>Loading...</td></tr>";

    const snapshot = await db.collection("pets").get();

    pets = [];

    snapshot.forEach(doc => {
      const pet = { id: doc.id, ...doc.data() };

      // ✅ OPTIONAL: only show user's pets
      if (currentUser && pet.ownerEmail !== currentUser.email) return;

      pets.push(pet);
    });

    renderTable();
  }

  // =========================
  // 🔥 RENDER TABLE
  // =========================
  function renderTable() {
    tbody.innerHTML = "";

    if (pets.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6">No pets found.</td></tr>`;
      return;
    }

    pets.forEach(p => {
      const row = document.createElement("tr");

      if (p.status === "Booked") {
        row.style.backgroundColor = "#c8ffca";
      }

      row.innerHTML = `
        <td><input type="checkbox" class="pet-check" data-id="${p.id}"></td>
        <td><img src="${p.img}" alt="${p.name}"></td>
        <td>${p.name}</td>
        <td>${p.type}</td>
        <td>${p.age} years</td>
        <td class="pet-status">${p.status || "Available"}</td>
      `;

      tbody.appendChild(row);
    });
  }

  // =========================
  // 🔥 DELETE PET
  // =========================
  $("btnDelete").addEventListener("click", async () => {
    const checks = document.querySelectorAll(".pet-check:checked");

    if (checks.length === 0) {
      alert("Please select at least one pet.");
      return;
    }

    for (let c of checks) {
      await db.collection("pets").doc(c.dataset.id).delete();
    }

    alert("Deleted successfully!");
    loadPets();
  });

  // =========================
  // 🔥 BOOK MEETUP
  // =========================
  $("btnBookMeetup").addEventListener("click", () => {
    const selected = document.querySelector(".pet-check:checked");

    if (!selected) {
      alert("Please select a pet.");
      return;
    }

    const pet = pets.find(p => p.id === selected.dataset.id);

    $("bookPetName").textContent = pet.name;
    $("bookForm").dataset.id = pet.id;
    $("bookModal").classList.remove("hidden");
  });

  $("bookCancel").onclick = () => {
    $("bookModal").classList.add("hidden");
  };

  // =========================
  // 🔥 CONFIRM BOOKING
  // =========================
  $("bookForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const id = $("bookForm").dataset.id;

    await db.collection("pets").doc(id).update({
      status: "Booked"
    });

    alert("💚 Meetup booked!");
    $("bookModal").classList.add("hidden");

    loadPets();
  });

  // =========================
  // 🔥 CANCEL BOOKING
  // =========================
  $("btnCancelMeetup").addEventListener("click", async () => {
    const selected = document.querySelector(".pet-check:checked");

    if (!selected) {
      alert("Select a pet.");
      return;
    }

    const id = selected.dataset.id;

    await db.collection("pets").doc(id).update({
      status: "Available"
    });

    alert("❌ Booking canceled.");
    loadPets();
  });

  // INIT
  document.addEventListener("DOMContentLoaded", loadPets);

})();
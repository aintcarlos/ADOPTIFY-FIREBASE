document.addEventListener("DOMContentLoaded", function () {

  const db = firebase.firestore();

  const tbody = document.getElementById("adoptBody");
  const btnBook = document.getElementById("btnBookMeetup");
  const btnDelete = document.getElementById("btnDelete");
  const btnCancelMeetup = document.getElementById("btnCancelMeetup");

  const modal = document.getElementById("bookModal");
  const bookForm = document.getElementById("bookForm");
  const bookPetName = document.getElementById("bookPetName");
  const bookCancel = document.getElementById("bookCancel");

  let pets = [];

  async function loadPets() {
    const snapshot = await db.collection("pets").get();

    pets = [];
    snapshot.forEach(doc => {
      pets.push({ id: doc.id, ...doc.data() });
    });

    render();
  }

  function render() {
    tbody.innerHTML = "";

    pets.forEach(p => {
      const row = `
        <tr>
          <td><input type="checkbox" class="pet-check" value="${p.id}"></td>
          <td><img src="${p.img}"></td>
          <td>${p.name}</td>
          <td>${p.type}</td>
          <td>${p.age}</td>
          <td>${p.status || "Available"}</td>
        </tr>
      `;
      tbody.innerHTML += row;
    });
  }

  btnBook.onclick = () => {
    const selected = document.querySelector(".pet-check:checked");

    if (!selected) {
      alert("Select a pet");
      return;
    }

    const pet = pets.find(p => p.id === selected.value);

    bookPetName.textContent = pet.name;
    bookForm.dataset.id = pet.id;

    modal.classList.remove("hidden");
  };

  bookCancel.onclick = () => {
    modal.classList.add("hidden");
  };

  bookForm.onsubmit = async (e) => {
    e.preventDefault();

    const id = bookForm.dataset.id;

    await db.collection("pets").doc(id).update({
      status: "Booked"
    });

    modal.classList.add("hidden");
    loadPets();
  };

  btnDelete.onclick = async () => {
    const checks = document.querySelectorAll(".pet-check:checked");
    const selected = document.querySelector(".pet-check:checked");

    if (!selected) {
      alert("Select a pet");
      return;
    }

    for (let c of checks) {
      await db.collection("pets").doc(c.value).delete();
    }

    loadPets();
  };

  btnCancelMeetup.onclick = async () => {
    const selected = document.querySelector(".pet-check:checked");
    if (!selected) {
      alert("Select a pet");
      return;
    }

    if (!selected) return;

    await db.collection("pets").doc(selected.value).update({
      status: "Available"
    });

    loadPets();
  };

  loadPets();

});

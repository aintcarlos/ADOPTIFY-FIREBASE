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

  // 🔥 ADDED
  const dateInput = document.getElementById("bookDate");

  let pets = [];

  // 🔥 ADDED
  let currentBookedDates = [];

  // =============================
  // LOAD PETS FROM FIREBASE
  // =============================
  async function loadPets() {
    try {
      const snapshot = await db.collection("pets").get();

      pets = [];
      snapshot.forEach(doc => {
        pets.push({ id: doc.id, ...doc.data() });
      });

      render();

    } catch (err) {
      console.error("Error loading pets:", err);
    }
  }

  // =============================
  // RENDER TABLE (FIXED)
  // =============================
  function render() {
    tbody.innerHTML = "";

    if (pets.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6">No pets found.</td></tr>`;
      return;
    }

    pets.forEach(p => {

      let rowStyle = "";
      if (p.status === "Booked") {
        rowStyle = "style='background:#c8ffca'";
      }

      const row = `
        <tr ${rowStyle}>
          <td><input type="checkbox" class="pet-check" value="${p.id}"></td>
          <td><img src="${p.img}" width="60"></td>
          <td>${p.name}</td>
          <td>${p.type}</td>
          <td>${p.age}</td>
          <td>${p.status || "Available"}</td>
        </tr>
      `;

      tbody.innerHTML += row;
    });
  }

  // =============================
  // BOOK MEETUP (WORKING)
  // =============================
  btnBook.onclick = async () => {
    const selected = document.querySelector(".pet-check:checked");

    if (!selected) {
      alert("Select a pet");
      return;
    }

    const pet = pets.find(p => p.id === selected.value);

    if (!pet) {
      alert("Pet not found.");
      return;
    }

    // 🔥 ADDED (GET BOOKINGS)
    const doc = await db.collection("pets").doc(pet.id).get();
    const data = doc.data();
    currentBookedDates = (data.bookings || []).map(b => b.date);

    bookPetName.textContent = pet.name;
    bookForm.dataset.id = pet.id;

    modal.classList.remove("hidden");
  };

  // =============================
  // 🔥 ADDED (DATE COLOR FEEDBACK)
  // =============================
  dateInput.addEventListener("input", () => {
    if (currentBookedDates.includes(dateInput.value)) {
      dateInput.style.background = "#ffb3b3"; // red
    } else {
      dateInput.style.background = "#fff";
    }
  });

  // =============================
  // CLOSE MODAL
  // =============================
  bookCancel.onclick = () => {
    modal.classList.add("hidden");
  };

  // =============================
  // CONFIRM BOOKING
  // =============================
  bookForm.onsubmit = async (e) => {
    e.preventDefault();

    const id = bookForm.dataset.id;

    // 🔥 ADDED
    const selectedDate = dateInput.value;

    try {

      // 🔥 ADDED (GET EXISTING BOOKINGS)
      const docRef = db.collection("pets").doc(id);
      const docSnap = await docRef.get();
      const pet = docSnap.data();

      let bookings = pet.bookings || [];

      // 🔥 ADDED (CHECK DUPLICATE DATE)
      if (bookings.find(b => b.date === selectedDate)) {
        alert("❌ This date is already booked!");
        return;
      }

      // 🔥 ADDED (SAVE BOOKING)
      bookings.push({
        date: selectedDate,
        userId: firebase.auth().currentUser?.uid || "guest",
        email: firebase.auth().currentUser?.email || "guest"
      });

      await docRef.update({
        status: "Booked", // keep your original
        bookings: bookings // 🔥 new feature
      });

      alert("Meetup booked!");

      modal.classList.add("hidden");
      loadPets();

    } catch (err) {
      console.error(err);
      alert("Error booking meetup.");
    }
  };

  // =============================
  // DELETE PET
  // =============================
  btnDelete.onclick = async () => {
    const checks = document.querySelectorAll(".pet-check:checked");

    if (checks.length === 0) {
      alert("Select at least one pet");
      return;
    }

    try {
      for (let c of checks) {
        await db.collection("pets").doc(c.value).delete();
      }

      alert("Deleted successfully!");
      loadPets();

    } catch (err) {
      console.error(err);
      alert("Delete failed.");
    }
  };

  // =============================
  // CANCEL BOOKING
  // =============================
  btnCancelMeetup.onclick = async () => {
    const selected = document.querySelector(".pet-check:checked");

    if (!selected) {
      alert("Select a pet");
      return;
    }

    try {
      await db.collection("pets").doc(selected.value).update({
        status: "Available"
      });

      alert("Booking canceled.");
      loadPets();

    } catch (err) {
      console.error(err);
      alert("Cancel failed.");
    }
  };

  // =============================
  // INIT
  // =============================
  loadPets();

});


// =============================
// LOGOUT
// =============================
function logout() {
  firebase.auth().signOut().then(() => {
    window.location.href = "login.html";
  });
}
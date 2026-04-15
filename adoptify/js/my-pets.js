// =========================
//   My Pets – PRIVATE VERSION
// =========================

// Get current user
const email = currentUser.email;

// Load ONLY pets for this user
let myPets = JSON.parse(localStorage.getItem(email + "_pets")) || [];

// Main table container
const petsBody = document.getElementById("petsBody");

function loadPets() {
    petsBody.innerHTML = "";

    if (myPets.length === 0) {
        petsBody.innerHTML =
            `<tr><td colspan="7" style="text-align:center; padding:20px;">No pets posted yet.</td></tr>`;
        return;
    }

    myPets.forEach(pet => {
        petsBody.innerHTML += `
        <tr>
            <td><img src="${pet.img}" alt="Pet"></td>
            <td>${pet.name}</td>
            <td>${pet.type}</td>
            <td>${pet.breed}</td>
            <td>${pet.age}</td>
            <td>${pet.status}</td>
            <td>
                <button class="action-btn edit-btn" onclick="openEditModal(${pet.id})">Edit</button>
                <button class="action-btn delete-btn" onclick="deletePet(${pet.id})">Delete</button>
            </td>
        </tr>
        `;
    });
}

loadPets();


// =========================
//       DELETE PET
// =========================
function deletePet(id) {
    if (!confirm("Are you sure you want to delete this pet?")) return;

    myPets = myPets.filter(p => p.id !== id);
    localStorage.setItem(email + "_pets", JSON.stringify(myPets));

    loadPets();
}



// =========================
//      EDIT PET MODAL
// =========================

// Create modal dynamically
document.body.insertAdjacentHTML("beforeend", `
<div id="editModal" style="display:none;">
    <div class="modal-box">
        <h2>Edit Pet</h2>

        <label>Name</label>
        <input id="editName">

        <label>Type</label>
        <input id="editType">

        <label>Breed</label>
        <input id="editBreed">

        <label>Age</label>
        <input id="editAge" type="number">

        <label>Status</label>
        <select id="editStatus">
            <option value="Available">Available</option>
            <option value="Pending Adoption">Pending Adoption</option>
         
        </select>

        <div class="modal-actions">
            <button class="modal-save" onclick="saveEdit()">Save</button>
            <button class="modal-cancel" onclick="closeEditModal()">Cancel</button>
        </div>

        <input type="hidden" id="editId">
    </div>
</div>
`);

const editModal = document.getElementById("editModal");


// =========================
//     OPEN EDIT MODAL
// =========================
function openEditModal(id) {
    const pet = myPets.find(p => p.id === id);

    if (!pet) {
        alert("Pet not found.");
        return;
    }

    document.getElementById("editId").value = pet.id;
    document.getElementById("editName").value = pet.name;
    document.getElementById("editType").value = pet.type;
    document.getElementById("editBreed").value = pet.breed;
    document.getElementById("editAge").value = pet.age;
    document.getElementById("editStatus").value = pet.status || "Available";

    editModal.style.display = "flex";
}


// =========================
//     SAVE EDIT CHANGES
// =========================
function saveEdit() {
    const id = Number(document.getElementById("editId").value);

    const index = myPets.findIndex(p => p.id === id);
    if (index === -1) {
        alert("Pet not found.");
        return;
    }

    // Update object
    myPets[index].name = document.getElementById("editName").value.trim();
    myPets[index].type = document.getElementById("editType").value.trim();
    myPets[index].breed = document.getElementById("editBreed").value.trim();
    myPets[index].age = document.getElementById("editAge").value.trim();
    myPets[index].status = document.getElementById("editStatus").value.trim();

    // Save only to this user's storage
    localStorage.setItem(email + "_pets", JSON.stringify(myPets));

    loadPets();
    closeEditModal();
    alert("Pet updated successfully!");
}


// =========================
//     CLOSE MODAL
// =========================
function closeEditModal() {
    editModal.style.display = "none";
}


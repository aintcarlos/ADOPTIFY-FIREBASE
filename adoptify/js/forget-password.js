const $ = id => document.getElementById(id);

// STEP 1 – CHECK EMAIL
$("fp_checkEmail").addEventListener("click", () => {
    const email = $("fp_email").value.trim().toLowerCase();

    if (email === "") {
        $("fp_message").textContent = "Please enter your email.";
        return;
    }

    // Load users from the global "users" array
    let users = JSON.parse(localStorage.getItem("users") || "[]");

    // Search inside users array
    const user = users.find(u => u.email.toLowerCase() === email);

    if (!user) {
        $("fp_message").textContent = "Email not found.";
        return;
    }

    // Store email temporarily
    localStorage.setItem("fp_target_email", email);

    // Switch steps
    $("stepEmail").classList.add("hidden");
    $("stepReset").classList.remove("hidden");
});


// STEP 2 – RESET PASSWORD
$("fp_resetBtn").addEventListener("click", () => {
    const newPass = $("fp_newPass").value;
    const confirmPass = $("fp_confirmPass").value;

    if (newPass === "" || confirmPass === "") {
        $("fp_resetMessage").textContent = "Password fields cannot be empty.";
        return;
    }

    if (newPass !== confirmPass) {
        $("fp_resetMessage").textContent = "Passwords do not match.";
        return;
    }

    const email = localStorage.getItem("fp_target_email");

    // Load users list
    let users = JSON.parse(localStorage.getItem("users") || "[]");

    // Find the correct user
    const index = users.findIndex(u => u.email.toLowerCase() === email);

    if (index === -1) {
        $("fp_resetMessage").textContent = "Error: User not found.";
        return;
    }

    // Update password
    users[index].password = newPass;

    // Save back
    localStorage.setItem("users", JSON.stringify(users));

    // Clear temp storage
    localStorage.removeItem("fp_target_email");

    alert("✅ Password updated! Please login again.");
    window.location.href = "login.html";
});

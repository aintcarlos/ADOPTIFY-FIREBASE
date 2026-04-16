const sections = document.querySelectorAll("section");
const navLinks = document.querySelectorAll("nav a[href^='#']");

window.addEventListener("scroll", () => {
  let current = "";

  sections.forEach(section => {
    const sectionTop = section.offsetTop - 100;
    const sectionHeight = section.clientHeight;

    if (pageYOffset >= sectionTop && pageYOffset < sectionTop + sectionHeight) {
      current = section.getAttribute("id");
    }
  });

  navLinks.forEach(link => {
    link.classList.remove("active");
    if (link.getAttribute("href") === "#" + current) {
      link.classList.add("active");
    }
  });
});
const images = [
  "images/1.jpg",
  "images/2.jpg",
  "images/4.jpg",
  "images/1.jpg",
  "images/2.jpg"
];

let index = 0;

function changeBackground() {
  const hero = document.querySelector(".hero");
  hero.style.backgroundImage = `url('${images[index]}')`;

  index = (index + 1) % images.length;
}

setInterval(changeBackground, 3000); // every 3 seconds

// load first image immediately
changeBackground();
function logout() {
  firebase.auth().signOut().then(() => {
    localStorage.removeItem("currentUser");
    window.location.href = "login.html";
  });
}
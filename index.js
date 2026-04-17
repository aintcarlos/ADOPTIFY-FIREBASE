const sections = document.querySelectorAll("section");
const navLinks = document.querySelectorAll("nav a[href^='#']");

window.addEventListener("scroll", () => {
  let current = "";
  sections.forEach(section => {
    const sectionTop = section.offsetTop - 100;
    if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + section.clientHeight) {
      current = section.getAttribute("id");
    }
  });
  navLinks.forEach(link => {
    link.classList.remove("active");
    if (link.getAttribute("href") === "#" + current) link.classList.add("active");
  });
});

// ── Hero background slideshow using free Unsplash pet photos ──
const images = [
  "https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=1600&q=80",
  "https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=1600&q=80",
  "https://images.unsplash.com/photo-1601758124510-52d02ddb7cbd?w=1600&q=80",
  "https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=1600&q=80",
  "https://images.unsplash.com/photo-1537151608828-ea2b11777ee8?w=1600&q=80",
];

let index = 0;

function changeBackground() {
  const hero = document.querySelector(".hero");
  if (hero) {
    hero.style.backgroundImage = `url('${images[index]}')`;
    index = (index + 1) % images.length;
  }
}

setInterval(changeBackground, 4000);
changeBackground();

"use strict";

// HOME PAGE

// 3 main feature=>

//footer slider +
const trackEl = document.querySelector(".companies-track");
let position = 0;
const speed = 0.1;
let paused = false;

trackEl.addEventListener("mouseenter", () => (paused = true));
trackEl.addEventListener("mouseleave", () => (paused = false));

trackEl.innerHTML += trackEl.innerHTML;

function animate() {
  if (!paused) {
    position -= speed;

    if (Math.abs(position) >= trackEl.scrollWidth / 2) {
      position = 0;
    }

    trackEl.style.transform = `translateX(${position}px)`;
  }

  requestAnimationFrame(animate);
}
animate();

// header appear +
let lastScroll = 0;
const headerEl = document.querySelector(".site-header");

window.addEventListener("scroll", () => {
  const currentScroll = window.pageYOffset;

  if (currentScroll > lastScroll && currentScroll > 80) {
    headerEl.style.transform = "translate(-50%, -200%)";
  } else {
    headerEl.style.transform = "translate(-50%, 0)";
  }
  if (currentScroll < 700) {
    headerEl.style.transform = "translate(-50%, 0)";
    return;
  }

  lastScroll = currentScroll;
});

// sub menu +
document.querySelectorAll(".has-submenu").forEach((item) => {
  const subMenu = item.querySelector(".sub-menu");

  item.addEventListener("mouseenter", () => {
    subMenu.style.opacity = "1";
    subMenu.style.visibility = "visible";
    subMenu.style.transform = "translateY(0)";
  });

  item.addEventListener("mouseleave", () => {
    subMenu.style.opacity = "0";
    subMenu.style.visibility = "hidden";
    subMenu.style.transform = "translateY(10px)";
  });
});

//hamburger

//main hamburger +
const hamburger = document.getElementById("hamburgerMenu");
hamburger.addEventListener("click", () => {
  hamburger.classList.toggle("active");
});

//submenu of hamburger +
const hamburgerItems = document.querySelectorAll(".hamburger-dropdown > li");

hamburgerItems.forEach((item) => {
  const subMenu = item.querySelector(".hamburger-sub-menu");
  if (!subMenu) return;

  item.addEventListener("click", (e) => {
    e.stopPropagation();

    hamburgerItems.forEach((el) => {
      if (el !== item) el.classList.remove("open");
    });

    item.classList.toggle("open");
  });
});

// 4 additional feature=>

//go to top +
const goTopBtn = document.querySelector(".goTop");

window.onscroll = function () {
  if (
    document.body.scrollTop > 300 ||
    document.documentElement.scrollTop > 300
  ) {
    goTopBtn.style.display = "block";
  } else {
    goTopBtn.style.display = "none";
  }
};

function scrollToTop() {
  const scrollDuration = 800;
  const scrollStep = -window.scrollY / (scrollDuration / 15);

  function scrollAnimation() {
    if (window.scrollY !== 0) {
      window.scrollBy(0, scrollStep);
      requestAnimationFrame(scrollAnimation);
    }
  }

  requestAnimationFrame(scrollAnimation);
}
goTopBtn.addEventListener("click", scrollToTop);

// pre load +
window.addEventListener("load", () => {
  const preloader = document.querySelector(".preloader");
  setTimeout(() => {
    preloader.classList.add("hidden");
  }, 2000);
});

// progress bar +
window.addEventListener("scroll", () => {
  const scrollTop = window.scrollY;
  const docHeight = document.body.scrollHeight - window.innerHeight;
  const scrollPercent = (scrollTop / docHeight) * 100;
  const progressBar = document.querySelector(".scroll-progress");
  progressBar.style.width = scrollPercent + "%";
});

//dynamic date +
function calculateDaysAgo(dateString) {
  const publishedDate = new Date(dateString);
  const currentDate = new Date();

  const diffTime = Math.abs(currentDate - publishedDate);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}

function updateNewsDays() {
  const publishDateElements = document
    .querySelectorAll(".publish-date")
    .forEach((element) => {
      const date = element.getAttribute("datetime");
      const daysAgo = calculateDaysAgo(date);
      element.textContent = `${daysAgo} days ago`;
    });
}
updateNewsDays();

const loginText = document.querySelector(".login-txt");
const loginIcon = document.querySelector(".login-icon");

const user = JSON.parse(localStorage.getItem("user"));

if (user) {
  loginText.textContent = "logout";
  loginText.href = "#";
  loginIcon.src = user.avatar;

  loginText.addEventListener("click", (e) => {
    e.preventDefault();
    localStorage.removeItem("user");
    location.reload();
  });
} else {
  loginText.textContent = "login";
  loginText.href = "login.html";
  loginIcon.src = "assets/icon/nav icon/profile_icon.png";
}

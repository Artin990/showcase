"use strict";

window.addEventListener("load", () => {
  const preloader = document.querySelector(".preloader");
  setTimeout(() => {
    preloader.classList.add("hidden");
  }, 2000);
});

const loginForm = document.querySelector(".login-box-input");
const signInBtn = document.querySelector(".sign-in-btn");
const userNameInp = document.querySelector(".username-value");
const passwordInp = document.querySelector(".password-value");
const errorLine = document.querySelector(".lg-error");
const imgEl = document.querySelector(".login-icon");

loginForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  signInBtn.disabled = true;
  errorLine.classList.remove("error-show");
  try {
    const res = await fetch("https://fakestoreapi.com/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        username: userNameInp.value,
        password: passwordInp.value,
      }),
    });
    if (!res.ok) {
      throw new Error("username or password is not correct");
    }
    const data = await res.json();
    const userData = {
      username: userNameInp.value,
      avatar: "assets/logins.jpg",
      token: data.token,
    };
    localStorage.setItem("user", JSON.stringify(userData));
    window.location.href = "index.html";

  } catch (error) {
    errorLine.classList.add("error-show");
  }
});

function checkInputs() {
  if (userNameInp.value.trim() !== "" && passwordInp.value.trim() !== "") {
    signInBtn.disabled = false;
    signInBtn.classList.add("active-btn");
  } else {
    signInBtn.disabled = true;
    signInBtn.classList.remove("active-btn");
  }
}

userNameInp.addEventListener("input", checkInputs);
passwordInp.addEventListener("input", checkInputs);

const showPassword = document
  .querySelector(".show-password")
  .addEventListener("click", () => {
    if (passwordInp.type == "password") {
      passwordInp.type = "text";
    } else {
      passwordInp.type = "password";
    }
  });

const headingText = "Lab Reschedule Management System";
const typingSpeed = 80;

const loginBtn = document.getElementById("login-btn");
const page1 = document.getElementById("page1");
const page2 = document.getElementById("page2");

const page1Heading = page1.querySelector(".heading");
const page2LeftHeading = page2.querySelector(".heading-left");

// Typing effect for page 1 heading only
function typeHeading(element, text, speed, callback) {
  element.textContent = "";
  let i = 0;
  function type() {
    if (i < text.length) {
      element.textContent += text.charAt(i++);
      setTimeout(type, speed);
    } else if (callback) {
      callback();
    }
  }
  type();
}

window.addEventListener("DOMContentLoaded", () => {
  typeHeading(page1Heading, headingText, typingSpeed);
});

// Login button click
loginBtn.addEventListener("click", () => {
  // Slide page1 fully left and out
  page1.style.transform = "translateX(-100%)";

  // Show page2 immediately (no typing effect on heading-left)
  page2.classList.add("active");
});

// Password toggle unchanged
const togglePasswordBtn = document.getElementById("toggle-password");
const passwordField = document.getElementById("password");

togglePasswordBtn.addEventListener("click", () => {
  if (passwordField.type === "password") {
    passwordField.type = "text";
    togglePasswordBtn.textContent = "Hide";
  } else {
    passwordField.type = "password";
    togglePasswordBtn.textContent = "Show";
  }
});

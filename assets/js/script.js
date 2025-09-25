document.addEventListener("DOMContentLoaded", () => {
  const num1 = Math.floor(Math.random() * 10) + 1;
  const num2 = Math.floor(Math.random() * 10) + 1;
  const captchaQuestion = document.getElementById("captcha-question");
  const captchaSumField = document.getElementById("captcha_sum");

  if (captchaQuestion && captchaSumField) {
    captchaQuestion.textContent = `${num1} + ${num2} = ?`;
    captchaSumField.value = num1 + num2;
  }
});






// document.addEventListener("DOMContentLoaded", function () {
//     // Generate simple math puzzle
//     const num1 = Math.floor(Math.random() * 10) + 1;
//     const num2 = Math.floor(Math.random() * 10) + 1;
//     const puzzle = document.getElementById("puzzle");
//     if (puzzle) {
//         puzzle.innerText = `${num1} + ${num2}`;
//         const form = document.querySelector(".candidate-form");
//         form.addEventListener("submit", function (e) {
//             const answer = document.getElementById("captcha").value;
//             if (parseInt(answer) !== num1 + num2) {
//                 e.preventDefault();
//                 alert("Incorrect puzzle answer! Please try again.");
//             }
//         });
//     }
// });


// document.addEventListener("DOMContentLoaded", function() {
//     const puzzleEl = document.getElementById("puzzle");
//     const hidden   = document.getElementById("captcha_sum");
//     const a = Math.floor(Math.random() * 10) + 1;
//     const b = Math.floor(Math.random() * 10) + 1;
//     puzzleEl.textContent = a + " + " + b;
//     hidden.value = a + b;
// });




 document.addEventListener("DOMContentLoaded", function() {
        const puzzleEl = document.getElementById("puzzle");
        const hidden   = document.getElementById("captcha_sum");
        const a = Math.floor(Math.random() * 10) + 1;
        const b = Math.floor(Math.random() * 10) + 1;
        puzzleEl.textContent = a + " + " + b;
        hidden.value = a + b;
    }
    );
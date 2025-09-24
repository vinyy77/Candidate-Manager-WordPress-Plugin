document.addEventListener("DOMContentLoaded", function () {
    // Generate simple math puzzle
    const num1 = Math.floor(Math.random() * 10) + 1;
    const num2 = Math.floor(Math.random() * 10) + 1;
    const puzzle = document.getElementById("puzzle");
    if (puzzle) {
        puzzle.innerText = `${num1} + ${num2}`;
        const form = document.querySelector(".candidate-form");
        form.addEventListener("submit", function (e) {
            const answer = document.getElementById("captcha").value;
            if (parseInt(answer) !== num1 + num2) {
                e.preventDefault();
                alert("Incorrect puzzle answer! Please try again.");
            }
        });
    }
});

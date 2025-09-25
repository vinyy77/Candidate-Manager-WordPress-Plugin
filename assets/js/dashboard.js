document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById("candidateModal");
    const closeBtn = document.querySelector(".modal-close");

    document.querySelectorAll(".btn-view").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("m-name").innerText = this.dataset.name;
            document.getElementById("m-email").innerText = this.dataset.email;
            document.getElementById("m-phone").innerText = this.dataset.phone || "N/A";
            document.getElementById("m-age").innerText = this.dataset.age || "N/A";
            document.getElementById("m-position").innerText = this.dataset.position;
            document.getElementById("m-address").innerText = this.dataset.address1 + " " + (this.dataset.address2 || "");
            document.getElementById("m-city").innerText = this.dataset.city;
            document.getElementById("m-state").innerText = this.dataset.state;
            document.getElementById("m-zip").innerText = this.dataset.zip;
            document.getElementById("m-license").href = this.dataset.license || "#";
            document.getElementById("m-resume").href = this.dataset.resume || "#";
            modal.style.display = "flex";
        });
    });

    closeBtn.addEventListener("click", () => modal.style.display = "none");
    window.addEventListener("click", e => { if (e.target === modal) modal.style.display = "none"; });
});

document.addEventListener("DOMContentLoaded", function () {
    // USER BOX TOGGLE
    const userBtn = document.querySelector('#user-btn');
    const userBox = document.querySelector('.user-box');

    console.log("User button found:", userBtn);
    console.log("User box found:", userBox);

    if (userBtn && userBox) {
        userBtn.addEventListener('click', function(e) {
            alert("Button clicked!");
            userBox.classList.toggle('active');
            e.stopPropagation();
        });

        userBox.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        document.addEventListener('click', function(e) {
            if (e.target !== userBtn && !userBox.contains(e.target)) {
                userBox.classList.remove('active');
            }
        });

        console.log("Event listeners added successfully");
    }


    // REVEAL ON SCROLL
    function revealOnScroll() {
        const elements = document.querySelectorAll(".animate");
        elements.forEach((el) => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight * 0.85) {
                el.classList.add("active");
            }
        });
    }

    window.addEventListener("scroll", revealOnScroll);
    revealOnScroll(); // Run on load
});

// document.getElementById("menu-btn").addEventListener("click", function() {
//     let navbar = document.getElementById("navbar");
//     navbar.style.display = (navbar.style.display === "block") ? "none" : "block";
// });


function previewFile() {
    const file = document.getElementById("file").files[0];
    const pdfPreview = document.getElementById("pdfPreview");
    const videoPreview = document.getElementById("videoPreview");

    if (file) {
        const fileType = file.type;

        if (fileType.includes("pdf")) {
            pdfPreview.src = URL.createObjectURL(file);
            pdfPreview.style.display = "block";
            videoPreview.style.display = "none";
        } 
        else if (fileType.includes("video")) {
            videoPreview.src = URL.createObjectURL(file);
            videoPreview.style.display = "block";
            pdfPreview.style.display = "none";
        } 
        else {
            alert("Unsupported file type. Please upload a PDF or a video.");
            pdfPreview.style.display = "none";
            videoPreview.style.display = "none";
        }
    }
};
function closeEditForm() {
    document.querySelector(".edit-book-form").style.display = "none";
    window.history.pushState({}, document.title, "educator_product.php");
}

function toggleMenu() {
    let menu = document.querySelector("nav ul");
    menu.classList.toggle("show");
}

function searchInPDF() {
    let searchText = document.getElementById("searchText").value;
    let iframe = document.getElementById("pdfViewer");

    if (searchText.trim() !== "") {
        iframe.src = iframe.src.split("#")[0] + "#search=" + encodeURIComponent(searchText);
    }
}
function toggleMenu() {
    let menu = document.getElementById("mobileMenu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}


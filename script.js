document.addEventListener("DOMContentLoaded", function() {
    let userBtn=document.querySelector('#user-btn');
    let userBox=document.querySelector('.user-box');


// let menu=document.querySelector('#menu-btn');


// menu.addEventListener('click',function(){
//     let nav=document.querySelector('.navbar');
//     nav.classList.toggle('active');
// })

userBtn.addEventListener('click',function(){
    userBox.classList.toggle('active');
});
});

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

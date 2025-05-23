const url = document.getElementById("pdfCanvas").getAttribute("data-url");
const studentId = document.getElementById("watermark").textContent;
let pdfDoc = null,
    pageNum = 1,
    pageRendering = false,
    pageNumPending = null,
    scale = 2,
    canvas = document.getElementById("pdfCanvas"),
    ctx = canvas.getContext("2d");

function renderPage(num) {
    pageRendering = true;

    pdfDoc.getPage(num).then((page) => {
        const viewport = page.getViewport({ scale: 2 });
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport,
        };

        const renderTask = page.render(renderContext);
        renderTask.promise.then(() => {
          //  addWatermark(); // Add watermark after rendering
            pageRendering = false;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        });
    });

    document.getElementById("pageNum").textContent = num;
}

function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

function onPrevPage() {
    if (pageNum <= 1) return;
    pageNum--;
    queueRenderPage(pageNum);
}

function onNextPage() {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum++;
    queueRenderPage(pageNum);
}

document.getElementById("prevPage").addEventListener("click", onPrevPage);
document.getElementById("nextPage").addEventListener("click", onNextPage);

function addWatermark() {
    // ctx.save();
    // ctx.font = "20px Arial";
    // ctx.fillStyle = "rgba(255, 0, 0, 0.3)"; // Light red
    // ctx.textAlign = "center";
    // ctx.textBaseline = "middle";
    // ctx.translate(canvas.width / 2, canvas.height / 2);
    // ctx.rotate(-Math.PI / 6); // Rotate for diagonal effect
    // ctx.fillText(studentId, 0, 0);
    // ctx.restore();
}

pdfjsLib.getDocument(url).promise.then((pdf) => {
    pdfDoc = pdf;
    document.getElementById("pageCount").textContent = pdf.numPages;
    renderPage(pageNum);
});

document.addEventListener("contextmenu", (event) => {
    event.preventDefault();
    alert("Right Click is disabled on this page!");
});

document.addEventListener("keydown", (event) => {
    if (
        event.key === "PrintScreen" ||
        (event.ctrlKey && event.key === "p") ||
        (event.metaKey && event.key === "p")
    ) {
        event.preventDefault();
        alert("Screenshots and printing are disabled!");
    }
});

document.addEventListener( "dragstart", ( event ) => event.preventDefault() );
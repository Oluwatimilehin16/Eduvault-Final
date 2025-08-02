<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access!");
}

// Validate book ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request!");
}

$book_id = intval($_GET['id']);
$student_id = $_SESSION['student_id'];

// Check if student has purchased this book
$purchase_check = mysqli_query($conn, "SELECT * FROM purchases WHERE student_id = '$student_id' AND product_id = '$book_id' AND status = 'paid'");
if (mysqli_num_rows($purchase_check) == 0) {
    die("You haven't purchased this book or payment is pending!");
}

// Fetch book details with base64 data
$sql = "SELECT title, file_data, file_name, category FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    die("Book not found!");
}

$title = htmlspecialchars($book['title']);
$file_data = $book['file_data'];
$file_name = $book['file_name'];
$category = $book['category'];

if (empty($file_data)) {
    die("File data not found!");
}

// Extract file extension
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Secure Book Viewer</title>
    <link rel="stylesheet" href="view_book.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
</head>
<body>
    <div class="viewer-container">
        <h2 class="book-title"><?php echo $title; ?></h2>
        
        <?php if ($file_ext === 'pdf'): ?>
            <!-- PDF Viewer -->
            <canvas id="pdfCanvas"></canvas>
            <div class="pagination">
                <button id="prevPage">Previous</button>
                <span>Page <span id="pageNum">1</span> of <span id="pageCount">?</span></span>
                <button id="nextPage">Next</button>
            </div>
            
            <script>
                // Convert base64 to ArrayBuffer for PDF.js
                function base64ToArrayBuffer(base64) {
                    const binaryString = atob(base64.split(',')[1]); // Remove data:application/pdf;base64, prefix
                    const bytes = new Uint8Array(binaryString.length);
                    for (let i = 0; i < binaryString.length; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }
                    return bytes.buffer;
                }

                // PDF.js setup
                pdfjsLib.getDocument({
                    data: base64ToArrayBuffer('<?php echo $file_data; ?>')
                }).promise.then(function(pdf) {
                    const canvas = document.getElementById('pdfCanvas');
                    const ctx = canvas.getContext('2d');
                    let currentPage = 1;
                    const numPages = pdf.numPages;
                    
                    document.getElementById('pageCount').textContent = numPages;
                    
                    function renderPage(pageNum) {
                        pdf.getPage(pageNum).then(function(page) {
                            const viewport = page.getViewport({scale: 1.5});
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            
                            const renderContext = {
                                canvasContext: ctx,
                                viewport: viewport
                            };
                            
                            page.render(renderContext);
                            document.getElementById('pageNum').textContent = pageNum;
                        });
                    }
                    
                    // Render first page
                    renderPage(currentPage);
                    
                    // Page navigation
                    document.getElementById('prevPage').addEventListener('click', function() {
                        if (currentPage > 1) {
                            currentPage--;
                            renderPage(currentPage);
                        }
                    });
                    
                    document.getElementById('nextPage').addEventListener('click', function() {
                        if (currentPage < numPages) {
                            currentPage++;
                            renderPage(currentPage);
                        }
                    });
                }).catch(function(error) {
                    console.error('Error loading PDF:', error);
                    document.querySelector('.viewer-container').innerHTML = '<p>Error loading PDF file.</p>';
                });
            </script>
            
        <?php elseif (in_array($file_ext, ['mp4', 'avi'])): ?>
            <!-- Video Viewer -->
            <video controls style="width: 100%; max-width: 800px;" controlsList="nodownload">
                <source src="<?php echo $file_data; ?>" type="video/<?php echo $file_ext; ?>">
                Your browser does not support the video tag.
            </video>
            
        <?php elseif ($file_ext === 'epub'): ?>
            <!-- EPUB Viewer (simplified) -->
            <div class="epub-container">
                <p>EPUB files require special handling. This is a simplified viewer.</p>
                <iframe src="<?php echo $file_data; ?>" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
            
        <?php elseif ($file_ext === 'docx'): ?>
            <!-- DOCX Viewer -->
            <div class="docx-container">
                <p>DOCX files are not directly viewable in browser. Download functionality would be implemented here.</p>
                <a href="<?php echo $file_data; ?>" download="<?php echo $file_name; ?>" class="download-btn">Download File</a>
            </div>
            
        <?php else: ?>
            <p>Unsupported file format: <?php echo $file_ext; ?></p>
        <?php endif; ?>

        <div id="watermark" style="display: none;">EduVault - Student ID: <?php echo $student_id; ?></div>
        <div class="pdf-watermark">EduVault - Student ID: <?php echo $student_id; ?></div>
    </div>

    <style>
        .viewer-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }
        
        .book-title {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        
        #pdfCanvas {
            border: 1px solid #ddd;
            display: block;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .pagination {
            text-align: center;
            margin: 20px 0;
        }
        
        .pagination button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 0 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        
        .pagination button:hover {
            background: #0056b3;
        }
        
        .pagination button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .pdf-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            color: rgba(0, 0, 0, 0.1);
            pointer-events: none;
            z-index: 1000;
            font-weight: bold;
        }
        
        .download-btn {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .download-btn:hover {
            background: #218838;
            color: white;
            text-decoration: none;
        }
    </style>

    <!-- Disable right-click and text selection for security -->
    <script>
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        
        // Disable text selection
        document.addEventListener('selectstart', function(e) {
            e.preventDefault();
        });
        
        // Disable F12, Ctrl+Shift+I, Ctrl+U
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || 
                (e.ctrlKey && e.shiftKey && e.key === 'I') || 
                (e.ctrlKey && e.key === 'u')) {
                e.preventDefault();
            }
        });
        
        // Disable drag and drop
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>
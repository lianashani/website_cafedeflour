<?php
include '../../../../config/koneksi.php';

$kode = $_GET['kode'] ?? '';

$data = null;
if ($kode != '') {
    $q = mysqli_query($conn, "SELECT * FROM transaksi WHERE kode_transaksi = '$kode'");
    $data = mysqli_fetch_assoc($q);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Order Tracking System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.6;
        }

        .main-wrapper {
            min-height: 100vh;
            padding: 2rem 0;
        }

        .container-custom {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header */
        .header-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            padding: 2rem;
            text-align: center;
            border-top: 4px solid var(--primary-color);
        }

        .header-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .header-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
            font-weight: 400;
        }

        /* Search Card */
        .search-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            margin-bottom: 2rem;
        }

        .search-header {
            padding: 1.5rem 2rem 0;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 0;
        }

        .search-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .search-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .search-body {
            padding: 2rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control-professional {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            font-size: 1rem;
            background-color: white;
            transition: all 0.2s ease;
        }

        .form-control-professional:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control-professional::placeholder {
            color: var(--gray-400);
        }

        .btn-professional {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 0.5rem;
        }

        .btn-primary-professional {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary-professional:hover {
            background-color: var(--primary-dark);
            color: white;
        }

        .btn-secondary-professional {
            background-color: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .btn-secondary-professional:hover {
            background-color: var(--gray-200);
            color: var(--gray-800);
        }

        /* QR Upload Section */
        .qr-section {
            border-top: 1px solid var(--gray-200);
            padding-top: 2rem;
            margin-top: 2rem;
        }

        .qr-upload-area {
            border: 2px dashed var(--gray-300);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background-color: var(--gray-50);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .qr-upload-area:hover {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.02);
        }

        .qr-upload-area.dragover {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
        }

        .qr-icon {
            font-size: 2.5rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .qr-upload-text {
            color: var(--gray-600);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .file-input-hidden {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .preview-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: none;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 6px;
            border: 1px solid var(--gray-200);
        }

        .qr-result {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .qr-result.success {
            background-color: rgba(5, 150, 105, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(5, 150, 105, 0.2);
        }

        .qr-result.error {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        /* Results */
        .result-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .result-header {
            background-color: var(--gray-900);
            color: white;
            padding: 1.5rem 2rem;
        }

        .result-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .result-subtitle {
            color: var(--gray-300);
            font-size: 0.875rem;
        }

        .result-body {
            padding: 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-row {
            border-bottom: 1px solid var(--gray-200);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            padding: 1rem 2rem;
            background-color: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
            width: 35%;
            vertical-align: top;
            border-right: 1px solid var(--gray-200);
        }

        .info-value {
            padding: 1rem 2rem;
            color: var(--gray-800);
            vertical-align: top;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-pending {
            background-color: rgba(217, 119, 6, 0.1);
            color: var(--warning-color);
        }

        .status-processing {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
        }

        .status-completed {
            background-color: rgba(5, 150, 105, 0.1);
            color: var(--success-color);
        }

        .status-cancelled {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--danger-color);
        }

        /* Error State */
        .error-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            padding: 3rem 2rem;
            text-align: center;
        }

        .error-icon {
            font-size: 3rem;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .error-message {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* Utilities */
        .text-center {
            text-align: center;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container-custom {
                padding: 0 0.5rem;
            }

            .header-section,
            .search-body {
                padding: 1.5rem;
            }

            .search-header {
                padding: 1.5rem 1.5rem 0;
            }

            .info-label,
            .info-value {
                display: block;
                width: 100%;
                padding: 0.75rem 1.5rem;
                border-right: none;
            }

            .info-label {
                background-color: var(--gray-100);
                border-bottom: none;
                padding-bottom: 0.5rem;
            }

            .info-value {
                padding-top: 0.5rem;
            }

            .d-flex {
                flex-direction: column;
            }

            .btn-professional {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="container-custom">
        <!-- Header -->
        <div class="header-section">
            <h1 class="header-title">Order Tracking System</h1>
            <p class="header-subtitle">Track your order status and delivery information</p>
        </div>

        <!-- Search Form -->
        <div class="search-card">
            <div class="search-header">
                <h2 class="search-title">Track Your Order</h2>
                <p class="search-description">Enter your transaction code to view order details and current status</p>
            </div>
            
            <div class="search-body">
                <form method="get" id="trackingForm">
                    <div class="form-group">
                        <label for="kodeInput" class="form-label">Transaction Code</label>
                        <input type="text" 
                               name="kode" 
                               id="kodeInput" 
                               value="<?= htmlspecialchars($kode) ?>" 
                               class="form-control-professional" 
                               placeholder="Enter your transaction code (e.g., TRX001234567890)" 
                               required>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn-professional btn-primary-professional">
                            <i class="bi bi-search"></i>
                            Track Order
                        </button>
                        <a href="../../backend/transaksi/tracking.php" class="btn-professional btn-secondary-professional">
                            <i class="bi bi-arrow-clockwise"></i>
                            Reset
                        </a>
                    </div>
                </form>

                <!-- QR Code Section -->
                <div class="qr-section">
                    <div class="form-group">
                        <label class="form-label">QR Code Scanner</label>
                        <div class="qr-upload-area" id="qrUploadArea">
                            <i class="bi bi-qr-code qr-icon"></i>
                            <div class="qr-upload-text">
                                <strong>Upload QR Code Image</strong><br>
                                Drag and drop your QR code image here, or click to select
                            </div>
                            <button type="button" class="btn-professional btn-secondary-professional" onclick="document.getElementById('qrImage').click()">
                                <i class="bi bi-cloud-upload"></i>
                                Choose File
                            </button>
                            <input type="file" id="qrImage" class="file-input-hidden" accept="image/*">
                        </div>
                        
                        <div class="preview-section" id="previewSection">
                            <div class="text-center">
                                <img id="preview" class="preview-image" src="#" alt="QR Code Preview">
                                <div id="qrResult" class="qr-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <?php if ($data): ?>
            <div class="result-card">
                <div class="result-header">
                    <div class="result-title">Order Details</div>
                    <div class="result-subtitle">Transaction Code: <?= htmlspecialchars($data['kode_transaksi']) ?></div>
                </div>
                <div class="result-body">
                    <table class="info-table">
                        <tr class="info-row">
                            <td class="info-label">Customer Name</td>
                            <td class="info-value"><?= htmlspecialchars($data['nama_pelanggan']) ?></td>
                        </tr>
                        <tr class="info-row">
                            <td class="info-label">Date & Time</td>
                            <td class="info-value"><?= htmlspecialchars($data['tanggal']) ?> at <?= htmlspecialchars($data['waktu']) ?></td>
                        </tr>
                        <tr class="info-row">
                            <td class="info-label">Location</td>
                            <td class="info-value"><?= htmlspecialchars($data['lokasi']) ?></td>
                        </tr>
                        <tr class="info-row">
                            <td class="info-label">Notes</td>
                            <td class="info-value"><?= htmlspecialchars($data['catatan']) ?: 'No additional notes' ?></td>
                        </tr>
                        <tr class="info-row">
                            <td class="info-label">Status</td>
                            <td class="info-value">
                                <?php
                                $status = strtolower(trim($data['status']));
                                $statusClass = 'status-processing'; // default
                                
                                if (in_array($status, ['pending', 'menunggu', 'waiting'])) {
                                    $statusClass = 'status-pending';
                                } elseif (in_array($status, ['completed', 'selesai', 'done', 'delivered'])) {
                                    $statusClass = 'status-completed';
                                } elseif (in_array($status, ['cancelled', 'dibatalkan', 'batal', 'canceled'])) {
                                    $statusClass = 'status-cancelled';
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= htmlspecialchars($data['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php elseif ($kode): ?>
            <div class="error-card">
                <i class="bi bi-exclamation-triangle error-icon"></i>
                <h3 class="error-title">Order Not Found</h3>
                <p class="error-message">
                    The order with transaction code <strong><?= htmlspecialchars($kode) ?></strong> was not found in our system.<br>
                    Please verify the transaction code and try again.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- QR Code Scanner Script -->
<script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrUploadArea = document.getElementById('qrUploadArea');
    const qrImageInput = document.getElementById('qrImage');
    const previewSection = document.getElementById('previewSection');
    const preview = document.getElementById('preview');
    const qrResult = document.getElementById('qrResult');
    const kodeInput = document.getElementById('kodeInput');
    const trackingForm = document.getElementById('trackingForm');

    // Focus on input if empty
    if (!kodeInput.value.trim()) {
        kodeInput.focus();
    }

    // Drag and drop functionality
    qrUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        qrUploadArea.classList.add('dragover');
    });

    qrUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        qrUploadArea.classList.remove('dragover');
    });

    qrUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        qrUploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type.startsWith('image/')) {
            qrImageInput.files = files;
            processQRImage(files[0]);
        }
    });

    // File input change
    qrImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            processQRImage(file);
        }
    });

    // Click to upload
    qrUploadArea.addEventListener('click', function() {
        qrImageInput.click();
    });

    function processQRImage(file) {
        if (!file.type.startsWith('image/')) {
            showQRResult('Please select a valid image file.', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                // Show preview
                preview.src = e.target.result;
                previewSection.style.display = 'block';
                
                // Process QR code
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                context.drawImage(img, 0, 0);

                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, canvas.width, canvas.height);

                if (code) {
                    showQRResult('QR code successfully read: ' + code.data, 'success');
                    kodeInput.value = code.data;
                    
                    // Auto-submit after 1.5 seconds
                    setTimeout(() => {
                        trackingForm.submit();
                    }, 1500);
                } else {
                    showQRResult('Unable to read QR code. Please ensure the image is clear and contains a valid QR code.', 'error');
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function showQRResult(message, type) {
        qrResult.textContent = message;
        qrResult.className = 'qr-result ' + type;
        qrResult.style.display = 'block';
    }

    // Form validation
    trackingForm.addEventListener('submit', function(e) {
        if (!kodeInput.value.trim()) {
            e.preventDefault();
            kodeInput.focus();
            kodeInput.style.borderColor = 'var(--danger-color)';
            
            setTimeout(() => {
                kodeInput.style.borderColor = '';
            }, 3000);
        }
    });
});
</script>

</body>
</html>
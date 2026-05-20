<?php
$uploadDir = __DIR__ . '/uploads';
$message = null;
$messageType = 'success';
$uploadedFiles = [];
$uploadErrors = [];

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['user_files'])) {
    $files = $_FILES['user_files'];
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'text/plain', 'application/zip',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword'
    ];

    $maxSize = 10 * 1024 * 1024; // 10 MB

    for ($i = 0, $count = count($files['name']); $i < $count; $i++) {
        $originalName = trim((string) $files['name'][$i]);

        if ($originalName === '') {
            continue;
        }

        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $uploadErrors[] = sprintf('"%s" failed to upload.', htmlspecialchars($originalName));
            continue;
        }

        if ($files['size'][$i] > $maxSize) {
            $uploadErrors[] = sprintf('"%s" is larger than 10 MB.', htmlspecialchars($originalName));
            continue;
        }

        $tmpName = $files['tmp_name'][$i];
        $fileType = mime_content_type($tmpName);
        if (!in_array($fileType, $allowedTypes, true)) {
            $uploadErrors[] = sprintf('"%s" has an unsupported file format.', htmlspecialchars($originalName));
            continue;
        }

        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($originalName));
        $targetName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeName;
        $targetPath = $uploadDir . '/' . $targetName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            $uploadErrors[] = sprintf('"%s" could not be saved.', htmlspecialchars($originalName));
            continue;
        }

        $uploadedFiles[] = [
            'original' => $safeName,
            'size' => round(filesize($targetPath) / 1024, 1),
            'type' => $fileType,
            'is_image' => str_starts_with($fileType, 'image/'),
            'stored' => $targetName,
        ];
    }

    if (!empty($uploadedFiles)) {
        $message = count($uploadedFiles) . ' file(s) uploaded successfully!';
        $messageType = empty($uploadErrors) ? 'success' : 'warning';
    } elseif (!empty($uploadErrors)) {
        $message = 'No files were uploaded.';
        $messageType = 'danger';
    } else {
        $message = 'Please choose at least one file.';
        $messageType = 'warning';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PulseVault Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-1: #090c1c;
            --bg-2: #1b1244;
            --accent-1: #48f5ff;
            --accent-2: #ff48ca;
            --accent-3: #7cff81;
        }

        body {
            min-height: 100vh;
            color: #f6f8ff;
            background:
                radial-gradient(circle at 15% 10%, #42267f 0%, transparent 30%),
                radial-gradient(circle at 80% 0%, #0ea5e9 0%, transparent 35%),
                linear-gradient(165deg, var(--bg-1), var(--bg-2));
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        .phone-shell {
            max-width: 430px;
            margin: 1.25rem auto;
            border-radius: 2rem;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .25);
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(16px);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .45);
        }

        .app-header {
            padding: 1.2rem;
            background: linear-gradient(120deg, var(--accent-2), var(--accent-1));
            color: #170b30;
        }

        .chip {
            display: inline-block;
            padding: .25rem .6rem;
            border-radius: 999px;
            background: rgba(23, 11, 48, .15);
            font-size: .75rem;
            font-weight: 700;
            margin-right: .35rem;
        }

        .dropzone {
            border: 2px dashed rgba(255, 255, 255, .4);
            border-radius: 1rem;
            background: rgba(255, 255, 255, .04);
            padding: 1.2rem 1rem;
            text-align: center;
            transition: .2s ease;
            cursor: pointer;
        }

        .dropzone.dragover {
            border-color: var(--accent-3);
            background: rgba(124, 255, 129, .1);
            transform: scale(1.01);
        }

        .btn-pulse {
            border: 0;
            color: #1a0a2f;
            font-weight: 700;
            background: linear-gradient(90deg, var(--accent-2), var(--accent-1));
        }

        .file-item {
            border: 1px solid rgba(255, 255, 255, .15);
            background: rgba(255, 255, 255, .08);
            border-radius: .85rem;
            padding: .65rem .75rem;
        }

        .thumb {
            width: 38px;
            height: 38px;
            border-radius: .6rem;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, .3);
        }

        .tiny { font-size: .78rem; opacity: .85; }
    </style>
</head>
<body>
<div class="container py-3">
    <div class="phone-shell">
        <div class="app-header">
            <h1 class="h4 fw-bold mb-1">🚀 PulseVault</h1>
            <p class="mb-2">Fast, beautiful uploads for your mobile app.</p>
            <span class="chip">Drag & drop</span><span class="chip">Multi-file</span><span class="chip">Secure checks</span>
        </div>

        <div class="p-3 p-md-4">
            <?php if ($message): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?> mb-3"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if (!empty($uploadErrors)): ?>
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3 tiny">
                        <?php foreach ($uploadErrors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <label for="user_files" class="dropzone mb-3 w-100" id="dropzone">
                    <div class="fs-2">📥</div>
                    <div class="fw-semibold">Tap to pick files</div>
                    <div class="tiny">or drag files here • up to 10 MB each</div>
                    <input type="file" name="user_files[]" id="user_files" class="d-none" multiple>
                </label>

                <div id="previewList" class="d-grid gap-2 mb-3"></div>

                <button class="btn btn-pulse w-100 py-2" type="submit">Upload Files</button>
            </form>

            <?php if (!empty($uploadedFiles)): ?>
                <hr class="border-light border-opacity-25 my-4">
                <h2 class="h6 fw-bold mb-3">Latest Uploads</h2>
                <div class="d-grid gap-2">
                    <?php foreach ($uploadedFiles as $file): ?>
                        <div class="file-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <?php if ($file['is_image']): ?>
                                    <img src="<?= htmlspecialchars('uploads/' . $file['stored']) ?>" class="thumb" alt="thumbnail">
                                <?php else: ?>
                                    <div class="thumb d-flex align-items-center justify-content-center">📄</div>
                                <?php endif; ?>
                                <div class="text-truncate">
                                    <div class="text-truncate"><?= htmlspecialchars($file['original']) ?></div>
                                    <div class="tiny"><?= htmlspecialchars($file['type']) ?></div>
                                </div>
                            </div>
                            <span class="tiny ms-2"><?= htmlspecialchars((string) $file['size']) ?> KB</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const fileInput = document.getElementById('user_files');
const dropzone = document.getElementById('dropzone');
const previewList = document.getElementById('previewList');

function iconFor(name) {
    const ext = name.split('.').pop()?.toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return '🖼️';
    if (['pdf'].includes(ext)) return '📕';
    if (['zip'].includes(ext)) return '🗜️';
    return '📄';
}

function renderPreview(files) {
    previewList.innerHTML = '';
    [...files].forEach(file => {
        const row = document.createElement('div');
        row.className = 'file-item d-flex justify-content-between';
        row.innerHTML = `<span class="me-2 text-truncate">${iconFor(file.name)} ${file.name}</span><span class="tiny">${Math.ceil(file.size / 1024)} KB</span>`;
        previewList.appendChild(row);
    });
}

fileInput.addEventListener('change', () => renderPreview(fileInput.files));
['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, event => {
        event.preventDefault();
        dropzone.classList.add('dragover');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, event => {
        event.preventDefault();
        dropzone.classList.remove('dragover');
    });
});

dropzone.addEventListener('drop', event => {
    fileInput.files = event.dataTransfer.files;
    renderPreview(fileInput.files);
});
</script>
</body>
</html>

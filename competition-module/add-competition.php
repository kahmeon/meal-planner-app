<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if user is admin
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../unauthorized.php");
    exit();
}

// Retrieve success/error messages from session
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

include '../navbar.php';
?>

<style>
/* Enhanced Red and White Theme */
:root {
    --primary-red: #d9230f;
    --dark-red: #b51d0d;
    --light-bg: #fff9f9;
}

body {
    background-color: var(--light-bg);
}

.bg-custom-primary {
    background-color: var(--primary-red);
    color: white;
    border-radius: 15px 15px 0 0 !important;
}

.btn-custom-danger {
    background-color: var(--primary-red);
    border-color: var(--primary-red);
    color: white;
    font-weight: 500;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

.btn-custom-danger:hover {
    background-color: var(--dark-red);
    border-color: var(--dark-red);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(217, 35, 15, 0.3);
}

.card {
    border: none;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    border-radius: 15px;
    overflow: hidden;
}

.form-control:focus {
    border-color: var(--primary-red);
    box-shadow: 0 0 0 0.25rem rgba(217, 35, 15, 0.25);
}

.input-group-text {
    background-color: #f8f9fa;
}

.alert {
    border-radius: 10px;
    padding: 15px;
}

.alert-success {
    background-color: #f0f9f0;
    border: 2px solid #c3e6c3;
    color: #155724;
}

.alert-danger {
    background-color: #fdf3f3;
    border: 2px solid #f5c6cb;
    color: #721c24;
}

/* Toast Notifications */
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 250px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: slideIn 0.5s, fadeOut 0.5s 2.5s;
}

.toast-success {
    background-color: #28a745;
    color: white;
}

.toast-error {
    background-color: #dc3545;
    color: white;
}

.toast-body {
    padding: 15px;
    display: flex;
    align-items: center;
}

/* Image Upload Styling */
#imagePreviewContainer {
    border: 2px dashed #ddd;
    transition: all 0.3s ease;
    cursor: pointer;
}

#imagePreviewContainer:hover {
    border-color: var(--primary-red);
    background-color: #fef2f2;
}

/* Animations */
@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

/* Emoji Styling */
.emoji {
    font-size: 1.2em;
    margin-right: 8px;
    vertical-align: middle;
}

input[type="date"].form-control {
    padding-top: 0.65rem;
    padding-bottom: 0.65rem;
}

</style>

<div class="container py-5">
  <div class="card">
    <div class="card-header bg-custom-primary py-3">
      <h2 class="mb-0 text-white"><i class="fas fa-plus-circle me-2"></i><span class="emoji">➕</span> Add New Competition</h2>
    </div>
    
    <div class="card-body">
      <?php if ($success): ?>
        <div class="alert alert-success mb-4">
          <i class="fas fa-check-circle me-2"></i><span class="emoji">🎉</span> <?= htmlspecialchars($success) ?>
        </div>
      <?php elseif ($error): ?>
        <div class="alert alert-danger mb-4">
          <i class="fas fa-exclamation-circle me-2"></i><span class="emoji">⚠️</span> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="process-add-competition.php" enctype="multipart/form-data" id="competitionForm">
        <div class="row g-4">
          <!-- Left Column -->
          <div class="col-lg-8">
            <div class="mb-4">
              <label class="form-label fw-bold">Competition Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control form-control-lg py-3" 
                     value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required
                     placeholder="🏆 Enter competition title">
            </div>
            
            <div class="mb-4">
              <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control py-3" rows="6" required
                        placeholder="📝 Describe your competition..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                  <input type="date" name="start_date" class="form-control py-3" 
                         value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                  <input type="date" name="end_date" class="form-control py-3" 
                         value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Right Column -->
          <div class="col-lg-4">
            <div class="card bg-light border-0">
              <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-cog me-2"></i><span class="emoji">⚙️</span> Settings</h5>
                
                <div class="mb-4">
                  <label class="form-label fw-bold">Prize Details</label>
                  <input type="text" name="prize" class="form-control py-3" 
                         value="<?= htmlspecialchars($_POST['prize'] ?? '') ?>"
                         placeholder="💰 Prize information">
                </div>
                
                <div class="mb-4">
                  <label class="form-label fw-bold">Competition Banner</label>
                  <div id="imagePreviewContainer" class="border rounded-3 p-3 text-center mb-2" style="height: 180px; background-color: #f8f9fa;">
                    <i class="fas fa-image fa-4x text-muted" id="imagePlaceholder" style="line-height: 180px;"></i>
                    <img id="imagePreview" src="" class="img-fluid d-none" style="max-height: 100%; border-radius: 8px;">
                  </div>
                  <input type="file" name="image" id="imageUpload" class="form-control" accept="image/*" onchange="previewImage(this)">
                  <div class="form-text mt-2">📷 Max 5MB (JPEG/PNG only)</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="d-flex justify-content-between mt-5 pt-3 border-top">
          <a href="competition.php" class="btn btn-outline-danger px-4">
            <i class="fas fa-arrow-left me-2"></i><span class="emoji">❌</span> Cancel
          </a>
          <button type="submit" class="btn btn-custom-danger px-4">
            <i class="fas fa-plus me-2"></i><span class="emoji">➕</span> Add Competition
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Toast Notifications -->
<div id="toastSuccess" class="toast toast-success" style="display:none;">
  <div class="toast-body">
    <i class="fas fa-check-circle me-2"></i><span class="emoji">🎉</span> Competition added successfully!
  </div>
</div>

<div id="toastError" class="toast toast-error" style="display:none;">
  <div class="toast-body">
    <i class="fas fa-exclamation-circle me-2"></i><span class="emoji">⚠️</span> Error adding competition!
  </div>
</div>

<script>
// Toast Notifications
<?php if ($success): ?>
  showToast('success');
<?php elseif ($error): ?>
  showToast('error');
<?php endif; ?>

function showToast(type) {
  const toast = document.getElementById(`toast${type.charAt(0).toUpperCase() + type.slice(1)}`);
  toast.style.display = 'block';
  setTimeout(() => {
    toast.style.display = 'none';
  }, 3000);
}

// Enhanced Image Preview with Drag & Drop
const imageUpload = document.getElementById('imageUpload');
const imagePreviewContainer = document.getElementById('imagePreviewContainer');

imagePreviewContainer.addEventListener('click', () => imageUpload.click());
imagePreviewContainer.addEventListener('dragover', (e) => {
  e.preventDefault();
  imagePreviewContainer.style.borderColor = 'var(--primary-red)';
  imagePreviewContainer.style.backgroundColor = '#fef2f2';
});
imagePreviewContainer.addEventListener('dragleave', () => {
  imagePreviewContainer.style.borderColor = '#ddd';
  imagePreviewContainer.style.backgroundColor = '#f8f9fa';
});
imagePreviewContainer.addEventListener('drop', (e) => {
  e.preventDefault();
  imagePreviewContainer.style.borderColor = '#ddd';
  imagePreviewContainer.style.backgroundColor = '#f8f9fa';
  if (e.dataTransfer.files.length) {
    imageUpload.files = e.dataTransfer.files;
    previewImage(imageUpload);
  }
});

function previewImage(input) {
  const preview = document.getElementById('imagePreview');
  const placeholder = document.getElementById('imagePlaceholder');
  const container = document.getElementById('imagePreviewContainer');
  const file = input.files[0];
  
  if (file) {
    const validTypes = ['image/jpeg', 'image/png'];
    if (!validTypes.includes(file.type)) {
      alert('❌ Only JPEG and PNG images are allowed');
      input.value = '';
      return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
      alert('❌ Image must be less than 5MB');
      input.value = '';
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      placeholder.classList.add('d-none');
      preview.src = e.target.result;
      preview.classList.remove('d-none');
      preview.style.objectFit = 'cover';
      container.style.backgroundColor = 'transparent';
    }
    reader.readAsDataURL(file);
  } else {
    placeholder.classList.remove('d-none');
    preview.classList.add('d-none');
    container.style.backgroundColor = '#f8f9fa';
  }
}

// Enhanced Form Validation
// Enhanced Form Validation
document.getElementById('competitionForm').addEventListener('submit', function(e) {
  const title = document.querySelector('[name="title"]').value.trim();
  const startDate = new Date(document.querySelector('[name="start_date"]').value);
  const endDate = new Date(document.querySelector('[name="end_date"]').value);
  const today = new Date();
  
  // Remove time part of today's date
  today.setHours(0, 0, 0, 0);

  if (title.length < 4) {
    alert('⚠️ Competition title must be at least 4 characters');
    e.preventDefault();
    return;
  }

  // Check if the start date is in the past
  if (startDate < today) {
    alert('⚠️ Start date cannot be in the past');
    e.preventDefault();
    return;
  }

  if (endDate <= startDate) {
    alert('⚠️ End date must be after start date');
    e.preventDefault();
  }
});

</script>

<?php include '../includes/footer.php'; ?>
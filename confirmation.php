<?php
// Success Messages Configuration
$successMessages = [
    'AdminAdded' => ['✅', 'Admin added successfully!', 'green'],
    'AdminUpdated' => ['✅', 'Admin updated successfully!', 'green'],
    'AdminDeleted' => ['🗑️', 'Admin deleted successfully!', 'red'],
    'UserAdded' => ['✅', 'User added successfully!', 'green'],
    'UserUpdated' => ['✅', 'User updated successfully!', 'green'],
    'UserDeleted' => ['🗑️', 'User deleted successfully!', 'red'],
    'StudentAdded' => ['✅', 'Student added successfully!', 'green'],
    'StudentUpdated' => ['✅', 'Student updated successfully!', 'green'],
    'StudentDeleted' => ['🗑️', 'Student deleted successfully!', 'red'],
    'Error' => ['❌', 'An error occurred. Please try again.', 'red'],
    'AccessDenied' => ['🚫', 'Access denied. You do not have permission.', 'red'],
];

// Process success message
$currentSuccessMsg = "";
$currentIcon = "";
$currentColor = "green";

if (isset($_GET['msg']) && array_key_exists($_GET['msg'], $successMessages)) {
    $msgData = $successMessages[$_GET['msg']];
    $currentIcon = $msgData[0];
    $currentSuccessMsg = $msgData[1];
    $currentColor = $msgData[2];
}

$colorClasses = [
    'green' => 'bg-green-100 border-green-400 text-green-700',
    'red' => 'bg-red-100 border-red-400 text-red-700',
    'blue' => 'bg-blue-100 border-blue-400 text-blue-700',
    'yellow' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
];

$alertClass = $colorClasses[$currentColor] ?? $colorClasses['green'];

// Helper function to create confirmed forms
function createConfirmedForm($action, $method = 'POST', $enctype = null, $confirmOptions = []) {
    $defaultOptions = [
        'message' => 'Are you sure you want to proceed?',
        'title' => 'Confirm Action',
        'icon' => '⚠️',
        'confirmText' => 'Yes',
        'cancelText' => 'Cancel',
        'confirmClass' => 'bg-blue-600 hover:bg-blue-700',
        'validateFields' => '',
        'validateFiles' => ''
    ];
    
    $options = array_merge($defaultOptions, $confirmOptions);
    
    $enctypeAttr = $enctype ? "enctype=\"$enctype\"" : '';
    $validateFieldsAttr = $options['validateFields'] ? "data-validate-fields=\"{$options['validateFields']}\"" : '';
    $validateFilesAttr = $options['validateFiles'] ? "data-validate-files=\"{$options['validateFiles']}\"" : '';
    
    return "<form action=\"$action\" method=\"$method\" $enctypeAttr class=\"space-y-5\" 
                  data-confirm=\"true\"
                  data-confirm-message=\"{$options['message']}\"
                  data-confirm-title=\"{$options['title']}\"
                  data-confirm-icon=\"{$options['icon']}\"
                  data-confirm-text=\"{$options['confirmText']}\"
                  data-confirm-cancel=\"{$options['cancelText']}\"
                  data-confirm-class=\"{$options['confirmClass']}\"
                  $validateFieldsAttr
                  $validateFilesAttr>";
}

// NEW: Generate confirmation script for custom forms (keeps original UI)
function generateConfirmationScript($options = []) {
    $defaultOptions = [
        'message' => 'Are you sure you want to proceed?',
        'title' => 'Confirm Action',
        'icon' => '⚠️',
        'confirmText' => 'Yes',
        'cancelText' => 'Cancel',
        'confirmClass' => 'bg-blue-600 hover:bg-blue-700',
        'validateFields' => '',
        'validateFiles' => ''
    ];
    
    $options = array_merge($defaultOptions, $options);
    
    // Build validation JavaScript
    $validationJS = '';
    $fieldValidationJS = '';
    $fileValidationJS = '';
    
    // Field validation
    if (!empty($options['validateFields'])) {
        $fields = array_map('trim', explode(',', $options['validateFields']));
        foreach ($fields as $field) {
            $fieldLabel = ucwords(str_replace(['_', 'Id'], [' ', ' ID'], $field));
            $fieldValidationJS .= "
        const {$field}Value = formData.get('{$field}')?.trim();
        if (!{$field}Value) {
            formSystem.showSuccessMessage('❌ {$fieldLabel} is required', '❌', 'red', 8000);
            const field = document.querySelector('[name=\"{$field}\"]');
            if (field) {
                field.classList.add('border-red-500');
                field.focus();
            }
            return false;
        } else {
            const field = document.querySelector('[name=\"{$field}\"]');
            if (field) field.classList.remove('border-red-500');
        }";
            
            // Email validation
            if ($field === 'email') {
                $fieldValidationJS .= "
        if ({$field}Value && !/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test({$field}Value)) {
            formSystem.showSuccessMessage('❌ Please enter a valid email address', '❌', 'red', 8000);
            const field = document.querySelector('[name=\"{$field}\"]');
            if (field) {
                field.classList.add('border-red-500');
                field.focus();
            }
            return false;
        }";
            }
        }
    }
    
    // File validation
    if (!empty($options['validateFiles'])) {
        $files = array_map('trim', explode(',', $options['validateFiles']));
        foreach ($files as $file) {
            $fileLabel = ucwords(str_replace(['_', 'Path'], [' ', ''], $file));
            $fileValidationJS .= "
        const {$file}File = formData.get('{$file}');
        if (!{$file}File || {$file}File.size === 0) {
            formSystem.showSuccessMessage('❌ {$fileLabel} is required', '❌', 'red', 8000);
            const field = document.querySelector('[name=\"{$file}\"]');
            if (field) {
                field.classList.add('border-red-500');
                field.focus();
            }
            return false;
        } else {
            const field = document.querySelector('[name=\"{$file}\"]');
            if (field) field.classList.remove('border-red-500');
        }";
        }
    }
    
    $validationJS = $fieldValidationJS . $fileValidationJS;
    
    // Build personalized message JavaScript
    $personalizedMessageJS = "let confirmMessage = `{$options['message']}`;";
    if (strpos($options['message'], '{name}') !== false || strpos($options['message'], '{email}') !== false || strpos($options['message'], '{department}') !== false) {
        $personalizedMessageJS .= "
        const nameValue = formData.get('name')?.trim() || '';
        const emailValue = formData.get('email')?.trim() || '';
        const departmentValue = formData.get('department')?.trim() || '';
        confirmMessage = confirmMessage.replace('{name}', nameValue).replace('{email}', emailValue).replace('{department}', departmentValue);";
    }
    
    return "
<script>
function confirmSubmission(event) {
    const formData = new FormData(event.target);
    
    // Validation
    {$validationJS}
    
    // Personalize confirmation message
    {$personalizedMessageJS}
    
    // Show confirmation modal using the existing FormSystem
    if (window.formSystem) {
        event.preventDefault();
        formSystem.showModal({
            title: '{$options['title']}',
            message: confirmMessage,
            icon: '{$options['icon']}',
            confirmText: '{$options['confirmText']}',
            cancelText: '{$options['cancelText']}',
            confirmClass: '{$options['confirmClass']}',
            onConfirm: () => {
                // Submit the form
                event.target.submit();
            }
        });
        return false;
    } else {
        // Fallback to browser confirm if FormSystem not available
        return confirm('{$options['icon']} ' + confirmMessage);
    }
}
</script>";
}
?>

<!-- Success Message Display -->
<?php if ($currentSuccessMsg): ?>
    <div id="successMessage" class="<?= $alertClass ?> border px-4 py-3 rounded mb-6 relative">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-xl mr-2"><?= $currentIcon ?></span>
                <span><?= $currentSuccessMsg ?></span>
            </div>
            <button onclick="hideSuccessMessage()" class="ml-4 text-lg font-bold hover:opacity-70 transition-opacity">×</button>
        </div>
    </div>
<?php endif; ?>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <div class="flex items-center mb-4">
            <span id="modalIcon" class="text-2xl mr-3">⚠️</span>
            <h3 id="modalTitle" class="font-bold text-xl text-gray-800">Confirm Action</h3>
        </div>
        <div class="mb-6">
            <p id="modalMessage" class="text-gray-700">Are you sure you want to proceed?</p>
        </div>
        <div class="flex gap-3 justify-end">
            <button id="modalCancelBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold transition-colors">No</button>
            <button id="modalConfirmBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold transition-colors">Yes</button>
        </div>
    </div>
</div>

<script>
// Complete Form System JavaScript
class FormSystem {
    constructor() {
        this.initConfirmationModal();
        this.initFormHandlers();
        this.initSuccessMessages();
    }
    
    // Confirmation Modal System
    initConfirmationModal() {
        this.modal = document.getElementById('confirmationModal');
        this.icon = document.getElementById('modalIcon');
        this.title = document.getElementById('modalTitle');
        this.message = document.getElementById('modalMessage');
        this.cancelBtn = document.getElementById('modalCancelBtn');
        this.confirmBtn = document.getElementById('modalConfirmBtn');
        this.currentCallback = null;
        
        // Event listeners
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.hideModal();
        });
        
        this.cancelBtn?.addEventListener('click', () => this.hideModal());
        this.confirmBtn?.addEventListener('click', () => {
            if (this.currentCallback) this.currentCallback();
            this.hideModal();
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal?.classList.contains('hidden')) {
                this.hideModal();
            }
        });
    }
    
    showModal(options = {}) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure you want to proceed?',
            icon = '⚠️',
            confirmText = 'Yes',
            cancelText = 'No',
            confirmClass = 'bg-red-600 hover:bg-red-700',
            onConfirm = null
        } = options;
        
        if (this.title) this.title.textContent = title;
        if (this.message) this.message.textContent = message;
        if (this.icon) this.icon.textContent = icon;
        if (this.confirmBtn) this.confirmBtn.textContent = confirmText;
        if (this.cancelBtn) this.cancelBtn.textContent = cancelText;
        
        if (this.confirmBtn) {
            this.confirmBtn.className = `text-white px-4 py-2 rounded font-semibold transition-colors ${confirmClass}`;
        }
        
        this.currentCallback = onConfirm;
        this.modal?.classList.remove('hidden');
        this.cancelBtn?.focus();
    }
    
    hideModal() {
        this.modal?.classList.add('hidden');
        this.currentCallback = null;
    }
    
    // Form Handlers
    initFormHandlers() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.tagName === 'FORM' && form.getAttribute('data-confirm') === 'true') {
                e.preventDefault();
                this.handleConfirmedForm(form);
            }
        });
    }
    
    handleConfirmedForm(form) {
        // Validate form first
        const validation = this.validateForm(form);
        if (!validation.isValid) {
            this.showValidationErrors(validation.errors);
            return;
        }
        
        // Get confirmation options
        const confirmMessage = form.getAttribute('data-confirm-message') || 'Are you sure?';
        const confirmTitle = form.getAttribute('data-confirm-title') || 'Confirm Action';
        const confirmIcon = form.getAttribute('data-confirm-icon') || '⚠️';
        const confirmText = form.getAttribute('data-confirm-text') || 'Yes';
        const cancelText = form.getAttribute('data-confirm-cancel') || 'Cancel';
        const confirmClass = form.getAttribute('data-confirm-class') || 'bg-blue-600 hover:bg-blue-700';
        
        // Personalize message
        let personalizedMessage = confirmMessage;
        const formData = new FormData(form);
        personalizedMessage = personalizedMessage.replace('{name}', formData.get('name') || '');
        personalizedMessage = personalizedMessage.replace('{email}', formData.get('email') || '');
        personalizedMessage = personalizedMessage.replace('{department}', formData.get('department') || '');
        
        // Show confirmation
        this.showModal({
            title: confirmTitle,
            message: personalizedMessage,
            icon: confirmIcon,
            confirmText: confirmText,
            cancelText: cancelText,
            confirmClass: confirmClass,
            onConfirm: () => {
                // Remove the data-confirm attribute to prevent double confirmation
                form.removeAttribute('data-confirm');
                form.submit();
            }
        });
    }
    
    validateForm(form) {
        const errors = [];
        let isValid = true;
        
        // Validate required fields
        const validateFields = form.getAttribute('data-validate-fields');
        if (validateFields) {
            const fieldNames = validateFields.split(',').map(field => field.trim());
            fieldNames.forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    const value = field.value.trim();
                    if (!value) {
                        errors.push(`${this.formatFieldName(fieldName)} is required`);
                        isValid = false;
                        field.classList.add('border-red-500');
                    } else {
                        field.classList.remove('border-red-500');
                        
                        // Email validation
                        if (fieldName === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                            errors.push('Please enter a valid email address');
                            isValid = false;
                            field.classList.add('border-red-500');
                        }
                    }
                }
            });
        }
        
        // Validate required files
        const validateFiles = form.getAttribute('data-validate-files');
        if (validateFiles) {
            const fileNames = validateFiles.split(',').map(file => file.trim());
            fileNames.forEach(fileName => {
                const fileField = form.querySelector(`input[name="${fileName}"][type="file"]`);
                if (fileField && (!fileField.files || fileField.files.length === 0)) {
                    errors.push(`${this.formatFieldName(fileName)} is required`);
                    isValid = false;
                    fileField.classList.add('border-red-500');
                } else if (fileField) {
                    fileField.classList.remove('border-red-500');
                }
            });
        }
        
        return { isValid, errors };
    }
    
    formatFieldName(fieldName) {
        return fieldName
            .replace(/([A-Z])/g, ' $1')
            .replace(/^./, str => str.toUpperCase())
            .replace(/([a-z])([A-Z])/g, '$1 $2');
    }
    
    showValidationErrors(errors) {
        if (errors.length > 0) {
            this.showSuccessMessage(errors.join('<br>'), '❌', 'red', 8000);
        }
    }
    
    // Success Messages
    initSuccessMessages() {
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => this.hideSuccessMessage(), 5000);
        }
    }
    
    hideSuccessMessage() {
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.style.transition = 'opacity 0.3s ease-out';
            successMessage.style.opacity = '0';
            setTimeout(() => successMessage.style.display = 'none', 300);
        }
    }
    
    showSuccessMessage(message, icon = '✅', color = 'green', duration = 5000) {
        const existingMessage = document.getElementById('successMessage');
        if (existingMessage) existingMessage.remove();
        
        const colorClasses = {
            'green': 'bg-green-100 border-green-400 text-green-700',
            'red': 'bg-red-100 border-red-400 text-red-700',
            'blue': 'bg-blue-100 border-blue-400 text-blue-700',
            'yellow': 'bg-yellow-100 border-yellow-400 text-yellow-700',
        };
        
        const alertClass = colorClasses[color] || colorClasses['green'];
        const messageHTML = `
            <div id="successMessage" class="${alertClass} border px-4 py-3 rounded mb-6 relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-xl mr-2">${icon}</span>
                        <span>${message}</span>
                    </div>
                    <button onclick="formSystem.hideSuccessMessage()" class="ml-4 text-lg font-bold hover:opacity-70 transition-opacity">×</button>
                </div>
            </div>
        `;
        
        const mainContent = document.querySelector('form')?.parentElement || document.body;
        mainContent.insertAdjacentHTML('afterbegin', messageHTML);
        
        if (duration > 0) {
            setTimeout(() => this.hideSuccessMessage(), duration);
        }
    }
}

// Initialize the system
document.addEventListener('DOMContentLoaded', function() {
    window.formSystem = new FormSystem();
});

// Legacy support functions
function hideSuccessMessage() {
    if (window.formSystem) window.formSystem.hideSuccessMessage();
}

function showSuccessMessage(message, icon, color, duration) {
    if (window.formSystem) window.formSystem.showSuccessMessage(message, icon, color, duration);
}
</script>